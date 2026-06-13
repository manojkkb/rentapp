<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\RoutesByUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasUuid, RoutesByUuid, SoftDeletes;

    /** @var list<string> */
    public const STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];

    protected $fillable = [
        'uuid',
        'order_number',
        'event_name',
        'customer_id',
        'vendor_id',
        'start_at',
        'end_at',
        'fulfillment_type',
        'delivery_address',
        'pickup_at',
        'delivery_at',
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
        'late_fees_total',
        'damage_fees_total',
        'lost_fees_total',
        'refunds_total',
        'internal_notes',
        'paid_amount',
        'status',
        'delivered_at',
        'returned_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'pickup_at' => 'datetime',
        'delivery_at' => 'datetime',
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
        'late_fees_total' => 'decimal:2',
        'damage_fees_total' => 'decimal:2',
        'lost_fees_total' => 'decimal:2',
        'refunds_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
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

    public function activities(): HasMany
    {
        return $this->hasMany(OrderActivity::class);
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
            'confirmed' => ['completed', 'cancelled'],
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

    /**
     * Net payments and refunds split between rental/order amount and security deposit.
     *
     * @return array{
     *     order_paid: float,
     *     deposit_paid: float,
     *     refund_order: float,
     *     refund_deposit: float,
     *     refund_total: float,
     *     order_due: float,
     *     deposit_due: float,
     *     total_due: float
     * }
     */
    public function paymentSummary(): array
    {
        $detail = is_array($this->payment_detail) ? $this->payment_detail : [];
        $orderNet = 0.0;
        $depositNet = 0.0;
        $refundOrder = 0.0;
        $refundDeposit = 0.0;

        foreach ($detail as $row) {
            if (! is_array($row)) {
                continue;
            }

            $amount = round((float) ($row['amount'] ?? 0), 2);
            $entryKind = $row['entry_kind'] ?? 'payment';
            $paymentFor = $row['payment_for'] ?? 'order_amount';
            $isDeposit = $paymentFor === 'security_deposit';

            if ($entryKind === 'refund') {
                if ($isDeposit) {
                    $refundDeposit += $amount;
                    $depositNet -= $amount;
                } else {
                    $refundOrder += $amount;
                    $orderNet -= $amount;
                }

                continue;
            }

            if ($isDeposit) {
                $depositNet += $amount;
            } else {
                $orderNet += $amount;
            }
        }

        $orderTotal = round((float) $this->grand_total, 2);
        $depositTotal = round((float) ($this->security_deposit ?? 0), 2);
        $orderDue = max(0.0, round($orderTotal - $orderNet, 2));
        $depositDue = max(0.0, round($depositTotal - $depositNet, 2));

        return [
            'order_paid' => round($orderNet, 2),
            'deposit_paid' => round($depositNet, 2),
            'refund_order' => round($refundOrder, 2),
            'refund_deposit' => round($refundDeposit, 2),
            'refund_total' => round($refundOrder + $refundDeposit, 2),
            'order_due' => $orderDue,
            'deposit_due' => $depositDue,
            'total_due' => round($orderDue + $depositDue, 2),
        ];
    }

    /**
     * Pending rental, payment, and refund items to review before marking the order completed.
     *
     * @return array{
     *     undelivered: list<array{name: string, quantity: int}>,
     *     not_returned: list<array{name: string, quantity: int, returned: int, pending: int}>,
     *     order_due: float,
     *     deposit_due: float,
     *     total_due: float,
     *     order_refund_pending: float,
     *     deposit_refund_pending: float,
     *     refund_pending_total: float,
     *     has_pending: bool
     * }
     */
    public function completionChecklist(): array
    {
        $this->loadMissing('items.item');

        $payment = $this->paymentSummary();
        $undelivered = [];
        $notReturned = [];

        foreach ($this->items as $line) {
            $name = trim((string) ($line->item_name ?: $line->item?->name ?: __('vendor.item')));
            $qty = max(1, (int) $line->quantity);
            $returned = min($qty, max(0, (int) ($line->returned_qty ?? 0)));

            if (! $line->delivered_at) {
                $undelivered[] = [
                    'name' => $name,
                    'quantity' => $qty,
                ];
            }

            if ($line->delivered_at && $returned < $qty) {
                $notReturned[] = [
                    'name' => $name,
                    'quantity' => $qty,
                    'returned' => $returned,
                    'pending' => $qty - $returned,
                ];
            }
        }

        $orderTotal = round((float) $this->grand_total, 2);
        $orderRefundPending = max(0.0, round($payment['order_paid'] - $orderTotal, 2));
        $depositRefundPending = max(0.0, round($payment['deposit_paid'] - $payment['refund_deposit'], 2));

        $hasPending = $undelivered !== []
            || $notReturned !== []
            || $payment['order_due'] > 0.009
            || $payment['deposit_due'] > 0.009
            || $orderRefundPending > 0.009
            || $depositRefundPending > 0.009;

        return [
            'undelivered' => $undelivered,
            'not_returned' => $notReturned,
            'order_due' => $payment['order_due'],
            'deposit_due' => $payment['deposit_due'],
            'total_due' => $payment['total_due'],
            'order_refund_pending' => $orderRefundPending,
            'deposit_refund_pending' => $depositRefundPending,
            'refund_pending_total' => round($orderRefundPending + $depositRefundPending, 2),
            'has_pending' => $hasPending,
        ];
    }
}
