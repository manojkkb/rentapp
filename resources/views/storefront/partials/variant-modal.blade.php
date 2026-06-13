<div x-show="showVariantModal" x-cloak
     class="fixed inset-0 z-[80] flex items-end justify-center sm:items-center sm:p-4"
     role="dialog" aria-modal="true" @keydown.escape.window="closeVariantModal()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[1px]" @click="closeVariantModal()" aria-hidden="true"></div>

    <div class="relative z-10 flex max-h-[92dvh] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl border border-gray-200 bg-white shadow-2xl sm:rounded-2xl"
         @click.stop>
        <div class="border-b border-gray-100 store-accent-bg-soft px-4 py-4 sm:px-5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="text-base font-bold text-gray-900 sm:text-lg">{{ __('vendor.store_choose_variant') }}</h3>
                    <p class="mt-0.5 truncate text-sm text-gray-600" x-text="variantModalItem?.name || ''"></p>
                </div>
                <button type="button" @click="closeVariantModal()" class="rounded-lg p-2 text-gray-500 hover:bg-white" aria-label="{{ __('vendor.cancel') }}">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-3 py-3 sm:px-4">
            <div class="space-y-2">
                <template x-for="variant in (variantModalItem?.variants || [])" :key="'sv-'+variant.id">
                    <button type="button"
                            class="flex w-full items-center gap-3 rounded-xl border px-3 py-3 text-left transition"
                            :class="String(variantModalPick) === String(variant.id)
                                ? 'border-[var(--store-accent)] store-accent-bg-soft ring-2 ring-[var(--store-accent-ring)]'
                                : (variantSelectable(variant)
                                    ? 'border-gray-200 bg-white hover:border-gray-300'
                                    : 'cursor-not-allowed border-gray-100 bg-gray-50 opacity-60')"
                            :disabled="!variantSelectable(variant)"
                            @click="variantSelectable(variant) && (variantModalPick = String(variant.id))">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                              :class="String(variantModalPick) === String(variant.id) ? 'store-accent-bg border-[var(--store-accent)] text-white' : 'border-gray-300 bg-white'">
                            <i x-show="String(variantModalPick) === String(variant.id)" class="fas fa-check text-[10px]" aria-hidden="true"></i>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="text-sm font-semibold text-gray-900" x-text="variant.label || variant.variant_code"></span>
                        </span>
                        <span class="shrink-0 text-sm font-bold store-accent-text-dark">
                            ₹<span x-text="Math.round(parseFloat(variant.price)).toLocaleString('en-IN')"></span>
                        </span>
                    </button>
                </template>
            </div>
            <p x-show="variantModalError" x-text="variantModalError" x-cloak class="mt-3 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-sm text-red-700"></p>
        </div>

        <div class="border-t border-gray-200 bg-gray-50 px-4 py-4 sm:px-5">
            <div class="flex gap-2">
                <button type="button" @click="closeVariantModal()"
                        class="{{ $theme['classes']['btn'] }} flex h-11 flex-1 items-center justify-center border border-gray-200 bg-white text-sm font-semibold text-gray-700">
                    {{ __('vendor.cancel') }}
                </button>
                <button type="button" @click="confirmVariantAdd()" :disabled="!variantModalPick"
                        class="{{ $theme['classes']['btn'] }} store-btn-primary flex h-11 flex-1 items-center justify-center text-sm font-bold disabled:opacity-50">
                    {{ __('vendor.store_add_to_cart') }}
                </button>
            </div>
        </div>
    </div>
</div>
