<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessCategory extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'slug',
        'description',
        'icon',
        'is_active',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'parent_id');
    }
    
    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(BusinessCategory::class, 'parent_id');
    }
    
    /**
     * Get all vendors in this category.
     */
    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class, 'business_category_id');
    }
    
    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope a query to only include parent categories.
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }
}
