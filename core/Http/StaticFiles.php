<?php

declare(strict_types=1);

namespace Lego\Http;

use Lego\Errors\NotFound;

/**
 * Serves component and module assets straight from their source folders.
 *
 * Each mount maps a URL prefix to a directory. Only whitelisted extensions are
 * served, so PHP sources next to the assets are never exposed, and every path
 * is resolved with realpath and confined to its mount. It works the same
 * behind nginx and under `php -S`, without symlinks or a copy step.
 */
final class StaticFiles
{
    public const MIME_TYPES = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'text/javascript; charset=utf-8',
        'mjs' => 'text/javascript; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];

    /** @var array<string, string> URL prefix ending in "/" => real directory path */
    private array $mounts = [];

    /** @param array<string, string> $mounts URL prefix => directory */
    public function __construct(array $mounts)
    {
        foreach ($mounts as $prefix => $directory) {
            $real = realpath($directory);
            if ($real !== false && is_dir($real)) {
                $this->mounts['/' . trim($prefix, '/') . '/'] = $real;
            }
        }
    }

    /** @return array<string, string> */
    public function mounts(): array
    {
        return $this->mounts;
    }

    /**
     * Returns the response for a static asset, or null when the request does not
     * target a mount.
     *
     * @throws NotFound for missing, forbidden or out-of-mount files.
     */
    public function serve(Request $request): ?Response
    {
        if ($request->method !== 'GET' && $request->method !== 'HEAD') {
            return null;
        }

        foreach ($this->mounts as $prefix => $directory) {
            if (str_starts_with($request->path . '/', $prefix) && $request->path . '/' !== $prefix) {
                return $this->file($directory, substr($request->path, strlen($prefix)), $request);
            }
        }

        return null;
    }

    private function file(string $directory, string $relative, Request $request): Response
    {
        $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
        $mime = self::MIME_TYPES[$extension] ?? null;
        $full = realpath($directory . DIRECTORY_SEPARATOR . $relative);

        if (
            $mime === null
            || $full === false
            || !is_file($full)
            || !str_starts_with($full, $directory . DIRECTORY_SEPARATOR)
        ) {
            throw new NotFound();
        }

        $etag = sprintf('"%x-%x"', (int) filemtime($full), (int) filesize($full));
        $cache = $request->query('v') !== null
            ? 'public, max-age=31536000, immutable'
            : 'no-cache';
        $headers = ['content-type' => $mime, 'etag' => $etag, 'cache-control' => $cache];

        if ($request->header('if-none-match') === $etag) {
            return new Response('', 304, $headers);
        }

        return new Response((string) file_get_contents($full), 200, $headers);
    }
}
