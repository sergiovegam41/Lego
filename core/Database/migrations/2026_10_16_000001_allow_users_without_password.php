<?php

declare(strict_types=1);

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Lego\Database\Migration;

/*
 * Users created from the Users module start without a usable password (an
 * empty hash never verifies) until an administrator resets it.
 */
return new class extends Migration {
    public function up(Builder $schema, Connection $db): void
    {
        $db->statement("ALTER TABLE users ALTER COLUMN password_hash SET DEFAULT ''");
    }

    public function down(Builder $schema, Connection $db): void
    {
        $db->statement('ALTER TABLE users ALTER COLUMN password_hash DROP DEFAULT');
    }
};
