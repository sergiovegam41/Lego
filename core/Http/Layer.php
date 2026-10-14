<?php

declare(strict_types=1);

namespace Lego\Http;

/**
 * The three layers of a Lego application, chosen by the first path segment.
 *
 * - Api:       /api/*        JSON responses.
 * - Component: /component/*  HTML fragments inserted by the shell without reloading.
 * - Page:      everything else, full HTML documents.
 */
enum Layer
{
    case Api;
    case Component;
    case Page;

    public static function fromPath(string $path): self
    {
        return match (true) {
            $path === '/api' || str_starts_with($path, '/api/') => self::Api,
            $path === '/component' || str_starts_with($path, '/component/') => self::Component,
            default => self::Page,
        };
    }
}
