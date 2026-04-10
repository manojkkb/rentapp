<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Vendor extends Model
{
    use SoftDeletes;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'owner_name',
        'slug',
        'logo',
        'business_category_id',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'gst_number',
        'language',
        'is_verified',
        'rating',
        'total_reviews',
        'is_active',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'rating' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];
    
    /**
     * Get the user that owns the vendor (primary owner).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the business category for the vendor.
     */
    public function businessCategory(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }
    
    /**
     * Get all users who have access to this vendor (many-to-many)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'vendor_users')
            ->withPivot('is_owner', 'role', 'is_active', 'last_login_at', 'permissions')
            ->withTimestamps();
    }
    
    /**
     * Get active users for this vendor
     */
    public function activeUsers()
    {
        return $this->users()->wherePivot('is_active', true);
    }
    
    /**
     * Get all items for the vendor.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Items::class);
    }
    
    /**
     * Get all categories for the vendor.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }
    
    /**
     * Get all reviews for the vendor.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(CustomerReview::class);
    }
    
    /**
     * Get approved reviews for the vendor.
     */
    public function approvedReviews(): HasMany
    {
        return $this->hasMany(CustomerReview::class)->where('is_approved', true);
    }
    
    /**
     * Update vendor rating based on approved reviews.
     */
    public function updateRating(): void
    {
        $approvedReviews = $this->reviews()->where('is_approved', true)->get();
        $totalReviews = $approvedReviews->count();
        
        if ($totalReviews > 0) {
            $averageRating = $approvedReviews->avg('rating');
            $this->update([
                'rating' => round($averageRating, 2),
                'total_reviews' => $totalReviews,
            ]);
        } else {
            $this->update([
                'rating' => 0,
                'total_reviews' => 0,
            ]);
        }
    }
    
    /**
     * Scope a query to only include verified vendors.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
    
    /**
     * Scope a query to only include active vendors.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Public URL for the business logo, or null if missing / not on disk.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        if (! Storage::disk('public')->exists($this->logo)) {
            return null;
        }

        return Storage::disk('public')->url($this->logo);
    }

    /**
     * Get the full address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);
        
        return implode(', ', $parts);
    }
}

