@extends('storefront.shop-layout')

@section('title', __('vendor.store_nav_orders').' — '.$vendor->name)

@section('content')
@include('storefront.partials.page-banner', [
    'title' => __('vendor.store_nav_orders'),
    'subtitle' => $customer ? $customer->name.' · '.$customer->mobile : null,
])

<div class="store-site-container py-8 sm:py-10">
    @if($orders->isEmpty())
        <div class="{{ $theme['classes']['section'] }} mx-auto max-w-lg bg-white px-6 py-20 text-center">
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full store-accent-bg-soft">
                <i class="fas fa-receipt text-2xl store-accent-text" aria-hidden="true"></i>
            </div>
            <h2 class="text-lg font-bold text-gray-900">{{ __('vendor.store_no_orders') }}</h2>
            <a href="{{ route('storefront.show', $vendor->slug) }}"
               class="{{ $theme['classes']['btn'] }} store-btn-primary mt-6 inline-flex h-11 items-center px-6 text-sm font-semibold">
                {{ __('vendor.store_nav_shop') }}
            </a>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach($orders as $order)
                @php
                    $statusLabel = __('vendor.'.$order->status) !== 'vendor.'.$order->status
                        ? __('vendor.'.$order->status)
                        : ucfirst($order->status);
                @endphp
                <a href="{{ route('storefront.orders.show', [$vendor->slug, $order->uuid]) }}"
                   class="{{ $theme['classes']['section'] }} group block bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-bold text-gray-900 group-hover:store-accent-text-dark">{{ $order->order_number }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $order->start_at?->format('d M Y') }} — {{ $order->end_at?->format('d M Y') }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold
                            @if($order->status === 'pending') bg-amber-100 text-amber-800
                            @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                            @elseif($order->status === 'completed') bg-emerald-100 text-emerald-800
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4 text-sm">
                        <span class="text-gray-600">{{ $order->items->count() }} {{ __('vendor.items') }}</span>
                        @if($store->show_prices_online)
                            <span class="text-lg font-bold text-gray-900">₹{{ number_format((float) $order->grand_total, 0) }}</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
