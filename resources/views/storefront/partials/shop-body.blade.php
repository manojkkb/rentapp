@php
    $containerClass = $containerClass ?? 'store-site-container py-6 sm:py-8';
    $gridClass = $gridClass ?? 'grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4';
    $sectionCardProps = $sectionCardProps ?? [];
    $itemCardProps = $itemCardProps ?? [];
    $toolbarSubtitle = $toolbarSubtitle ?? null;
    $catalogTitle = $catalogTitle ?? (($isCategoryPage ?? false)
        ? ($pageCategory->name ?? __('vendor.categories'))
        : (($isHomePage ?? false) ? __('vendor.store_all_items') : __('vendor.items')));
    $emptyClass = $emptyClass ?? '';
    $searchEmptyClass = $searchEmptyClass ?? 'mt-6 rounded-xl border border-gray-200 bg-white px-4 py-10 text-center text-sm text-gray-600';
@endphp

<div class="{{ $containerClass }}">
    @if($isCategoryPage ?? false)
        <nav class="mb-4 text-sm text-gray-500" aria-label="{{ __('vendor.store_breadcrumb') }}">
            <ol class="flex flex-wrap items-center gap-1.5">
                <li>
                    <a href="{{ route('storefront.show', $vendor->slug) }}" class="store-link font-medium hover:underline">{{ $vendor->name }}</a>
                </li>
                <li aria-hidden="true" class="text-gray-300">/</li>
                <li class="font-semibold text-gray-800" aria-current="page">{{ $pageCategory->name }}</li>
            </ol>
        </nav>
        <header class="mb-5 sm:mb-6">
            <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ $pageCategory->name }}</h1>
            <p class="mt-1 text-sm text-gray-600">
                {{ trans_choice('vendor.store_category_item_count', $items->count(), ['count' => $items->count()]) }}
            </p>
        </header>
    @elseif($isHomePage ?? false)
        <h1 class="sr-only">{{ $vendor->name }} — {{ __('vendor.online_store') }}</h1>
    @endif

    @include('storefront.partials.categories')

    @if(($isHomePage ?? false) && ! empty($homeSections))
        @include('storefront.partials.home-sections', [
            'sectionCardProps' => $sectionCardProps,
        ])
    @endif

    <section class="mt-5 sm:mt-6" aria-labelledby="store-catalog-heading">
        @include('storefront.partials.catalog-toolbar', [
            'toolbarSubtitle' => $toolbarSubtitle,
            'catalogTitle' => $catalogTitle,
        ])

        @if($items->isEmpty())
            <div class="{{ $theme['classes']['section'] }} bg-white px-4 py-16 text-center {{ $emptyClass }}">
                <p class="text-sm text-gray-600">{{ __('vendor.store_public_no_items') }}</p>
            </div>
        @else
            <div class="{{ $gridClass }}">
                @foreach($items as $item)
                    @include('storefront.partials.item-card', array_merge(['item' => $item], $itemCardProps))
                @endforeach
            </div>
            <p x-show="searchQuery.trim() && filteredItemCount === 0"
               x-cloak
               class="{{ $searchEmptyClass }}">
                {{ __('vendor.store_search_no_results') }}
            </p>
        @endif
    </section>

    @if($isHomePage ?? false)
        @include('storefront.partials.about')
        @include('storefront.partials.locations')
    @endif
</div>
