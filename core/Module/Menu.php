<?php

declare(strict_types=1);

namespace Lego\Module;

use Lego\Auth\Gate;
use Lego\Auth\Identity;

/**
 * The navigation menu, deduced from the modules (decision D1): one entry per
 * module the user may view, in the order declared by #[Module(order: ...)].
 */
final class Menu
{
    public function __construct(private readonly ModuleRegistry $modules, private readonly Gate $gate)
    {
    }

    /** @return list<array{name: string, label: string, icon: string, url: string, active: bool}> */
    public function for(Identity $user, string $currentPath = '/'): array
    {
        $items = [];
        foreach ($this->modules->all() as $module) {
            if (!$this->gate->allows($user, $module->permission('view'))) {
                continue;
            }
            $items[] = [
                'name' => $module->name,
                'label' => $module->label,
                'icon' => $module->icon,
                'url' => $module->url(),
                'active' => $currentPath === $module->url() || str_starts_with($currentPath, $module->url() . '/'),
            ];
        }

        return $items;
    }
}
