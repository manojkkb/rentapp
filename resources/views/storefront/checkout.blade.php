@extends('storefront.shop-layout')

@php
    $defaultFulfillment = $store->pickup_enabled
        ? 'pickup'
        : ($store->delivery_enabled ? 'delivery' : 'pickup');
@endphp

@section('title', __('vendor.store_nav_checkout').' — '.$vendor->name)

@section('content')
@include('storefront.partials.page-banner', [
    'title' => __('vendor.store_nav_checkout'),
    'subtitle' => __('vendor.store_checkout_note'),
    'backUrl' => route('storefront.cart', $vendor->slug),
    'backLabel' => __('vendor.store_nav_cart'),
])

<div class="store-site-container py-8 sm:py-10" x-data="storefrontCheckout({
    otpUrl: @js(route('storefront.otp.send', $vendor->slug)),
    defaultFulfillment: @js($defaultFulfillment),
    mobileInvalid: @js(__('vendor.store_mobile_invalid')),
    cartSubtotal: @js($cartSubtotal),
    deliveryCharge: @js($deliveryChargePreview ?? 0),
    freeDeliveryMin: @js($freeDeliveryMin),
})">
    @if(isset($errors) && $errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('storefront.checkout.place', $vendor->slug) }}" method="POST" class="grid gap-8 lg:grid-cols-5">
        @csrf

        <div class="space-y-6 lg:col-span-3">
            <section class="{{ $theme['classes']['section'] }} bg-white p-5 sm:p-6">
                <h2 class="mb-5 flex items-center gap-2 text-base font-bold text-gray-900">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full store-accent-bg text-xs font-bold text-white">1</span>
                    {{ __('vendor.store_checkout_contact') }}
                </h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('vendor.name') }}</label>
                        <input type="text" id="name" name="name" required maxlength="255"
                               value="{{ old('name', $customer?->name ?? Auth::user()?->name) }}"
                               class="store-input">
                    </div>
                    <div>
                        <label for="mobile" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('vendor.mobile') }}</label>
                        <div class="flex gap-2">
                            <input type="tel" id="mobile" name="mobile" x-ref="mobile" required pattern="[0-9]{10}" maxlength="10"
                                   value="{{ old('mobile', $customer?->mobile ?? Auth::user()?->mobile) }}"
                                   class="store-input min-w-0 flex-1">
                            <button type="button" @click="sendOtp()" :disabled="otpSending"
                                    class="{{ $theme['classes']['btn'] }} shrink-0 border border-gray-200 bg-gray-50 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100 disabled:opacity-50">
                                <span x-show="!otpSending">{{ __('vendor.store_send_otp') }}</span>
                                <span x-show="otpSending" x-cloak><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></span>
                            </button>
                        </div>
                        <p x-show="otpMessage" x-text="otpMessage" x-cloak class="mt-1.5 text-xs text-emerald-700"></p>
                        <p x-show="otpError" x-text="otpError" x-cloak class="mt-1.5 text-xs text-red-600"></p>
                    </div>
                    <div>
                        <label for="otp" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('vendor.store_otp_code') }}</label>
                        <input type="text" id="otp" name="otp" required pattern="[0-9]{6}" maxlength="6" inputmode="numeric"
                               value="{{ old('otp') }}" class="store-input tracking-widest" placeholder="000000">
                        <p class="mt-1.5 text-xs text-gray-500">{{ __('vendor.store_otp_help') }}</p>
                    </div>
                </div>
            </section>

            <section class="{{ $theme['classes']['section'] }} bg-white p-5 sm:p-6">
                <h2 class="mb-4 text-base font-bold text-gray-900">{{ __('vendor.store_booking_window') }}</h2>
                <p class="text-sm text-gray-700">
                    <i class="fas fa-calendar-alt mr-1 store-accent-text" aria-hidden="true"></i>
                    {{ $booking['start_label'] }} → {{ $booking['end_label'] }}
                </p>
                <button type="button" @click="openBookingModal()" class="mt-3 text-sm font-medium store-accent-text hover:underline">
                    {{ __('vendor.edit') }}
                </button>
            </section>

            <section class="{{ $theme['classes']['section'] }} bg-white p-5 sm:p-6">
                <h2 class="mb-5 flex items-center gap-2 text-base font-bold text-gray-900">
                    <span class="flex h-7 w-7 items-center justify-center rounded-full store-accent-bg text-xs font-bold text-white">2</span>
                    {{ __('vendor.store_checkout_fulfillment') }}
                </h2>
                <div class="mb-5 flex flex-wrap gap-2">
                    @if($store->pickup_enabled)
                        <label class="cursor-pointer">
                            <input type="radio" name="fulfillment_type" value="pickup" class="peer sr-only"
                                   x-model="fulfillmentType" @checked(old('fulfillment_type', $defaultFulfillment) === 'pickup')>
                            <span class="{{ $theme['classes']['chip'] }} inline-flex items-center gap-2 border border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-medium text-gray-700 peer-checked:store-chip-active">
                                <i class="fas fa-store text-xs" aria-hidden="true"></i>
                                {{ __('vendor.pickup') }}
                            </span>
                        </label>
                    @endif
                    @if($store->delivery_enabled)
                        <label class="cursor-pointer">
                            <input type="radio" name="fulfillment_type" value="delivery" class="peer sr-only"
                                   x-model="fulfillmentType" @checked(old('fulfillment_type') === 'delivery')>
                            <span class="{{ $theme['classes']['chip'] }} inline-flex items-center gap-2 border border-gray-200 bg-gray-50 px-5 py-2.5 text-sm font-medium text-gray-700 peer-checked:store-chip-active">
                                <i class="fas fa-truck text-xs" aria-hidden="true"></i>
                                {{ __('vendor.delivery') }}
                            </span>
                        </label>
                    @endif
                </div>

                <div x-show="fulfillmentType === 'pickup'" x-cloak>
                    <label for="pickup_at" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('vendor.pickup_datetime') }}</label>
                    <input type="datetime-local" id="pickup_at" name="pickup_at" min="{{ $minDatetime }}"
                           value="{{ old('pickup_at') }}" class="store-input">
                </div>

                <div x-show="fulfillmentType === 'delivery'" x-cloak class="space-y-4">
                    <div>
                        <label for="delivery_at" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('vendor.delivery_datetime') }}</label>
                        <input type="datetime-local" id="delivery_at" name="delivery_at" min="{{ $minDatetime }}"
                               value="{{ old('delivery_at') }}" class="store-input">
                    </div>
                    <div>
                        <label for="delivery_address" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('vendor.delivery_address') }}</label>
                        <textarea id="delivery_address" name="delivery_address" rows="3" maxlength="5000"
                                  x-bind:required="fulfillmentType === 'delivery'"
                                  class="store-input resize-y"
                                  placeholder="{{ __('vendor.store_delivery_address_placeholder') }}">{{ old('delivery_address', $customer?->address) }}</textarea>
                    </div>
                </div>
            </section>

            <button type="submit"
                    class="{{ $theme['classes']['btn'] }} store-btn-primary flex h-13 w-full items-center justify-center gap-2 py-3.5 text-base font-bold">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                {{ __('vendor.store_place_order') }}
            </button>
        </div>

        <aside class="lg:col-span-2">
            <div class="{{ $theme['classes']['section'] }} sticky top-24 bg-white p-5 sm:p-6">
                <h2 class="text-base font-bold text-gray-900">{{ __('vendor.store_order_summary') }}</h2>
                <ul class="mt-4 max-h-72 space-y-4 overflow-y-auto">
                    @foreach($lines as $line)
                        <li class="flex gap-3 text-sm">
                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                @if($line['photo_url'])
                                    <img src="{{ $line['photo_url'] }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-900">{{ $line['name'] }}</p>
                                <p class="text-xs text-gray-500">× {{ $line['quantity'] }}</p>
                            </div>
                            @if($store->show_prices_online)
                                <span class="shrink-0 font-semibold text-gray-900">₹{{ number_format($line['line_total'], 0) }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @if($store->show_prices_online)
                    <div class="mt-5 space-y-2 border-t border-gray-100 pt-5">
                        <div class="flex items-center justify-between text-sm text-gray-600">
                            <span>{{ __('vendor.subtotal') }}</span>
                            <span>₹{{ number_format($cartSubtotal, 0) }}</span>
                        </div>
                        @if($store->delivery_enabled && ($deliveryChargePreview ?? 0) > 0)
                            <div x-show="fulfillmentType === 'delivery'" x-cloak class="flex items-center justify-between text-sm text-gray-600">
                                <span>{{ __('vendor.delivery_charge') }}</span>
                                <span>₹{{ number_format($deliveryChargePreview, 0) }}</span>
                            </div>
                            <div x-show="fulfillmentType === 'delivery'" x-cloak class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-900">{{ __('vendor.grand_total') }}</span>
                                <span class="text-xl font-bold text-gray-900">₹{{ number_format($cartSubtotal + $deliveryChargePreview, 0) }}</span>
                            </div>
                        @else
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-900">{{ __('vendor.grand_total') }}</span>
                                <span class="text-xl font-bold text-gray-900">₹{{ number_format($cartSubtotal, 0) }}</span>
                            </div>
                        @endif
                        @if($freeDeliveryMin && ($deliveryChargePreview ?? 0) === 0.0 && $cartSubtotal >= (float) $freeDeliveryMin)
                            <p x-show="fulfillmentType === 'delivery'" x-cloak class="text-xs text-emerald-700">
                                {{ __('vendor.store_free_delivery_applied', ['amount' => number_format((float) $freeDeliveryMin, 0)]) }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </aside>
    </form>
</div>
@endsection
