<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Product Model
 *
 * FILOSOFÍA LEGO:
 * Modelo Eloquent para la tabla products.
 * Ejemplo de CRUD completo con todas las operaciones básicas.
 *
 * CAMPOS:
 * - id: PK auto-increment
 * - name: Nombre del producto
 * - description: Descripción
 * - price: Precio decimal
 * - stock: Cantidad en inventario
 * - category: Categoría del producto
 * - image_url: URL de imagen (opcional)
 * - is_active: Estado activo/inactivo
 * - created_at: Fecha de creación
 * - updated_at: Fecha de actualización
 */
class Product extends Model
{
    /**
     * La tabla asociada al modelo
     */
    protected $table = 'products';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
        'stock',
        'min_stock',
        'category',
        'image_url',
        'is_active'
    ];

    /**
     * Los atributos que deben ser cast a tipos nativos
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
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
        'is_active' => true,
        'stock' => 0
    ];

    /**
     * Scope: Solo productos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Por categoría
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Con stock disponible
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Accessor: Formato de precio
     */
    public function getPriceFormattedAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Accessor: Estado en texto
     */
    public function getStatusTextAttribute()
    {
        return $this->is_active ? 'Activo' : 'Inactivo';
    }

    /**
     * Accessor: Disponibilidad
     */
    public function getAvailabilityAttribute()
    {
        if ($this->stock > 10) return 'En Stock';
        if ($this->stock > 0) return 'Pocas Unidades';
        return 'Agotado';
    }

    /**
     * Relación: Un producto tiene múltiples imágenes
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    /**
     * Relación: Obtener solo la imagen principal
     */
    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Accessor: URL de la imagen principal (fallback al campo image_url legacy)
     */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        // Primero intentar con la relación de imágenes
        $primaryImage = $this->primaryImage;
        if ($primaryImage && $primaryImage->url) {
            return $primaryImage->url;
        }

        // Fallback al campo legacy image_url
        return $this->image_url;
    }
}
