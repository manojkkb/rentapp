<div class="store-site-container max-w-5xl py-8 sm:py-10">
    <header class="mb-8 border-b border-gray-100 pb-8">
        @if($banner['subtitle'] ?? $store->tagline)
            <p class="text-xs font-medium uppercase tracking-[0.2em] text-gray-400">{{ $banner['subtitle'] ?? $store->tagline }}</p>
        @endif
        <h1 class="mt-1 text-3xl font-light tracking-tight text-gray-900 sm:text-4xl">{{ $banner['title'] ?? $vendor->name }}</h1>
        @if($store->description)
            <p class="mt-4 max-w-2xl text-base leading-relaxed text-gray-600">{{ $store->description }}</p>
        @endif
        <div class="mt-5 flex flex-wrap items-center gap-4 text-sm text-gray-500">
            @if($store->business_phone)
                <a href="tel:{{ preg_replace('/\s+/', '', $store->business_phone) }}" class="font-medium store-accent-text hover:underline">
                    <i class="fas fa-phone mr-1 text-xs" aria-hidden="true"></i>{{ $store->business_phone }}
                </a>
            @endif
            @if($vendor->city)
                <span><i class="fas fa-map-marker-alt mr-1 text-xs" aria-hidden="true"></i>{{ $vendor->city }}{{ $vendor->state ? ', '.$vendor->state : '' }}</span>
            @endif
            @if($store->pickup_enabled)
                <span>{{ __('vendor.pickup') }}</span>
            @endif
            @if($store->delivery_enabled)
                <span>{{ __('vendor.delivery') }}</span>
            @endif
        </div>
    </header>

    @include('storefront.partials.category-header')

    @include('storefront.partials.categories', ['minimal' => true])

    @if(($isHomePage ?? false) && ! empty($homeSections))
        @include('storefront.partials.home-sections')
    @endif

    <section class="mt-8" aria-labelledby="store-catalog-heading">
        @include('storefront.partials.catalog-toolbar', [
            'catalogTitle' => ($isCategoryPage ?? false)
                ? $pageCategory->name
                : (($isHomePage ?? false) ? __('vendor.store_all_items') : __('vendor.store_public_catalog')),
        ])

        @if($items->isEmpty())
            <p class="text-sm text-gray-500">{{ __('vendor.store_public_no_items') }}</p>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($items as $item)
                    @include('storefront.partials.item-card', ['item' => $item])
                @endforeach
            </div>
            <p x-show="searchQuery.trim() && filteredItemCount === 0"
               x-cloak
               class="mt-6 text-center text-sm text-gray-500">
                {{ __('vendor.store_search_no_results') }}
            </p>
        @endif
    </section>

    @if($locations->isNotEmpty())
        <section class="mt-12 border-t border-gray-100 pt-10">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-900">{{ __('vendor.store_tab_locations') }}</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach($locations as $location)
                    <div class="border border-gray-100 p-4">
                        <h3 class="font-medium text-gray-900">{{ $location->name }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $location->full_address }}</p>
                        @if($location->phone)
                            <a href="tel:{{ preg_replace('/\s+/', '', $location->phone) }}" class="mt-2 inline-block text-sm store-accent-text">{{ $location->phone }}</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
