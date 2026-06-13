@extends('vendor.layouts.app')

@section('title', __('vendor.create_order'))
@section('page-title', __('vendor.create_order'))

@section('styles')
@include('vendor.orders.partials.order-wizard-flatpickr-assets')
<style>
    [x-cloak]{display:none !important;}
    .flatpickr-calendar { border-radius: 12px !important; box-shadow: 0 10px 40px rgba(0,0,0,.15) !important; border: 1px solid #e5e7eb !important; font-family: 'Inter', sans-serif !important; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #059669 !important; border-color: #059669 !important; }
    .flatpickr-day.today { border-color: #059669 !important; }
    .flatpickr-day:hover { background: #d1fae5 !important; }
    .flatpickr-months .flatpickr-month { height: 40px !important; }
    .flatpickr-current-month { font-size: 1rem !important; font-weight: 600 !important; }
    .date-input-wrapper { position: relative; }
    .date-input-wrapper .date-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 14px; }
    .date-input-wrapper input { padding-right: 32px; min-height: 40px; font-size: 0.875rem; }
    .time-input-wrapper { position: relative; }
    .time-input-wrapper .time-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 14px; }
    .booking-time-select {
        min-height: 40px;
        font-size: 0.875rem;
        padding-right: 32px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: none;
        color: #111827;
    }
    .booking-time-select.is-placeholder { color: #9ca3af; }
    .booking-time-select option { color: #111827; }
    .booking-time-select option[value=""] { color: #9ca3af; }
    @media (min-width: 640px) {
        .date-input-wrapper input { min-height: 2.5rem; }
        .booking-time-select { min-height: 2.5rem; }
    }
</style>
@endsection

@section('content')
<div class="mx-auto max-w-2xl pb-[max(4.25rem,env(safe-area-inset-bottom))] max-md:pb-[max(11rem,env(safe-area-inset-bottom))] md:pb-0">
    @include('vendor.orders.partials.wizard-steps', ['current' => 4, 'compact' => true])

    @include('vendor.orders.partials.wizard-fulfillment-inner', [
        'livewireWizard' => false,
        'wizard' => $wizard,
    ])
</div>
@endsection

@section('scripts')
@vite('resources/js/order-wizard-datetime.js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    requestAnimationFrame(function () {
        window.dispatchEvent(new CustomEvent('remount-fulfillment-datetimes'));
    });
});
</script>
@endsection
