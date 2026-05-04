<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    public const STATUSES = ['pending', 'confirmed', 'ongoing', 'completed', 'cancelled'];

    protected $fillable = [
        'order_number',
        'customer_id',
        'vendor_id',
        'start_at',
        'end_at',
        'fulfillment_type',
        'delivery_address',
        'pickup_at',
        'delivery_charge',
        'discount_type',
        'discount_value',
        'discount_amount',
        'coupon_id',
        'coupon_code',
        'coupon_discount',
        'security_deposit',
        'security_deposit_type',
        'security_deposit_value',
        'token_amount',
        'payment_detail',
        'sub_total',
        'tax_total',
        'discount_total',
        'grand_total',
        'extra_charges_total',
        'extra_charges_lines',
        'paid_amount',
        'status',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'pickup_at' => 'datetime',
        'delivery_charge' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'security_deposit_value' => 'decimal:2',
        'token_amount' => 'decimal:2',
        'payment_detail' => 'array',
        'sub_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'extra_charges_total' => 'decimal:2',
        'extra_charges_lines' => 'array',
        'paid_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the vendor that owns the order
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the customer that owns the order
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(VendorCustomer::class, 'customer_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    
    /**
     * Get the review for this order
     */
    public function review()
    {
        return $this->hasOne(CustomerReview::class);
    }
    
    /**
     * Check if this order has been reviewed
     */
    public function hasReview(): bool
    {
        return $this->review()->exists();
    }

    /**
     * Valid next statuses (forward-only workflow; no moving back to earlier steps).
     *
     * @return list<string>
     */
    public function allowedNextStatuses(): array
    {
        return match ($this->status) {
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['ongoing', 'cancelled'],
            'ongoing' => ['completed', 'cancelled'],
            'completed', 'cancelled' => [],
            default => [],
        };
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $newStatus = strtolower(trim($newStatus));
        if ($newStatus === (string) $this->status) {
            return true;
        }

        return in_array($newStatus, $this->allowedNextStatuses(), true);
    }

    public function isLockedForEditing(): bool
    {
        return in_array($this->status, ['completed', 'cancelled'], true);
    }
}
