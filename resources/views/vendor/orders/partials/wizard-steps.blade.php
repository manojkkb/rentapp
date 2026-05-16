@php
    /** @var int $current 1–5 */
    $total = (int) ($total ?? 5);
    $current = max(1, min((int) ($current ?? 1), $total));
    $pct = min(100, max(0, (int) round(100 * $current / $total)));
    $wizardNavMb = ($compact ?? false) ? 'mb-3 sm:mb-4' : 'mb-6 sm:mb-8';
@endphp
<nav class="{{ $wizardNavMb }}" aria-label="{{ __('vendor.create_order') }}">
    <div class="sr-only" role="status">
        {{ __('vendor.order_wizard_step_of', ['current' => $current, 'total' => $total]) }}
    </div>
    <div
        class="h-2.5 w-full overflow-hidden rounded-full bg-gray-200"
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="{{ $pct }}"
        aria-label="{{ __('vendor.order_wizard_step_of', ['current' => $current, 'total' => $total]) }}"
    >
        <div
            class="h-full rounded-full bg-emerald-600 transition-[width] duration-300 ease-out"
            style="width: {{ $pct }}%"
        ></div>
    </div>
</nav>
