<?php

declare(strict_types=1);

namespace Lego\Http;

/**
 * Immutable HTTP response.
 *
 * Handlers return a Response; only send() writes to the output. Nothing in the
 * framework calls echo, die or exit, which keeps every path testable.
 */
final class Response
{
    /** @var array<string, string> */
    private readonly array $headers;

    /**
     * @param array<string, string> $headers
     * @param list<string>          $cookies Complete Set-Cookie header values.
     */
    public function __construct(
        public readonly string $body = '',
        public readonly int $status = 200,
        array $headers = [],
        private readonly array $cookies = [],
    ) {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $normalized[strtolower($name)] = $value;
        }
        $this->headers = $normalized;
    }

    public static function html(string $html, int $status = 200): self
    {
        return new self($html, $status, ['content-type' => 'text/html; charset=utf-8']);
    }

    public static function json(mixed $data, int $status = 200): self
    {
        $body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return new self($body, $status, ['content-type' => 'application/json; charset=utf-8']);
    }

    public static function text(string $text, int $status = 200): self
    {
        return new self($text, $status, ['content-type' => 'text/plain; charset=utf-8']);
    }

    /** Redirect; 303 after a form POST so the browser follows with GET. */
    public static function redirect(string $location, int $status = 302): self
    {
        return new self('', $status, ['location' => $location]);
    }

    public static function noContent(): self
    {
        return new self('', 204);
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        return $this->headers;
    }

    /** @return list<string> */
    public function cookies(): array
    {
        return $this->cookies;
    }

    public function withHeader(string $name, string $value): self
    {
        return new self($this->body, $this->status, [...$this->headers, strtolower($name) => $value], $this->cookies);
    }

    public function withBody(string $body): self
    {
        return new self($body, $this->status, $this->headers, $this->cookies);
    }

    /** Adds a Set-Cookie header, usually built by CookiePolicy. */
    public function withCookie(string $setCookie): self
    {
        return new self($this->body, $this->status, $this->headers, [...$this->cookies, $setCookie]);
    }

    public function send(bool $includeBody = true): void
    {
        if (!headers_sent()) {
            http_response_code($this->status);
            foreach ($this->headers as $name => $value) {
                header($name . ': ' . $value);
            }
            foreach ($this->cookies as $cookie) {
                header('set-cookie: ' . $cookie, false);
            }
        }

        if ($includeBody) {
            echo $this->body;
        }
    }
}
