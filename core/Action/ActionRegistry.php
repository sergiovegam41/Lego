<?php

declare(strict_types=1);

namespace Lego\Action;

use Lego\Auth\Gate;
use Lego\Auth\Identity;
use Lego\Data\Validator;
use Lego\Module\ModuleRegistry;
use LogicException;

/**
 * Every action of every module, built from the code at boot. Nothing is
 * registered by hand: CRUD comes from ResourceActions, the rest from
 * #[Action] methods (CustomActions).
 */
final class ActionRegistry
{
    /** @var array<string, ActionDefinition> */
    private array $actions = [];

    public function __construct(ModuleRegistry $modules, Validator $validator = new Validator())
    {
        $resource = new ResourceActions($validator);
        $custom = new CustomActions();

        foreach ($modules->all() as $module) {
            if (!$module->isRecord()) {
                continue;
            }
            foreach ([...$resource->for($module), ...$custom->for($module)] as $action) {
                if (preg_match('/^[a-z][a-z0-9_]{0,63}$/', $action->name) !== 1) {
                    throw new LogicException("Invalid action name \"{$action->name}\".");
                }
                if (isset($this->actions[$action->name])) {
                    throw new LogicException("Duplicate action name \"{$action->name}\".");
                }
                $this->actions[$action->name] = $action;
            }
        }
    }

    /** @return array<string, ActionDefinition> */
    public function all(): array
    {
        return $this->actions;
    }

    public function get(string $name): ?ActionDefinition
    {
        return $this->actions[$name] ?? null;
    }

    /** @return list<ActionDefinition> */
    public function forModule(string $module): array
    {
        return array_values(array_filter($this->actions, static fn (ActionDefinition $a): bool => $a->module === $module));
    }

    /** @return list<ActionDefinition> Actions the user may run, optionally of one module. */
    public function allowedFor(Identity $user, Gate $gate, ?string $module = null): array
    {
        $actions = $module === null ? array_values($this->actions) : $this->forModule($module);

        return array_values(array_filter($actions, static fn (ActionDefinition $a): bool => $gate->allows($user, $a->permission)));
    }
}
