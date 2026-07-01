@php
    $livewireWizard = $livewireWizard ?? false;
@endphp


<div class="w-full" x-data="orderWizardPayment(@js($paymentPreview))" data-wizard-alpine-root>
    <div class="mb-3 rounded-xl border border-gray-200/90 bg-white p-3 text-sm text-gray-700 shadow-sm sm:mb-4 sm:p-4">
        <div class="flex items-center justify-between gap-2 border-b border-gray-100 pb-2">
            <p class="text-sm font-bold text-gray-900">{{ __('vendor.order_wizard_step_payment') }}</p>
            <span class="text-[10px] font-bold uppercase tracking-wide text-gray-400">{{ __('vendor.order_wizard_payment_amounts') }}</span>
        </div>
        <dl class="mt-2 space-y-1.5 text-[13px] sm:text-sm">
            <div class="flex justify-between gap-2">
                <dt class="text-gray-600">{{ __('vendor.sub_total') }}</dt>
                <dd class="font-semibold tabular-nums text-gray-900">₹{{ number_format($paymentPreview['sub_total'], 2) }}</dd>
            </div>
            @if(($paymentPreview['delivery_charge'] ?? 0) > 0.0001)
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-600">{{ __('vendor.delivery_charge') }}</dt>
                    <dd class="font-semibold tabular-nums text-gray-900">₹{{ number_format($paymentPreview['delivery_charge'], 2) }}</dd>
                </div>
            @endif
            <div class="flex justify-between gap-2 border-t border-gray-100 pt-1.5">
                <dt class="font-semibold text-gray-900">{{ __('vendor.grand_total') }}</dt>
                <dd class="font-bold tabular-nums text-emerald-700">₹{{ number_format($paymentPreview['grand_total'], 2) }}</dd>
            </div>
            <div class="flex items-start justify-between gap-2 border-t border-gray-100 pt-1.5">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                        <dt class="text-gray-600">{{ __('vendor.quote_security_deposit') }}</dt>
                        <button type="button" @click="openDepositModal()"
                                class="text-[11px] font-semibold text-emerald-700 underline decoration-emerald-600/40 underline-offset-2 hover:text-emerald-800 sm:text-xs">
                            {{ $paymentPreview['sd_labels']['configure'] ?? __('vendor.order_wizard_security_deposit_configure') }}
                        </button>
                    </div>
                    <p class="mt-0.5 truncate text-[11px] text-gray-500 sm:text-xs" x-text="depositRuleSummary()"></p>
                </div>
                <dd class="shrink-0 font-semibold tabular-nums text-gray-900">₹<span x-text="fmt(depositAmount())">0.00</span></dd>
            </div>
            <div class="flex justify-between gap-2 rounded-lg bg-emerald-50/80 px-2.5 py-2 ring-1 ring-emerald-100">
                <dt class="text-xs font-bold text-emerald-900 sm:text-sm">{{ __('vendor.order_wizard_total_payable') }}</dt>
                <dd class="text-base font-bold tabular-nums text-emerald-800 sm:text-lg">₹<span x-text="fmt(totalPayable())">0.00</span></dd>
            </div>
        </dl>
        <p class="mt-1.5 line-clamp-2 text-[10px] leading-snug text-gray-500 sm:text-[11px]">{{ __('vendor.order_wizard_total_payable_help') }}</p>
    </div>

    <div x-show="depositModalOpen" x-cloak class="fixed inset-0 z-50 flex items-end justify-center p-2 sm:items-center sm:p-4" role="dialog" aria-modal="true"
         @keydown.escape.window="closeDepositModal()">
        <div class="absolute inset-0 bg-gray-900/50" @click="closeDepositModal()"></div>
        <div class="relative z-10 w-full max-w-md overflow-hidden rounded-t-2xl bg-white shadow-xl ring-1 ring-gray-900/5 sm:rounded-2xl" @click.stop>
            <div class="border-b border-gray-100 px-3 py-2.5 sm:px-4 sm:py-3">
                <h2 class="text-base font-bold leading-tight text-gray-900 sm:text-lg">{{ $paymentPreview['sd_labels']['modal_title'] ?? __('vendor.order_wizard_security_deposit_modal_title') }}</h2>
                <p class="mt-0.5 line-clamp-2 text-[11px] leading-snug text-gray-600 sm:text-xs">{{ $paymentPreview['sd_labels']['modal_subtitle'] ?? __('vendor.order_wizard_security_deposit_modal_subtitle') }}</p>
            </div>
            <div class="px-3 py-3 sm:px-4 sm:py-3">
                <div class="grid grid-cols-2 gap-1.5">
                    <label class="flex min-h-[2.75rem] cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-2 py-1.5 transition hover:border-emerald-400 hover:bg-emerald-50/50">
                        <input type="radio" class="h-3.5 w-3.5 shrink-0 text-emerald-600" value="none" x-model="modalDepositType">
                        <span class="text-[11px] font-semibold leading-tight text-gray-900 sm:text-xs">{{ __('vendor.order_wizard_sd_type_none') }}</span>
                    </label>
                    <label class="flex min-h-[2.75rem] cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-2 py-1.5 transition hover:border-emerald-400 hover:bg-emerald-50/50">
                        <input type="radio" class="h-3.5 w-3.5 shrink-0 text-emerald-600" value="order_amount" x-model="modalDepositType">
                        <span class="text-[11px] font-semibold leading-tight text-gray-900 sm:text-xs">{{ __('vendor.order_wizard_sd_type_order_pct') }}</span>
                    </label>
                    <label class="flex min-h-[2.75rem] cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-2 py-1.5 transition hover:border-emerald-400 hover:bg-emerald-50/50">
                        <input type="radio" class="h-3.5 w-3.5 shrink-0 text-emerald-600" value="product_security_deposit" x-model="modalDepositType">
                        <span class="text-[11px] font-semibold leading-tight text-gray-900 sm:text-xs">{{ __('vendor.order_wizard_sd_type_product_pct') }}</span>
                    </label>
                    <label class="flex min-h-[2.75rem] cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-2 py-1.5 transition hover:border-emerald-400 hover:bg-emerald-50/50">
                        <input type="radio" class="h-3.5 w-3.5 shrink-0 text-emerald-600" value="fixed_amount" x-model="modalDepositType">
                        <span class="text-[11px] font-semibold leading-tight text-gray-900 sm:text-xs">{{ __('vendor.order_wizard_sd_type_fixed') }}</span>
                    </label>
                </div>
                <div class="mt-2.5 flex flex-col gap-1.5 sm:flex-row sm:items-end sm:gap-3" x-show="modalDepositType !== 'none'" x-cloak>
                    <div class="min-w-0 flex-1">
                        <label for="wizard_security_deposit_value_modal" class="mb-1 block text-[11px] font-semibold text-gray-800 sm:text-xs">
                            {{ __('vendor.order_wizard_sd_value') }}
                            <span class="font-normal text-gray-500" x-text="modalDepositType === 'fixed_amount' ? '(₹)' : '(%)'"></span>
                        </label>
                        <input type="number" id="wizard_security_deposit_value_modal" step="0.01" min="0"
                               class="h-10 w-full max-w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm text-gray-900 shadow-inner focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 sm:max-w-[12rem]"
                               x-model="modalDepositValue"
                               :disabled="modalDepositType === 'none'"
                               placeholder="0">
                    </div>
                </div>
            </div>
            <div class="flex flex-col-reverse gap-1.5 border-t border-gray-100 bg-gray-50/90 px-3 py-2.5 sm:flex-row sm:justify-end sm:gap-2 sm:px-4">
                <button type="button" @click="closeDepositModal()"
                        class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-white px-3 text-sm font-semibold text-gray-800 ring-1 ring-gray-200 transition hover:bg-gray-50 sm:w-auto sm:min-w-[5.5rem]">
                    {{ __('vendor.cancel') }}
                </button>
                <button type="button" @click="applyDepositModal()"
                        class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 sm:w-auto sm:min-w-[5.5rem]">
                    {{ __('vendor.order_wizard_security_deposit_modal_apply') }}
                </button>
            </div>
        </div>
    </div>

    <form @if(! ($livewireWizard ?? false)) action="{{ route('vendor.orders.create.complete') }}" method="POST" @endif
          class="space-y-4 rounded-xl border border-gray-200/90 bg-white p-3 shadow-sm sm:space-y-5 sm:p-4"
          @if($livewireWizard ?? false)
          @submit.prevent="
              const f = $event.target;
              $wire.placeOrder({
                  initial_payment_amount: f.initial_payment_amount?.value || '',
                  initial_payment_method: f.initial_payment_method?.value || '',
                  security_deposit_payment_amount: f.security_deposit_payment_amount?.value || '',
                  security_deposit_payment_method: f.security_deposit_payment_method?.value || '',
                  security_deposit_type: depositType,
                  security_deposit_value: depositType === 'none' ? '' : depositValue,
              })
          "
          @endif>
        @if(! ($livewireWizard ?? false)) @csrf @endif
        <input type="hidden" name="security_deposit_type" x-bind:value="depositType">
        <input type="hidden" name="security_deposit_value" x-bind:value="depositType === 'none' ? '' : depositValue">

        <div>
            <h3 class="text-sm font-bold text-gray-900">{{ __('vendor.order_wizard_initial_payment') }}</h3>
            <p class="mt-0.5 line-clamp-2 text-xs text-gray-500">{{ __('vendor.order_wizard_initial_payment_help') }}</p>
            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-3">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-800 sm:text-sm">{{ __('vendor.order_wizard_amount') }}</label>
                    <input type="number" name="initial_payment_amount" step="0.01" min="0" value="{{ old('initial_payment_amount') }}"
                           inputmode="decimal"
                           class="h-10 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm text-gray-900 shadow-inner focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 @error('initial_payment_amount') border-red-500 @enderror"
                           placeholder="0.00">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-gray-800 sm:text-sm">{{ __('vendor.order_wizard_payment_method') }}</label>
                    <select name="initial_payment_method" class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-white px-2.5 text-sm text-gray-900 shadow-inner focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 @error('initial_payment_method') border-red-500 @enderror">
                        <option value="">{{ __('vendor.order_wizard_select_method') }}</option>
                        <option value="Cash" @selected(old('initial_payment_method') === 'Cash')>Cash</option>
                        <option value="UPI" @selected(old('initial_payment_method') === 'UPI')>UPI</option>
                        <option value="Card" @selected(old('initial_payment_method') === 'Card')>Card</option>
                        <option value="Bank transfer" @selected(old('initial_payment_method') === 'Bank transfer')>Bank transfer</option>
                    </select>
                </div>
            </div>
        </div>

        <template x-if="depositAmount() > 0.009">
            <div class="border-t border-gray-100 pt-3">
                <h3 class="text-sm font-bold text-gray-900">{{ __('vendor.order_wizard_sd_payment_received') }}</h3>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-800 sm:text-sm">{{ __('vendor.order_wizard_amount') }}</label>
                        <input type="number" name="security_deposit_payment_amount" step="0.01" min="0"
                               x-bind:max="depositAmount()"
                               value="{{ old('security_deposit_payment_amount') }}"
                               inputmode="decimal"
                               class="h-10 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm text-gray-900 shadow-inner focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 @error('security_deposit_payment_amount') border-red-500 @enderror"
                               placeholder="0.00">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-gray-800 sm:text-sm">{{ __('vendor.order_wizard_payment_method') }}</label>
                        <select name="security_deposit_payment_method"
                                class="h-10 w-full appearance-none rounded-lg border border-gray-300 bg-white px-2.5 text-sm text-gray-900 shadow-inner focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 @error('security_deposit_payment_method') border-red-500 @enderror">
                            <option value="">{{ __('vendor.order_wizard_select_method') }}</option>
                            <option value="Cash" @selected(old('security_deposit_payment_method') === 'Cash')>Cash</option>
                            <option value="UPI" @selected(old('security_deposit_payment_method') === 'UPI')>UPI</option>
                            <option value="Card" @selected(old('security_deposit_payment_method') === 'Card')>Card</option>
                            <option value="Bank transfer" @selected(old('security_deposit_payment_method') === 'Bank transfer')>Bank transfer</option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <x-order-wizard-actions class="border-t border-gray-200 pt-3 sm:pt-3">
            @if($livewireWizard ?? false)
                <button type="button" wire:click="goToStep(4)"
                        class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-emerald-700 [touch-action:manipulation] sm:mr-auto">
                    <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                    {{ __('vendor.back') }}
                </button>
            @else
            <a href="{{ route('vendor.orders.create.fulfillment') }}"
               class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-emerald-700 [touch-action:manipulation] sm:mr-auto">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                {{ __('vendor.back') }}
            </a>
            @endif
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="placeOrder"
                    class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition [touch-action:manipulation] hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-70 sm:w-auto sm:min-w-[8rem]">
                <span wire:loading.remove wire:target="placeOrder">
                    <i class="fas fa-check text-xs" aria-hidden="true"></i>
                    {{ __('vendor.order_wizard_place_order') }}
                </span>
                <span wire:loading wire:target="placeOrder">
                    <i class="fas fa-spinner fa-spin text-xs"></i>
                </span>
            </button>
        </x-order-wizard-actions>
    </form>
</div>
