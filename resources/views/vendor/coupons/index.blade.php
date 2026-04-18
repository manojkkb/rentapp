@extends('vendor.layouts.app')

@section('title', __('vendor.coupons'))
@section('page-title', __('vendor.coupons'))

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
    .date-input-wrapper input { padding-right: 32px; }
    .date-clear-btn { position: absolute; right: 28px; top: 50%; transform: translateY(-50%); color: #9ca3af; cursor: pointer; font-size: 12px; padding: 2px 4px; display: none; }
    .date-clear-btn:hover { color: #ef4444; }
    .date-input-wrapper input:not([value=""]) ~ .date-clear-btn,
    .date-input-wrapper input.has-value ~ .date-clear-btn { display: block; }
</style>
@endsection

@section('content')
<!-- Header -->
<div class="mb-6 flex items-start justify-between gap-3">
    <div class="flex-1">
        <div class="flex items-center space-x-3 mb-2">
            
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('vendor.coupons') }}</h2>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-tag text-emerald-600 mr-1"></i>
                    <span class="font-medium" id="total-count">{{ $coupons->total() }}</span> {{ __('vendor.total_coupons') }}
                </p>
            </div>
        </div>
    </div>
    <button type="button" onclick="openCreateModal()"
            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold rounded-lg transition-all shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
        <i class="fas fa-plus mr-2"></i>
        {{ __('vendor.add_coupon') }}
    </button>
</div>

<!-- Filters -->
<div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
            <label for="search" class="block text-xs font-medium text-gray-700 mb-2">
                <i class="fas fa-search mr-1"></i>{{ __('vendor.search') }}
            </label>
            <input type="text" id="search" placeholder="{{ __('vendor.search_coupon_placeholder') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                   oninput="debounceSearch()">
        </div>
        <div>
            <label for="type_filter" class="block text-xs font-medium text-gray-700 mb-2">
                <i class="fas fa-percentage mr-1"></i>{{ __('vendor.type') }}
            </label>
            <select id="type_filter" onchange="fetchCoupons()"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                <option value="">{{ __('vendor.all_types') }}</option>
                <option value="fixed">{{ __('vendor.fixed_amount') }}</option>
                <option value="percent">{{ __('vendor.percentage') }}</option>
            </select>
        </div>
        <div>
            <label for="status_filter" class="block text-xs font-medium text-gray-700 mb-2">
                <i class="fas fa-toggle-on mr-1"></i>{{ __('vendor.status') }}
            </label>
            <select id="status_filter" onchange="fetchCoupons()"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                <option value="">{{ __('vendor.all_status') }}</option>
                <option value="active">{{ __('vendor.active') }}</option>
                <option value="inactive">{{ __('vendor.inactive') }}</option>
            </select>
        </div>
    </div>
    <div class="mt-4 flex items-center justify-between">
        <button type="button" onclick="clearFilters()" class="text-sm text-gray-600 hover:text-gray-900 font-medium">
            <i class="fas fa-times-circle mr-1"></i>{{ __('vendor.clear_filters') }}
        </button>
    </div>
</div>

<!-- Coupon List -->
<div id="coupon-list" class="space-y-3">
    @include('vendor.coupons._list')
</div>

