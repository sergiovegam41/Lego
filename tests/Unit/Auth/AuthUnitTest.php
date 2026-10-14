<?php

declare(strict_types=1);

use Lego\Audit\ActionLog;
use Lego\Auth\Gate;
use Lego\Auth\Identity;
use Lego\Auth\LoginController;
use Lego\Auth\Passwords;
use Lego\Auth\Permissions;
use Lego\Errors\Forbidden;
use Lego\Errors\Unauthenticated;
use Lego\Http\CookiePolicy;

it('grants permissions exactly, by module wildcard or globally', function (array $granted, string $permission, bool $expected) {
    expect(Permissions::grants($granted, $permission))->toBe($expected);
})->with([
    'exact' => [['clients.create'], 'clients.create', true],
    'other operation' => [['clients.create'], 'clients.delete', false],
    'module wildcard' => [['clients.*'], 'clients.delete', true],
    'other module' => [['clients.*'], 'orders.view', false],
    'global' => [['*'], 'orders.view', true],
    'prefix is not a wildcard' => [['client.*'], 'clients.view', false],
    'none' => [[], 'clients.view', false],
]);

it('rejects malformed permission names', function (string $name) {
    Permissions::grants(['*'], $name);
})->throws(InvalidArgumentException::class)->with(['clients', 'Clients.view', 'clients.view.extra', '*', '']);

it('authorizes through a single gate', function () {
    $gate = new Gate();
    $user = new Identity(1, 'Ana', 'ana@example.com', 'sales', ['clients.*']);

    $gate->authorize($user, 'clients.update', ['name' => 'x'], new stdClass());

    expect($gate->allows($user, 'orders.view'))->toBeFalse();
    expect(fn () => $gate->authorize($user, 'orders.view'))->toThrow(Forbidden::class);
    expect(fn () => $gate->authorize(null, 'clients.view'))->toThrow(Unauthenticated::class);
});

it('hashes and verifies passwords', function () {
    $hash = Passwords::hash('correct horse');

    expect(Passwords::verify('correct horse', $hash))->toBeTrue()
        ->and(Passwords::verify('wrong', $hash))->toBeFalse()
        ->and(Passwords::needsRehash($hash))->toBeFalse();
});

it('generates readable random passwords', function () {
    $a = Passwords::generate();
    $b = Passwords::generate();

    expect($a)->toHaveLength(20)->toMatch('/^[a-km-zA-HJ-NP-Z2-9]+$/')->and($a)->not->toBe($b);
});

it('only accepts local paths as the post-login destination', function (mixed $next, string $expected) {
    expect(LoginController::safeNext($next))->toBe($expected);
})->with([
    ['/clients?page=2', '/clients?page=2'],
    ['//evil.example', '/'],
    ['/\\evil.example', '/'],
    ['https://evil.example', '/'],
    ['clients', '/'],
    [null, '/'],
    [['/x'], '/'],
]);

it('builds cookies with safe defaults', function () {
    $http = new CookiePolicy(false);
    $https = CookiePolicy::fromAppUrl('https://lego.example');

    expect($http->make('lego_session', 'abc', 60))->toBe('lego_session=abc; Path=/; HttpOnly; SameSite=Lax; Max-Age=60')
        ->and($https->make('lego_session', 'abc'))->toBe('__Host-lego_session=abc; Path=/; HttpOnly; SameSite=Lax; Secure')
        ->and($http->forget('lego_session'))->toBe('lego_session=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0');
});

it('redacts secrets from audit input at any depth', function () {
    $redacted = ActionLog::redact([
        'email' => 'ana@example.com',
        'password' => 'x',
        'nested' => ['api_token' => 'y', 'name' => 'z'],
    ]);

    expect($redacted)->toBe([
        'email' => 'ana@example.com',
        'password' => '[redacted]',
        'nested' => ['api_token' => '[redacted]', 'name' => 'z'],
    ]);
});
