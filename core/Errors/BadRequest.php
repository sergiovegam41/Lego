<?php

declare(strict_types=1);

namespace Lego\Errors;

/** 400: malformed request, such as invalid JSON or an unexpected content type. */
final class BadRequest extends HttpError
{
    public function status(): int
    {
        return 400;
    }

    public function errorCode(): string
    {
        return 'bad_request';
    }

    protected function defaultMessage(): string
    {
        return 'La petición no es válida.';
    }
}
