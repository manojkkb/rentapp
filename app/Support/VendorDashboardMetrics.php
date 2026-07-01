<?php

namespace App\Support;

use App\Http\Controllers\Vendor\Concerns\ListsVendorLogistics;
use App\Models\Order;
use App\Models\Vendor;
use Carbon\Carbon;
use InvalidArgumentException;

class VendorDashboardMetrics
{
    use ListsVendorLogistics;

    public const PERIODS = ['today', 'weekly', 'monthly', 'yearly', 'custom'];

    /**
     * @return array{
     *     total_orders: int,
     *     monthly_orders: int,
     *     total_revenue: float,
     *     monthly_revenue: float,
     *     order_status_counts: array<string, int>,
     *     pending_orders: int,
     *     outstanding_balance: float,
     *     orders_with_balance_due: int,
     *     out_on_rent: int
     * }
     */
    public static function summary(Vendor $vendor): array
    {
        return (new self)->buildSummary($vendor);
    }

    /**
     * @return array{count: int, orders: list<array<string, mixed>>}
     */
    public static function deliveryPreview(Vendor $vendor, int $limit = 5): array
    {
        return (new self)->buildDeliveryPreview($vendor, $limit);
    }

    /**
     * @return array{count: int, orders: list<array<string, mixed>>}
     */
    public static function returnPreview(Vendor $vendor, int $limit = 5): array
    {
        return (new self)->buildReturnPreview($vendor, $limit);
    }

    /**
     * @return array<string, mixed>
     */
    public static function apiPayload(
        Vendor $vendor,
        string $period = 'monthly',
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        return (new self)->buildApiPayload($vendor, $period, $startDate, $endDate);
    }

    /**
     * @return array{from: Carbon, to: Carbon}
     */
    public static function periodBounds(string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        $now = now();

        return match ($period) {
            'today' => [
                'from' => $now->copy()->startOfDay(),
                'to' => $now->copy()->endOfDay(),
            ],
            'weekly' => [
                'from' => $now->copy()->startOfWeek(),
                'to' => $now->copy()->endOfWeek(),
            ],
            'monthly' => [
                'from' => $now->copy()->startOfMonth(),
                'to' => $now->copy()->endOfMonth(),
            ],
            'yearly' => [
                'from' => $now->copy()->startOfYear(),
                'to' => $now->copy()->endOfYear(),
            ],
            'custom' => self::customPeriodBounds($startDate, $endDate),
            default => throw new InvalidArgumentException("Unsupported dashboard period [{$period}]."),
        };
    }

    /**
     * @return array{from: Carbon, to: Carbon}
     */
    private static function customPeriodBounds(?string $startDate, ?string $endDate): array
    {
        if (! $startDate || ! $endDate) {
            throw new InvalidArgumentException('start_date and end_date are required when period is custom.');
        }

        return [
            'from' => Carbon::parse($startDate)->startOfDay(),
            'to' => Carbon::parse($endDate)->endOfDay(),
        ];
    }

    /**
     * @return array{
     *     total_orders: int,
     *     monthly_orders: int,
     *     total_revenue: float,
     *     monthly_revenue: float,
     *     order_status_counts: array<string, int>,
     *     pending_orders: int,
     *     outstanding_balance: float,
     *     orders_with_balance_due: int,
     *     out_on_rent: int
     * }
     */
    private function buildSummary(Vendor $vendor): array
    {
        $totalOrders = $vendor->orders()->count();
        $monthlyOrders = $vendor->orders()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $totalRevenue = (float) $vendor->orders()->where('status', 'completed')->sum('grand_total');
        $monthlyRevenue = (float) $vendor->orders()
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('grand_total');

        $statusCountRows = $vendor->orders()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();
        $orderStatusCounts = [];
        foreach (Order::STATUSES as $status) {
            $orderStatusCounts[$status] = (int) ($statusCountRows[$status] ?? 0);
        }

        $pendingOrders = (int) ($orderStatusCounts['pending'] ?? 0);

        $outstandingBalance = (float) $vendor->orders()
            ->whereIn('status', ['pending', 'confirmed'])
            ->selectRaw('COALESCE(SUM(GREATEST(0, COALESCE(grand_total, 0) + COALESCE(security_deposit, 0) - COALESCE(paid_amount, 0))), 0) as balance')
            ->value('balance');

        $ordersWithBalanceDue = (int) $vendor->orders()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereRaw('(COALESCE(grand_total, 0) + COALESCE(security_deposit, 0) - COALESCE(paid_amount, 0)) > 0.009')
            ->count();

        $outOnRent = $vendor->orders()
            ->where('status', 'confirmed')
            ->whereNotNull('delivered_at')
            ->whereNull('returned_at')
            ->count();

        return [
            'total_orders' => $totalOrders,
            'monthly_orders' => $monthlyOrders,
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'order_status_counts' => $orderStatusCounts,
            'pending_orders' => $pendingOrders,
            'outstanding_balance' => $outstandingBalance,
            'orders_with_balance_due' => $ordersWithBalanceDue,
            'out_on_rent' => $outOnRent,
        ];
    }

