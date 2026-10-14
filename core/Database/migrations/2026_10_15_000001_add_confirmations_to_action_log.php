<?php

declare(strict_types=1);

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Lego\Database\Migration;

/*
 * Two-phase confirmation of destructive actions lives in the action log: a
 * "pending" row holds the SHA-256 of a single-use token, the hash of the
 * validated input and an expiry. Consuming it turns the row "confirmed".
 */
return new class extends Migration {
    public function up(Builder $schema, Connection $db): void
    {
        $schema->table('action_log', static function (Blueprint $table): void {
            $table->char('confirmation_hash', 64)->nullable()->unique();
            $table->char('input_hash', 64)->nullable();
            $table->timestampTz('expires_at')->nullable();
        });
    }

    public function down(Builder $schema, Connection $db): void
    {
        $schema->table('action_log', static function (Blueprint $table): void {
            $table->dropColumn(['confirmation_hash', 'input_hash', 'expires_at']);
        });
    }
};