<!-- Create/Edit Coupon Modal -->
<div id="couponModal" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full transform transition-all max-h-[90vh] flex flex-col" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-100 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center bg-emerald-600 rounded-lg">
                            <i class="fas fa-ticket-alt text-white"></i>
                        </div>
                        <div>
                            <h3 id="modalTitle" class="text-lg font-bold text-gray-900">{{ __('vendor.add_coupon') }}</h3>
                            <p id="modalSubtitle" class="text-xs text-gray-600">{{ __('vendor.create_new_coupon_code') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <form id="couponForm" onsubmit="submitCoupon(event)" class="flex-1 overflow-y-auto p-6 space-y-4">
                <input type="hidden" id="coupon_id" value="">

                <!-- Code & Name -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="code" class="block text-sm font-semibold text-gray-700 mb-1">
                            {{ __('vendor.coupon_code') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="code" name="code" required maxlength="50"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent uppercase"
                               placeholder="{{ __('vendor.coupon_code_placeholder') }}">
                        <p id="codeError" class="mt-1 text-xs text-red-600 hidden"></p>
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.name') }}</label>
                        <input type="text" id="name" name="name" maxlength="255"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                               placeholder="{{ __('vendor.coupon_name_placeholder') }}">
                    </div>
                </div>

                <!-- Type & Value -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            {{ __('vendor.type') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="type" name="type" required onchange="updateValueLabel()"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="fixed">{{ __('vendor.fixed_amount_currency') }}</option>
                            <option value="percent">{{ __('vendor.percentage_percent') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="value" class="block text-sm font-semibold text-gray-700 mb-1">
                            <span id="valueLabel">{{ __('vendor.value_currency') }}</span> <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="value" name="value" required step="0.01" min="0.01"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                               placeholder="0.00">
                        <p id="valueError" class="mt-1 text-xs text-red-600 hidden"></p>
                    </div>
                </div>

                <!-- Min Order & Max Discount -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="min_order_amount" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.min_order') }}</label>
                        <input type="number" id="min_order_amount" name="min_order_amount" step="0.01" min="0"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                               placeholder="0.00">
                    </div>
                    <div>
                        <label for="max_discount_amount" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.max_discount') }}</label>
                        <input type="number" id="max_discount_amount" name="max_discount_amount" step="0.01" min="0"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                               placeholder="{{ __('vendor.no_limit') }}">
                    </div>
                </div>

                <!-- Usage Limit & Active -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="usage_limit" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.usage_limit') }}</label>
                        <input type="number" id="usage_limit" name="usage_limit" min="1"
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                               placeholder="{{ __('vendor.unlimited') }}">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" id="is_active" name="is_active" checked
                                   class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                            <span class="text-sm font-semibold text-gray-700">{{ __('vendor.active') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-1">
                            <i class="far fa-calendar-alt text-emerald-600 mr-1"></i>{{ __('vendor.start_date') }}
                        </label>
                        <div class="date-input-wrapper">
                            <input type="text" id="start_date" name="start_date" readonly
                                   class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white cursor-pointer"
                                   placeholder="{{ __('vendor.select_start_date') }}">
                            <span class="date-clear-btn" onclick="clearDate('start')" title="{{ __('vendor.clear') }}">
                                <i class="fas fa-times"></i>
                            </span>
                            <span class="date-icon"><i class="fas fa-chevron-down"></i></span>
                        </div>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-1">
                            <i class="far fa-calendar-alt text-emerald-600 mr-1"></i>{{ __('vendor.end_date') }}
                        </label>
                        <div class="date-input-wrapper">
                            <input type="text" id="end_date" name="end_date" readonly
                                   class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white cursor-pointer"
                                   placeholder="{{ __('vendor.select_end_date') }}">
                            <span class="date-clear-btn" onclick="clearDate('end')" title="{{ __('vendor.clear') }}">
                                <i class="fas fa-times"></i>
                            </span>
                            <span class="date-icon"><i class="fas fa-chevron-down"></i></span>
                        </div>
                    </div>
                </div>

                <p id="formError" class="text-sm text-red-600 hidden"></p>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal()"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit" id="submitBtn"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-95 shadow-sm">
                        <i class="fas fa-check mr-2"></i><span id="submitBtnText">{{ __('vendor.create_coupon') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div id="deleteModal" class="fixed inset-0 z-[80] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeDeleteModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-sm w-full transform transition-all" onclick="event.stopPropagation()">
            <div class="p-6 text-center">
                <div class="w-14 h-14 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-trash-alt text-red-500 text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('vendor.delete_coupon') }}</h3>
                <p class="text-sm text-gray-600 mb-1">{{ __('vendor.confirm_delete_coupon') }}</p>
                <p class="text-sm font-bold text-gray-900 mb-6" id="deleteCode"></p>
                <div class="flex items-center justify-center gap-3">
                    <button type="button" onclick="closeDeleteModal()"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="button" onclick="deleteCoupon()" id="deleteBtn"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-all active:scale-95">
                        <i class="fas fa-trash-alt mr-2"></i>{{ __('vendor.delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const baseUrl = '{{ route("vendor.coupons.index") }}';
let searchTimeout = null;
let pendingDeleteId = null;

// --- Flatpickr Date Pickers ---
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

function toggleClearBtn(instance) {
    const wrapper = instance.element.closest('.date-input-wrapper');
    if (!wrapper) return;
    const clearBtn = wrapper.querySelector('.date-clear-btn');
    if (clearBtn) {
        clearBtn.style.display = instance.selectedDates.length > 0 ? 'block' : 'none';
    }
}

function getDefaultStartDate() {
    const d = new Date();
    d.setHours(0, 1, 0, 0);
    return d;
}

function getDefaultEndDate() {
    const d = new Date();
    d.setMonth(d.getMonth() + 1);
    d.setHours(23, 59, 0, 0);
    return d;
}

const startPicker = flatpickr('#start_date', {
    ...fpConfig,
    defaultDate: getDefaultStartDate(),
    onChange: function(selectedDates, dateStr, instance) {
        toggleClearBtn(instance);
        if (selectedDates.length > 0) {
            endPicker.set('minDate', selectedDates[0]);
        } else {
            endPicker.set('minDate', null);
        }
    }
});

const endPicker = flatpickr('#end_date', {
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

function clearDate(which) {
    if (which === 'start') {
        startPicker.clear();
        endPicker.set('minDate', null);
    } else {
        endPicker.clear();
        startPicker.set('maxDate', null);
    }
    toggleClearBtn(which === 'start' ? startPicker : endPicker);
}

// --- Filters ---
function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => fetchCoupons(), 400);
}

function clearFilters() {
    document.getElementById('search').value = '';
    document.getElementById('type_filter').value = '';
    document.getElementById('status_filter').value = '';
    fetchCoupons();
}

function fetchCoupons() {
    const search = document.getElementById('search').value;
    const type = document.getElementById('type_filter').value;
    const status = document.getElementById('status_filter').value;

    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (type) params.append('type', type);
    if (status) params.append('status', status);

    const listEl = document.getElementById('coupon-list');
    listEl.innerHTML = '<div class="flex items-center justify-center py-12"><i class="fas fa-spinner fa-spin text-emerald-500 text-2xl"></i></div>';

    fetch(`${baseUrl}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            listEl.innerHTML = data.html;
            document.getElementById('total-count').textContent = data.total;
        }
    })
    .catch(err => {
        console.error(err);
        listEl.innerHTML = '<div class="text-center py-12 text-red-500">{{ __('vendor.error_loading_coupons') }}</div>';
    });
}

// --- Modal ---
function openCreateModal() {
    document.getElementById('coupon_id').value = '';
    document.getElementById('couponForm').reset();
    document.getElementById('is_active').checked = true;
    document.getElementById('modalTitle').textContent = '{{ __('vendor.add_coupon') }}';
    document.getElementById('modalSubtitle').textContent = '{{ __('vendor.create_new_coupon_code') }}';
    document.getElementById('submitBtnText').textContent = '{{ __('vendor.create_coupon') }}';
    clearFormErrors();
    updateValueLabel();
    startPicker.set('maxDate', null);
    endPicker.set('minDate', null);
    startPicker.setDate(getDefaultStartDate(), true);
    endPicker.clear();
    document.getElementById('couponModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function editCoupon(id) {
    clearFormErrors();
    document.getElementById('couponModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('modalTitle').textContent = '{{ __('vendor.edit_coupon') }}';
    document.getElementById('modalSubtitle').textContent = '{{ __('vendor.update_coupon_details') }}';
    document.getElementById('submitBtnText').textContent = '{{ __('vendor.update_coupon') }}';

    // Show loading
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;

    fetch(`${baseUrl}/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const c = data.coupon;
            document.getElementById('coupon_id').value = c.id;
            document.getElementById('code').value = c.code;
            document.getElementById('name').value = c.name || '';
            document.getElementById('type').value = c.type;
            document.getElementById('value').value = c.value;
            document.getElementById('min_order_amount').value = c.min_order_amount > 0 ? c.min_order_amount : '';
            document.getElementById('max_discount_amount').value = c.max_discount_amount || '';
            document.getElementById('usage_limit').value = c.usage_limit || '';
            document.getElementById('is_active').checked = c.is_active;
            // Reset constraints before setting dates
            startPicker.set('maxDate', null);
            endPicker.set('minDate', null);
            if (c.start_date) {
                startPicker.setDate(c.start_date, true);
                endPicker.set('minDate', c.start_date);
            } else {
                startPicker.clear();
            }
            if (c.end_date) {
                endPicker.setDate(c.end_date, true);
                startPicker.set('maxDate', c.end_date);
            } else {
                endPicker.clear();
            }
            updateValueLabel();
        } else {
            closeModal();
            showToast(data.message || '{{ __('vendor.error_loading_coupon') }}', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        closeModal();
        showToast('{{ __('vendor.error_loading_coupon') }}', 'error');
    })
    .finally(() => { submitBtn.disabled = false; });
}

function closeModal() {
    document.getElementById('couponModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function updateValueLabel() {
    const type = document.getElementById('type').value;
    document.getElementById('valueLabel').textContent = type === 'percent' ? '{{ __('vendor.value_percent') }}' : '{{ __('vendor.value_currency') }}';
}

function clearFormErrors() {
    document.getElementById('codeError').classList.add('hidden');
    document.getElementById('valueError').classList.add('hidden');
    document.getElementById('formError').classList.add('hidden');
}

// --- Submit ---
function submitCoupon(e) {
    e.preventDefault();
    clearFormErrors();

    const id = document.getElementById('coupon_id').value;
    const isEdit = !!id;
    const url = isEdit ? `${baseUrl}/${id}` : baseUrl;
    const method = isEdit ? 'PUT' : 'POST';

    const body = {
        code: document.getElementById('code').value,
        name: document.getElementById('name').value || null,
        type: document.getElementById('type').value,
        value: document.getElementById('value').value,
        min_order_amount: document.getElementById('min_order_amount').value || null,
        max_discount_amount: document.getElementById('max_discount_amount').value || null,
        usage_limit: document.getElementById('usage_limit').value || null,
        start_date: document.getElementById('start_date').value || null,
        end_date: document.getElementById('end_date').value || null,
        is_active: document.getElementById('is_active').checked,
    };

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.querySelector('#submitBtnText').textContent = isEdit ? '{{ __('vendor.updating') }}' : '{{ __('vendor.creating') }}';

    fetch(url, {
        method: method,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(body),
    })
    .then(r => r.json().then(data => ({ status: r.status, data })))
    .then(({ status, data }) => {
        if (status === 422) {
            if (data.errors) {
                if (data.errors.code) {
                    document.getElementById('codeError').textContent = data.errors.code[0];
                    document.getElementById('codeError').classList.remove('hidden');
                }
                if (data.errors.value) {
                    document.getElementById('valueError').textContent = data.errors.value[0];
                    document.getElementById('valueError').classList.remove('hidden');
                }
                const otherErrors = Object.entries(data.errors)
                    .filter(([k]) => k !== 'code' && k !== 'value')
                    .map(([, v]) => v[0]);
                if (otherErrors.length) {
                    document.getElementById('formError').textContent = otherErrors.join('. ');
                    document.getElementById('formError').classList.remove('hidden');
                }
            } else {
                document.getElementById('formError').textContent = data.message || '{{ __('vendor.validation_error') }}';
                document.getElementById('formError').classList.remove('hidden');
            }
            return;
        }

        if (data.success) {
            closeModal();
            fetchCoupons();
            showToast(data.message, 'success');
        } else {
            document.getElementById('formError').textContent = data.message || '{{ __('vendor.error_saving_coupon') }}';
            document.getElementById('formError').classList.remove('hidden');
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('formError').textContent = '{{ __('vendor.error_saving_coupon') }}';
        document.getElementById('formError').classList.remove('hidden');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.querySelector('#submitBtnText').textContent = isEdit ? '{{ __('vendor.update_coupon') }}' : '{{ __('vendor.create_coupon') }}';
    });
}

// --- Toggle ---
function toggleCoupon(id) {
    fetch(`${baseUrl}/${id}/toggle`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const isActive = data.is_active;

            // Update toggle button
            const toggleBtn = document.querySelector(`[data-coupon-toggle="${id}"]`);
            if (toggleBtn) {
                toggleBtn.className = `p-2 rounded-lg transition-colors ${isActive ? 'text-emerald-600 hover:bg-emerald-50' : 'text-gray-400 hover:bg-gray-100'}`;
                toggleBtn.title = isActive ? '{{ __('vendor.deactivate') }}' : '{{ __('vendor.activate') }}';
                toggleBtn.querySelector('i').className = `fas ${isActive ? 'fa-toggle-on' : 'fa-toggle-off'} text-lg`;
            }

            // Update status badge
            const statusBadge = document.querySelector(`[data-coupon-status="${id}"]`);
            if (statusBadge) {
                if (isActive) {
                    statusBadge.className = 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-600';
                    statusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i>{{ __('vendor.active') }}';
                } else {
                    statusBadge.className = 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500';
                    statusBadge.innerHTML = '<i class="fas fa-ban mr-1"></i>{{ __('vendor.inactive') }}';
                }
            }

            // Update code badge
            const codeBadge = document.querySelector(`[data-coupon-badge="${id}"]`);
            if (codeBadge) {
                codeBadge.className = `inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold tracking-wider border ${isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200'}`;
            }

            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('{{ __('vendor.error_toggling_coupon') }}', 'error');
    });
}

// --- Delete ---
function confirmDeleteCoupon(id, code) {
    pendingDeleteId = id;
    document.getElementById('deleteCode').textContent = code;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
    pendingDeleteId = null;
}

function deleteCoupon() {
    if (!pendingDeleteId) return;

    const btn = document.getElementById('deleteBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>{{ __('vendor.deleting') }}';

    fetch(`${baseUrl}/${pendingDeleteId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const deletedId = pendingDeleteId;
            closeDeleteModal();
            // Remove card from DOM
            const card = document.querySelector(`[data-coupon-id="${deletedId}"]`);
            if (card) {
                card.style.transition = 'opacity 0.3s, transform 0.3s';
                card.style.opacity = '0';
                card.style.transform = 'translateX(20px)';
                setTimeout(() => {
                    card.remove();
                    // Update count
                    const countEl = document.getElementById('total-count');
                    if (countEl) countEl.textContent = Math.max(0, parseInt(countEl.textContent) - 1);
                    // Show empty state if no cards left
                    if (!document.querySelector('.coupon-card')) {
                        document.getElementById('coupon-list').innerHTML = '<div class="col-span-full text-center py-16"><div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4"><i class="fas fa-ticket-alt text-2xl text-gray-400"></i></div><h3 class="text-lg font-semibold text-gray-700 mb-1">{{ __('vendor.no_coupons_found') }}</h3><p class="text-sm text-gray-500">{{ __('vendor.create_first_coupon') }}</p></div>';
                    }
                }, 300);
            }
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error', 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('{{ __('vendor.error_deleting_coupon') }}', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash-alt mr-2"></i>{{ __('vendor.delete') }}';
    });
}

// --- Toast ---
function showToast(message, type = 'success') {
    const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 z-[90]`;
    toast.innerHTML = `
        <i class="fas ${icon} text-2xl"></i>
        <div><p class="font-medium">${message}</p></div>
        <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-100"><i class="fas fa-times"></i></button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}
</script>
@endsection
