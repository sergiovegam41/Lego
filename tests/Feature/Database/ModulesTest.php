<?php

declare(strict_types=1);

use Lego\Application;
use Lego\Auth\Passwords;
use Lego\Auth\Role;
use Lego\Auth\User;
use Lego\Console\Console;
use Lego\Database\Migration;
use Lego\Environment;
use Lego\Http\Request;
use Lego\Http\Response;
use Lego\Log\Logger;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Fixtures\Modules\Clients\Client;
use Tests\Support\FakeGuard;

/** A real user (the audit log references it) and an app acting as that user. */
function actingAs(array $permissions, string $email = 'ana@example.com'): array
{
    $role = Role::query()->create(['name' => 'role_' . md5($email), 'label' => 'Rol', 'permissions' => $permissions]);
    $user = User::query()->create([
        'name' => 'Ana',
        'email' => $email,
        'password_hash' => Passwords::hash('secret-123'),
        'role_id' => $role->id,
    ]);
    $identity = $user->load('role')->toIdentity();
    $app = new Application(
        basePath(),
        new Environment(['APP_DEBUG' => 'false', 'APP_URL' => 'http://localhost']),
        new Logger(fopen('php://memory', 'w+b')),
        testDatabase(),
        new FakeGuard($identity),
        modulesPath: fixture('Modules'),
        modulesNamespace: 'Tests\\Fixtures\\Modules\\',
    );

    return [$app, $identity];
}

/** @return array<string, mixed> */
function api(Application $app, string $action, array $input = []): array
{
    $response = $app->handle(csrfRequest('POST', '/api/actions/' . $action, $input));

    return [...json_decode($response->body, true), 'http' => $response->status];
}

function form(Application $app, string $uri, array $form = []): Response
{
    $token = str_repeat('ab', 32);

    return $app->handle(Request::create('POST', $uri, ['_csrf' => $token, ...$form], cookies: ['lego_csrf' => $token]));
}

function auditVia(string $action): array
{
    return testDatabase()->connection()->table('action_log')->where('action', $action)->where('status', 'ok')->orderBy('id')->pluck('via')->all();
}

function newClient(array $data = []): Client
{
    $client = new Client();
    $client->forceFill([...['name' => 'Florería Lima', 'email' => 'ventas@lima.pe', 'active' => true], ...$data])->save();

    return $client->refresh();
}

