@extends('storefront.shop-layout')

@section('title', __('vendor.store_terms_conditions').' — '.$vendor->name)

@section('content')
@include('storefront.partials.page-banner', [
    'title' => __('vendor.store_terms_conditions'),
    'subtitle' => $vendor->name,
    'backUrl' => route('storefront.show', $vendor->slug),
    'backLabel' => __('vendor.store_back_to_shop'),
])

<div class="store-site-container py-8 sm:py-10">
    <article class="{{ $theme['classes']['section'] }} bg-white p-5 sm:p-8">
        <div class="prose prose-sm max-w-none text-gray-700 sm:prose-base">
            {!! \App\Support\StorefrontRichText::render(\App\Support\StorefrontPages::content($vendor, \App\Models\VendorStorePage::KEY_TERMS)) !!}
        </div>
    </article>
</div>
@endsection
