@extends('vendor.layouts.app')

@section('title', __('vendor.add') . ' ' . __('vendor.cart'))
@section('page-title', __('vendor.add') . ' ' . __('vendor.cart'))

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
<!-- Back Button -->
<div class="mb-4 md:mb-6">
    <a href="{{ route('vendor.carts.index') }}" 
       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 bg-white hover:bg-blue-50 rounded-lg border border-gray-200 transition-all active:scale-95">
        <i class="fas fa-arrow-left mr-2"></i>
        <span class="hidden sm:inline">{{ __('vendor.back') }}</span>
        <span class="sm:hidden">{{ __('vendor.back') }}</span>
    </a>
</div>

<div class="max-w-2xl">
    
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{{ __('vendor.create') }} {{ __('vendor.cart') }}</h1>
        <p class="text-sm md:text-base text-gray-600">{{ __('vendor.start_new_cart') }}</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Form Header -->
        <div class="px-4 py-4 md:px-6 md:py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-blue-600 rounded-lg">
                    <i class="fas fa-shopping-cart text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg md:text-xl font-bold text-gray-900">{{ __('vendor.cart') }} {{ __('vendor.details') }}</h2>
                    <p class="text-xs md:text-sm text-gray-600">{{ __('vendor.fill_information') }}</p>
                </div>
            </div>
        </div>

        <form action="{{ route('vendor.carts.store') }}" method="POST" class="p-4 md:p-6">
            @csrf

            <!-- Customer Selection -->
            <div class="mb-5 md:mb-6">
                <label for="customer_id" class="block text-sm md:text-base font-semibold text-gray-700 mb-2">
                    {{ __('vendor.select') }} {{ __('vendor.customer') }} <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <select name="customer_id" 
                            id="customer_id"
                            class="w-full pl-11 pr-4 py-2.5 md:py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('customer_id') border-red-500 @enderror"
                            required>
                        <option value="">{{ __('vendor.choose_customer') }}</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} - {{ $customer->mobile }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('customer_id')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $message }}
                    </p>
                @enderror
                @if($customers->count() == 0)
                    <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            {{ __('vendor.no_customers') }}. <a href="{{ route('vendor.customers.create') }}" class="font-semibold underline hover:text-yellow-900">{{ __('vendor.create_customer_first') }}</a>
                        </p>
                    </div>
                @endif
            </div>

            <!-- Cart Name -->
            <div class="mb-5 md:mb-6">
                <label for="cart_name" class="block text-sm md:text-base font-semibold text-gray-700 mb-2">
                    {{ __('vendor.name') }} <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i class="fas fa-tag text-gray-400"></i>
                    </div>
                    <input type="text" 
                           name="cart_name" 
                           id="cart_name" 
                           value="{{ old('cart_name') }}"
                           class="w-full pl-11 pr-4 py-2.5 md:py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('cart_name') border-red-500 @enderror"
                           placeholder="{{ __('vendor.cart_name_placeholder') }}"
                           required>
                </div>
                @error('cart_name')
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Booking Dates -->
            <div class="mb-6 md:mb-8">
                <label class="block text-sm md:text-base font-semibold text-gray-700 mb-3">
                    <i class="fas fa-calendar text-emerald-600 mr-1"></i>
                    {{ __('vendor.booking_dates') }} <span class="text-gray-500 text-xs">({{ __('vendor.optional') }})</span>
                </label>
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Start Time -->
                    <div>
                        <label for="start_time" class="block text-sm font-semibold text-gray-700 mb-1">
                            <i class="far fa-calendar-alt text-emerald-600 mr-1"></i>{{ __('vendor.start_date_time') }}
                        </label>
                        <div class="date-input-wrapper">
                            <input type="text" 
                                   name="start_time" 
                                   id="start_time" 
                                   value="{{ old('start_time') }}"
                                   readonly
                                   class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white cursor-pointer @error('start_time') border-red-500 @enderror"
                                   placeholder="Select start date">
                            <span class="date-clear-btn" onclick="clearDate('start')" title="Clear">
                                <i class="fas fa-times"></i>
                            </span>
                            <span class="date-icon"><i class="fas fa-chevron-down"></i></span>
                        </div>
                        @error('start_time')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- End Time -->
                    <div>
                        <label for="end_time" class="block text-sm font-semibold text-gray-700 mb-1">
                            <i class="far fa-calendar-alt text-emerald-600 mr-1"></i>{{ __('vendor.end_date_time') }}
                        </label>
                        <div class="date-input-wrapper">
                            <input type="text" 
                                   name="end_time" 
                                   id="end_time" 
                                   value="{{ old('end_time') }}"
                                   readonly
                                   class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white cursor-pointer @error('end_time') border-red-500 @enderror"
                                   placeholder="Select end date">
                            <span class="date-clear-btn" onclick="clearDate('end')" title="Clear">
                                <i class="fas fa-times"></i>
                            </span>
                            <span class="date-icon"><i class="fas fa-chevron-down"></i></span>
                        </div>
                        @error('end_time')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Info Banner -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-sm text-blue-900 font-semibold mb-1">{{ __('vendor.after_creating_cart') }}</p>
                        <p class="text-sm text-blue-800">
                            {{ __('vendor.cart_management_info') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('vendor.carts.index') }}" 
                   class="flex-1 sm:flex-none px-6 py-2.5 md:py-3 text-center text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    {{ __('vendor.cancel') }}
                </a>
                <button type="submit" 
                        class="flex-1 sm:flex-none px-6 py-2.5 md:py-3 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-lg transition-all shadow-sm hover:shadow active:scale-95">
                    <i class="fas fa-save mr-2"></i>
                    {{ __('vendor.create') }} {{ __('vendor.cart') }}
                </button>
            </div>
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
});
</script>
@endsection
