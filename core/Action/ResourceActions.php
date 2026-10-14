<?php

declare(strict_types=1);

namespace Lego\Action;

use Illuminate\Database\Eloquent\Builder;
use Lego\Data\Record;
use Lego\Data\Validator;
use Lego\Errors\ValidationFailed;
use Lego\Module\ModuleDefinition;

/**
 * The CRUD actions a Record module gets for free:
 *
 * | Action          | Permission       | Notes                          |
 * |-----------------|------------------|--------------------------------|
 * | <module>_list   | <module>.view    | search, sort, pagination       |
 * | <module>_get    | <module>.view    |                                |
 * | <module>_create | <module>.create  |                                |
 * | <module>_update | <module>.update  | partial, idempotent            |
 * | <module>_delete | <module>.delete  | destructive, needs confirmation|
 */
final class ResourceActions
{
    public const PER_PAGE = 20;

    public const MAX_PER_PAGE = 100;

    public function __construct(private readonly Validator $validator)
    {
    }

    /** @return list<ActionDefinition> */
    public function for(ModuleDefinition $module): array
    {
        /** @var class-string<Record> $class */
        $class = $module->class;
        $actions = [];

        if ($module->allows('list')) {
            $actions[] = $this->list($module, $class);
        }
        if ($module->allows('get')) {
            $actions[] = new ActionDefinition(
                name: $module->name . '_get',
                module: $module->name,
                operation: 'get',
                label: 'Ver',
                description: "Devuelve un registro de {$module->label} por su id.",
                permission: $module->permission('view'),
                scope: ActionDefinition::RECORD,
                inputSchema: self::idSchema(),
                prepare: static fn (): array => [],
                run: static fn (array $clean, Record $record): array => $record->toData(),
                readOnly: true,
                idempotent: true,
            );
        }
        if ($module->allows('create')) {
            $actions[] = new ActionDefinition(
                name: $module->name . '_create',
                module: $module->name,
                operation: 'create',
                label: 'Crear',
                description: "Crea un registro en {$module->label}.",
                permission: $module->permission('create'),
                scope: ActionDefinition::COLLECTION,
                inputSchema: self::fieldsSchema($class, requireRequired: true),
                prepare: fn (array $input): array => $this->validator->validate($class, $input, Validator::CREATE),
                run: static function (array $clean) use ($class): array {
                    $record = new $class();
                    $record->forceFill($clean)->save();

                    return $record->refresh()->toData();
                },
            );
        }
        if ($module->allows('update')) {
            $actions[] = new ActionDefinition(
                name: $module->name . '_update',
                module: $module->name,
                operation: 'update',
                label: 'Guardar',
                description: "Modifica los campos indicados de un registro de {$module->label}.",
                permission: $module->permission('update'),
                scope: ActionDefinition::RECORD,
                inputSchema: self::withId(self::fieldsSchema($class, requireRequired: false)),
                prepare: fn (array $input, Record $record): array => $this->validator->validate($class, $input, Validator::UPDATE, $record, ['id']),
                run: static function (array $clean, Record $record): array {
                    $record->forceFill($clean)->save();

                    return $record->refresh()->toData();
                },
                idempotent: true,
            );
        }
        if ($module->allows('delete')) {
            $actions[] = new ActionDefinition(
                name: $module->name . '_delete',
                module: $module->name,
                operation: 'delete',
                label: 'Eliminar',
                description: "Elimina un registro de {$module->label}. Pide confirmación.",
                permission: $module->permission('delete'),
                scope: ActionDefinition::RECORD,
                inputSchema: self::idSchema(),
                prepare: static fn (): array => [],
                run: static function (array $clean, Record $record): array {
                    $id = $record->getKey();
                    $record->delete();

                    return ['id' => $id];
                },
                destructive: true,
                confirm: true,
            );
        }

        return $actions;
    }

