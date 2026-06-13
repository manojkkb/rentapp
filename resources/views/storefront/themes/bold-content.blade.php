@include('storefront.partials.store-banner', ['heroParams' => ['heroVariant' => 'compact']])

@include('storefront.partials.shop-body', [
    'containerClass' => 'store-site-container py-5 sm:py-7',
    'gridClass' => 'grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3',
    'toolbarSubtitle' => __('vendor.store_theme_bold_tagline'),
    'emptyClass' => 'font-bold uppercase tracking-wide text-gray-700',
    'searchEmptyClass' => 'mt-6 rounded-md border-2 px-4 py-10 text-center text-sm font-semibold text-gray-600',
])
