<?php

declare(strict_types=1);

namespace Lego\Http;

/**
 * Security headers on every response (doc/ia.md, research rule 9).
 *
 * - Content-Security-Policy: scripts only from this origin plus the per-request
 *   nonce of the import map; no inline styles, frames, plugins or foreign forms.
 * - Permissions-Policy: browser features off. `tools` is the WebMCP feature:
 *   off unless AI agents are enabled (LEGO_AGENT_ACCESS), and then only for
 *   this origin, never for embedded third parties.
 * - Strict-Transport-Security and Cross-Origin-Opener-Policy on HTTPS.
 */
final class SecurityHeaders
{
    public function __construct(private readonly bool $https = false, private readonly bool $agentTools = false)
    {
    }

    /** @return array<string, string> */
    public function for(string $nonce): array
    {
        $headers = [
            'content-security-policy' => implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}'",
                "style-src 'self'",
                "img-src 'self' data:",
                "connect-src 'self'",
                "font-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'",
            ]),
            'permissions-policy' => implode(', ', [
                'tools=' . ($this->agentTools ? '(self)' : '()'),
                'camera=()',
                'microphone=()',
                'geolocation=()',
                'payment=()',
                'usb=()',
            ]),
            'x-content-type-options' => 'nosniff',
            'x-frame-options' => 'DENY',
            'referrer-policy' => 'same-origin',
        ];

        if ($this->https) {
            // Browsers ignore both on plain HTTP, and Chrome logs an error for COOP.
            $headers['strict-transport-security'] = 'max-age=31536000; includeSubDomains';
            $headers['cross-origin-opener-policy'] = 'same-origin';
        }

        return $headers;
    }
}
