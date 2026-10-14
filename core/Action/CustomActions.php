<?php

declare(strict_types=1);

namespace Lego\Action;

use Illuminate\Support\Str;
use Lego\Auth\Identity;
use Lego\Data\Record;
use Lego\Errors\ValidationFailed;
use Lego\Module\ModuleDefinition;
use LogicException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Builds actions from the #[Action] methods of a module Record.
 *
 * The method name gives the action name and permission:
 * Client::markVip() in module "clients" -> action "clients_mark_vip",
 * permission "clients.mark_vip". Its scalar parameters become the input.
 */
final class CustomActions
{
    private const TYPES = ['string' => 'string', 'int' => 'integer', 'float' => 'number', 'bool' => 'boolean'];

    /** @return list<ActionDefinition> */
    public function for(ModuleDefinition $module): array
    {
        $actions = [];
        foreach ((new \ReflectionClass($module->class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(Action::class);
            if ($attributes === []) {
                continue;
            }
            $actions[] = $this->build($module, $method, $attributes[0]->newInstance());
        }

        return $actions;
    }

    private function build(ModuleDefinition $module, ReflectionMethod $method, Action $action): ActionDefinition
    {
        $operation = Str::snake($method->getName());
        if (in_array($operation, ['list', 'get', 'create', 'update', 'delete', 'view'], true)) {
            throw new LogicException("#[Action] {$method->class}::{$method->getName()} uses a reserved CRUD name.");
        }
        // People read the label; agents decide from the description (doc/ia.md).
        if (trim($action->description) === '') {
            throw new LogicException("#[Action] {$method->class}::{$method->getName()} needs a description.");
        }
        if ($action->external && !$action->confirm) {
            throw new LogicException("#[Action] {$method->class}::{$method->getName()} is external, so it must also confirm.");
        }

        // A parameter typed Identity receives the acting user; it is not input.
        $actor = null;
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && $type->getName() === Identity::class) {
                $actor = $parameter->getName();
                continue;
            }
            $this->assertSupported($method, $parameter);
            $parameters[] = $parameter;
        }

        $scope = $method->isStatic() ? ActionDefinition::COLLECTION : ActionDefinition::RECORD;
        $name = $method->getName();
        $class = $module->class;
        $withActor = static fn (array $clean, Identity $user): array => $actor === null ? $clean : [...$clean, $actor => $user];

        return new ActionDefinition(
            name: $module->name . '_' . $operation,
            module: $module->name,
            operation: $operation,
            label: $action->label,
            description: $action->description,
            permission: $module->permission($operation),
            scope: $scope,
            inputSchema: $this->schema($parameters, $scope),
            prepare: fn (array $input): array => $this->bind($parameters, $input, $scope),
            run: $method->isStatic()
                ? static fn (array $clean, ?Record $none, Identity $user): mixed => [$class, $name](...$withActor($clean, $user))
                : static fn (array $clean, Record $record, Identity $user): mixed => $record->$name(...$withActor($clean, $user)),
            readOnly: $action->readOnly,
            destructive: $action->destructive,
            idempotent: $action->idempotent,
            confirm: $action->confirm,
            untrustedContent: $action->untrustedContent,
            external: $action->external,
        );
    }

    private function assertSupported(ReflectionMethod $method, ReflectionParameter $parameter): void
    {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionNamedType || !isset(self::TYPES[$type->getName()]) || $parameter->isVariadic()) {
            throw new LogicException(
                "#[Action] {$method->class}::{$method->getName()} parameter \${$parameter->getName()} must be string, int, float or bool.",
            );
        }
    }

    /**
     * @param list<ReflectionParameter> $parameters
     * @return array<string, mixed>
     */
    private function schema(array $parameters, string $scope): array
    {
        $properties = [];
        $required = [];
        if ($scope === ActionDefinition::RECORD) {
            $properties['id'] = ['type' => 'integer', 'minimum' => 1];
            $required[] = 'id';
        }

        foreach ($parameters as $parameter) {
            /** @var ReflectionNamedType $type */
            $type = $parameter->getType();
            $info = $parameter->getAttributes(Param::class)[0] ?? null;
            $param = $info?->newInstance();
            $schema = ['type' => $type->allowsNull() ? [self::TYPES[$type->getName()], 'null'] : self::TYPES[$type->getName()]];
            if ($param !== null) {
                $schema = ['title' => $param->label, ...($param->description !== '' ? ['description' => $param->description] : []), ...$schema];
            }
            if ($parameter->isDefaultValueAvailable()) {
                $schema['default'] = $parameter->getDefaultValue();
            } else {
                $required[] = $parameter->getName();
            }
            $properties[$parameter->getName()] = $schema;
        }

        return array_filter(
            ['type' => 'object', 'properties' => $properties, 'required' => $required, 'additionalProperties' => false],
            static fn (mixed $v): bool => $v !== [],
        );
    }

    /**
     * Validates the input against the method signature and returns named
     * arguments in order.
     *
     * @param list<ReflectionParameter> $parameters
     * @param array<string, mixed>      $input
     * @return array<string, mixed>
     */
    private function bind(array $parameters, array $input, string $scope): array
    {
        $errors = [];
        $known = array_map(static fn (ReflectionParameter $p): string => $p->getName(), $parameters);
        foreach (array_keys($input) as $key) {
            if (!in_array($key, $known, true) && !($scope === ActionDefinition::RECORD && $key === 'id')) {
                $errors[(string) $key][] = ['rule' => 'unknown_field', 'message' => 'Este parámetro no existe.'];
            }
        }

        $args = [];
        foreach ($parameters as $parameter) {
            $name = $parameter->getName();
            /** @var ReflectionNamedType $type */
            $type = $parameter->getType();
            $raw = $input[$name] ?? null;

            if ($raw === null || $raw === '') {
                if ($type->getName() === 'bool' && array_key_exists($name, $input)) {
                    $args[$name] = false;
                } elseif ($parameter->isDefaultValueAvailable()) {
                    $args[$name] = $parameter->getDefaultValue();
                } elseif ($type->allowsNull()) {
                    $args[$name] = null;
                } else {
                    $errors[$name][] = ['rule' => 'required', 'message' => 'Este dato es obligatorio.'];
                }
                continue;
            }

            $value = self::cast($type->getName(), $raw);
            if ($value === null) {
                $errors[$name][] = ['rule' => 'type', 'message' => 'Debe ser de tipo ' . self::TYPES[$type->getName()] . '.'];
                continue;
            }
            $args[$name] = $value;
        }

        if ($errors !== []) {
            throw new ValidationFailed($errors);
        }

        return $args;
    }

    private static function cast(string $type, mixed $raw): string|int|float|bool|null
    {
        return match ($type) {
            'string' => is_scalar($raw) && !is_bool($raw) ? trim((string) $raw) : null,
            'int' => filter_var($raw, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
            'float' => is_bool($raw) ? null : filter_var($raw, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE),
            'bool' => is_bool($raw) ? $raw : filter_var($raw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE),
            default => null,
        };
    }
}
