@php
    $available = $item->rentableAvailableStock();
    $reserved = (int) ($item->reserved_qty ?? 0);
    $rented = (int) ($item->rented_qty ?? 0);
    $compact = $compact ?? false;
    $layout = $layout ?? ($compact ? 'compact' : 'list');
    $showUnavailable = $showUnavailable ?? true;
    $dl = $dl ?? 'text-[11px] font-semibold uppercase tracking-wide text-gray-500';
@endphp

@if($layout === 'cards')
    <div class="grid grid-cols-3 gap-2">
        <div class="rounded-lg border border-emerald-100 bg-emerald-50/50 px-3 py-2.5">
            <p class="{{ $dl }}">{{ __('vendor.available_stock') }}</p>
            <p class="mt-0.5 text-lg font-bold tabular-nums text-emerald-700">{{ $available }}</p>
        </div>
        <div class="rounded-lg border border-sky-100 bg-sky-50/50 px-3 py-2.5">
            <p class="{{ $dl }}">{{ __('vendor.reserved_stock') }}</p>
            <p class="mt-0.5 text-lg font-bold tabular-nums text-sky-700">{{ $reserved }}</p>
        </div>
        <div class="rounded-lg border border-violet-100 bg-violet-50/50 px-3 py-2.5">
            <p class="{{ $dl }}">{{ __('vendor.rented_stock') }}</p>
            <p class="mt-0.5 text-lg font-bold tabular-nums text-violet-700">{{ $rented }}</p>
        </div>
    </div>
@elseif($layout === 'compact')
    <div class="flex flex-wrap gap-1" title="{{ __('vendor.field_hint_available_stock') }} · {{ __('vendor.field_hint_reserved_stock') }} · {{ __('vendor.field_hint_rented_stock') }}">
        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-1.5 py-0.5 text-[11px] font-semibold text-emerald-800 ring-1 ring-emerald-100">
            <span class="font-medium text-emerald-600">{{ __('vendor.available_stock') }}</span>
            <span class="tabular-nums">{{ $available }}</span>
        </span>
        <span class="inline-flex items-center gap-1 rounded-md bg-sky-50 px-1.5 py-0.5 text-[11px] font-semibold text-sky-800 ring-1 ring-sky-100">
            <span class="font-medium text-sky-600">{{ __('vendor.reserved_stock') }}</span>
            <span class="tabular-nums">{{ $reserved }}</span>
        </span>
        <span class="inline-flex items-center gap-1 rounded-md bg-violet-50 px-1.5 py-0.5 text-[11px] font-semibold text-violet-800 ring-1 ring-violet-100">
            <span class="font-medium text-violet-600">{{ __('vendor.rented_stock') }}</span>
            <span class="tabular-nums">{{ $rented }}</span>
        </span>
    </div>
@else
    <div class="space-y-1">
        <div class="flex items-center justify-between gap-2 text-xs">
            <span class="font-medium text-emerald-700">{{ __('vendor.available_stock') }}</span>
            <span class="tabular-nums font-bold text-emerald-800">{{ $available }}</span>
        </div>
        <div class="flex items-center justify-between gap-2 text-xs">
            <span class="font-medium text-sky-700">{{ __('vendor.reserved_stock') }}</span>
            <span class="tabular-nums font-bold text-sky-800">{{ $reserved }}</span>
        </div>
        <div class="flex items-center justify-between gap-2 text-xs">
            <span class="font-medium text-violet-700">{{ __('vendor.rented_stock') }}</span>
            <span class="tabular-nums font-bold text-violet-800">{{ $rented }}</span>
        </div>
    </div>
@endif

@if($showUnavailable && !$item->is_available)
    <p class="mt-1 text-[11px] font-medium text-orange-600">{{ __('vendor.unavailable') }}</p>
@endif
