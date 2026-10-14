<?php

declare(strict_types=1);

namespace Lego\Http;

use Lego\Errors\BadRequest;
use Lego\View\Component;
use Lego\View\Document;
use Throwable;
use UnexpectedValueException;

/**
 * Turns a Request into a Response: static assets first, then routes, and any
 * exception into an error response for the request's layer.
 *
 * Every response carries an X-Trace-Id header; the same id appears in error
 * bodies and in the logs, so a user can report it.
 */
final class Kernel
{
    public function __construct(
        private readonly Router $router,
        private readonly StaticFiles $staticFiles,
        private readonly ErrorRenderer $errors,
    ) {
    }

    public function handle(Request $request): Response
    {
        $traceId = bin2hex(random_bytes(8));

        try {
            $response = $this->staticFiles->serve($request) ?? $this->dispatch($request);
        } catch (Throwable $e) {
            $response = $this->errors->render($e, $request, $traceId);
        }

        $response = $response->withHeader('x-trace-id', $traceId);

        return $request->method === 'HEAD' ? $response->withBody('') : $response;
    }

    private function dispatch(Request $request): Response
    {
        if ($request->malformedBody) {
            throw new BadRequest('El cuerpo de la petición no es un objeto JSON válido.');
        }

        ['handler' => $handler, 'params' => $params] = $this->router->match($request);

        return $this->toResponse($handler($request->withParams($params)), $request);
    }

    /**
     * Handlers may return a Response, an array (JSON), a string (HTML) or a
     * Component, rendered as a fragment on the Component layer and as a page
     * elsewhere.
     */
    private function toResponse(mixed $result, Request $request): Response
    {
        return match (true) {
            $result instanceof Response => $result,
            is_array($result) => Response::json($result),
            $result instanceof Component => Response::html(
                $request->layer() === Layer::Component
                    ? Document::fragment($result)
                    : Document::page('Lego', $result),
            ),
            is_string($result) => Response::html($result),
            default => throw new UnexpectedValueException(
                'A route handler must return a Response, array, string or Component; got ' . get_debug_type($result),
            ),
        };
    }
}
