<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Core\Attributes\ApiGetResource;
use Core\Attributes\ApiCrudResource;

/**
 * Testimonial Model
 *
 * Represents customer testimonials and reviews.
 */
#[ApiGetResource(
    endpoint: 'testimonials',
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'author', 'created_at'],
    filterable: ['id', 'author', 'is_active'],
    searchable: ['author', 'message']
)]
#[ApiCrudResource(
    endpoint: 'testimonials'
)]
class Testimonial extends Model
{
    protected $table = 'testimonials';

    protected $fillable = [
        'author',
        'message',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Scope: Get only active testimonials
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Order by newest first
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get formatted created date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y');
    }

    /**
     * Get truncated message for preview
     */
    public function getPreviewMessageAttribute(): string
    {
        return strlen($this->message) > 100
            ? substr($this->message, 0, 100) . '...'
            : $this->message;
    }
}
