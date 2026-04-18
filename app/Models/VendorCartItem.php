<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorCartItem extends Model
{
    protected $fillable = [
        'vendor_cart_id',
        'item_id',
        'quantity',
        'price_type',
        'billing_units',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'billing_units' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Line amount before tax/discounts: price × qty × billing_units (billing_units ignored for fixed).
     */
    public function lineSubtotal(): float
    {
        if (! $this->item) {
            return 0.0;
        }

        $price = (float) $this->item->price;
        $qty = (int) $this->quantity;
        $type = $this->item->price_type ?? $this->price_type;

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
     * Get the cart that owns the item
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(VendorCart::class, 'vendor_cart_id');
    }

    /**
     * Get the rental item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Items::class, 'item_id');
    }
}
