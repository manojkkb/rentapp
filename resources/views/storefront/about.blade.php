@extends('storefront.shop-layout')

@section('title', __('vendor.store_about_us').' — '.$vendor->name)

@section('content')
@include('storefront.partials.page-banner', [
    'title' => __('vendor.store_about_us'),
    'subtitle' => $store->tagline ?: $vendor->name,
    'backUrl' => route('storefront.show', $vendor->slug),
    'backLabel' => __('vendor.store_back_to_shop'),
])

<div class="store-site-container py-8 sm:py-10">
    <article class="{{ $theme['classes']['section'] }} overflow-hidden bg-white">
        <div class="grid gap-0 md:grid-cols-[minmax(0,1fr)_18rem]">
            <div class="p-5 sm:p-8">
                <div class="prose prose-sm max-w-none text-gray-700 sm:prose-base">
                    {!! \App\Support\StorefrontRichText::render($aboutContent) !!}
                </div>
            </div>
            <aside class="store-accent-bg-soft border-t border-gray-100 p-5 sm:p-6 md:border-l md:border-t-0">
                @if($vendor->logo_url)
                    <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->name }}" class="mb-4 h-16 w-16 rounded-xl object-contain">
                @endif
                <p class="font-bold text-gray-900">{{ $vendor->name }}</p>
                @if($vendor->businessCategory)
                    <p class="mt-1 text-sm text-gray-600">{{ $vendor->businessCategory->name }}</p>
                @endif
                @if($vendor->full_address)
                    <p class="mt-4 flex items-start gap-2 text-sm text-gray-700">
                        <i class="fas fa-map-marker-alt mt-0.5 shrink-0 store-accent-text" aria-hidden="true"></i>
                        <span>{{ $vendor->full_address }}</span>
                    </p>
                @endif
                @if($store->pickup_enabled || $store->delivery_enabled)
                    <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.store_checkout_fulfillment') }}</p>
                    <p class="mt-1 text-sm text-gray-700">
                        @if($store->pickup_enabled){{ __('vendor.pickup') }}@endif
                        @if($store->pickup_enabled && $store->delivery_enabled) · @endif
                        @if($store->delivery_enabled){{ __('vendor.delivery') }}@endif
                    </p>
                @endif
            </aside>
        </div>
    </article>
</div>
@endsection
