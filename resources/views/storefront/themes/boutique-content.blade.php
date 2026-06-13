@include('storefront.partials.store-banner', ['heroParams' => ['heroVariant' => 'split']])

<div class="store-site-container py-6 sm:py-10">
    <div class="lg:flex lg:items-start lg:gap-10">
        <aside class="hidden w-56 shrink-0 lg:block xl:w-64">
            @include('storefront.partials.categories-sidebar')
        </aside>

        <div class="min-w-0 flex-1">
            @include('storefront.partials.category-header')

            <div class="lg:hidden">
                @include('storefront.partials.categories')
            </div>

            @if(($isHomePage ?? false) && ! empty($homeSections))
                @include('storefront.partials.home-sections', [
                    'sectionCardProps' => ['boutiqueCard' => true],
                ])
            @endif

            <section class="mt-5 lg:mt-0" aria-labelledby="store-catalog-heading">
                @include('storefront.partials.catalog-toolbar', [
                    'catalogTitle' => ($isCategoryPage ?? false)
                        ? $pageCategory->name
                        : (($isHomePage ?? false) ? __('vendor.store_all_items') : __('vendor.store_public_catalog')),
                ])

                @if($items->isEmpty())
                    <div class="{{ $theme['classes']['section'] }} store-surface-bg px-4 py-16 text-center">
                        <p class="text-sm text-gray-600">{{ __('vendor.store_public_no_items') }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        @foreach($items as $item)
                            @include('storefront.partials.item-card', ['item' => $item, 'boutiqueCard' => true])
                        @endforeach
                    </div>
                    <p x-show="searchQuery.trim() && filteredItemCount === 0"
                       x-cloak
                       class="mt-6 text-center text-sm text-gray-500">
                        {{ __('vendor.store_search_no_results') }}
                    </p>
                @endif
            </section>

            @include('storefront.partials.about')
            @include('storefront.partials.locations')
        </div>
    </div>
</div>
