<form action="{{ route('vendor.store.address.update') }}" method="POST"
      x-data="{ submitting: false }" @submit="submitting = true" class="space-y-5">
    @csrf
    @method('PUT')

    <p class="text-sm text-gray-600">{{ __('vendor.store_address_help') }}</p>

    <div class="grid gap-5 lg:grid-cols-2">
        <div class="lg:col-span-2">
            <label for="address_line1" class="mb-1 block text-sm font-semibold text-gray-800">
                {{ __('vendor.address_line1') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" id="address_line1" name="address_line1" required
                   value="{{ old('address_line1', $vendor->address_line1) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>

        <div class="lg:col-span-2">
            <label for="address_line2" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.address_line2') }}</label>
            <input type="text" id="address_line2" name="address_line2"
                   value="{{ old('address_line2', $vendor->address_line2) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>

        <div>
            <label for="city" class="mb-1 block text-sm font-semibold text-gray-800">
                {{ __('vendor.city') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" id="city" name="city" required
                   value="{{ old('city', $vendor->city) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>

        <div>
            <label for="state" class="mb-1 block text-sm font-semibold text-gray-800">
                {{ __('vendor.state') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" id="state" name="state" required
                   value="{{ old('state', $vendor->state) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>

        <div>
            <label for="postal_code" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.postal_code') }}</label>
            <input type="text" id="postal_code" name="postal_code"
                   value="{{ old('postal_code', $vendor->postal_code) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>

        <div>
            <label for="country" class="mb-1 block text-sm font-semibold text-gray-800">
                {{ __('vendor.country') }} <span class="text-red-500">*</span>
            </label>
            <input type="text" id="country" name="country" required
                   value="{{ old('country', $vendor->country ?: 'India') }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>

        <div>
            <label for="latitude" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_latitude') }}</label>
            <input type="number" step="any" id="latitude" name="latitude"
                   value="{{ old('latitude', $vendor->latitude) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>

        <div>
            <label for="longitude" class="mb-1 block text-sm font-semibold text-gray-800">{{ __('vendor.store_longitude') }}</label>
            <input type="number" step="any" id="longitude" name="longitude"
                   value="{{ old('longitude', $vendor->longitude) }}"
                   class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/25">
        </div>
    </div>

    @include('vendor.store.partials.save-button')
</form>
