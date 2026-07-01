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
     @keydown.escape.window="if (!showAddCategoryModal) closeAddItemModal()">
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

                <div @click.outside="closeQuickCategoryDropdown()">
                    <label for="orderWizardQuickItemCategorySearch" class="{{ $labelClass }}">
                        {{ __('vendor.category') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400" aria-hidden="true">
                            <i class="fas fa-search text-sm"></i>
                        </span>
                        <input type="text"
                               id="orderWizardQuickItemCategorySearch"
                               x-model="quickCategoryQuery"
                               @focus="openQuickCategoryDropdown()"
                               @input="onQuickCategoryQueryInput()"
                               @keydown.escape.prevent="closeQuickCategoryDropdown()"
                               autocomplete="off"
                               role="combobox"
                               :aria-expanded="quickCategoryDropdownOpen ? 'true' : 'false'"
                               aria-controls="orderWizardQuickCategoryListbox"
                               aria-autocomplete="list"
                               class="{{ $inputClass }} pl-10 pr-10"
                               placeholder="{{ __('vendor.item_category_search_placeholder') }}">
                        <button type="button"
                                x-show="quickItem.category_id || (quickCategoryQuery && quickCategoryQuery.length)"
                                x-cloak
                                @click="clearQuickCategory()"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                :aria-label="@json(__('vendor.clear'))">
                            <i class="fas fa-times text-sm" aria-hidden="true"></i>
                        </button>

                        <ul id="orderWizardQuickCategoryListbox"
                            x-show="quickCategoryDropdownOpen && filteredQuickCategories.length"
                            x-cloak
                            role="listbox"
                            class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
                            <template x-for="cat in filteredQuickCategories" :key="cat.id">
                                <li role="option"
                                    :aria-selected="String(quickItem.category_id) === String(cat.id)"
                                    @mousedown.prevent="selectQuickCategory(cat)"
                                    class="cursor-pointer px-3 py-2.5 text-sm text-gray-800 hover:bg-emerald-50"
                                    :class="{ 'bg-emerald-50 font-medium text-emerald-900': String(quickItem.category_id) === String(cat.id) }"
                                    x-text="cat.name"></li>
                            </template>
                        </ul>

                        <p x-show="quickCategoryDropdownOpen && quickCategoryQuery.trim() && !filteredQuickCategories.length"
                           x-cloak
                           class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-xs text-gray-600 shadow-lg">
                            {{ __('vendor.order_wizard_no_categories_match') }}
                        </p>
                    </div>

                    <button type="button"
                            @click="openAddCategoryModal()"
                            class="mt-2 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 hover:text-emerald-800">
                        <i class="fas fa-plus-circle text-xs" aria-hidden="true"></i>
                        {{ __('vendor.order_wizard_add_category_inline') }}
                    </button>
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

                <div class="flex flex-col gap-2 border-t border-gray-200 pt-4 sm:flex-row sm:justify-end">
                    <button type="button"
                            @click="closeAddItemModal()"
                            class="{{ $btnOutlineNeutral ?? 'inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50' }} w-full sm:w-auto">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit"
                            :disabled="quickItemSaving"
                            class="{{ $btnPrimary ?? 'inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700' }} w-full disabled:opacity-60 sm:w-auto">
                        <span x-show="!quickItemSaving">{{ __('vendor.order_wizard_save_and_add_item') }}</span>
                        <span x-show="quickItemSaving" x-cloak>{{ __('vendor.save') }}…</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</template>

{{-- Add category modal (stacked above add-item modal) --}}
<template x-teleport="body">
    <div x-show="showAddCategoryModal"
         x-cloak
         class="fixed inset-0 z-[80] flex items-end justify-center sm:items-center sm:p-4"
         role="dialog"
         aria-modal="true"
         aria-labelledby="orderWizardQuickCategoryTitle"
         @keydown.escape.window.stop="closeAddCategoryModal()">
        <div class="fixed inset-0 bg-black/50"
             @click="closeAddCategoryModal()"
             aria-hidden="true"></div>

        <div class="relative z-10 w-full max-w-md overflow-hidden rounded-t-2xl border border-gray-200 bg-white shadow-2xl sm:rounded-2xl"
             @click.stop>
            <div class="border-b border-gray-100 bg-gradient-to-r from-slate-50 to-emerald-50/40 px-4 py-4 sm:px-5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                            <i class="fas fa-folder-plus text-sm" aria-hidden="true"></i>
                        </span>
                        <div>
                            <h3 id="orderWizardQuickCategoryTitle" class="text-base font-bold text-gray-900 sm:text-lg">
                                {{ __('vendor.order_wizard_new_category_heading') }}
                            </h3>
                            <p class="text-xs text-gray-500">{{ __('vendor.order_wizard_add_category_modal_hint') }}</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="closeAddCategoryModal()"
                            class="rounded-xl p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                            :aria-label="@json(__('vendor.cancel'))">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-5">
                <label for="orderWizardQuickCategoryName" class="mb-1 block text-sm font-medium text-gray-800">
                    {{ __('vendor.category_name') }} <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="orderWizardQuickCategoryName"
                       x-model="newCategoryName"
                       maxlength="255"
                       @keydown.enter.prevent="saveQuickCategory()"
                       class="{{ $inputClass }}"
                       placeholder="{{ __('vendor.category_name') }}">

                <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button"
                            @click="closeAddCategoryModal()"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-xl border border-gray-200 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:min-h-[40px]">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="button"
                            @click="saveQuickCategory()"
                            :disabled="categoryCreateSaving"
                            class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60 sm:min-h-[40px]">
                        <span x-show="!categoryCreateSaving">
                            <i class="fas fa-check text-xs" aria-hidden="true"></i>
                            {{ __('vendor.order_wizard_save_and_select_category') }}
                        </span>
                        <span x-show="categoryCreateSaving" x-cloak>
                            <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                            {{ __('vendor.save') }}…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
