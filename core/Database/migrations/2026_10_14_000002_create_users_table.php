<?php

declare(strict_types=1);

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Lego\Database\Migration;

/*
 * Emails are stored lowercase and are unique. Each user has exactly one role.
 */
return new class extends Migration {
    public function up(Builder $schema, Connection $db): void
    {
        $schema->create('users', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 255)->unique();
            $table->string('password_hash', 255);
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestampTz('last_login_at')->nullable();
            $table->timestampsTz();
        });

        $db->statement('ALTER TABLE users ADD CONSTRAINT users_email_lowercase CHECK (email = lower(email))');
    }

    public function down(Builder $schema, Connection $db): void
    {
        $schema->drop('users');
    }
};
