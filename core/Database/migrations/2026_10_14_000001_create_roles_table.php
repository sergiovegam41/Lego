<?php

declare(strict_types=1);

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Lego\Database\Migration;

/*
 * Roles hold a list of permissions such as "clients.create", "clients.*" or "*"
 * (decision D2). Permission names come from code; roles are data.
 */
return new class extends Migration {
    public function up(Builder $schema, Connection $db): void
    {
        $schema->create('roles', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 64)->unique();
            $table->string('label', 120);
            $table->jsonb('permissions')->default('[]');
            $table->timestampsTz();
        });
    }

    public function down(Builder $schema, Connection $db): void
    {
        $schema->drop('roles');
    }
};
