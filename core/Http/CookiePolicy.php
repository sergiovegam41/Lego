<?php

declare(strict_types=1);

namespace Lego\Http;

/**
 * Builds Set-Cookie headers with safe defaults: Path=/, HttpOnly, SameSite=Lax.
 *
 * When the application runs on HTTPS (APP_URL starts with https://) cookies
 * also get Secure and the __Host- prefix, which binds them to the exact host
 * and prevents subdomains from overwriting them.
 */
final class CookiePolicy
{
    public function __construct(public readonly bool $secure)
    {
    }

    public static function fromAppUrl(?string $appUrl): self
    {
        return new self(str_starts_with(strtolower((string) $appUrl), 'https://'));
    }

    public function name(string $base): string
    {
        return $this->secure ? '__Host-' . $base : $base;
    }

    public function make(string $base, string $value, ?int $maxAgeSeconds = null): string
    {
        $parts = [$this->name($base) . '=' . rawurlencode($value), 'Path=/', 'HttpOnly', 'SameSite=Lax'];
        if ($this->secure) {
            $parts[] = 'Secure';
        }
        if ($maxAgeSeconds !== null) {
            $parts[] = 'Max-Age=' . $maxAgeSeconds;
        }

        return implode('; ', $parts);
    }

    public function forget(string $base): string
    {
        return $this->make($base, '', 0);
    }
}
