<?php

declare(strict_types=1);

use Lego\Action\ActionRegistry;
use Lego\Action\AgentPolicy;
use Lego\Http\Request;
use Lego\Http\SecurityHeaders;
use Lego\Module\ModuleRegistry;
use Tests\Support\FakeGuard;

it('sends a strict CSP whose nonce matches the import map of the page', function () {
    $response = app(guard: FakeGuard::as())->handle(Request::create('GET', '/'));
    $csp = $response->header('content-security-policy');
    preg_match("/'nonce-([^']+)'/", (string) $csp, $nonce);

    expect($csp)->toContain("default-src 'self'")
        ->and($csp)->toContain("style-src 'self'")
        ->and($csp)->not->toContain('unsafe-inline')
        ->and($csp)->toContain("frame-ancestors 'none'")
        ->and($response->body)->toContain('<script type="importmap" nonce="' . $nonce[1] . '">');
});

it('uses a new nonce on every response', function () {
    $a = app()->handle(Request::create('GET', '/login'))->header('content-security-policy');
    $b = app()->handle(Request::create('GET', '/login'))->header('content-security-policy');

    expect($a)->not->toBe($b);
});

it('keeps WebMCP tools off unless agents are enabled', function () {
    $off = app()->handle(Request::create('GET', '/login'))->header('permissions-policy');
    $on = app(['LEGO_AGENT_ACCESS' => 'read'])->handle(Request::create('GET', '/login'))->header('permissions-policy');

    expect($off)->toStartWith('tools=()')
        ->and($off)->toContain('camera=()')
        ->and($on)->toStartWith('tools=(self)');
});

it('adds HSTS and COOP only on HTTPS', function () {
    expect((new SecurityHeaders(https: false))->for('n'))->not->toHaveKey('strict-transport-security')
        ->and((new SecurityHeaders(https: false))->for('n'))->not->toHaveKey('cross-origin-opener-policy')
        ->and((new SecurityHeaders(https: true))->for('n'))->toMatchArray([
            'strict-transport-security' => 'max-age=31536000; includeSubDomains',
            'cross-origin-opener-policy' => 'same-origin',
        ]);
});

it('lets agents run nothing, read-only actions or opted-in writes', function (string $access, array $expected) {
    $modules = new ModuleRegistry([fixture('Modules') => 'Tests\\Fixtures\\Modules\\', fixture('AgentModules') => 'Tests\\Fixtures\\AgentModules\\']);
    $policy = new AgentPolicy($access, $modules);
    $allowed = array_keys(array_filter((new ActionRegistry($modules))->all(), $policy->allows(...)));

    expect(array_values(array_intersect($allowed, ['clients_list', 'clients_create', 'notes_list', 'notes_create'])))->toBe($expected);
})->with([
    'off' => ['off', []],
    'read' => ['read', ['clients_list', 'notes_list']],
    'write' => ['write', ['clients_list', 'notes_list', 'notes_create']],
]);

it('rejects an unknown agent access level', function () {
    new AgentPolicy('everything', new ModuleRegistry([]));
})->throws(InvalidArgumentException::class);
