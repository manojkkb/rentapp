@extends('storefront.shop-layout')

@section('title', __('vendor.store_nav_cart').' — '.$vendor->name)

@section('content')
@include('storefront.partials.page-banner', [
    'title' => __('vendor.store_nav_cart'),
    'subtitle' => $lines->count().' '.__('vendor.items'),
])

<div class="store-site-container py-8 sm:py-10">
    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    @if($lines->isEmpty())
        <div class="{{ $theme['classes']['section'] }} mx-auto max-w-lg bg-white px-6 py-20 text-center">
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full store-accent-bg-soft">
                <i class="fas fa-shopping-cart text-2xl store-accent-text" aria-hidden="true"></i>
            </div>
            <h2 class="text-lg font-bold text-gray-900">{{ __('vendor.store_cart_empty') }}</h2>
            <p class="mt-2 text-sm text-gray-500">{{ __('vendor.store_continue_shopping') }}</p>
            <a href="{{ route('storefront.show', $vendor->slug) }}"
               class="{{ $theme['classes']['btn'] }} store-btn-primary mt-6 inline-flex h-11 items-center px-6 text-sm font-semibold">
                {{ __('vendor.store_nav_shop') }}
            </a>
        </div>
    @else
        <div class="grid gap-8 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-2">
                @foreach($lines as $line)
                    <article class="{{ $theme['classes']['section'] }} flex flex-wrap items-center gap-4 bg-white p-4 sm:flex-nowrap sm:p-5">
                        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-gray-100">
                            @if($line['photo_url'])
                                <img src="{{ $line['photo_url'] }}" alt="" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center store-accent-bg-soft">
                                    <i class="fas fa-image text-gray-300" aria-hidden="true"></i>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-base font-semibold text-gray-900">{{ $line['name'] }}</h2>
                            @if($line['category_name'])
                                <p class="text-xs text-gray-500">{{ $line['category_name'] }}</p>
                            @endif
                        @if($store->show_prices_online)
                            <p class="mt-2 text-sm font-bold store-accent-text-dark">
                                ₹{{ number_format($line['unit_price'], 0) }}
                                <span class="font-normal text-gray-500">× {{ $line['quantity'] }}</span>
                                @if($line['uses_billing_units'] && $line['billing_units'])
                                    <span class="font-normal text-gray-500">× {{ rtrim(rtrim(number_format($line['billing_units'], 2), '0'), '.') }} {{ $line['rental_period_label'] }}</span>
                                @endif
                            </p>
                            <p class="text-xs font-semibold text-gray-900">= ₹{{ number_format($line['line_total'], 0) }}</p>
                        @endif
                        </div>
                        <div class="flex w-full items-center gap-2 sm:w-auto">
                            <form action="{{ route('storefront.cart.update', [$vendor->slug, $line['key']]) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <label class="sr-only" for="qty-{{ $line['key'] }}">{{ __('vendor.quantity') }}</label>
                                <input id="qty-{{ $line['key'] }}" type="number" name="quantity" min="1" max="99"
                                       value="{{ $line['quantity'] }}"
                                       class="store-input h-11 w-16 text-center"
                                       onchange="this.form.submit()">
                            </form>
                            <form action="{{ route('storefront.cart.remove', [$vendor->slug, $line['key']]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex h-11 w-11 items-center justify-center rounded-lg text-red-600 transition hover:bg-red-50" title="{{ __('vendor.remove') }}">
                                    <i class="fas fa-trash-alt" aria-hidden="true"></i>
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="lg:col-span-1">
                <div class="{{ $theme['classes']['section'] }} sticky top-24 bg-white p-5 sm:p-6">
                    <h2 class="text-base font-bold text-gray-900">{{ __('vendor.store_order_summary') }}</h2>
                    @if($store->show_prices_online)
                        <div class="mt-4 flex items-center justify-between border-b border-gray-100 pb-4">
                            <span class="text-sm text-gray-600">{{ __('vendor.subtotal') }}</span>
                            <span class="text-xl font-bold text-gray-900">₹{{ number_format($cartSubtotal, 0) }}</span>
                        </div>
                    @endif
                    <a href="{{ route('storefront.checkout', $vendor->slug) }}"
                       class="{{ $theme['classes']['btn'] }} store-btn-primary mt-5 flex h-12 w-full items-center justify-center gap-2 text-sm font-bold">
                        {{ __('vendor.store_proceed_checkout') }}
                        <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                    </a>
                    <a href="{{ route('storefront.show', $vendor->slug) }}"
                       class="mt-3 block text-center text-sm font-medium store-accent-text hover:underline">
                        {{ __('vendor.store_continue_shopping') }}
                    </a>
                </div>
            </aside>
        </div>
    @endif
</div>
@endsection
