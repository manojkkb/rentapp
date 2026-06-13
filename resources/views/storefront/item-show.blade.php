@extends('storefront.shop-layout')

@section('title', $item->name.' — '.$vendor->name)

@section('content')
@php
    $showPrice = $store->show_prices_online;
    $periodLabel = $item->rental_period
        ? (\App\Models\Items::rentalPeriodSelectOptions()[$item->rental_period] ?? $item->rental_period)
        : '';
    $usesBilling = \App\Models\Items::rentalPeriodUsesBillingUnits($item->rental_period ?? 'per_day');
    $billingUnits = ($hasBookingDates ?? false) && $usesBilling
        ? ($bookingDefaultsByPriceType[$item->rental_period ?? 'per_day'] ?? null)
        : null;
    $usesVariants = $item->usesVariants();
    $activeVariants = $usesVariants
        ? $item->variants->filter(fn ($v) => $v->is_active && $v->is_available)
        : collect();
    $unitPrice = (float) $item->price;
    if ($usesVariants && $activeVariants->isNotEmpty()) {
        $unitPrice = (float) $activeVariants->min('price');
    }
    $linePreview = ($hasBookingDates ?? false)
        ? \App\Support\StorefrontRentalPricing::lineSubtotal($unitPrice, 1, $item->rental_period ?? 'per_day', $billingUnits)
        : $unitPrice;
    $variantAttributes = $item->variantAttributes;
    $itemWaUrl = ($whatsappEnabled ?? false)
        ? \App\Support\StorefrontWhatsApp::url(
            $store,
            \App\Support\StorefrontWhatsApp::itemMessage(
                $vendor,
                $item,
                route('storefront.items.show', [$vendor->slug, $item]),
                $whatsappRentalHint ?? null,
            ),
        )
        : null;
@endphp

