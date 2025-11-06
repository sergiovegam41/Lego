<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Core\Attributes\ApiGetResource;
use Core\Attributes\ApiCrudResource;

/**
 * Flower Model
 *
 * Represents individual flower products in the catalog.
 * Each flower belongs to a category and can have multiple images.
 */
#[ApiGetResource(
    endpoint: 'flowers',
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'name', 'price', 'created_at'],
    filterable: ['id', 'name', 'category_id', 'is_active'],
    searchable: ['name', 'description']
)]
#[ApiCrudResource(
    endpoint: 'flowers'
)]
class Flower extends Model
{
    protected $table = 'flowers';

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'category_id' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // IMPORTANTE: No incluir 'primary_image' y 'all_images' en $appends
    // porque causan queries adicionales y pueden romper el API CRUD.
    // En su lugar, estos atributos se acceden explícitamente cuando se necesitan.
    protected $appends = ['category_name'];

    /**
     * Get the category this flower belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get all images for this flower
     */
    public function images(): HasMany
    {
        return $this->hasMany(FlowerImage::class, 'flower_id')->orderBy('sort_order');
    }

    /**
     * Get primary image
     */
    public function primaryImage(): HasMany
    {
        return $this->images()->where('is_primary', true);
    }

    /**
     * Scope: Get only active flowers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by category
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: Price range filter
     */
    public function scopePriceRange($query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Get category name (appended attribute)
     */
    public function getCategoryNameAttribute(): ?string
    {
        return $this->category?->name;
    }

    /**
     * Get primary image URL (appended attribute)
     */
    public function getPrimaryImageAttribute(): ?string
    {
        // Si no hay ID, no podemos cargar imágenes
        if (!$this->id || !$this->exists) {
            return null;
        }

        try {
            // Verificar si la clase FileService existe
            if (!class_exists('\Core\Services\File\FileService')) {
                return null;
            }

            $fileService = new \Core\Services\File\FileService();
            $fileAssociations = $fileService->getEntityFiles('Flower', $this->id);

            if (!$fileAssociations || $fileAssociations->isEmpty()) {
                return null;
            }

            // Buscar la imagen principal
            $primaryAssoc = $fileAssociations->firstWhere('is_primary', true);
            if ($primaryAssoc && isset($primaryAssoc->file) && isset($primaryAssoc->file->url)) {
                return $primaryAssoc->file->url;
            }

            // Si no hay principal, devolver la primera
            $firstAssoc = $fileAssociations->first();
            if ($firstAssoc && isset($firstAssoc->file) && isset($firstAssoc->file->url)) {
                return $firstAssoc->file->url;
            }

            return null;
        } catch (\Exception $e) {
            // En caso de error, log y devolver null en lugar de romper todo
            error_log("Error loading primary image for Flower {$this->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all images URLs (appended attribute for gallery/carousel)
     */
    public function getAllImagesAttribute(): array
    {
        // Si no hay ID, no podemos cargar imágenes
        if (!$this->id || !$this->exists) {
            return [];
        }

        try {
            // Verificar si la clase FileService existe
            if (!class_exists('\Core\Services\File\FileService')) {
                return [];
            }

            $fileService = new \Core\Services\File\FileService();
            $fileAssociations = $fileService->getEntityFiles('Flower', $this->id);

            if (!$fileAssociations || $fileAssociations->isEmpty()) {
                return [];
            }

            // Retornar todas las imágenes con su información
            return $fileAssociations->map(function($assoc) {
                if (!$assoc || !isset($assoc->file)) {
                    return null;
                }
                $file = $assoc->file;
                return [
                    'url' => $file->url ?? null,
                    'original_name' => $file->original_name ?? 'image.jpg',
                    'is_primary' => ($assoc->metadata['is_primary'] ?? false) === true
                ];
            })->filter()->values()->toArray();

        } catch (\Exception $e) {
            error_log("Error loading all images for Flower {$this->id}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }
}