    /**
     * @return array{count: int, orders: list<array<string, mixed>>}
     */
    private function buildDeliveryPreview(Vendor $vendor, int $limit): array
    {
        $today = now()->toDateString();
        $now = now()->format('Y-m-d H:i:s');

        $query = $vendor->orders()
            ->whereNull('delivered_at')
            ->whereIn('status', ['confirmed']);

        $count = (clone $query)->count();

        $orders = (clone $query)
            ->with(['customer' => fn ($q) => $q->withTrashed(), 'items.item'])
            ->orderByRaw('case
                when (start_at is not null and date(start_at) = ?)
                    or (pickup_at is not null and date(pickup_at) = ?)
                    or (delivery_at is not null and date(delivery_at) = ?)
                then 0
                when coalesce(delivery_at, start_at, pickup_at, created_at) < ?
                then 1
                else 2
            end asc, coalesce(delivery_at, start_at, pickup_at, created_at) asc', [$today, $today, $today, $now])
            ->take($limit)
            ->get()
            ->map(fn (Order $order) => $this->mapDeliveryRow($order))
            ->values()
            ->all();

        return [
            'count' => $count,
            'orders' => $orders,
        ];
    }

    /**
     * @return array{count: int, orders: list<array<string, mixed>>}
     */
    private function buildReturnPreview(Vendor $vendor, int $limit): array
    {
        $today = now()->toDateString();
        $now = now()->format('Y-m-d H:i:s');

        $query = $vendor->orders()
            ->whereNull('returned_at')
            ->whereNotNull('delivered_at')
            ->whereIn('status', ['confirmed']);

        $count = (clone $query)->count();

        $orders = (clone $query)
            ->with(['customer' => fn ($q) => $q->withTrashed(), 'items.item'])
            ->orderByRaw('case
                when end_at is not null and date(end_at) = ? then 0
                when coalesce(end_at, created_at) < ? then 1
                else 2
            end asc, coalesce(end_at, created_at) asc', [$today, $now])
            ->take($limit)
            ->get()
            ->map(fn (Order $order) => $this->mapReturnRow($order))
            ->values()
            ->all();

        return [
            'count' => $count,
            'orders' => $orders,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildApiPayload(
        Vendor $vendor,
        string $period,
        ?string $startDate = null,
        ?string $endDate = null,
    ): array {
        $bounds = self::periodBounds($period, $startDate, $endDate);
        $summary = $this->buildApiSummary($vendor, $bounds['from'], $bounds['to']);
        $deliveries = $this->buildApiDeliveryPreview($vendor);
        $returns = $this->buildApiReturnPreview($vendor);

        return [
            'filter' => $period,
            'start_date' => $bounds['from']->toDateString(),
            'end_date' => $bounds['to']->toDateString(),
            'total_revenue' => $summary['total_revenue'],
            'total_bookings' => $summary['total_bookings'],
            'total_dues' => $summary['total_dues'],
            'orders_with_dues' => $summary['orders_with_dues'],
            'order_status_counts' => $summary['order_status_counts'],
            'deliveries' => $deliveries,
            'returns' => $returns,
        ];
    }

    /**
     * @return array{
     *     total_revenue: float,
     *     total_bookings: int,
     *     order_status_counts: array<string, int>,
     *     total_dues: float,
     *     orders_with_dues: int
     * }
     */
    private function buildApiSummary(Vendor $vendor, Carbon $from, Carbon $to): array
    {
        $periodOrders = $vendor->orders()->whereBetween('created_at', [$from, $to]);

        $totalBookings = (clone $periodOrders)->count();
        $totalRevenue = (float) (clone $periodOrders)
            ->where('status', 'completed')
            ->sum('grand_total');

        $statusCountRows = (clone $periodOrders)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();
        $orderStatusCounts = [];
        foreach (Order::STATUSES as $status) {
            $orderStatusCounts[$status] = (int) ($statusCountRows[$status] ?? 0);
        }

        $outstandingBalance = (float) $vendor->orders()
            ->whereIn('status', ['pending', 'confirmed'])
            ->selectRaw('COALESCE(SUM(GREATEST(0, COALESCE(grand_total, 0) + COALESCE(security_deposit, 0) - COALESCE(paid_amount, 0))), 0) as balance')
            ->value('balance');

        $ordersWithBalanceDue = (int) $vendor->orders()
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereRaw('(COALESCE(grand_total, 0) + COALESCE(security_deposit, 0) - COALESCE(paid_amount, 0)) > 0.009')
            ->count();

        return [
            'total_revenue' => $totalRevenue,
            'total_bookings' => $totalBookings,
            'order_status_counts' => $orderStatusCounts,
            'total_dues' => $outstandingBalance,
            'orders_with_dues' => $ordersWithBalanceDue,
        ];
    }

    /**
     * @return array{count: int, orders: list<array<string, mixed>>}
     */
    private function buildApiDeliveryPreview(Vendor $vendor, int $limit = 5): array
    {
        $today = now()->toDateString();
        $now = now()->format('Y-m-d H:i:s');

        $query = $vendor->orders()
            ->whereNull('delivered_at')
            ->whereIn('status', ['confirmed']);

        $count = (clone $query)->count();

        $orders = (clone $query)
            ->with(['customer' => fn ($q) => $q->withTrashed(), 'items'])
            ->orderByRaw('case
                when (start_at is not null and date(start_at) = ?)
                    or (pickup_at is not null and date(pickup_at) = ?)
                    or (delivery_at is not null and date(delivery_at) = ?)
                then 0
                when coalesce(delivery_at, start_at, pickup_at, created_at) < ?
                then 1
                else 2
            end asc, coalesce(delivery_at, start_at, pickup_at, created_at) asc', [$today, $today, $today, $now])
            ->take($limit)
            ->get()
            ->map(fn (Order $order) => $this->mapApiDeliveryPreview($order))
            ->values()
            ->all();

        return [
            'count' => $count,
            'orders' => $orders,
        ];
    }

    /**
     * @return array{count: int, orders: list<array<string, mixed>>}
     */
    private function buildApiReturnPreview(Vendor $vendor, int $limit = 5): array
    {
        $today = now()->toDateString();
        $now = now()->format('Y-m-d H:i:s');

        $query = $vendor->orders()
            ->whereNull('returned_at')
            ->whereNotNull('delivered_at')
            ->whereIn('status', ['confirmed']);

        $count = (clone $query)->count();

        $orders = (clone $query)
            ->with(['customer' => fn ($q) => $q->withTrashed(), 'items'])
            ->orderByRaw('case
                when end_at is not null and date(end_at) = ? then 0
                when coalesce(end_at, created_at) < ? then 1
                else 2
            end asc, coalesce(end_at, created_at) asc', [$today, $now])
            ->take($limit)
            ->get()
            ->map(fn (Order $order) => $this->mapApiReturnPreview($order))
            ->values()
            ->all();

        return [
            'count' => $count,
            'orders' => $orders,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapApiDeliveryPreview(Order $order): array
    {
        $handoffAt = ($order->fulfillment_type ?? 'pickup') === 'delivery'
            ? ($order->delivery_at ?? $order->start_at)
            : ($order->start_at ?? $order->pickup_at);
        $sched = $this->logisticsDayTime($handoffAt);
        $eventName = trim((string) ($order->event_name ?? ''));

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer->name ?? 'N/A',
            'event_name' => $eventName !== '' ? $eventName : null,
            'fulfillment_type' => $order->fulfillment_type ?? 'pickup',
            'scheduled_at' => $handoffAt?->toIso8601String(),
            'day_line' => $sched['day_line'],
            'time_line' => $sched['time_line'],
            'is_today' => $sched['is_today'],
            'is_tomorrow' => $sched['is_tomorrow'],
            'items_count' => $order->items->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapApiReturnPreview(Order $order): array
    {
        $sched = $this->logisticsDayTime($order->end_at);
        $eventName = trim((string) ($order->event_name ?? ''));

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer->name ?? 'N/A',
            'event_name' => $eventName !== '' ? $eventName : null,
            'fulfillment_type' => $order->fulfillment_type ?? 'pickup',
            'scheduled_at' => $order->end_at?->toIso8601String(),
            'day_line' => $sched['day_line'],
            'time_line' => $sched['time_line'],
            'is_today' => $sched['is_today'],
            'is_tomorrow' => $sched['is_tomorrow'],
            'items_count' => $order->items->count(),
        ];
    }
}
