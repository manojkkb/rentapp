@extends('vendor.layouts.app')

@section('title', __('vendor.create_order'))
@section('page-title', __('vendor.create_order'))

@section('styles')
<style>[x-cloak]{display:none !important;}</style>
@endsection

@section('content')
@php
    $ft = old('fulfillment_type', $wizard['fulfillment_type'] ?? 'pickup');
    $pickupField = old('pickup_at');
    if ($pickupField === null && ! empty($wizard['pickup_at'])) {
        try {
            $pickupField = \Carbon\Carbon::parse($wizard['pickup_at'])->format('Y-m-d\TH:i');
        } catch (\Throwable $e) {
            $pickupField = '';
        }
    }
    $pickupField = $pickupField ?? '';
    $delAddr = old('delivery_address', $wizard['delivery_address'] ?? '');
    $delCharge = old('delivery_charge', isset($wizard['delivery_charge']) ? (string) $wizard['delivery_charge'] : '0');
@endphp

<div class="mx-auto max-w-2xl pb-[max(4.25rem,env(safe-area-inset-bottom))] max-md:pb-[max(11rem,env(safe-area-inset-bottom))] md:pb-0">
    @include('vendor.orders.partials.wizard-steps', ['current' => 4, 'compact' => true])

    <form action="{{ route('vendor.orders.create.fulfillment.store') }}" method="POST" class="space-y-4 rounded-xl border border-gray-200/90 bg-white p-3 shadow-sm sm:space-y-5 sm:p-4" x-data="{ fulfillment: @js($ft) }">
        @csrf

        <div>
            <span class="mb-2 block text-sm font-bold text-gray-900">{{ __('vendor.fulfillment_method') }}</span>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-2">
                <label class="flex h-10 cursor-pointer items-center gap-2.5 rounded-lg border px-3 transition [touch-action:manipulation] active:scale-[0.99] sm:h-11 sm:gap-3 sm:px-3.5"
                       :class="fulfillment === 'pickup' ? 'border-emerald-500 bg-emerald-50/80 ring-1 ring-emerald-500/25' : 'border-gray-200 bg-gray-50/50 hover:border-gray-300'">
                    <input type="radio" name="fulfillment_type" value="pickup" class="h-4 w-4 shrink-0 text-emerald-600 focus:ring-emerald-500 sm:h-[18px] sm:w-[18px]" x-model="fulfillment">
                    <span class="text-sm font-semibold text-gray-900 sm:text-base">{{ __('vendor.pickup') }}</span>
                </label>
                <label class="flex h-10 cursor-pointer items-center gap-2.5 rounded-lg border px-3 transition [touch-action:manipulation] active:scale-[0.99] sm:h-11 sm:gap-3 sm:px-3.5"
                       :class="fulfillment === 'delivery' ? 'border-emerald-500 bg-emerald-50/80 ring-1 ring-emerald-500/25' : 'border-gray-200 bg-gray-50/50 hover:border-gray-300'">
                    <input type="radio" name="fulfillment_type" value="delivery" class="h-4 w-4 shrink-0 text-emerald-600 focus:ring-emerald-500 sm:h-[18px] sm:w-[18px]" x-model="fulfillment">
                    <span class="text-sm font-semibold text-gray-900 sm:text-base">{{ __('vendor.delivery') }}</span>
                </label>
            </div>
            @error('fulfillment_type')
                <p class="mt-1.5 text-xs text-red-600 sm:text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="fulfillment === 'pickup'" x-cloak class="space-y-1">
            <label class="block text-xs font-semibold text-gray-800 sm:text-sm">{{ __('vendor.pickup_datetime') }}</label>
            <input type="datetime-local" name="pickup_at" value="{{ $pickupField }}"
                   class="h-10 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm text-gray-900 shadow-inner focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 @error('pickup_at') border-red-500 @enderror">
            @error('pickup_at')
                <p class="mt-0.5 text-xs text-red-600 sm:text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="fulfillment === 'delivery'" x-cloak class="space-y-3">
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-800 sm:text-sm">{{ __('vendor.delivery_address') }} <span class="text-red-500">*</span></label>
                <textarea name="delivery_address" rows="3"
                          class="min-h-[5.5rem] w-full rounded-lg border border-gray-300 bg-white px-2.5 py-2 text-sm text-gray-900 shadow-inner focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 @error('delivery_address') border-red-500 @enderror sm:min-h-[6rem]">{{ $delAddr }}</textarea>
                @error('delivery_address')
                    <p class="mt-0.5 text-xs text-red-600 sm:text-sm">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-800 sm:text-sm">{{ __('vendor.delivery_charge') }}</label>
                <input type="number" name="delivery_charge" step="0.01" min="0" value="{{ $delCharge }}"
                       inputmode="decimal"
                       class="h-10 w-full max-w-none rounded-lg border border-gray-300 bg-white px-2.5 text-sm text-gray-900 shadow-inner focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 @error('delivery_charge') border-red-500 @enderror sm:max-w-xs">
                @error('delivery_charge')
                    <p class="mt-0.5 text-xs text-red-600 sm:text-sm">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if($errors->has('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800 sm:text-sm">{{ $errors->first('error') }}</div>
        @endif

        <x-order-wizard-actions class="border-t border-gray-200 pt-3 sm:pt-3">
            <a href="{{ route('vendor.orders.create.summary') }}"
               class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-blue-700 [touch-action:manipulation] sm:mr-auto">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                {{ __('vendor.back') }}
            </a>
            <button type="submit"
                    class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition [touch-action:manipulation] hover:bg-emerald-700 active:bg-emerald-800 sm:w-auto sm:min-w-[8rem]">
                <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                {{ __('vendor.order_wizard_continue_payment') }}
            </button>
        </x-order-wizard-actions>
    </form>
</div>
@endsection
