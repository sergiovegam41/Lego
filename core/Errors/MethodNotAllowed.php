<?php

declare(strict_types=1);

namespace Lego\Errors;

/** 405: the path exists but not for this method. Carries the Allow header. */
final class MethodNotAllowed extends HttpError
{
    /** @param list<string> $allowed */
    public function __construct(public readonly array $allowed)
    {
        parent::__construct(null, ['allow' => implode(', ', $allowed)]);
    }

    public function status(): int
    {
        return 405;
    }

    public function errorCode(): string
    {
        return 'method_not_allowed';
    }

    protected function defaultMessage(): string
    {
        return 'Método no permitido.';
    }
}
