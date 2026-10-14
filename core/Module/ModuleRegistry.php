<?php

declare(strict_types=1);

namespace Lego\Module;

use Illuminate\Support\Str;
use Lego\Data\Record;
use Lego\View\Component;
use LogicException;
use ReflectionClass;

/**
 * Discovers modules: every folder <root>/<Name>/ whose top-level PHP files
 * contain exactly one class with #[Module]. No registration anywhere else.
 *
 * There are two roots: core/Modules (Users, Roles) and the application's
 * modules/ folder. Both follow the same convention; names must not repeat.
 */
final class ModuleRegistry
{
    /** Names that would collide with core routes. */
    public const RESERVED = ['api', 'component', 'login', 'logout', 'health', 'lego', 'modules'];

    /** @var array<string, ModuleDefinition> */
    private array $modules = [];

    /** @param array<string, string> $roots folder => PSR-4 namespace, e.g. ['/app/modules' => 'Modules\\'] */
    public function __construct(array $roots)
    {
        foreach ($roots as $path => $namespace) {
            foreach (glob(rtrim($path, '/\\') . '/*', GLOB_ONLYDIR) ?: [] as $folder) {
                $definition = $this->inspect($folder, $namespace);
                if ($definition === null) {
                    continue;
                }
                if (isset($this->modules[$definition->name])) {
                    throw new LogicException("Module \"{$definition->name}\" is defined twice: {$this->modules[$definition->name]->class} and {$definition->class}.");
                }
                $this->modules[$definition->name] = $definition;
            }
        }

        uasort($this->modules, static fn (ModuleDefinition $a, ModuleDefinition $b): int => [$a->order, $a->label] <=> [$b->order, $b->label]);
    }

    /** @return array<string, ModuleDefinition> ordered for menus */
    public function all(): array
    {
        return $this->modules;
    }

    public function get(string $name): ?ModuleDefinition
    {
        return $this->modules[$name] ?? null;
    }

    private function inspect(string $folder, string $namespace): ?ModuleDefinition
    {
        $folderName = basename($folder);
        if (preg_match('/^[A-Z][A-Za-z0-9]*$/', $folderName) !== 1) {
            return null;
        }

        $found = [];
        foreach (glob($folder . '/*.php') ?: [] as $file) {
            $class = $namespace . $folderName . '\\' . basename($file, '.php');
            if (!class_exists($class)) {
                continue;
            }
            $attributes = (new ReflectionClass($class))->getAttributes(Module::class);
            if ($attributes !== []) {
                $found[] = [$class, $attributes[0]->newInstance()];
            }
        }

        if ($found === []) {
            return null;
        }
        if (count($found) > 1) {
            throw new LogicException("Module folder {$folderName} has more than one #[Module] class.");
        }

        [[$class, $module]] = $found;
        $name = Str::snake($folderName);
        $this->assertValid($name, $class, $module);

        return new ModuleDefinition(
            name: $name,
            label: $module->label,
            icon: $module->icon,
            order: $module->order,
            description: $module->description,
            class: $class,
            path: $folder,
            operations: is_subclass_of($class, Record::class) ? array_values($module->operations) : [],
            agentWrites: $module->agentWrites,
        );
    }

    private function assertValid(string $name, string $class, Module $module): void
    {
        if (in_array($name, self::RESERVED, true)) {
            throw new LogicException("Module name \"{$name}\" is reserved.");
        }
        if (!is_subclass_of($class, Record::class) && !is_subclass_of($class, Component::class)) {
            throw new LogicException("#[Module] class {$class} must extend " . Record::class . ' or ' . Component::class . '.');
        }
        if (array_intersect(['update', 'delete'], $module->operations) !== [] && !in_array('get', $module->operations, true)) {
            throw new LogicException("Module {$name} needs the \"get\" operation to update or delete records.");
        }
        $unknown = array_diff($module->operations, Module::OPERATIONS);
        if ($unknown !== []) {
            throw new LogicException("Module {$name} declares unknown operations: " . implode(', ', $unknown));
        }
    }
}
