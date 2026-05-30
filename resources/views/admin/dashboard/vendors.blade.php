@extends('admin.layouts.app')

@section('title', 'Vendor Performance - Rentkia Admin')

@php
    $s = $data['summary'];
    $subBadge = fn (string $st) => match ($st) {
        'active' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        'trial' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
        default => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    };
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">Vendor Performance</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Store activity, ratings &amp; rental revenue</p>
    </div>

    @include('admin.dashboard.partials.tabs')

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Total vendors</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">{{ $s['total_vendors'] }}</p>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Active stores</p>
            <p class="mt-1 text-2xl font-black text-green-600">{{ $s['active_vendors'] }}</p>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">With bookings</p>
            <p class="mt-1 text-2xl font-black text-gray-900 dark:text-white">{{ $s['vendors_with_orders'] }}</p>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Platform avg rating</p>
            <p class="mt-1 text-2xl font-black text-amber-600">{{ number_format($s['avg_rating'], 1) }} <i class="fas fa-star text-sm"></i></p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Most orders</h2>
            <ul class="mt-4 space-y-3">
                @foreach($data['top_by_orders'] as $v)
                    <li class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-900/40">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $v['name'] }}</span>
                        <span class="font-bold text-green-600">{{ $v['orders_count'] }} orders</span>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="rounded-2xl border-2 border-gray-100 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">Highest rated</h2>
            <ul class="mt-4 space-y-3">
                @forelse($data['top_by_rating'] as $v)
                    <li class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-900/40">
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $v['name'] }}</span>
                        <span class="font-bold text-amber-600">{{ $v['avg_rating'] }} <i class="fas fa-star text-xs"></i> ({{ $v['reviews_count'] }})</span>
                    </li>
                @empty
                    <li class="text-sm text-gray-500">No approved reviews yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border-2 border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <h2 class="text-lg font-black text-gray-900 dark:text-white">All vendors</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Store</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">City</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Plan</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600">Items</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600">Orders</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600">Revenue</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-600">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($data['vendors'] as $vendor)
                        <tr>
                            <td class="px-5 py-3">
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $vendor['name'] }}</span>
                                @if($vendor['is_verified'])<i class="fas fa-check-circle ml-1 text-green-500 text-xs" title="Verified"></i>@endif
                                @unless($vendor['is_active'])<span class="ml-1 text-xs text-gray-400">(inactive)</span>@endunless
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $vendor['city'] ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-lg px-2 py-1 text-xs font-bold uppercase {{ $subBadge($vendor['subscription']) }}">{{ $vendor['subscription'] }}</span>
                            </td>
                            <td class="px-5 py-3 text-right tabular-nums">{{ $vendor['items_count'] }}</td>
                            <td class="px-5 py-3 text-right tabular-nums">{{ $vendor['orders_count'] }}</td>
                            <td class="px-5 py-3 text-right font-bold text-green-600">₹{{ number_format((int) round($vendor['revenue']), 0) }}</td>
                            <td class="px-5 py-3 text-right">
                                @if($vendor['reviews_count'] > 0)
                                    {{ $vendor['avg_rating'] }} <i class="fas fa-star text-amber-500 text-xs"></i>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500">No vendors yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
