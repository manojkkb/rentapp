@extends('storefront.shop-layout')

@section('title', __('vendor.store_contact_us').' — '.$vendor->name)

@section('content')
@include('storefront.partials.page-banner', [
    'title' => __('vendor.store_contact_us'),
    'subtitle' => __('vendor.store_contact_subtitle'),
    'backUrl' => route('storefront.show', $vendor->slug),
    'backLabel' => __('vendor.store_back_to_shop'),
])

<div class="store-site-container space-y-6 py-8 sm:space-y-8 sm:py-10">
    @if(($whatsappEnabled ?? false) && ($whatsappContactUrl ?? null))
        <div class="{{ $theme['classes']['section'] }} flex flex-col items-start gap-4 bg-gradient-to-r from-[#25D366]/10 to-white p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
            <div>
                <p class="text-sm font-semibold text-gray-900">{{ __('vendor.store_whatsapp_contact_title') }}</p>
                <p class="mt-1 text-sm text-gray-600">{{ __('vendor.store_whatsapp_contact_hint') }}</p>
                @if($whatsappNumber ?? null)
                    <p class="mt-2 font-mono text-sm text-gray-700">{{ $whatsappNumber }}</p>
                @endif
            </div>
            @include('storefront.partials.whatsapp-button', [
                'url' => $whatsappContactUrl,
                'label' => __('vendor.store_whatsapp_chat'),
                'variant' => 'primary',
                'class' => 'h-12 px-6',
            ])
        </div>
    @endif

    @if(\App\Support\StorefrontPages::content($vendor, \App\Models\VendorStorePage::KEY_CONTACT))
        <article class="{{ $theme['classes']['section'] }} bg-white p-5 sm:p-6">
            <div class="prose prose-sm max-w-none text-gray-700 sm:prose-base">
                {!! \App\Support\StorefrontRichText::render(\App\Support\StorefrontPages::content($vendor, \App\Models\VendorStorePage::KEY_CONTACT)) !!}
            </div>
        </article>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @if($store->business_phone)
            <a href="tel:{{ preg_replace('/\s+/', '', $store->business_phone) }}"
               class="{{ $theme['classes']['section'] }} group bg-white p-5 transition hover:shadow-md">
                <span class="flex h-10 w-10 items-center justify-center rounded-full store-accent-bg-soft store-accent-text">
                    <i class="fas fa-phone" aria-hidden="true"></i>
                </span>
                <p class="mt-3 text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.mobile') }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 group-hover:store-accent-text-dark">{{ $store->business_phone }}</p>
            </a>
        @endif

        @if($store->business_email)
            <a href="mailto:{{ $store->business_email }}"
               class="{{ $theme['classes']['section'] }} group bg-white p-5 transition hover:shadow-md">
                <span class="flex h-10 w-10 items-center justify-center rounded-full store-accent-bg-soft store-accent-text">
                    <i class="fas fa-envelope" aria-hidden="true"></i>
                </span>
                <p class="mt-3 text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.email') }}</p>
                <p class="mt-1 break-all text-sm font-semibold text-gray-900 group-hover:store-accent-text-dark">{{ $store->business_email }}</p>
            </a>
        @endif

        @if($vendor->full_address)
            <div class="{{ $theme['classes']['section'] }} bg-white p-5">
                <span class="flex h-10 w-10 items-center justify-center rounded-full store-accent-bg-soft store-accent-text">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                </span>
                <p class="mt-3 text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.address') }}</p>
                <p class="mt-1 text-sm leading-relaxed text-gray-700">{{ $vendor->full_address }}</p>
            </div>
        @endif
    </div>

    @if($locations->isNotEmpty())
        <section>
            <h2 class="mb-4 text-lg font-bold text-gray-900">{{ __('vendor.store_tab_locations') }}</h2>
            @include('storefront.partials.locations', ['locations' => $locations])
        </section>
    @endif
</div>
@endsection
