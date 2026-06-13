@php
    $slug = $vendor->slug;
    $activeCategoryId = $activeCategory ?? ($pageCategory->id ?? null);
@endphp
@if($categories->isNotEmpty())
    <nav class="space-y-1" aria-label="{{ __('vendor.categories') }}">
        <p class="mb-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-400">{{ __('vendor.categories') }}</p>
        <a href="{{ route('storefront.show', $slug) }}"
           class="{{ $theme['classes']['chip'] }} block px-3 py-2.5 text-sm transition {{ ! $activeCategoryId ? 'store-chip-active' : 'border-gray-200 text-gray-700 hover:border-gray-300' }}">
            {{ __('vendor.all') }}
        </a>
        @foreach($categories as $category)
            <a href="{{ route('storefront.category', [$slug, $category->slug]) }}"
               class="{{ $theme['classes']['chip'] }} block px-3 py-2.5 text-sm transition {{ $activeCategoryId === $category->id ? 'store-chip-active' : 'border-gray-200 text-gray-700 hover:border-gray-300' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </nav>

    @if($store->business_phone || $vendor->city)
        <div class="mt-8 rounded-xl border border-gray-200/80 p-4 text-sm text-gray-600">
            @if($store->business_phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $store->business_phone) }}" class="block font-medium store-accent-text hover:underline">
                    <i class="fas fa-phone mr-1.5 text-xs" aria-hidden="true"></i>{{ $store->business_phone }}
                </a>
            @endif
            @if($vendor->city)
                <p class="mt-2 text-xs text-gray-500">
                    <i class="fas fa-map-marker-alt mr-1" aria-hidden="true"></i>
                    {{ $vendor->city }}{{ $vendor->state ? ', '.$vendor->state : '' }}
                </p>
            @endif
        </div>
    @endif
@endif
