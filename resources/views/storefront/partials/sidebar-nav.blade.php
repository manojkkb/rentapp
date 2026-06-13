@php
    $slug = $vendor->slug;
    $linkClass = 'store-sidebar-link flex items-center gap-2.5 rounded-lg border border-transparent px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50';
    $activeClass = ' active';
@endphp
<nav class="space-y-1">
    <a href="{{ route('storefront.show', $slug) }}"
       class="{{ $linkClass }}{{ request()->routeIs('storefront.show') ? $activeClass : '' }}">
        <i class="fas fa-store w-4 text-center text-xs" aria-hidden="true"></i>
        {{ __('vendor.store_nav_shop') }}
    </a>

    @if($categories->isNotEmpty() ?? false)
        <p class="px-3 pt-3 text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('vendor.categories') }}</p>
        <a href="{{ route('storefront.show', $slug) }}"
           class="{{ $linkClass }}{{ request()->routeIs('storefront.show') && ! request('category') ? $activeClass : '' }}">
            <i class="fas fa-border-all w-4 text-center text-xs" aria-hidden="true"></i>
            {{ __('vendor.all') }}
        </a>
        @foreach(($categories ?? collect()) as $category)
            <a href="{{ route('storefront.show', ['slug' => $slug, 'category' => $category->id]) }}"
               class="{{ $linkClass }}{{ (int) request('category') === $category->id ? $activeClass : '' }}">
                <i class="fas fa-tag w-4 text-center text-xs text-gray-400" aria-hidden="true"></i>
                <span class="truncate">{{ $category->name }}</span>
            </a>
        @endforeach
    @endif

    <p class="px-3 pt-4 text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('vendor.store_nav_account') }}</p>
    <a href="{{ route('storefront.cart', $slug) }}"
       class="{{ $linkClass }}{{ request()->routeIs('storefront.cart') ? $activeClass : '' }}">
        <i class="fas fa-shopping-cart w-4 text-center text-xs" aria-hidden="true"></i>
        {{ __('vendor.store_nav_cart') }}
        @if(($cartCount ?? 0) > 0)
            <span data-cart-badge class="ml-auto rounded-full store-accent-bg px-2 py-0.5 text-[10px] font-bold text-white">{{ $cartCount }}</span>
        @else
            <span data-cart-badge class="ml-auto hidden rounded-full store-accent-bg px-2 py-0.5 text-[10px] font-bold text-white"></span>
        @endif
    </a>
    <a href="{{ route('storefront.checkout', $slug) }}"
       class="{{ $linkClass }}{{ request()->routeIs('storefront.checkout') ? $activeClass : '' }}">
        <i class="fas fa-credit-card w-4 text-center text-xs" aria-hidden="true"></i>
        {{ __('vendor.store_nav_checkout') }}
    </a>
    <a href="{{ route('storefront.orders', $slug) }}"
       class="{{ $linkClass }}{{ request()->routeIs('storefront.orders*') ? $activeClass : '' }}">
        <i class="fas fa-receipt w-4 text-center text-xs" aria-hidden="true"></i>
        {{ __('vendor.store_nav_orders') }}
    </a>
</nav>
