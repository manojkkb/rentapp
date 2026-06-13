@php
    $slug = $vendor->slug;
    $linkClass = 'store-nav-link flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition hover:opacity-80';
@endphp
<div class="p-4 pt-14">
    <div class="mb-4 flex items-center gap-3 border-b pb-4" style="border-color: var(--store-input-border);">
        @if($vendor->logo_url)
            <img src="{{ $vendor->logo_url }}" alt="" class="h-10 w-10 rounded-lg object-contain">
        @endif
        <div class="min-w-0">
            <p class="truncate font-bold" style="color: var(--store-heading);">{{ $vendor->name }}</p>
            @if($store->tagline)
                <p class="truncate text-xs" style="color: var(--store-placeholder);">{{ $store->tagline }}</p>
            @endif
        </div>
    </div>

    <nav class="space-y-1">
        <a href="{{ route('storefront.show', $slug) }}" @click="menuOpen = false" class="{{ $linkClass }}">
            <i class="fas fa-store w-4 text-center text-xs store-accent-text" aria-hidden="true"></i>
            {{ __('vendor.store_nav_shop') }}
        </a>
        <a href="{{ route('storefront.cart', $slug) }}" @click="menuOpen = false" class="{{ $linkClass }}">
            <i class="fas fa-shopping-cart w-4 text-center text-xs store-accent-text" aria-hidden="true"></i>
            {{ __('vendor.store_nav_cart') }}
            @if(($cartCount ?? 0) > 0)
                <span data-cart-badge class="ml-auto rounded-full store-accent-bg px-2 py-0.5 text-[10px] font-bold text-white">{{ $cartCount }}</span>
            @endif
        </a>
        <a href="{{ route('storefront.checkout', $slug) }}" @click="menuOpen = false" class="{{ $linkClass }}">
            <i class="fas fa-credit-card w-4 text-center text-xs store-accent-text" aria-hidden="true"></i>
            {{ __('vendor.store_nav_checkout') }}
        </a>
        <a href="{{ route('storefront.orders', $slug) }}" @click="menuOpen = false" class="{{ $linkClass }}">
            <i class="fas fa-receipt w-4 text-center text-xs store-accent-text" aria-hidden="true"></i>
            {{ __('vendor.store_nav_orders') }}
        </a>
    </nav>

    @if(($categories ?? collect())->isNotEmpty())
        <p class="mb-2 mt-5 px-4 text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('vendor.categories') }}</p>
        <div class="flex flex-wrap gap-2 px-1">
            <a href="{{ route('storefront.show', $slug) }}" @click="menuOpen = false"
               class="{{ $theme['classes']['chip'] }} border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700">
                {{ __('vendor.all') }}
            </a>
            @foreach($categories as $category)
                <a href="{{ route('storefront.show', ['slug' => $slug, 'category' => $category->id]) }}" @click="menuOpen = false"
                   class="{{ $theme['classes']['chip'] }} border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    @endif

    @if($store->business_phone)
        <a href="tel:{{ preg_replace('/\s+/', '', $store->business_phone) }}" @click="menuOpen = false"
           class="{{ $theme['classes']['btn'] }} store-btn-primary mt-5 flex h-11 w-full items-center justify-center gap-2 text-sm font-semibold">
            <i class="fas fa-phone" aria-hidden="true"></i>
            {{ $store->business_phone }}
        </a>
    @endif
</div>
