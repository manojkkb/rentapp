@extends('vendor.layouts.app')

@section('title', __('vendor.customer_details'))
@section('page-title', __('vendor.customer_details'))

@section('content')
@php
    $card = 'overflow-hidden rounded-xl border border-gray-200/90 bg-white';
    $body = 'p-3 sm:p-4';
    $initial = strtoupper(substr($customer->name, 0, 1));
@endphp

<div class="mx-auto w-full max-w-4xl space-y-3 sm:space-y-4">
    <header class="flex flex-wrap items-start justify-between gap-2">
        <div class="min-w-0">
            <a href="{{ route('vendor.customers.index') }}"
               wire:navigate
               class="mb-1.5 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-600">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                {{ __('vendor.back_to_customers') }}
            </a>
            <h1 class="truncate text-lg font-bold text-gray-900 sm:text-xl">{{ $customer->name }}</h1>
            <p class="mt-0.5 flex items-center gap-1.5 text-sm text-gray-600">
                <i class="fas fa-phone text-[10px] text-emerald-600" aria-hidden="true"></i>
                <span class="font-medium tabular-nums">{{ $customer->mobile }}</span>
            </p>
        </div>
        <a href="{{ route('vendor.customers.edit', $customer) }}"
           wire:navigate
           class="inline-flex min-h-[40px] shrink-0 items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            <i class="fas fa-edit text-xs" aria-hidden="true"></i>
            {{ __('vendor.edit_customer') }}
        </a>
    </header>

    @if(session('success'))
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-900">
            <i class="fas fa-check-circle mt-0.5 text-emerald-600" aria-hidden="true"></i>
            <p class="flex-1">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Profile --}}
    <section class="{{ $card }}">
        <div class="flex items-start gap-3 p-3 sm:items-center sm:gap-4 sm:p-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-xl font-bold text-white ring-2 ring-emerald-100">
                {{ $initial }}
            </div>
            <div class="min-w-0 flex-1 space-y-2">
                <div class="flex flex-wrap gap-1.5">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $customer->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-gray-50 text-gray-600 ring-gray-100' }}">
                        {{ $customer->is_active ? __('vendor.active') : __('vendor.inactive') }}
                    </span>
                    @if($customer->user_id)
                        <span class="inline-flex items-center rounded-full bg-teal-50 px-2 py-0.5 text-[11px] font-semibold text-teal-700 ring-1 ring-teal-100">
                            <i class="fas fa-check-circle mr-1 text-[9px]" aria-hidden="true"></i>{{ __('vendor.registered') }}
                        </span>
                    @endif
                </div>
                @if($customer->address)
                    <p class="text-sm leading-snug text-gray-700">
                        <i class="fas fa-location-dot mr-1.5 text-xs text-gray-400" aria-hidden="true"></i>{{ $customer->address }}
                    </p>
                @endif
                <p class="text-xs text-gray-500">
                    {{ __('vendor.customer_member_since') }} {{ $customer->created_at->format('M j, Y') }}
                    <span class="mx-1 text-gray-300">·</span>
                    <span class="font-mono text-[11px]">{{ $customer->uuid }}</span>
                </p>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="{{ $card }}">
        <div class="{{ $body }}">
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3">
                <div class="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.total_orders') }}</p>
                    <p class="mt-0.5 text-lg font-bold tabular-nums text-gray-900">{{ $orderStats['total'] }}</p>
                </div>
                <div class="rounded-lg border border-amber-100 bg-amber-50/50 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-800/80">{{ __('vendor.customer_active_orders') }}</p>
                    <p class="mt-0.5 text-lg font-bold tabular-nums text-amber-900">{{ $orderStats['active'] }}</p>
                </div>
                <div class="rounded-lg border border-emerald-100 bg-emerald-50/50 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-800/80">{{ __('vendor.customer_completed_orders') }}</p>
                    <p class="mt-0.5 text-lg font-bold tabular-nums text-emerald-900">{{ $orderStats['completed'] }}</p>
                </div>
                <div class="rounded-lg border border-teal-100 bg-teal-50/50 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-teal-800/80">{{ __('vendor.customer_total_paid') }}</p>
                    <p class="mt-0.5 text-lg font-bold tabular-nums text-teal-900">₹{{ number_format($orderStats['total_paid'], 2) }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Recent orders --}}
    <section class="{{ $card }}">
        <div class="border-b border-gray-100 bg-gradient-to-r from-slate-50 to-emerald-50/20 px-3 py-2.5 sm:px-4 sm:py-3">
            <h2 class="text-sm font-bold text-gray-900 sm:text-base">{{ __('vendor.customer_recent_orders') }}</h2>
        </div>
        <div class="{{ $body }}">
            @if($recentOrders->isEmpty())
                <p class="rounded-lg border border-dashed border-gray-200 bg-gray-50/80 px-4 py-8 text-center text-sm text-gray-500">
                    {{ __('vendor.customer_no_orders_yet') }}
                </p>
            @else
                <div class="space-y-2">
                    @foreach($recentOrders as $order)
                        @php $pill = $statusMeta[$order->status] ?? 'bg-gray-100 text-gray-800 ring-1 ring-gray-200/70'; @endphp
                        <a href="{{ route('vendor.orders.show', $order) }}"
                           wire:navigate
                           class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 bg-white px-3 py-2.5 transition hover:border-emerald-200 hover:bg-emerald-50/30">
                            <div class="min-w-0">
                                <p class="truncate font-mono text-sm font-semibold text-emerald-700">{{ $order->order_number }}</p>
                                @if($order->event_name)
                                    <p class="truncate text-xs text-gray-600">{{ $order->event_name }}</p>
                                @endif
                                <p class="mt-0.5 text-[11px] text-gray-500">{{ $order->created_at->format('M j, Y') }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $pill }}">{{ ucfirst($order->status) }}</span>
                                <p class="mt-1 text-sm font-bold tabular-nums text-gray-900">₹{{ number_format($order->grand_total, 2) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
