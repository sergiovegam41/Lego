<?php

declare(strict_types=1);

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Lego\Database\Migration;

/*
 * Append-only audit trail shared by people, the CLI and future AI agents.
 * `via` says where the action came from (ui, api, cli, agent); `status` is
 * ok, denied, error, pending or confirmed.
 */
return new class extends Migration {
    public function up(Builder $schema, Connection $db): void
    {
        $schema->create('action_log', static function (Blueprint $table): void {
            $table->id();
            $table->timestampTz('occurred_at')->useCurrent();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 120);
            $table->string('via', 16);
            $table->string('status', 16);
            $table->string('subject', 120)->nullable();
            $table->jsonb('input')->nullable();
            $table->string('trace_id', 32)->nullable();
            $table->string('ip', 45)->nullable();
            $table->index(['action', 'occurred_at']);
            $table->index('user_id');
        });
    }

    public function down(Builder $schema, Connection $db): void
    {
        $schema->drop('action_log');
    }
};
