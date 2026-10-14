<?php

declare(strict_types=1);

namespace Lego\Auth;

use Lego\Action\ActionRegistry;
use Lego\Module\ModuleRegistry;

/**
 * Every permission a role can grant, deduced from the modules and their
 * actions, with a readable label: "*", "clients.*", "clients.view", ...
 *
 * The Roles module offers these as checkboxes. The application builds the
 * catalog once at boot; fields read it through PermissionCatalog::current().
 */
final class PermissionCatalog
{
    private static ?self $current = null;

    /** @param array<string, string> $permissions permission => label */
    public function __construct(private readonly array $permissions)
    {
    }

    public static function build(ModuleRegistry $modules, ActionRegistry $actions): self
    {
        $permissions = ['*' => 'Todo (administrador)'];
        foreach ($modules->all() as $module) {
            $permissions[$module->name . '.*'] = "{$module->label}: todo";
            $permissions[$module->permission('view')] = "{$module->label}: ver";
            foreach ($actions->forModule($module->name) as $action) {
                $permissions[$action->permission] ??= "{$module->label}: " . mb_strtolower($action->label);
            }
        }

        return new self($permissions);
    }

    public static function use(self $catalog): void
    {
        self::$current = $catalog;
    }

    public static function current(): self
    {
        return self::$current ?? new self(['*' => 'Todo (administrador)']);
    }

    /** @return array<string, string> permission => label */
    public function all(): array
    {
        return $this->permissions;
    }
}
