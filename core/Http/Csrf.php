<?php

declare(strict_types=1);

namespace Lego\Http;

use Lego\Errors\Forbidden;

/**
 * CSRF protection for every unsafe request (POST, PUT, PATCH, DELETE).
 *
 * Double submit: the browser keeps a random token in an HttpOnly cookie, and
 * the server renders the same token into forms (field `_csrf`) and into a
 * meta tag for fetch (header `X-CSRF-Token`). Another site can neither read
 * the cookie nor guess the token. On top of that, an Origin header from a
 * different host is rejected.
 */
final class Csrf
{
    public const COOKIE = 'lego_csrf';

    public const HEADER = 'x-csrf-token';

    public const FIELD = '_csrf';

    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(private readonly CookiePolicy $cookies)
    {
    }

    /** The token stored in the browser, or null when absent or malformed. */
    public function tokenFrom(Request $request): ?string
    {
        $token = $request->cookie($this->cookies->name(self::COOKIE));

        return $token !== null && preg_match('/^[0-9a-f]{64}$/', $token) === 1 ? $token : null;
    }

    public function issue(): string
    {
        return bin2hex(random_bytes(32));
    }

    public function cookieFor(string $token): string
    {
        return $this->cookies->make(self::COOKIE, $token);
    }

    /** @throws Forbidden with code csrf_mismatch */
    public function verify(Request $request): void
    {
        if (in_array($request->method, self::SAFE_METHODS, true)) {
            return;
        }

        if (!$this->sameOrigin($request)) {
            throw Forbidden::csrf();
        }

        $expected = $this->tokenFrom($request);
        $sent = $request->header(self::HEADER) ?? $request->input(self::FIELD);
        if ($expected === null || !is_string($sent) || !hash_equals($expected, $sent)) {
            throw Forbidden::csrf();
        }
    }

    private function sameOrigin(Request $request): bool
    {
        $origin = $request->header('origin');
        if ($origin === null || $origin === '' || $origin === 'null') {
            return $origin !== 'null';
        }

        $host = parse_url($origin, PHP_URL_HOST);
        $port = parse_url($origin, PHP_URL_PORT);
        $originHost = strtolower((string) $host) . ($port !== null ? ':' . $port : '');

        return $originHost === strtolower((string) $request->header('host'));
    }
}
