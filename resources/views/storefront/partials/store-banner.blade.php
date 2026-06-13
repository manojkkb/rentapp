@if($banner['enabled'] ?? true)
    @include('storefront.partials.hero', $heroParams ?? [])
@endif