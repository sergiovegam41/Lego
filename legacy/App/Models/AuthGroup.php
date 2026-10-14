<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Core\Attributes\ApiGetResource;

/**
 * AuthGroup Model
 *
 * FILOSOFÍA LEGO:
 * Catálogo de grupos de autenticación disponibles.
 *
 * CAMPOS:
 * - id: Identificador del grupo (ADMINS, APIS, etc.) - Primary Key
 * - name: Nombre descriptivo del grupo
 * - description: Descripción del grupo (opcional)
 * - is_active: Si el grupo está activo
 */
#[ApiGetResource(
    endpoint: 'auth-groups',
    pagination: 'offset',
    perPage: 50,
    sortable: ['id', 'name', 'is_active', 'created_at'],
    filterable: ['id', 'name', 'is_active'],
    searchable: ['id', 'name', 'description']
)]
class AuthGroup extends Model
{
    /**
     * La tabla asociada al modelo
     */
    protected $table = 'auth_groups';

    /**
     * La clave primaria (no es auto-increment)
     */
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'id',
        'name',
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
     * Scope: Grupos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

