<?php

declare(strict_types=1);

namespace Lego\Module;

use Lego\Data\Record;

/** Everything Lego deduces about one module folder. */
final readonly class ModuleDefinition
{
    /**
     * @param class-string $class
     * @param list<string> $operations
     */
    public function __construct(
        public string $name,
        public string $label,
        public string $icon,
        public int $order,
        public string $description,
        public string $class,
        public string $path,
        public array $operations,
        public bool $agentWrites = false,
    ) {
    }

    /** True when the module class is a Record (CRUD module). */
    public function isRecord(): bool
    {
        return is_subclass_of($this->class, Record::class);
    }

    public function allows(string $operation): bool
    {
        return in_array($operation, $this->operations, true);
    }

    /** URL segment: sales_orders -> sales-orders */
    public function slug(): string
    {
        return str_replace('_', '-', $this->name);
    }

    public function url(): string
    {
        return '/' . $this->slug();
    }

    public function permission(string $operation): string
    {
        return $this->name . '.' . $operation;
    }
}
