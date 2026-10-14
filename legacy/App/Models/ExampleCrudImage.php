<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExampleCrudImage Model
 *
 * FILOSOFÍA LEGO:
 * Modelo Eloquent para gestionar imágenes de registros example_crud.
 * Relación 1:N con ExampleCrud (un registro tiene múltiples imágenes).
 * Este modelo sirve como template/ejemplo para implementar sistemas de imágenes.
 *
 * CAMPOS:
 * - id: PK auto-increment
 * - example_crud_id: FK a example_crud
 * - url: URL completa de la imagen en MinIO
 * - key: Key/path en MinIO (para eliminación)
 * - original_name: Nombre original del archivo
 * - size: Tamaño en bytes
 * - mime_type: Tipo MIME
 * - display_order: Orden de visualización
 * - is_primary: Marca si es la imagen principal
 * - created_at: Fecha de creación
 * - updated_at: Fecha de actualización
 */
class ExampleCrudImage extends Model
{
    /**
     * La tabla asociada al modelo
     */
    protected $table = 'example_crud_images';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'example_crud_id',
        'url',
        'key',
        'original_name',
        'size',
        'mime_type',
        'display_order',
        'is_primary'
    ];

    /**
     * Los atributos que deben ser cast a tipos nativos
     */
    protected $casts = [
        'example_crud_id' => 'integer',
        'size' => 'integer',
        'display_order' => 'integer',
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Valores por defecto para atributos
     */
    protected $attributes = [
        'display_order' => 0,
        'is_primary' => false
    ];

    /**
     * Relación: Una imagen pertenece a un registro example_crud
     */
    public function exampleCrud(): BelongsTo
    {
        return $this->belongsTo(ExampleCrud::class);
    }

    /**
     * Scope: Solo imágenes primarias
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope: Ordenadas por orden ascendente
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }

    /**
     * Accessor: Tamaño formateado (KB/MB)
     */
    public function getSizeFormattedAttribute(): string
    {
        if (!$this->size) return 'N/A';

        $bytes = $this->size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }

    /**
     * Accessor: Extensión del archivo
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_name ?? '', PATHINFO_EXTENSION);
    }
}
