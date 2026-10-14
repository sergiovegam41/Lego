<?php

declare(strict_types=1);

namespace Lego\Errors;

/**
 * 422: the input broke one or more field rules. Only the validator throws it.
 *
 * `fields` maps each field name to a list of {rule, message, params?}.
 */
final class ValidationFailed extends HttpError
{
    /** @param array<string, list<array{rule: string, message: string, params?: array<string, mixed>}>> $fields */
    public function __construct(public readonly array $fields)
    {
        $count = count($fields);
        parent::__construct($count === 1 ? 'Revisa el campo marcado.' : "Revisa los {$count} campos marcados.");
    }

    public function status(): int
    {
        return 422;
    }

    public function errorCode(): string
    {
        return 'validation_failed';
    }

    protected function defaultMessage(): string
    {
        return 'Revisa los campos marcados.';
    }

    public function details(): array
    {
        return ['fields' => $this->fields];
    }
}
