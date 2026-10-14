<?php

declare(strict_types=1);

namespace Lego\Http;

use Lego\Auth\Identity;

/**
 * Immutable HTTP request.
 *
 * Built once from PHP globals in production and from Request::create() in tests,
 * so nothing else in the framework reads $_SERVER, $_GET or $_POST. The kernel
 * enriches it with route parameters, the signed-in user, the CSRF token and
 * the trace id through the with*() methods.
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
        public readonly ?string $ip = null,
        public readonly ?Identity $user = null,
        public readonly string $csrfToken = '',
        public readonly string $traceId = '',
        public readonly string $cspNonce = '',
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
            ip: isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : null,
        );
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
            ip: '127.0.0.1',
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

    /** Path plus query string, as the browser requested it. */
    public function fullPath(): string
    {
        return $this->query === [] ? $this->path : $this->path . '?' . http_build_query($this->query);
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
        return $this->with(['params' => $params]);
    }

    public function withUser(?Identity $user): self
    {
        return $this->with(['user' => $user]);
    }

    public function withCsrfToken(string $token): self
    {
        return $this->with(['csrfToken' => $token]);
    }

    public function withTraceId(string $traceId): self
    {
        return $this->with(['traceId' => $traceId]);
    }

    /** Nonce that inline scripts of this response must carry to pass the CSP. */
    public function withCspNonce(string $nonce): self
    {
        return $this->with(['cspNonce' => $nonce]);
    }

    /** @param array<string, mixed> $changes */
    private function with(array $changes): self
    {
        return new self(...[
            'method' => $this->method,
            'path' => $this->path,
            'query' => $this->query,
            'body' => $this->body,
            'headers' => $this->headers,
            'cookies' => $this->cookies,
            'params' => $this->params,
            'malformedBody' => $this->malformedBody,
            'ip' => $this->ip,
            'user' => $this->user,
            'csrfToken' => $this->csrfToken,
            'traceId' => $this->traceId,
            'cspNonce' => $this->cspNonce,
            ...$changes,
        ]);
    }
}
