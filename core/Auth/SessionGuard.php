<?php

declare(strict_types=1);

namespace Lego\Auth;

use Lego\Http\CookiePolicy;
use Lego\Http\Request;

/** Reads the session cookie and resolves it through the session store. */
final class SessionGuard implements Guard
{
    public const COOKIE = 'lego_session';

    public function __construct(
        private readonly SessionStore $sessions,
        private readonly CookiePolicy $cookies,
    ) {
    }

    public function identify(Request $request): ?Identity
    {
        $token = $this->token($request);

        return $token === null ? null : $this->sessions->find($token);
    }

    public function token(Request $request): ?string
    {
        $token = $request->cookie($this->cookies->name(self::COOKIE));

        return $token === null || $token === '' ? null : $token;
    }

    public function cookieFor(string $token): string
    {
        return $this->cookies->make(self::COOKIE, $token, $this->sessions->absoluteDays * 86400);
    }

    public function forgetCookie(): string
    {
        return $this->cookies->forget(self::COOKIE);
    }
}
