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
        <div class="grid grid-cols-2 gap-2 md:grid-cols-3">
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
            <label class="{{ $lb }}">{{ __('vendor.late_fee') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_late_fee') }}</p>
            <input type="number" name="late_fee" step="0.01" min="0" value="{{ $o('late_fee', 0) }}" required class="{{ $in }} @error('late_fee') border-red-500 @enderror">
            @error('late_fee')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
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
            <input type="number" name="min_rental_duration" min="1" max="3650" value="{{ $o('min_rental_duration', 1) }}" required class="{{ $in }} @error('min_rental_duration') border-red-500 @enderror" title="{{ __('vendor.rental_duration_days_hint') }}">
            @error('min_rental_duration')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $lb }}">{{ __('vendor.maximum_rental_duration') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_max_rental') }}</p>
            <input type="number" name="max_rental_duration" min="1" max="3650" value="{{ $o('max_rental_duration', 90) }}" required class="{{ $in }} @error('max_rental_duration') border-red-500 @enderror">
            @error('max_rental_duration')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
        </div>
    </div>

    <div class="border-t border-gray-200/80 pt-2">
        <div class="max-w-xs">
            <label class="{{ $lb }}">{{ __('vendor.condition_status') }} <span class="text-red-500">*</span></label>
            <p class="{{ $fh }}">{{ __('vendor.field_hint_condition') }}</p>
            <select name="condition_status" required class="{{ $in }} @error('condition_status') border-red-500 @enderror">
                @foreach (\App\Models\Items::conditionStatusOptions() as $key => $label)
                    <option value="{{ $key }}" @selected($o('condition_status', 'good') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            @error('condition_status')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="border-t border-gray-200/80 pt-2" @if(!empty($variantAware)) x-show="!hasVariants" x-cloak @endif>
        <p class="{{ $sh }} mb-0.5">{{ __('vendor.item_inventory_buckets_section') }}</p>
        <p class="mb-1.5 text-[10px] font-medium leading-snug text-gray-600">{{ __('vendor.item_stock_buckets_hint') }}</p>
        <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3">
            <div>
                <label class="{{ $lb }}">{{ __('vendor.stock_quantity') }} <span class="text-red-500">*</span></label>
                <p class="{{ $fh }}">{{ __('vendor.field_hint_stock') }}</p>
                <input type="number" name="stock" min="0" value="{{ $o('stock', 1) }}" required class="{{ $in }} @error('stock') border-red-500 @enderror" @if(!empty($variantAware)) :disabled="hasVariants" @endif>
                @error('stock')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $lb }}">{{ __('vendor.damaged_stock') }} <span class="text-red-500">*</span></label>
                <p class="{{ $fh }}">{{ __('vendor.field_hint_damaged_stock') }}</p>
                <input type="number" name="damaged_stock" min="0" value="{{ $o('damaged_stock', 0) }}" required class="{{ $in }} @error('damaged_stock') border-red-500 @enderror" @if(!empty($variantAware)) :disabled="hasVariants" @endif>
                @error('damaged_stock')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="{{ $lb }}">{{ __('vendor.maintenance_stock') }} <span class="text-red-500">*</span></label>
                <p class="{{ $fh }}">{{ __('vendor.field_hint_maintenance_stock') }}</p>
                <input type="number" name="maintenance_stock" min="0" value="{{ $o('maintenance_stock', 0) }}" required class="{{ $in }} @error('maintenance_stock') border-red-500 @enderror" @if(!empty($variantAware)) :disabled="hasVariants" @endif>
                @error('maintenance_stock')<p class="mt-0.5 text-[11px] text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="mt-1.5">
            <label class="flex cursor-pointer items-center gap-1.5">
                <input type="checkbox" name="manage_stock" value="1" class="h-3.5 w-3.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" @checked(filter_var(old('manage_stock', $item?->manage_stock ?? true), FILTER_VALIDATE_BOOLEAN)) @if(!empty($variantAware)) :disabled="hasVariants" @endif>
                <span class="text-xs font-medium text-gray-700">{{ __('vendor.track_stock_quantity') }}</span>
            </label>
            <p class="{{ $fh }} mt-0.5 mb-0 pl-5">{{ __('vendor.field_hint_manage_stock') }}</p>
        </div>
        </div>
    </div>
</div>
