<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'item_id',
        'item_name',
        'price',
        'quantity',
        'price_type',
        'billing_units',
        'start_at',
        'end_at',
        'rent_days',
        'total_price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'billing_units' => 'decimal:4',
        'total_price' => 'decimal:2',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Line amount from snapshot fields (matches VendorCartItem::lineSubtotal logic).
     */
    public function lineSubtotal(): float
    {
        $price = (float) $this->price;
        $qty = (int) $this->quantity;
        $type = $this->price_type ?? 'per_day';

        if ($type === 'fixed') {
            return round($price * $qty, 2);
        }

        $units = (float) ($this->billing_units ?? 1);
        if ($units < 0.0001) {
            $units = 1.0;
        }

        return round($price * $qty * $units, 2);
    }

    /**
     * Get the order that owns the item
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the item (product)
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Items::class, 'item_id');
    }
}
