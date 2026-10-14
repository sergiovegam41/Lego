<?php

declare(strict_types=1);

use Lego\Application;
use Lego\Environment;
use Lego\Log\Logger;

/** Absolute path inside the repository. */
function basePath(string $path = ''): string
{
    $root = dirname(__DIR__);

    return $path === '' ? $root : $root . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
}

/** Absolute path inside tests/Fixtures. */
function fixture(string $path = ''): string
{
    return basePath('tests/Fixtures/' . ltrim($path, '/\\'));
}

/**
 * Application booted on the real repository, with explicit environment values
 * and a logger that writes to memory instead of stderr.
 *
 * @param array<string, string> $env
 * @param resource|null         $log
 */
function app(array $env = [], $log = null): Application
{
    return new Application(
        basePath(),
        new Environment(['APP_DEBUG' => 'false', ...$env]),
        new Logger($log ?? fopen('php://memory', 'w+b')),
    );
}
