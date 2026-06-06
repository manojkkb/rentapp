{{-- Min / max rental duration — expects form tokens; optional $item --}}
@php
    $o = function (string $key, $default) use ($item) {
        if ($item === null) {
            return old($key, $default);
        }

        return old($key, $item->{$key} ?? $default);
    };
@endphp

<p class="text-xs leading-snug text-gray-600">{{ __('vendor.rental_duration_days_hint') }}</p>
<div class="{{ $igrid2 ?? 'grid grid-cols-1 gap-4 sm:grid-cols-2' }}">
    <div>
        <label class="{{ $ilabel }}">{{ __('vendor.minimum_rental_duration') }} <span class="text-red-500">*</span></label>
        <p class="{{ $ihint }}">{{ __('vendor.field_hint_min_rental') }}</p>
        <input type="number" name="min_rental_duration" min="1" max="3650" value="{{ $o('min_rental_duration', 1) }}" required
               class="{{ $ifc }} @error('min_rental_duration') border-red-500 ring-red-500/20 @enderror"
               title="{{ __('vendor.rental_duration_days_hint') }}">
        @error('min_rental_duration')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="{{ $ilabel }}">{{ __('vendor.maximum_rental_duration') }} <span class="text-red-500">*</span></label>
        <p class="{{ $ihint }}">{{ __('vendor.field_hint_max_rental') }}</p>
        <input type="number" name="max_rental_duration" min="1" max="3650" value="{{ $o('max_rental_duration', 90) }}" required
               class="{{ $ifc }} @error('max_rental_duration') border-red-500 ring-red-500/20 @enderror">
        @error('max_rental_duration')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
    </div>
</div>
