@include('storefront.partials.store-banner', ['heroParams' => ['heroVariant' => 'neon']])

<div class="store-site-container py-6 sm:py-8">
    @include('storefront.partials.category-header')

    @include('storefront.partials.categories')

    @if(($isHomePage ?? false) && ! empty($homeSections))
        @include('storefront.partials.home-sections')
    @endif

    <section class="mt-5" aria-labelledby="store-catalog-heading">
        @include('storefront.partials.catalog-toolbar', [
            'toolbarSubtitle' => __('vendor.store_theme_neon_tagline'),
            'catalogTitle' => ($isCategoryPage ?? false)
                ? $pageCategory->name
                : (($isHomePage ?? false) ? __('vendor.store_all_items') : __('vendor.store_public_catalog')),
        ])

        @if($items->isEmpty())
            <div class="{{ $theme['classes']['section'] }} store-surface-bg px-4 py-14 text-center">
                <p class="text-sm font-bold text-gray-700">{{ __('vendor.store_public_no_items') }}</p>
            </div>
        @else
            <div class="store-theme-neon-scroll -mx-4 flex gap-4 overflow-x-auto px-4 pb-4 snap-x snap-mandatory sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-4 lg:gap-5">
                @foreach($items as $item)
                    <div class="w-[72vw] shrink-0 snap-center sm:w-auto">
                        @include('storefront.partials.item-card', ['item' => $item])
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
