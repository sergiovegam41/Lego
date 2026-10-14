<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

/**
 * Amount with two decimals, stored as a decimal string ("1500.00") so it never
 * goes through floating point. Accepts numbers and numeric strings with a dot
 * or a comma as the decimal separator.
 */
final class Money extends Field
{
    private ?string $min = null;

    private ?string $max = null;

    public function type(): string
    {
        return 'money';
    }

    public function min(int|float|string $min): self
    {
        $this->min = self::format((string) $min);

        return $this;
    }

    public function max(int|float|string $max): self
    {
        $this->max = self::format((string) $max);

        return $this;
    }

    protected function cast(mixed $raw): mixed
    {
        if (is_bool($raw) || !is_scalar($raw)) {
            throw $this->invalid('Debe ser un importe.');
        }

        $text = str_replace(',', '.', trim((string) $raw));
        if (preg_match('/^-?\d+(\.\d{1,2})?$/', $text) !== 1) {
            throw $this->invalid('Debe ser un importe con hasta dos decimales.');
        }

        return self::format($text);
    }

    public function check(mixed $value): array
    {
        $errors = [];
        if ($this->min !== null && self::compare($value, $this->min) < 0) {
            $errors[] = ['rule' => 'min', 'params' => ['min' => $this->min], 'message' => "{$this->label} no puede ser menor que {$this->min}."];
        }
        if ($this->max !== null && self::compare($value, $this->max) > 0) {
            $errors[] = ['rule' => 'max', 'params' => ['max' => $this->max], 'message' => "{$this->label} no puede ser mayor que {$this->max}."];
        }

        return $errors;
    }

    public function display(mixed $value): string
    {
        return $value === null ? '' : number_format((float) $value, 2, ',', '.');
    }

    protected function valueSchema(): array
    {
        return ['type' => ['number', 'string'], 'pattern' => '^-?\d+([.,]\d{1,2})?$'];
    }

    private static function format(string $amount): string
    {
        $negative = str_starts_with($amount, '-');
        [$units, $cents] = array_pad(explode('.', ltrim($amount, '-')), 2, '');
        $units = ltrim($units, '0') === '' ? '0' : ltrim($units, '0');

        return ($negative && ($units !== '0' || trim($cents, '0') !== '') ? '-' : '') . $units . '.' . str_pad(substr($cents, 0, 2), 2, '0');
    }

    /** Compares two normalized amounts in cents, without floats. */
    private static function compare(string $a, string $b): int
    {
        return (int) str_replace('.', '', $a) <=> (int) str_replace('.', '', $b);
    }
}
