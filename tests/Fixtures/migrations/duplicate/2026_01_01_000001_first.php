<?php

declare(strict_types=1);

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Lego\Database\Migration;

return new class extends Migration {
    public function up(Builder $schema, Connection $db): void
    {
    }

    public function down(Builder $schema, Connection $db): void
    {
    }
};
