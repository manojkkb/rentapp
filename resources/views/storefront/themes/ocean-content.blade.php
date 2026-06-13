@include('storefront.partials.store-banner')

@include('storefront.partials.shop-body', [
    'toolbarSubtitle' => __('vendor.store_theme_ocean_tagline'),
    'itemCardProps' => ['oceanCard' => true],
    'sectionCardProps' => ['oceanCard' => true],
])
