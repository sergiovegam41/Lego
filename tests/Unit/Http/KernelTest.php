<?php

declare(strict_types=1);

use Lego\Errors\BadRequest;
use Lego\Http\Request;
use Lego\Http\Response;
use Tests\Support\FakeGuard;

it('redirects anonymous page requests to the login with the original path', function () {
    $response = app()->handle(Request::create('GET', '/?tab=2'));

    expect($response->status)->toBe(302)
        ->and($response->header('location'))->toBe('/login?next=%2F%3Ftab%3D2');
});

it('renders the welcome page for a signed-in user', function () {
    $response = app(guard: FakeGuard::as(name: 'Ana <b>'))->handle(Request::create('GET', '/'));

    expect($response->status)->toBe(200)
        ->and($response->body)->toStartWith('<!doctype html>')
        ->and($response->body)->toContain('Hola, Ana &lt;b&gt;.')
        ->and($response->body)->toMatch('#href="/_lego/Welcome/welcome.css\?v=[0-9a-f]+"#')
        ->and($response->body)->toMatch('#<meta name="csrf-token" content="[0-9a-f]{64}">#');
});

it('answers 401 to anonymous fragments and API calls', function () {
    $app = app();
    $app->router->get('/api/private', fn () => ['secret' => true]);

    $fragment = $app->handle(Request::create('GET', '/component/welcome'));
    $api = $app->handle(Request::create('GET', '/api/private'));

    expect($fragment->status)->toBe(401)
        ->and($fragment->body)->toContain('data-code="unauthenticated"')
        ->and($api->status)->toBe(401)
        ->and(json_decode($api->body, true)['error']['code'])->toBe('unauthenticated');
});

it('enforces route permissions through the gate', function () {
    $register = function ($app) {
        $app->router->get('/api/clients', fn () => ['ok' => true])->can('clients.view');

        return $app;
    };

    $denied = $register(app(guard: FakeGuard::as(['orders.*'])))->handle(Request::create('GET', '/api/clients'));
    $allowed = $register(app(guard: FakeGuard::as(['clients.*'])))->handle(Request::create('GET', '/api/clients'));

    expect($denied->status)->toBe(403)
        ->and(json_decode($denied->body, true)['error']['code'])->toBe('forbidden')
        ->and($allowed->status)->toBe(200);
});

it('issues a CSRF cookie once and reuses the one the browser sends', function () {
    $first = app()->handle(Request::create('GET', '/login'));
    $token = cookiesFrom($first)['lego_csrf'] ?? null;
    $second = app()->handle(Request::create('GET', '/login', cookies: ['lego_csrf' => $token]));

    expect($token)->toMatch('/^[0-9a-f]{64}$/')
        ->and($first->cookies()[0])->toContain('HttpOnly')
        ->and($first->cookies()[0])->toContain('SameSite=Lax')
        ->and($first->body)->toContain('value="' . $token . '"')
        ->and($second->cookies())->toBe([]);
});

it('rejects unsafe requests without a matching CSRF token', function (Request $request) {
    $app = app(guard: FakeGuard::as());
    $app->router->post('/api/things', fn () => ['ok' => true]);

    $response = $app->handle($request);

    expect($response->status)->toBe(403)
        ->and(json_decode($response->body, true)['error']['code'])->toBe('csrf_mismatch');
})->with([
    'no token' => fn () => Request::create('POST', '/api/things'),
    'header without cookie' => fn () => Request::create('POST', '/api/things', headers: ['X-CSRF-Token' => str_repeat('ab', 32)]),
    'wrong token' => fn () => Request::create('POST', '/api/things', headers: ['X-CSRF-Token' => str_repeat('cd', 32)], cookies: ['lego_csrf' => str_repeat('ab', 32)]),
    'other origin' => fn () => csrfRequest('POST', '/api/things', headers: ['Origin' => 'https://evil.example', 'Host' => 'lego.test']),
    'opaque origin' => fn () => csrfRequest('POST', '/api/things', headers: ['Origin' => 'null']),
]);

it('accepts unsafe requests with a matching CSRF token in header or form field', function () {
    $app = app(guard: FakeGuard::as());
    $app->router->post('/api/things', fn () => ['ok' => true]);
    $token = str_repeat('ab', 32);

    $header = $app->handle(csrfRequest('POST', '/api/things', headers: ['Origin' => 'http://lego.test', 'Host' => 'lego.test']));
    $field = $app->handle(Request::create('POST', '/api/things', ['_csrf' => $token], cookies: ['lego_csrf' => $token]));

    expect($header->status)->toBe(200)->and($field->status)->toBe(200);
});

it('uses Secure __Host- cookies when the app runs on HTTPS', function () {
    $response = app(['APP_URL' => 'https://lego.example'])->handle(Request::create('GET', '/login'));

    expect($response->cookies()[0])->toStartWith('__Host-lego_csrf=')
        ->and($response->cookies()[0])->toContain('; Secure');
});

