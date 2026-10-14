<?php

declare(strict_types=1);

namespace Lego\Modules\Roles;

use Lego\Auth\Identity;
use Lego\Auth\PermissionCatalog;
use Lego\Data\Fields\Checklist;
use Lego\Data\Fields\Text;
use Lego\Data\Record;
use Lego\Errors\BusinessRuleViolation;
use Lego\Module\Module;

/**
 * Roles and their permissions (decision D2). Permissions are chosen from the
 * catalog deduced from the modules, so a role can only grant what exists.
 *
 * The "admin" role keeps "*" and cannot be renamed or deleted, and a role in
 * use cannot be deleted.
 *
 * @property int          $id
 * @property string       $name
 * @property string       $label
 * @property list<string> $permissions
 */
#[Module(label: 'Roles', icon: 'shield-outline', order: 910)]
final class Role extends Record
{
    public const ADMIN = 'admin';

    protected $table = 'roles';

    protected $casts = ['permissions' => 'array'];

    public static function fields(): array
    {
        return [
            Text::make('name', 'Nombre interno')->required()->max(64)->unique()->sortable()
                ->pattern('/^[a-z][a-z0-9_]*$/', 'Solo minúsculas, números y guion bajo, empezando por una letra.'),
            Text::make('label', 'Nombre visible')->required()->max(120)->searchable()->sortable(),
            Checklist::make('permissions', 'Permisos')
                ->optionsFrom(static fn (): array => PermissionCatalog::current()->all()),
        ];
    }

    public function beforeChange(string $operation, array $clean, Identity $by): void
    {
        if ($operation === 'delete') {
            if ($this->name === self::ADMIN) {
                throw new BusinessRuleViolation('role_locked', 'El rol de administrador no se puede eliminar.');
            }
            $users = $this->getConnection()->table('users')->where('role_id', $this->getKey())->count();
            if ($users > 0) {
                throw new BusinessRuleViolation('role_in_use', "No se puede eliminar: lo usan {$users} usuarios.");
            }
        }

        if ($operation === 'update' && $this->name === self::ADMIN) {
            if (isset($clean['name']) && $clean['name'] !== self::ADMIN) {
                throw new BusinessRuleViolation('role_locked', 'El rol de administrador no se puede renombrar.');
            }
            if (isset($clean['permissions']) && !in_array('*', $clean['permissions'], true)) {
                throw new BusinessRuleViolation('role_locked', 'El rol de administrador debe conservar todos los permisos.');
            }
        }
    }
}
