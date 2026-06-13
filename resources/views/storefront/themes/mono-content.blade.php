@include('storefront.partials.store-banner')

@include('storefront.partials.shop-body', [
    'containerClass' => 'store-site-container max-w-4xl py-8 sm:py-10',
    'gridClass' => 'grid grid-cols-1 gap-4 sm:grid-cols-2',
    'searchEmptyClass' => 'mt-6 text-center text-sm text-gray-500',
])
