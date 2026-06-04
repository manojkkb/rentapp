{{-- Quick create item — standard vendor form controls (matches order wizard step 1) --}}
@php
    $inputClass = 'block h-10 w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 shadow-inner placeholder:text-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/25';
    $labelClass = 'mb-1.5 block text-sm font-semibold text-gray-800';
@endphp

<template x-teleport="body">
<div x-show="showAddItemModal"
     x-cloak
     class="fixed inset-0 z-[70] flex items-end justify-center p-0 sm:items-center sm:p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="orderWizardQuickItemTitle"
     @keydown.escape.window="closeAddItemModal()">
    <div class="absolute inset-0 bg-black/40"
         @click="closeAddItemModal()"
         aria-hidden="true"></div>

    <div class="relative z-10 flex max-h-[92dvh] w-full max-w-lg flex-col overflow-hidden rounded-t-xl border border-gray-200 bg-white shadow-xl sm:max-h-[90vh] sm:rounded-xl"
         @click.stop>

        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
            <h3 id="orderWizardQuickItemTitle" class="text-base font-bold text-gray-900">
                {{ __('vendor.order_wizard_new_item_heading') }}
            </h3>
            <button type="button"
                    @click="closeAddItemModal()"
                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                    :aria-label="@json(__('vendor.cancel'))">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-4">
            <form @submit.prevent="saveQuickItem()" class="space-y-4">
                <div>
                    <label for="orderWizardQuickItemName" class="{{ $labelClass }}">
                        {{ __('vendor.item_name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="orderWizardQuickItemName"
                           name="name"
                           x-model="quickItem.name"
                           maxlength="255"
                           required
                           class="{{ $inputClass }}"
                           placeholder="{{ __('vendor.item_name_placeholder') }}">
                </div>

                <div>
                    <label for="orderWizardQuickItemCategory" class="{{ $labelClass }}">
                        {{ __('vendor.category') }} <span class="text-red-500">*</span>
                    </label>
                    <select id="orderWizardQuickItemCategory"
                            name="category_id"
                            x-model="quickItem.category_id"
                            required
                            class="{{ $inputClass }}">
                        <option value="">{{ __('vendor.select_category') }}</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="String(cat.id)" x-text="cat.name"></option>
                        </template>
                    </select>
                    <button type="button"
                            @click="openAddCategoryInline()"
                            class="mt-2 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                        <i class="fas fa-plus-circle text-xs" aria-hidden="true"></i>
                        {{ __('vendor.order_wizard_add_category_inline') }}
                    </button>

                    <div x-show="showAddCategoryInline"
                         x-cloak
                         class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50/80 p-3">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-bold text-emerald-900">{{ __('vendor.order_wizard_new_category_heading') }}</span>
                            <button type="button"
                                    @click="closeAddCategoryInline()"
                                    class="rounded p-1 text-gray-500 hover:bg-white/80"
                                    :aria-label="@json(__('vendor.cancel'))">
                                <i class="fas fa-times text-sm" aria-hidden="true"></i>
                            </button>
                        </div>
                        <label for="orderWizardNewCategoryName" class="mb-1 block text-xs font-medium text-gray-600">
                            {{ __('vendor.category_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="orderWizardNewCategoryName"
                               x-model="newCategoryName"
                               maxlength="255"
                               class="{{ $inputClass }} mb-2">
                        <p x-show="categoryInlineError"
                           x-text="categoryInlineError"
                           x-cloak
                           class="mb-2 text-xs text-red-600"
                           role="alert"></p>
                        <button type="button"
                                @click="saveQuickCategory()"
                                :disabled="categoryCreateSaving"
                                class="h-10 w-full rounded-lg bg-emerald-600 px-3 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">
                            <span x-show="!categoryCreateSaving">{{ __('vendor.order_wizard_save_and_select_category') }}</span>
                            <span x-show="categoryCreateSaving" x-cloak>{{ __('vendor.save') }}…</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="orderWizardQuickItemPrice" class="{{ $labelClass }}">
                            {{ __('vendor.price') }} (₹) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               id="orderWizardQuickItemPrice"
                               name="price"
                               x-model="quickItem.price"
                               min="0"
                               step="0.01"
                               inputmode="decimal"
                               required
                               class="{{ $inputClass }}"
                               placeholder="0.00">
                    </div>
                    <div>
                        <label for="orderWizardQuickItemRental" class="{{ $labelClass }}">
                            {{ __('vendor.rental_period') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="orderWizardQuickItemRental"
                                name="rental_period"
                                x-model="quickItem.rental_period"
                                required
                                class="{{ $inputClass }}">
                            @foreach($rentalPeriods as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <p x-show="quickItemError"
                   x-text="quickItemError"
                   x-cloak
                   class="text-sm text-red-600"
                   role="alert"></p>

                <div class="flex flex-col gap-2 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end">
                    <button type="button"
                            @click="closeAddItemModal()"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit"
                            :disabled="quickItemSaving"
                            class="h-10 w-full rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60 sm:w-auto">
                        <span x-show="!quickItemSaving">{{ __('vendor.order_wizard_save_and_add_item') }}</span>
                        <span x-show="quickItemSaving" x-cloak>{{ __('vendor.save') }}…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</template>
