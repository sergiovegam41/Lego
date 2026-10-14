<?php

declare(strict_types=1);

namespace Lego\Errors;

use ErrorException;
use Lego\Http\Response;
use Lego\Log\Logger;

/**
 * Process-wide safety net, installed once by the entry point.
 *
 * - PHP warnings and notices become ErrorException, so "Undefined array key"
 *   reaches the error renderer instead of passing silently.
 * - A fatal error that kills the request is logged and answered with a bare 500.
 */
final class ErrorHandling
{
    private const FATAL = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];

    public static function register(Logger $logger): void
    {
        set_error_handler(self::convert(...));
        register_shutdown_function(static fn () => self::onShutdown($logger, error_get_last()));
    }

    /**
     * Error handler: throws for every reported severity, ignores silenced ones.
     *
     * @throws ErrorException
     */
    public static function convert(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        if ((error_reporting() & $severity) === 0) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    /** @param array{type: int, message: string, file: string, line: int}|null $error */
    public static function onShutdown(Logger $logger, ?array $error): void
    {
        if ($error === null || !in_array($error['type'], self::FATAL, true)) {
            return;
        }

        $logger->error($error['message'], ['fatal' => true, 'file' => $error['file'] . ':' . $error['line']]);
        if (!headers_sent()) {
            Response::text('Ha ocurrido un error inesperado.', 500)->send();
        }
    }
}
