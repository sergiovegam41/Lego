<?php

declare(strict_types=1);

namespace Lego\Http;

use Lego\Errors\HttpError;
use Lego\Log\Logger;
use Lego\View\Document;
use Throwable;

/**
 * Converts any exception into a response shaped for the request's layer.
 *
 * - Api: the JSON envelope {"status": "error", "error": {"code", "message"}, "trace_id"}.
 * - Component: a .lego-error fragment with data-status, data-code and data-trace-id.
 * - Page: a minimal HTML document with the same fragment.
 *
 * HttpError messages are safe to show. Any other Throwable is logged and
 * becomes a generic 500 whose details only appear when debug is on.
 */
final class ErrorRenderer
{
    public function __construct(
        private readonly Logger $logger,
        private readonly bool $debug = false,
    ) {
    }

    public function render(Throwable $e, Request $request, string $traceId): Response
    {
        if ($e instanceof HttpError) {
            [$status, $code, $message, $headers] = [$e->status(), $e->errorCode(), $e->getMessage(), $e->headers()];
        } else {
            $this->logger->exception($e, ['trace_id' => $traceId, 'method' => $request->method, 'path' => $request->path]);
            $status = 500;
            $code = 'internal_error';
            $message = "Ha ocurrido un error inesperado. Código de referencia: {$traceId}.";
            $headers = [];
        }

        $debug = $this->debug && !$e instanceof HttpError ? self::debugInfo($e) : null;

        $response = match ($request->layer()) {
            Layer::Api => Response::json(array_filter([
                'status' => 'error',
                'error' => ['code' => $code, 'message' => $message],
                'trace_id' => $traceId,
                'debug' => $debug,
            ], static fn (mixed $value): bool => $value !== null), $status),
            Layer::Component => Response::html(self::fragment($status, $code, $message, $traceId, $debug), $status),
            Layer::Page => Response::html(
                Document::page("Error {$status}", self::fragment($status, $code, $message, $traceId, $debug)),
                $status,
            ),
        };

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    /** @return array{exception: string, message: string, file: string, trace: list<string>} */
    private static function debugInfo(Throwable $e): array
    {
        return [
            'exception' => $e::class,
            'message' => $e->getMessage(),
            'file' => $e->getFile() . ':' . $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString()),
        ];
    }

    /** @param array<string, mixed>|null $debug */
    private static function fragment(int $status, string $code, string $message, string $traceId, ?array $debug): string
    {
        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = sprintf(
            '<section class="lego-error" data-status="%d" data-code="%s" data-trace-id="%s"><h1>Error %d</h1><p>%s</p>',
            $status,
            $escape($code),
            $escape($traceId),
            $status,
            $escape($message),
        );

        if ($debug !== null) {
            $html .= '<pre>' . $escape(json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '') . '</pre>';
        }

        return $html . '</section>';
    }
}
