<?php

declare(strict_types=1);

use Lego\Application;
use Lego\Auth\Installer;
use Lego\Environment;
use Lego\Http\Request;
use Lego\Log\Logger;
use Lego\Modules\Roles\Role;
use Lego\Modules\Users\User;
use Tests\Support\Browser;
use Tests\Support\FakeGuard;

/** App acting as the installed administrator (role "admin" with "*"). */
function asAdmin(): array
{
    (new Installer())->install('admin@example.com', 'Admin', 'admin-password');
    $admin = User::findByEmail('admin@example.com')->load('role');
    $app = new Application(
        basePath(),
        new Environment(['APP_DEBUG' => 'false', 'APP_URL' => 'http://localhost']),
        new Logger(fopen('php://memory', 'w+b')),
        testDatabase(),
        new FakeGuard($admin->toIdentity()),
        modulesPath: fixture('Modules'),
        modulesNamespace: 'Tests\\Fixtures\\Modules\\',
    );

    return [$app, $admin];
}

function run(Application $app, string $action, array $input = []): array
{
    $response = $app->handle(csrfRequest('POST', '/api/actions/' . $action, $input));

    return [...json_decode($response->body, true), 'http' => $response->status];
}

/** Runs a confirmable action through both steps. */
function confirmed(Application $app, string $action, array $input): array
{
    $first = run($app, $action, $input);

    return $first['http'] === 428 ? run($app, $action, [...$input, 'confirmation_token' => $first['confirmation']['token']]) : $first;
}

describe('roles', function () {
    it('grants only permissions from the catalog', function () {
        [$app] = asAdmin();

        $valid = run($app, 'roles_create', ['name' => 'sales', 'label' => 'Ventas', 'permissions' => ['clients.view', 'clients.create']]);
        $invalid = run($app, 'roles_create', ['name' => 'Bad Name', 'label' => 'Mal', 'permissions' => ['clients.view', 'bank.rob']]);

        expect($valid['http'])->toBe(201)
            ->and($valid['data']['permissions'])->toBe(['clients.create', 'clients.view'])
            ->and($invalid['http'])->toBe(422)
            ->and($invalid['error']['fields']['name'][0]['rule'])->toBe('pattern')
            ->and($invalid['error']['fields']['permissions'][0]['message'])->toContain('bank.rob');
    });

    it('protects the admin role and roles in use', function () {
        [$app, $admin] = asAdmin();
        $sales = Role::query()->create(['name' => 'sales', 'label' => 'Ventas', 'permissions' => ['clients.view']]);
        User::query()->create(['name' => 'Ana', 'email' => 'ana@example.com', 'password_hash' => '', 'role_id' => $sales->id]);

        $dropStar = run($app, 'roles_update', ['id' => $admin->role_id, 'permissions' => ['clients.view']]);
        $rename = run($app, 'roles_update', ['id' => $admin->role_id, 'name' => 'boss']);
        $deleteAdmin = confirmed($app, 'roles_delete', ['id' => $admin->role_id]);
        $deleteInUse = confirmed($app, 'roles_delete', ['id' => $sales->id]);

        expect($dropStar['error']['code'])->toBe('role_locked')
            ->and($rename['error']['code'])->toBe('role_locked')
            ->and($deleteAdmin['error']['code'])->toBe('role_locked')
            ->and($deleteInUse['http'])->toBe(409)
            ->and($deleteInUse['error']['message'])->toBe('No se puede eliminar: lo usan 1 usuarios.');
    });

    it('renders permissions as checkboxes and saves them from a plain form', function () {
        [$app] = asAdmin();
        $role = Role::query()->create(['name' => 'sales', 'label' => 'Ventas', 'permissions' => ['clients.view']]);
        $token = str_repeat('ab', 32);

        $form = $app->handle(Request::create('GET', '/roles/' . $role->id))->body;
        $save = $app->handle(Request::create('POST', '/roles/' . $role->id, [
            '_csrf' => $token, 'name' => 'sales', 'label' => 'Ventas', 'permissions' => ['', 'clients.view', 'clients.update'],
        ], cookies: ['lego_csrf' => $token]));

        expect($form)->toContain('name="permissions[]" value="clients.view" checked')
            ->and($form)->toContain('Clientes: bloquear')
            ->and($save->status)->toBe(303)
            ->and($role->refresh()->permissions)->toBe(['clients.update', 'clients.view']);
    });
});

