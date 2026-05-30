@extends('vendor.layouts.app')

@section('title', __('vendor.create_order'))
@section('page-title', __('vendor.create_order'))

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
<style>
    .flatpickr-calendar { border-radius: 12px !important; box-shadow: 0 10px 40px rgba(0,0,0,.15) !important; border: 1px solid #e5e7eb !important; font-family: 'Inter', sans-serif !important; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #059669 !important; border-color: #059669 !important; }
    .flatpickr-day.today { border-color: #059669 !important; }
    .flatpickr-day:hover { background: #d1fae5 !important; }
    .flatpickr-months .flatpickr-month { height: 40px !important; }
    .flatpickr-current-month { font-size: 1rem !important; font-weight: 600 !important; }
    .flatpickr-time input { font-size: 1rem !important; }
    .date-input-wrapper { position: relative; }
    .date-input-wrapper .date-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 14px; }
    .date-input-wrapper input { padding-right: 32px; min-height: 40px; font-size: 0.875rem; }
    @media (min-width: 640px) {
        .date-input-wrapper input { min-height: 2.5rem; font-size: 0.875rem; }
    }
    .date-clear-btn { position: absolute; right: 28px; top: 50%; transform: translateY(-50%); color: #9ca3af; cursor: pointer; font-size: 12px; padding: 2px 4px; display: none; }
    .date-clear-btn:hover { color: #ef4444; }
    .date-input-wrapper input:not([value=""]) ~ .date-clear-btn,
    .date-input-wrapper input.has-value ~ .date-clear-btn { display: block; }
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
                           class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-10 pr-10 text-sm text-gray-900 shadow-inner placeholder:text-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 @error('customer_id') border-red-500 @enderror"
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
                            <input type="text" id="orderWizard_newCustomerName" maxlength="255" class="h-9 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 sm:h-10" placeholder="{{ __('vendor.customer_name') }}">
                        </div>
                        <div>
                            <label for="orderWizard_newCustomerMobile" class="mb-0.5 block text-[11px] font-medium text-gray-600 sm:text-xs">{{ __('vendor.mobile') }} <span class="text-red-500">*</span></label>
                            <input type="text" id="orderWizard_newCustomerMobile" maxlength="10" inputmode="numeric" class="h-9 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 sm:h-10" placeholder="{{ __('vendor.customer_mobile') }}">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="orderWizard_newCustomerAddress" class="mb-0.5 block text-[11px] font-medium text-gray-600 sm:text-xs">{{ __('vendor.address') }}</label>
                        <input type="text" id="orderWizard_newCustomerAddress" maxlength="500" class="h-9 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 sm:h-10" placeholder="{{ __('vendor.optional') }}">
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
                           class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3 text-sm text-gray-900 shadow-inner transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 @error('event_name') border-red-500 @enderror @error('cart_name') border-red-500 @enderror"
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

            <div class="mb-4 sm:mb-5">
                <label class="mb-2 block text-sm font-semibold text-gray-800">
                    <i class="fas fa-calendar mr-1 text-emerald-600"></i>
                    {{ __('vendor.booking_dates') }} <span class="text-red-500">*</span>
                </label>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-3">
                    <div>
                        <label for="start_time" class="mb-1 block text-xs font-semibold text-gray-700 sm:text-sm">
                            {{ __('vendor.start_date_time') }}
                        </label>
                        <div class="date-input-wrapper">
                            <input type="text"
                                   name="start_time"
                                   id="start_time"
                                   value="{{ old('start_time', $wizardPrefill['start_time'] ?? '') }}"
                                   readonly
                                   required
                                   class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('start_time') border-red-500 @enderror"
                                   placeholder="{{ __('vendor.start_date_time') }}">
                            <span class="date-clear-btn" onclick="clearDate('start')" title="{{ __('vendor.cancel') }}">
                                <i class="fas fa-times"></i>
                            </span>
                            <span class="date-icon"><i class="fas fa-chevron-down"></i></span>
                        </div>
                        @error('start_time')
                            <p class="mt-1.5 flex items-center text-xs text-red-600 sm:text-sm">
                                <i class="fas fa-exclamation-circle mr-1 text-xs"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_time" class="mb-1 block text-xs font-semibold text-gray-700 sm:text-sm">
                            {{ __('vendor.end_date_time') }}
                        </label>
                        <div class="date-input-wrapper">
                            <input type="text"
                                   name="end_time"
                                   id="end_time"
                                   value="{{ old('end_time', $wizardPrefill['end_time'] ?? '') }}"
                                   readonly
                                   required
                                   class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('end_time') border-red-500 @enderror"
                                   placeholder="{{ __('vendor.end_date_time') }}">
                            <span class="date-clear-btn" onclick="clearDate('end')" title="{{ __('vendor.cancel') }}">
                                <i class="fas fa-times"></i>
                            </span>
                            <span class="date-icon"><i class="fas fa-chevron-down"></i></span>
                        </div>
                        @error('end_time')
                            <p class="mt-1.5 flex items-center text-xs text-red-600 sm:text-sm">
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
                   class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-blue-700 [touch-action:manipulation] sm:mr-auto">
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function toggleClearBtn(instance) {
        const wrapper = instance.element.closest('.date-input-wrapper');
        if (!wrapper) return;
        const clearBtn = wrapper.querySelector('.date-clear-btn');
        if (clearBtn) {
            clearBtn.style.display = instance.selectedDates.length > 0 ? 'block' : 'none';
        }
    }

    const fpConfig = {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'M j, Y h:i K',
        time_24hr: false,
        allowInput: false,
        disableMobile: false,
        monthSelectorType: 'dropdown',
        animate: true,
        onReady: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
        },
        onChange: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
        }
    };

    const startPicker = flatpickr('#start_time', {
        ...fpConfig,
        onChange: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
            if (selectedDates.length > 0) {
                endPicker.set('minDate', selectedDates[0]);
            } else {
                endPicker.set('minDate', null);
            }
        }
    });

    const endPicker = flatpickr('#end_time', {
        ...fpConfig,
        onChange: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
            if (selectedDates.length > 0) {
                startPicker.set('maxDate', selectedDates[0]);
            } else {
                startPicker.set('maxDate', null);
            }
        }
    });

    window.clearDate = function(which) {
        if (which === 'start') {
            startPicker.clear();
            endPicker.set('minDate', null);
        } else {
            endPicker.clear();
            startPicker.set('maxDate', null);
        }
        toggleClearBtn(which === 'start' ? startPicker : endPicker);
    };

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
