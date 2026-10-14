<?php

declare(strict_types=1);

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Lego\Database\Migration;

/*
 * Server-side sessions (decision D3). The id is the SHA-256 of the cookie
 * token, so a database leak does not expose usable tokens. A session ends at
 * expires_at (sliding, renewed on activity), at absolute_expires_at, or when
 * revoked_at is set by logout.
 */
return new class extends Migration {
    public function up(Builder $schema, Connection $db): void
    {
        $schema->create('sessions', static function (Blueprint $table): void {
            $table->char('id', 64)->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestampTz('created_at');
            $table->timestampTz('last_seen_at');
            $table->timestampTz('expires_at');
            $table->timestampTz('absolute_expires_at');
            $table->timestampTz('revoked_at')->nullable();
            $table->index('user_id');
        });
    }

    public function down(Builder $schema, Connection $db): void
    {
        $schema->drop('sessions');
    }
};
