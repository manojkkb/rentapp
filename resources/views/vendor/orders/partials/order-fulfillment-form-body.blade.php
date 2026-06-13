@php
    $bookingTz = config('app.timezone');
    $parseFulfillmentDateTime = function ($dt) use ($bookingTz) {
        if (! $dt) {
            return ['date' => '', 'time' => '', 'combined' => ''];
        }
        $local = $dt->copy()->timezone($bookingTz);

        return [
            'date' => $local->format('Y-m-d'),
            'time' => $local->format('H:i'),
            'combined' => $local->format('Y-m-d H:i'),
        ];
    };
    $pickupParts = $parseFulfillmentDateTime($order->pickup_at);
    $deliveryParts = $parseFulfillmentDateTime($order->delivery_at);
    $prefix = $prefix ?? 'fulfillment';
@endphp

<div class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3">
    <label class="relative flex min-h-[48px] cursor-pointer rounded-xl border-2 p-3 transition-all"
           :class="fulfillmentType === 'pickup' ? 'border-emerald-500 bg-emerald-50/50 ring-1 ring-emerald-500/30' : 'border-gray-200 hover:border-gray-300 bg-white'">
        <input type="radio"
               name="fulfillment_type_{{ $prefix }}"
               value="pickup"
               x-model="fulfillmentType"
               @change="fulfillmentFieldError = ''"
               class="sr-only">
        <div class="flex w-full items-start gap-2">
            <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2"
                  :class="fulfillmentType === 'pickup' ? 'border-emerald-600 bg-emerald-600' : 'border-gray-300 bg-white'">
                <span class="h-1.5 w-1.5 rounded-full bg-white" x-show="fulfillmentType === 'pickup'"></span>
            </span>
            <div>
                <span class="block text-sm font-bold text-gray-900">{{ __('vendor.pickup') }}</span>
                <span class="block text-xs text-gray-600">{{ __('vendor.pickup_short_help') }}</span>
            </div>
        </div>
    </label>
    <label class="relative flex min-h-[48px] cursor-pointer rounded-xl border-2 p-3 transition-all"
           :class="fulfillmentType === 'delivery' ? 'border-orange-500 bg-orange-50/50 ring-1 ring-orange-500/30' : 'border-gray-200 hover:border-gray-300 bg-white'">
        <input type="radio"
               name="fulfillment_type_{{ $prefix }}"
               value="delivery"
               x-model="fulfillmentType"
               @change="fulfillmentFieldError = ''"
               class="sr-only">
        <div class="flex w-full items-start gap-2">
            <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border-2"
                  :class="fulfillmentType === 'delivery' ? 'border-orange-600 bg-orange-600' : 'border-gray-300 bg-white'">
                <span class="h-1.5 w-1.5 rounded-full bg-white" x-show="fulfillmentType === 'delivery'"></span>
            </span>
            <div>
                <span class="block text-sm font-bold text-gray-900">{{ __('vendor.delivery') }}</span>
                <span class="block text-xs text-gray-600">{{ __('vendor.delivery_short_help') }}</span>
            </div>
        </div>
    </label>
</div>

<div x-show="fulfillmentType === 'pickup'" class="mt-4 space-y-4" x-cloak>
    @include('vendor.orders.partials.order-fulfillment-datetime-fields', [
        'prefix' => $prefix . '_pickup',
        'parts' => $pickupParts,
        'hiddenValue' => $pickupParts['combined'],
        'restrictPastDates' => false,
        'changeEvent' => 'fulfillment-pickup-at-changed',
        'label' => __('vendor.pickup_datetime'),
        'help' => __('vendor.pickup_datetime_help'),
        'required' => true,
    ])
    <div class="space-y-1.5">
        <label for="{{ $prefix }}_pickup_address" class="block text-xs font-semibold text-gray-800 sm:text-sm">{{ __('vendor.delivery_address') }}</label>
        <textarea id="{{ $prefix }}_pickup_address"
                  x-model="deliveryAddress"
                  rows="3"
                  class="w-full resize-y rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/25"
                  placeholder="{{ __('vendor.delivery_address_help') }}"></textarea>
        <p class="text-xs text-gray-500">{{ __('vendor.delivery_address_optional_pickup') }}</p>
    </div>
</div>

<div x-show="fulfillmentType === 'delivery'" class="mt-4 space-y-4" x-cloak>
    <div class="space-y-1.5">
        <label for="{{ $prefix }}_delivery_address" class="block text-xs font-semibold text-gray-800 sm:text-sm">
            {{ __('vendor.delivery_address') }} <span class="text-red-500">*</span>
        </label>
        <textarea id="{{ $prefix }}_delivery_address"
                  x-model="deliveryAddress"
                  @input="fulfillmentFieldError = ''"
                  rows="3"
                  class="w-full resize-y rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/25"
                  :class="fulfillmentFieldError ? 'border-red-400' : ''"
                  placeholder="{{ __('vendor.delivery_address_help') }}"></textarea>
    </div>

    @include('vendor.orders.partials.order-fulfillment-datetime-fields', [
        'prefix' => $prefix . '_delivery',
        'parts' => $deliveryParts,
        'hiddenValue' => $deliveryParts['combined'],
        'restrictPastDates' => false,
        'changeEvent' => 'fulfillment-delivery-at-changed',
        'label' => __('vendor.delivery_datetime'),
        'help' => __('vendor.delivery_datetime_help'),
        'required' => false,
    ])

    <div class="space-y-1.5">
        <label for="{{ $prefix }}_delivery_charge" class="block text-xs font-semibold text-gray-800 sm:text-sm">{{ __('vendor.delivery_charge') }}</label>
        <div class="relative max-w-xs">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₹</span>
            <input type="number"
                   id="{{ $prefix }}_delivery_charge"
                   x-model.number="deliveryCharge"
                   min="0"
                   step="0.01"
                   class="w-full rounded-lg border border-gray-200 bg-white py-2.5 pl-8 pr-3 text-sm text-gray-900 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/25"
                   placeholder="0.00" />
        </div>
        <p class="text-xs text-gray-500">{{ __('vendor.delivery_charge_help') }}</p>
    </div>
</div>

<p x-show="fulfillmentFieldError" class="mt-3 text-sm text-red-600" x-text="fulfillmentFieldError"></p>
