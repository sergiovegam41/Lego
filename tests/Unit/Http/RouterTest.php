<?php

declare(strict_types=1);

use Lego\Errors\HttpError;
use Lego\Http\Request;
use Lego\Http\Router;

function routerWith(string $method, string $path): Router
{
    $router = new Router();
    $router->add($method, $path, static fn (Request $r): string => 'ok');

    return $router;
}

it('matches a static path', function () {
    $match = routerWith('GET', '/users')->match(Request::create('GET', '/users'));

    expect($match['params'])->toBe([]);
});

it('captures named parameters', function () {
    $match = routerWith('GET', '/users/{id}/roles/{role}')->match(Request::create('GET', '/users/7/roles/admin'));

    expect($match['params'])->toBe(['id' => '7', 'role' => 'admin']);
});

it('does not let a parameter span several segments', function () {
    routerWith('GET', '/users/{id}')->match(Request::create('GET', '/users/7/extra'));
})->throws(HttpError::class);

it('ignores a trailing slash', function () {
    $match = routerWith('GET', '/users/')->match(Request::create('GET', '/users/'));

    expect($match)->toHaveKey('handler');
});

it('answers HEAD with the GET route', function () {
    $match = routerWith('GET', '/users')->match(Request::create('HEAD', '/users'));

    expect($match)->toHaveKey('handler');
});

it('throws 404 when nothing matches', function () {
    try {
        routerWith('GET', '/users')->match(Request::create('GET', '/nope'));
        $this->fail('Expected an HttpError');
    } catch (HttpError $e) {
        expect($e->status())->toBe(404)->and($e->errorCode())->toBe("not_found");
    }
});

it('throws 405 with the allowed methods when only the method differs', function () {
    try {
        routerWith('GET', '/users')->match(Request::create('POST', '/users'));
        $this->fail('Expected an HttpError');
    } catch (HttpError $e) {
        expect($e->status())->toBe(405)->and($e->headers())->toBe(['allow' => 'GET']);
    }
});

it('treats regex characters in paths literally', function () {
    routerWith('GET', '/a.b')->match(Request::create('GET', '/aXb'));
})->throws(HttpError::class);

it('lists its routes', function () {
    expect(routerWith('POST', '/x/')->routes())->toBe([['method' => 'POST', 'path' => '/x']]);
});
