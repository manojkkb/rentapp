<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderActivity;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OrderActivityTimeline
{
    /**
     * @return Collection<int, array{at: \Carbon\CarbonInterface, label: string, description: string, icon: string, tone: string, meta: ?string, actor: ?string}>
     */
    public static function for(Order $order): Collection
    {
        $order->loadMissing(['activities.user']);

        if ($order->activities->isNotEmpty()) {
            return $order->activities
                ->sortByDesc(fn (OrderActivity $activity) => $activity->created_at?->getTimestamp() ?? 0)
                ->values()
                ->map(fn (OrderActivity $activity) => self::mapActivity($activity));
        }

        return self::synthesize($order);
    }

    /**
     * Insert timeline rows for an order that has no activity records yet.
     */
    public static function backfill(Order $order): void
    {
        if ($order->activities()->exists()) {
            return;
        }

        $order->loadMissing(['items', 'customer']);

        foreach (self::synthesize($order) as $entry) {
            $activity = new OrderActivity([
                'order_id' => $order->id,
                'user_id' => null,
                'action' => (string) ($entry['_action'] ?? 'legacy'),
                'old_values' => null,
                'new_values' => $entry['_payload'] ?? null,
                'ip_address' => null,
                'user_agent' => null,
            ]);

            $activity->created_at = $entry['at'];
            $activity->updated_at = $entry['at'];
            $activity->save();
        }
    }

    /**
     * @return Collection<int, array{at: \Carbon\CarbonInterface, label: string, description: string, icon: string, tone: string, meta: ?string, actor: ?string, _action?: string, _payload?: array<string, mixed>}>
     */
    private static function synthesize(Order $order): Collection
    {
        $order->loadMissing(['items', 'customer']);

        $events = collect();

        if ($order->created_at) {
            $events->push(self::entry(
                $order->created_at,
                __('vendor.order_activity_created'),
                __('vendor.order_activity_created_desc', ['number' => $order->order_number]),
                'fa-receipt',
                'slate',
                $order->customer?->name,
                OrderActivity::ACTION_CREATED,
                [
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer?->name,
                ],
            ));
        }

        if ($order->pickup_at) {
            $events->push(self::entry(
                $order->pickup_at,
                __('vendor.order_activity_pickup_scheduled'),
                __('vendor.order_activity_pickup_scheduled_desc'),
                'fa-store',
                'amber',
                null,
                OrderActivity::ACTION_PICKUP_SCHEDULED,
                ['pickup_at' => $order->pickup_at->toIso8601String()],
            ));
        }

        if ($order->delivery_at) {
            $events->push(self::entry(
                $order->delivery_at,
                __('vendor.order_activity_delivery_scheduled'),
                __('vendor.order_activity_delivery_scheduled_desc'),
                'fa-truck',
                'amber',
                $order->delivery_address ? mb_strimwidth((string) $order->delivery_address, 0, 80, '…') : null,
                OrderActivity::ACTION_DELIVERY_SCHEDULED,
                [
                    'delivery_at' => $order->delivery_at->toIso8601String(),
                    'delivery_address' => $order->delivery_address,
                ],
            ));
        }

        self::appendDeliveryEvents($order, $events);
        self::appendReturnEvents($order, $events);
        self::appendPaymentEvents($order, $events);

        if ($order->status === 'completed' && $order->updated_at) {
            $events->push(self::entry(
                $order->updated_at,
                __('vendor.order_activity_completed'),
                __('vendor.order_activity_completed_desc'),
                'fa-circle-check',
                'emerald',
                null,
                OrderActivity::ACTION_STATUS_CHANGED,
                ['from' => 'confirmed', 'to' => 'completed'],
            ));
        }

        if ($order->status === 'cancelled' && $order->updated_at) {
            $events->push(self::entry(
                $order->updated_at,
                __('vendor.order_activity_cancelled'),
                __('vendor.order_activity_cancelled_desc'),
                'fa-ban',
                'rose',
                null,
                OrderActivity::ACTION_STATUS_CHANGED,
                ['from' => (string) $order->status, 'to' => 'cancelled'],
            ));
        }

        return $events
            ->filter(fn (array $e) => $e['at'] !== null)
            ->sortByDesc(fn (array $e) => $e['at']->getTimestamp())
            ->values();
    }

  /**
     * @return array{at: \Carbon\CarbonInterface, label: string, description: string, icon: string, tone: string, meta: ?string, actor: ?string, actor_display: string, headline: string}
     */
    private static function mapActivity(OrderActivity $activity): array
    {
        $payload = is_array($activity->new_values) ? $activity->new_values : [];

        return self::presentEntry(match ($activity->action) {
            OrderActivity::ACTION_CREATED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_created'),
                __('vendor.order_activity_created_desc', ['number' => $payload['order_number'] ?? '—']),
                'fa-receipt',
                'slate',
                $payload['customer_name'] ?? null,
            ),

            OrderActivity::ACTION_PICKUP_SCHEDULED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_pickup_scheduled'),
                __('vendor.order_activity_pickup_scheduled_desc'),
                'fa-store',
                'amber',
            ),

            OrderActivity::ACTION_DELIVERY_SCHEDULED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_delivery_scheduled'),
                __('vendor.order_activity_delivery_scheduled_desc'),
                'fa-truck',
                'amber',
                isset($payload['delivery_address']) ? mb_strimwidth((string) $payload['delivery_address'], 0, 80, '…') : null,
            ),

            OrderActivity::ACTION_FULFILLMENT_UPDATED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_fulfillment_updated'),
                __('vendor.order_activity_fulfillment_updated_desc'),
                'fa-truck',
                'amber',
            ),

            OrderActivity::ACTION_ALL_DELIVERED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_delivered'),
                trans_choice('vendor.order_activity_delivered_desc', (int) ($payload['unit_count'] ?? 1), [
                    'count' => (int) ($payload['unit_count'] ?? 1),
                ]),
                'fa-truck-ramp-box',
                'teal',
            ),

            OrderActivity::ACTION_ITEM_DELIVERED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_item_delivered'),
                (string) ($payload['item_name'] ?? __('vendor.item')),
                'fa-truck-ramp-box',
                'teal',
                trans_choice('vendor.order_activity_qty_units', max(1, (int) ($payload['quantity'] ?? 1)), [
                    'count' => max(1, (int) ($payload['quantity'] ?? 1)),
                ]),
            ),

            OrderActivity::ACTION_ALL_RETURNED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_returned'),
                trans_choice('vendor.order_activity_returned_desc', (int) ($payload['unit_count'] ?? 1), [
                    'count' => (int) ($payload['unit_count'] ?? 1),
                ]),
                'fa-rotate-left',
                'emerald',
            ),

            OrderActivity::ACTION_ITEM_RETURNED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_item_returned'),
                (string) ($payload['item_name'] ?? __('vendor.item')),
                'fa-rotate-left',
                'emerald',
                __('vendor.order_activity_return_qty', [
                    'returned' => (int) ($payload['returned'] ?? 0),
                    'total' => (int) ($payload['total'] ?? 1),
                ]),
            ),

            OrderActivity::ACTION_PAYMENT => self::mapPaymentEntry($activity->created_at, $payload),

            OrderActivity::ACTION_PAYMENT_REMOVED => self::mapPaymentRemovedEntry($activity->created_at, $payload),

            OrderActivity::ACTION_STATUS_CHANGED => self::mapStatusEntry($activity->created_at, $payload),

            OrderActivity::ACTION_ITEM_ADDED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_item_added'),
                (string) ($payload['item_name'] ?? __('vendor.item')),
                'fa-cart-plus',
                'teal',
                trans_choice('vendor.order_activity_qty_units', max(1, (int) ($payload['quantity'] ?? 1)), [
                    'count' => max(1, (int) ($payload['quantity'] ?? 1)),
                ]),
            ),

            OrderActivity::ACTION_ITEM_UPDATED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_item_updated'),
                (string) ($payload['item_name'] ?? __('vendor.item')),
                'fa-pen',
                'amber',
                __('vendor.order_activity_item_qty_changed', [
                    'from' => (int) (($activity->old_values ?? [])['quantity'] ?? 0),
                    'to' => (int) ($payload['quantity'] ?? 0),
                ]),
            ),

            OrderActivity::ACTION_ITEM_REMOVED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_item_removed'),
                (string) ($payload['item_name'] ?? __('vendor.item')),
                'fa-trash',
                'rose',
                trans_choice('vendor.order_activity_qty_units', max(1, (int) ($payload['quantity'] ?? 1)), [
                    'count' => max(1, (int) ($payload['quantity'] ?? 1)),
                ]),
            ),

            OrderActivity::ACTION_DISCOUNT_APPLIED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_discount_applied'),
                __('vendor.order_activity_discount_applied_desc', [
                    'amount' => number_format((float) ($payload['discount_amount'] ?? 0), 2),
                ]),
                'fa-tag',
                'amber',
            ),

            OrderActivity::ACTION_DISCOUNT_REMOVED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_discount_removed'),
                __('vendor.order_activity_discount_removed_desc'),
                'fa-tag',
                'slate',
            ),

            OrderActivity::ACTION_COUPON_APPLIED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_coupon_applied'),
                __('vendor.order_activity_coupon_applied_desc', [
                    'code' => (string) ($payload['coupon_code'] ?? '—'),
                    'amount' => number_format((float) ($payload['coupon_discount'] ?? 0), 2),
                ]),
                'fa-ticket',
                'amber',
            ),

            OrderActivity::ACTION_COUPON_REMOVED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_coupon_removed'),
                __('vendor.order_activity_coupon_removed_desc', [
                    'code' => (string) ($payload['coupon_code'] ?? '—'),
                ]),
                'fa-ticket',
                'slate',
            ),

            OrderActivity::ACTION_EXTRA_CHARGE_ADDED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_extra_charge_added'),
                (string) ($payload['label'] ?? __('vendor.extra_charge')),
                'fa-plus',
                'amber',
                '₹'.number_format((float) ($payload['amount'] ?? 0), 2),
            ),

            OrderActivity::ACTION_EXTRA_CHARGE_REMOVED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_extra_charge_removed'),
                (string) ($payload['label'] ?? __('vendor.extra_charge')),
                'fa-minus',
                'rose',
                '₹'.number_format((float) ($payload['amount'] ?? 0), 2),
            ),

            OrderActivity::ACTION_SECURITY_DEPOSIT_UPDATED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_security_deposit_updated'),
                __('vendor.order_activity_security_deposit_updated_desc', [
                    'type' => str_replace('_', ' ', (string) ($payload['security_deposit_type'] ?? 'none')),
                    'amount' => number_format((float) ($payload['security_deposit'] ?? 0), 2),
                ]),
                'fa-shield-halved',
                'teal',
            ),

            OrderActivity::ACTION_BOOKING_UPDATED => self::entry(
                $activity->created_at,
                __('vendor.order_activity_booking_updated'),
                __('vendor.order_activity_booking_updated_desc'),
                'fa-calendar-days',
                'amber',
            ),

            OrderActivity::ACTION_RENTAL_CLEARED => self::mapRentalClearedEntry($activity->created_at, $payload),

            default => self::entry(
                $activity->created_at,
                __('vendor.order_activity_generic'),
                (string) $activity->action,
                'fa-clock',
                'slate',
            ),
        }, $activity);
    }

    private static function resolveActor(OrderActivity $activity): ?string
    {
        if ($activity->user?->name) {
            return (string) $activity->user->name;
        }

        $payload = is_array($activity->new_values) ? $activity->new_values : [];

        return isset($payload['actor_name']) && $payload['actor_name'] !== ''
            ? (string) $payload['actor_name']
            : null;
    }

    /**
     * @param  array{at: \Carbon\CarbonInterface, label: string, description: string, icon: string, tone: string, meta: ?string}  $entry
     * @return array{at: \Carbon\CarbonInterface, label: string, description: string, icon: string, tone: string, meta: ?string, actor: ?string, actor_display: string, headline: string}
     */
    private static function presentEntry(array $entry, OrderActivity $activity): array
    {
        $actor = self::resolveActor($activity);
        $actorDisplay = $actor ?: __('vendor.order_activity_system');
        $entry['actor'] = $actor;
        $entry['actor_display'] = $actorDisplay;
        $entry['headline'] = __('vendor.order_activity_headline', [
            'name' => $actorDisplay,
            'action' => $entry['label'],
        ]);

        return $entry;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{at: \Carbon\CarbonInterface, label: string, description: string, icon: string, tone: string, meta: ?string, actor: null}
     */
    private static function mapPaymentEntry(?Carbon $at, array $payload): array
    {
        $amount = round((float) ($payload['amount'] ?? 0), 2);
        $entryKind = $payload['entry_kind'] ?? 'payment';
        $isRefund = $entryKind === 'refund';
        $isDeposit = ($payload['payment_for'] ?? '') === 'security_deposit';
        $method = (string) ($payload['method'] ?? 'other');
        $methodLabel = $method === 'settlement'
            ? __('vendor.payment_method_settlement')
            : ucfirst(str_replace('_', ' ', $method));

        if ($isRefund) {
            return self::entry(
                $at ?? now(),
                $isDeposit ? __('vendor.order_activity_refund_deposit') : __('vendor.order_activity_refund_order'),
                '₹'.number_format($amount, 2).' · '.$methodLabel,
                'fa-arrow-rotate-left',
                'rose',
                $isDeposit ? __('vendor.payment_for_deposit_short') : __('vendor.payment_for_order_short'),
            );
        }

        return self::entry(
            $at ?? now(),
            $isDeposit ? __('vendor.order_activity_payment_deposit') : __('vendor.order_activity_payment_order'),
            '₹'.number_format($amount, 2).' · '.$methodLabel,
            'fa-wallet',
            'emerald',
            $isDeposit ? __('vendor.payment_for_deposit_short') : __('vendor.payment_for_order_short'),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{at: \Carbon\CarbonInterface, label: string, description: string, icon: string, tone: string, meta: ?string, actor: null}
     */
    private static function mapPaymentRemovedEntry(?Carbon $at, array $payload): array
    {
        $mapped = self::mapPaymentEntry($at, $payload);

        return self::entry(
            $at ?? now(),
            __('vendor.order_activity_payment_removed'),
            $mapped['description'],
            'fa-trash',
            'rose',
            $mapped['meta'],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{at: \Carbon\CarbonInterface, label: string, description: string, icon: string, tone: string, meta: ?string, actor: null}
     */
    private static function mapRentalClearedEntry(?Carbon $at, array $payload): array
    {
        $scope = (string) ($payload['scope'] ?? 'both');

        return match ($scope) {
            'return' => self::entry(
                $at ?? now(),
                __('vendor.order_activity_return_cleared'),
                __('vendor.order_activity_return_cleared_desc'),
                'fa-rotate-left',
                'rose',
            ),
            'delivery' => self::entry(
                $at ?? now(),
                __('vendor.order_activity_delivery_cleared'),
                __('vendor.order_activity_delivery_cleared_desc'),
                'fa-truck-ramp-box',
                'rose',
            ),
            default => self::entry(
                $at ?? now(),
                __('vendor.order_activity_rental_cleared'),
                __('vendor.order_activity_rental_cleared_desc'),
                'fa-eraser',
                'rose',
            ),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{at: \Carbon\CarbonInterface, label: string, description: string, icon: string, tone: string, meta: ?string, actor: null}
     */
    private static function mapStatusEntry(?Carbon $at, array $payload): array
    {
        $to = (string) ($payload['to'] ?? '');

        if ($to === 'completed') {
            return self::entry(
                $at ?? now(),
                __('vendor.order_activity_completed'),
                __('vendor.order_activity_completed_desc'),
                'fa-circle-check',
                'emerald',
            );
        }

        if ($to === 'cancelled') {
            return self::entry(
                $at ?? now(),
                __('vendor.order_activity_cancelled'),
                __('vendor.order_activity_cancelled_desc'),
                'fa-ban',
                'rose',
            );
        }

        if ($to === 'confirmed') {
            return self::entry(
                $at ?? now(),
                __('vendor.order_activity_confirmed'),
                __('vendor.order_activity_confirmed_desc'),
                'fa-check-circle',
                'teal',
            );
        }

        return self::entry(
            $at ?? now(),
            __('vendor.order_activity_status_changed'),
            __('vendor.order_activity_status_changed_desc', [
                'from' => ucfirst((string) ($payload['from'] ?? '—')),
                'to' => ucfirst($to !== '' ? $to : '—'),
            ]),
            'fa-arrows-rotate',
            'slate',
        );
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     */
    private static function appendDeliveryEvents(Order $order, Collection $events): void
    {
        $lines = $order->items->filter(fn (OrderItem $line) => $line->delivered_at !== null);

        if ($lines->isEmpty()) {
            return;
        }

        if ($order->delivered_at && $lines->count() === $order->items->count()) {
            $count = $lines->sum(fn (OrderItem $l) => max(1, (int) $l->quantity));
            $events->push(self::entry(
                $order->delivered_at,
                __('vendor.order_activity_delivered'),
                trans_choice('vendor.order_activity_delivered_desc', $count, ['count' => $count]),
                'fa-truck-ramp-box',
                'teal',
                null,
                OrderActivity::ACTION_ALL_DELIVERED,
                ['unit_count' => $count],
            ));

            return;
        }

        foreach ($lines as $line) {
            $qty = max(1, (int) $line->quantity);
            $events->push(self::entry(
                $line->delivered_at,
                __('vendor.order_activity_item_delivered'),
                $line->item_name ?? __('vendor.item'),
                'fa-truck-ramp-box',
                'teal',
                trans_choice('vendor.order_activity_qty_units', $qty, ['count' => $qty]),
                OrderActivity::ACTION_ITEM_DELIVERED,
                [
                    'order_item_id' => $line->id,
                    'item_name' => $line->item_name,
                    'quantity' => $qty,
                ],
            ));
        }
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     */
    private static function appendReturnEvents(Order $order, Collection $events): void
    {
        $returnedLines = $order->items->filter(function (OrderItem $line) {
            $qty = max(1, (int) $line->quantity);
            $returned = min($qty, max(0, (int) ($line->returned_qty ?? 0)));

            return $line->returned_at !== null || $returned > 0;
        });

        if ($returnedLines->isEmpty()) {
            return;
        }

        if ($order->returned_at && $returnedLines->count() === $order->items->count()) {
            $count = $returnedLines->sum(fn (OrderItem $l) => min(max(1, (int) $l->quantity), max(0, (int) ($l->returned_qty ?? 0))));
            $events->push(self::entry(
                $order->returned_at,
                __('vendor.order_activity_returned'),
                trans_choice('vendor.order_activity_returned_desc', $count, ['count' => $count]),
                'fa-rotate-left',
                'emerald',
                null,
                OrderActivity::ACTION_ALL_RETURNED,
                ['unit_count' => $count],
            ));

            return;
        }

        foreach ($returnedLines as $line) {
            $qty = max(1, (int) $line->quantity);
            $returned = min($qty, max(0, (int) ($line->returned_qty ?? 0)));
            $at = $line->returned_at ?? $order->returned_at ?? $line->updated_at;

            $events->push(self::entry(
                $at,
                __('vendor.order_activity_item_returned'),
                $line->item_name ?? __('vendor.item'),
                'fa-rotate-left',
                'emerald',
                __('vendor.order_activity_return_qty', ['returned' => $returned, 'total' => $qty]),
                OrderActivity::ACTION_ITEM_RETURNED,
                [
                    'order_item_id' => $line->id,
                    'item_name' => $line->item_name,
                    'returned' => $returned,
                    'total' => $qty,
                ],
            ));
        }
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     */
    private static function appendPaymentEvents(Order $order, Collection $events): void
    {
        $detail = is_array($order->payment_detail) ? $order->payment_detail : [];

        foreach ($detail as $row) {
            if (! is_array($row)) {
                continue;
            }

            $amount = round((float) ($row['amount'] ?? 0), 2);
            if ($amount < 0.01) {
                continue;
            }

            $at = null;
            foreach (['recorded_at', 'paid_on'] as $key) {
                if (empty($row[$key])) {
                    continue;
                }
                try {
                    $at = Carbon::parse($row[$key]);
                    break;
                } catch (\Throwable) {
                    continue;
                }
            }

            $mapped = self::mapPaymentEntry($at ?? $order->updated_at ?? now(), $row);
            $events->push($mapped + [
                '_action' => OrderActivity::ACTION_PAYMENT,
                '_payload' => $row,
            ]);
        }
    }

    /**
     * @return array{at: \Carbon\CarbonInterface, label: string, description: string, icon: string, tone: string, meta: ?string, actor: null, _action?: string, _payload?: array<string, mixed>}
     */
    private static function entry(
        ?Carbon $at,
        string $label,
        string $description,
        string $icon,
        string $tone,
        ?string $meta = null,
        ?string $action = null,
        ?array $payload = null,
    ): array {
        $system = __('vendor.order_activity_system');
        $row = [
            'at' => $at ?? now(),
            'label' => $label,
            'description' => $description,
            'icon' => $icon,
            'tone' => $tone,
            'meta' => $meta,
            'actor' => null,
            'actor_display' => $system,
            'headline' => __('vendor.order_activity_headline', [
                'name' => $system,
                'action' => $label,
            ]),
        ];

        if ($action !== null) {
            $row['_action'] = $action;
        }
        if ($payload !== null) {
            $row['_payload'] = $payload;
        }

        return $row;
    }
}
