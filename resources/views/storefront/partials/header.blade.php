@php
    $minimal = $minimal ?? false;
    $slug = $vendor->slug;
    $navActive = fn (string $route) => request()->routeIs($route) ? ' active' : '';
    $headerLight = $theme['header_is_light'] ?? $minimal;
    $headerText = $headerLight ? 'text-gray-800' : 'text-white';
    $headerTextMuted = $headerLight ? 'text-gray-500' : 'text-white/80';
    $headerHover = $headerLight ? 'hover:bg-gray-100' : 'hover:bg-white/10';
    $headerNav = $headerLight ? 'text-gray-700 hover:text-gray-900' : 'text-white/90 hover:text-white';
    $headerIconBtn = $headerLight ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-white/15 text-white hover:bg-white/25';
@endphp
<header class="{{ $theme['classes']['header'] }} store-header-bg sticky top-0 z-30 {{ $headerLight ? 'border-b border-gray-200 shadow-sm' : 'shadow-md' }} {{ $headerText }}">
    <div class="store-site-container flex items-center gap-3 py-3 sm:py-3.5">
        <button type="button" @click="menuOpen = !menuOpen"
                class="rounded-lg p-2 lg:hidden {{ $headerHover }} {{ $headerText }}"
                aria-label="Menu">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>

        <a href="{{ route('storefront.show', $slug) }}" class="flex min-w-0 items-center gap-3">
            @if($vendor->logo_url)
                <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->name }}"
                     class="h-10 w-10 shrink-0 object-contain {{ $headerLight ? 'rounded-lg' : 'rounded-lg border border-white/20 bg-white p-0.5' }} sm:h-11 sm:w-11">
            @else
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-sm font-bold sm:h-11 sm:w-11 sm:text-base {{ $headerLight ? 'store-btn-primary' : 'bg-white/15' }}">
                    {{ strtoupper(substr($vendor->name, 0, 1)) }}
                </div>
            @endif
            <div class="min-w-0 hidden sm:block">
                <p class="truncate text-sm font-bold leading-tight lg:text-base">{{ $vendor->name }}</p>
                @if($store->tagline)
                    <p class="truncate text-[11px] {{ $headerTextMuted }} lg:text-xs">{{ $store->tagline }}</p>
                @endif
            </div>
        </a>

        <nav class="ml-auto hidden items-center gap-1 lg:flex" aria-label="Store">
            <a href="{{ route('storefront.show', $slug) }}"
               class="store-nav-link relative px-3 py-2 text-sm font-medium transition{{ $navActive('storefront.show') }}">
                {{ __('vendor.store_nav_shop') }}
            </a>
            <a href="{{ route('storefront.orders', $slug) }}"
               class="store-nav-link relative px-3 py-2 text-sm font-medium transition{{ $navActive('storefront.orders*') }}">
                {{ __('vendor.store_nav_orders') }}
            </a>
            <a href="{{ route('storefront.cart', $slug) }}"
               class="relative ml-1 inline-flex h-10 w-10 items-center justify-center rounded-full transition {{ $headerIconBtn }}"
               aria-label="{{ __('vendor.store_nav_cart') }}">
                <i class="fas fa-shopping-cart text-sm" aria-hidden="true"></i>
                @if(($cartCount ?? 0) > 0)
                    <span data-cart-badge class="absolute -right-0.5 -top-0.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-white px-1 text-[10px] font-bold store-accent-text">{{ $cartCount }}</span>
                @else
                    <span data-cart-badge class="absolute -right-0.5 -top-0.5 hidden h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-white px-1 text-[10px] font-bold store-accent-text"></span>
                @endif
            </a>
            @if(($whatsappEnabled ?? false) && ($whatsappContactUrl ?? null))
                <a href="{{ $whatsappContactUrl }}" target="_blank" rel="noopener noreferrer"
                   class="{{ $theme['classes']['btn'] }} ml-2 inline-flex items-center gap-1.5 bg-[#25D366] px-4 py-2 text-xs font-semibold text-white hover:bg-[#20BD5A]">
                    <i class="fab fa-whatsapp text-sm" aria-hidden="true"></i>
                    {{ __('vendor.store_whatsapp_chat') }}
                </a>
            @elseif($store->business_phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $store->business_phone) }}"
                   class="{{ $theme['classes']['btn'] }} ml-2 inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold transition {{ $headerLight ? 'store-btn-primary' : 'bg-white store-accent-text hover:bg-white/90' }}">
                    <i class="fas fa-phone text-[10px]" aria-hidden="true"></i>
                    {{ __('vendor.call') }}
                </a>
            @endif
        </nav>

        <a href="{{ route('storefront.cart', $slug) }}"
           class="relative inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full lg:hidden {{ $headerIconBtn }}">
            <i class="fas fa-shopping-cart text-sm" aria-hidden="true"></i>
            @if(($cartCount ?? 0) > 0)
                <span data-cart-badge class="absolute -right-0.5 -top-0.5 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-white px-1 text-[10px] font-bold store-accent-text">{{ $cartCount }}</span>
            @else
                <span data-cart-badge class="absolute -right-0.5 -top-0.5 hidden h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-white px-1 text-[10px] font-bold store-accent-text"></span>
            @endif
        </a>
    </div>
</header>
