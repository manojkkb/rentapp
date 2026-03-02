<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Items extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'price_type',
        'stock',
        'manage_stock',
        'is_available',
        'is_active',
        'meta_title',
        'meta_description',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'manage_stock' => 'boolean',
        'price' => 'decimal:2',
    ];
    
    /**
     * Get the vendor that owns the item
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
    
    /**
     * Get the category of the item
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Scope a query to only include active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
