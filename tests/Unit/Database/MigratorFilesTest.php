<?php

declare(strict_types=1);

use Lego\Database\Database;
use Lego\Database\Migrator;
use Lego\Environment;

function migratorFor(array $paths): Migrator
{
    return new Migrator(new Database(new Environment(['DB_PORT' => '1'])), $paths);
}

it('lists migration files sorted by name across folders', function () {
    $names = array_keys(migratorFor([fixture('migrations/b'), fixture('migrations/a')])->files());

    expect($names)->toBe(['2026_01_01_000001_first', '2026_01_02_000001_second']);
});

it('rejects two migrations with the same name', function () {
    migratorFor([fixture('migrations/a'), fixture('migrations/duplicate')])->files();
})->throws(LogicException::class, 'Duplicate migration name');

it('ships core migrations that each return a Migration', function () {
    foreach (migratorFor([basePath('core/Database/migrations')])->files() as $name => $file) {
        expect(require $file)->toBeInstanceOf(Lego\Database\Migration::class, $name);
    }
});
