@extends('vendor.layouts.app')

@section('title', __('vendor.add_coupon'))
@section('page-title', __('vendor.add_coupon'))

@section('styles')
    @include('vendor.coupons.partials.coupon-flatpickr-assets')
@endsection

@section('content')
<div class="mx-auto max-w-5xl space-y-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
    <header>
        <a href="{{ route('vendor.coupons.index') }}"
           wire:navigate
           class="mb-2 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-600">
            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
            {{ __('vendor.back_to_coupons') }}
        </a>
        <h1 class="text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.add_coupon') }}</h1>
        <p class="mt-0.5 text-sm text-gray-600">{{ __('vendor.create_new_coupon_code') }}</p>
    </header>

    @include('vendor.coupons.partials.coupon-form')
</div>
@endsection

@section('scripts')
@vite(['resources/js/order-wizard-datetime.js', 'resources/js/coupon-date-pickers.js'])
@endsection
