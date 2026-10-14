<?php

declare(strict_types=1);

namespace Lego\Database;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;

/**
 * A schema change. Migration files return an anonymous subclass:
 *
 *     return new class extends Migration {
 *         public function up(Builder $schema, Connection $db): void { ... }
 *         public function down(Builder $schema, Connection $db): void { ... }
 *     };
 *
 * Each migration runs inside its own transaction. Migrations only change the
 * schema; data for a new installation comes from `php lego install`.
 */
abstract class Migration
{
    abstract public function up(Builder $schema, Connection $db): void;

    abstract public function down(Builder $schema, Connection $db): void;
}
