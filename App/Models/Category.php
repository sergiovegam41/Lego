<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Core\Attributes\ApiGetResource;
use Core\Attributes\ApiCrudResource;

/**
 * Category Model
 *
 * Represents product categories for the flower shop.
 * Each category can have multiple flowers associated.
 */
#[ApiGetResource(
    endpoint: 'categories',
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'name', 'created_at'],
    filterable: ['id', 'name', 'is_active'],
    searchable: ['name', 'description']
)]
#[ApiCrudResource(
    endpoint: 'categories'
)]
class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
        'image_url',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // IMPORTANTE: primary_image se incluye en appends para la tabla
    // Se usa caché para evitar queries múltiples
    protected $appends = ['primary_image'];

    /**
     * Get all flowers belonging to this category
     */
    public function flowers(): HasMany
    {
        return $this->hasMany(Flower::class, 'category_id');
    }

    /**
     * Get active flowers in this category
     */
    public function activeFlowers(): HasMany
    {
        return $this->flowers()->where('is_active', true);
    }

    /**
     * Scope: Get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get flower count for this category
     */
    public function getFlowerCountAttribute(): int
    {
        return $this->flowers()->count();
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
            $fileAssociations = $fileService->getEntityFiles('Category', $this->id);

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
            error_log("Error loading primary image for Category {$this->id}: " . $e->getMessage());
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
            $fileAssociations = $fileService->getEntityFiles('Category', $this->id);

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
            error_log("Error loading all images for Category {$this->id}: " . $e->getMessage());
            return [];
        }
    }
}
