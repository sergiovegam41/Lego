<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductImage Model
 *
 * FILOSOFÍA LEGO:
 * Modelo Eloquent para gestionar imágenes de productos.
 * Relación 1:N con Product (un producto tiene múltiples imágenes).
 *
 * CAMPOS:
 * - id: PK auto-increment
 * - product_id: FK a products
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
class ProductImage extends Model
{
    /**
     * La tabla asociada al modelo
     */
    protected $table = 'product_images';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'product_id',
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
        'product_id' => 'integer',
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
     * Relación: Una imagen pertenece a un producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
