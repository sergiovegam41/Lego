<?php

declare(strict_types=1);

namespace Lego;

use Dotenv\Dotenv;

/**
 * Configuration read from environment variables.
 *
 * A .env file is optional and only fills variables that are not already set,
 * so Docker, Dokploy and CI configure Lego with plain environment variables.
 */
final class Environment
{
    /** @param array<string, string> $values */
    public function __construct(private readonly array $values)
    {
    }

    public static function load(string $basePath): self
    {
        if (is_file($basePath . DIRECTORY_SEPARATOR . '.env')) {
            Dotenv::createImmutable($basePath)->safeLoad();
        }

        $values = [];
        foreach ([...getenv(), ...$_ENV, ...$_SERVER] as $key => $value) {
            if (is_string($key) && is_scalar($value)) {
                $values[$key] = (string) $value;
            }
        }

        return new self($values);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $value = $this->values[$key] ?? null;

        return $value === null || $value === '' ? $default : $value;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        return $value === null
            ? $default
            : in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    public function debug(): bool
    {
        return $this->bool('APP_DEBUG');
    }
}
