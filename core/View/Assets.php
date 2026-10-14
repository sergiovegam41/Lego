<?php

declare(strict_types=1);

namespace Lego\View;

use LogicException;

/**
 * Collects the stylesheets and ES modules used while rendering one page or fragment.
 *
 * Components register their files as they render. Document reads the collection
 * once rendering ends and emits each file a single time, versioned by its
 * modification time. Files are served by StaticFiles from the same mounts.
 */
final class Assets
{
    /** @var array<string, string> real directory => URL prefix */
    private static array $mounts = [];

    private static ?self $active = null;

    /** @var array<string, string> real path => URL */
    private array $styles = [];

    /** @var array<string, string> real path => URL */
    private array $scripts = [];

    /** @param array<string, string> $mounts URL prefix => directory */
    public static function mount(array $mounts): void
    {
        self::$mounts = [];
        foreach ($mounts as $prefix => $directory) {
            $real = realpath($directory);
            if ($real !== false) {
                self::$mounts[$real] = '/' . trim($prefix, '/') . '/';
            }
        }
    }

    /**
     * Runs a render callback and returns its HTML with the assets it registered.
     *
     * @param callable(): string $render
     * @return array{0: string, 1: self}
     */
    public static function collect(callable $render): array
    {
        $previous = self::$active;
        $collector = self::$active = new self();

        try {
            return [$render(), $collector];
        } finally {
            self::$active = $previous;
        }
    }

    /** Registers a stylesheet in the active collection. Outside a collection it is a no-op. */
    public static function style(string $file): void
    {
        if (self::$active !== null) {
            $real = self::realFile($file);
            self::$active->styles[$real] ??= self::url($real);
        }
    }

    /** Registers an ES module in the active collection. Outside a collection it is a no-op. */
    public static function script(string $file): void
    {
        if (self::$active !== null) {
            $real = self::realFile($file);
            self::$active->scripts[$real] ??= self::url($real);
        }
    }

    /** Public URL of a file inside a mount, versioned by its modification time. */
    public static function url(string $file): string
    {
        $real = self::realFile($file);
        foreach (self::$mounts as $directory => $prefix) {
            if (str_starts_with($real, $directory . DIRECTORY_SEPARATOR)) {
                $relative = str_replace(DIRECTORY_SEPARATOR, '/', substr($real, strlen($directory) + 1));

                return $prefix . $relative . '?v=' . dechex((int) filemtime($real));
            }
        }

        throw new LogicException("Asset is outside every mount: {$file}");
    }

    /** @return list<string> */
    public function styles(): array
    {
        return array_values($this->styles);
    }

    /** @return list<string> */
    public function scripts(): array
    {
        return array_values($this->scripts);
    }

    public function tags(): string
    {
        $tags = [];
        foreach ($this->styles as $url) {
            $tags[] = '<link rel="stylesheet" href="' . htmlspecialchars($url, ENT_QUOTES) . '">';
        }
        foreach ($this->scripts as $url) {
            $tags[] = '<script type="module" src="' . htmlspecialchars($url, ENT_QUOTES) . '"></script>';
        }

        return implode("\n", $tags);
    }

    private static function realFile(string $file): string
    {
        $real = realpath($file);
        if ($real === false || !is_file($real)) {
            throw new LogicException("Asset not found: {$file}");
        }

        return $real;
    }
}
