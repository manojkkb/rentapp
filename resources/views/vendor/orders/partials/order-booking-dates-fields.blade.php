@php
    $prefix = $prefix ?? 'edit';
    $startDateId = $prefix . '_start_date';
    $endDateId = $prefix . '_end_date';
    $startTimeId = $prefix . '_start_time_select';
    $endTimeId = $prefix . '_end_time_select';
    $startAtHiddenId = $prefix . '_start_at';
    $endAtHiddenId = $prefix . '_end_at';
    $restrictPastDates = $restrictPastDates ?? false;
    $startErrorId = $startErrorId ?? ($prefix . 'StartTimeError');
    $endErrorId = $endErrorId ?? ($prefix . 'EndTimeError');
@endphp

<input type="hidden" name="start_at" id="{{ $startAtHiddenId }}" value="{{ $startAtValue ?? '' }}">
<input type="hidden" name="end_at" id="{{ $endAtHiddenId }}" value="{{ $endAtValue ?? '' }}">

<div x-data="orderBookingDates({
         startDateValue: @js($startParts['date'] ?? ''),
         endDateValue: @js($endParts['date'] ?? ''),
         prefillStartTime: @js($startParts['time'] ?? ''),
         prefillEndTime: @js($endParts['time'] ?? ''),
         endDateInputId: @js($endDateId),
         restrictPastDates: @json($restrictPastDates),
         startAtHiddenId: @js($startAtHiddenId),
         endAtHiddenId: @js($endAtHiddenId),
     })"
     x-init="init()"
     @sync-booking-dates.window="syncCombined()">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/60 p-3">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-800">{{ __('vendor.start_date_time') }}</p>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <div>
                    <div class="date-input-wrapper">
                        <input type="text"
                               id="{{ $startDateId }}"
                               x-ref="startDate"
                               readonly
                               class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                               placeholder="{{ __('vendor.select_date') }}">
                        <span class="date-icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></span>
                    </div>
                </div>
                <div>
                    <div class="time-input-wrapper">
                        <select id="{{ $startTimeId }}"
                                x-ref="startTimeSelect"
                                class="booking-time-select w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                                data-placeholder="{{ __('vendor.select_time') }}">
                            <option value="">{{ __('vendor.select_time') }}</option>
                        </select>
                        <span class="time-icon"><i class="fas fa-clock" aria-hidden="true"></i></span>
                    </div>
                </div>
            </div>
            <p id="{{ $startErrorId }}" class="mt-1 text-xs text-red-600 hidden"></p>
        </div>

        <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/60 p-3">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-800">{{ __('vendor.end_date_time') }}</p>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <div>
                    <div class="date-input-wrapper">
                        <input type="text"
                               id="{{ $endDateId }}"
                               x-ref="endDate"
                               readonly
                               class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                               placeholder="{{ __('vendor.select_date') }}">
                        <span class="date-icon"><i class="fas fa-calendar-alt" aria-hidden="true"></i></span>
                    </div>
                </div>
                <div>
                    <div class="time-input-wrapper">
                        <select id="{{ $endTimeId }}"
                                x-ref="endTimeSelect"
                                class="booking-time-select w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                                data-placeholder="{{ __('vendor.select_time') }}">
                            <option value="">{{ __('vendor.select_time') }}</option>
                        </select>
                        <span class="time-icon"><i class="fas fa-clock" aria-hidden="true"></i></span>
                    </div>
                </div>
            </div>
            <p id="{{ $endErrorId }}" class="mt-1 text-xs text-red-600 hidden"></p>
        </div>
    </div>
</div>
