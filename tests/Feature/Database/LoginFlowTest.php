<?php

declare(strict_types=1);

use Lego\Auth\Installer;
use Lego\Http\Request;
use Tests\Support\Browser;

beforeEach(function () {
    (new Installer())->install('admin@example.com', 'Admin', 'admin-password');
    $this->browser = new Browser(dbApp());
});

it('signs in through the form and lands on the requested page', function () {
    $redirect = $this->browser->get('/');
    $form = $this->browser->get($redirect->header('location'));

    $login = $this->browser->post('/login', ['email' => 'admin@example.com', 'password' => 'admin-password', 'next' => '/?tab=1']);
    $home = $this->browser->get('/');

    expect($redirect->header('location'))->toBe('/login?next=%2F')
        ->and($form->body)->toContain('name="next" value="/"')
        ->and($login->status)->toBe(303)
        ->and($login->header('location'))->toBe('/?tab=1')
        ->and($this->browser->cookies)->toHaveKey('lego_session')
        ->and($home->status)->toBe(200)
        ->and($home->body)->toContain('Hola, Admin.');
});

it('shows the error and keeps the email after a wrong password', function () {
    $this->browser->get('/login');

    $response = $this->browser->post('/login', ['email' => 'admin@example.com', 'password' => 'nope']);

    expect($response->status)->toBe(401)
        ->and($response->body)->toContain('Correo o contraseña incorrectos.')
        ->and($response->body)->toContain('value="admin@example.com"')
        ->and($this->browser->cookies)->not->toHaveKey('lego_session');
});

it('refuses a login without the CSRF token', function () {
    $this->browser->get('/login');

    $response = $this->browser->post('/login', ['email' => 'admin@example.com', 'password' => 'admin-password'], withCsrf: false);

    expect($response->status)->toBe(403)->and($this->browser->cookies)->not->toHaveKey('lego_session');
});

it('never redirects to another host after login', function () {
    $this->browser->get('/login');

    $response = $this->browser->post('/login', ['email' => 'admin@example.com', 'password' => 'admin-password', 'next' => '//evil.example/x']);

    expect($response->header('location'))->toBe('/');
});

it('signs out, revokes the session server-side and clears the cookie', function () {
    $this->browser->get('/login');
    $this->browser->post('/login', ['email' => 'admin@example.com', 'password' => 'admin-password']);
    $stolen = $this->browser->cookies['lego_session'];

    $logout = $this->browser->post('/logout');
    $replay = dbApp()->handle(Request::create('GET', '/', cookies: ['lego_session' => $stolen]));

    expect($logout->status)->toBe(303)
        ->and($logout->header('location'))->toBe('/login')
        ->and($this->browser->cookies)->not->toHaveKey('lego_session')
        ->and($replay->status)->toBe(302)
        ->and(testDatabase()->connection()->table('action_log')->where('action', 'auth.logout')->count())->toBe(1);
});

it('sends signed-in users away from the login form', function () {
    $this->browser->get('/login');
    $this->browser->post('/login', ['email' => 'admin@example.com', 'password' => 'admin-password']);

    expect($this->browser->get('/login?next=/x')->header('location'))->toBe('/x');
});

it('reports a healthy database', function () {
    $response = dbApp()->handle(Request::create('GET', '/health'));

    expect($response->status)->toBe(200)
        ->and(json_decode($response->body, true))->toMatchArray(['status' => 'ok', 'database' => 'ok']);
});
