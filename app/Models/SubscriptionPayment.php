<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'subscription_id',
        'user_id',
        'vendor_id',
        'amount',
        'payment_gateway',
        'payment_id',
        'status',
        'order_id',
        'paid_at',
        'failure_reason',
        'receipt_url',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
