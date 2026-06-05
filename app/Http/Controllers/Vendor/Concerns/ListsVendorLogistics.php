<?php

namespace App\Http\Controllers\Vendor\Concerns;

use App\Models\Order;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ListsVendorLogistics
{
    protected function deliveriesPaginator(Vendor $vendor): LengthAwarePaginator
    {
        $today = now()->toDateString();
        $now = now()->format('Y-m-d H:i:s');

        return $vendor->orders()
            ->whereNull('delivered_at')
            ->whereIn('status', ['confirmed'])
            ->with(['customer', 'items.item'])
            ->orderByRaw('case
                when (start_at is not null and date(start_at) = ?)
                    or (pickup_at is not null and date(pickup_at) = ?)
                then 0
                when coalesce(start_at, pickup_at, created_at) < ?
                then 1
                else 2
            end asc, coalesce(start_at, pickup_at, created_at) asc', [$today, $today, $now])
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Order $order) => $this->mapDeliveryRow($order));
    }

    protected function returnsPaginator(Vendor $vendor): LengthAwarePaginator
    {
        $today = now()->toDateString();
        $now = now()->format('Y-m-d H:i:s');

        return $vendor->orders()
            ->whereNull('returned_at')
            ->whereNotNull('delivered_at')
            ->whereIn('status', ['confirmed'])
            ->with(['customer', 'items.item'])
            ->orderByRaw('case
                when end_at is not null and date(end_at) = ? then 0
                when coalesce(end_at, created_at) < ? then 1
                else 2
            end asc, coalesce(end_at, created_at) asc', [$today, $now])
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Order $order) => $this->mapReturnRow($order));
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapDeliveryRow(Order $order): array
    {
        $handoffAt = ($order->fulfillment_type ?? 'pickup') === 'delivery'
            ? ($order->delivery_at ?? $order->start_at)
            : ($order->start_at ?? $order->pickup_at);
        $sched = $this->logisticsDayTime($handoffAt);

        $totalUnits = 0;
        $deliveredUnits = 0;
        $lines = $order->items->map(function ($line) use (&$totalUnits, &$deliveredUnits) {
            $qty = max(1, (int) $line->quantity);
            $totalUnits += $qty;
            if ($line->delivered_at !== null) {
                $deliveredUnits += $qty;
            }

            return [
                'id' => (int) $line->id,
                'name' => $line->item?->name ?? $line->item_name ?? __('vendor.item'),
                'quantity' => $qty,
                'delivered' => $line->delivered_at !== null,
            ];
        })->values()->all();

        $customerName = $order->customer->name ?? 'N/A';
        $eventName = trim((string) ($order->event_name ?? ''));
        $titleLine = $eventName !== ''
            ? ($customerName !== 'N/A' ? $eventName.' ('.$customerName.')' : $eventName)
            : $customerName;

        return [
            'id' => $order->uuid,
            'order_number' => $order->order_number,
            'event_name' => $eventName !== '' ? $eventName : null,
            'customer_name' => $customerName,
            'title_line' => $titleLine,
            'fulfillment_type' => $order->fulfillment_type ?? 'pickup',
            'day_line' => $sched['day_line'],
            'time_line' => $sched['time_line'],
            'is_highlight_today' => $sched['is_today'],
            'is_highlight_tomorrow' => $sched['is_tomorrow'],
            'total_units' => $totalUnits,
            'delivered_units' => $deliveredUnits,
            'lines' => $lines,
            'lines_b64' => base64_encode(json_encode($lines)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapReturnRow(Order $order): array
    {
        $sched = $this->logisticsDayTime($order->end_at);

        $totalUnits = 0;
        $returnedUnits = 0;
        $lines = $order->items->map(function ($line) use (&$totalUnits, &$returnedUnits) {
            $qty = max(1, (int) $line->quantity);
            $returnedQty = min($qty, max(0, (int) ($line->returned_qty ?? 0)));
            $totalUnits += $qty;
            $returnedUnits += $returnedQty;
            $fullyReturned = $line->returned_at !== null || $returnedQty >= $qty;

            return [
                'id' => (int) $line->id,
                'name' => $line->item?->name ?? $line->item_name ?? __('vendor.item'),
                'order_qty' => $qty,
                'returned_qty' => $returnedQty,
                'returnable_qty' => max(0, $qty - $returnedQty),
                'delivered' => $line->delivered_at !== null,
                'fully_returned' => $fullyReturned,
            ];
        })->values()->all();

        $customerName = $order->customer->name ?? 'N/A';
        $eventName = trim((string) ($order->event_name ?? ''));
        $titleLine = $eventName !== ''
            ? ($customerName !== 'N/A' ? $eventName.' ('.$customerName.')' : $eventName)
            : $customerName;

        return [
            'id' => $order->uuid,
            'order_number' => $order->order_number,
            'event_name' => $eventName !== '' ? $eventName : null,
            'customer_name' => $customerName,
            'title_line' => $titleLine,
            'fulfillment_type' => $order->fulfillment_type ?? 'pickup',
            'day_line' => $sched['day_line'],
            'time_line' => $sched['time_line'],
            'is_highlight_today' => $sched['is_today'],
            'is_highlight_tomorrow' => $sched['is_tomorrow'],
            'total_units' => $totalUnits,
            'returned_units' => $returnedUnits,
            'lines' => $lines,
            'lines_b64' => base64_encode(json_encode($lines)),
        ];
    }

    /**
     * @return array{day_line: string, time_line: string, is_today: bool, is_tomorrow: bool}
     */
    protected function logisticsDayTime(?Carbon $at): array
    {
        if ($at === null) {
            return [
                'day_line' => '—',
                'time_line' => '',
                'is_today' => false,
                'is_tomorrow' => false,
            ];
        }

        $at = $at->copy()->timezone((string) config('app.timezone'));
        $now = now()->timezone((string) config('app.timezone'));

        $isToday = $at->isSameDay($now);
        $isTomorrow = $at->isSameDay($now->copy()->addDay());

        if ($isToday) {
            $dayLine = __('vendor.dashboard_handoff_today');
        } elseif ($isTomorrow) {
            $dayLine = __('vendor.dashboard_handoff_tomorrow');
        } elseif ($at->greaterThan($now)) {
            $daysUntil = (int) $now->copy()->startOfDay()->diffInDays($at->copy()->startOfDay(), false);
            if ($daysUntil > 0 && $daysUntil <= 6) {
                $dayLine = $at->translatedFormat('l');
            } else {
                $dayLine = $at->translatedFormat('d M Y');
            }
        } else {
            $dayLine = $at->translatedFormat('d M Y');
        }

        return [
            'day_line' => $dayLine,
            'time_line' => $at->format('g:i A'),
            'is_today' => $isToday,
            'is_tomorrow' => $isTomorrow,
        ];
    }
}
