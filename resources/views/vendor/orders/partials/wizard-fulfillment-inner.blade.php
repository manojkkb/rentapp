@php
    use App\Support\OrderWizardDateTimeDefaults;

    $livewireWizard = $livewireWizard ?? false;
@endphp

@php
    $ft = old('fulfillment_type', $wizard['fulfillment_type'] ?? 'pickup');
    $defaultFulfillmentAt = OrderWizardDateTimeDefaults::format(OrderWizardDateTimeDefaults::defaultFulfillmentAt());
    $parseDateTime = function (?string $value): array {
        if (! $value) {
            return ['date' => '', 'time' => ''];
        }
        try {
            $dt = \Carbon\Carbon::parse($value);

            return ['date' => $dt->format('Y-m-d'), 'time' => $dt->format('H:i')];
        } catch (\Throwable $e) {
            return ['date' => '', 'time' => ''];
        }
    };
    $pickupAtValue = old('pickup_at', $wizard['pickup_at'] ?? '') ?: $defaultFulfillmentAt;
    $deliveryAtValue = old('delivery_at', $wizard['delivery_at'] ?? '') ?: $defaultFulfillmentAt;
    $pickupParts = $parseDateTime($pickupAtValue);
    $deliveryParts = $parseDateTime($deliveryAtValue);
    $delAddr = old('delivery_address', $wizard['delivery_address'] ?? '');
    $delCharge = old('delivery_charge', isset($wizard['delivery_charge']) ? (string) $wizard['delivery_charge'] : '0');
@endphp

