@extends('storefront.shop-layout')

@section('title', $order->order_number.' — '.$vendor->name)

@section('content')
@php
    $statusLabel = __('vendor.'.$order->status) !== 'vendor.'.$order->status
        ? __('vendor.'.$order->status)
        : ucfirst($order->status);
@endphp

@include('storefront.partials.page-banner', [
    'title' => __('vendor.store_order_confirmed'),
    'subtitle' => $order->order_number,
    'backUrl' => route('storefront.orders', $vendor->slug),
    'backLabel' => __('vendor.store_nav_orders'),
])

<div class="store-site-container py-8 sm:py-10">
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            <i class="fas fa-check-circle mr-1" aria-hidden="true"></i>{{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="{{ $theme['classes']['section'] }} bg-white p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span class="rounded-full px-3 py-1 text-xs font-bold
                        @if($order->status === 'pending') bg-amber-100 text-amber-800
                        @elseif($order->status === 'confirmed') bg-blue-100 text-blue-800
                        @elseif($order->status === 'completed') bg-emerald-100 text-emerald-800
                        @else bg-gray-100 text-gray-700 @endif">
                        {{ $statusLabel }}
                    </span>
                    <p class="text-sm text-gray-500">{{ $order->created_at?->format('d M Y, h:i A') }}</p>
                </div>

                <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('vendor.start_date') }}</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $order->start_at?->format('d M Y, h:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('vendor.end_date') }}</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $order->end_at?->format('d M Y, h:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('vendor.fulfillment_method') }}</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            {{ $order->fulfillment_type === 'delivery' ? __('vendor.delivery') : __('vendor.pickup') }}
                        </dd>
                    </div>
                    @if($order->fulfillment_type === 'delivery' && $order->delivery_address)
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-gray-400">{{ __('vendor.delivery_address') }}</dt>
                            <dd class="mt-1 font-medium text-gray-900 whitespace-pre-line">{{ $order->delivery_address }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="{{ $theme['classes']['section'] }} bg-white p-5 sm:p-6">
                <h2 class="mb-4 text-base font-bold text-gray-900">{{ __('vendor.items') }}</h2>
                <ul class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                        <li class="flex items-center justify-between gap-4 py-4 text-sm">
                            <div>
                                <p class="font-medium text-gray-900">{{ $item->item_name }}</p>
                                <p class="text-xs text-gray-500">× {{ $item->quantity }}</p>
                            </div>
                            @if($store->show_prices_online)
                                <span class="font-semibold text-gray-900">₹{{ number_format((float) $item->total_price, 0) }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <aside>
            @if($store->show_prices_online)
                <div class="{{ $theme['classes']['section'] }} sticky top-24 bg-white p-5 sm:p-6">
                    <h2 class="text-base font-bold text-gray-900">{{ __('vendor.store_order_summary') }}</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>{{ __('vendor.subtotal') }}</span>
                            <span>₹{{ number_format((float) $order->sub_total, 0) }}</span>
                        </div>
                        @if((float) $order->delivery_charge > 0)
                            <div class="flex justify-between text-gray-600">
                                <span>{{ __('vendor.delivery_charge') }}</span>
                                <span>₹{{ number_format((float) $order->delivery_charge, 0) }}</span>
                            </div>
                        @endif
                        @if((float) $order->security_deposit > 0)
                            <div class="flex justify-between text-gray-600">
                                <span>{{ __('vendor.security_deposit') }}</span>
                                <span>₹{{ number_format((float) $order->security_deposit, 0) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-gray-100 pt-4 text-lg font-bold text-gray-900">
                            <span>{{ __('vendor.grand_total') }}</span>
                            <span>₹{{ number_format((float) $order->grand_total, 0) }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-4 flex flex-col gap-3">
                <a href="{{ route('storefront.show', $vendor->slug) }}"
                   class="{{ $theme['classes']['btn'] }} store-btn-primary flex h-11 items-center justify-center text-sm font-semibold">
                    {{ __('vendor.store_continue_shopping') }}
                </a>
                @if($store->business_phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $store->business_phone) }}"
                       class="{{ $theme['classes']['btn'] }} flex h-11 items-center justify-center border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-phone mr-2 text-xs" aria-hidden="true"></i>{{ __('vendor.call') }}
                    </a>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection
