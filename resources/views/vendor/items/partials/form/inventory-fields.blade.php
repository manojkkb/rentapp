{{-- Stock buckets — expects form tokens; optional $item, $variantAware --}}
@php
    $o = function (string $key, $default) use ($item) {
        if ($item === null) {
            return old($key, $default);
        }

        return old($key, $item->{$key} ?? $default);
    };
@endphp

<div @if(!empty($variantAware)) x-show="!hasVariants" x-cloak @endif>
    @unless(!empty($compact))
        <p class="mb-3 text-xs font-medium leading-snug text-gray-600">{{ __('vendor.item_stock_buckets_hint') }}</p>
    @endunless
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
        <div>
            <label class="{{ $ilabel }}">{{ __('vendor.stock_quantity') }} <span class="text-red-500">*</span></label>
            <p class="{{ $ihint }}">{{ __('vendor.field_hint_stock') }}</p>
            <input type="number" name="stock" min="0" value="{{ $o('stock', 1) }}" required
                   class="{{ $ifc }} @error('stock') border-red-500 ring-red-500/20 @enderror"
                   @if(!empty($variantAware)) :disabled="hasVariants" @endif>
            @error('stock')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $ilabel }}">{{ __('vendor.damaged_stock') }} <span class="text-red-500">*</span></label>
            <p class="{{ $ihint }}">{{ __('vendor.field_hint_damaged_stock') }}</p>
            <input type="number" name="damaged_stock" min="0" value="{{ $o('damaged_stock', 0) }}" required
                   class="{{ $ifc }} @error('damaged_stock') border-red-500 ring-red-500/20 @enderror"
                   @if(!empty($variantAware)) :disabled="hasVariants" @endif>
            @error('damaged_stock')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="{{ $ilabel }}">{{ __('vendor.maintenance_stock') }} <span class="text-red-500">*</span></label>
            <p class="{{ $ihint }}">{{ __('vendor.field_hint_maintenance_stock') }}</p>
            <input type="number" name="maintenance_stock" min="0" value="{{ $o('maintenance_stock', 0) }}" required
                   class="{{ $ifc }} @error('maintenance_stock') border-red-500 ring-red-500/20 @enderror"
                   @if(!empty($variantAware)) :disabled="hasVariants" @endif>
            @error('maintenance_stock')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="mt-3 rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5">
        <label class="flex cursor-pointer items-start gap-3">
            <input type="checkbox" name="manage_stock" value="1"
                   class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                   @checked(filter_var(old('manage_stock', $item?->manage_stock ?? true), FILTER_VALIDATE_BOOLEAN))
                   @if(!empty($variantAware)) :disabled="hasVariants" @endif>
            <span>
                <span class="block text-sm font-medium text-gray-800">{{ __('vendor.track_stock_quantity') }}</span>
                <span class="{{ $ihint }} mt-0.5">{{ __('vendor.field_hint_manage_stock') }}</span>
            </span>
        </label>
    </div>
    @if(!empty($variantAware))
        <p x-show="hasVariants" x-cloak class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50/80 px-3.5 py-2.5 text-xs leading-snug text-emerald-900">
            <i class="fas fa-layer-group mr-1.5 opacity-70" aria-hidden="true"></i>
            {{ __('vendor.item_variant_stock_in_grid') }}
        </p>
    @endif
</div>
