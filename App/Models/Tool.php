<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Core\Attributes\ApiGetResource;

/**
 * Tool Model
 *
 * FILOSOFÍA LEGO:
 * Modelo para el CRUD de Herramientas.
 * Campos: nombre, descripción
 * Relaciones: features (1:N), imágenes via entity_files
 *
 * API AUTOMÁTICO GET-ONLY:
 * - GET /api/get/tools        → Listar con paginación, filtros, búsqueda
 * - GET /api/get/tools/{id}   → Obtener por ID
 *
 * CAMPOS:
 * - id: PK auto-increment
 * - name: Nombre de la herramienta
 * - description: Descripción detallada
 * - is_active: Estado activo/inactivo
 * - created_at: Fecha de creación
 * - updated_at: Fecha de actualización
 */
#[ApiGetResource(
    endpoint: 'tools',
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'name', 'created_at'],
    filterable: ['id', 'name', 'is_active'],
    searchable: ['name', 'description']
)]
class Tool extends Model
{
    /**
     * La tabla asociada al modelo
     */
    protected $table = 'tools';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
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
     * Los atributos que deben ocultarse en arrays/JSON
     */
    protected $hidden = [];

    /**
     * Valores por defecto para atributos
     */
    protected $attributes = [
        'is_active' => true
    ];

    /**
     * Scope: Solo herramientas activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor: Estado en texto
     */
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Activo' : 'Inactivo';
    }

    /**
     * Relación: Una herramienta tiene múltiples características
     */
    public function features(): HasMany
    {
        return $this->hasMany(ToolFeature::class)->orderBy('display_order');
    }

    /**
     * Accessor: Obtener características como array de strings
     */
    public function getFeaturesListAttribute(): array
    {
        return $this->features->pluck('feature')->toArray();
    }

    /**
     * Accessor: Número de características
     */
    public function getFeaturesCountAttribute(): int
    {
        return $this->features->count();
    }
}

