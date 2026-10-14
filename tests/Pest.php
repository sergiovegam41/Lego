<?php

declare(strict_types=1);

use Lego\Application;
use Lego\Auth\Guard;
use Lego\Database\Database;
use Lego\Database\Migrator;
use Lego\Environment;
use Lego\Http\Response;
use Lego\Log\Logger;
use Lego\Support\Clock;
use Lego\Support\SystemClock;

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
 * Application for unit tests: no reachable database (port 1 refuses at once),
 * explicit environment, a logger that writes to memory and an optional fake
 * guard to act as a signed-in user.
 *
 * @param array<string, string> $env
 * @param resource|null         $log
 */
function app(array $env = [], $log = null, ?Guard $guard = null): Application
{
    $env = new Environment(['APP_DEBUG' => 'false', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => '1', ...$env]);

    return new Application(basePath(), $env, new Logger($log ?? fopen('php://memory', 'w+b')), new Database($env), $guard);
}

/**
 * Shared PostgreSQL connection for database tests, migrated once per run.
 *
 * Reads DB_* from the environment. The database name must end in "_test",
 * because the public schema is dropped and recreated. Tests are skipped when
 * the server is unreachable, unless LEGO_REQUIRE_DB=true (CI, compose.test.yml).
 */
function testDatabase(): Database
{
    static $db = null;
    static $error = null;

    if ($error !== null) {
        PHPUnit\Framework\Assert::markTestSkipped($error);
    }
    if ($db !== null) {
        $db->activate();

        return $db;
    }

    $env = Environment::load(basePath());
    $name = (string) $env->get('DB_DATABASE', '');
    $candidate = new Database($env);

    if (!str_ends_with($name, '_test') || !$candidate->isReachable()) {
        $error = str_ends_with($name, '_test')
            ? "PostgreSQL no disponible en {$env->get('DB_HOST')}:{$env->get('DB_PORT', '5432')}"
            : 'DB_DATABASE debe terminar en _test para correr los tests de base de datos';
        if ($env->bool('LEGO_REQUIRE_DB')) {
            throw new RuntimeException($error);
        }
        PHPUnit\Framework\Assert::markTestSkipped($error);
    }

    $candidate->connection()->statement('DROP SCHEMA IF EXISTS public CASCADE');
    $candidate->connection()->statement('CREATE SCHEMA public');
    (new Migrator($candidate, [basePath('core/Database/migrations')]))->migrate();

    return $db = $candidate;
}

/** Application wired to the test database. */
function dbApp(?Clock $clock = null, array $env = []): Application
{
    $env = new Environment(['APP_DEBUG' => 'false', 'APP_URL' => 'http://localhost', ...$env]);

    return new Application(basePath(), $env, new Logger(fopen('php://memory', 'w+b')), testDatabase(), null, $clock ?? new SystemClock());
}

/**
 * Cookie values set by a response, by name.
 *
 * @return array<string, string>
 */
function cookiesFrom(Response $response): array
{
    $cookies = [];
    foreach ($response->cookies() as $header) {
        [$pair] = explode(';', $header, 2);
        [$name, $value] = explode('=', $pair, 2);
        $cookies[$name] = rawurldecode($value);
    }

    return $cookies;
}

pest()->extend(Tests\Support\DatabaseTestCase::class)->in('Feature/Database');
