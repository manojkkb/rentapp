{{-- Variant picker — tap entire row to select --}}
<template x-teleport="body">
<div x-show="showVariantModal"
     x-cloak
     class="fixed inset-0 z-[70] flex items-end justify-center p-0 sm:items-center sm:p-4"
     role="dialog"
     aria-modal="true"
     aria-labelledby="orderWizardVariantPickerTitle"
     @keydown.escape.window="closeVariantModal()">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px]"
         @click="closeVariantModal()"
         aria-hidden="true"></div>

    <div class="relative z-10 flex max-h-[92dvh] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl border border-gray-200 bg-white shadow-2xl sm:max-h-[90vh] sm:rounded-2xl"
         @click.stop>

        <div class="border-b border-gray-100 bg-gradient-to-b from-emerald-50/60 to-white px-4 py-3.5 sm:px-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200/80">
                            <i class="fas fa-layer-group text-sm" aria-hidden="true"></i>
                        </span>
                        <div class="min-w-0">
                            <h3 id="orderWizardVariantPickerTitle" class="text-base font-bold leading-tight text-gray-900 sm:text-lg">
                                <span x-show="variantModalMode === 'add'">{{ __('vendor.order_wizard_choose_variant_heading') }}</span>
                                <span x-show="variantModalMode === 'modify'" x-cloak>{{ __('vendor.order_wizard_select_variant_heading') }}</span>
                                <span x-show="variantModalMode === 'change'" x-cloak>{{ __('vendor.order_wizard_edit_variant_heading') }}</span>
                            </h3>
                            <p class="mt-0.5 truncate text-xs text-gray-500 sm:text-sm" x-text="variantModalItem?.name || ''"></p>
                        </div>
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-gray-600 sm:text-sm">
                        <span x-show="variantModalMode === 'add'">{{ __('vendor.order_wizard_choose_variant_hint') }}</span>
                        <span x-show="variantModalMode === 'modify'" x-cloak>{{ __('vendor.order_wizard_modify_variant_hint') }}</span>
                        <span x-show="variantModalMode === 'change'" x-cloak>{{ __('vendor.order_wizard_select_variant') }}</span>
                    </p>
                    <p x-show="variantModalMode !== 'change' && variantModalSelectionCount() > 0"
                       x-cloak
                       class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-800 ring-1 ring-emerald-200/70">
                        <i class="fas fa-check-circle text-[10px]" aria-hidden="true"></i>
                        <span x-text="variantModalSelectionCount()"></span>
                        <span>{{ __('vendor.order_wizard_variants_selected') }}</span>
                    </p>
                </div>
                <button type="button"
                        @click="closeVariantModal()"
                        class="shrink-0 rounded-xl p-2 text-gray-500 transition hover:bg-white hover:text-gray-800"
                        :aria-label="@json(__('vendor.cancel'))">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto overscroll-y-contain px-3 py-3 sm:px-4 sm:py-4">
            <div class="space-y-2">
                <template x-for="variant in (variantModalItem?.variants || [])" :key="'vm-'+variant.id">
                    <button type="button"
                            class="flex w-full items-center gap-3 rounded-xl border px-3 py-3 text-left transition [touch-action:manipulation] sm:gap-3.5 sm:px-3.5 sm:py-3.5"
                            :class="isVariantModalRowSelected(variant)
                                ? 'border-emerald-500 bg-emerald-50 shadow-sm ring-2 ring-emerald-500/20'
                                : (variantSelectable(variant)
                                    ? 'border-gray-200 bg-white hover:border-emerald-300 hover:bg-emerald-50/30 active:bg-emerald-50/50'
                                    : 'cursor-not-allowed border-gray-100 bg-gray-50 opacity-60')"
                            :disabled="!variantSelectable(variant)"
                            :aria-pressed="isVariantModalRowSelected(variant)"
                            @click="handleVariantRowClick(variant)">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 transition sm:h-6 sm:w-6"
                              :class="isVariantModalRowSelected(variant)
                                  ? 'border-emerald-600 bg-emerald-600 text-white'
                                  : (variantSelectable(variant) ? 'border-gray-300 bg-white' : 'border-gray-200 bg-gray-100')">
                            <i x-show="isVariantModalRowSelected(variant)"
                               class="fas fa-check text-[10px] sm:text-xs"
                               aria-hidden="true"></i>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="text-sm font-semibold leading-snug text-gray-900 sm:text-[15px]"
                                      x-text="variant.label || variant.variant_code"></span>
                                <span x-show="variantModalItem && variantQtyInOrder(variantModalItem, variant.id) >= 1 && variantModalMode === 'add'"
                                      x-cloak
                                      class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-800 ring-1 ring-emerald-200/60"
                                      x-text="variantInCartLabel(variantModalItem, variant.id)">
                                </span>
                                <span x-show="!variantSelectable(variant)"
                                      x-cloak
                                      class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 ring-1 ring-gray-200/80">
                                    {{ __('vendor.unavailable') }}
                                </span>
                            </span>
                            <span class="mt-0.5 block text-[11px] font-medium text-blue-600 tabular-nums sm:text-xs"
                                  x-show="variant.manage_stock"
                                  x-text="availableStockLabel(variantAvailableStock(variantModalItem, variant))"></span>
                        </span>

                        <span class="shrink-0 text-right">
                            <span class="block text-sm font-bold tabular-nums text-emerald-800 sm:text-base">
                                ₹<span x-text="parseFloat(variant.price).toFixed(2)"></span>
                            </span>
                        </span>
                    </button>
                </template>
            </div>

            <p x-show="variantModalError"
               x-text="variantModalError"
               x-cloak
               class="mt-3 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-sm text-red-700"
               role="alert"></p>
        </div>

        <div class="border-t border-gray-200 bg-gray-50/80 px-3 py-3 sm:px-4 sm:py-4">
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
                        :disabled="!variantModalPick"
                        class="{{ $btnPrimary ?? 'inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700' }} w-full disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
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
</template>
