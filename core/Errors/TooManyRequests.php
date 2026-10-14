<?php

declare(strict_types=1);

namespace Lego\Errors;

/** 429: too many attempts in a short time, such as failed logins. */
final class TooManyRequests extends HttpError
{
    public function status(): int
    {
        return 429;
    }

    public function errorCode(): string
    {
        return 'too_many_requests';
    }

    protected function defaultMessage(): string
    {
        return 'Demasiados intentos. Espera unos minutos y vuelve a intentarlo.';
    }
}
