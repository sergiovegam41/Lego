<?php

declare(strict_types=1);

namespace Lego\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Lego\Environment;

/**
 * The PostgreSQL connection, through Eloquent (decision D10).
 *
 * The PDO handle opens lazily on the first query, so booting the application
 * never touches the database by itself.
 */
final class Database
{
    private readonly Capsule $capsule;

    public function __construct(Environment $env)
    {
        $this->capsule = new Capsule();
        $this->capsule->addConnection([
            'driver' => 'pgsql',
            'host' => $env->get('DB_HOST', '127.0.0.1'),
            'port' => (int) $env->get('DB_PORT', '5432'),
            'database' => $env->get('DB_DATABASE', 'lego'),
            'username' => $env->get('DB_USERNAME', 'lego'),
            'password' => $env->get('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => $env->get('DB_SSLMODE', 'prefer'),
            'connect_timeout' => 5,
        ]);
        $this->activate();
    }

    /** Makes this connection the one used by Eloquent models. */
    public function activate(): void
    {
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();
    }

    public function connection(): Connection
    {
        return $this->capsule->getConnection();
    }

    public function schema(): Builder
    {
        return $this->connection()->getSchemaBuilder();
    }

    /** True when a trivial query succeeds. Used by /health. */
    public function isReachable(): bool
    {
        try {
            $this->connection()->select('select 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
