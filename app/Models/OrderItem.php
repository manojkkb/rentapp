<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /** @var list<string> */
    public const ITEM_STATUSES = [
        'reserved',
        'confirmed',
        'picked_up',
        'in_use',
        'return_requested',
        'partially_returned',
        'returned',
        'damaged',
        'lost',
        'cancelled',
    ];

    /** @var list<string> */
    public const CONDITION_LEVELS = [
        'excellent',
        'good',
        'average',
        'damaged',
    ];

    protected $fillable = [
        'order_id',
        'item_id',
        'item_variant_id',
        'item_name',
        'variant_label',
        'price',
        'quantity',
        'returned_qty',
        'damaged_qty',
        'lost_qty',
        'rental_period',
        'rent_type',
        'billing_units',
        'start_at',
        'end_at',
        'delivered_at',
        'returned_at',
        'rental_duration_minutes',
        'rent_days',
        'total_price',
        'security_deposit',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'late_fee',
        'damage_fee',
        'lost_fee',
        'refund_amount',
        'final_amount',
        'item_status',
        'condition_out',
        'condition_in',
        'damage_notes',
        'customer_notes',
        'admin_notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'billing_units' => 'decimal:4',
        'total_price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'damage_fee' => 'decimal:2',
        'lost_fee' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
        'quantity' => 'integer',
        'rent_days' => 'integer',
        'rental_duration_minutes' => 'integer',
        'item_variant_id' => 'integer',
        'returned_qty' => 'integer',
        'damaged_qty' => 'integer',
        'lost_qty' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Line amount from snapshot fields (price × quantity × billing units by rental_period).
     */
    public function lineSubtotal(): float
    {
        $price = (float) $this->price;
        $qty = (int) $this->quantity;
        $type = $this->rental_period ?? 'per_day';

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
     * Persist catalog line total and mirror billing snapshot fields used on the line.
     */
    public function refreshLineTotals(): void
    {
        $this->refresh();
        $tp = $this->lineSubtotal();
        $this->total_price = $tp;
        $this->subtotal = $tp;
        $this->rent_type = $this->rental_period ?: 'per_day';
        $this->final_amount = round(
            (float) $tp
                - (float) ($this->discount_amount ?? 0)
                + (float) ($this->tax_amount ?? 0)
                + (float) ($this->late_fee ?? 0)
                + (float) ($this->damage_fee ?? 0)
                + (float) ($this->lost_fee ?? 0)
                - (float) ($this->refund_amount ?? 0),
            2
        );
        $this->save();
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

    public function itemVariant(): BelongsTo
    {
        return $this->belongsTo(ItemVariant::class, 'item_variant_id');
    }

    public function usesVariant(): bool
    {
        return $this->item_variant_id !== null;
    }
}
