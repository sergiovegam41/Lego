<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ToolFeature Model
 *
 * FILOSOFÍA LEGO:
 * Modelo para características de herramientas.
 * Permite vincular una lista indefinida de strings a cada herramienta.
 *
 * CAMPOS:
 * - id: PK auto-increment
 * - tool_id: FK a tools
 * - feature: La característica como string
 * - display_order: Orden de visualización
 * - created_at: Fecha de creación
 * - updated_at: Fecha de actualización
 */
class ToolFeature extends Model
{
    /**
     * La tabla asociada al modelo
     */
    protected $table = 'tool_features';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'tool_id',
        'feature',
        'display_order'
    ];

    /**
     * Los atributos que deben ser cast a tipos nativos
     */
    protected $casts = [
        'tool_id' => 'integer',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Valores por defecto para atributos
     */
    protected $attributes = [
        'display_order' => 0
    ];

    /**
     * Relación: Una característica pertenece a una herramienta
     */
    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    /**
     * Scope: Ordenadas por orden ascendente
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }
}

