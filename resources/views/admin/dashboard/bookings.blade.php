@extends('admin.layouts.app')

@section('title', 'Booking Analytics - Rentkia Admin')

@php
    $s = $data['summary'];
    $ost = $data['order_status_counts'];
    $statusLabels = [
        'pending' => 'Pending', 'confirmed' => 'Confirmed', 'ongoing' => 'Ongoing',
        'completed' => 'Completed', 'cancelled' => 'Cancelled',
    ];
    $statusChip = fn (string $st) => match ($st) {
        'pending' => 'border-amber-200 bg-amber-50 text-amber-900',
        'confirmed' => 'border-sky-200 bg-sky-50 text-sky-900',
        'ongoing' => 'border-violet-200 bg-violet-50 text-violet-900',
        'completed' => 'border-green-200 bg-green-50 text-green-900',
        'cancelled' => 'border-red-200 bg-red-50 text-red-900',
        default => 'border-gray-200 bg-gray-50 text-gray-800',
    };
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">Booking Analytics</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rental orders, fulfillment &amp; completion trends</p>
    </div>

    @include('admin.dashboard.partials.tabs')

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Total bookings</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">{{ number_format($s['total_orders']) }}</p>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Completed</p>
            <p class="mt-1 text-2xl font-black text-green-600">{{ number_format($s['completed_orders']) }}</p>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Avg order value</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">₹{{ number_format((int) round($s['avg_order_value']), 0) }}</p>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Cancellation rate</p>
            <p class="mt-1 text-2xl font-black text-red-600">{{ $s['cancellation_rate'] }}%</p>
        </div>
    </div>

    <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-lg font-black text-gray-900 dark:text-white">Order status breakdown</h2>
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach (\App\Models\Order::STATUSES as $st)
                <span class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-sm font-semibold {{ $statusChip($st) }}">
                    {{ $statusLabels[$st] ?? $st }}
                    <span class="rounded-lg bg-white/80 px-2 py-0.5 text-xs font-black">{{ $ost[$st] ?? 0 }}</span>
                </span>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Bookings per month</h2>
            <div class="mt-4">
                @include('admin.dashboard.partials.bar-chart', ['series' => $data['monthly_orders']])
            </div>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Completed per month</h2>
            <div class="mt-4">
                @include('admin.dashboard.partials.bar-chart', ['series' => $data['monthly_completed']])
            </div>
        </div>
    </div>

    @if(!empty($data['fulfillment_breakdown']))
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Fulfillment type</h2>
            <div class="mt-4 flex flex-wrap gap-4">
                @foreach($data['fulfillment_breakdown'] as $type => $count)
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-600 dark:bg-gray-900/40">
                        <p class="text-xs font-bold uppercase text-gray-500">{{ ucfirst($type ?: 'unknown') }}</p>
                        <p class="text-xl font-black text-gray-900 dark:text-white">{{ $count }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Recent bookings</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Order</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Vendor</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Customer</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Type</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600">Amount</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($data['recent_orders'] as $order)
                        <tr>
                            <td class="px-5 py-3 font-semibold">{{ $order['order_number'] }}</td>
                            <td class="px-5 py-3">{{ $order['vendor_name'] }}</td>
                            <td class="px-5 py-3">{{ $order['customer_name'] }}</td>
                            <td class="px-5 py-3 capitalize">{{ $order['fulfillment_type'] }}</td>
                            <td class="px-5 py-3"><span class="rounded-lg border px-2 py-0.5 text-xs font-bold {{ $statusChip($order['status']) }}">{{ $statusLabels[$order['status']] ?? $order['status'] }}</span></td>
                            <td class="px-5 py-3 text-right font-bold">₹{{ number_format((int) round($order['total_amount']), 0) }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $order['created_at'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500">No bookings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
