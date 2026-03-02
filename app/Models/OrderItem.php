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
        'start_at',
        'end_at',
        'rent_days',
        'total_price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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
