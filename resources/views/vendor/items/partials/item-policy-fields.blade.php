{{-- Shared item fees, inventory buckets, rental limits, physical specs ($item null on create) --}}
@php
    /** @var \App\Models\Items|null $item */
    $o = function (string $key, $default) use ($item) {
        if ($item === null) {
            return old($key, $default);
        }

        return old($key, $item->{$key} ?? $default);
    };
    $fh = 'text-xs text-gray-500 mb-2 leading-snug';
@endphp

<div class="border-t border-gray-200 pt-5 mb-5">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('vendor.item_fees_section') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.security_deposit') }} (₹) <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_security_deposit') }}</p>
            <input type="number" name="security_deposit" step="0.01" min="0" value="{{ $o('security_deposit', 0) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('security_deposit') border-red-500 @enderror">
            @error('security_deposit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.replacement_cost') }} (₹) <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_replacement_cost') }}</p>
            <input type="number" name="replacement_cost" step="0.01" min="0" value="{{ $o('replacement_cost', 0) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('replacement_cost') border-red-500 @enderror">
            @error('replacement_cost')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.late_fee') }} (₹) <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_late_fee') }}</p>
            <input type="number" name="late_fee" step="0.01" min="0" value="{{ $o('late_fee', 0) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('late_fee') border-red-500 @enderror">
            @error('late_fee')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="border-t border-gray-200 pt-5 mb-5">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('vendor.item_rental_limits_section') }}</h3>
    <p class="text-xs text-gray-500 mb-3">{{ __('vendor.rental_duration_days_hint') }}</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.minimum_rental_duration') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_min_rental') }}</p>
            <input type="number" name="min_rental_duration" min="1" max="3650" value="{{ $o('min_rental_duration', 1) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('min_rental_duration') border-red-500 @enderror">
            @error('min_rental_duration')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.maximum_rental_duration') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_max_rental') }}</p>
            <input type="number" name="max_rental_duration" min="1" max="3650" value="{{ $o('max_rental_duration', 90) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('max_rental_duration') border-red-500 @enderror">
            @error('max_rental_duration')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="border-t border-gray-200 pt-5 mb-5">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('vendor.item_physical_section') }}</h3>
    <div class="max-w-md">
        <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.condition_status') }} <span class="text-red-500">*</span></label>
        <p class="{{ $fh }}">{{ __('vendor.field_hint_condition') }}</p>
        <select name="condition_status" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('condition_status') border-red-500 @enderror">
            @foreach (\App\Models\Items::conditionStatusOptions() as $key => $label)
                <option value="{{ $key }}" @selected($o('condition_status', 'good') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('condition_status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="border-t border-gray-200 pt-5 mb-5">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('vendor.item_inventory_buckets_section') }}</h3>
    <p class="text-xs text-gray-500 mb-3">{{ __('vendor.item_stock_buckets_hint') }}</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.stock_quantity') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_stock') }}</p>
            <input type="number" name="stock" min="0" value="{{ $o('stock', 1) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('stock') border-red-500 @enderror">
            @error('stock')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.damaged_stock') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_damaged_stock') }}</p>
            <input type="number" name="damaged_stock" min="0" value="{{ $o('damaged_stock', 0) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('damaged_stock') border-red-500 @enderror">
            @error('damaged_stock')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.maintenance_stock') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_maintenance_stock') }}</p>
            <input type="number" name="maintenance_stock" min="0" value="{{ $o('maintenance_stock', 0) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('maintenance_stock') border-red-500 @enderror">
            @error('maintenance_stock')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="mt-4">
        <label class="flex items-center cursor-pointer">
            <input type="checkbox" name="manage_stock" value="1" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500" @checked(filter_var(old('manage_stock', $item?->manage_stock ?? true), FILTER_VALIDATE_BOOLEAN))>
            <span class="ml-2 text-sm font-medium text-gray-700">{{ __('vendor.track_stock_quantity') }}</span>
        </label>
        <p class="{{ $fh }} mt-1.5 ml-7 mb-0">{{ __('vendor.field_hint_manage_stock') }}</p>
    </div>
</div>
