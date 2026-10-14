<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

use Closure;

/**
 * Several values out of a list, stored as a JSON array (cast the model
 * attribute to "array"). Rendered as checkboxes; sent as a JSON array or as
 * repeated form fields `name[]`.
 *
 *     Checklist::make('permissions', 'Permisos')->optionsFrom(fn () => Permissions::catalog())
 */
final class Checklist extends Field
{
    /** @var array<string, string>|null */
    private ?array $options = [];

    private ?Closure $provider = null;

    public function type(): string
    {
        return 'checklist';
    }

    /** @param array<string, string> $options value => label */
    public function options(array $options): self
    {
        $this->options = $options;
        $this->provider = null;

        return $this;
    }

    /** @param Closure(): array<string, string> $provider */
    public function optionsFrom(Closure $provider): self
    {
        $this->provider = $provider;
        $this->options = null;

        return $this;
    }

    /** @return array<string, string> */
    public function choices(): array
    {
        return $this->options ??= ($this->provider)();
    }

    public function normalize(mixed $raw): mixed
    {
        if ($raw === null || $raw === '') {
            return $this->default ?? [];
        }

        return $this->cast($raw);
    }

    protected function cast(mixed $raw): mixed
    {
        if (!is_array($raw) || !array_is_list($raw)) {
            throw $this->invalid('Debe ser una lista.');
        }

        $values = [];
        foreach ($raw as $item) {
            if (!is_scalar($item) || is_bool($item)) {
                throw $this->invalid('Cada elemento debe ser un texto.');
            }
            $item = trim((string) $item);
            if ($item !== '' && !in_array($item, $values, true)) {
                $values[] = $item;
            }
        }
        sort($values);

        return $values;
    }

    public function check(mixed $value): array
    {
        $unknown = array_values(array_diff($value, array_keys($this->choices())));

        return $unknown === []
            ? []
            : [['rule' => 'in', 'params' => ['unknown' => $unknown], 'message' => 'Opciones no válidas: ' . implode(', ', $unknown) . '.']];
    }

    public function display(mixed $value): string
    {
        $count = is_array($value) ? count($value) : 0;

        return $count === 1 ? '1 elegido' : "{$count} elegidos";
    }

    /** @return list<string> */
    public function formValue(mixed $value): string|array
    {
        return is_array($value) ? array_values(array_map('strval', $value)) : [];
    }

    protected function valueSchema(): array
    {
        return ['type' => 'array', 'items' => ['type' => 'string', 'enum' => array_keys($this->choices())], 'uniqueItems' => true];
    }
}
