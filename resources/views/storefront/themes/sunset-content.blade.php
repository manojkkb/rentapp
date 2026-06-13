@include('storefront.partials.store-banner', ['heroParams' => ['heroVariant' => 'sunset']])

<div class="store-site-container py-6 sm:py-8">
    @include('storefront.partials.category-header')

    @include('storefront.partials.categories')

    @if(($isHomePage ?? false) && ! empty($homeSections))
        @include('storefront.partials.home-sections')
    @endif

    <section class="mt-5 sm:mt-6" aria-labelledby="store-catalog-heading">
        @include('storefront.partials.catalog-toolbar', [
            'catalogTitle' => ($isCategoryPage ?? false)
                ? $pageCategory->name
                : (($isHomePage ?? false) ? __('vendor.store_all_items') : __('vendor.store_public_catalog')),
        ])

        @if($items->isEmpty())
            <div class="{{ $theme['classes']['section'] }} store-surface-bg px-4 py-14 text-center">
                <p class="text-sm text-gray-600">{{ __('vendor.store_public_no_items') }}</p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4 lg:gap-5">
                @foreach($items as $index => $item)
                    <div class="{{ $index === 0 ? 'col-span-2 row-span-2 lg:col-span-2 lg:row-span-2' : '' }}">
                        @include('storefront.partials.item-card', [
                            'item' => $item,
                            'sunsetFeatured' => $index === 0,
                        ])
                    </div>
                @endforeach
            </div>
            <p x-show="searchQuery.trim() && filteredItemCount === 0"
               x-cloak
               class="mt-6 text-center text-sm text-gray-600">
                {{ __('vendor.store_search_no_results') }}
            </p>
        @endif
    </section>

    @include('storefront.partials.about')
    @include('storefront.partials.locations')
</div>
