<?php

declare(strict_types=1);

use Lego\Action\ActionDefinition;
use Lego\Action\ActionRegistry;
use Lego\Module\ModuleRegistry;

/*
 * Every action must be usable by an AI agent without guessing: a name tools
 * accept, a description written for someone new, a strict input schema with
 * every parameter described, and honest annotations (doc/ia.md).
 */

/** @return array<string, ActionDefinition> core modules plus the test modules */
function everyAction(): array
{
    return (new ActionRegistry(new ModuleRegistry([
        basePath('core/Modules') => 'Lego\\Modules\\',
        basePath('modules') => 'Modules\\',
        fixture('Modules') => 'Tests\\Fixtures\\Modules\\',
    ])))->all();
}

it('names every action so that any tool protocol accepts it', function () {
    foreach (array_keys(everyAction()) as $name) {
        expect($name)->toMatch('/^[a-z][a-z0-9_]{0,29}$/', "{$name} is not a valid tool name of at most 30 characters");
    }
});

it('describes every action for people and for agents', function () {
    foreach (everyAction() as $name => $action) {
        expect($action->label)->not->toBe('', "{$name} has no label")
            ->and(mb_strlen($action->description))->toBeGreaterThanOrEqual(20, "{$name} description is too short")
            ->and(mb_strlen($action->description))->toBeLessThanOrEqual(500, "{$name} description is too long")
            ->and($action->description)->toEndWith('.', "{$name} description should be a sentence");
    }
});

it('gives every action a strict input schema with every parameter described', function () {
    foreach (everyAction() as $name => $action) {
        if ($action->module === 'users' || $action->module === 'roles') {
            continue; // their schemas list rows from PostgreSQL; covered by the database tests
        }
        $schema = $action->inputSchema();

        expect($schema['type'])->toBe('object', $name)
            ->and($schema['additionalProperties'])->toBeFalse("{$name} accepts unknown keys");
        foreach ($schema['properties'] ?? [] as $property => $definition) {
            if ($property !== 'id') {
                expect(isset($definition['title']) || isset($definition['description']))
                    ->toBeTrue("{$name}.{$property} has neither title nor description");
            }
        }
    }
});

it('derives honest annotations', function () {
    $actions = everyAction();

    expect($actions['clients_list']->readOnly)->toBeTrue()
        ->and($actions['clients_list']->untrustedContent)->toBeTrue()
        ->and($actions['clients_delete']->destructive)->toBeTrue()
        ->and($actions['clients_delete']->confirm)->toBeTrue()
        ->and($actions['clients_delete']->idempotent)->toBeTrue()
        ->and($actions['clients_create']->idempotent)->toBeFalse()
        ->and($actions['users_reset_password']->confirm)->toBeTrue();

    foreach ($actions as $name => $action) {
        if ($action->destructive || $action->external) {
            expect($action->confirm)->toBeTrue("{$name} is destructive or external but does not confirm");
        }
        if ($action->readOnly) {
            expect($action->destructive)->toBeFalse("{$name} cannot be read-only and destructive");
        }
    }
});
