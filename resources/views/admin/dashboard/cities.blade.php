@extends('admin.layouts.app')

@section('title', 'City Analytics - Rentkia Admin')

@php
    $s = $data['summary'];
    $cities = $data['cities'];
    $maxRevenue = max(1, (float) collect($cities)->max('revenue'));
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">City Analytics</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Vendors, bookings &amp; revenue by location</p>
    </div>

    @include('admin.dashboard.partials.tabs')

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Cities covered</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">{{ $s['cities_count'] }}</p>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Top city</p>
            <p class="mt-1 text-xl font-black text-gray-900 dark:text-white">{{ $s['top_city'] }}</p>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Top city revenue</p>
            <p class="mt-1 text-2xl font-black text-green-600">₹{{ number_format((int) round($s['top_city_revenue']), 0) }}</p>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">No city set</p>
            <p class="mt-1 text-2xl font-black text-amber-600">{{ $s['vendors_without_city'] }}</p>
            <p class="text-xs text-gray-500">vendors missing location</p>
        </div>
    </div>

    <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-lg font-black text-gray-900 dark:text-white">Revenue by city</h2>
        <div class="mt-4 space-y-3">
            @foreach($cities as $city)
                @php $pct = min(100, round(($city['revenue'] / $maxRevenue) * 100)); @endphp
                <div>
                    <div class="mb-1 flex flex-wrap items-center justify-between gap-2 text-sm">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $city['city'] }}@if($city['state']), {{ $city['state'] }}@endif</span>
                        <span class="font-bold text-green-600">₹{{ number_format((int) round($city['revenue']), 0) }}</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                        <div class="h-full rounded-full bg-green-gradient" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="mt-0.5 text-xs text-gray-500">{{ $city['vendors'] }} vendors · {{ $city['orders'] }} orders</p>
                </div>
            @endforeach
            @if(empty($cities))
                <p class="text-sm text-gray-500">No city data available. Add city to vendor profiles.</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                <h2 class="text-lg font-black text-gray-900 dark:text-white">City breakdown</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600">City</th>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600">State</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600">Vendors</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600">Orders</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($cities as $city)
                            <tr>
                                <td class="px-5 py-3 font-semibold text-gray-900 dark:text-white">{{ $city['city'] }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $city['state'] ?? '—' }}</td>
                                <td class="px-5 py-3 text-right tabular-nums">{{ $city['vendors'] }}</td>
                                <td class="px-5 py-3 text-right tabular-nums">{{ $city['orders'] }}</td>
                                <td class="px-5 py-3 text-right font-bold text-green-600">₹{{ number_format((int) round($city['revenue']), 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                <h2 class="text-lg font-black text-gray-900 dark:text-white">By state</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-gray-600">State</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600">Vendors</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600">Orders</th>
                            <th class="px-5 py-3 text-right font-semibold text-gray-600">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($data['by_state'] as $state)
                            <tr>
                                <td class="px-5 py-3 font-semibold text-gray-900 dark:text-white">{{ $state['state'] }}</td>
                                <td class="px-5 py-3 text-right tabular-nums">{{ $state['vendors'] }}</td>
                                <td class="px-5 py-3 text-right tabular-nums">{{ $state['orders'] }}</td>
                                <td class="px-5 py-3 text-right font-bold text-green-600">₹{{ number_format((int) round($state['revenue']), 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No state data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
