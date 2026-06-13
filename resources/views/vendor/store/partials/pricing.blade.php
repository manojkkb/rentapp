<form action="{{ route('vendor.store.pricing.update') }}" method="POST"
      x-data="{
          submitting: false,
          depositType: @js(old('default_security_deposit_type', $store->default_security_deposit_type ?? 'none')),
          showValue() { return this.depositType !== 'none'; },
          valueLabel() {
              if (this.depositType === 'fixed_amount') return @js(__('vendor.order_wizard_sd_type_fixed'));
              if (this.depositType === 'order_amount') return @js(__('vendor.order_wizard_sd_type_order_pct'));
              if (this.depositType === 'product_security_deposit') return @js(__('vendor.order_wizard_sd_type_product_pct'));
              return '';
          }
      }"
      @submit="submitting = true"
      class="space-y-5">
    @csrf
    @method('PUT')

    <p class="text-sm text-gray-600">{{ __('vendor.store_pricing_help') }}</p>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="lg:col-span-2">
            <label class="mb-2 block text-sm font-semibold text-gray-800">{{ __('vendor.store_default_security_deposit') }}</label>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($depositTypes as $type)
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2.5 text-sm transition"
                           :class="depositType === @js($type) ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="default_security_deposit_type" value="{{ $type }}"
                               x-model="depositType" class="text-emerald-600 focus:ring-emerald-500">
                        <span>
                            @switch($type)
                                @case('none') {{ __('vendor.order_wizard_sd_type_none') }} @break
                                @case('order_amount') {{ __('vendor.order_wizard_sd_type_order_pct') }} @break
                                @case('product_security_deposit') {{ __('vendor.order_wizard_sd_type_product_pct') }} @break
                                @case('fixed_amount') {{ __('vendor.order_wizard_sd_type_fixed') }} @break
                            @endswitch
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div x-show="showValue()" x-cloak>
            <label for="default_security_deposit_value" class="mb-1 block text-sm font-semibold text-gray-800">
                <span x-text="valueLabel()"></span> <span class="text-red-500">*</span>
            </label>
            <input type="number" step="0.01" min="0" id="default_security_deposit_value" name="default_security_deposit_value"
                   value="{{ old('default_security_deposit_value', $store->default_security_deposit_value) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>

        <div>
            <label for="min_order_amount" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_min_order_amount') }}</label>
            <input type="number" step="0.01" min="0" id="min_order_amount" name="min_order_amount"
                   value="{{ old('min_order_amount', $store->min_order_amount) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3">
            <input type="checkbox" name="show_prices_online" value="1" @checked(old('show_prices_online', $store->show_prices_online))
                   class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm font-medium text-gray-800">{{ __('vendor.store_show_prices') }}</span>
        </label>
        <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-3">
            <input type="checkbox" name="show_gst_online" value="1" @checked(old('show_gst_online', $store->show_gst_online))
                   class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span class="text-sm font-medium text-gray-800">{{ __('vendor.store_show_gst') }}</span>
        </label>
    </div>

    @include('vendor.store.partials.save-button')
</form>
