@extends('admin.layouts.app')

@section('title', 'Revenue Analytics - Rentkia Admin')

@php
    $s = $data['summary'];
    $combined = $data['monthly_combined'];
    $maxTotal = max(1, (float) collect($combined)->max('total'));
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">Revenue Analytics</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rental GMV and subscription revenue — last 12 months</p>
    </div>

    @include('admin.dashboard.partials.tabs')

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Total rental GMV</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">₹{{ number_format((int) round($s['total_rental_gmv']), 0) }}</p>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Subscription revenue</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">₹{{ number_format((int) round($s['total_subscription']), 0) }}</p>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">GMV (12 months)</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">₹{{ number_format((int) round($s['period_rental_gmv']), 0) }}</p>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">MoM GMV growth</p>
            <p class="mt-1 text-2xl font-black {{ $s['gmv_growth_pct'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $s['gmv_growth_pct'] >= 0 ? '+' : '' }}{{ $s['gmv_growth_pct'] }}%
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Monthly rental GMV</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Completed orders only</p>
            <div class="mt-4">
                @include('admin.dashboard.partials.bar-chart', [
                    'series' => $data['monthly_gmv'],
                    'prefix' => '₹',
                ])
            </div>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Monthly subscription revenue</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Successful plan payments</p>
            <div class="mt-4">
                @include('admin.dashboard.partials.bar-chart', [
                    'series' => $data['monthly_subscription'],
                    'prefix' => '₹',
                ])
            </div>
        </div>
    </div>

    <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-lg font-black text-gray-900 dark:text-white">Combined revenue trend</h2>
        <div class="mt-4 space-y-3">
            @foreach ($combined as $row)
                @php $pct = min(100, round(($row['total'] / $maxTotal) * 100)); @endphp
                <div>
                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2 text-sm">
                        <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $row['label'] }}</span>
                        <span class="font-bold tabular-nums text-gray-900 dark:text-white">₹{{ number_format((int) round($row['total']), 0) }}</span>
                    </div>
                    <div class="h-4 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                        <div class="flex h-full">
                            <div class="bg-emerald-500" style="width: {{ $row['total'] > 0 ? ($row['rental_gmv'] / $row['total']) * $pct : 0 }}%" title="Rental"></div>
                            <div class="bg-amber-400" style="width: {{ $row['total'] > 0 ? ($row['subscription'] / $row['total']) * $pct : 0 }}%" title="Subscription"></div>
                        </div>
                    </div>
                    <p class="mt-0.5 text-xs text-gray-500">Rental ₹{{ number_format((int) $row['rental_gmv'], 0) }} · Sub ₹{{ number_format((int) $row['subscription'], 0) }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Top vendors by GMV</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/40">
                <tr>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">#</th>
                    <th class="px-5 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Vendor</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Orders</th>
                    <th class="px-5 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($data['top_vendors'] as $i => $vendor)
                    <tr>
                        <td class="px-5 py-3 text-gray-500">{{ $i + 1 }}</td>
                        <td class="px-5 py-3 font-semibold text-gray-900 dark:text-white">{{ $vendor['name'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $vendor['orders_count'] }}</td>
                        <td class="px-5 py-3 text-right font-bold text-green-600">₹{{ number_format((int) round($vendor['revenue']), 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No revenue data yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
