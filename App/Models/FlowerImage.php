<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * FlowerImage Model
 *
 * Represents images associated with flower products.
 * Supports multiple images per flower with ordering and primary image selection.
 */
class FlowerImage extends Model
{
    protected $table = 'flower_images';

    protected $fillable = [
        'flower_id',
        'image_url',
        'sort_order',
        'is_primary'
    ];

    protected $casts = [
        'flower_id' => 'integer',
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the flower this image belongs to
     */
    public function flower(): BelongsTo
    {
        return $this->belongsTo(Flower::class, 'flower_id');
    }

    /**
     * Set this image as primary (and unset others)
     */
    public function setPrimary(): void
    {
        // Unset all other primary images for this flower
        FlowerImage::where('flower_id', $this->flower_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set this as primary
        $this->is_primary = true;
        $this->save();
    }
}
