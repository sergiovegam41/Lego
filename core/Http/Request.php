<?php

declare(strict_types=1);

namespace Lego\Http;

/**
 * Immutable HTTP request.
 *
 * Built once from PHP globals in production and from Request::create() in tests,
 * so nothing else in the framework reads $_SERVER, $_GET or $_POST.
 */
final class Request
{
    /** @var array<string, string> */
    private readonly array $headers;

    /**
     * @param array<string, mixed>  $query
     * @param array<string, mixed>  $body
     * @param array<string, string> $headers
     * @param array<string, string> $cookies
     * @param array<string, string> $params  Route parameters, filled by the router.
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query = [],
        public readonly array $body = [],
        array $headers = [],
        public readonly array $cookies = [],
        public readonly array $params = [],
        public readonly bool $malformedBody = false,
    ) {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $normalized[strtolower((string) $name)] = (string) $value;
        }
        $this->headers = $normalized;
    }

    public static function fromGlobals(): self
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');

        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with((string) $key, 'HTTP_')) {
                $headers[str_replace('_', '-', substr((string) $key, 5))] = (string) $value;
            }
        }
        foreach (['CONTENT_TYPE' => 'content-type', 'CONTENT_LENGTH' => 'content-length'] as $key => $name) {
            if (isset($_SERVER[$key])) {
                $headers[$name] = (string) $_SERVER[$key];
            }
        }

        $body = $_POST;
        $malformed = false;
        if (str_contains(strtolower($headers['content-type'] ?? ''), 'application/json')) {
            [$body, $malformed] = self::decodeJson((string) file_get_contents('php://input'));
        }

        return new self(
            method: $method,
            path: self::normalizePath((string) parse_url($uri, PHP_URL_PATH)),
            query: $_GET,
            body: $body,
            headers: $headers,
            cookies: array_map('strval', $_COOKIE),
            malformedBody: $malformed,
        );
    }

    /**
     * Decodes a JSON body. An empty body is an empty object; anything that is
     * not a JSON object marks the request as malformed (answered with 400).
     *
     * @return array{0: array<string, mixed>, 1: bool}
     */
    public static function decodeJson(string $raw): array
    {
        if (trim($raw) === '') {
            return [[], false];
        }

        $decoded = json_decode($raw, true);

        $isObject = $decoded === [] || (is_array($decoded) && !array_is_list($decoded));

        return $isObject ? [$decoded, false] : [[], true];
    }

    /**
     * Builds a request for tests and internal calls.
     *
     * @param array<string, mixed>  $body
     * @param array<string, string> $headers
     * @param array<string, string> $cookies
     */
    public static function create(
        string $method,
        string $uri,
        array $body = [],
        array $headers = [],
        array $cookies = [],
    ): self {
        $query = [];
        parse_str((string) parse_url($uri, PHP_URL_QUERY), $query);

        return new self(
            method: strtoupper($method),
            path: self::normalizePath((string) parse_url($uri, PHP_URL_PATH)),
            query: $query,
            body: $body,
            headers: $headers,
            cookies: $cookies,
        );
    }

    /**
     * Decodes the path, guarantees a leading slash and removes the trailing one.
     */
    public static function normalizePath(string $path): string
    {
        $path = '/' . ltrim(rawurldecode($path), '/');

        return $path === '/' ? $path : rtrim($path, '/');
    }

    public function layer(): Layer
    {
        return Layer::fromPath($this->path);
    }

    public function header(string $name, ?string $default = null): ?string
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        return $this->headers;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function param(string $key, ?string $default = null): ?string
    {
        return $this->params[$key] ?? $default;
    }

    public function cookie(string $name, ?string $default = null): ?string
    {
        return $this->cookies[$name] ?? $default;
    }

    /** @param array<string, string> $params */
    public function withParams(array $params): self
    {
        return new self(
            $this->method,
            $this->path,
            $this->query,
            $this->body,
            $this->headers,
            $this->cookies,
            $params,
            $this->malformedBody,
        );
    }
}