describe('users', function () {
    it('creates a user who cannot sign in until a password is reset', function () {
        [$app] = asAdmin();
        $sales = Role::query()->create(['name' => 'sales', 'label' => 'Ventas', 'permissions' => ['clients.view']]);

        $created = run($app, 'users_create', ['name' => 'Beto', 'email' => 'Beto@Example.com', 'role_id' => (string) $sales->id]);
        $browser = new Browser(dbApp());
        $browser->get('/login');
        $before = $browser->post('/login', ['email' => 'beto@example.com', 'password' => '']);
        $reset = confirmed($app, 'users_reset_password', ['id' => $created['data']['id']]);
        $after = $browser->post('/login', ['email' => 'beto@example.com', 'password' => $reset['data']['password']]);

        expect($created['http'])->toBe(201)
            ->and($created['data'])->toMatchArray(['email' => 'beto@example.com', 'role_id' => $sales->id, 'is_active' => true])
            ->and($created['data'])->not->toHaveKey('password_hash')
            ->and($before->status)->toBe(401)
            ->and($reset['data']['message'])->toContain($reset['data']['password'])
            ->and($after->status)->toBe(303);
    });

    it('closes the sessions of a user whose password is reset', function () {
        [$app] = asAdmin();
        $sales = Role::query()->create(['name' => 'sales', 'label' => 'Ventas', 'permissions' => ['clients.view']]);
        $user = User::query()->create(['name' => 'Ana', 'email' => 'ana@example.com', 'password_hash' => '', 'role_id' => $sales->id]);
        $token = dbApp()->sessions->start($user);

        confirmed($app, 'users_reset_password', ['id' => $user->id]);

        expect(dbApp()->sessions->find($token))->toBeNull();
    });

    it('rejects a role that does not exist', function () {
        [$app] = asAdmin();

        $result = run($app, 'users_create', ['name' => 'X', 'email' => 'x@example.com', 'role_id' => '999']);

        expect($result['error']['fields']['role_id'][0]['rule'])->toBe('in');
    });

    it('does not let administrators lock themselves out', function () {
        [$app, $admin] = asAdmin();
        $other = Role::query()->create(['name' => 'sales', 'label' => 'Ventas', 'permissions' => []]);

        $deactivate = run($app, 'users_update', ['id' => $admin->id, 'is_active' => false]);
        $demote = run($app, 'users_update', ['id' => $admin->id, 'role_id' => (string) $other->id]);
        $delete = confirmed($app, 'users_delete', ['id' => $admin->id]);
        $rename = run($app, 'users_update', ['id' => $admin->id, 'name' => 'Administradora']);

        expect($deactivate['error']['code'])->toBe('cannot_deactivate_self')
            ->and($demote['error']['code'])->toBe('cannot_change_own_role')
            ->and($delete['error']['code'])->toBe('cannot_delete_self')
            ->and($rename['http'])->toBe(200)
            ->and($admin->refresh()->is_active)->toBeTrue();
    });

    it('shows the role label in the list and the generated password without JavaScript', function () {
        [$app] = asAdmin();
        $token = str_repeat('ab', 32);
        $user = User::findByEmail('admin@example.com');

        $list = $app->handle(Request::create('GET', '/users'))->body;
        $step1 = $app->handle(Request::create('POST', "/users/{$user->id}/actions/reset_password", ['_csrf' => $token], cookies: ['lego_csrf' => $token]))->body;
        preg_match('/name="confirmation_token" value="([0-9a-f]{64})"/', $step1, $m);
        $step2 = $app->handle(Request::create('POST', "/users/{$user->id}/actions/reset_password", ['_csrf' => $token, 'confirmation_token' => $m[1]], cookies: ['lego_csrf' => $token]))->body;

        expect($list)->toContain('<td>Administrador</td>')
            ->and($list)->not->toContain('password_hash')
            ->and($step2)->toContain('Contraseña nueva para admin@example.com:');
    });
});
