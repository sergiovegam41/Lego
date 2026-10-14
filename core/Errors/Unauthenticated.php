<?php

declare(strict_types=1);

namespace Lego\Errors;

/** 401: no valid session. Pages redirect to /login; fragments and JSON answer 401. */
final class Unauthenticated extends HttpError
{
    public function status(): int
    {
        return 401;
    }

    public function errorCode(): string
    {
        return 'unauthenticated';
    }

    protected function defaultMessage(): string
    {
        return 'Tu sesión ha caducado. Vuelve a iniciar sesión.';
    }
}
