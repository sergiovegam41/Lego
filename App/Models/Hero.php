<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Core\Attributes\ApiGetResource;

#[ApiGetResource(
    endpoint: 'heroes',
    pagination: 'offset',
    perPage: 20,
    sortable: ['id', 'sort_order', 'created_at'],
    filterable: ['id', 'is_active'],
    searchable: ['title', 'subtitle']
)]
class Hero extends Model
{
    protected $table = 'heroes';

    protected $fillable = [
        'title',
        'subtitle',
        'background_image',
        'cta_label',
        'cta_link',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get active hero with highest priority (lowest sort_order)
     */
    public static function getActive()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->first();
    }

    /**
     * Get all active heroes ordered by priority
     */
    public static function getAllActive()
    {
        return self::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();
    }
}
