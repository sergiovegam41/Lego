<?php

declare(strict_types=1);

use Lego\Action\ActionDefinition;
use Lego\Action\ActionRegistry;
use Lego\Auth\Gate;
use Lego\Auth\Identity;
use Lego\Http\Request;
use Lego\Module\Menu;
use Lego\Module\ModuleRegistry;
use Tests\Fixtures\Modules\Clients\Client;
use Tests\Support\FakeGuard;

function fixtureModules(): ModuleRegistry
{
    return new ModuleRegistry(fixture('Modules'), 'Tests\\Fixtures\\Modules\\');
}

it('discovers modules from their folders, in menu order', function () {
    $modules = fixtureModules();
    $clients = $modules->get('clients');

    expect(array_keys($modules->all()))->toBe(['clients', 'reports'])
        ->and($clients->class)->toBe(Client::class)
        ->and($clients->isRecord())->toBeTrue()
        ->and($clients->url())->toBe('/clients')
        ->and($clients->permission('create'))->toBe('clients.create')
        ->and($modules->get('reports')->isRecord())->toBeFalse();
});

it('rejects modules whose name collides with core routes', function () {
    new ModuleRegistry(fixture('InvalidModules'), 'Tests\\Fixtures\\InvalidModules\\');
})->throws(LogicException::class, 'reserved');

it('builds CRUD and custom actions with names, permissions and annotations', function () {
    $actions = new ActionRegistry(fixtureModules());
    $summary = array_map(
        static fn (ActionDefinition $a): string => "{$a->name} {$a->permission} {$a->scope}"
            . ($a->readOnly ? ' ro' : '') . ($a->destructive ? ' destructive' : '') . ($a->confirm ? ' confirm' : ''),
        array_values($actions->all()),
    );

    expect($summary)->toBe([
        'clients_list clients.view collection ro',
        'clients_get clients.view record ro',
        'clients_create clients.create collection',
        'clients_update clients.update record',
        'clients_delete clients.delete record destructive confirm',
        'clients_block clients.block record destructive confirm',
        'clients_register_visit clients.register_visit record',
        'clients_count_active clients.count_active collection ro',
    ]);
});

it('derives input schemas from fields and method signatures', function () {
    $actions = new ActionRegistry(fixtureModules());
    $create = $actions->get('clients_create')->inputSchema;
    $update = $actions->get('clients_update')->inputSchema;
    $block = $actions->get('clients_block')->inputSchema;
    $visit = $actions->get('clients_register_visit')->inputSchema;

    expect($create['required'])->toBe(['name', 'email'])
        ->and($create['additionalProperties'])->toBeFalse()
        ->and(array_keys($create['properties']))->toBe(['name', 'email', 'credit', 'active', 'tier', 'since', 'visits', 'notes'])
        ->and($update['required'])->toBe(['id'])
        ->and($block['required'])->toBe(['id', 'reason'])
        ->and($block['properties']['reason'])->toBe(['title' => 'Motivo', 'type' => 'string'])
        ->and($visit['properties']['times'])->toBe(['type' => 'integer', 'default' => 1])
        ->and($visit['required'])->toBe(['id']);
});

it('lists only the actions a user may run', function () {
    $actions = new ActionRegistry(fixtureModules());
    $viewer = new Identity(1, 'Ana', 'ana@example.com', 'viewer', ['clients.view']);

    $names = array_map(static fn (ActionDefinition $a): string => $a->name, $actions->allowedFor($viewer, new Gate()));

    expect($names)->toBe(['clients_list', 'clients_get']);
});

it('builds the menu from the modules the user may view', function () {
    $menu = new Menu(fixtureModules(), new Gate());
    $viewer = new Identity(1, 'Ana', 'ana@example.com', 'viewer', ['clients.view']);
    $admin = new Identity(2, 'Admin', 'admin@example.com', 'admin', ['*']);

    expect(array_column($menu->for($viewer), 'label'))->toBe(['Clientes'])
        ->and(array_column($menu->for($admin, '/clients/12'), 'active', 'name'))->toBe(['clients' => true, 'reports' => false]);
});

it('serves a screen module inside the layout and as a fragment', function () {
    $app = app(guard: FakeGuard::as(['reports.view']));

    $page = $app->handle(Request::create('GET', '/reports'));
    $fragment = $app->handle(Request::create('GET', '/component/reports'));
    $denied = app(guard: FakeGuard::as(['clients.view']))->handle(Request::create('GET', '/reports'));

    expect($page->status)->toBe(200)
        ->and($page->body)->toContain('class="lego-shell"')
        ->and($page->body)->toContain('<h1>Informes</h1>')
        ->and($fragment->body)->not->toContain('lego-shell')
        ->and($fragment->body)->toContain('<h1>Informes</h1>')
        ->and($denied->status)->toBe(403);
});

it('keeps every module in the repository valid', function () {
    $modules = new ModuleRegistry(basePath('modules'));

    expect(new ActionRegistry($modules))->toBeInstanceOf(ActionRegistry::class);
});
