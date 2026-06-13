<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OrderActivityLogger
{
    public static function log(
        Order $order,
        string $action,
        ?array $newValues = null,
        ?array $oldValues = null,
        ?Carbon $occurredAt = null,
    ): OrderActivity {
        $user = Auth::user();
        $request = request();

        if ($user?->name) {
            $newValues = array_merge($newValues ?? [], [
                'actor_name' => (string) $user->name,
            ]);
        }

        $activity = new OrderActivity([
            'order_id' => $order->id,
            'user_id' => $user?->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);

        if ($occurredAt) {
            $activity->created_at = $occurredAt;
            $activity->updated_at = $occurredAt;
        }

        $activity->save();

        return $activity;
    }

    /**
     * @return array<string, mixed>
     */
    public static function captureSnapshot(Order $order): array
    {
        $order->loadMissing('items');

        return [
            'status' => (string) $order->status,
            'start_at' => $order->start_at?->toIso8601String(),
            'end_at' => $order->end_at?->toIso8601String(),
            'fulfillment_type' => $order->fulfillment_type,
            'pickup_at' => $order->pickup_at?->toIso8601String(),
            'delivery_at' => $order->delivery_at?->toIso8601String(),
            'delivery_address' => $order->delivery_address,
            'discount_type' => $order->discount_type,
            'discount_value' => $order->discount_value,
            'discount_amount' => $order->discount_amount,
            'coupon_code' => $order->coupon_code,
            'coupon_discount' => $order->coupon_discount,
            'security_deposit_type' => $order->security_deposit_type,
            'security_deposit_value' => $order->security_deposit_value,
            'payment_detail' => is_array($order->payment_detail) ? $order->payment_detail : [],
            'items' => $order->items->mapWithKeys(fn (OrderItem $line) => [
                $line->id => [
                    'item_id' => $line->item_id,
                    'item_name' => $line->item_name,
                    'quantity' => (int) $line->quantity,
                    'billing_units' => $line->billing_units,
                ],
            ])->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $before
     */
    public static function logDiffFromSnapshot(Order $order, array $before): void
    {
        $order->refresh()->loadMissing('items');

        $oldStatus = (string) ($before['status'] ?? '');
        if ($oldStatus !== (string) $order->status) {
            self::logStatusChanged($order, $oldStatus, (string) $order->status);
        }

        $oldStart = $before['start_at'] ?? null;
        $oldEnd = $before['end_at'] ?? null;
        if ($oldStart !== $order->start_at?->toIso8601String()
            || $oldEnd !== $order->end_at?->toIso8601String()) {
            self::logBookingUpdated($order, [
                'start_at' => $oldStart,
                'end_at' => $oldEnd,
            ]);
        }

        $fulfillmentBefore = [
            'fulfillment_type' => $before['fulfillment_type'] ?? null,
            'pickup_at' => $before['pickup_at'] ?? null,
            'delivery_at' => $before['delivery_at'] ?? null,
            'delivery_address' => $before['delivery_address'] ?? null,
        ];
        $fulfillmentAfter = [
            'fulfillment_type' => $order->fulfillment_type,
            'pickup_at' => $order->pickup_at?->toIso8601String(),
            'delivery_at' => $order->delivery_at?->toIso8601String(),
            'delivery_address' => $order->delivery_address,
        ];
        if ($fulfillmentBefore !== $fulfillmentAfter) {
            self::logFulfillmentUpdated($order, $fulfillmentBefore);
        }

        if ((string) ($before['discount_type'] ?? '') !== (string) ($order->discount_type ?? '')
            || (float) ($before['discount_amount'] ?? 0) !== (float) ($order->discount_amount ?? 0)) {
            if ($order->discount_type && (float) ($order->discount_amount ?? 0) > 0) {
                self::logDiscountApplied($order, [
                    'discount_type' => $before['discount_type'] ?? null,
                    'discount_value' => $before['discount_value'] ?? null,
                    'discount_amount' => $before['discount_amount'] ?? 0,
                ]);
            } elseif ((float) ($before['discount_amount'] ?? 0) > 0) {
                self::logDiscountRemoved($order);
            }
        }

        if ((string) ($before['coupon_code'] ?? '') !== (string) ($order->coupon_code ?? '')) {
            if ($order->coupon_code) {
                self::logCouponApplied($order, [
                    'coupon_code' => $before['coupon_code'] ?? null,
                    'coupon_discount' => $before['coupon_discount'] ?? 0,
                ]);
            } elseif ($before['coupon_code'] ?? null) {
                self::logCouponRemoved($order, (string) $before['coupon_code']);
            }
        }

        if ((string) ($before['security_deposit_type'] ?? '') !== (string) ($order->security_deposit_type ?? '')
            || (float) ($before['security_deposit_value'] ?? 0) !== (float) ($order->security_deposit_value ?? 0)) {
            self::logSecurityDepositUpdated($order, [
                'security_deposit_type' => $before['security_deposit_type'] ?? null,
                'security_deposit_value' => $before['security_deposit_value'] ?? null,
            ]);
        }

        self::logPaymentDiff(
            $order,
            is_array($before['payment_detail'] ?? null) ? $before['payment_detail'] : [],
            is_array($order->payment_detail) ? $order->payment_detail : [],
        );

        /** @var array<int, array<string, mixed>> $beforeItems */
        $beforeItems = is_array($before['items'] ?? null) ? $before['items'] : [];
        $afterItems = $order->items->mapWithKeys(fn (OrderItem $line) => [
            $line->id => [
                'item_id' => $line->item_id,
                'item_name' => $line->item_name,
                'quantity' => (int) $line->quantity,
                'billing_units' => $line->billing_units,
            ],
        ])->all();

        foreach ($beforeItems as $lineId => $lineBefore) {
            if (! isset($afterItems[$lineId])) {
                self::logItemRemoved($order, [
                    'order_item_id' => (int) $lineId,
                    'item_id' => $lineBefore['item_id'] ?? null,
                    'item_name' => $lineBefore['item_name'] ?? null,
                    'quantity' => (int) ($lineBefore['quantity'] ?? 1),
                ]);
            }
        }

        foreach ($afterItems as $lineId => $lineAfter) {
            if (! isset($beforeItems[$lineId])) {
                self::logItemAdded($order, [
                    'order_item_id' => (int) $lineId,
                    'item_id' => $lineAfter['item_id'] ?? null,
                    'item_name' => $lineAfter['item_name'] ?? null,
                    'quantity' => (int) ($lineAfter['quantity'] ?? 1),
                ]);
                continue;
            }

            $lineBefore = $beforeItems[$lineId];
            if ((int) ($lineBefore['quantity'] ?? 0) !== (int) ($lineAfter['quantity'] ?? 0)
                || (string) ($lineBefore['billing_units'] ?? '') !== (string) ($lineAfter['billing_units'] ?? '')) {
                self::logItemUpdated($order, [
                    'order_item_id' => (int) $lineId,
                    'item_id' => $lineAfter['item_id'] ?? null,
                    'item_name' => $lineAfter['item_name'] ?? null,
                    'quantity' => (int) ($lineAfter['quantity'] ?? 1),
                    'billing_units' => $lineAfter['billing_units'] ?? null,
                ], $lineBefore);
            }
        }
    }

    public static function logCreated(Order $order): void
    {
        $order->loadMissing('customer');

        self::log($order, OrderActivity::ACTION_CREATED, [
            'order_number' => $order->order_number,
            'customer_name' => $order->customer?->name,
        ], null, $order->created_at);
    }

    public static function logPickupScheduled(Order $order, ?Carbon $at = null): void
    {
        if (! $order->pickup_at) {
            return;
        }

        self::log($order, OrderActivity::ACTION_PICKUP_SCHEDULED, [
            'pickup_at' => $order->pickup_at->toIso8601String(),
        ], null, $at ?? $order->pickup_at);
    }

    public static function logDeliveryScheduled(Order $order, ?Carbon $at = null): void
    {
        if (! $order->delivery_at) {
            return;
        }

        self::log($order, OrderActivity::ACTION_DELIVERY_SCHEDULED, [
            'delivery_at' => $order->delivery_at->toIso8601String(),
            'delivery_address' => $order->delivery_address,
        ], null, $at ?? $order->delivery_at);
    }

    public static function logFulfillmentUpdated(Order $order, array $oldValues): void
    {
        self::log($order, OrderActivity::ACTION_FULFILLMENT_UPDATED, [
            'fulfillment_type' => $order->fulfillment_type,
            'pickup_at' => $order->pickup_at?->toIso8601String(),
            'delivery_at' => $order->delivery_at?->toIso8601String(),
            'delivery_address' => $order->delivery_address,
        ], $oldValues);
    }

    public static function logStatusChanged(Order $order, string $from, string $to): void
    {
        self::log($order, OrderActivity::ACTION_STATUS_CHANGED, [
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * @param  array<string, mixed>  $newValues
     */
    public static function logItemAdded(Order $order, array $newValues): void
    {
        self::log($order, OrderActivity::ACTION_ITEM_ADDED, $newValues);
    }

    /**
     * @param  array<string, mixed>  $newValues
     * @param  array<string, mixed>  $oldValues
     */
    public static function logItemUpdated(Order $order, array $newValues, array $oldValues): void
    {
        self::log($order, OrderActivity::ACTION_ITEM_UPDATED, $newValues, $oldValues);
    }

    /**
     * @param  array<string, mixed>  $removed
     */
    public static function logItemRemoved(Order $order, array $removed): void
    {
        self::log($order, OrderActivity::ACTION_ITEM_REMOVED, $removed);
    }

    public static function logDiscountApplied(Order $order, ?array $oldValues = null): void
    {
        self::log($order, OrderActivity::ACTION_DISCOUNT_APPLIED, [
            'discount_type' => $order->discount_type,
            'discount_value' => $order->discount_value,
            'discount_amount' => $order->discount_amount,
        ], $oldValues);
    }

    public static function logDiscountRemoved(Order $order): void
    {
        self::log($order, OrderActivity::ACTION_DISCOUNT_REMOVED, []);
    }

    public static function logCouponApplied(Order $order, ?array $oldValues = null): void
    {
        self::log($order, OrderActivity::ACTION_COUPON_APPLIED, [
            'coupon_code' => $order->coupon_code,
            'coupon_discount' => $order->coupon_discount,
        ], $oldValues);
    }

    public static function logCouponRemoved(Order $order, ?string $removedCode = null): void
    {
        self::log($order, OrderActivity::ACTION_COUPON_REMOVED, [
            'coupon_code' => $removedCode ?? $order->coupon_code,
        ]);
    }

    /**
     * @param  array<string, mixed>  $chargeRow
     */
    public static function logExtraChargeAdded(Order $order, array $chargeRow): void
    {
        self::log($order, OrderActivity::ACTION_EXTRA_CHARGE_ADDED, $chargeRow);
    }

    /**
     * @param  array<string, mixed>  $chargeRow
     */
    public static function logExtraChargeRemoved(Order $order, array $chargeRow): void
    {
        self::log($order, OrderActivity::ACTION_EXTRA_CHARGE_REMOVED, $chargeRow);
    }

    public static function logSecurityDepositUpdated(Order $order, ?array $oldValues = null): void
    {
        self::log($order, OrderActivity::ACTION_SECURITY_DEPOSIT_UPDATED, [
            'security_deposit_type' => $order->security_deposit_type,
            'security_deposit_value' => $order->security_deposit_value,
            'security_deposit' => $order->security_deposit,
        ], $oldValues);
    }

    /**
     * @param  array<string, mixed>  $oldValues
     */
    public static function logBookingUpdated(Order $order, array $oldValues): void
    {
        self::log($order, OrderActivity::ACTION_BOOKING_UPDATED, [
            'start_at' => $order->start_at?->toIso8601String(),
            'end_at' => $order->end_at?->toIso8601String(),
        ], $oldValues);
    }

    public static function logRentalCleared(Order $order, string $scope): void
    {
        self::log($order, OrderActivity::ACTION_RENTAL_CLEARED, [
            'scope' => $scope,
        ]);
    }

    /**
     * @param  array<string, mixed>  $paymentRow
     */
    public static function logPayment(Order $order, array $paymentRow, ?Carbon $at = null): void
    {
        $occurredAt = $at ?? self::paymentTimestamp($paymentRow);

        self::log($order, OrderActivity::ACTION_PAYMENT, $paymentRow, null, $occurredAt);
    }

    /**
     * @param  array<string, mixed>  $paymentRow
     */
    public static function logPaymentRemoved(Order $order, array $paymentRow): void
    {
        self::log($order, OrderActivity::ACTION_PAYMENT_REMOVED, $paymentRow);
    }

    public static function logAllDelivered(Order $order, int $unitCount, ?Carbon $at = null): void
    {
        self::log($order, OrderActivity::ACTION_ALL_DELIVERED, [
            'unit_count' => $unitCount,
        ], null, $at ?? $order->delivered_at ?? now());
    }

    public static function logItemDelivered(OrderItem $line, ?Carbon $at = null): void
    {
        $line->loadMissing('order');

        self::log($line->order, OrderActivity::ACTION_ITEM_DELIVERED, [
            'order_item_id' => $line->id,
            'item_name' => $line->item_name,
            'quantity' => max(1, (int) $line->quantity),
        ], null, $at ?? $line->delivered_at ?? now());
    }

    public static function logAllReturned(Order $order, int $unitCount, ?Carbon $at = null): void
    {
        self::log($order, OrderActivity::ACTION_ALL_RETURNED, [
            'unit_count' => $unitCount,
        ], null, $at ?? $order->returned_at ?? now());
    }

    public static function logItemReturned(OrderItem $line, ?Carbon $at = null): void
    {
        $line->loadMissing('order');

        $qty = max(1, (int) $line->quantity);
        $returned = min($qty, max(0, (int) ($line->returned_qty ?? 0)));

        self::log($line->order, OrderActivity::ACTION_ITEM_RETURNED, [
            'order_item_id' => $line->id,
            'item_name' => $line->item_name,
            'returned' => $returned,
            'total' => $qty,
        ], null, $at ?? $line->returned_at ?? now());
    }

    /**
     * @param  list<array<string, mixed>>  $before
     * @param  list<array<string, mixed>>  $after
     */
    private static function logPaymentDiff(Order $order, array $before, array $after): void
    {
        $beforeBag = self::paymentMultiset($before);
        $afterBag = self::paymentMultiset($after);

        foreach ($afterBag['rows'] as $fingerprint => $row) {
            $added = ($afterBag['counts'][$fingerprint] ?? 0) - ($beforeBag['counts'][$fingerprint] ?? 0);
            for ($i = 0; $i < $added; $i++) {
                self::logPayment($order, $row);
            }
        }

        foreach ($beforeBag['rows'] as $fingerprint => $row) {
            $removed = ($beforeBag['counts'][$fingerprint] ?? 0) - ($afterBag['counts'][$fingerprint] ?? 0);
            for ($i = 0; $i < $removed; $i++) {
                self::logPaymentRemoved($order, $row);
            }
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{counts: array<string, int>, rows: array<string, array<string, mixed>>}
     */
    private static function paymentMultiset(array $rows): array
    {
        $counts = [];
        $mapped = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $fingerprint = self::paymentFingerprint($row);
            $counts[$fingerprint] = ($counts[$fingerprint] ?? 0) + 1;
            $mapped[$fingerprint] = $row;
        }

        return ['counts' => $counts, 'rows' => $mapped];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private static function paymentFingerprint(array $row): string
    {
        return hash('sha256', json_encode([
            $row['payment_for'] ?? '',
            $row['method'] ?? '',
            round((float) ($row['amount'] ?? 0), 2),
            $row['entry_kind'] ?? 'payment',
            $row['recorded_at'] ?? $row['paid_on'] ?? '',
        ]));
    }

    /**
     * @param  array<string, mixed>  $paymentRow
     */
    private static function paymentTimestamp(array $paymentRow): Carbon
    {
        foreach (['recorded_at', 'paid_on'] as $key) {
            if (empty($paymentRow[$key])) {
                continue;
            }

            try {
                return Carbon::parse($paymentRow[$key]);
            } catch (\Throwable) {
                continue;
            }
        }

        return now();
    }
}
