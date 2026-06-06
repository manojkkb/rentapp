{{-- Deposits & late fees only — expects $ifc, $ilabel, $ihint, $ierror; optional $item --}}
@php
    $o = function (string $key, $default) use ($item) {
        if ($item === null) {
            return old($key, $default);
        }

        return old($key, $item->{$key} ?? $default);
    };
@endphp

<div class="{{ $igrid2 ?? 'grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3' }}">
    <div>
        <label class="{{ $ilabel }}">{{ __('vendor.security_deposit') }} <span class="text-red-500">*</span></label>
        <p class="{{ $ihint }}">{{ __('vendor.field_hint_security_deposit') }}</p>
        <input type="number" name="security_deposit" step="0.01" min="0" value="{{ $o('security_deposit', 0) }}" required
               class="{{ $ifc }} @error('security_deposit') border-red-500 ring-red-500/20 @enderror">
        @error('security_deposit')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="{{ $ilabel }}">{{ __('vendor.replacement_cost') }} <span class="text-red-500">*</span></label>
        <p class="{{ $ihint }}">{{ __('vendor.field_hint_replacement_cost') }}</p>
        <input type="number" name="replacement_cost" step="0.01" min="0" value="{{ $o('replacement_cost', 0) }}" required
               class="{{ $ifc }} @error('replacement_cost') border-red-500 ring-red-500/20 @enderror">
        @error('replacement_cost')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2 lg:col-span-1">
        <label class="{{ $ilabel }}">{{ __('vendor.late_fee') }} <span class="text-red-500">*</span></label>
        <p class="{{ $ihint }}">{{ __('vendor.field_hint_late_fee') }}</p>
        <input type="number" name="late_fee" step="0.01" min="0" value="{{ $o('late_fee', 0) }}" required
               class="{{ $ifc }} @error('late_fee') border-red-500 ring-red-500/20 @enderror">
        @error('late_fee')<p class="{{ $ierror }}">{{ $message }}</p>@enderror
    </div>
</div>
