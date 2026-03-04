<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReview extends Model
{
    protected $fillable = [
        'user_id',
        'vendor_id',
        'order_id',
        'rating',
        'review',
        'is_approved',
        'vendor_reply',
        'replied_at',
        'helpful_count',
    ];
    
    protected $casts = [
        'is_approved' => 'boolean',
        'replied_at' => 'datetime',
        'rating' => 'integer',
        'helpful_count' => 'integer',
    ];
    
    /**
     * Get the user who wrote the review.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the vendor being reviewed.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
    
    /**
     * Get the order/booking this review is for.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Scope a query to only include approved reviews.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }
    
    /**
     * Scope a query to only include reviews with replies.
     */
    public function scopeWithReplies($query)
    {
        return $query->whereNotNull('vendor_reply');
    }
    
    /**
     * Scope a query to filter by rating.
     */
    public function scopeRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }
}
