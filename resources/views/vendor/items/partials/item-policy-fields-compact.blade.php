{{-- Dense layout for vendor item create (single screen target on md+) --}}
@php
    /** @var \App\Models\Items|null $item */
    $o = function (string $key, $default) use ($item) {
        if ($item === null) {
            return old($key, $default);
        }

        return old($key, $item->{$key} ?? $default);
    };
    $lb = 'block text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-0.5';
    $in = 'w-full min-w-0 rounded border border-gray-300 px-2 py-1.5 text-sm text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500';
    $fh = 'text-[10px] leading-snug text-gray-500 mb-1';
    $sh = 'text-[10px] font-bold uppercase tracking-wide text-emerald-900/90';
@endphp

<div class="space-y-2 rounded-md border border-gray-200 bg-gray-50/80 p-2.5">
    <div>
        <p class="{{ $sh }} mb-1.5">{{ __('vendor.item_fees_section') }}</p>
        <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
        <div>
            <label class="{{ $lb }}">{{ __('vendor.security_deposit') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_security_deposit') }}</p>
            <input type="number" name="security_deposit" step="0.01" min="0" value="{{ $o('security_deposit', 0) }}" required class="{{ $in }} @error('security_deposit') border-red-500 @enderror">
            @error('security_deposit')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lb }}">{{ __('vendor.replacement_cost') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_replacement_cost') }}</p>
            <input type="number" name="replacement_cost" step="0.01" min="0" value="{{ $o('replacement_cost', 0) }}" required class="{{ $in }} @error('replacement_cost') border-red-500 @enderror">
            @error('replacement_cost')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lb }}">{{ __('vendor.late_fee_per_day') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_late_fee_per_day') }}</p>
            <input type="number" name="late_fee_per_day" step="0.01" min="0" value="{{ $o('late_fee_per_day', 0) }}" required class="{{ $in }} @error('late_fee_per_day') border-red-500 @enderror">
            @error('late_fee_per_day')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="col-span-2 flex flex-col justify-end gap-1 md:col-span-1">
            <label class="flex cursor-pointer items-center gap-1.5">
                <input type="checkbox" name="is_damage_protection" value="1" class="h-3.5 w-3.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" @checked(filter_var(old('is_damage_protection', $item?->is_damage_protection ?? false), FILTER_VALIDATE_BOOLEAN))>
                <span class="text-xs font-medium text-gray-700 leading-tight">{{ __('vendor.is_damage_protection') }}</span>
            </label>
            <p class="{{ $fh }} mb-0 pl-5 md:pl-0">{{ __('vendor.field_hint_damage_protection') }}</p>
        </div>
        </div>
    </div>

    <div class="border-t border-gray-200/80 pt-2">
        <p class="{{ $sh }} mb-1.5">{{ __('vendor.item_rental_limits_section') }}</p>
        <p class="mb-1.5 text-[10px] text-gray-600 leading-snug">{{ __('vendor.rental_duration_days_hint') }}</p>
        <div class="grid grid-cols-2 gap-2">
        <div>
            <label class="{{ $lb }}">{{ __('vendor.minimum_rental_duration') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_min_rental') }}</p>
            <input type="number" name="minimum_rental_duration" min="1" max="3650" value="{{ $o('minimum_rental_duration', 1) }}" required class="{{ $in }} @error('minimum_rental_duration') border-red-500 @enderror" title="{{ __('vendor.rental_duration_days_hint') }}">
            @error('minimum_rental_duration')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lb }}">{{ __('vendor.maximum_rental_duration') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_max_rental') }}</p>
            <input type="number" name="maximum_rental_duration" min="1" max="3650" value="{{ $o('maximum_rental_duration', 90) }}" required class="{{ $in }} @error('maximum_rental_duration') border-red-500 @enderror">
            @error('maximum_rental_duration')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        </div>
    </div>

    <div class="border-t border-gray-200/80 pt-2">
        <p class="{{ $sh }} mb-1.5">{{ __('vendor.item_physical_section') }}</p>
        <div class="grid grid-cols-2 gap-2 md:grid-cols-5">
        <div>
            <label class="{{ $lb }}">{{ __('vendor.weight_kg') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_weight') }}</p>
            <input type="number" name="weight" step="0.001" min="0" value="{{ $o('weight', 0) }}" required class="{{ $in }} @error('weight') border-red-500 @enderror">
            @error('weight')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lb }}">{{ __('vendor.condition_status') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_condition') }}</p>
            <select name="condition_status" required class="{{ $in }} @error('condition_status') border-red-500 @enderror">
                @foreach (\App\Models\Items::conditionStatusOptions() as $key => $label)
                    <option value="{{ $key }}" @selected($o('condition_status', 'good') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('condition_status')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lb }}">{{ __('vendor.dimension_length_cm') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_dimension_length') }}</p>
            <input type="number" name="dimension_length" step="0.01" min="0" value="{{ $o('dimension_length', 0) }}" required class="{{ $in }} @error('dimension_length') border-red-500 @enderror">
            @error('dimension_length')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lb }}">{{ __('vendor.dimension_width_cm') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_dimension_width') }}</p>
            <input type="number" name="dimension_width" step="0.01" min="0" value="{{ $o('dimension_width', 0) }}" required class="{{ $in }} @error('dimension_width') border-red-500 @enderror">
            @error('dimension_width')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lb }}">{{ __('vendor.dimension_height_cm') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_dimension_height') }}</p>
            <input type="number" name="dimension_height" step="0.01" min="0" value="{{ $o('dimension_height', 0) }}" required class="{{ $in }} @error('dimension_height') border-red-500 @enderror">
            @error('dimension_height')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        </div>
    </div>

    <div class="border-t border-gray-200/80 pt-2">
        <p class="{{ $sh }} mb-0.5">{{ __('vendor.item_inventory_buckets_section') }}</p>
        <p class="mb-1.5 text-[10px] font-medium leading-snug text-gray-600">{{ __('vendor.item_stock_buckets_hint') }}</p>
        <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3 md:grid-cols-5">
            <div>
                <label class="{{ $lb }}">{{ __('vendor.total_stock') }} <span class="text-red-500">*</span></label>
                <p class="{{ $fh }}">{{ __('vendor.field_hint_total_stock') }}</p>
                <input type="number" name="total_stock" min="0" value="{{ $o('total_stock', 1) }}" required class="{{ $in }} @error('total_stock') border-red-500 @enderror">
                @error('total_stock')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $lb }}">{{ __('vendor.available_stock') }} <span class="text-red-500">*</span></label>
                <p class="{{ $fh }}">{{ __('vendor.field_hint_available_stock') }}</p>
                <input type="number" name="available_stock" min="0" value="{{ $o('available_stock', 1) }}" required class="{{ $in }} @error('available_stock') border-red-500 @enderror">
                @error('available_stock')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $lb }}">{{ __('vendor.rented_stock') }} <span class="text-red-500">*</span></label>
                <p class="{{ $fh }}">{{ __('vendor.field_hint_rented_stock') }}</p>
                <input type="number" name="rented_stock" min="0" value="{{ $o('rented_stock', 0) }}" required class="{{ $in }} @error('rented_stock') border-red-500 @enderror">
                @error('rented_stock')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $lb }}">{{ __('vendor.damaged_stock') }} <span class="text-red-500">*</span></label>
                <p class="{{ $fh }}">{{ __('vendor.field_hint_damaged_stock') }}</p>
                <input type="number" name="damaged_stock" min="0" value="{{ $o('damaged_stock', 0) }}" required class="{{ $in }} @error('damaged_stock') border-red-500 @enderror">
                @error('damaged_stock')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $lb }}">{{ __('vendor.maintenance_stock') }} <span class="text-red-500">*</span></label>
                <p class="{{ $fh }}">{{ __('vendor.field_hint_maintenance_stock') }}</p>
                <input type="number" name="maintenance_stock" min="0" value="{{ $o('maintenance_stock', 0) }}" required class="{{ $in }} @error('maintenance_stock') border-red-500 @enderror">
                @error('maintenance_stock')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-1.5">
            <label class="flex cursor-pointer items-center gap-1.5">
                <input type="checkbox" name="manage_stock" value="1" class="h-3.5 w-3.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" @checked(filter_var(old('manage_stock', $item?->manage_stock ?? true), FILTER_VALIDATE_BOOLEAN))>
                <span class="text-xs font-medium text-gray-700">{{ __('vendor.track_stock_quantity') }}</span>
            </label>
            <p class="{{ $fh }} mt-0.5 mb-0 pl-5">{{ __('vendor.field_hint_manage_stock') }}</p>
        </div>
        </div>
    </div>
</div>
