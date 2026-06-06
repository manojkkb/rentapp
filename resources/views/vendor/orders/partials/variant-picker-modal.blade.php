{{-- Variant picker — multi-select when adding, single when editing --}}
<template x-teleport="body">
<div x-show="showVariantModal"
     x-cloak
     class="fixed inset-0 z-[70] flex items-end justify-center p-0 sm:items-center sm:p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="orderWizardVariantPickerTitle"
     @keydown.escape.window="closeVariantModal()">
    <div class="absolute inset-0 bg-black/40"
         @click="closeVariantModal()"
         aria-hidden="true"></div>

    <div class="relative z-10 flex max-h-[92dvh] w-full max-w-lg flex-col overflow-hidden rounded-t-xl border border-gray-200 bg-white shadow-xl sm:max-h-[90vh] sm:rounded-xl"
         @click.stop>

        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3">
            <div class="min-w-0 pr-3">
                <h3 id="orderWizardVariantPickerTitle" class="text-base font-bold text-gray-900">
                    <span x-show="variantModalMode === 'add'">{{ __('vendor.order_wizard_choose_variant_heading') }}</span>
                    <span x-show="variantModalMode === 'modify'" x-cloak>{{ __('vendor.order_wizard_select_variant_heading') }}</span>
                    <span x-show="variantModalMode === 'change'" x-cloak>{{ __('vendor.order_wizard_edit_variant_heading') }}</span>
                </h3>
                <p class="mt-0.5 truncate text-xs text-gray-500" x-text="variantModalItem?.name || ''"></p>
            </div>
            <button type="button"
                    @click="closeVariantModal()"
                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100"
                    :aria-label="@json(__('vendor.cancel'))">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-4">
            <p class="mb-3 text-sm text-gray-600">
                <span x-show="variantModalMode === 'add'">{{ __('vendor.order_wizard_choose_variant_hint') }}</span>
                <span x-show="variantModalMode === 'modify'" x-cloak>{{ __('vendor.order_wizard_modify_variant_hint') }}</span>
                <span x-show="variantModalMode === 'change'" x-cloak>{{ __('vendor.order_wizard_select_variant') }}</span>
            </p>

            <div class="space-y-2">
                <template x-for="variant in (variantModalItem?.variants || [])" :key="'vm-'+variant.id">
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border px-3 py-3 transition"
                           :class="(variantModalMode === 'change' ? String(variantModalPick) === String(variant.id) : isVariantModalChecked(variant.id))
                               ? 'border-emerald-400 bg-emerald-50 ring-1 ring-emerald-200'
                               : (variantSelectable(variant) ? 'border-gray-200 bg-white hover:border-emerald-200 hover:bg-emerald-50/40' : 'cursor-not-allowed border-gray-100 bg-gray-50 opacity-60')"
                           @click="variantModalMode !== 'change' && variantSelectable(variant) ? toggleVariantModalSelection(variant.id) : null">
                        <input x-show="variantModalMode === 'change'"
                               type="radio"
                               class="mt-1 h-4 w-4 border-gray-300 text-emerald-600 focus:ring-emerald-500"
                               name="order_wizard_variant_pick"
                               :value="variant.id"
                               :disabled="!variantSelectable(variant)"
                               x-model="variantModalPick">
                        <input x-show="variantModalMode !== 'change'"
                               type="checkbox"
                               class="mt-1 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                               :checked="isVariantModalChecked(variant.id)"
                               :disabled="!variantSelectable(variant)"
                               @click.stop="toggleVariantModalSelection(variant.id)">
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-2">
                                <span class="block text-sm font-semibold text-gray-900" x-text="variant.label || variant.variant_code"></span>
                                <span x-show="variantModalItem && variantQtyInOrder(variantModalItem, variant.id) >= 1 && variantModalMode === 'add'"
                                      x-cloak
                                      class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800"
                                      x-text="variantInCartLabel(variantModalItem, variant.id)">
                                </span>
                            </span>
                            <span class="mt-0.5 block text-xs font-bold text-emerald-800 tabular-nums">
                                ₹<span x-text="parseFloat(variant.price).toFixed(2)"></span>
                            </span>
                            <span class="mt-0.5 block text-[11px] text-gray-500" x-show="variant.manage_stock">
                                {{ __('vendor.stock') }}: <span x-text="variant.stock"></span>
                            </span>
                        </span>
                    </label>
                </template>
            </div>

            <p x-show="variantModalError"
               x-text="variantModalError"
               x-cloak
               class="mt-3 text-sm text-red-600"
               role="alert"></p>

            <div class="mt-4 flex flex-col gap-2 border-t border-gray-200 pt-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <button type="button"
                            @click="closeVariantModal()"
                            class="{{ $btnOutlineNeutral ?? 'inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50' }} w-full sm:w-auto">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="button"
                            x-show="variantModalMode === 'change'"
                            x-cloak
                            @click="confirmVariantModal(false)"
                            class="{{ $btnPrimary ?? 'inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700' }} w-full sm:w-auto">
                        {{ __('vendor.save') }}
                    </button>
                    <button type="button"
                            x-show="variantModalMode === 'add' || variantModalMode === 'modify'"
                            @click="confirmVariantModal(true)"
                            :disabled="variantModalSelectionCount() < 1"
                            class="{{ $btnOutline ?? 'inline-flex h-10 items-center justify-center rounded-lg border border-emerald-300 bg-white px-4 text-sm font-semibold text-emerald-700 hover:bg-emerald-50' }} w-full disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                        <span x-show="variantModalMode === 'add'">{{ __('vendor.order_wizard_add_variants_continue') }}</span>
                        <span x-show="variantModalMode === 'modify'" x-cloak>{{ __('vendor.order_wizard_modify_variant_continue') }}</span>
                    </button>
                    <button type="button"
                            x-show="variantModalMode === 'add' || variantModalMode === 'modify'"
                            @click="confirmVariantModal(false)"
                            :disabled="variantModalSelectionCount() < 1"
                            class="{{ $btnPrimary ?? 'inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700' }} w-full disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                        <span x-show="variantModalMode === 'add'">{{ __('vendor.order_wizard_add_selected_variants') }}</span>
                        <span x-show="variantModalMode === 'modify'" x-cloak>{{ __('vendor.save') }}</span>
                        <span x-show="variantModalMode === 'add' && variantModalSelectionCount() > 0" x-cloak class="ml-1 tabular-nums" x-text="'('+variantModalSelectionCount()+')'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>
