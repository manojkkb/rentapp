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
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.late_fee_per_day') }} (₹) <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_late_fee_per_day') }}</p>
            <input type="number" name="late_fee_per_day" step="0.01" min="0" value="{{ $o('late_fee_per_day', 0) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('late_fee_per_day') border-red-500 @enderror">
            @error('late_fee_per_day')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="mt-4">
        <label class="flex items-center cursor-pointer">
            <input type="checkbox" name="is_damage_protection" value="1" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500" @checked(filter_var(old('is_damage_protection', $item?->is_damage_protection ?? false), FILTER_VALIDATE_BOOLEAN))>
            <span class="ml-2 text-sm font-medium text-gray-700">{{ __('vendor.is_damage_protection') }}</span>
        </label>
        <p class="{{ $fh }} mt-1.5 ml-7 mb-0">{{ __('vendor.field_hint_damage_protection') }}</p>
    </div>
</div>

<div class="border-t border-gray-200 pt-5 mb-5">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('vendor.item_rental_limits_section') }}</h3>
    <p class="text-xs text-gray-500 mb-3">{{ __('vendor.rental_duration_days_hint') }}</p>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.minimum_rental_duration') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_min_rental') }}</p>
            <input type="number" name="minimum_rental_duration" min="1" max="3650" value="{{ $o('minimum_rental_duration', 1) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('minimum_rental_duration') border-red-500 @enderror">
            @error('minimum_rental_duration')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.maximum_rental_duration') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_max_rental') }}</p>
            <input type="number" name="maximum_rental_duration" min="1" max="3650" value="{{ $o('maximum_rental_duration', 90) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('maximum_rental_duration') border-red-500 @enderror">
            @error('maximum_rental_duration')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="border-t border-gray-200 pt-5 mb-5">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('vendor.item_physical_section') }}</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.weight_kg') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_weight') }}</p>
            <input type="number" name="weight" step="0.001" min="0" value="{{ $o('weight', 0) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('weight') border-red-500 @enderror">
            @error('weight')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.dimension_length_cm') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_dimension_length') }}</p>
            <input type="number" name="dimension_length" step="0.01" min="0" value="{{ $o('dimension_length', 0) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('dimension_length') border-red-500 @enderror">
            @error('dimension_length')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.dimension_width_cm') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_dimension_width') }}</p>
            <input type="number" name="dimension_width" step="0.01" min="0" value="{{ $o('dimension_width', 0) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('dimension_width') border-red-500 @enderror">
            @error('dimension_width')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.dimension_height_cm') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_dimension_height') }}</p>
            <input type="number" name="dimension_height" step="0.01" min="0" value="{{ $o('dimension_height', 0) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('dimension_height') border-red-500 @enderror">
            @error('dimension_height')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

<div class="border-t border-gray-200 pt-5 mb-5">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('vendor.item_inventory_buckets_section') }}</h3>
    <p class="text-xs text-gray-500 mb-3">{{ __('vendor.item_stock_buckets_hint') }}</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.total_stock') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_total_stock') }}</p>
            <input type="number" name="total_stock" min="0" value="{{ $o('total_stock', 1) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('total_stock') border-red-500 @enderror">
            @error('total_stock')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.available_stock') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_available_stock') }}</p>
            <input type="number" name="available_stock" min="0" value="{{ $o('available_stock', 1) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('available_stock') border-red-500 @enderror">
            @error('available_stock')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('vendor.rented_stock') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_rented_stock') }}</p>
            <input type="number" name="rented_stock" min="0" value="{{ $o('rented_stock', 0) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 @error('rented_stock') border-red-500 @enderror">
            @error('rented_stock')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
