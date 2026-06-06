{{-- Pricing, fees & inventory in one compact block — expects form tokens --}}
@php
    $o = function (string $key, $default) use ($item) {
        if ($item === null) {
            return old($key, $default);
        }

        return old($key, $item->{$key} ?? $default);
    };
    $subhead = 'text-xs font-semibold uppercase tracking-wide text-gray-500';
@endphp

<div class="space-y-4">
    {{-- Pricing --}}
    <div id="item-section-pricing" class="scroll-mt-24">
        <h3 class="{{ $subhead }} mb-3">{{ __('vendor.item_form_section_pricing') }}</h3>
        <div class="{{ $igrid2 }}">
            <div x-show="!hasVariants" x-cloak>
                <label for="price" class="{{ $ilabel }}">{{ __('vendor.price') }} (₹) <span class="text-red-500">*</span></label>
                <p class="{{ $ihint }}">{{ __('vendor.field_hint_price') }}</p>
                <input type="number" id="price" name="price" value="{{ $o('price', 0) }}" step="0.01" min="0"
                       :required="!hasVariants"
                       :disabled="hasVariants"
                       class="{{ $ifc }} @error('price') border-red-500 ring-red-500/20 @enderror"
                       placeholder="0.00">
                @error('price')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
            </div>
            <div :class="hasVariants ? 'sm:col-span-2' : ''">
                <label for="rental_period" class="{{ $ilabel }}">{{ __('vendor.rental_period') }} <span class="text-red-500">*</span></label>
                <p class="{{ $ihint }}">{{ __('vendor.field_hint_rental_period') }}</p>
                <select id="rental_period" name="rental_period" required
                        class="{{ $ifc }} @error('rental_period') border-red-500 ring-red-500/20 @enderror">
                    @foreach($rentalPeriods as $key => $label)
                        <option value="{{ $key }}" {{ old('rental_period', ($item ?? null)?->rental_period ?? 'per_day') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('rental_period')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="min_rental_duration" class="{{ $ilabel }}">{{ __('vendor.minimum_rental_duration') }} <span class="text-red-500">*</span></label>
                <p class="{{ $ihint }}">{{ __('vendor.field_hint_min_rental') }}</p>
                <input type="number" id="min_rental_duration" name="min_rental_duration" min="1" max="3650"
                       value="{{ $o('min_rental_duration', 1) }}" required
                       class="{{ $ifc }} @error('min_rental_duration') border-red-500 ring-red-500/20 @enderror">
                @error('min_rental_duration')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="max_rental_duration" class="{{ $ilabel }}">{{ __('vendor.maximum_rental_duration') }} <span class="text-red-500">*</span></label>
                <p class="{{ $ihint }}">{{ __('vendor.field_hint_max_rental') }}</p>
                <input type="number" id="max_rental_duration" name="max_rental_duration" min="1" max="3650"
                       value="{{ $o('max_rental_duration', 90) }}" required
                       class="{{ $ifc }} @error('max_rental_duration') border-red-500 ring-red-500/20 @enderror">
                @error('max_rental_duration')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
            </div>
        </div>
        <p x-show="hasVariants" x-cloak class="mt-2 rounded-lg border border-emerald-100 bg-emerald-50/80 px-3 py-2 text-xs text-emerald-900">
            {{ __('vendor.item_variant_price_in_grid') }}
        </p>
    </div>

    <div class="border-t border-gray-100"></div>

    {{-- Deposits --}}
    <div id="item-section-deposits" class="scroll-mt-24">
        <h3 class="{{ $subhead }} mb-3">{{ __('vendor.item_fees_section') }}</h3>
        @include('vendor.items.partials.form.deposits-fields', ['item' => $item ?? null])
    </div>

    <div class="border-t border-gray-100"></div>

    {{-- Inventory --}}
    <div id="item-section-inventory" class="scroll-mt-24">
        <h3 class="{{ $subhead }} mb-3">{{ __('vendor.item_form_section_inventory') }}</h3>
        @include('vendor.items.partials.form.inventory-fields', [
            'item' => $item ?? null,
            'variantAware' => true,
            'compact' => true,
        ])
    </div>
</div>
