<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Core\Attributes\ApiGetResource;

/**
 * Role Model
 *
 * FILOSOFÍA LEGO:
 * Catálogo de roles disponibles por grupo de autenticación.
 * Permite definir roles sin necesidad de tener usuarios con esos roles.
 *
 * CAMPOS:
 * - id: ID auto-increment
 * - auth_group_id: Grupo de autenticación (ADMINS, APIS, etc.)
 * - role_id: Identificador del rol (SUPERADMIN, ADMIN, etc.)
 * - role_name: Nombre descriptivo del rol (opcional)
 * - description: Descripción del rol (opcional)
 * - is_active: Si el rol está activo
 */
#[ApiGetResource(
    endpoint: 'roles-config',
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'auth_group_id', 'role_id', 'role_name', 'is_active', 'created_at'],
    filterable: ['id', 'auth_group_id', 'role_id', 'role_name', 'is_active'],
    searchable: ['role_id', 'role_name', 'description']
)]
class Role extends Model
{
    /**
     * La tabla asociada al modelo
     */
    protected $table = 'auth_roles';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'auth_group_id',
        'role_id',
        'role_name',
        'description',
        'is_active'
    ];

    /**
     * Los atributos que deben ser cast a tipos nativos
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Valores por defecto para atributos
     */
    protected $attributes = [
        'is_active' => true
    ];

    /**
     * Scope: Roles activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Por grupo de autenticación
     */
    public function scopeByAuthGroup($query, string $authGroupId)
    {
        return $query->where('auth_group_id', $authGroupId);
    }

    /**
     * Obtener roles agrupados por auth_group_id
     */
    public static function getGroupedRoles(): array
    {
        return self::active()
                   ->orderBy('auth_group_id')
                   ->orderBy('role_id')
                   ->get()
                   ->groupBy('auth_group_id')
                   ->map(function($group) {
                       return $group->pluck('role_id')->toArray();
                   })
                   ->toArray();
    }

    /**
     * Obtener roles de un grupo específico
     */
    public static function getRolesByGroup(string $authGroupId): array
    {
        return self::active()
                   ->byAuthGroup($authGroupId)
                   ->orderBy('role_id')
                   ->get()
                   ->pluck('role_id')
                   ->toArray();
    }
}

