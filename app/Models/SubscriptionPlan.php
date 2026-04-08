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


}
