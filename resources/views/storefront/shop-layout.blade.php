@php
    $isMinimal = ($theme['template'] ?? 'classic') === 'minimal';
    $slug = $vendor->slug;
    $seo = $seo ?? \App\Support\StorefrontSeo::forHome($vendor, $store);
    $storefrontConfig = [
        'booking' => $booking ?? ['is_set' => false],
        'hasBookingDates' => $hasBookingDates ?? false,
        'bookingSaveUrl' => $bookingSaveUrl ?? route('storefront.booking.save', $slug),
        'cartAddUrl' => $cartAddUrl ?? route('storefront.cart.add', $slug),
        'catalogItems' => $catalogItemsForJs ?? [],
        'bookingDefaultsByPriceType' => $bookingDefaultsByPriceType ?? [],
        'i18n' => [
            'added' => __('vendor.store_added_to_cart'),
            'booking_required' => __('vendor.store_booking_required'),
            'select_variant' => __('vendor.store_select_variant'),
            'booking_saved' => __('vendor.store_booking_saved'),
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="{{ $theme['header'] }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('storefront.partials.seo-head')
    @vite(['resources/css/app.css', 'resources/js/storefront-shop.js'])
    @include('storefront.partials.theme-styles')
    @stack('styles')
</head>
<body class="store-page-bg min-h-dvh antialiased {{ $theme['classes']['page'] }}"
      x-data="storefrontShop(@js($storefrontConfig))">
    @include('storefront.partials.header', ['minimal' => $isMinimal])

    <div x-show="menuOpen" x-cloak @click="menuOpen = false"
         class="fixed inset-0 z-40 bg-black/40 lg:hidden"></div>
    <div x-show="menuOpen" x-cloak
         class="store-mobile-menu-bg fixed inset-x-0 top-0 z-50 max-h-[85dvh] overflow-y-auto border-b shadow-xl lg:hidden"
         style="border-color: var(--store-input-border);">
        @include('storefront.partials.mobile-nav')
    </div>

    @include('storefront.partials.booking-strip')
    @include('storefront.partials.booking-modal')
    @include('storefront.partials.variant-modal')

    @if(session('success'))
        <div class="store-site-container pt-3">
            <div class="rounded-lg border px-4 py-2.5 text-sm store-alert-success">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <main class="{{ (($whatsappEnabled ?? false) || $store->business_phone) ? 'pb-24 sm:pb-0' : '' }}">
        @yield('content')
    </main>

    @include('storefront.partials.footer')
    @include('storefront.partials.mobile-bar')
    @stack('scripts')
</body>
</html>
