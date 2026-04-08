<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $fillable = [
        'vendor_id',
        'code',
        'name',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function isValid(float $orderAmount = 0): bool
    {
        if (!$this->is_active) return false;
        if ($this->start_date && now()->lt($this->start_date)) return false;
        if ($this->end_date && now()->gt($this->end_date)) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        if ($orderAmount > 0 && $orderAmount < $this->min_order_amount) return false;

        return true;
    }

    public function calculateDiscount(float $subTotal): float
    {
        if ($this->type === 'percent') {
            $discount = round($subTotal * ($this->value / 100), 2);
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }
        } else {
            $discount = round($this->value, 2);
        }

        return min($discount, $subTotal);
    }
}
