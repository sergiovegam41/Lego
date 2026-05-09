<?php

namespace Core\Services\Graph;

use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class GraphScanner
{
    private Graph $graph;
    private array $classToNodeId = [];
    private array $errors = [];

    public function __construct(
        private readonly array $scanPaths,
        private readonly string $projectRoot,
        private readonly array $excludePaths = ['vendor', 'node_modules', 'tests', 'doc', 'public/uploads']
    ) {
        $this->graph = new Graph();
    }

    public function scan(): Graph
    {
        $files = [];
        foreach ($this->scanPaths as $path) {
            $files = array_merge($files, $this->collectPhpFiles($path));
        }

        foreach ($files as $file) {
            $this->indexFile($file);
        }

        foreach ($files as $file) {
            $this->extractRelationships($file);
        }

        return $this->graph;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function collectPhpFiles(string $dir): array
    {
        $absolute = $this->projectRoot . DIRECTORY_SEPARATOR . $dir;
        if (!is_dir($absolute)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($absolute, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($this->projectRoot . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);

            foreach ($this->excludePaths as $exclude) {
                if (str_starts_with($relativePath, $exclude . '/') || str_starts_with($relativePath, $exclude)) {
                    continue 2;
                }
            }

            $files[] = $file->getPathname();
        }

        return $files;
    }

    private function indexFile(string $file): void
    {
        $ast = $this->parseFile($file);
        if ($ast === null) {
            return;
        }

        $finder = new NodeFinder();
        $classes = $finder->find($ast, fn(Node $n) =>
            $n instanceof Node\Stmt\Class_
            || $n instanceof Node\Stmt\Interface_
            || $n instanceof Node\Stmt\Trait_
            || $n instanceof Node\Stmt\Enum_
        );

        foreach ($classes as $class) {
            if (!isset($class->namespacedName)) {
                continue;
            }

            $fqn  = $class->namespacedName->toString();
            $id   = $this->classToId($fqn);
            $type = $this->detectType($class);

            $relativePath = $this->relativePath($file);
            $loc = count(file($file)) ?: 0;

            $this->graph->addNode($id, [
                'id'         => $id,
                'fqn'        => $fqn,
                'short_name' => $class->name->toString(),
                'namespace'  => $class->namespacedName->slice(0, -1)?->toString() ?? '',
                'file'       => $relativePath,
                'type'       => $type,
                'layer'      => $this->detectLayer($relativePath),
                'loc'        => $loc,
                'attributes' => $this->extractClassAttributes($class),
            ]);

            $this->classToNodeId[$fqn] = $id;
        }
    }

    private function extractRelationships(string $file): void
    {
        $ast = $this->parseFile($file);
        if ($ast === null) {
            return;
        }

        $finder = new NodeFinder();
        $classes = $finder->find($ast, fn(Node $n) =>
            $n instanceof Node\Stmt\Class_
            || $n instanceof Node\Stmt\Interface_
            || $n instanceof Node\Stmt\Trait_
        );

        foreach ($classes as $class) {
            if (!isset($class->namespacedName)) {
                continue;
            }

            $fromFqn = $class->namespacedName->toString();
            $fromId  = $this->classToId($fromFqn);

            if ($class instanceof Node\Stmt\Class_) {
                if ($class->extends) {
                    $this->addEdgeIfKnown($fromId, $class->extends->toString(), 'extends');
                }
                foreach ($class->implements as $interface) {
                    $this->addEdgeIfKnown($fromId, $interface->toString(), 'implements');
                }
            }

            if ($class instanceof Node\Stmt\Interface_) {
                foreach ($class->extends as $interface) {
                    $this->addEdgeIfKnown($fromId, $interface->toString(), 'extends');
                }
            }

            $this->extractTraitUses($class, $fromId);
            $this->extractAttributeEdges($class, $fromId);
            $this->extractInstantiations($class, $fromId);
            $this->extractStaticCalls($class, $fromId);
            $this->extractTypeHints($class, $fromId);
        }
    }

    private function extractTraitUses(Node\Stmt\ClassLike $class, string $fromId): void
    {
        $finder = new NodeFinder();
        $traitUses = $finder->findInstanceOf($class, Node\Stmt\TraitUse::class);
        foreach ($traitUses as $use) {
            foreach ($use->traits as $trait) {
                $this->addEdgeIfKnown($fromId, $trait->toString(), 'uses_trait');
            }
        }
    }

    private function extractAttributeEdges(Node\Stmt\ClassLike $class, string $fromId): void
    {
        foreach ($class->attrGroups as $group) {
            foreach ($group->attrs as $attr) {
                $this->addEdgeIfKnown($fromId, $attr->name->toString(), 'attribute');
            }
        }
    }

    private function extractInstantiations(Node\Stmt\ClassLike $class, string $fromId): void
    {
        $finder = new NodeFinder();
        $news = $finder->findInstanceOf($class, Node\Expr\New_::class);
        foreach ($news as $new) {
            if ($new->class instanceof Node\Name) {
                $this->addEdgeIfKnown($fromId, $new->class->toString(), 'instantiates');
            }
        }
    }

    private function extractStaticCalls(Node\Stmt\ClassLike $class, string $fromId): void
    {
        $finder = new NodeFinder();
        $calls = $finder->findInstanceOf($class, Node\Expr\StaticCall::class);
        foreach ($calls as $call) {
            if ($call->class instanceof Node\Name) {
                $name = $call->class->toString();
                if (in_array($name, ['self', 'static', 'parent'])) {
                    continue;
                }
                $this->addEdgeIfKnown($fromId, $name, 'static_call');
            }
        }

        $fetches = $finder->findInstanceOf($class, Node\Expr\ClassConstFetch::class);
        foreach ($fetches as $fetch) {
            if ($fetch->class instanceof Node\Name) {
                $name = $fetch->class->toString();
                if (in_array($name, ['self', 'static', 'parent'])) {
                    continue;
                }
                $this->addEdgeIfKnown($fromId, $name, 'const_fetch');
            }
        }
    }

    private function extractTypeHints(Node\Stmt\ClassLike $class, string $fromId): void
    {
        $finder  = new NodeFinder();
        $methods = $finder->findInstanceOf($class, Node\Stmt\ClassMethod::class);

        foreach ($methods as $method) {
            foreach ($method->params as $param) {
                $this->addTypeEdge($fromId, $param->type, 'type_hint');
            }
            $this->addTypeEdge($fromId, $method->returnType, 'returns');
        }
    }

    private function addTypeEdge(string $fromId, $type, string $edgeType): void
    {
        if ($type === null) {
            return;
        }

        if ($type instanceof Node\NullableType) {
            $this->addTypeEdge($fromId, $type->type, $edgeType);
            return;
        }

        if ($type instanceof Node\UnionType || $type instanceof Node\IntersectionType) {
            foreach ($type->types as $subType) {
                $this->addTypeEdge($fromId, $subType, $edgeType);
            }
            return;
        }

        if ($type instanceof Node\Name) {
            $name = $type->toString();
            if (!in_array($name, ['self', 'static', 'parent', 'array', 'string', 'int', 'bool', 'float', 'void', 'mixed', 'object', 'callable', 'iterable', 'never', 'null', 'true', 'false'])) {
                $this->addEdgeIfKnown($fromId, $name, $edgeType);
            }
        }
    }

    private function addEdgeIfKnown(string $fromId, string $targetFqn, string $type): void
    {
        if (!isset($this->classToNodeId[$targetFqn])) {
            return;
        }

        $toId = $this->classToNodeId[$targetFqn];
        if ($toId === $fromId) {
            return;
        }

        foreach ($this->graph->getOutgoingEdges($fromId) as $existing) {
            if ($existing['to'] === $toId && $existing['type'] === $type) {
                return;
            }
        }

        $this->graph->addEdge($fromId, $toId, $type);
    }

    private function parseFile(string $file): ?array
    {
        try {
            $code = file_get_contents($file);
            $parser = (new ParserFactory())->createForHostVersion();
            $ast = $parser->parse($code);

            if ($ast === null) {
                return null;
            }

            $traverser = new NodeTraverser();
            $traverser->addVisitor(new NameResolver());
            return $traverser->traverse($ast);
        } catch (\Throwable $e) {
            $this->errors[] = ['file' => $this->relativePath($file), 'message' => $e->getMessage()];
            return null;
        }
    }

    private function detectType(Node\Stmt\ClassLike $class): string
    {
        if ($class instanceof Node\Stmt\Interface_) {
            return 'interface';
        }
        if ($class instanceof Node\Stmt\Trait_) {
            return 'trait';
        }
        if ($class instanceof Node\Stmt\Enum_) {
            return 'enum';
        }

        if ($class instanceof Node\Stmt\Class_) {
            $extends = $class->extends?->toString();
            $name    = $class->name?->toString() ?? '';

            if ($class->isAbstract()) {
                return 'abstract-class';
            }
            if ($extends === 'CoreComponent' || str_ends_with((string)$extends, '\\CoreComponent')) {
                return 'component';
            }
            if ($extends === 'Model' || str_ends_with((string)$extends, '\\Model')) {
                return 'model';
            }
            if (str_ends_with((string)$extends, 'CoreController') || str_ends_with($name, 'Controller')) {
                return 'controller';
            }
            if (str_ends_with((string)$extends, 'CoreCommand') || str_ends_with($name, 'Command')) {
                return 'command';
            }
            if (str_ends_with($name, 'Component')) {
                return 'component';
            }
        }

        return 'class';
    }

    private function detectLayer(string $relativePath): string
    {
        $relativePath = str_replace('\\', '/', $relativePath);

        if (str_starts_with($relativePath, 'Core/Components/')) return 'core-components';
        if (str_starts_with($relativePath, 'Core/Commands/'))   return 'core-commands';
        if (str_starts_with($relativePath, 'Core/Routing/'))    return 'core-routing';
        if (str_starts_with($relativePath, 'Core/Controllers/')) return 'core-controllers';
        if (str_starts_with($relativePath, 'Core/Attributes/'))  return 'core-attributes';
        if (str_starts_with($relativePath, 'Core/Contracts/'))   return 'core-contracts';
        if (str_starts_with($relativePath, 'Core/Traits/'))      return 'core-traits';
        if (str_starts_with($relativePath, 'Core/Services/'))    return 'core-services';
        if (str_starts_with($relativePath, 'Core/Registry/'))    return 'core-registry';
        if (str_starts_with($relativePath, 'Core/'))             return 'core';
        if (str_starts_with($relativePath, 'App/Controllers/'))  return 'app-controllers';
        if (str_starts_with($relativePath, 'App/Models/'))       return 'app-models';
        if (str_starts_with($relativePath, 'App/'))              return 'app';
        if (str_starts_with($relativePath, 'components/Core/'))   return 'components-core';
        if (str_starts_with($relativePath, 'components/App/'))    return 'components-app';
        if (str_starts_with($relativePath, 'components/shared/')) return 'components-shared';
        if (str_starts_with($relativePath, 'components/'))        return 'components';
        if (str_starts_with($relativePath, 'Routes/'))            return 'routes';

        return 'other';
    }

    private function extractClassAttributes(Node\Stmt\ClassLike $class): array
    {
        $names = [];
        foreach ($class->attrGroups as $group) {
            foreach ($group->attrs as $attr) {
                $names[] = $attr->name->toString();
            }
        }
        return $names;
    }

    private function classToId(string $fqn): string
    {
        $short = substr($fqn, strrpos($fqn, '\\') + 1);
        return $this->kebab($short);
    }

    private function kebab(string $str): string
    {
        $str = preg_replace('/([a-z])([A-Z])/', '$1-$2', $str);
        return strtolower($str);
    }

    private function relativePath(string $file): string
    {
        $rel = str_replace($this->projectRoot . DIRECTORY_SEPARATOR, '', $file);
        return str_replace('\\', '/', $rel);
    }
}