describe('JSON API', function () {
    it('creates a record, answers 201 and audits it', function () {
        [$app] = actingAs(['clients.*']);

        $result = api($app, 'clients_create', ['name' => ' Florería Lima ', 'email' => 'Ventas@Lima.pe', 'credit' => '1500,5', 'tier' => 'gold']);

        expect($result['http'])->toBe(201)
            ->and($result['data'])->toMatchArray(['name' => 'Florería Lima', 'email' => 'ventas@lima.pe', 'credit' => '1500.50', 'tier' => 'gold'])
            ->and($result['ui'])->toBe(['focus' => ['module' => 'clients', 'id' => $result['data']['id']]])
            ->and(auditVia('clients_create'))->toBe(['api']);
    });

    it('returns every field error at once', function () {
        [$app] = actingAs(['clients.*']);
        newClient();

        $result = api($app, 'clients_create', ['email' => 'VENTAS@lima.pe', 'credit' => '-5', 'tier' => 'platinum', 'color' => 'red']);

        expect($result['http'])->toBe(422)
            ->and($result['error']['code'])->toBe('validation_failed')
            ->and(array_keys($result['error']['fields']))->toEqualCanonicalizing(['color', 'name', 'email', 'credit', 'tier'])
            ->and($result['error']['fields']['email'][0]['rule'])->toBe('unique')
            ->and($result['error']['fields']['color'][0]['rule'])->toBe('unknown_field');
    });

    it('lists with search, sort and pagination', function () {
        [$app] = actingAs(['clients.view']);
        foreach (['Ana', 'Beto', 'Carla', 'Ana María'] as $i => $name) {
            newClient(['name' => $name, 'email' => "c{$i}@example.com"]);
        }

        $result = api($app, 'clients_list', ['q' => 'ana', 'sort' => 'name', 'dir' => 'asc', 'per_page' => 1, 'page' => 2]);

        expect($result['http'])->toBe(200)
            ->and(array_column($result['data']['items'], 'name'))->toBe(['Ana María'])
            ->and($result['data']['meta'])->toMatchArray(['total' => 2, 'pages' => 2, 'page' => 2]);
    });

    it('updates only the fields sent', function () {
        [$app] = actingAs(['clients.*']);
        $client = newClient(['credit' => '10.00']);

        $result = api($app, 'clients_update', ['id' => $client->id, 'credit' => 99]);

        expect($result['data'])->toMatchArray(['name' => 'Florería Lima', 'credit' => '99.00']);
    });

    it('deletes in two steps with a single-use token bound to the record', function () {
        [$app] = actingAs(['clients.*']);
        $client = newClient();
        $other = newClient(['email' => 'otro@lima.pe']);

        $step1 = api($app, 'clients_delete', ['id' => $client->id]);
        $token = $step1['confirmation']['token'];
        $wrongRecord = api($app, 'clients_delete', ['id' => $other->id, 'confirmation_token' => $token]);
        $step2 = api($app, 'clients_delete', ['id' => $client->id, 'confirmation_token' => $token]);
        $replay = api($app, 'clients_delete', ['id' => $other->id, 'confirmation_token' => $token]);

        expect($step1['http'])->toBe(428)
            ->and($step1['confirmation']['message'])->toBe('Se eliminará «Florería Lima». Esta acción no se puede deshacer.')
            ->and($wrongRecord['http'])->toBe(409)
            ->and($wrongRecord['error']['code'])->toBe('confirmation_invalid')
            ->and($step2['http'])->toBe(200)
            ->and(Client::query()->find($client->id))->toBeNull()
            ->and($replay['http'])->toBe(409)
            ->and(Client::query()->find($other->id))->not->toBeNull();
    });

    it('refuses and audits actions without permission', function () {
        [$app] = actingAs(['clients.view']);

        $result = api($app, 'clients_create', ['name' => 'X', 'email' => 'x@example.com']);
        $denied = testDatabase()->connection()->table('action_log')->where('action', 'clients_create')->value('status');

        expect($result['http'])->toBe(403)->and($denied)->toBe('denied')->and(Client::query()->count())->toBe(0);
    });

    it('runs custom actions with their own rules', function () {
        [$app] = actingAs(['clients.*']);
        $client = newClient(['visits' => 2]);

        $visit = api($app, 'clients_register_visit', ['id' => $client->id, 'times' => '3']);
        $badVisit = api($app, 'clients_register_visit', ['id' => $client->id, 'times' => 'many']);
        $block = api($app, 'clients_block', ['id' => $client->id, 'reason' => 'Impago']);
        $blocked = api($app, 'clients_block', ['id' => $client->id, 'reason' => 'Impago', 'confirmation_token' => $block['confirmation']['token']]);
        $again = api($app, 'clients_block', ['id' => $client->id, 'reason' => 'Otra vez']);
        $count = api($app, 'clients_count_active');

        expect($visit['data'])->toBe(5)
            ->and($badVisit['error']['fields']['times'][0]['rule'])->toBe('type')
            ->and($block['http'])->toBe(428)
            ->and($blocked['data'])->toBe(['blocked' => true])
            ->and($client->refresh()->active)->toBeFalse()
            ->and($again['http'])->toBe(428);

        $confirmedAgain = api($app, 'clients_block', ['id' => $client->id, 'reason' => 'Otra vez', 'confirmation_token' => $again['confirmation']['token']]);

        expect($confirmedAgain['http'])->toBe(409)
            ->and($confirmedAgain['error']['code'])->toBe('client_already_blocked')
            ->and($count['data'])->toBe(0);
    });

    it('serves the catalog of allowed actions', function () {
        [$app] = actingAs(['clients.view']);

        $response = $app->handle(Request::create('GET', '/api/actions'));
        $names = array_column(json_decode($response->body, true)['data'], 'name');

        expect($names)->toBe(['clients_list', 'clients_get']);
    });

    it('answers 404 for unknown actions and records', function () {
        [$app] = actingAs(['clients.*']);

        expect(api($app, 'clients_nope')['http'])->toBe(404)
            ->and(api($app, 'clients_get', ['id' => 999])['http'])->toBe(404)
            ->and(api($app, 'clients_get')['http'])->toBe(422);
    });
});

