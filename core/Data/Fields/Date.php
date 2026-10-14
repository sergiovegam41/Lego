<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

use DateTimeImmutable;
use DateTimeInterface;

/** Calendar date, exchanged as YYYY-MM-DD. */
final class Date extends Field
{
    public function type(): string
    {
        return 'date';
    }

    protected function cast(mixed $raw): mixed
    {
        if ($raw instanceof DateTimeInterface) {
            return $raw->format('Y-m-d');
        }

        $text = is_string($raw) ? trim($raw) : '';
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $text);
        if ($date === false || $date->format('Y-m-d') !== $text) {
            throw $this->invalid('Debe ser una fecha con formato AAAA-MM-DD.');
        }

        return $text;
    }

    public function display(mixed $value): string
    {
        $text = $this->formValue($value);

        return $text === '' ? '' : implode('/', array_reverse(explode('-', $text)));
    }

    public function formValue(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return $value === null ? '' : substr((string) $value, 0, 10);
    }

    protected function valueSchema(): array
    {
        return ['type' => 'string', 'format' => 'date'];
    }
}
