<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Core\Attributes\ApiGetResource;

/**
 * Product Model
 *
 * FILOSOFÍA LEGO:
 * Modelo Eloquent para la tabla products.
 * Exposición de API de solo LECTURA para TableComponent.
 *
 * API AUTOMÁTICO GET-ONLY:
 * Este modelo expone automáticamente endpoints de lectura para tablas:
 * - GET /api/get/products        → Listar con paginación, filtros, búsqueda
 * - GET /api/get/products/{id}   → Obtener por ID
 *
 * PROPÓSITO:
 * - Alimentar TableComponent con datos
 * - Paginación server-side
 * - Filtros y búsqueda globales
 * - Sin operaciones de escritura (solo lectura)
 * - Evita colisión con App/Controllers/Products/Controllers/ProductsController.php
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
 *
 * CONFIGURACIÓN API GET:
 * - endpoint: Ruta SIN /api/get (ej: 'products' o 'catalog/items')
 *   El prefijo /api/get se agrega automáticamente
 *   Si se omite, auto-genera: 'products'
 * - pagination: 'offset' (page/limit) | 'cursor' | 'page'
 * - perPage: Elementos por página (1-100, default: 20)
 * - sortable: Campos permitidos para ordenar
 * - filterable: Campos permitidos para filtrar
 * - searchable: Campos donde buscar con ?search=texto
 */
#[ApiGetResource(
    endpoint: 'products',  // Opcional: Personaliza (sin /api/get, se agrega automáticamente)
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'name', 'description', 'price', 'stock', 'category', 'created_at'],
    filterable: ['id', 'name', 'description', 'price', 'stock', 'category', 'is_active'],
    searchable: ['name', 'description', 'sku']
)]
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