describe('screens', function () {
    it('lists records with the action catalog embedded', function () {
        [$app] = actingAs(['clients.*']);
        newClient(['credit' => '1234.50']);

        $page = $app->handle(Request::create('GET', '/clients'));
        $fragment = $app->handle(Request::create('GET', '/component/clients'));

        expect($page->status)->toBe(200)
            ->and($page->body)->toContain('class="lego-layout"')
            ->and($page->body)->toContain('Florería Lima')
            ->and($page->body)->toContain('1.234,50')
            ->and($page->body)->toContain('data-lego-actions="clients"')
            ->and($page->body)->toContain('href="/clients/new"')
            ->and($fragment->body)->not->toContain('lego-layout')
            ->and($fragment->body)->toContain('Florería Lima');
    });

    it('creates through the form and shows field errors', function () {
        [$app] = actingAs(['clients.*']);

        $invalid = form($app, '/clients/new', ['name' => '', 'email' => 'nope', 'active' => '0']);
        $valid = form($app, '/clients/new', ['name' => 'Beto', 'email' => 'beto@example.com', 'active' => '1', 'since' => '2026-01-31']);

        expect($invalid->status)->toBe(422)
            ->and($invalid->body)->toContain('Nombre es obligatorio.')
            ->and($invalid->body)->toContain('Introduce un correo válido.')
            ->and($invalid->body)->toContain('value="nope"')
            ->and($valid->status)->toBe(303)
            ->and($valid->header('location'))->toBe('/clients')
            ->and(Client::query()->where('email', 'beto@example.com')->value('since'))->toBe('2026-01-31')
            ->and(auditVia('clients_create'))->toBe(['ui']);
    });

    it('edits, unchecks a boolean and deletes with confirmation', function () {
        [$app] = actingAs(['clients.*']);
        $client = newClient();

        $edit = $app->handle(Request::create('GET', '/clients/' . $client->id));
        $update = form($app, '/clients/' . $client->id, ['name' => 'Lima SAC', 'email' => 'ventas@lima.pe', 'active' => '0']);
        $afterUpdate = $client->refresh()->only(['name', 'active']);
        $confirm = form($app, '/clients/' . $client->id . '/delete');
        preg_match('/name="confirmation_token" value="([0-9a-f]{64})"/', $confirm->body, $m);
        $delete = form($app, '/clients/' . $client->id . '/delete', ['confirmation_token' => $m[1]]);

        expect($edit->body)->toContain('value="Florería Lima"')
            ->and($edit->body)->toContain('data-action="clients_block"')
            ->and($update->status)->toBe(303)
            ->and($afterUpdate)->toBe(['name' => 'Lima SAC', 'active' => false])
            ->and($confirm->body)->toContain('Se eliminará «Lima SAC».')
            ->and($delete->status)->toBe(303)
            ->and(Client::query()->count())->toBe(0);
    });

    it('shows a read-only form without update permission', function () {
        [$app] = actingAs(['clients.view']);
        $client = newClient();

        $edit = $app->handle(Request::create('GET', '/clients/' . $client->id));

        expect($edit->status)->toBe(200)
            ->and($edit->body)->toContain('disabled')
            ->and($edit->body)->not->toContain('>Guardar<')
            ->and($edit->body)->not->toContain('>Eliminar<');
    });
});

