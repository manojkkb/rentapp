<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderActivity extends Model
{
    public const ACTION_CREATED = 'created';

    public const ACTION_PICKUP_SCHEDULED = 'pickup_scheduled';

    public const ACTION_DELIVERY_SCHEDULED = 'delivery_scheduled';

    public const ACTION_FULFILLMENT_UPDATED = 'fulfillment_updated';

    public const ACTION_ALL_DELIVERED = 'all_delivered';

    public const ACTION_ITEM_DELIVERED = 'item_delivered';

    public const ACTION_ALL_RETURNED = 'all_returned';

    public const ACTION_ITEM_RETURNED = 'item_returned';

    public const ACTION_PAYMENT = 'payment';

    public const ACTION_PAYMENT_REMOVED = 'payment_removed';

    public const ACTION_STATUS_CHANGED = 'status_changed';

    public const ACTION_ITEM_ADDED = 'item_added';

    public const ACTION_ITEM_UPDATED = 'item_updated';

    public const ACTION_ITEM_REMOVED = 'item_removed';

    public const ACTION_DISCOUNT_APPLIED = 'discount_applied';

    public const ACTION_DISCOUNT_REMOVED = 'discount_removed';

    public const ACTION_COUPON_APPLIED = 'coupon_applied';

    public const ACTION_COUPON_REMOVED = 'coupon_removed';

    public const ACTION_EXTRA_CHARGE_ADDED = 'extra_charge_added';

    public const ACTION_EXTRA_CHARGE_REMOVED = 'extra_charge_removed';

    public const ACTION_SECURITY_DEPOSIT_UPDATED = 'security_deposit_updated';

    public const ACTION_BOOKING_UPDATED = 'booking_updated';

    public const ACTION_RENTAL_CLEARED = 'rental_cleared';

    protected $fillable = [
        'order_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
