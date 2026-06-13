{{-- Edit quantity & duration — order show line editor --}}
<div x-show="lineEditOpen"
     x-cloak
     class="fixed inset-0 z-[80] flex items-end justify-center p-2 sm:items-center sm:p-4"
     role="dialog"
     aria-modal="true"
     @keydown.escape.window="lineEditOpen && !lineEditSaving && closeLineEdit()">
    <div x-show="lineEditOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="absolute inset-0 bg-gray-900/55 backdrop-blur-[1px]"
         @click="!lineEditSaving && closeLineEdit()"></div>
    <div x-show="lineEditOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         class="relative z-10 w-full max-w-md overflow-hidden rounded-t-2xl bg-white shadow-2xl ring-1 ring-gray-900/5 sm:rounded-2xl"
         @click.stop>
        <div class="relative overflow-hidden border-b border-emerald-100 bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-600 px-4 py-4 text-white sm:px-5">
            <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-8 right-8 h-20 w-20 rounded-full bg-white/5"></div>
            <div class="relative flex items-start gap-3 pr-8">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                    <i class="fas fa-sliders-h text-sm" aria-hidden="true"></i>
                </div>
                <div class="min-w-0">
                    <h4 class="text-base font-bold leading-tight">{{ __('vendor.order_wizard_summary_edit_line') }}</h4>
                    <p class="mt-1 line-clamp-2 text-sm text-emerald-100" x-text="lineEditName"></p>
                </div>
            </div>
            <button type="button"
                    @click="!lineEditSaving && closeLineEdit()"
                    class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-white/80 transition hover:bg-white/15 hover:text-white"
                    :disabled="lineEditSaving"
                    aria-label="{{ __('vendor.modal_close_aria') }}">
                <i class="fas fa-times text-sm" aria-hidden="true"></i>
            </button>
        </div>

        <div class="space-y-4 px-4 py-4 sm:px-5 sm:py-5">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label for="order_line_edit_price" class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                        <i class="fas fa-rupee-sign text-[10px] text-emerald-600" aria-hidden="true"></i>
                        {{ __('vendor.price') }}
                    </label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-medium text-gray-500">₹</span>
                        <input id="order_line_edit_price"
                               type="number"
                               min="0"
                               step="0.01"
                               inputmode="decimal"
                               x-model="lineEditPrice"
                               :disabled="lineEditSaving"
                               class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50/50 pl-8 pr-3 text-sm font-medium text-gray-900 transition focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-60">
                    </div>
                </div>
                <div>
                    <label for="order_line_edit_rental" class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                        <i class="fas fa-clock text-[10px] text-emerald-600" aria-hidden="true"></i>
                        {{ __('vendor.rental_period') }}
                    </label>
                    <div class="relative">
                        <select id="order_line_edit_rental"
                                x-model="lineEditRentalPeriod"
                                @change="onLineEditRentalChange()"
                                :disabled="lineEditSaving"
                                class="h-11 w-full appearance-none rounded-xl border border-gray-200 bg-gray-50/50 px-3 pr-9 text-sm font-medium text-gray-900 transition focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-60">
                            <template x-for="(label, key) in rentalPeriods" :key="'order-edit-rp-'+key">
                                <option :value="key" x-text="label"></option>
                            </template>
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                            <i class="fas fa-chevron-down text-xs" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-700">
                    <i class="fas fa-cubes mr-1 text-[10px] text-emerald-600" aria-hidden="true"></i>
                    {{ __('vendor.quantity') }}
                </label>
                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="decrementLineEditQty()"
                            :disabled="lineEditSaving || lineEditQty <= 1"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-lg font-bold text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40">
                        −
                    </button>
                    <input id="order_line_edit_qty"
                           type="number"
                           min="1"
                           step="1"
                           x-model.number="lineEditQty"
                           :disabled="lineEditSaving"
                           class="h-11 min-w-0 flex-1 rounded-xl border border-gray-200 bg-gray-50/50 px-3 text-center text-lg font-bold tabular-nums text-gray-900 transition focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-60">
                    <button type="button"
                            @click="incrementLineEditQty()"
                            :disabled="lineEditSaving"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-lg font-bold text-emerald-800 transition hover:bg-emerald-100 disabled:opacity-40">
                        +
                    </button>
                </div>
            </div>

            <div x-show="lineEditUsesBilling" x-cloak x-transition>
                <label class="mb-2 block text-xs font-semibold text-gray-700">
                    <i class="fas fa-hourglass-half mr-1 text-[10px] text-emerald-600" aria-hidden="true"></i>
                    <span x-text="lineEditBillingLabel()"></span>
                </label>
                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="decrementLineEditBilling()"
                            :disabled="lineEditSaving"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-lg font-bold text-gray-700 transition hover:bg-gray-100 disabled:opacity-40">
                        −
                    </button>
                    <input id="order_line_edit_billing"
                           type="number"
                           step="0.01"
                           min="0.01"
                           lang="en"
                           x-model="lineEditBilling"
                           :disabled="lineEditSaving"
                           class="h-11 min-w-0 flex-1 rounded-xl border border-gray-200 bg-gray-50/50 px-3 text-center text-lg font-bold tabular-nums text-gray-900 transition focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:opacity-60">
                    <button type="button"
                            @click="incrementLineEditBilling()"
                            :disabled="lineEditSaving"
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-lg font-bold text-emerald-800 transition hover:bg-emerald-100 disabled:opacity-40">
                        +
                    </button>
                </div>
                <p class="mt-1.5 text-[11px] text-gray-500" x-text="lineEditBillingUnitShort()"></p>
            </div>

            <div class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50/50 px-4 py-3 ring-1 ring-emerald-100/80">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700/80">{{ __('vendor.order_wizard_summary_total_amount_label') }}</p>
                        <p class="mt-0.5 truncate text-xs text-gray-600">
                            <span x-text="lineEditRentalPeriodLabel()"></span>
                            · ₹<span x-text="formatRupeeInt(lineEditPrice)"></span>
                            × <span x-text="lineEditQty"></span>
                            <template x-if="lineEditUsesBilling && lineEditBilling">
                                <span> × <span x-text="formatBillingUnitsDisplay(lineEditBilling)"></span></span>
                            </template>
                        </p>
                    </div>
                    <p class="shrink-0 text-xl font-bold tabular-nums text-emerald-700 sm:text-2xl">
                        ₹<span x-text="formatRupeeInt(lineEditPreviewTotal())"></span>
                    </p>
                </div>
            </div>

            <p x-show="lineEditError"
               x-text="lineEditError"
               x-cloak
               class="rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-xs font-medium text-red-700"
               role="alert"></p>

            <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end sm:gap-3">
                <button type="button"
                        @click="closeLineEdit()"
                        :disabled="lineEditSaving"
                        class="inline-flex h-11 w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 disabled:opacity-50 sm:w-auto">
                    {{ __('vendor.cancel') }}
                </button>
                <button type="button"
                        @click="saveLineEdit()"
                        :disabled="lineEditSaving"
                        class="inline-flex h-11 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-50 sm:w-auto">
                    <i class="fas fa-spinner fa-spin text-xs" x-show="lineEditSaving" x-cloak></i>
                    <i class="fas fa-check text-xs" x-show="!lineEditSaving"></i>
                    <span x-show="!lineEditSaving">{{ __('vendor.save') }}</span>
                    <span x-show="lineEditSaving" x-cloak>{{ __('vendor.updating') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