<div class="w-full" data-wizard-alpine-root>
    <form id="orderWizard_fulfillment_form"
          @if(! ($livewireWizard ?? false)) action="{{ route('vendor.orders.create.fulfillment.store') }}" method="POST" @else onsubmit="event.preventDefault(); return false;" @endif
          class="space-y-4 rounded-xl border border-gray-200/90 bg-white p-3 shadow-sm sm:space-y-5 sm:p-4"
          @submit.prevent="submitFulfillmentStep()"
          @wizard-pickup-at-changed.window="pickupAt = $event.detail || ''; fulfillmentStepError = ''"
          x-data="{
              livewireWizard: @json($livewireWizard ?? false),
              fulfillment: @js($ft),
              deliveryAddress: @js($delAddr),
              pickupAt: @js($pickupAtValue),
              fulfillmentStepError: '',
              collectPayload() {
                  window.dispatchEvent(new CustomEvent('sync-fulfillment-datetimes'));
                  return {
                      fulfillment_type: this.fulfillment,
                      delivery_address: this.deliveryAddress,
                      pickup_at: document.getElementById('pickup_at')?.value || '',
                      delivery_at: document.getElementById('delivery_at')?.value || '',
                      delivery_charge: document.querySelector('[name=delivery_charge]')?.value || '0',
                  };
              },
              get canContinue() {
                  if (this.fulfillment === 'delivery') {
                      return (this.deliveryAddress || '').trim() !== '';
                  }
                  if (this.fulfillment === 'pickup') {
                      const hidden = document.getElementById('pickup_at')?.value || this.pickupAt || '';
                      return hidden.trim() !== '';
                  }
                  return false;
              },
              submitFulfillmentStep() {
                  window.dispatchEvent(new CustomEvent('sync-fulfillment-datetimes'));
                  this.pickupAt = document.getElementById('pickup_at')?.value || this.pickupAt || '';
                  if (!this.canContinue) {
                      this.fulfillmentStepError = this.fulfillment === 'delivery'
                          ? @js(__('vendor.delivery_address_required'))
                          : @js(__('vendor.pickup_datetime_required'));
                      return;
                  }
                  this.fulfillmentStepError = '';
                  if (!this.livewireWizard) {
                      this.$el.submit();
                      return;
                  }
                  if (!this.$wire?.saveFulfillment) {
                      this.fulfillmentStepError = @js(__('vendor.order_wizard_session_expired'));
                      return;
                  }
                  this.$wire.saveFulfillment(this.collectPayload());
              },
          }">
        @if(! ($livewireWizard ?? false)) @csrf @endif

        <div>
            <span class="mb-2 block text-sm font-bold text-gray-900">{{ __('vendor.fulfillment_method') }}</span>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-2">
                <label class="flex h-10 cursor-pointer items-center gap-2.5 rounded-lg border px-3 transition [touch-action:manipulation] active:scale-[0.99] sm:h-11 sm:gap-3 sm:px-3.5"
                       :class="fulfillment === 'pickup' ? 'border-emerald-500 bg-emerald-50/80 ring-1 ring-emerald-500/25' : 'border-gray-200 bg-gray-50/50 hover:border-gray-300'">
                    <input type="radio" name="fulfillment_type" value="pickup" class="h-4 w-4 shrink-0 text-emerald-600 focus:ring-emerald-500 sm:h-[18px] sm:w-[18px]" x-model="fulfillment" @change="fulfillmentStepError = ''; $nextTick(() => window.dispatchEvent(new CustomEvent('remount-fulfillment-datetimes')))">
                    <span class="text-sm font-semibold text-gray-900 sm:text-base">{{ __('vendor.pickup') }}</span>
                </label>
                <label class="flex h-10 cursor-pointer items-center gap-2.5 rounded-lg border px-3 transition [touch-action:manipulation] active:scale-[0.99] sm:h-11 sm:gap-3 sm:px-3.5"
                       :class="fulfillment === 'delivery' ? 'border-emerald-500 bg-emerald-50/80 ring-1 ring-emerald-500/25' : 'border-gray-200 bg-gray-50/50 hover:border-gray-300'">
                    <input type="radio" name="fulfillment_type" value="delivery" class="h-4 w-4 shrink-0 text-emerald-600 focus:ring-emerald-500 sm:h-[18px] sm:w-[18px]" x-model="fulfillment" @change="fulfillmentStepError = ''; $nextTick(() => window.dispatchEvent(new CustomEvent('remount-fulfillment-datetimes')))">
                    <span class="text-sm font-semibold text-gray-900 sm:text-base">{{ __('vendor.delivery') }}</span>
                </label>
            </div>
            @error('fulfillment_type')
                <p class="mt-1.5 text-xs text-red-600 sm:text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="fulfillment === 'pickup'" x-cloak>
            @include('vendor.orders.partials.order-fulfillment-datetime-fields', [
                'prefix' => 'pickup',
                'parts' => $pickupParts,
                'hiddenValue' => $pickupAtValue,
                'restrictPastDates' => true,
                'changeEvent' => 'wizard-pickup-at-changed',
                'label' => __('vendor.pickup_datetime'),
                'help' => __('vendor.pickup_datetime_help'),
                'required' => true,
            ])
            @error('pickup_at')
                <p class="mt-0.5 text-xs text-red-600 sm:text-sm">{{ $message }}</p>
            @enderror
        </div>

        <div x-show="fulfillment === 'delivery'" x-cloak class="space-y-3">
            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-800 sm:text-sm">{{ __('vendor.delivery_address') }} <span class="text-red-500">*</span></label>
                <textarea name="delivery_address"
                          rows="3"
                          x-model="deliveryAddress"
                          @input="fulfillmentStepError = ''"
                          class="min-h-[5.5rem] w-full rounded-lg border border-gray-300 bg-white px-2.5 py-2 text-sm text-gray-900 shadow-inner focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 @error('delivery_address') border-red-500 @enderror sm:min-h-[6rem]"></textarea>
                @error('delivery_address')
                    <p class="mt-0.5 text-xs text-red-600 sm:text-sm">{{ $message }}</p>
                @enderror
            </div>

            @include('vendor.orders.partials.order-fulfillment-datetime-fields', [
                'prefix' => 'delivery',
                'parts' => $deliveryParts,
                'hiddenValue' => $deliveryAtValue,
                'restrictPastDates' => true,
                'changeEvent' => null,
                'label' => __('vendor.delivery_datetime'),
                'help' => __('vendor.delivery_datetime_help'),
                'required' => false,
            ])
            @error('delivery_at')
                <p class="mt-0.5 text-xs text-red-600 sm:text-sm">{{ $message }}</p>
            @enderror

            <div>
                <label class="mb-1 block text-xs font-semibold text-gray-800 sm:text-sm">{{ __('vendor.delivery_charge') }}</label>
                <input type="number" name="delivery_charge" step="0.01" min="0" value="{{ $delCharge }}"
                       inputmode="decimal"
                       class="h-10 w-full max-w-none rounded-lg border border-gray-300 bg-white px-2.5 text-sm text-gray-900 shadow-inner focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25 @error('delivery_charge') border-red-500 @enderror sm:max-w-xs">
                @error('delivery_charge')
                    <p class="mt-0.5 text-xs text-red-600 sm:text-sm">{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if($errors->has('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800 sm:text-sm">{{ $errors->first('error') }}</div>
        @endif

        <x-order-wizard-actions class="border-t border-gray-200 pt-3 sm:pt-3">
            @if($livewireWizard ?? false)
                <button type="button" wire:click="goToStep(3)"
                        class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-emerald-700 [touch-action:manipulation] sm:mr-auto">
                    <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                    {{ __('vendor.back') }}
                </button>
            @else
            <a href="{{ route('vendor.orders.create.summary') }}"
               class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-emerald-700 [touch-action:manipulation] sm:mr-auto">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                {{ __('vendor.back') }}
            </a>
            @endif
            <div class="flex w-full flex-col items-stretch gap-2 sm:w-auto sm:items-end">
                <p x-show="fulfillmentStepError"
                   x-text="fulfillmentStepError"
                   x-cloak
                   class="text-center text-xs font-medium text-red-600 sm:text-right sm:text-sm"
                   role="alert"></p>
                <button type="button"
                        @click="submitFulfillmentStep()"
                        wire:loading.attr="disabled"
                        wire:target="saveFulfillment"
                        :aria-disabled="!canContinue"
                        :class="canContinue
                            ? 'inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition [touch-action:manipulation] hover:bg-emerald-700 active:bg-emerald-800 disabled:opacity-70 sm:w-auto sm:min-w-[8rem]'
                            : 'inline-flex h-10 w-full cursor-not-allowed items-center justify-center gap-1.5 rounded-lg bg-gray-300 px-4 text-sm font-semibold text-gray-500 shadow-none transition [touch-action:manipulation] sm:w-auto sm:min-w-[8rem]'">
                    <span wire:loading.remove wire:target="saveFulfillment">
                        <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                        {{ __('vendor.order_wizard_continue_payment') }}
                    </span>
                    <span wire:loading wire:target="saveFulfillment">
                        <i class="fas fa-spinner fa-spin text-xs" aria-hidden="true"></i>
                    </span>
                </button>
            </div>
        </x-order-wizard-actions>
    </form>
</div>