describe('parity', function () {
    it('runs the same action from the UI, the API and the CLI', function () {
        [$app, $identity] = actingAs(['clients.*']);

        form($app, '/clients/new', ['name' => 'Desde UI', 'email' => 'ui@example.com']);
        api($app, 'clients_create', ['name' => 'Desde API', 'email' => 'api@example.com']);
        $cli = new CommandTester(Console::create($app)->find('run'));
        $exit = $cli->execute(['action' => 'clients_create', '--as' => $identity->email, '--set' => ['name=Desde CLI', 'email=cli@example.com']]);

        expect($exit)->toBe(0)
            ->and(json_decode($cli->getDisplay(), true)['data']['name'])->toBe('Desde CLI')
            ->and(auditVia('clients_create'))->toBe(['ui', 'api', 'cli'])
            ->and(Client::query()->orderBy('id')->pluck('name')->all())->toBe(['Desde UI', 'Desde API', 'Desde CLI']);
    });

    it('confirms destructive actions on the CLI only with --yes', function () {
        [$app, $identity] = actingAs(['clients.*']);
        $client = newClient();
        $cli = new CommandTester(Console::create($app)->find('run'));

        $asked = $cli->execute(['action' => 'clients_delete', '--as' => $identity->email, '--set' => ['id=' . $client->id]]);
        $done = $cli->execute(['action' => 'clients_delete', '--as' => $identity->email, '--set' => ['id=' . $client->id], '--yes' => true]);

        expect($asked)->toBe(2)->and($done)->toBe(0)->and(Client::query()->count())->toBe(0);
    });

    it('refuses CLI actions the user may not run', function () {
        [$app, $identity] = actingAs(['clients.view']);
        $cli = new CommandTester(Console::create($app)->find('run'));

        $exit = $cli->execute(['action' => 'clients_create', '--as' => $identity->email, '--input' => '{"name":"X","email":"x@example.com"}']);

        expect($exit)->toBe(1)->and(json_decode($cli->getDisplay(), true)['error']['code'])->toBe('forbidden');
    });
});

it('scaffolds a module that lints and migrates', function () {
    $dir = sys_get_temp_dir() . '/lego-make-' . bin2hex(random_bytes(4));
    mkdir($dir);
    $app = new Application(basePath(), new Environment([]), new Logger(fopen('php://memory', 'w+b')), testDatabase(), modulesPath: $dir);

    $tester = new CommandTester(Console::create($app)->find('make:module'));
    $exit = $tester->execute(['name' => 'SalesOrders', '--label' => 'Pedidos']);
    $record = "{$dir}/SalesOrders/SalesOrder.php";
    $migrations = glob("{$dir}/SalesOrders/migrations/*_create_sales_orders_table.php");
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($record), $lint, $lintExit);
    $migration = require $migrations[0];
    $migration->up(testDatabase()->schema(), testDatabase()->connection());

    expect($exit)->toBe(0)
        ->and(file_get_contents($record))->toContain("#[Module(label: 'Pedidos')]")
        ->and(file_get_contents($record))->toContain('final class SalesOrder extends Record')
        ->and(file_get_contents($record))->toContain("protected \$table = 'sales_orders';")
        ->and($lintExit)->toBe(0)
        ->and($migration)->toBeInstanceOf(Migration::class)
        ->and(testDatabase()->schema()->hasTable('sales_orders'))->toBeTrue()
        ->and($tester->execute(['name' => 'SalesOrders']))->toBe(1);

    array_map('unlink', [$record, $migrations[0]]);
    rmdir("{$dir}/SalesOrders/migrations");
    rmdir("{$dir}/SalesOrders");
    rmdir($dir);
});
