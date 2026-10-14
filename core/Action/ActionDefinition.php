<?php

declare(strict_types=1);

namespace Lego\Action;

use Closure;

/**
 * One action of a module, as every surface sees it: the UI button, the JSON
 * endpoint, the CLI and, later, AI tools all read this same definition.
 *
 * - prepare(input, record) validates and returns the clean input.
 * - run(clean, record) performs the action and returns its data.
 * The executor runs them with authorization, confirmation and audit around.
 */
final readonly class ActionDefinition
{
    public const COLLECTION = 'collection';

    public const RECORD = 'record';

    /**
     * @param array<string, mixed> $inputSchema JSON Schema of the input object.
     * @param Closure(array<string, mixed>, ?\Lego\Data\Record): array<string, mixed> $prepare
     * @param Closure(array<string, mixed>, ?\Lego\Data\Record): mixed $run
     */
    public function __construct(
        public string $name,
        public string $module,
        public string $operation,
        public string $label,
        public string $description,
        public string $permission,
        public string $scope,
        public array $inputSchema,
        public Closure $prepare,
        public Closure $run,
        public bool $readOnly = false,
        public bool $destructive = false,
        public bool $idempotent = false,
        public bool $confirm = false,
    ) {
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
            'input_schema' => $this->inputSchema,
            'annotations' => [
                'read_only' => $this->readOnly,
                'destructive' => $this->destructive,
                'idempotent' => $this->idempotent,
                'confirm' => $this->confirm,
            ],
        ];
    }
}
