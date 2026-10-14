<?php

declare(strict_types=1);

namespace Lego\Errors;

/**
 * 409: the input is valid but the action cannot happen in the current state.
 * Thrown by action handlers with a stable code and a message for the user:
 *
 *     throw new BusinessRuleViolation('client_blocked', 'El cliente ya está bloqueado.');
 */
final class BusinessRuleViolation extends HttpError
{
    public function __construct(private readonly string $reason, string $message)
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return 409;
    }

    public function errorCode(): string
    {
        return $this->reason;
    }

    protected function defaultMessage(): string
    {
        return 'No se puede completar la acción.';
    }
}
