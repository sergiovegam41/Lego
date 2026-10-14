<?php

declare(strict_types=1);

use Lego\Http\Request;
use Lego\Ui\Shell\Screen;
use Tests\Support\FakeGuard;

it('derives instance keys and DOM prefixes from paths', function (string $path, string $key, string $prefix) {
    expect(Screen::keyFor($path))->toBe($key)->and(Screen::domPrefix($key))->toBe($prefix);
})->with([
    ['/', 'home', 'lg-home-'],
    ['/component', 'home', 'lg-home-'],
    ['/clients', 'clients', 'lg-clients-'],
    ['/component/clients/5', 'clients/5', 'lg-clients-5-'],
    ['/sales-orders/new/', 'sales-orders/new', 'lg-sales-orders-new-'],
]);

it('renders the shell with the active screen, the import map and one module script', function () {
    $page = app(guard: FakeGuard::as())->handle(Request::create('GET', '/reports'))->body;

    preg_match('#<script type="importmap">(.*?)</script>#', $page, $map);
    $imports = json_decode($map[1] ?? '{}', true)['imports'] ?? [];

    expect($imports['lego'] ?? '')->toMatch('#^/_lego/js/lego\.js\?v=[0-9a-f]+$#')
        ->and($imports['lego/'] ?? '')->toBe('/_lego/js/')
        ->and(strpos($page, 'type="importmap"'))->toBeLessThan(strpos($page, '<script type="module"'))
        ->and(substr_count($page, '<script type="module"'))->toBe(1)
        ->and($page)->toContain('src="' . $imports['lego'] . '"')
        ->and($page)->toContain('data-lego-outlet')
        ->and($page)->toContain('data-lego-tabs')
        ->and($page)->toMatch('#<section class="lego-screen" data-lego-screen data-lego-key="reports" data-lego-url="/reports" data-lego-title="Informes" data-lego-module="reports">#')
        ->and($page)->toMatch('#<a href="/reports" data-lego-nav data-module="reports" aria-current="page">#');
});

it('returns the same screen root as a fragment, without the shell', function () {
    $response = app(guard: FakeGuard::as())->handle(Request::create('GET', '/component/reports?tab=2'));

    expect($response->body)->toStartWith('<section class="lego-screen" data-lego-screen data-lego-key="reports" data-lego-url="/reports?tab=2"')
        ->and($response->body)->not->toContain('lego-shell')
        ->and($response->body)->not->toContain('importmap')
        ->and($response->header('cache-control'))->toBe('no-store');
});

it('keeps static assets cacheable and everything else uncached', function () {
    $app = app(guard: FakeGuard::as());

    expect($app->handle(Request::create('GET', '/_lego/js/lego.js?v=1'))->header('cache-control'))->toBe('public, max-age=31536000, immutable')
        ->and($app->handle(Request::create('GET', '/_lego/js/ui/form.js'))->header('content-type'))->toBe('text/javascript; charset=utf-8')
        ->and($app->handle(Request::create('GET', '/'))->header('cache-control'))->toBe('no-store')
        ->and($app->handle(Request::create('GET', '/api/missing'))->header('cache-control'))->toBe('no-store');
});

it('answers 401 without redirecting when a fragment is requested without a session', function () {
    $response = app()->handle(Request::create('GET', '/component/clients/5'));

    expect($response->status)->toBe(401)->and($response->header('location'))->toBeNull();
});
