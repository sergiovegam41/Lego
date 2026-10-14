<?php

declare(strict_types=1);

/*
 * Router script for `php -S` in the browser tests: the real Application,
 * with the test modules of tests/Fixtures/Modules.
 */

$base = dirname(__DIR__, 2);
require $base . '/vendor/autoload.php';

$app = new Lego\Application(
    $base,
    Lego\Environment::load($base),
    modulesPath: $base . '/tests/Fixtures/Modules',
    modulesNamespace: 'Tests\\Fixtures\\Modules\\',
);
Lego\Errors\ErrorHandling::register($app->logger);

$request = Lego\Http\Request::fromGlobals();
$app->handle($request)->send(includeBody: $request->method !== 'HEAD');
