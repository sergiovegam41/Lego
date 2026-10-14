<?php

declare(strict_types=1);

namespace Lego\Action;

use Closure;

/**
 * One action of a module, as every surface sees it: the UI button, the JSON
 * endpoint, the CLI and, later, AI tools all read this same definition.
 *
 * - prepare(input, record) validates and returns the clean input.
 * - run(clean, record, user) performs the action as that user and returns its data.
 * The executor runs them with authorization, confirmation and audit around.
 */
final class ActionDefinition
{
    public const COLLECTION = 'collection';

    public const RECORD = 'record';

    /** @var array<string, mixed>|null */
    private ?array $schema = null;

    /**
     * @param Closure(): array<string, mixed>|array<string, mixed> $inputSchema JSON Schema of the
     *        input object, or a closure that builds it the first time it is needed (options of
     *        a field may come from the database, which must not be queried at boot).
     * @param Closure(array<string, mixed>, ?\Lego\Data\Record): array<string, mixed> $prepare
     * @param Closure(array<string, mixed>, ?\Lego\Data\Record, \Lego\Auth\Identity): mixed $run
     */
    public function __construct(
        public readonly string $name,
        public readonly string $module,
        public readonly string $operation,
        public readonly string $label,
        public readonly string $description,
        public readonly string $permission,
        public readonly string $scope,
        private readonly Closure|array $inputSchema,
        public readonly Closure $prepare,
        public readonly Closure $run,
        public readonly bool $readOnly = false,
        public readonly bool $destructive = false,
        public readonly bool $idempotent = false,
        public readonly bool $confirm = false,
    ) {
    }

    /** @return array<string, mixed> JSON Schema of the input object. */
    public function inputSchema(): array
    {
        return $this->schema ??= $this->inputSchema instanceof Closure ? ($this->inputSchema)() : $this->inputSchema;
    }

    public function needsRecord(): bool
    {
        return $this->scope === self::RECORD;
    }

    /** @return array<string, mixed> Public description, without handlers, for catalogs and agents. */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'module' => $this->module,
            'label' => $this->label,
            'description' => $this->description,
            'permission' => $this->permission,
            'scope' => $this->scope,
            'input_schema' => $this->inputSchema(),
            'annotations' => [
                'read_only' => $this->readOnly,
                'destructive' => $this->destructive,
                'idempotent' => $this->idempotent,
                'confirm' => $this->confirm,
            ],
        ];
    }
}
