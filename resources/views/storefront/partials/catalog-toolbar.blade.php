@php
    $toolbarTitle = $catalogTitle ?? $toolbarTitle ?? __('vendor.store_public_catalog');
    $toolbarSubtitle = $toolbarSubtitle ?? null;
    $itemCount = $items->count();
@endphp
<div class="mb-4 flex flex-col gap-3 sm:mb-5 sm:flex-row sm:items-center sm:justify-between">
    <div class="flex min-w-0 items-center gap-2 sm:gap-3">
        <div class="min-w-0">
            @if($toolbarSubtitle)
                <p class="text-[10px] font-bold uppercase tracking-widest store-accent-text sm:text-xs">{{ $toolbarSubtitle }}</p>
            @endif
            <h2 id="store-catalog-heading" class="text-lg font-bold text-gray-900 sm:text-xl">{{ $toolbarTitle }}</h2>
        </div>
        <span class="shrink-0 rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-600 shadow-sm ring-1 ring-gray-200"
              x-show="!searchQuery.trim()"
              x-text="'{{ $itemCount }} {{ __('vendor.items') }}'"></span>
        <span class="shrink-0 rounded-full store-accent-bg-soft px-3 py-1 text-xs font-semibold store-accent-text-dark"
              x-show="searchQuery.trim()"
              x-cloak
              x-text="filteredItemCount + ' {{ __('vendor.store_search_results') }}'"></span>
    </div>
    <div class="relative w-full sm:w-72 sm:shrink-0">
        <label for="store-catalog-search" class="sr-only">{{ __('vendor.store_search_items') }}</label>
        <i class="fas fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400" aria-hidden="true"></i>
        <input id="store-catalog-search"
               type="search"
               x-model="searchQuery"
               placeholder="{{ __('vendor.store_search_items_placeholder') }}"
               class="store-input pl-9 pr-9"
               autocomplete="off">
        <button type="button"
                x-show="searchQuery.trim()"
                x-cloak
                @click="searchQuery = ''; updateFilteredCount()"
                class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                aria-label="{{ __('vendor.clear') }}">
            <i class="fas fa-times text-xs" aria-hidden="true"></i>
        </button>
    </div>
</div>
