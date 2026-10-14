<?php

declare(strict_types=1);

use Composer\Autoload\ClassLoader;
use Lego\Application;
use Lego\Auth\Installer;
use Lego\Modules\Users\User;
use Lego\Console\Console;
use Lego\Environment;
use Lego\Http\Request;
use Lego\Log\Logger;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Support\FakeGuard;

/*
 * Follows doc/modulos.md step by step ("Crear un módulo"): if the guide stops
 * working, this test fails.
 */

it('builds a working module with two files and no core changes, as the guide says', function () {
    $dir = sys_get_temp_dir() . '/lego-guide-' . bin2hex(random_bytes(4));
    mkdir($dir);
    $loader = array_values(ClassLoader::getRegisteredLoaders())[0];
    $loader->addPsr4('Modules\\', $dir, true);
    (new Installer())->install('ana@example.com', 'Ana', 'secret-123');
    $identity = User::findByEmail('ana@example.com')->load('role')->toIdentity();

    $boot = static fn (): Application => new Application(
        basePath(),
        new Environment(['APP_DEBUG' => 'false', 'APP_URL' => 'http://localhost']),
        new Logger(fopen('php://memory', 'w+b')),
        testDatabase(),
        new FakeGuard($identity),
        modulesPath: $dir,
    );

    // 1. php lego make:module Invoices --label=Facturas
    $make = new CommandTester(Console::create($boot())->find('make:module'));
    expect($make->execute(['name' => 'Invoices', '--label' => 'Facturas']))->toBe(0);

    $created = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
        $created[] = str_replace('\\', '/', substr($file->getPathname(), strlen($dir) + 1));
    }
    sort($created);

    // 2. php lego migrate
    $app = $boot();
    $applied = $app->migrator()->migrate();

    // 3. The module works: menu, actions, API and screens.
    $api = $app->handle(csrfRequest('POST', '/api/actions/invoices_create', ['name' => 'F-001']));
    $list = $app->handle(Request::create('GET', '/invoices'));
    $edit = $app->handle(Request::create('GET', '/invoices/' . json_decode($api->body, true)['data']['id']));

    expect($created)->toHaveCount(2)
        ->and($created[0])->toBe('Invoices/Invoice.php')
        ->and($created[1])->toMatch('#^Invoices/migrations/\d{4}_\d{2}_\d{2}_\d{6}_create_invoices_table\.php$#')
        ->and($applied)->toHaveCount(1)
        ->and($app->modules->get('invoices')->label)->toBe('Facturas')
        ->and(array_keys(array_filter($app->actions->all(), static fn ($a) => $a->module === 'invoices')))
        ->toBe(['invoices_list', 'invoices_get', 'invoices_create', 'invoices_update', 'invoices_delete'])
        ->and($api->status)->toBe(201)
        ->and($list->body)->toContain('F-001')
        ->and($list->body)->toContain('data-lego-nav data-module="invoices"')
        ->and($edit->body)->toContain('value="F-001"');

    foreach (array_reverse($created) as $file) {
        unlink("{$dir}/{$file}");
    }
    rmdir("{$dir}/Invoices/migrations");
    rmdir("{$dir}/Invoices");
    rmdir($dir);
});
