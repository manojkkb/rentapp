@php
    $slug = $vendor->slug;
    $isActive = fn (array $routes) => collect($routes)->contains(fn ($r) => request()->routeIs($r));
@endphp
<aside class="hidden w-64 shrink-0 border-r border-gray-200 bg-white lg:block">
    <div class="sticky top-0 flex h-dvh flex-col p-4">
        <a href="{{ route('storefront.show', $slug) }}" class="mb-4 flex items-center gap-3">
            @if($vendor->logo_url)
                <img src="{{ $vendor->logo_url }}" alt="" class="h-10 w-10 rounded-lg object-contain">
            @else
                <div class="flex h-10 w-10 items-center justify-center rounded-lg store-accent-bg text-sm font-bold text-white">
                    {{ strtoupper(substr($vendor->name, 0, 1)) }}
                </div>
            @endif
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-gray-900">{{ $vendor->name }}</p>
                @if($store->tagline)
                    <p class="truncate text-xs text-gray-500">{{ $store->tagline }}</p>
                @endif
            </div>
        </a>

        @include('storefront.partials.sidebar-nav')

        <div class="mt-auto rounded-xl store-accent-bg-soft p-3 text-xs text-gray-600">
            @if($store->business_phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $store->business_phone) }}" class="font-semibold store-accent-text-dark">
                    <i class="fas fa-phone mr-1"></i>{{ $store->business_phone }}
                </a>
            @endif
        </div>
    </div>
</aside>
