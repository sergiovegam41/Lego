<?php

declare(strict_types=1);

use Lego\Database\Migrator;

function coreMigrator(): Migrator
{
    return new Migrator(testDatabase(), [basePath('core/Database/migrations')]);
}

it('has every core migration applied in the first batch', function () {
    $status = coreMigrator()->status();

    expect($status)->not->toBeEmpty();
    foreach ($status as $row) {
        expect($row['batch'])->toBe(1, $row['name']);
    }
});

it('does nothing when there is nothing pending', function () {
    expect(coreMigrator()->migrate())->toBe([]);
});

it('rolls back the last batch and applies it again', function () {
    $schema = testDatabase()->schema();

    $reverted = coreMigrator()->rollback();

    expect($reverted)->toHaveCount(count(coreMigrator()->files()))
        ->and($reverted[0])->toEndWith('create_action_log_table')
        ->and($schema->hasTable('users'))->toBeFalse();

    $applied = coreMigrator()->migrate();

    expect($applied)->toHaveCount(count($reverted))
        ->and($schema->hasTable('users'))->toBeTrue();
});

it('enforces lowercase unique emails and role integrity in the schema', function () {
    $db = testDatabase()->connection();
    $roleId = $db->table('roles')->insertGetId(['name' => 'r', 'label' => 'R', 'permissions' => '[]']);
    $user = ['name' => 'A', 'password_hash' => 'x', 'role_id' => $roleId];

    $db->table('users')->insert([...$user, 'email' => 'a@example.com']);

    expect(fn () => $db->transaction(fn () => $db->table('users')->insert([...$user, 'email' => 'a@example.com'])))
        ->toThrow(Illuminate\Database\QueryException::class)
        ->and(fn () => $db->transaction(fn () => $db->table('users')->insert([...$user, 'email' => 'B@example.com'])))
        ->toThrow(Illuminate\Database\QueryException::class)
        ->and(fn () => $db->transaction(fn () => $db->table('roles')->where('id', $roleId)->delete()))
        ->toThrow(Illuminate\Database\QueryException::class);
});
