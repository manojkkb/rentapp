@php
    $prefix = $prefix ?? 'fulfillment';
    $dateId = $prefix . '_date';
    $timeId = $prefix . '_time_select';
    $hiddenId = $prefix . '_at';
    $parts = $parts ?? ['date' => '', 'time' => ''];
    $hiddenValue = $hiddenValue ?? '';
    $restrictPastDates = $restrictPastDates ?? false;
    $changeEvent = $changeEvent ?? null;
    $label = $label ?? __('vendor.pickup_datetime');
    $help = $help ?? __('vendor.pickup_datetime_help');
    $required = $required ?? false;
@endphp

<div x-data="orderFulfillmentDatetime({
         hiddenId: @js($hiddenId),
         prefillTime: @js($parts['time'] ?? ''),
         restrictPastDates: @json($restrictPastDates),
         changeEvent: @js($changeEvent),
     })"
     @sync-fulfillment-datetimes.window="sync()"
     @remount-fulfillment-datetimes.window="$nextTick(() => mountIfVisible())"
     class="space-y-2">
    <label class="block text-xs font-semibold text-gray-800 sm:text-sm">
        {{ $label }}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
    <input type="hidden" name="{{ $hiddenId }}" id="{{ $hiddenId }}" value="{{ $hiddenValue }}">
    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
        <div>
            <div class="date-input-wrapper">
                <input type="text"
                       id="{{ $dateId }}"
                       x-ref="dateInput"
                       value="{{ $parts['date'] ?? '' }}"
                       readonly
                       class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                       placeholder="{{ __('vendor.select_date') }}">
                <span class="date-icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></span>
            </div>
        </div>
        <div>
            <div class="time-input-wrapper">
                <select id="{{ $timeId }}"
                        x-ref="timeSelect"
                        class="booking-time-select w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 {{ ($parts['time'] ?? '') === '' ? 'is-placeholder' : '' }}"
                        data-placeholder="{{ __('vendor.select_time') }}">
                    <option value="">{{ __('vendor.select_time') }}</option>
                </select>
                <span class="time-icon"><i class="fas fa-clock" aria-hidden="true"></i></span>
            </div>
        </div>
    </div>
    @if($help)
        <p class="text-xs text-gray-500">{{ $help }}</p>
    @endif
</div>
