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
    $startParts = $parseBookingDateTime($startTime);
    $endParts = $parseBookingDateTime($endTime);
@endphp

<div class="w-full" data-wizard-alpine-root>
    <div class="mb-3 sm:mb-4">
        <h1 class="text-base font-bold leading-tight text-gray-900 sm:text-lg">{{ __('vendor.create_order') }}</h1>
        <p class="mt-1 max-md:line-clamp-3 text-xs leading-snug text-gray-600 sm:text-sm">{{ __('vendor.create_order_direct_subtitle') }}</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100">
        <form @submit.prevent="document.dispatchEvent(new CustomEvent('sync-booking-dates')); $nextTick(() => $wire.saveStep1())" class="p-3 sm:p-4">
            {{-- Customer picker (Alpine UI + Livewire state) --}}
            <div class="mb-4 sm:mb-5"
                 wire:ignore.self
                 x-data="orderWizardCustomerPicker({ customers: @js($customers) })"
                 @customer-saved.window="onCustomerSaved($event.detail)">
                <label for="orderWizard_customerSearch" class="mb-1.5 block text-sm font-semibold text-gray-800">
                    {{ __('vendor.select') }} {{ __('vendor.customer') }} <span class="text-red-500">*</span>
                </label>
                <div class="relative" @click.outside="dropdownOpen = false">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-search text-sm text-gray-400"></i>
                    </div>
                    <input type="text"
                           id="orderWizard_customerSearch"
                           x-model="searchLabel"
                           @focus="openDropdown()"
                           @input="onSearchInput()"
                           class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-10 pr-10 text-sm text-gray-900 placeholder:text-gray-400 focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('customer_id') border-red-500 @enderror"
                           placeholder="{{ __('vendor.order_wizard_search_customer_placeholder') }}"
                           autocomplete="off">
                    <button type="button"
                            x-show="$wire.customerId"
                            x-cloak
                            @click="clearSelection()"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                            aria-label="{{ __('vendor.clear') }}">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                    <div x-show="dropdownOpen"
                         x-cloak
                         class="order-wizard-customer-dropdown absolute z-40 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg">
                        <button type="button"
                                @click="openNewCustomer()"
                                class="sticky top-0 flex w-full items-center gap-2 border-b border-gray-100 bg-white px-3 py-2 text-left text-xs font-semibold text-emerald-700 hover:bg-emerald-50 sm:text-sm">
                            <i class="fas fa-plus-circle text-xs" aria-hidden="true"></i>
                            {{ __('vendor.order_wizard_add_customer_inline') }}
                        </button>
                        <template x-for="customer in filteredCustomers" :key="customer.id">
                            <div @click.stop="selectCustomer(customer)"
                                 class="flex cursor-pointer items-center justify-between px-3 py-2 transition-colors hover:bg-emerald-50"
                                 role="option">
                                <div>
                                    <span class="text-sm font-medium text-gray-900" x-text="customer.name"></span>
                                    <span class="ml-2 text-xs text-gray-500" x-text="customer.mobile"></span>
                                </div>
                                <i class="fas fa-check text-emerald-500"
                                   :class="isSelected(customer.id) ? '' : 'hidden'"
                                   aria-hidden="true"></i>
                            </div>
                        </template>
                        <div x-show="filteredCustomers.length === 0 && searchQuery !== ''"
                             class="px-3 py-2 text-center text-xs text-gray-500 sm:text-sm">
                            <i class="fas fa-search mr-1" aria-hidden="true"></i>{{ __('vendor.order_wizard_no_customers_match') }}
                        </div>
                    </div>
                </div>

                @if(count($customers) === 0)
                    <div class="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-2.5 sm:p-3">
                        <p class="text-xs text-amber-900 sm:text-sm">
                            <i class="fas fa-info-circle mr-1 text-xs" aria-hidden="true"></i>
                            {{ __('vendor.no_customers') }}.
                            <button type="button"
                                    @click="openNewCustomer()"
                                    class="font-semibold text-emerald-700 underline decoration-emerald-600/40 underline-offset-2 hover:text-emerald-800">
                                {{ __('vendor.order_wizard_add_customer_inline') }}
                            </button>
                        </p>
                    </div>
                @endif
            </div>

            {{-- Event name --}}
            <div class="mb-4 sm:mb-5">
                <label for="event_name" class="mb-1.5 block text-sm font-semibold text-gray-800">
                    {{ __('vendor.event_name') }} <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fas fa-calendar-check text-sm text-gray-400"></i>
                    </div>
                    <input type="text"
                           wire:model="eventName"
                           id="event_name"
                           class="h-10 w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3 text-sm text-gray-900 transition focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('event_name') border-red-500 @enderror"
                           placeholder="{{ __('vendor.cart_name_placeholder') }}"
                           maxlength="255">
                </div>
            </div>

            {{-- Booking dates (Flatpickr + Alpine, wire:ignore on inputs only) --}}
            <div class="mb-4 sm:mb-5">
                <label class="mb-2 block text-sm font-semibold text-gray-800">
                    <i class="fas fa-calendar mr-1 text-emerald-600"></i>
                    {{ __('vendor.booking_dates') }} <span class="text-red-500">*</span>
                </label>

                <div wire:ignore
                     x-data="orderWizardBookingDates({
                         prefillStartTime: @js($startParts['time']),
                         prefillEndTime: @js($endParts['time']),
                         startDateValue: @js($startParts['date']),
                         endDateValue: @js($endParts['date']),
                     })"
                     x-init="init()"
                     @sync-booking-dates.window="syncToWire()">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-4">
                    <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-emerald-800 sm:text-sm">{{ __('vendor.start_date_time') }}</p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div>
                                <div class="date-input-wrapper">
                                    <input type="text"
                                           id="start_date"
                                           x-ref="startDate"
                                           readonly
                                           class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('start_time') border-red-500 @enderror"
                                           placeholder="{{ __('vendor.select_date') }}">
                                    <span class="date-icon"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                            </div>
                            <div>
                                <div class="time-input-wrapper">
                                    <select id="start_time_select"
                                            x-ref="startTimeSelect"
                                            class="booking-time-select w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('start_time') border-red-500 @enderror"
                                            data-placeholder="{{ __('vendor.select_time') }}">
                                        <option value="">{{ __('vendor.select_time') }}</option>
                                    </select>
                                    <span class="time-icon"><i class="fas fa-clock"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 rounded-lg border border-gray-100 bg-gray-50/60 p-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-emerald-800 sm:text-sm">{{ __('vendor.end_date_time') }}</p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div>
                                <div class="date-input-wrapper">
                                    <input type="text"
                                           id="end_date"
                                           x-ref="endDate"
                                           readonly
                                           class="w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('end_time') border-red-500 @enderror"
                                           placeholder="{{ __('vendor.select_date') }}">
                                    <span class="date-icon"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                            </div>
                            <div>
                                <div class="time-input-wrapper">
                                    <select id="end_time_select"
                                            x-ref="endTimeSelect"
                                            class="booking-time-select w-full cursor-pointer rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500 @error('end_time') border-red-500 @enderror"
                                            data-placeholder="{{ __('vendor.select_time') }}">
                                        <option value="">{{ __('vendor.select_time') }}</option>
                                    </select>
                                    <span class="time-icon"><i class="fas fa-clock"></i></span>
                                </div>
                            </div>
                        </div>
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
                        wire:loading.attr="disabled"
                        wire:target="saveStep1"
                        class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition [touch-action:manipulation] hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-70 sm:w-auto sm:min-w-[8rem]">
                    <span wire:loading.remove wire:target="saveStep1">
                        <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                        {{ __('vendor.order_wizard_continue_items') }}
                    </span>
                    <span wire:loading wire:target="saveStep1">
                        <i class="fas fa-spinner fa-spin text-xs"></i>
                    </span>
                </button>
            </x-order-wizard-actions>
        </form>

        @if($showNewCustomerForm)
            <div class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4"
                 wire:key="order-wizard-new-customer-modal"
                 x-data
                 x-trap.noscroll="true"
                 @keydown.escape.window="$wire.closeNewCustomerForm()">
                <div class="fixed inset-0 bg-black/50"
                     wire:click="closeNewCustomerForm"
                     aria-hidden="true"></div>

                <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-t-2xl border border-gray-200 bg-white shadow-2xl sm:rounded-2xl"
                     @click.stop
                     role="dialog"
                     aria-modal="true"
                     aria-labelledby="orderWizard_newCustomerModalTitle">
                    <div class="border-b border-gray-100 px-4 py-3 sm:px-5 sm:py-4">
                        <div class="flex items-center justify-between gap-3">
                            <h3 id="orderWizard_newCustomerModalTitle" class="text-base font-bold text-gray-900 sm:text-lg">
                                <i class="fas fa-user-plus mr-1.5 text-sm text-emerald-600" aria-hidden="true"></i>{{ __('vendor.order_wizard_new_customer_heading') }}
                            </h3>
                            <button type="button"
                                    wire:click="closeNewCustomerForm"
                                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                                    aria-label="{{ __('vendor.cancel') }}">
                                <i class="fas fa-times" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <div class="p-4 sm:p-5">
                        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="orderWizard_newCustomerName" class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.customer_name') }} <span class="text-red-500">*</span></label>
                                <input type="text"
                                       id="orderWizard_newCustomerName"
                                       wire:model="newCustomerName"
                                       maxlength="255"
                                       autofocus
                                       class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('newCustomerName') border-red-500 @enderror"
                                       placeholder="{{ __('vendor.customer_name') }}">
                            </div>
                            <div>
                                <label for="orderWizard_newCustomerMobile" class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.mobile') }} <span class="text-red-500">*</span></label>
                                <input type="text"
                                       id="orderWizard_newCustomerMobile"
                                       wire:model="newCustomerMobile"
                                       maxlength="10"
                                       inputmode="numeric"
                                       class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 @error('newCustomerMobile') border-red-500 @enderror"
                                       placeholder="{{ __('vendor.customer_mobile') }}">
                            </div>
                        </div>
                        <div class="mb-5">
                            <label for="orderWizard_newCustomerAddress" class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.address') }}</label>
                            <input type="text"
                                   id="orderWizard_newCustomerAddress"
                                   wire:model="newCustomerAddress"
                                   maxlength="500"
                                   class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"
                                   placeholder="{{ __('vendor.optional') }}">
                        </div>

                        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button type="button"
                                    wire:click="closeNewCustomerForm"
                                    class="inline-flex min-h-[44px] items-center justify-center rounded-lg border border-gray-200 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:min-h-[40px]">
                                {{ __('vendor.cancel') }}
                            </button>
                            <button type="button"
                                    wire:click="saveNewCustomer"
                                    wire:loading.attr="disabled"
                                    wire:target="saveNewCustomer"
                                    class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-70 sm:min-h-[40px]">
                                <span wire:loading.remove wire:target="saveNewCustomer">
                                    <i class="fas fa-save text-xs" aria-hidden="true"></i>
                                    {{ __('vendor.order_wizard_save_and_select_customer') }}
                                </span>
                                <span wire:loading wire:target="saveNewCustomer">
                                    <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
