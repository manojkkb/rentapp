<form action="{{ route('vendor.store.delivery.update') }}" method="POST"
      x-data="{ submitting: false }" @submit="submitting = true" class="space-y-5">
    @csrf
    @method('PUT')

    <p class="text-sm text-gray-600">{{ __('vendor.store_delivery_help') }}</p>

    <div class="grid gap-3 sm:grid-cols-2">
        <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-4">
            <input type="checkbox" name="pickup_enabled" value="1" @checked(old('pickup_enabled', $store->pickup_enabled))
                   class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span>
                <span class="block text-sm font-semibold text-gray-900">{{ __('vendor.pickup') }}</span>
                <span class="text-xs text-gray-500">{{ __('vendor.store_pickup_help') }}</span>
            </span>
        </label>
        <label class="flex items-center gap-3 rounded-lg border border-gray-200 p-4">
            <input type="checkbox" name="delivery_enabled" value="1" @checked(old('delivery_enabled', $store->delivery_enabled))
                   class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            <span>
                <span class="block text-sm font-semibold text-gray-900">{{ __('vendor.delivery') }}</span>
                <span class="text-xs text-gray-500">{{ __('vendor.store_delivery_option_help') }}</span>
            </span>
        </label>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="default_delivery_charge" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_default_delivery_charge') }}</label>
            <input type="number" step="0.01" min="0" id="default_delivery_charge" name="default_delivery_charge"
                   value="{{ old('default_delivery_charge', $store->default_delivery_charge ?? 0) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
            <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_default_delivery_charge_help') }}</p>
        </div>
        <div>
            <label for="free_delivery_min_amount" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_free_delivery_min') }}</label>
            <input type="number" step="0.01" min="0" id="free_delivery_min_amount" name="free_delivery_min_amount"
                   value="{{ old('free_delivery_min_amount', $store->free_delivery_min_amount) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
            <p class="mt-1 text-xs text-gray-500">{{ __('vendor.store_free_delivery_min_help') }}</p>
        </div>
    </div>

    @include('vendor.store.partials.save-button')
</form>
