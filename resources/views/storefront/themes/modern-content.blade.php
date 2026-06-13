@include('storefront.partials.store-banner', ['heroParams' => ['heroOverlay' => true]])

@include('storefront.partials.shop-body', [
    'toolbarSubtitle' => __('vendor.store_public_rent_now'),
    'itemCardProps' => ['modernCard' => true],
    'sectionCardProps' => ['modernCard' => true],
])
