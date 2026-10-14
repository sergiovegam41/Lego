<?php

declare(strict_types=1);

namespace Lego\Http;

use Closure;
use Lego\Errors\MethodNotAllowed;
use Lego\Errors\NotFound;

/**
 * Maps a method and a path to a handler.
 *
 * Patterns use {name} placeholders that match one path segment. HEAD requests
 * are answered by GET routes. A handler receives the Request and returns a
 * Response, a string (HTML) or an array (JSON).
 */
final class Router
{
    /** @var list<array{method: string, path: string, regex: string, handler: Closure}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function add(string $method, string $path, callable $handler): void
    {
        $path = Request::normalizePath($path);

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'regex' => self::compile($path),
            'handler' => $handler(...),
        ];
    }

    /**
     * @return array{handler: Closure, params: array<string, string>}
     *
     * @throws NotFound         when no path matches.
     * @throws MethodNotAllowed when the path only matches other methods.
     */
    public function match(Request $request): array
    {
        $method = $request->method === 'HEAD' ? 'GET' : $request->method;
        $allowed = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $request->path, $matches) !== 1) {
                continue;
            }
            if ($route['method'] !== $method) {
                $allowed[] = $route['method'];
                continue;
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            return ['handler' => $route['handler'], 'params' => $params];
        }

        if ($allowed !== []) {
            throw new MethodNotAllowed(array_values(array_unique($allowed)));
        }

        throw new NotFound();
    }

    /** @return list<array{method: string, path: string}> */
    public function routes(): array
    {
        return array_map(
            static fn (array $route): array => ['method' => $route['method'], 'path' => $route['path']],
            $this->routes,
        );
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