it('adds security headers and a trace id to every response', function () {
    $ok = app()->handle(Request::create('GET', '/login'));
    $error = app()->handle(Request::create('GET', '/api/missing'));

    expect($ok->header('x-content-type-options'))->toBe('nosniff')
        ->and($ok->header('x-frame-options'))->toBe('DENY')
        ->and($ok->header('referrer-policy'))->toBe('same-origin')
        ->and($ok->header('x-trace-id'))->toMatch('/^[0-9a-f]{16}$/')
        ->and($error->header('x-trace-id'))->toBe(json_decode($error->body, true)['trace_id']);
});

it('serves framework assets without a session', function () {
    $css = app()->handle(Request::create('GET', '/_lego/Welcome/welcome.css'));
    $php = app()->handle(Request::create('GET', '/_lego/Welcome/Welcome.php'));

    expect($css->status)->toBe(200)->and($css->body)->toContain('.lego-welcome')
        ->and($php->status)->toBe(404);
});

it('returns a component as a fragment on the component layer', function () {
    $response = app(guard: FakeGuard::as())->handle(Request::create('GET', '/component/welcome'));

    expect($response->status)->toBe(200)
        ->and($response->body)->not->toContain('<!doctype')
        ->and($response->body)->toContain('class="lego-welcome"');
});

it('reports an unreachable database on the health check', function () {
    $response = app()->handle(Request::create('GET', '/health'));

    expect($response->status)->toBe(503)
        ->and(json_decode($response->body, true)['error']['code'])->toBe('database_unavailable');
});

it('shapes 404 errors by layer', function () {
    $api = app()->handle(Request::create('GET', '/api/missing'));
    $component = app()->handle(Request::create('GET', '/component/missing'));
    $page = app()->handle(Request::create('GET', '/missing'));
    $body = json_decode($api->body, true);

    expect($api->status)->toBe(404)
        ->and($body['status'])->toBe('error')
        ->and($body['error'])->toBe(['code' => 'not_found', 'message' => 'No encontramos lo que buscas.'])
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
    $app->router->post('/api/echo', fn (Request $r) => $r->body)->public();

    $response = $app->handle(new Request('POST', '/api/echo', malformedBody: true));

    expect($response->status)->toBe(400)
        ->and(json_decode($response->body, true)['error']['code'])->toBe('bad_request');
});

it('hides unexpected errors behind a reference code and logs them', function () {
    $log = fopen('php://memory', 'w+b');
    $app = app(log: $log);
    $app->router->get('/api/boom', fn () => throw new RuntimeException('database password is hunter2'))->public();

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
    $app->router->get('/api/type-error', fn () => strlen([]))->public();

    expect($app->handle(Request::create('GET', '/api/type-error'))->status)->toBe(500);
});

it('shows error details only in debug mode', function () {
    $app = app(['APP_DEBUG' => 'true']);
    $app->router->get('/api/boom', fn () => throw new RuntimeException('detail'))->public();
    $app->router->get('/component/boom', fn () => throw new RuntimeException('detail'))->public();

    $api = json_decode($app->handle(Request::create('GET', '/api/boom'))->body, true);
    $fragment = $app->handle(Request::create('GET', '/component/boom'))->body;

    expect($api['debug']['message'])->toBe('detail')
        ->and($api['debug']['exception'])->toBe(RuntimeException::class)
        ->and($fragment)->toContain('<pre>');
});

it('keeps the public message of HTTP errors', function () {
    $app = app();
    $app->router->get('/api/bad', fn () => throw new BadRequest('Falta el parámetro <x>.'))->public();
    $app->router->get('/component/bad', fn () => throw new BadRequest('Falta el parámetro <x>.'))->public();

    $body = json_decode($app->handle(Request::create('GET', '/api/bad'))->body, true);
    $fragment = $app->handle(Request::create('GET', '/component/bad'))->body;

    expect($body['error'])->toBe(['code' => 'bad_request', 'message' => 'Falta el parámetro <x>.'])
        ->and($fragment)->toContain('Falta el parámetro &lt;x&gt;.');
});

it('converts handler return values into responses', function () {
    $app = app();
    $app->router->get('/api/array', fn () => ['a' => 1])->public();
    $app->router->get('/text', fn () => '<p>hola</p>')->public();
    $app->router->get('/custom', fn () => Response::text('x', 201))->public();
    $app->router->get('/invalid', fn () => 42)->public();

    expect($app->handle(Request::create('GET', '/api/array'))->body)->toBe('{"a":1}')
        ->and($app->handle(Request::create('GET', '/text'))->body)->toBe('<p>hola</p>')
        ->and($app->handle(Request::create('GET', '/custom'))->status)->toBe(201)
        ->and($app->handle(Request::create('GET', '/invalid'))->status)->toBe(500);
});

it('passes route parameters to the handler', function () {
    $app = app(guard: FakeGuard::as());
    $app->router->get('/api/users/{id}', fn (Request $r) => ['id' => $r->param('id')]);

    expect($app->handle(Request::create('GET', '/api/users/42'))->body)->toBe('{"id":"42"}');
});

it('sends no body for HEAD requests', function () {
    $response = app()->handle(Request::create('HEAD', '/login'));

    expect($response->status)->toBe(200)->and($response->body)->toBe('');
});
