<?php

declare(strict_types=1);

// Single entry point for nginx + php-fpm and for `php -S` (router script).

require dirname(__DIR__) . '/vendor/autoload.php';

$app = Lego\Application::boot(dirname(__DIR__));
Lego\Errors\ErrorHandling::register($app->logger);

$request = Lego\Http\Request::fromGlobals();
$app->handle($request)->send(includeBody: $request->method !== 'HEAD');
