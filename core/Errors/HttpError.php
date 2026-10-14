<?php

declare(strict_types=1);

namespace Lego\Errors;

use RuntimeException;

/**
 * An error that maps directly to an HTTP status.
 *
 * Its message is safe to show to the user: it never contains input values or
 * exception details. Any other Throwable is reported as a generic 500.
 */
abstract class HttpError extends RuntimeException
{
    /** @param array<string, string> $headers */
    public function __construct(?string $message = null, private readonly array $headers = [])
    {
        parent::__construct($message ?? $this->defaultMessage());
    }

    abstract public function status(): int;

    /** Stable machine code in snake_case, the only thing clients compare. */
    abstract public function errorCode(): string;

    abstract protected function defaultMessage(): string;

    /** @return array<string, string> */
    public function headers(): array
    {
        return $this->headers;
    }
}
