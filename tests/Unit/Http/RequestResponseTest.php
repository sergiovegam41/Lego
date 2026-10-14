<?php

declare(strict_types=1);

use Lego\Http\Layer;
use Lego\Http\Request;
use Lego\Http\Response;

it('parses the query string and normalizes the path', function () {
    $request = Request::create('get', '/users/?page=2&q=ana');

    expect($request->method)->toBe('GET')
        ->and($request->path)->toBe('/users')
        ->and($request->query('page'))->toBe('2')
        ->and($request->query('q'))->toBe('ana')
        ->and($request->query('missing', 'x'))->toBe('x');
});

it('decodes the path', function () {
    expect(Request::create('GET', '/a%20b')->path)->toBe('/a b');
});

it('reads headers case-insensitively', function () {
    $request = Request::create('GET', '/', headers: ['X-Requested-With' => 'lego']);

    expect($request->header('x-requested-with'))->toBe('lego')
        ->and($request->header('X-REQUESTED-WITH'))->toBe('lego');
});

it('detects its layer from the first path segment', function (string $path, Layer $layer) {
    expect(Request::create('GET', $path)->layer())->toBe($layer);
})->with([
    ['/api', Layer::Api],
    ['/api/users', Layer::Api],
    ['/apiary', Layer::Page],
    ['/component/users', Layer::Component],
    ['/', Layer::Page],
]);

it('returns a copy with route parameters', function () {
    $request = Request::create('GET', '/users/7');
    $withParams = $request->withParams(['id' => '7']);

    expect($request->param('id'))->toBeNull()
        ->and($withParams->param('id'))->toBe('7')
        ->and($withParams->path)->toBe('/users/7');
});

it('decodes JSON bodies and flags anything that is not an object', function (string $raw, array $body, bool $malformed) {
    expect(Request::decodeJson($raw))->toBe([$body, $malformed]);
})->with([
    'object' => ['{"name":"Ana"}', ['name' => 'Ana'], false],
    'empty body' => ['', [], false],
    'empty object' => ['{}', [], false],
    'invalid' => ['{"name":', [], true],
    'list' => ['[1,2]', [], true],
    'scalar' => ['"text"', [], true],
]);

it('keeps the malformed flag when adding route parameters', function () {
    $request = new Request('POST', '/api/x', malformedBody: true);

    expect($request->withParams(['id' => '1'])->malformedBody)->toBeTrue();
});

it('builds JSON responses', function () {
    $response = Response::json(['ok' => true, 'name' => 'Ñandú/1'], 201);

    expect($response->status)->toBe(201)
        ->and($response->header('Content-Type'))->toBe('application/json; charset=utf-8')
        ->and($response->body)->toBe('{"ok":true,"name":"Ñandú/1"}');
});

it('is immutable when adding headers', function () {
    $original = Response::html('<p>x</p>');
    $changed = $original->withHeader('X-Test', '1');

    expect($original->header('x-test'))->toBeNull()
        ->and($changed->header('x-test'))->toBe('1');
});

it('builds redirects', function () {
    $response = Response::redirect('/login');

    expect($response->status)->toBe(302)->and($response->header('location'))->toBe('/login');
});
