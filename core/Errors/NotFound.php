<?php

declare(strict_types=1);

namespace Lego\Errors;

/** 404: no route, action, record or asset matches the request. */
final class NotFound extends HttpError
{
    public function status(): int
    {
        return 404;
    }

    public function errorCode(): string
    {
        return 'not_found';
    }

    protected function defaultMessage(): string
    {
        return 'No encontramos lo que buscas.';
    }
}
