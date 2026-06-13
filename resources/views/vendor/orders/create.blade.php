@extends('vendor.layouts.app')

@section('title', __('vendor.create_order'))
@section('page-title', __('vendor.create_order'))

@section('styles')
<style>
    .flatpickr-calendar { border-radius: 12px !important; box-shadow: 0 10px 40px rgba(0,0,0,.15) !important; border: 1px solid #e5e7eb !important; font-family: 'Inter', sans-serif !important; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #059669 !important; border-color: #059669 !important; }
    .flatpickr-day.booking-in-range {
        background: #d1fae5 !important;
        border-color: #a7f3d0 !important;
        color: #065f46 !important;
        box-shadow: none !important;
    }
    .flatpickr-day.booking-range-start,
    .flatpickr-day.booking-range-end,
    .flatpickr-day.booking-range-preview-end {
        background: #059669 !important;
        border-color: #059669 !important;
        color: #fff !important;
    }
    .flatpickr-day.booking-range-start.booking-in-range,
    .flatpickr-day.booking-range-end.booking-in-range {
        border-radius: 0 !important;
    }
    .flatpickr-day.booking-range-start { border-radius: 50% 0 0 50% !important; }
    .flatpickr-day.booking-range-end,
    .flatpickr-day.booking-range-preview-end { border-radius: 0 50% 50% 0 !important; }
    .flatpickr-day.booking-range-start.booking-range-end { border-radius: 50% !important; }
    .flatpickr-day.today { border-color: #059669 !important; }
    .flatpickr-day:hover { background: #d1fae5 !important; }
    .flatpickr-day.booking-range-start:hover,
    .flatpickr-day.booking-range-end:hover,
    .flatpickr-day.booking-range-preview-end:hover { background: #047857 !important; }
    .flatpickr-months .flatpickr-month { height: 40px !important; }
    .flatpickr-current-month { font-size: 1rem !important; font-weight: 600 !important; }
    .flatpickr-time input { font-size: 1rem !important; }
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
    .booking-time-select.is-placeholder {
        color: #9ca3af;
    }
    .booking-time-select option {
        color: #111827;
    }
    .booking-time-select option[value=""] {
        color: #9ca3af;
    }
    @media (min-width: 640px) {
        .date-input-wrapper input { min-height: 2.5rem; font-size: 0.875rem; }
        .booking-time-select { min-height: 2.5rem; }
    }
    .order-wizard-customer-dropdown { z-index: 40; }
</style>
@endsection

@section('content')
@php
    $wizardPrefill = $wizardPrefill ?? [];
    $selectedCustomerId = old('customer_id', $wizardPrefill['customer_id'] ?? '');
    $selectedCustomer = $selectedCustomerId !== '' && $selectedCustomerId !== null
        ? $customers->firstWhere('id', (int) $selectedCustomerId)
        : null;
@endphp

<div class="mx-auto max-w-2xl pb-[max(4.25rem,env(safe-area-inset-bottom))] max-md:pb-[max(11rem,env(safe-area-inset-bottom))] md:pb-0">
    @include('vendor.orders.partials.wizard-steps', ['current' => 1, 'compact' => true])

    <div class="mb-3 sm:mb-4">
        <h1 class="text-base font-bold leading-tight text-gray-900 sm:text-lg">{{ __('vendor.create_order') }}</h1>
        <p class="mt-1 max-md:line-clamp-3 text-xs leading-snug text-gray-600 sm:text-sm">{{ __('vendor.create_order_direct_subtitle') }}</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100">
        <form id="orderWizard_step1_form" action="{{ route('vendor.orders.create.step1') }}" method="POST" class="p-3 sm:p-4">
            @csrf

            <div class="mb-4 sm:mb-5">
                <label for="orderWizard_customerSearch" class="mb-1.5 block text-sm font-semibold text-gray-800">
                    {{ __('vendor.select') }} {{ __('vendor.customer') }} <span class="text-red-500">*</span>
                </label>
                <input type="hidden" name="customer_id" id="orderWizard_customerId" value="{{ old('customer_id', $wizardPrefill['customer_id'] ?? '') }}">
                <div class="relative" id="orderWizard_customerSearchWrap">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-search text-sm text-gray-400"></i>
                    </div>
                    <input type="text"
                           id="orderWizard_customerSearch"
                           class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-10 pr-10 text-sm text-gray-900 placeholder:text-gray-400 focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('customer_id') border-red-500 @enderror"
                           placeholder="{{ __('vendor.order_wizard_search_customer_placeholder') }}"
                           autocomplete="off"
                           value="{{ $selectedCustomer ? $selectedCustomer->name.' — '.$selectedCustomer->mobile : '' }}">
                    <button type="button" id="orderWizard_customerClear" class="{{ $selectedCustomer ? 'flex' : 'hidden' }} absolute inset-y-0 right-0 items-center pr-3 text-gray-400 hover:text-gray-600" aria-label="{{ __('vendor.clear') }}">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                    <div id="orderWizard_customerDropdown" class="order-wizard-customer-dropdown absolute z-40 mt-1 hidden max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg">
                        <button type="button" id="orderWizard_addCustomerBtn" class="sticky top-0 flex w-full items-center gap-2 border-b border-gray-100 bg-white px-3 py-2 text-left text-xs font-semibold text-emerald-700 hover:bg-emerald-50 sm:text-sm">
                            <i class="fas fa-plus-circle text-xs" aria-hidden="true"></i>
                            {{ __('vendor.order_wizard_add_customer_inline') }}
                        </button>
                        <div id="orderWizard_customerList">
                            @foreach($customers as $customer)
                                <div class="order-wizard-customer-option flex cursor-pointer items-center justify-between px-3 py-2 transition-colors hover:bg-emerald-50"
                                     role="option"
                                     data-id="{{ $customer->id }}"
                                     data-name="{{ $customer->name }}"
                                     data-mobile="{{ $customer->mobile }}">
                                    <div>
                                        <span class="text-sm font-medium text-gray-900">{{ $customer->name }}</span>
                                        <span class="ml-2 text-xs text-gray-500">{{ $customer->mobile }}</span>
                                    </div>
                                    <i class="fas fa-check check-icon hidden text-emerald-500" aria-hidden="true"></i>
                                </div>
                            @endforeach
                        </div>
                        <div id="orderWizard_customerNoResults" class="hidden px-3 py-2 text-center text-xs text-gray-500 sm:text-sm">
                            <i class="fas fa-search mr-1" aria-hidden="true"></i>{{ __('vendor.order_wizard_no_customers_match') }}
                        </div>
                    </div>
                </div>
                <p id="orderWizard_customerFormError" class="mt-1.5 hidden text-xs text-red-600 sm:text-sm"></p>
                @error('customer_id')
                    <p class="mt-1.5 flex items-center text-xs text-red-600 sm:text-sm">
                        <i class="fas fa-exclamation-circle mr-1 text-xs"></i>
                        {{ $message }}
                    </p>
                @enderror

                <div id="orderWizard_addCustomerInline" class="mt-3 hidden rounded-lg border border-emerald-200 bg-emerald-50/80 p-3 sm:p-3.5">
                    <div class="mb-2 flex items-center justify-between">
                        <h4 class="text-xs font-bold text-emerald-900 sm:text-sm">
                            <i class="fas fa-user-plus mr-1 text-xs" aria-hidden="true"></i>{{ __('vendor.order_wizard_new_customer_heading') }}
                        </h4>
                        <button type="button" id="orderWizard_closeAddCustomer" class="rounded p-1 text-gray-500 hover:bg-white/80 hover:text-gray-700" aria-label="{{ __('vendor.cancel') }}">
                            <i class="fas fa-times text-sm"></i>
                        </button>
                    </div>
                    <div class="mb-2 grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3">
                        <div>
                            <label for="orderWizard_newCustomerName" class="mb-0.5 block text-[11px] font-medium text-gray-600 sm:text-xs">{{ __('vendor.customer_name') }} <span class="text-red-500">*</span></label>
                            <input type="text" id="orderWizard_newCustomerName" maxlength="255" class="h-9 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 sm:h-10" placeholder="{{ __('vendor.customer_name') }}">
                        </div>
                        <div>
                            <label for="orderWizard_newCustomerMobile" class="mb-0.5 block text-[11px] font-medium text-gray-600 sm:text-xs">{{ __('vendor.mobile') }} <span class="text-red-500">*</span></label>
                            <input type="text" id="orderWizard_newCustomerMobile" maxlength="10" inputmode="numeric" class="h-9 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 sm:h-10" placeholder="{{ __('vendor.customer_mobile') }}">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="orderWizard_newCustomerAddress" class="mb-0.5 block text-[11px] font-medium text-gray-600 sm:text-xs">{{ __('vendor.address') }}</label>
                        <input type="text" id="orderWizard_newCustomerAddress" maxlength="500" class="h-9 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 sm:h-10" placeholder="{{ __('vendor.optional') }}">
                    </div>
                    <p id="orderWizard_addCustomerError" class="mb-2 hidden text-xs text-red-600"></p>
                    <button type="button" id="orderWizard_saveNewCustomer" class="h-10 w-full rounded-lg bg-emerald-600 px-3 text-sm font-semibold text-white transition hover:bg-emerald-700 active:scale-[0.99]">
                        <i class="fas fa-save mr-1 text-xs" aria-hidden="true"></i>{{ __('vendor.order_wizard_save_and_select_customer') }}
                    </button>
                </div>

                @if($customers->count() === 0)
                    <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-2.5 sm:p-3">
                        <p class="text-xs text-amber-900 sm:text-sm">
                            <i class="fas fa-info-circle mr-1 text-xs" aria-hidden="true"></i>
                            {{ __('vendor.no_customers') }}. {{ __('vendor.order_wizard_add_customer_inline') }}.
                        </p>
                    </div>
                @endif
            </div>

            <div class="mb-4 sm:mb-5">
                <label for="event_name" class="mb-1.5 block text-sm font-semibold text-gray-800">
                    {{ __('vendor.event_name') }} <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-calendar-check text-sm text-gray-400"></i>
                    </div>
                    <input type="text"
                           name="event_name"
                           id="event_name"
                           value="{{ old('event_name', old('cart_name', $wizardPrefill['event_name'] ?? '')) }}"
                           class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3 text-sm text-gray-900 transition focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('event_name') border-red-500 @enderror @error('cart_name') border-red-500 @enderror"
                           placeholder="{{ __('vendor.cart_name_placeholder') }}"
                           required
                           maxlength="255">
                </div>
                @error('event_name')
                    <p class="mt-1.5 flex items-center text-xs text-red-600 sm:text-sm">
                        <i class="fas fa-exclamation-circle mr-1 text-xs"></i>
                        {{ $message }}
                    </p>
                @enderror
                @error('cart_name')
                    <p class="mt-1.5 flex items-center text-xs text-red-600 sm:text-sm">
                        <i class="fas fa-exclamation-circle mr-1 text-xs"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            @php
                $parseBookingDateTime = function (?string $value): array {
                    if (! $value) {
                        return ['date' => '', 'time' => ''];
                    }
                    try {
                        $dt = \Carbon\Carbon::parse($value);

                        return ['date' => $dt->format('Y-m-d'), 'time' => $dt->format('H:i')];
                    } catch (\Throwable) {
                        return ['date' => '', 'time' => ''];
                    }
                };
                $startParts = $parseBookingDateTime(old('start_time', $wizardPrefill['start_time'] ?? null));
                $endParts = $parseBookingDateTime(old('end_time', $wizardPrefill['end_time'] ?? null));
            @endphp

            <div class="mb-4 sm:mb-5">
                <label class="mb-2 block text-sm font-semibold text-gray-800">
                    <i class="fas fa-calendar mr-1 text-emerald-600"></i>
                    {{ __('vendor.booking_dates') }} <span class="text-red-500">*</span>
                </label>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-4">
                    <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-emerald-800 sm:text-sm">{{ __('vendor.start_date_time') }}</p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div>
                                <div class="date-input-wrapper">
                                    <input type="text"
                                           id="start_date"
                                           value="{{ $startParts['date'] }}"
                                           readonly
                                           class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('start_time') border-red-500 @enderror"
                                           placeholder="{{ __('vendor.select_date') }}">
                                    <span class="date-icon"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                            </div>
                            <div>
                                <div class="time-input-wrapper">
                                    <select id="start_time_select"
                                            class="booking-time-select w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('start_time') border-red-500 @enderror {{ $startParts['time'] === '' ? 'is-placeholder' : '' }}"
                                            data-placeholder="{{ __('vendor.select_time') }}">
                                        <option value="">{{ __('vendor.select_time') }}</option>
                                    </select>
                                    <span class="time-icon"><i class="fas fa-clock"></i></span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="start_time" id="start_time" value="{{ old('start_time', $wizardPrefill['start_time'] ?? '') }}">
                        @error('start_time')
                            <p class="flex items-center text-xs text-red-600 sm:text-sm">
                                <i class="fas fa-exclamation-circle mr-1 text-xs"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-emerald-800 sm:text-sm">{{ __('vendor.end_date_time') }}</p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div>
                                <div class="date-input-wrapper">
                                    <input type="text"
                                           id="end_date"
                                           value="{{ $endParts['date'] }}"
                                           readonly
                                           class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('end_time') border-red-500 @enderror"
                                           placeholder="{{ __('vendor.select_date') }}">
                                    <span class="date-icon"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                            </div>
                            <div>
                                <div class="time-input-wrapper">
                                    <select id="end_time_select"
                                            class="booking-time-select w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('end_time') border-red-500 @enderror {{ $endParts['time'] === '' ? 'is-placeholder' : '' }}"
                                            data-placeholder="{{ __('vendor.select_time') }}">
                                        <option value="">{{ __('vendor.select_time') }}</option>
                                    </select>
                                    <span class="time-icon"><i class="fas fa-clock"></i></span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="end_time" id="end_time" value="{{ old('end_time', $wizardPrefill['end_time'] ?? '') }}">
                        @error('end_time')
                            <p class="flex items-center text-xs text-red-600 sm:text-sm">
                                <i class="fas fa-exclamation-circle mr-1 text-xs"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50/90 p-2.5 sm:p-3">
                <div class="flex items-start gap-2">
                    <i class="fas fa-info-circle mt-0.5 shrink-0 text-sm text-emerald-600"></i>
                    <p class="text-xs leading-snug text-emerald-900 sm:text-sm">{{ __('vendor.order_wizard_step1_footer') }}</p>
                </div>
            </div>

            <x-order-wizard-actions class="border-t border-gray-200 pt-3 sm:pt-3">
                <a href="{{ route('vendor.orders.index') }}"
                   class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-emerald-700 [touch-action:manipulation] sm:mr-auto">
                    <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                    {{ __('vendor.back') }}
                </a>
                <button type="submit"
                        class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition [touch-action:manipulation] hover:bg-emerald-700 active:bg-emerald-800 sm:w-auto sm:min-w-[8rem]">
                    <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                    {{ __('vendor.order_wizard_continue_items') }}
                </button>
            </x-order-wizard-actions>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@vite(['resources/js/order-wizard-datetime.js'])
<script>
document.addEventListener('DOMContentLoaded', function() {
    const OWD = window.OrderWizardDateTime;
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const startTimeSelect = document.getElementById('start_time_select');
    const endTimeSelect = document.getElementById('end_time_select');
    const startTimeHidden = document.getElementById('start_time');
    const endTimeHidden = document.getElementById('end_time');
    const wizardForm = document.getElementById('orderWizard_step1_form');

    const prefillStartTime = @json($startParts['time']);
    const prefillEndTime = @json($endParts['time']);
    const safeStartDate = OWD.sanitizeDateStr(startDateInput?.value || '');
    const safeEndDate = OWD.sanitizeDateStr(endDateInput?.value || '');
    if (startDateInput) startDateInput.value = safeStartDate;
    if (endDateInput) endDateInput.value = safeEndDate;

    function syncHiddenDatetime(dateStr, timeStr, hiddenInput) {
        if (!hiddenInput) return;
        hiddenInput.value = OWD.combineDateTime(dateStr, timeStr);
    }

    function refreshStartTimes() {
        OWD.buildTimeOptions(startTimeSelect, startTimeSelect?.value || prefillStartTime, {
            dateStr: startPicker?.input?.value || safeStartDate,
        });
        syncHiddenDatetime(startPicker?.input?.value || '', startTimeSelect?.value || '', startTimeHidden);
    }

    function refreshEndTimes() {
        const endDate = endPicker?.input?.value || '';
        const startDate = startPicker?.input?.value || '';
        const startTime = startTimeSelect?.value || '';
        let minDateTime = null;
        if (endDate && startDate && endDate === startDate && startTime) {
            minDateTime = OWD.parseDateTime(startDate, startTime);
        }
        OWD.buildTimeOptions(endTimeSelect, endTimeSelect?.value || prefillEndTime, {
            dateStr: endDate,
            minDateTime,
            strictlyAfter: !!minDateTime,
        });
        syncHiddenDatetime(endDate, endTimeSelect?.value || '', endTimeHidden);
    }

    function openTimeSelect(selectEl) {
        if (!selectEl) return;
        requestAnimationFrame(function () {
            selectEl.focus();
            if (typeof selectEl.showPicker === 'function') {
                try { selectEl.showPicker(); } catch (e) { /* unsupported browser */ }
            }
        });
    }

    function openDatePicker(picker) {
        if (!picker || typeof picker.open !== 'function') return;
        requestAnimationFrame(function () {
            picker.open();
        });
    }

    let hoverEndDate = null;
    let startPicker;
    let endPicker;

    function dayStart(date) {
        return new Date(date.getFullYear(), date.getMonth(), date.getDate());
    }

    function isEndPicker(fp) {
        return fp?.element?.id === 'end_date';
    }

    function getBookingRange() {
        const startRaw = startPicker?.selectedDates?.[0];
        const endRaw = endPicker?.selectedDates?.[0];
        const start = startRaw ? dayStart(startRaw) : null;
        const end = endRaw ? dayStart(endRaw) : null;

        return { start, end };
    }

    function decorateBookingDay(dayElem, date, fp) {
        if (!date || Number.isNaN(date.getTime())) return;

        const { start, end } = getBookingRange();
        const day = dayStart(date);

        dayElem.classList.remove(
            'booking-in-range',
            'booking-range-start',
            'booking-range-end',
            'booking-range-preview-end'
        );

        let rangeEnd = end;
        if (!rangeEnd && isEndPicker(fp) && hoverEndDate && start) {
            rangeEnd = dayStart(hoverEndDate);
            if (rangeEnd < start) {
                rangeEnd = start;
            }
        }

        if (start && rangeEnd) {
            if (day >= start && day <= rangeEnd) {
                dayElem.classList.add('booking-in-range');
            }
            if (day.getTime() === start.getTime()) {
                dayElem.classList.add('booking-range-start');
            }
            if (end && day.getTime() === end.getTime()) {
                dayElem.classList.add('booking-range-end');
            } else if (!end && hoverEndDate && isEndPicker(fp) && day.getTime() === rangeEnd.getTime()) {
                dayElem.classList.add('booking-range-preview-end');
            }
        } else if (start && day.getTime() === start.getTime()) {
            dayElem.classList.add('booking-range-start', 'booking-in-range');
        }
    }

    function refreshRangeHighlight(fp) {
        if (!fp?.calendarContainer) return;
        fp.calendarContainer.querySelectorAll('.flatpickr-day').forEach(function (dayEl) {
            if (dayEl.dateObj) {
                decorateBookingDay(dayEl, dayEl.dateObj, fp);
            }
        });
    }

    function bindEndPickerHover() {
        if (!endPicker?.calendarContainer || endPicker.calendarContainer.dataset.rangeHoverBound) {
            return;
        }
        endPicker.calendarContainer.dataset.rangeHoverBound = '1';
        endPicker.calendarContainer.addEventListener('mouseover', function (e) {
            const dayEl = e.target.closest('.flatpickr-day:not(.flatpickr-disabled)');
            if (!dayEl?.dateObj) return;
            hoverEndDate = dayEl.dateObj;
            refreshRangeHighlight(endPicker);
        });
        endPicker.calendarContainer.addEventListener('mouseleave', function () {
            hoverEndDate = null;
            refreshRangeHighlight(endPicker);
        });
    }

    const bookingRangeHooks = {
        onDayCreate: function (_dObj, _dStr, fp, dayElem) {
            if (dayElem.dateObj) {
                decorateBookingDay(dayElem, dayElem.dateObj, fp);
            }
        },
        onOpen: function (_selectedDates, _dateStr, fp) {
            if (isEndPicker(fp)) {
                bindEndPickerHover();
            }
            refreshRangeHighlight(fp);
        },
        onMonthChange: function (_selectedDates, _dateStr, fp) {
            refreshRangeHighlight(fp);
        },
        onYearChange: function (_selectedDates, _dateStr, fp) {
            refreshRangeHighlight(fp);
        },
    };

    function afterBookingRangeChange() {
        if (startPicker) refreshRangeHighlight(startPicker);
        if (endPicker) refreshRangeHighlight(endPicker);
    }

    const fpDateConfig = OWD.flatpickrDateConfig();

    startPicker = flatpickr('#start_date', {
        ...fpDateConfig,
        ...bookingRangeHooks,
        defaultDate: safeStartDate || null,
        onChange: function (selectedDates, dateStr) {
            if (selectedDates?.length > 0) {
                refreshStartTimes();
                if (endPicker) {
                    endPicker.set('minDate', selectedDates[0]);
                }
                openTimeSelect(startTimeSelect);
            } else {
                syncHiddenDatetime('', '', startTimeHidden);
                if (endPicker) endPicker.set('minDate', 'today');
            }
            refreshEndTimes();
            afterBookingRangeChange();
        },
    });

    endPicker = flatpickr('#end_date', {
        ...fpDateConfig,
        ...bookingRangeHooks,
        defaultDate: safeEndDate || null,
        onReady: function () {
            bindEndPickerHover();
            afterBookingRangeChange();
        },
        onChange: function (selectedDates, dateStr) {
            hoverEndDate = null;
            if (selectedDates?.length > 0) {
                refreshEndTimes();
                if (startPicker) startPicker.set('maxDate', selectedDates[0]);
                openTimeSelect(endTimeSelect);
            } else {
                syncHiddenDatetime('', '', endTimeHidden);
                if (startPicker) startPicker.set('maxDate', null);
            }
            afterBookingRangeChange();
        },
    });

    startTimeSelect.addEventListener('change', function () {
        syncHiddenDatetime(startPicker.input.value, startTimeSelect.value, startTimeHidden);
        refreshEndTimes();
        if (startTimeSelect.value) {
            openDatePicker(endPicker);
        }
    });

    endTimeSelect.addEventListener('change', function () {
        syncHiddenDatetime(endPicker.input.value, endTimeSelect.value, endTimeHidden);
    });

    if (wizardForm) {
        wizardForm.addEventListener('submit', function () {
            syncHiddenDatetime(startPicker.input.value, startTimeSelect.value, startTimeHidden);
            syncHiddenDatetime(endPicker.input.value, endTimeSelect.value, endTimeHidden);
        });
    }

    if (startPicker?.selectedDates?.length > 0) {
        endPicker?.set('minDate', startPicker.selectedDates[0]);
    } else {
        endPicker?.set('minDate', 'today');
    }
    if (endPicker?.selectedDates?.length > 0) {
        startPicker?.set('maxDate', endPicker.selectedDates[0]);
    }
    refreshStartTimes();
    refreshEndTimes();
    afterBookingRangeChange();

    (function orderWizardCustomerPicker() {
        const wrap = document.getElementById('orderWizard_customerSearchWrap');
        const searchInput = document.getElementById('orderWizard_customerSearch');
        const hiddenId = document.getElementById('orderWizard_customerId');
        const dropdown = document.getElementById('orderWizard_customerDropdown');
        const list = document.getElementById('orderWizard_customerList');
        const noResults = document.getElementById('orderWizard_customerNoResults');
        const clearBtn = document.getElementById('orderWizard_customerClear');
        const addBtn = document.getElementById('orderWizard_addCustomerBtn');
        const inline = document.getElementById('orderWizard_addCustomerInline');
        const closeInline = document.getElementById('orderWizard_closeAddCustomer');
        const saveBtn = document.getElementById('orderWizard_saveNewCustomer');
        const errInline = document.getElementById('orderWizard_addCustomerError');
        const errForm = document.getElementById('orderWizard_customerFormError');
        const form = document.getElementById('orderWizard_step1_form');
        if (!wrap || !searchInput || !hiddenId || !dropdown || !list || !form) return;

        const storeUrl = @json(route('vendor.customers.store'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function showDropdown() {
            dropdown.classList.remove('hidden');
        }

        function hideDropdown() {
            dropdown.classList.add('hidden');
        }

        function filterList() {
            const q = searchInput.value.toLowerCase().trim();
            const opts = list.querySelectorAll('.order-wizard-customer-option');
            let visible = 0;
            opts.forEach(function (opt) {
                const name = (opt.dataset.name || '').toLowerCase();
                const mobile = (opt.dataset.mobile || '').toLowerCase();
                const match = !q || name.includes(q) || mobile.includes(q);
                opt.classList.toggle('hidden', !match);
                if (match) visible += 1;
            });
            noResults.classList.toggle('hidden', visible > 0 || q === '');
        }

        function selectOption(el) {
            hiddenId.value = el.dataset.id;
            searchInput.value = (el.dataset.name || '') + ' — ' + (el.dataset.mobile || '');
            clearBtn.classList.remove('hidden');
            clearBtn.classList.add('flex');
            hideDropdown();
            list.querySelectorAll('.check-icon').forEach(function (i) { i.classList.add('hidden'); });
            const check = el.querySelector('.check-icon');
            if (check) check.classList.remove('hidden');
            errForm.classList.add('hidden');
        }

        function clearSelection() {
            hiddenId.value = '';
            searchInput.value = '';
            clearBtn.classList.add('hidden');
            clearBtn.classList.remove('flex');
            list.querySelectorAll('.check-icon').forEach(function (i) { i.classList.add('hidden'); });
        }

        list.addEventListener('click', function (e) {
            const el = e.target.closest('.order-wizard-customer-option');
            if (el && !el.classList.contains('hidden')) selectOption(el);
        });

        searchInput.addEventListener('focus', function () {
            showDropdown();
            filterList();
        });

        searchInput.addEventListener('input', function () {
            showDropdown();
            filterList();
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) hideDropdown();
        });

        clearBtn.addEventListener('click', function () {
            clearSelection();
            searchInput.focus();
        });

        addBtn.addEventListener('click', function () {
            hideDropdown();
            inline.classList.remove('hidden');
            const q = searchInput.value.trim();
            document.getElementById('orderWizard_newCustomerName').value = /^\d+$/.test(q) ? '' : q;
            document.getElementById('orderWizard_newCustomerMobile').value = /^\d{10}$/.test(q) ? q : '';
            document.getElementById('orderWizard_newCustomerAddress').value = '';
            errInline.classList.add('hidden');
            document.getElementById('orderWizard_newCustomerName').focus();
        });

        closeInline.addEventListener('click', function () {
            inline.classList.add('hidden');
            errInline.classList.add('hidden');
        });

        saveBtn.addEventListener('click', function () {
            const name = document.getElementById('orderWizard_newCustomerName').value.trim();
            const mobile = document.getElementById('orderWizard_newCustomerMobile').value.trim();
            const address = document.getElementById('orderWizard_newCustomerAddress').value.trim();
            errInline.classList.add('hidden');
            if (!name || !mobile) {
                errInline.textContent = @json(__('vendor.order_wizard_new_customer_name_mobile_required'));
                errInline.classList.remove('hidden');
                return;
            }
            if (!/^\d{10}$/.test(mobile)) {
                errInline.textContent = @json(__('vendor.order_wizard_mobile_10_digits'));
                errInline.classList.remove('hidden');
                return;
            }
            saveBtn.disabled = true;
            const prevHtml = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>';
            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ name: name, mobile: mobile, address: address || null }),
            })
                .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, status: r.status, data: data }; }); })
                .then(function (res) {
                    if (res.ok && res.data.success && res.data.customer) {
                        const c = res.data.customer;
                        const row = document.createElement('div');
                        row.className = 'order-wizard-customer-option flex cursor-pointer items-center justify-between px-3 py-2 transition-colors hover:bg-emerald-50';
                        row.setAttribute('role', 'option');
                        row.dataset.id = String(c.id);
                        row.dataset.name = c.name;
                        row.dataset.mobile = c.mobile;
                        row.innerHTML = '<div><span class="text-sm font-medium text-gray-900"></span><span class="ml-2 text-xs text-gray-500"></span></div><i class="fas fa-check check-icon hidden text-emerald-500" aria-hidden="true"></i>';
                        row.querySelector('span.text-sm').textContent = c.name;
                        row.querySelector('span.text-xs').textContent = c.mobile;
                        list.insertBefore(row, list.firstChild);
                        selectOption(row);
                        inline.classList.add('hidden');
                        if (typeof showToast === 'function') {
                            showToast(res.data.message || @json(__('vendor.customer_added')), 'success');
                        }
                        return;
                    }
                    let msg = res.data.message || @json(__('vendor.order_wizard_customer_create_failed'));
                    if (res.data.errors) {
                        const first = Object.values(res.data.errors)[0];
                        if (first && first[0]) msg = first[0];
                    }
                    errInline.textContent = msg;
                    errInline.classList.remove('hidden');
                })
                .catch(function () {
                    errInline.textContent = @json(__('vendor.order_wizard_customer_create_failed'));
                    errInline.classList.remove('hidden');
                })
                .finally(function () {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = prevHtml;
                });
        });

        form.addEventListener('submit', function (e) {
            errForm.classList.add('hidden');
            if (!hiddenId.value || String(hiddenId.value).trim() === '') {
                e.preventDefault();
                errForm.textContent = @json(__('vendor.order_wizard_select_customer_required'));
                errForm.classList.remove('hidden');
                searchInput.focus();
                showDropdown();
                filterList();
            }
        });
    })();
});
</script>
@endsection
