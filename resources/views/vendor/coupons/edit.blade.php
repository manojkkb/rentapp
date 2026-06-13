@extends('vendor.layouts.app')

@section('title', __('vendor.edit_coupon'))
@section('page-title', __('vendor.edit_coupon'))

@section('styles')
    @include('vendor.coupons.partials.coupon-flatpickr-assets')
@endsection

@section('content')
<div class="mx-auto max-w-5xl space-y-4 pb-[max(1rem,env(safe-area-inset-bottom))]">
    <header>
        <a href="{{ route('vendor.coupons.show', $coupon) }}"
           wire:navigate
           class="mb-2 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-600">
            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
            {{ __('vendor.back_to_coupon') }}
        </a>
        <h1 class="text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.edit_coupon') }}</h1>
        <p class="mt-0.5 font-mono text-sm text-violet-700">{{ $coupon->code }}</p>
    </header>

    @include('vendor.coupons.partials.coupon-form', ['coupon' => $coupon])
</div>
@endsection

@section('scripts')
@vite(['resources/js/order-wizard-datetime.js', 'resources/js/coupon-date-pickers.js'])
@endsection
