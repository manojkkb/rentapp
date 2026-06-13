<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\RoutesByUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasUuid, RoutesByUuid;

    protected $fillable = [
        'uuid',
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
        'used_count' => 'integer',
        'usage_limit' => 'integer',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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

    /**
     * UI status: inactive, exhausted, expired, scheduled, or active.
     */
    public function lifecycleStatus(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return 'exhausted';
        }

        if ($this->end_date && now()->gt($this->end_date)) {
            return 'expired';
        }

        if ($this->start_date && now()->lt($this->start_date)) {
            return 'scheduled';
        }

        return 'active';
    }

    public function discountLabel(): string
    {
        if ($this->type === 'percent') {
            $pct = rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.');

            return $pct.'% '.__('vendor.off');
        }

        return '₹'.number_format((float) $this->value, 2).' '.__('vendor.off');
    }
}
