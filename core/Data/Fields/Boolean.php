<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

/** Yes/no. Empty input means false, so it is never "missing". */
final class Boolean extends Field
{
    private const TRUE = ['1', 'true', 'on', 'yes', 'si', 'sí'];

    private const FALSE = ['0', 'false', 'off', 'no'];

    public function type(): string
    {
        return 'boolean';
    }

    public function normalize(mixed $raw): mixed
    {
        if ($raw === null || $raw === '') {
            return $this->default ?? false;
        }

        return $this->cast($raw);
    }

    protected function cast(mixed $raw): mixed
    {
        if (is_bool($raw)) {
            return $raw;
        }
        if ($raw === 1 || $raw === 0) {
            return $raw === 1;
        }

        $text = is_string($raw) ? mb_strtolower(trim($raw)) : null;

        return match (true) {
            in_array($text, self::TRUE, true) => true,
            in_array($text, self::FALSE, true) => false,
            default => throw $this->invalid('Debe ser sí o no.'),
        };
    }

    public function display(mixed $value): string
    {
        return $value ? 'Sí' : 'No';
    }

    public function formValue(mixed $value): string
    {
        return $value ? '1' : '0';
    }

    protected function valueSchema(): array
    {
        return ['type' => 'boolean'];
    }
}
