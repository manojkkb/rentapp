<?php

namespace App\Support;

use App\Models\CustomerReview;
use App\Models\Items;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardAnalytics
{
    public const MONTHS = 12;

    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $now = now();
        $trialDays = VendorSubscription::trialDays();
        $trialCutoff = $now->copy()->subDays($trialDays);

        $totalVendors = Vendor::count();
        $activeVendors = Vendor::where('is_active', true)->count();
        $verifiedVendors = Vendor::where('is_verified', true)->count();
        $newVendorsMonth = Vendor::query()
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count();

        $totalOrders = Order::count();
        $monthlyOrders = Order::query()
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->count();

        $totalGmv = (float) Order::where('status', 'completed')->sum('grand_total');
        $monthlyGmv = (float) Order::query()
            ->where('status', 'completed')
            ->whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->sum('grand_total');

        $totalItems = Items::count();
        $activeItems = Items::where('is_active', true)->count();
        $totalUsers = User::count();

        $subscriptionRevenue = (float) SubscriptionPayment::where('status', 'completed')->sum('amount');
        $monthlySubscriptionRevenue = (float) SubscriptionPayment::query()
            ->where('status', 'completed')
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->sum('amount');

        $activeSubscriptions = Subscription::query()
            ->where('status', 'active')
            ->where('expiry_date', '>', $now)
            ->distinct('vendor_id')
            ->count('vendor_id');

        $trialVendors = Vendor::query()
            ->where('created_at', '>', $trialCutoff)
            ->whereDoesntHave('subscriptions', function ($query) use ($now) {
                $query->where('status', 'active')->where('expiry_date', '>', $now);
            })
            ->count();

        $expiredVendors = max(0, $totalVendors - $activeSubscriptions - $trialVendors);

        $orderStatusCounts = $this->orderStatusCounts();

        $pendingReviews = CustomerReview::where('is_approved', false)->count();

        $recentOrders = Order::query()
            ->with(['vendor:id,name', 'customer:id,name'])
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'vendor_name' => $order->vendor->name ?? '—',
                'customer_name' => $order->customer->name ?? '—',
                'total_amount' => (float) $order->grand_total,
                'status' => $order->status,
                'created_at' => $order->created_at->diffForHumans(),
            ])
            ->values()
            ->all();

        $topVendors = $this->topVendorsByGmv(5);

        $recentVendors = Vendor::query()
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'city', 'is_verified', 'is_active', 'created_at'])
            ->map(fn (Vendor $vendor) => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'city' => $vendor->city,
                'is_verified' => $vendor->is_verified,
                'is_active' => $vendor->is_active,
                'subscription' => VendorSubscription::status($vendor),
                'created_at' => $vendor->created_at->diffForHumans(),
            ])
            ->values()
            ->all();

        return [
            'stats' => [
                'total_vendors' => $totalVendors,
                'active_vendors' => $activeVendors,
                'verified_vendors' => $verifiedVendors,
                'new_vendors_month' => $newVendorsMonth,
                'total_orders' => $totalOrders,
                'monthly_orders' => $monthlyOrders,
                'total_gmv' => $totalGmv,
                'monthly_gmv' => $monthlyGmv,
                'total_items' => $totalItems,
                'active_items' => $activeItems,
                'total_users' => $totalUsers,
                'subscription_revenue' => $subscriptionRevenue,
                'monthly_subscription_revenue' => $monthlySubscriptionRevenue,
                'pending_reviews' => $pendingReviews,
            ],
            'subscription_health' => [
                'active' => $activeSubscriptions,
                'trial' => $trialVendors,
                'expired' => $expiredVendors,
            ],
            'order_status_counts' => $orderStatusCounts,
            'recent_orders' => $recentOrders,
            'top_vendors' => $topVendors,
            'recent_vendors' => $recentVendors,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function revenue(): array
    {
        $monthlyGmv = $this->monthlySeries(fn (Carbon $month) => (float) Order::query()
            ->where('status', 'completed')
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->sum('grand_total'));

        $monthlySubscription = $this->monthlySeries(fn (Carbon $month) => (float) SubscriptionPayment::query()
            ->where('status', 'completed')
            ->whereYear('paid_at', $month->year)
            ->whereMonth('paid_at', $month->month)
            ->sum('amount'));

        $combined = collect($monthlyGmv)->map(function (array $row, int $i) use ($monthlySubscription) {
            $sub = $monthlySubscription[$i]['value'] ?? 0;

            return [
                'label' => $row['label'],
                'rental_gmv' => $row['value'],
                'subscription' => $sub,
                'total' => $row['value'] + $sub,
            ];
        })->values()->all();

        $totalRental = (float) collect($monthlyGmv)->sum('value');
        $totalSubscription = (float) collect($monthlySubscription)->sum('value');
        $currentMonth = end($monthlyGmv) ?: ['value' => 0];
        $prevMonth = $monthlyGmv[count($monthlyGmv) - 2] ?? ['value' => 0];
        $gmvGrowth = $this->percentChange((float) ($prevMonth['value'] ?? 0), (float) ($currentMonth['value'] ?? 0));

        return [
            'summary' => [
                'total_rental_gmv' => (float) Order::where('status', 'completed')->sum('grand_total'),
                'total_subscription' => (float) SubscriptionPayment::where('status', 'completed')->sum('amount'),
                'period_rental_gmv' => $totalRental,
                'period_subscription' => $totalSubscription,
                'gmv_growth_pct' => $gmvGrowth,
            ],
            'monthly_combined' => $combined,
            'monthly_gmv' => $monthlyGmv,
            'monthly_subscription' => $monthlySubscription,
            'top_vendors' => $this->topVendorsByGmv(10),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function bookings(): array
    {
        $monthlyOrders = $this->monthlySeries(fn (Carbon $month) => Order::query()
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count());

        $monthlyCompleted = $this->monthlySeries(fn (Carbon $month) => Order::query()
            ->where('status', 'completed')
            ->whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)
            ->count());

        $statusCounts = $this->orderStatusCounts();

        $fulfillmentRows = Order::query()
            ->select('fulfillment_type', DB::raw('count(*) as total'))
            ->groupBy('fulfillment_type')
            ->pluck('total', 'fulfillment_type')
            ->all();

        $totalOrders = Order::count();
        $completedOrders = (int) ($statusCounts['completed'] ?? 0);
        $avgOrderValue = $completedOrders > 0
            ? (float) Order::where('status', 'completed')->avg('grand_total')
            : 0.0;

        $cancellationRate = $totalOrders > 0
            ? round(((int) ($statusCounts['cancelled'] ?? 0) / $totalOrders) * 100, 1)
            : 0.0;

        return [
            'summary' => [
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'avg_order_value' => $avgOrderValue,
                'cancellation_rate' => $cancellationRate,
                'period_orders' => (int) collect($monthlyOrders)->sum('value'),
            ],
            'order_status_counts' => $statusCounts,
            'fulfillment_breakdown' => $fulfillmentRows,
            'monthly_orders' => $monthlyOrders,
            'monthly_completed' => $monthlyCompleted,
            'recent_orders' => Order::query()
                ->with(['vendor:id,name', 'customer:id,name'])
                ->latest()
                ->take(10)
                ->get()
                ->map(fn (Order $order) => [
                    'order_number' => $order->order_number,
                    'vendor_name' => $order->vendor->name ?? '—',
                    'customer_name' => $order->customer->name ?? '—',
                    'total_amount' => (float) $order->grand_total,
                    'status' => $order->status,
                    'fulfillment_type' => $order->fulfillment_type ?? 'pickup',
                    'created_at' => $order->created_at->format('d M Y'),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function vendorPerformance(): array
    {
        $vendors = Vendor::query()
            ->withCount(['orders', 'items', 'reviews'])
            ->get();

        $gmvByVendor = Order::query()
            ->where('status', 'completed')
            ->select('vendor_id', DB::raw('sum(grand_total) as revenue'))
            ->groupBy('vendor_id')
            ->pluck('revenue', 'vendor_id');

        $ratingsByVendor = CustomerReview::query()
            ->where('is_approved', true)
            ->select('vendor_id', DB::raw('avg(rating) as avg_rating'))
            ->groupBy('vendor_id')
            ->pluck('avg_rating', 'vendor_id');

        $performance = $vendors->map(function (Vendor $vendor) use ($gmvByVendor, $ratingsByVendor) {
            return [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'city' => $vendor->city,
                'is_active' => $vendor->is_active,
                'is_verified' => $vendor->is_verified,
                'subscription' => VendorSubscription::status($vendor),
                'orders_count' => (int) $vendor->orders_count,
                'items_count' => (int) $vendor->items_count,
                'reviews_count' => (int) $vendor->reviews_count,
                'revenue' => (float) ($gmvByVendor[$vendor->id] ?? 0),
                'avg_rating' => round((float) ($ratingsByVendor[$vendor->id] ?? 0), 1),
            ];
        })
            ->sortByDesc('revenue')
            ->values()
            ->all();

        $activeCount = collect($performance)->where('is_active', true)->count();
        $withOrders = collect($performance)->where('orders_count', '>', 0)->count();

        return [
            'summary' => [
                'total_vendors' => $vendors->count(),
                'active_vendors' => $activeCount,
                'vendors_with_orders' => $withOrders,
                'avg_rating' => round((float) CustomerReview::where('is_approved', true)->avg('rating'), 1),
            ],
            'vendors' => $performance,
            'top_by_orders' => collect($performance)->sortByDesc('orders_count')->take(5)->values()->all(),
            'top_by_rating' => collect($performance)->filter(fn ($v) => $v['reviews_count'] > 0)->sortByDesc('avg_rating')->take(5)->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function cities(): array
    {
        $vendorByCity = Vendor::query()
            ->select('city', 'state', DB::raw('count(*) as vendors'))
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city', 'state')
            ->orderByDesc('vendors')
            ->get();

        $ordersByCity = Order::query()
            ->join('vendors', 'orders.vendor_id', '=', 'vendors.id')
            ->select('vendors.city', 'vendors.state', DB::raw('count(orders.id) as orders'), DB::raw('sum(case when orders.status = \'completed\' then orders.grand_total else 0 end) as revenue'))
            ->whereNotNull('vendors.city')
            ->where('vendors.city', '!=', '')
            ->groupBy('vendors.city', 'vendors.state')
            ->get()
            ->keyBy(fn ($row) => $row->city.'|'.$row->state);

        $cities = $vendorByCity->map(function ($row) use ($ordersByCity) {
            $key = $row->city.'|'.$row->state;
            $orderRow = $ordersByCity->get($key);

            return [
                'city' => $row->city,
                'state' => $row->state,
                'vendors' => (int) $row->vendors,
                'orders' => (int) ($orderRow->orders ?? 0),
                'revenue' => (float) ($orderRow->revenue ?? 0),
            ];
        })
            ->sortByDesc('revenue')
            ->values()
            ->all();

        $unknownVendors = Vendor::query()
            ->where(function ($q) {
                $q->whereNull('city')->orWhere('city', '');
            })
            ->count();

        return [
            'summary' => [
                'cities_count' => count($cities),
                'top_city' => $cities[0]['city'] ?? '—',
                'top_city_revenue' => $cities[0]['revenue'] ?? 0,
                'vendors_without_city' => $unknownVendors,
            ],
            'cities' => $cities,
            'by_state' => collect($cities)
                ->groupBy('state')
                ->map(fn (Collection $group, string $state) => [
                    'state' => $state ?: 'Unknown',
                    'vendors' => $group->sum('vendors'),
                    'orders' => $group->sum('orders'),
                    'revenue' => $group->sum('revenue'),
                ])
                ->sortByDesc('revenue')
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function orderStatusCounts(): array
    {
        $rows = Order::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $counts = [];
        foreach (Order::STATUSES as $status) {
            $counts[$status] = (int) ($rows[$status] ?? 0);
        }

        return $counts;
    }

    /**
     * @return list<array{name: string, revenue: float, orders_count: int}>
     */
    public function topVendorsByGmv(int $limit = 5): array
    {
        $rows = Order::query()
            ->where('status', 'completed')
            ->select('vendor_id', DB::raw('sum(grand_total) as revenue'), DB::raw('count(*) as orders_count'))
            ->groupBy('vendor_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        $names = Vendor::query()
            ->whereIn('id', $rows->pluck('vendor_id'))
            ->pluck('name', 'id');

        return $rows->map(fn ($row) => [
            'name' => $names[$row->vendor_id] ?? 'Unknown',
            'revenue' => (float) $row->revenue,
            'orders_count' => (int) $row->orders_count,
        ])->values()->all();
    }

    /**
     * @param  callable(Carbon): (float|int)  $callback
     * @return list<array{label: string, value: float, month_key: string}>
     */
    private function monthlySeries(callable $callback): array
    {
        $series = [];

        for ($i = self::MONTHS - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i)->startOfMonth();
            $value = $callback($month);
            $series[] = [
                'label' => $month->format('M Y'),
                'value' => (float) $value,
                'month_key' => $month->format('Y-m'),
            ];
        }

        return $series;
    }

    private function percentChange(float $previous, float $current): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
