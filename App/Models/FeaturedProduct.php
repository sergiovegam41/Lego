<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Core\Attributes\ApiGetResource;
use Core\Attributes\ApiCrudResource;

/**
 * FeaturedProduct Model
 *
 * Representa productos destacados/populares con tags y orden de visualización.
 * Cada entrada relaciona un producto con un tag específico y su posición en la lista.
 */
#[ApiGetResource(
    endpoint: 'featured-products',
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'product_id', 'sort_order', 'created_at'],
    filterable: ['id', 'product_id', 'tag', 'is_active'],
    searchable: ['tag', 'description']
)]
#[ApiCrudResource(
    endpoint: 'featured-products'
)]
class FeaturedProduct extends Model
{
    protected $table = 'featured_products';

    protected $fillable = [
        'product_id',
        'tag',
        'description',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Agregar product_name y product_image como atributos computados
    protected $appends = ['product_name', 'product_price', 'product_image'];

    /**
     * Relación: FeaturedProduct pertenece a un Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Flower::class, 'product_id');
    }

    /**
     * Scope: Obtener solo productos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Ordenar por sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Scope: Filtrar por tag
     */
    public function scopeByTag($query, string $tag)
    {
        return $query->where('tag', $tag);
    }

    /**
     * Obtener nombre del producto (atributo computado)
     */
    public function getProductNameAttribute(): ?string
    {
        return $this->product?->name;
    }

    /**
     * Obtener precio del producto (atributo computado)
     */
    public function getProductPriceAttribute(): ?float
    {
        return $this->product?->price;
    }

    /**
     * Obtener imagen principal del producto (atributo computado)
     */
    public function getProductImageAttribute(): ?string
    {
        return $this->product?->primary_image;
    }

    /**
     * Obtener fecha formateada
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y');
    }

    /**
     * Tags predefinidos del sistema
     */
    public static function getAvailableTags(): array
    {
        return [
            'most-popular' => 'Más Popular',
            'best-seller' => 'Más Vendido',
            'free-shipping' => 'Envío Gratis',
            'new-arrival' => 'Nuevo',
            'limited-edition' => 'Edición Limitada',
            'discount' => 'En Descuento',
            'featured' => 'Destacado',
            'seasonal' => 'Temporada'
        ];
    }
}
