<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

use InvalidArgumentException;

/**
 * One field of a record: the single source for validation, table columns,
 * form inputs, search and the JSON Schema of the module's actions.
 *
 *     Text::make('name', 'Nombre')->required()->max(120)->searchable()->sortable()
 *
 * normalize() turns raw input (form strings or JSON values) into the stored
 * value, or null when empty; check() returns the rule violations of a
 * normalized value.
 */
abstract class Field
{
    protected bool $required = false;

    protected bool $unique = false;

    protected bool $searchable = false;

    protected bool $sortable = false;

    protected bool $inTable = true;

    protected ?string $help = null;

    final public function __construct(public readonly string $name, public readonly string $label)
    {
        if (preg_match('/^[a-z][a-z0-9_]*$/', $name) !== 1) {
            throw new InvalidArgumentException("Invalid field name \"{$name}\"; use snake_case.");
        }
    }

    public static function make(string $name, string $label): static
    {
        return new static($name, $label);
    }

    /** Short machine name of the type: text, textarea, email, number, money, boolean, date, select. */
    abstract public function type(): string;

    /**
     * Converts a non-empty raw value into the stored value.
     *
     * @throws InvalidValue
     */
    abstract protected function cast(mixed $raw): mixed;

    /** @return array<string, mixed> JSON Schema of a non-null value. */
    abstract protected function valueSchema(): array;

    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    public function unique(bool $unique = true): static
    {
        $this->unique = $unique;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function hideInTable(): static
    {
        $this->inTable = false;

        return $this;
    }

    public function help(string $help): static
    {
        $this->help = $help;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isInTable(): bool
    {
        return $this->inTable;
    }

    public function helpText(): ?string
    {
        return $this->help;
    }

    /**
     * Raw input to stored value; null for empty input.
     *
     * @throws InvalidValue
     */
    public function normalize(mixed $raw): mixed
    {
        if ($raw === null || (is_string($raw) && trim($raw) === '')) {
            return null;
        }

        return $this->cast($raw);
    }

    /** @return list<array{rule: string, message: string, params?: array<string, mixed>}> */
    public function check(mixed $value): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    public function jsonSchema(): array
    {
        $schema = $this->valueSchema();
        if (!$this->required && isset($schema['type'])) {
            $schema['type'] = [...(array) $schema['type'], 'null'];
        }

        return array_filter(
            ['title' => $this->label, 'description' => $this->help, ...$schema],
            static fn (mixed $v): bool => $v !== null,
        );
    }

    /** Human-readable value for tables and messages. */
    public function display(mixed $value): string
    {
        return $value === null ? '' : (string) $value;
    }

    /** Value as it goes into a form input. */
    public function formValue(mixed $value): string
    {
        return $value === null ? '' : (string) $value;
    }

    protected function invalid(string $message): InvalidValue
    {
        return new InvalidValue($message);
    }
}
