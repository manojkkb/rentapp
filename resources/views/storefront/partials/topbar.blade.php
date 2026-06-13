<header class="sticky top-0 z-30 flex items-center gap-3 border-b border-gray-200 bg-white/95 px-3 py-3 backdrop-blur-md sm:px-4">
    <button type="button" @click="sidebarOpen = true" class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 lg:hidden" aria-label="Menu">
        <i class="fas fa-bars" aria-hidden="true"></i>
    </button>
    <div class="min-w-0 flex-1 lg:hidden">
        <p class="truncate text-sm font-bold text-gray-900">{{ $vendor->name }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('storefront.cart', $vendor->slug) }}"
           class="relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50">
            <i class="fas fa-shopping-cart text-sm" aria-hidden="true"></i>
            @if(($cartCount ?? 0) > 0)
                <span data-cart-badge class="absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full store-accent-bg px-1 text-[10px] font-bold text-white{{ ($cartCount ?? 0) > 0 ? '' : ' hidden' }}">
                    {{ $cartCount }}
                </span>
            @else
                <span data-cart-badge class="absolute -right-1 -top-1 hidden h-5 min-w-[1.25rem] items-center justify-center rounded-full store-accent-bg px-1 text-[10px] font-bold text-white"></span>
            @endif
        </a>
        @auth
            <span class="hidden text-xs text-gray-600 sm:inline">{{ Auth::user()->name }}</span>
        @endauth
    </div>
</header>
