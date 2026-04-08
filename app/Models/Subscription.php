<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    //
    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'vendor_id',
        'start_date',
        'expiry_date',
        'status',
        'amount',
        'payment_gateway',
        'payment_id',
        'auto_renew',
        
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'expiry_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

}
