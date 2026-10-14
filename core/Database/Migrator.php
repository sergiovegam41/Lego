<?php

declare(strict_types=1);

namespace Lego\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use LogicException;

/**
 * Applies and reverts migrations from a list of folders.
 *
 * - File names start with a sortable timestamp: 2026_10_14_000001_create_users.php.
 * - Each migration runs in its own transaction; a failure stops the run and
 *   leaves earlier migrations applied.
 * - A PostgreSQL advisory lock makes concurrent starts safe, so several
 *   containers can run `migrate` on boot at the same time.
 * - Running it again with nothing pending does nothing (idempotent).
 */
final class Migrator
{
    public const TABLE = 'lego_migrations';

    private const LOCK_KEY = 74_657_301;

    /** @param list<string> $paths */
    public function __construct(private readonly Database $db, private readonly array $paths)
    {
    }

    /** @return list<string> Names of the migrations applied, in order. */
    public function migrate(): array
    {
        return $this->locked(function (Connection $db): array {
            $applied = $this->appliedNames();
            $pending = array_values(array_diff(array_keys($this->files()), $applied));
            if ($pending === []) {
                return [];
            }

            $batch = (int) $db->table(self::TABLE)->max('batch') + 1;
            foreach ($pending as $name) {
                $db->transaction(function (Connection $db) use ($name, $batch): void {
                    $this->load($name)->up($db->getSchemaBuilder(), $db);
                    $db->table(self::TABLE)->insert(['name' => $name, 'batch' => $batch, 'ran_at' => date(DATE_ATOM)]);
                });
            }

            return $pending;
        });
    }

    /** @return list<string> Names of the migrations reverted, newest first. */
    public function rollback(int $batches = 1): array
    {
        return $this->locked(function (Connection $db) use ($batches): array {
            $lastBatches = $db->table(self::TABLE)->distinct()->orderByDesc('batch')->limit(max(1, $batches))->pluck('batch');
            $names = $db->table(self::TABLE)
                ->whereIn('batch', $lastBatches)
                ->orderByDesc('batch')
                ->orderByDesc('name')
                ->pluck('name')
                ->all();

            foreach ($names as $name) {
                $db->transaction(function (Connection $db) use ($name): void {
                    $this->load($name)->down($db->getSchemaBuilder(), $db);
                    $db->table(self::TABLE)->where('name', $name)->delete();
                });
            }

            return $names;
        });
    }

    /** @return list<array{name: string, batch: int|null}> */
    public function status(): array
    {
        $this->ensureTable();
        $batches = $this->db->connection()->table(self::TABLE)->pluck('batch', 'name')->all();

        $rows = [];
        foreach (array_keys($this->files()) as $name) {
            $rows[] = ['name' => $name, 'batch' => isset($batches[$name]) ? (int) $batches[$name] : null];
        }

        return $rows;
    }

    /** @return array<string, string> migration name => file path, sorted by name */
    public function files(): array
    {
        $files = [];
        foreach ($this->paths as $path) {
            foreach (glob(rtrim($path, '/\\') . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
                $name = basename($file, '.php');
                if (isset($files[$name])) {
                    throw new LogicException("Duplicate migration name: {$name}");
                }
                $files[$name] = $file;
            }
        }
        ksort($files);

        return $files;
    }

    /** @return list<string> */
    private function appliedNames(): array
    {
        return $this->db->connection()->table(self::TABLE)->orderBy('name')->pluck('name')->all();
    }

    private function load(string $name): Migration
    {
        $file = $this->files()[$name] ?? throw new LogicException("Migration file not found: {$name}");
        $migration = require $file;
        if (!$migration instanceof Migration) {
            throw new LogicException("Migration {$name} must return an instance of " . Migration::class);
        }

        return $migration;
    }

    private function ensureTable(): void
    {
        $schema = $this->db->schema();
        if (!$schema->hasTable(self::TABLE)) {
            $schema->create(self::TABLE, static function (Blueprint $table): void {
                $table->string('name', 191)->primary();
                $table->unsignedInteger('batch');
                $table->timestampTz('ran_at');
            });
        }
    }

    /**
     * @template T
     * @param callable(Connection): T $callback
     * @return T
     */
    private function locked(callable $callback): mixed
    {
        $db = $this->db->connection();
        $db->select('select pg_advisory_lock(?)', [self::LOCK_KEY]);

        try {
            $this->ensureTable();

            return $callback($db);
        } finally {
            $db->select('select pg_advisory_unlock(?)', [self::LOCK_KEY]);
        }
    }
}