<div class="store-site-container py-6 sm:py-8">
    <nav class="mb-5 text-sm text-gray-500" aria-label="{{ __('vendor.store_breadcrumb') }}">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li>
                <a href="{{ route('storefront.show', $vendor->slug) }}" class="store-link font-medium hover:underline">{{ $vendor->name }}</a>
            </li>
            @if($item->category)
                <li aria-hidden="true" class="text-gray-300">/</li>
                <li>
                    <a href="{{ route('storefront.category', [$vendor->slug, $item->category->slug]) }}" class="store-link font-medium hover:underline">{{ $item->category->name }}</a>
                </li>
            @endif
            <li aria-hidden="true" class="text-gray-300">/</li>
            <li class="font-semibold text-gray-800" aria-current="page">{{ $item->name }}</li>
        </ol>
    </nav>

    <article class="{{ $theme['classes']['section'] }} overflow-hidden bg-white">
        <div class="grid grid-cols-1 gap-0 lg:grid-cols-2">
            <div class="relative aspect-square bg-gray-100 lg:aspect-auto lg:min-h-[28rem]">
                @if($item->photo_url)
                    <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full min-h-[16rem] w-full items-center justify-center store-accent-bg-soft lg:min-h-[28rem]">
                        <i class="fas fa-image text-5xl text-gray-300" aria-hidden="true"></i>
                    </div>
                @endif
            </div>

            <div class="flex flex-col p-5 sm:p-6 lg:p-8">
                @if($item->category)
                    <p class="text-xs font-bold uppercase tracking-wider store-accent-text">{{ $item->category->name }}</p>
                @endif
                <h1 class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl">{{ $item->name }}</h1>
                @if($item->item_code)
                    <p class="mt-1 font-mono text-xs text-gray-500">{{ $item->item_code }}</p>
                @endif

                @if($showPrice)
                    <div class="mt-5 rounded-xl border border-gray-100 bg-gray-50/80 px-4 py-4">
                        <p class="text-3xl font-bold store-accent-text-dark">₹{{ number_format($linePreview, 0) }}</p>
                        @if($periodLabel)
                            <p class="mt-1 text-sm text-gray-600">
                                @if($billingUnits)
                                    ₹{{ number_format($unitPrice, 0) }} / {{ $periodLabel }} × {{ rtrim(rtrim(number_format($billingUnits, 2), '0'), '.') }}
                                    @if($hasBookingDates ?? false)
                                        <span class="text-gray-500">· {{ __('vendor.store_price_for_period') }}</span>
                                    @endif
                                @else
                                    / {{ $periodLabel }}
                                @endif
                            </p>
                        @endif
                        @if(($hasBookingDates ?? false) && ! $billingUnits)
                            <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_price_for_period') }}</p>
                        @endif
                    </div>
                @else
                    <p class="mt-5 text-sm font-medium text-gray-500">{{ __('vendor.store_public_price_on_request') }}</p>
                @endif

                @if($item->description)
                    <div class="mt-5 border-t border-gray-100 pt-5">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">{{ __('vendor.description') }}</h2>
                        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ $item->description }}</p>
                    </div>
                @endif

                @if($usesVariants && $activeVariants->isNotEmpty())
                    <div class="mt-5 border-t border-gray-100 pt-5" x-data="{ detailVariant: '' }">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">{{ __('vendor.store_choose_variant') }}</h2>
                        <div class="mt-3 space-y-2">
                            @foreach($activeVariants as $variant)
                                @php
                                    $variantLabel = $variant->displayLabel($variantAttributes);
                                    $variantStock = $variant->rentableAvailableStock();
                                    $outOfStock = $variant->manage_stock && $variantStock < 1;
                                    $variantPrice = (float) $variant->price;
                                    $variantLine = ($hasBookingDates ?? false)
                                        ? \App\Support\StorefrontRentalPricing::lineSubtotal($variantPrice, 1, $item->rental_period ?? 'per_day', $billingUnits)
                                        : $variantPrice;
                                @endphp
                                <button type="button"
                                        @if(! $outOfStock) @click="detailVariant = '{{ $variant->id }}'" @endif
                                        :class="detailVariant === '{{ $variant->id }}'
                                            ? 'border-[var(--store-accent)] store-accent-bg-soft ring-2 ring-[var(--store-accent-ring)]'
                                            : '{{ $outOfStock ? 'cursor-not-allowed border-gray-100 bg-gray-50 opacity-60' : 'border-gray-200 bg-white hover:border-gray-300' }}'"
                                        class="flex w-full items-center gap-3 rounded-xl border px-3 py-3 text-left transition {{ $outOfStock ? 'cursor-not-allowed border-gray-100 bg-gray-50 opacity-60' : '' }}"
                                        @if($outOfStock) disabled @endif>
                                    <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                                          :class="detailVariant === '{{ $variant->id }}' ? 'store-accent-bg border-[var(--store-accent)] text-white' : 'border-gray-300 bg-white'">
                                        <i x-show="detailVariant === '{{ $variant->id }}'" class="fas fa-check text-[10px]" aria-hidden="true"></i>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-semibold text-gray-900">{{ $variantLabel }}</span>
                                        @if($outOfStock)
                                            <span class="text-xs text-gray-500">{{ __('vendor.store_out_of_stock') }}</span>
                                        @endif
                                    </span>
                                    @if($showPrice)
                                        <span class="shrink-0 text-sm font-bold store-accent-text-dark">₹{{ number_format($variantLine, 0) }}</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                        <p x-show="$root.detailVariantError" x-text="$root.detailVariantError" x-cloak class="mt-3 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-sm text-red-700"></p>
                        <button type="button"
                                @click="addItemFromDetail({{ $item->id }}, detailVariant)"
                                class="{{ $theme['classes']['btn'] }} store-btn-primary mt-4 flex h-12 w-full items-center justify-center gap-2 text-sm font-bold">
                            <i class="fas fa-cart-plus" aria-hidden="true"></i>
                            {{ __('vendor.store_add_to_cart') }}
                        </button>
                        @if($itemWaUrl)
                            @include('storefront.partials.whatsapp-button', [
                                'url' => $itemWaUrl,
                                'label' => __('vendor.store_ask_on_whatsapp'),
                                'variant' => 'outline',
                                'class' => 'mt-3 h-11 w-full',
                            ])
                        @endif
                    </div>
                @else
                    <button type="button" @click="addItem({{ $item->id }})"
                            class="{{ $theme['classes']['btn'] }} store-btn-primary mt-6 flex h-12 w-full items-center justify-center gap-2 text-sm font-bold">
                        <i class="fas fa-cart-plus" aria-hidden="true"></i>
                        {{ __('vendor.store_add_to_cart') }}
                    </button>
                    @if($itemWaUrl)
                        @include('storefront.partials.whatsapp-button', [
                            'url' => $itemWaUrl,
                            'label' => __('vendor.store_ask_on_whatsapp'),
                            'variant' => 'outline',
                            'class' => 'mt-3 h-11 w-full',
                        ])
                    @endif
                @endif

                @if(! ($hasBookingDates ?? false))
                    <p class="mt-3 text-center text-xs text-amber-700">
                        <button type="button" @click="openBookingModal()" class="font-semibold underline hover:no-underline">
                            {{ __('vendor.store_set_booking_dates') }}
                        </button>
                        {{ __('vendor.store_booking_for_pricing') }}
                    </p>
                @endif
            </div>
        </div>
    </article>
</div>
@endsection