    /** @param class-string<Record> $class */
    private function list(ModuleDefinition $module, string $class): ActionDefinition
    {
        $fields = $class::fieldMap();
        $sortable = array_keys(array_filter($fields, static fn ($f): bool => $f->isSortable()));
        $searchable = array_keys(array_filter($fields, static fn ($f): bool => $f->isSearchable()));

        return new ActionDefinition(
            name: $module->name . '_list',
            module: $module->name,
            operation: 'list',
            label: 'Listar',
            description: "Lista registros de {$module->label} con búsqueda, orden y paginación.",
            permission: $module->permission('view'),
            scope: ActionDefinition::COLLECTION,
            inputSchema: [
                'type' => 'object',
                'properties' => array_filter([
                    'q' => $searchable === [] ? null : ['type' => 'string', 'description' => 'Texto a buscar en: ' . implode(', ', $searchable)],
                    'sort' => ['type' => 'string', 'enum' => ['id', ...$sortable]],
                    'dir' => ['type' => 'string', 'enum' => ['asc', 'desc']],
                    'page' => ['type' => 'integer', 'minimum' => 1],
                    'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => self::MAX_PER_PAGE],
                ]),
                'additionalProperties' => false,
            ],
            prepare: static fn (array $input): array => self::listInput($input, $sortable, $searchable !== []),
            run: static function (array $clean) use ($class, $searchable): array {
                $query = $class::query();
                if ($clean['q'] !== '') {
                    $query->where(static function (Builder $where) use ($searchable, $clean): void {
                        foreach ($searchable as $column) {
                            $where->orWhereRaw('CAST(' . $column . ' AS TEXT) ILIKE ?', ['%' . addcslashes($clean['q'], '%_\\') . '%']);
                        }
                    });
                }
                $total = (clone $query)->count();
                $items = $query->orderBy($clean['sort'], $clean['dir'])
                    ->orderBy('id', $clean['dir'])
                    ->forPage($clean['page'], $clean['per_page'])
                    ->get()
                    ->map(static fn (Record $r): array => $r->toData())
                    ->all();

                return [
                    'items' => $items,
                    'meta' => [
                        'page' => $clean['page'],
                        'per_page' => $clean['per_page'],
                        'total' => $total,
                        'pages' => max(1, (int) ceil($total / $clean['per_page'])),
                        'q' => $clean['q'],
                        'sort' => $clean['sort'],
                        'dir' => $clean['dir'],
                    ],
                ];
            },
            readOnly: true,
            idempotent: true,
        );
    }

    /**
     * @param array<string, mixed> $input
     * @param list<string>         $sortable
     * @return array{q: string, sort: string, dir: string, page: int, per_page: int}
     */
    private static function listInput(array $input, array $sortable, bool $searchable): array
    {
        $errors = [];
        foreach (array_keys($input) as $key) {
            if (!in_array($key, ['q', 'sort', 'dir', 'page', 'per_page'], true) || ($key === 'q' && !$searchable)) {
                $errors[(string) $key][] = ['rule' => 'unknown_field', 'message' => 'Este parámetro no existe.'];
            }
        }

        $sort = (string) ($input['sort'] ?? 'id');
        if (!in_array($sort, ['id', ...$sortable], true)) {
            $errors['sort'][] = ['rule' => 'in', 'message' => 'No se puede ordenar por ese campo.'];
        }
        $dir = strtolower((string) ($input['dir'] ?? 'desc'));
        if (!in_array($dir, ['asc', 'desc'], true)) {
            $errors['dir'][] = ['rule' => 'in', 'message' => 'El orden debe ser asc o desc.'];
        }
        $page = filter_var($input['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $perPage = filter_var($input['per_page'] ?? self::PER_PAGE, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => self::MAX_PER_PAGE]]);
        if ($page === false) {
            $errors['page'][] = ['rule' => 'min', 'message' => 'La página debe ser un entero mayor que 0.'];
        }
        if ($perPage === false) {
            $errors['per_page'][] = ['rule' => 'max', 'message' => 'Entre 1 y ' . self::MAX_PER_PAGE . ' por página.'];
        }

        if ($errors !== []) {
            throw new ValidationFailed($errors);
        }

        $q = $input['q'] ?? '';

        return ['q' => is_scalar($q) ? trim((string) $q) : '', 'sort' => $sort, 'dir' => $dir, 'page' => (int) $page, 'per_page' => (int) $perPage];
    }

    /**
     * @param class-string<Record> $class
     * @return array<string, mixed>
     */
    private static function fieldsSchema(string $class, bool $requireRequired): array
    {
        $properties = [];
        $required = [];
        foreach ($class::fields() as $field) {
            $properties[$field->name] = $field->jsonSchema();
            if ($requireRequired && $field->isRequired()) {
                $required[] = $field->name;
            }
        }

        return array_filter(
            ['type' => 'object', 'properties' => $properties, 'required' => $required, 'additionalProperties' => false],
            static fn (mixed $v): bool => $v !== [],
        );
    }

    /** @return array<string, mixed> */
    private static function idSchema(): array
    {
        return ['type' => 'object', 'properties' => ['id' => ['type' => 'integer', 'minimum' => 1]], 'required' => ['id'], 'additionalProperties' => false];
    }

    /**
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    private static function withId(array $schema): array
    {
        $schema['properties'] = ['id' => ['type' => 'integer', 'minimum' => 1], ...($schema['properties'] ?? [])];
        $schema['required'] = ['id', ...($schema['required'] ?? [])];

        return $schema;
    }
}
