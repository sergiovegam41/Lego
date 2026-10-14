<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

/** Integer or decimal number with optional bounds. */
final class Number extends Field
{
    private bool $integer = false;

    private int|float|null $min = null;

    private int|float|null $max = null;

    public function type(): string
    {
        return 'number';
    }

    public function integer(bool $integer = true): self
    {
        $this->integer = $integer;

        return $this;
    }

    public function isInteger(): bool
    {
        return $this->integer;
    }

    public function min(int|float $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function max(int|float $max): self
    {
        $this->max = $max;

        return $this;
    }

    protected function cast(mixed $raw): mixed
    {
        if (is_bool($raw) || !is_numeric(is_string($raw) ? trim($raw) : $raw)) {
            throw $this->invalid($this->integer ? 'Debe ser un número entero.' : 'Debe ser un número.');
        }

        $number = $raw + 0;
        if ($this->integer) {
            if (!is_int($number) && floor((float) $number) !== (float) $number) {
                throw $this->invalid('Debe ser un número entero.');
            }

            return (int) $number;
        }

        return $number;
    }

    public function check(mixed $value): array
    {
        $errors = [];
        if ($this->min !== null && $value < $this->min) {
            $errors[] = ['rule' => 'min', 'params' => ['min' => $this->min], 'message' => "{$this->label} no puede ser menor que {$this->min}."];
        }
        if ($this->max !== null && $value > $this->max) {
            $errors[] = ['rule' => 'max', 'params' => ['max' => $this->max], 'message' => "{$this->label} no puede ser mayor que {$this->max}."];
        }

        return $errors;
    }

    protected function valueSchema(): array
    {
        return array_filter(
            ['type' => $this->integer ? 'integer' : 'number', 'minimum' => $this->min, 'maximum' => $this->max],
            static fn (mixed $v): bool => $v !== null,
        );
    }
}
