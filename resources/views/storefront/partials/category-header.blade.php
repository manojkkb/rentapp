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
@endif
