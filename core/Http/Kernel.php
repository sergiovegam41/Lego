<?php

declare(strict_types=1);

namespace Lego\Http;

use Lego\Auth\Gate;
use Lego\Auth\Guard;
use Lego\Errors\BadRequest;
use Lego\Errors\Unauthenticated;
use Lego\View\Component;
use Lego\View\Document;
use Throwable;
use UnexpectedValueException;

/**
 * Turns a Request into a Response.
 *
 * Pipeline: static assets, then
 *   1. malformed body           -> 400
 *   2. route match              -> 404 / 405
 *   3. identify the user        (session cookie)
 *   4. route access             -> 401 / 403 (closed unless the route is public)
 *   5. CSRF on unsafe methods   -> 403
 *   6. handler
 * Any exception becomes an error response for the request's layer.
 *
 * Every response carries X-Trace-Id and basic security headers. The CSRF
 * cookie is issued on the first request that lacks it.
 */
final class Kernel
{
    private const SECURITY_HEADERS = [
        'x-content-type-options' => 'nosniff',
        'x-frame-options' => 'DENY',
        'referrer-policy' => 'same-origin',
    ];

    public function __construct(
        private readonly Router $router,
        private readonly StaticFiles $staticFiles,
        private readonly ErrorRenderer $errors,
        private readonly Guard $guard,
        private readonly Csrf $csrf,
        private readonly Gate $gate,
    ) {
    }

    public function handle(Request $request): Response
    {
        $traceId = bin2hex(random_bytes(8));
        $request = $request->withTraceId($traceId);
        $issuedCsrf = null;

        try {
            $response = $this->staticFiles->serve($request);
            if ($response === null) {
                $token = $this->csrf->tokenFrom($request) ?? ($issuedCsrf = $this->csrf->issue());
                $response = $this->dispatch($request->withCsrfToken($token));
            }
        } catch (Throwable $e) {
            $response = $this->errors->render($e, $request, $traceId);
        }

        if ($issuedCsrf !== null) {
            $response = $response->withCookie($this->csrf->cookieFor($issuedCsrf));
        }
        foreach ([...self::SECURITY_HEADERS, 'x-trace-id' => $traceId] as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $request->method === 'HEAD' ? $response->withBody('') : $response;
    }

    private function dispatch(Request $request): Response
    {
        if ($request->malformedBody) {
            throw new BadRequest('El cuerpo de la petición no es un objeto JSON válido.');
        }

        ['route' => $route, 'params' => $params] = $this->router->match($request);
        $request = $request->withParams($params);

        if ($route->access() === Access::Authenticated) {
            $request = $request->withUser($this->guard->identify($request));
            if ($request->user === null) {
                throw new Unauthenticated();
            }
            if ($route->permission() !== null) {
                $this->gate->authorize($request->user, $route->permission(), $request->body);
            }
        } elseif ($this->hasSessionCookie($request)) {
            $request = $request->withUser($this->guard->identify($request));
        }

        $this->csrf->verify($request);

        return $this->toResponse(($route->handler)($request), $request);
    }

    private function hasSessionCookie(Request $request): bool
    {
        foreach (array_keys($request->cookies) as $name) {
            if (str_ends_with($name, 'lego_session')) {
                return true;
            }
        }

        return false;
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
                    : Document::page('Lego', $result, csrfToken: $request->csrfToken),
            ),
            is_string($result) => Response::html($result),
            default => throw new UnexpectedValueException(
                'A route handler must return a Response, array, string or Component; got ' . get_debug_type($result),
            ),
        };
    }
}
