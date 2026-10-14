<?php

declare(strict_types=1);

/*
 * Fresh data for the browser tests: empty schema, all migrations, one admin
 * and two clients. Refuses to run unless DB_DATABASE ends in "_test".
 */

use Lego\Application;
use Lego\Auth\Installer;
use Lego\Environment;
use Tests\Fixtures\Modules\Clients\Client;

$base = dirname(__DIR__, 2);
require $base . '/vendor/autoload.php';

$env = Environment::load($base);
if (!str_ends_with((string) $env->get('DB_DATABASE'), '_test')) {
    fwrite(STDERR, "DB_DATABASE must end in _test\n");
    exit(1);
}

$app = new Application($base, $env, modulesPath: $base . '/tests/Fixtures/Modules', modulesNamespace: 'Tests\\Fixtures\\Modules\\');
$db = $app->db->connection();
$db->statement('DROP SCHEMA IF EXISTS public CASCADE');
$db->statement('CREATE SCHEMA public');
$app->migrator()->migrate();

(new Installer())->install('admin@example.com', 'Admin', 'browser-password');

foreach ([['Florería Lima', 'ventas@lima.pe', '1500.50'], ['Rosas del Norte', 'hola@rosas.co', '320.00']] as [$name, $email, $credit]) {
    (new Client())->forceFill(['name' => $name, 'email' => $email, 'credit' => $credit, 'active' => true])->save();
}

fwrite(STDOUT, "Browser test data ready.\n");
