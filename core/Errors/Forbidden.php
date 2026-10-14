<?php

declare(strict_types=1);

namespace Lego\Errors;

/**
 * 403: signed in without the required permission ("forbidden"), or an unsafe
 * request without a valid CSRF token or from another origin ("csrf_mismatch").
 */
final class Forbidden extends HttpError
{
    public function __construct(?string $message = null, private readonly string $reason = 'forbidden')
    {
        parent::__construct($message);
    }

    public static function csrf(): self
    {
        return new self('La página caducó. Recárgala y vuelve a intentarlo.', 'csrf_mismatch');
    }

    public function status(): int
    {
        return 403;
    }

    public function errorCode(): string
    {
        return $this->reason;
    }

    protected function defaultMessage(): string
    {
        return 'No tienes permiso para hacer esto.';
    }
}
