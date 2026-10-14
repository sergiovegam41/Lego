<?php

declare(strict_types=1);

namespace Lego\Auth;

use Lego\Errors\HttpError;

/** Wrong email, wrong password or inactive user: the same message for all three. */
final class InvalidCredentials extends HttpError
{
    public function status(): int
    {
        return 401;
    }

    public function errorCode(): string
    {
        return 'invalid_credentials';
    }

    protected function defaultMessage(): string
    {
        return 'Correo o contraseña incorrectos.';
    }
}
