<?php

declare(strict_types=1);

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Lego\Database\Migration;

return new class extends Migration {
    public function up(Builder $schema, Connection $db): void
    {
        $schema->create('clients', static function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 255)->unique();
            $table->decimal('credit', 12, 2)->nullable();
            $table->boolean('active')->default(true);
            $table->string('tier', 16)->nullable();
            $table->date('since')->nullable();
            $table->integer('visits')->default(0);
            $table->text('notes')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(Builder $schema, Connection $db): void
    {
        $schema->drop('clients');
    }
};
