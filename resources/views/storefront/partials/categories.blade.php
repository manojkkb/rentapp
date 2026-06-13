@php
    $minimal = $minimal ?? false;
    $activeCategoryId = $activeCategory ?? ($pageCategory->id ?? null);
@endphp
@if($categories->isNotEmpty())
    <nav class="store-hide-scrollbar -mx-1 flex gap-2 overflow-x-auto pb-1 sm:gap-2.5" aria-label="{{ __('vendor.categories') }}">
        <a href="{{ route('storefront.show', $vendor->slug) }}"
           class="{{ $theme['classes']['chip'] }} shrink-0 px-3 py-2 text-xs font-semibold transition sm:text-sm {{ ! $activeCategoryId ? 'store-chip-active' : ($minimal ? 'border-gray-200 bg-white text-gray-700 hover:border-gray-300' : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:ring-gray-300') }}">
            {{ __('vendor.all') }}
        </a>
        @foreach($categories as $category)
            <a href="{{ route('storefront.category', [$vendor->slug, $category->slug]) }}"
               class="{{ $theme['classes']['chip'] }} shrink-0 px-3 py-2 text-xs font-semibold transition sm:text-sm {{ $activeCategoryId === $category->id ? 'store-chip-active' : ($minimal ? 'border-gray-200 bg-white text-gray-700 hover:border-gray-300' : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:ring-gray-300') }}">
                {{ $category->name }}
            </a>
        @endforeach
    </nav>
@endif
