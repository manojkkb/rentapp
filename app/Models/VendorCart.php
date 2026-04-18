<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorCart extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'vendor_id',
        'cart_name',
        'sub_total',
        'tax_total',
        'discount_total',
        'discount_type',
        'discount_value',
        'discount_amount',
        'token_amount',
        'paid_amount',
        'grand_total',
        'start_time',
        'end_time',
        'fulfillment_type',
        'delivery_address',
        'pickup_at',
        'delivery_charge',
        'coupon_id',
        'coupon_code',
        'coupon_discount',
        'security_deposit',
        'security_deposit_type',
        'security_deposit_value',
        'payment_detail',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'token_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'security_deposit_value' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'payment_detail' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'pickup_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the vendor that owns the cart
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the customer that owns the cart
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(VendorCustomer::class, 'customer_id');
    }

    /**
     * Get the cart items
     */
    public function items(): HasMany
    {
        return $this->hasMany(VendorCartItem::class);
    }

    /**
     * Get the coupon applied to this cart
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
