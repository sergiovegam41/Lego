<?php

declare(strict_types=1);

use Lego\Console\Console;
use Symfony\Component\Console\Tester\CommandTester;

/*
 * Every read-only command runs without errors. `routes` broke silently in
 * phase 2 when routes became objects; this test exists so that cannot repeat.
 */

function command(string $name, array $input = []): CommandTester
{
    $tester = new CommandTester(Console::create(app())->find($name));
    $tester->execute($input);

    return $tester;
}

it('lists routes with their access policy', function () {
    $tester = command('routes');
    $display = $tester->getDisplay();

    expect($tester->getStatusCode())->toBe(0)
        ->and($display)->toMatch('#\|\s*GET\s*\|\s*/login\s*\|\s*pública\s*\|#')
        ->and($display)->toMatch('#\|\s*POST\s*\|\s*/logout\s*\|\s*sesión\s*\|#')
        ->and($display)->toContain('/clients/{id}');
});

it('lists actions as a table and as JSON with their input schema', function () {
    $table = command('actions', ['--module' => 'clients']);
    $json = json_decode(command('actions', ['--module' => 'clients', '--json' => true])->getDisplay(), true);
    $create = array_values(array_filter($json, static fn (array $a): bool => $a['name'] === 'clients_create'))[0] ?? [];

    expect($table->getStatusCode())->toBe(0)
        ->and($table->getDisplay())->toContain('clients_block')
        ->and($create['input_schema']['required'] ?? null)->toBe(['name', 'email']);
});

it('registers every core command', function () {
    $names = array_keys(Console::create(app())->all());

    expect($names)->toContain('serve', 'routes', 'migrate', 'migrate:rollback', 'migrate:status', 'install', 'user:password', 'actions', 'run', 'make:module');
});
