@extends('admin.layouts.app')

@section('title', 'Dashboard - Rentkia Admin')

@php
    $s = $dashboard['stats'];
    $sub = $dashboard['subscription_health'];
    $ost = $dashboard['order_status_counts'];
    $recentOrders = $dashboard['recent_orders'];
    $topVendors = $dashboard['top_vendors'];
    $recentVendors = $dashboard['recent_vendors'];
    $adminName = auth()->guard('admin')->user()->name ?? 'Admin';

    $statusLabels = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    $statusChip = fn (string $st) => match ($st) {
        'pending' => 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-200',
        'confirmed' => 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-800 dark:bg-sky-900/30 dark:text-sky-200',
        'completed' => 'border-green-200 bg-green-50 text-green-900 dark:border-green-800 dark:bg-green-900/30 dark:text-green-200',
        'cancelled' => 'border-red-200 bg-red-50 text-red-900 dark:border-red-800 dark:bg-red-900/30 dark:text-red-200',
        default => 'border-gray-200 bg-gray-50 text-gray-800 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200',
    };

    $subBadge = fn (string $st) => match ($st) {
        'active' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        'trial' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
        default => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    };
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    {{-- Welcome --}}
    <div class="overflow-hidden rounded-2xl bg-green-gradient px-5 py-5 text-white shadow-lg sm:px-6 sm:py-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black sm:text-3xl">Welcome back, {{ $adminName }}</h1>
                <p class="mt-1 text-sm text-green-50/90">Platform overview for your rental marketplace</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.subscriptions.plans.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-green-700 shadow-sm transition hover:bg-green-50">
                    <i class="fas fa-layer-group"></i>
                    Subscription Plans
                </a>
                <a href="{{ route('admin.subscriptions.plans.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-green-900/30 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/30 transition hover:bg-green-900/40">
                    <i class="fas fa-plus"></i>
                    Add Plan
                </a>
            </div>
        </div>
    </div>

    @include('admin.dashboard.partials.tabs')

    {{-- Primary KPIs --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-400">
                <i class="fas fa-store text-lg"></i>
            </div>
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Vendors</p>
            <p class="mt-1 text-3xl font-black text-gray-900 dark:text-white">{{ number_format($s['total_vendors']) }}</p>
            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ $s['active_vendors'] }} active · {{ $s['new_vendors_month'] }} new this month
            </p>
        </div>

        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-400">
                <i class="fas fa-receipt text-lg"></i>
            </div>
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Rental Orders</p>
            <p class="mt-1 text-3xl font-black text-gray-900 dark:text-white">{{ number_format($s['total_orders']) }}</p>
            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $s['monthly_orders'] }} this month</p>
        </div>

        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400">
                <i class="fas fa-indian-rupee-sign text-lg"></i>
            </div>
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Rental GMV</p>
            <p class="mt-1 text-3xl font-black text-gray-900 dark:text-white">₹{{ number_format((int) round($s['total_gmv']), 0) }}</p>
            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">₹{{ number_format((int) round($s['monthly_gmv']), 0) }} this month</p>
        </div>

        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400">
                <i class="fas fa-box text-lg"></i>
            </div>
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Listings</p>
            <p class="mt-1 text-3xl font-black text-gray-900 dark:text-white">{{ number_format($s['total_items']) }}</p>
            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $s['active_items'] }} active listings</p>
        </div>

        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400">
                <i class="fas fa-users text-lg"></i>
            </div>
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Platform Users</p>
            <p class="mt-1 text-3xl font-black text-gray-900 dark:text-white">{{ number_format($s['total_users']) }}</p>
            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $s['verified_vendors'] }} verified vendors</p>
        </div>

        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">
                <i class="fas fa-credit-card text-lg"></i>
            </div>
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subscription Revenue</p>
            <p class="mt-1 text-3xl font-black text-gray-900 dark:text-white">₹{{ number_format((int) round($s['subscription_revenue']), 0) }}</p>
            <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">₹{{ number_format((int) round($s['monthly_subscription_revenue']), 0) }} this month</p>
        </div>
    </div>

    {{-- Order pipeline + subscription health --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800 lg:col-span-2">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Order Pipeline</h2>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">All rental orders across vendors</p>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach (\App\Models\Order::STATUSES as $st)
                    @php $n = (int) ($ost[$st] ?? 0); @endphp
                    <span class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-sm font-semibold {{ $statusChip($st) }}">
                        {{ $statusLabels[$st] ?? ucfirst($st) }}
                        <span class="rounded-lg bg-white/70 px-2 py-0.5 text-xs font-black tabular-nums dark:bg-black/20">{{ $n }}</span>
                    </span>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Vendor Subscriptions</h2>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Trial &amp; paid plan status</p>
            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-xl bg-green-50 px-4 py-3 dark:bg-green-900/20">
                    <span class="text-sm font-semibold text-green-800 dark:text-green-300">Active plans</span>
                    <span class="text-xl font-black text-green-700 dark:text-green-400">{{ $sub['active'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-amber-50 px-4 py-3 dark:bg-amber-900/20">
                    <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">Free trial</span>
                    <span class="text-xl font-black text-amber-700 dark:text-amber-400">{{ $sub['trial'] }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-red-50 px-4 py-3 dark:bg-red-900/20">
                    <span class="text-sm font-semibold text-red-800 dark:text-red-300">Expired / unpaid</span>
                    <span class="text-xl font-black text-red-700 dark:text-red-400">{{ $sub['expired'] }}</span>
                </div>
            </div>
            @if($s['pending_reviews'] > 0)
                <p class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                    <i class="fas fa-star mr-1"></i>{{ $s['pending_reviews'] }} reviews awaiting approval
                </p>
            @endif
        </div>
    </div>

    {{-- Recent orders + top vendors --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                <h2 class="text-lg font-black text-gray-900 dark:text-white">Recent Orders</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Latest rental bookings platform-wide</p>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($recentOrders as $order)
                    <div class="flex items-center gap-3 px-5 py-3.5">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-gray-900 dark:text-white">
                                {{ $order['order_number'] ?? '#'.$order['id'] }}
                            </p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                {{ $order['vendor_name'] }} · {{ $order['customer_name'] }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">₹{{ number_format((int) round($order['total_amount']), 0) }}</p>
                            <span class="inline-block rounded-lg border px-2 py-0.5 text-[10px] font-bold uppercase {{ $statusChip($order['status']) }}">
                                {{ $statusLabels[$order['status']] ?? $order['status'] }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No orders yet.</p>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                <h2 class="text-lg font-black text-gray-900 dark:text-white">Top Vendors by GMV</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Completed rental revenue</p>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($topVendors as $index => $vendor)
                    <div class="flex items-center gap-3 px-5 py-3.5">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-green-gradient text-sm font-black text-white">
                            {{ $index + 1 }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ $vendor['name'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $vendor['orders_count'] }} completed orders</p>
                        </div>
                        <p class="text-sm font-black text-green-600 dark:text-green-400">₹{{ number_format((int) round($vendor['revenue']), 0) }}</p>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No completed orders yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent vendors --}}
    <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Recently Joined Vendors</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">New stores on the platform</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Store</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">City</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Subscription</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentVendors as $vendor)
                        <tr>
                            <td class="px-5 py-3 font-semibold text-gray-900 dark:text-white">{{ $vendor['name'] }}</td>
                            <td class="px-5 py-3 text-gray-600 dark:text-gray-400">{{ $vendor['city'] ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-lg px-2 py-1 text-xs font-bold uppercase {{ $subBadge($vendor['subscription']) }}">
                                    {{ $vendor['subscription'] }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                @if($vendor['is_active'])
                                    <span class="text-xs font-semibold text-green-600 dark:text-green-400">Active</span>
                                @else
                                    <span class="text-xs font-semibold text-gray-500">Inactive</span>
                                @endif
                                @if($vendor['is_verified'])
                                    <span class="ml-1 text-xs text-green-600 dark:text-green-400"><i class="fas fa-check-circle"></i></span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-500 dark:text-gray-400">{{ $vendor['created_at'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No vendors yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
