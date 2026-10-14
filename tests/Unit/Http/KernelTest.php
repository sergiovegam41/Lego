<?php

declare(strict_types=1);

use Lego\Errors\BadRequest;
use Lego\Http\Request;
use Lego\Http\Response;

it('renders the welcome page with its assets', function () {
    $response = app()->handle(Request::create('GET', '/'));

    expect($response->status)->toBe(200)
        ->and($response->header('content-type'))->toBe('text/html; charset=utf-8')
        ->and($response->body)->toStartWith('<!doctype html>')
        ->and($response->body)->toMatch('#href="/_lego/Welcome/welcome.css\?v=[0-9a-f]+"#');
});

it('adds a trace id to every response', function () {
    $ok = app()->handle(Request::create('GET', '/'));
    $error = app()->handle(Request::create('GET', '/api/missing'));

    expect($ok->header('x-trace-id'))->toMatch('/^[0-9a-f]{16}$/')
        ->and($error->header('x-trace-id'))->toBe(json_decode($error->body, true)['trace_id']);
});

it('serves the welcome stylesheet it links to', function () {
    $response = app()->handle(Request::create('GET', '/_lego/Welcome/welcome.css'));

    expect($response->status)->toBe(200)->and($response->body)->toContain('.lego-welcome');
});

it('never serves framework PHP sources', function () {
    expect(app()->handle(Request::create('GET', '/_lego/Welcome/Welcome.php'))->status)->toBe(404);
});

it('returns a component as a fragment on the component layer', function () {
    $response = app()->handle(Request::create('GET', '/component/welcome'));

    expect($response->status)->toBe(200)
        ->and($response->body)->not->toContain('<!doctype')
        ->and($response->body)->toContain('class="lego-welcome"');
});

it('reports health with the status envelope', function () {
    $response = app()->handle(Request::create('GET', '/health'));

    expect($response->status)->toBe(200)
        ->and(json_decode($response->body, true))->toMatchArray(['status' => 'ok']);
});

it('shapes 404 errors by layer', function () {
    $api = app()->handle(Request::create('GET', '/api/missing'));
    $component = app()->handle(Request::create('GET', '/component/missing'));
    $page = app()->handle(Request::create('GET', '/missing'));
    $body = json_decode($api->body, true);

    expect($api->status)->toBe(404)
        ->and($body['status'])->toBe('error')
        ->and($body['error'])->toBe(['code' => 'not_found', 'message' => 'No encontramos lo que buscas.'])
        ->and($body)->toHaveKey('trace_id')
        ->and($component->status)->toBe(404)
        ->and($component->body)->toStartWith('<section class="lego-error" data-status="404" data-code="not_found" data-trace-id="')
        ->and($page->status)->toBe(404)
        ->and($page->body)->toStartWith('<!doctype html>');
});

it('answers 405 with an Allow header', function () {
    $response = app()->handle(Request::create('POST', '/health'));

    expect($response->status)->toBe(405)->and($response->header('allow'))->toBe('GET');
});

it('answers 400 to a malformed JSON body before routing', function () {
    $app = app();
    $app->router->post('/api/echo', fn (Request $r) => $r->body);
    $request = new Request('POST', '/api/echo', malformedBody: true);

    $response = $app->handle($request);

    expect($response->status)->toBe(400)
        ->and(json_decode($response->body, true)['error']['code'])->toBe('bad_request');
});

it('hides unexpected errors behind a reference code and logs them', function () {
    $log = fopen('php://memory', 'w+b');
    $app = app(log: $log);
    $app->router->get('/api/boom', fn () => throw new RuntimeException('database password is hunter2'));

    $response = $app->handle(Request::create('GET', '/api/boom'));
    $body = json_decode($response->body, true);
    rewind($log);
    $logged = json_decode(trim((string) stream_get_contents($log)), true);

    expect($response->status)->toBe(500)
        ->and($response->body)->not->toContain('hunter2')
        ->and($body['error']['code'])->toBe('internal_error')
        ->and($body['error']['message'])->toContain($body['trace_id'])
        ->and($body)->not->toHaveKey('debug')
        ->and($logged)->toMatchArray([
            'level' => 'error',
            'message' => 'database password is hunter2',
            'trace_id' => $body['trace_id'],
            'path' => '/api/boom',
        ]);
});

it('catches PHP errors, not only exceptions', function () {
    $app = app();
    $app->router->get('/api/type-error', fn () => strlen([]));

    expect($app->handle(Request::create('GET', '/api/type-error'))->status)->toBe(500);
});

it('shows error details only in debug mode', function () {
    $app = app(['APP_DEBUG' => 'true']);
    $app->router->get('/api/boom', fn () => throw new RuntimeException('detail'));
    $app->router->get('/component/boom', fn () => throw new RuntimeException('detail'));

    $api = json_decode($app->handle(Request::create('GET', '/api/boom'))->body, true);
    $fragment = $app->handle(Request::create('GET', '/component/boom'))->body;

    expect($api['debug']['message'])->toBe('detail')
        ->and($api['debug']['exception'])->toBe(RuntimeException::class)
        ->and($fragment)->toContain('<pre>');
});

it('keeps the public message of HTTP errors', function () {
    $app = app();
    $app->router->get('/api/bad', fn () => throw new BadRequest('Falta el parámetro <x>.'));
    $app->router->get('/component/bad', fn () => throw new BadRequest('Falta el parámetro <x>.'));

    $body = json_decode($app->handle(Request::create('GET', '/api/bad'))->body, true);
    $fragment = $app->handle(Request::create('GET', '/component/bad'))->body;

    expect($body['error'])->toBe(['code' => 'bad_request', 'message' => 'Falta el parámetro <x>.'])
        ->and($fragment)->toContain('Falta el parámetro &lt;x&gt;.');
});

it('converts handler return values into responses', function () {
    $app = app();
    $app->router->get('/api/array', fn () => ['a' => 1]);
    $app->router->get('/text', fn () => '<p>hola</p>');
    $app->router->get('/custom', fn () => Response::text('x', 201));
    $app->router->get('/invalid', fn () => 42);

    expect($app->handle(Request::create('GET', '/api/array'))->body)->toBe('{"a":1}')
        ->and($app->handle(Request::create('GET', '/text'))->body)->toBe('<p>hola</p>')
        ->and($app->handle(Request::create('GET', '/custom'))->status)->toBe(201)
        ->and($app->handle(Request::create('GET', '/invalid'))->status)->toBe(500);
});

it('passes route parameters to the handler', function () {
    $app = app();
    $app->router->get('/api/users/{id}', fn (Request $r) => ['id' => $r->param('id')]);

    expect($app->handle(Request::create('GET', '/api/users/42'))->body)->toBe('{"id":"42"}');
});

it('sends no body for HEAD requests', function () {
    $response = app()->handle(Request::create('HEAD', '/'));

    expect($response->status)->toBe(200)->and($response->body)->toBe('');
});
