<?php

declare(strict_types=1);

namespace Lego\Http;

use Lego\Errors\MethodNotAllowed;
use Lego\Errors\NotFound;

/**
 * Maps a method and a path to a Route.
 *
 * Patterns use {name} placeholders that match one path segment. HEAD requests
 * are answered by GET routes. Every route starts closed (Access::Authenticated);
 * see Route::public() and Route::can().
 */
final class Router
{
    /** @var list<Route> */
    private array $routes = [];

    public function get(string $path, callable $handler): Route
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): Route
    {
        return $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, callable $handler): Route
    {
        $path = Request::normalizePath($path);

        return $this->routes[] = new Route(strtoupper($method), $path, self::compile($path), $handler(...));
    }

    /**
     * @return array{route: Route, params: array<string, string>}
     *
     * @throws NotFound         when no path matches.
     * @throws MethodNotAllowed when the path only matches other methods.
     */
    public function match(Request $request): array
    {
        $method = $request->method === 'HEAD' ? 'GET' : $request->method;
        $allowed = [];

        foreach ($this->routes as $route) {
            if (preg_match($route->regex, $request->path, $matches) !== 1) {
                continue;
            }
            if ($route->method !== $method) {
                $allowed[] = $route->method;
                continue;
            }

            return ['route' => $route, 'params' => array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY)];
        }

        if ($allowed !== []) {
            throw new MethodNotAllowed(array_values(array_unique($allowed)));
        }

        throw new NotFound();
    }

    /** @return list<Route> */
    public function routes(): array
    {
        return $this->routes;
    }

    private static function compile(string $path): string
    {
        $parts = preg_split('/(\{[a-zA-Z_][a-zA-Z0-9_]*\})/', $path, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
        $regex = '';
        foreach ($parts as $part) {
            $regex .= preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $part, $m) === 1
                ? '(?P<' . $m[1] . '>[^/]+)'
                : preg_quote($part, '#');
        }

        return '#^' . $regex . '$#';
    }
}
