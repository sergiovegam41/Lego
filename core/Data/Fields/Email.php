<?php

declare(strict_types=1);

namespace Lego\Data\Fields;

/** Email address, stored lowercase. */
final class Email extends Text
{
    public function type(): string
    {
        return 'email';
    }

    protected function cast(mixed $raw): mixed
    {
        return mb_strtolower((string) parent::cast($raw));
    }

    public function check(mixed $value): array
    {
        $errors = parent::check($value);
        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = ['rule' => 'email', 'message' => 'Introduce un correo válido.'];
        }

        return $errors;
    }

    protected function valueSchema(): array
    {
        return [...parent::valueSchema(), 'format' => 'email'];
    }
}
