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
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
