<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Core\Attributes\ApiGetResource;

/**
 * ExampleCrud Model
 *
 * FILOSOFÍA LEGO:
 * Modelo de ejemplo/template para demostrar CRUD completo.
 * Este modelo sirve como referencia para construir otros CRUDs en el framework.
 *
 * API AUTOMÁTICO GET-ONLY:
 * Este modelo expone automáticamente endpoints de lectura para tablas:
 * - GET /api/get/example-crud        → Listar con paginación, filtros, búsqueda
 * - GET /api/get/example-crud/{id}   → Obtener por ID
 *
 * PROPÓSITO:
 * - Alimentar TableComponent con datos
 * - Paginación server-side
 * - Filtros y búsqueda globales
 * - Sin operaciones de escritura (solo lectura)
 * - Template/ejemplo para construir otros CRUDs
 *
 * CAMPOS:
 * - id: PK auto-increment
 * - name: Nombre del registro
 * - description: Descripción
 * - price: Precio decimal
 * - stock: Cantidad en inventario
 * - min_stock: Stock mínimo para alertas
 * - category: Categoría
 * - image_url: URL de imagen (opcional)
 * - is_active: Estado activo/inactivo
 * - created_at: Fecha de creación
 * - updated_at: Fecha de actualización
 *
 * CONFIGURACIÓN API GET:
 * - endpoint: Ruta SIN /api/get (ej: 'example-crud')
 *   El prefijo /api/get se agrega automáticamente
 * - pagination: 'offset' (page/limit) | 'cursor' | 'page'
 * - perPage: Elementos por página (1-100, default: 20)
 * - sortable: Campos permitidos para ordenar
 * - filterable: Campos permitidos para filtrar
 * - searchable: Campos donde buscar con ?search=texto
 */
#[ApiGetResource(
    endpoint: 'example-crud',
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'name', 'description', 'price', 'stock', 'category', 'created_at'],
    filterable: ['id', 'name', 'description', 'price', 'stock', 'category', 'is_active'],
    searchable: ['name', 'description']
)]
class ExampleCrud extends Model
{
    /**
     * La tabla asociada al modelo
     */
    protected $table = 'example_crud';

    /**
     * Los atributos que se pueden asignar masivamente
     */
    protected $fillable = [
        'name',
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
        'min_stock' => 'integer',
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
        'stock' => 0,
        'min_stock' => 5
    ];

    /**
     * Scope: Solo registros activos
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
     * Scope: Stock bajo (menor que min_stock)
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock < min_stock');
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
        if ($this->stock > $this->min_stock) return 'En Stock';
        if ($this->stock > 0) return 'Stock Bajo';
        return 'Agotado';
    }

    /**
     * Accessor: Alerta de stock bajo
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->stock < $this->min_stock;
    }

    /**
     * Relación: Un registro tiene múltiples imágenes
     */
    public function images(): HasMany
    {
        return $this->hasMany(ExampleCrudImage::class)->orderBy('display_order');
    }

    /**
     * Relación: Obtener solo la imagen principal
     */
    public function primaryImage()
    {
        return $this->hasOne(ExampleCrudImage::class)->where('is_primary', true);
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
