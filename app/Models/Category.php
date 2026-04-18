<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    protected $appends = ['image_url'];

    protected $fillable = [
        'vendor_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all items in this category
     */
    public function items(): HasMany
    {
        return $this->hasMany(Items::class);
    }

    /**
     * Get the parent category
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get all subcategories
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the vendor that owns this category
     */
    public function vendor()
    {
        return $this->belongsTo(\App\Models\Vendor::class);
    }

    /**
     * Scope a query to only include active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Public URL for the category image on S3, or null.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Storage::disk('s3')->url($this->image);
    }
}
