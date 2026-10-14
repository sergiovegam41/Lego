<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

/** One value out of a fixed list: ->options(['basic' => 'Básico', 'gold' => 'Oro']). */
final class Select extends Field
{
    /** @var array<string, string> value => label */
    private array $options = [];

    public function type(): string
    {
        return 'select';
    }

    /** @param array<string, string> $options value => label */
    public function options(array $options): self
    {
        $this->options = array_combine(array_map('strval', array_keys($options)), array_values($options));

        return $this;
    }

    /** @return array<string, string> */
    public function choices(): array
    {
        return $this->options;
    }

    protected function cast(mixed $raw): mixed
    {
        if (!is_scalar($raw)) {
            throw $this->invalid('Debe ser una de las opciones.');
        }

        return (string) $raw;
    }

    public function check(mixed $value): array
    {
        return array_key_exists((string) $value, $this->options)
            ? []
            : [['rule' => 'in', 'params' => ['options' => array_keys($this->options)], 'message' => 'Elige una de las opciones.']];
    }

    public function display(mixed $value): string
    {
        return $value === null ? '' : ($this->options[(string) $value] ?? (string) $value);
    }

    protected function valueSchema(): array
    {
        return ['type' => 'string', 'enum' => array_keys($this->options)];
    }
}
