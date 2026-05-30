<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    //
    protected $fillable = [
        'name',
        'slug',
        'type',
        'billing_cycle',
        'price',
        'discount_price',
        'duration_days',
        'is_trial',
        'features',
        'is_active',
        'is_popular',
    ];

    protected $casts = [
        'features' => 'array',
        'is_trial' => 'boolean',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'duration_days' => 'integer',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePurchasable($query)
    {
        return $query->active()->where('is_trial', false);
    }
}
