@php
    /** @var int $current 1–5 */
    $total = (int) ($total ?? 5);
    $current = max(1, min((int) ($current ?? 1), $total));
    $pct = min(100, max(0, (int) round(100 * $current / $total)));
    $wizardNavMb = ($compact ?? false) ? 'mb-3 sm:mb-4' : 'mb-6 sm:mb-8';

    $steps = [
        ['label' => __('vendor.order_wizard_step_event'), 'icon' => 'fa-calendar-alt'],
        ['label' => __('vendor.order_wizard_step_items'), 'icon' => 'fa-box-open'],
        ['label' => __('vendor.order_wizard_step_summary'), 'icon' => 'fa-clipboard-list'],
        ['label' => __('vendor.order_wizard_step_pickup_delivery'), 'icon' => 'fa-truck'],
        ['label' => __('vendor.order_wizard_step_payment'), 'icon' => 'fa-credit-card'],
    ];

    $currentStep = $steps[$current - 1] ?? $steps[0];
@endphp

<nav class="{{ $wizardNavMb }}" aria-label="{{ __('vendor.create_order') }}">
    {{-- Mobile: compact dot stepper --}}
    <div
        class="sm:hidden"
        role="group"
        aria-roledescription="{{ __('vendor.order_wizard_step_of', ['current' => $current, 'total' => $total]) }}"
    >
        <p class="mb-2 text-center text-[11px] font-medium text-gray-500">
            {{ __('vendor.order_wizard_step_of', ['current' => $current, 'total' => $total]) }}
        </p>

        <ol class="flex items-center px-1">
            @foreach($steps as $index => $step)
                @php
                    $stepNum = $index + 1;
                    $isComplete = $stepNum < $current;
                    $isCurrent = $stepNum === $current;
                    $isUpcoming = $stepNum > $current;
                @endphp

                @if($index > 0)
                    <li class="h-0.5 min-w-[0.35rem] flex-1 rounded-full {{ $stepNum <= $current ? 'bg-emerald-500' : 'bg-gray-200' }}" aria-hidden="true"></li>
                @endif

                <li
                    class="relative shrink-0"
                    @if($isCurrent) aria-current="step" @endif
                >
                    <div
                        @class([
                            'flex items-center justify-center rounded-full transition-all duration-300',
                            'h-8 w-8 border-2 border-emerald-600 bg-emerald-600 text-white shadow-sm shadow-emerald-600/25' => $isCurrent,
                            'h-6 w-6 border-2 border-emerald-600 bg-emerald-600 text-white' => $isComplete,
                            'h-6 w-6 border-2 border-gray-200 bg-white text-gray-300' => $isUpcoming,
                        ])
                        title="{{ $step['label'] }}"
                    >
                        @if($isComplete)
                            <i class="fas fa-check text-[9px]" aria-hidden="true"></i>
                            <span class="sr-only">{{ $step['label'] }} — {{ __('vendor.completed') }}</span>
                        @elseif($isCurrent)
                            <i class="fas {{ $step['icon'] }} text-[11px]" aria-hidden="true"></i>
                        @else
                            <span class="text-[10px] font-semibold tabular-nums" aria-hidden="true">{{ $stepNum }}</span>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>

        <p class="mt-2.5 text-center text-sm font-bold leading-snug text-gray-900">
            {{ $currentStep['label'] }}
        </p>

        <div
            class="mt-2 h-1 overflow-hidden rounded-full bg-gray-200"
            role="progressbar"
            aria-valuemin="0"
            aria-valuemax="100"
            aria-valuenow="{{ $pct }}"
            aria-label="{{ __('vendor.order_wizard_step_of', ['current' => $current, 'total' => $total]) }}"
        >
            <div
                class="h-full rounded-full bg-emerald-500 transition-[width] duration-500 ease-out"
                style="width: {{ $pct }}%"
            ></div>
        </div>
    </div>

    {{-- Desktop: card with full stepper --}}
    <div
        class="hidden overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100 sm:block"
        role="group"
        aria-roledescription="{{ __('vendor.order_wizard_step_of', ['current' => $current, 'total' => $total]) }}"
    >
        <div class="border-b border-gray-100 bg-gradient-to-r from-emerald-50/80 via-white to-white px-4 py-3">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm shadow-emerald-600/25"
                    aria-hidden="true"
                >
                    <i class="fas {{ $currentStep['icon'] }} text-base"></i>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">
                        {{ __('vendor.order_wizard_step_of', ['current' => $current, 'total' => $total]) }}
                    </p>
                    <p class="truncate text-base font-bold leading-snug text-gray-900">
                        {{ $currentStep['label'] }}
                    </p>
                </div>

                <div
                    class="shrink-0 rounded-full border border-emerald-200 bg-white px-3 py-1.5 text-sm font-bold tabular-nums text-emerald-700"
                    aria-hidden="true"
                >
                    {{ $pct }}%
                </div>
            </div>

            <div
                class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-200/90"
                role="progressbar"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="{{ $pct }}"
                aria-label="{{ __('vendor.order_wizard_step_of', ['current' => $current, 'total' => $total]) }}"
            >
                <div
                    class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600 transition-[width] duration-500 ease-out"
                    style="width: {{ $pct }}%"
                ></div>
            </div>
        </div>

        <div class="px-4 py-3">
            <ol class="flex w-full items-start">
                @foreach($steps as $index => $step)
                    @php
                        $stepNum = $index + 1;
                        $isComplete = $stepNum < $current;
                        $isCurrent = $stepNum === $current;
                        $isUpcoming = $stepNum > $current;
                    @endphp

                    @if($index > 0)
                        <li class="flex min-w-[0.75rem] flex-1 items-center pt-[1.125rem]" aria-hidden="true">
                            <span @class([
                                'block h-0.5 w-full rounded-full transition-colors duration-300',
                                'bg-emerald-600' => $stepNum <= $current,
                                'bg-gray-200' => $stepNum > $current,
                            ])></span>
                        </li>
                    @endif

                    <li
                        class="flex w-[5rem] shrink-0 flex-col items-center"
                        @if($isCurrent) aria-current="step" @endif
                    >
                        <div
                            @class([
                                'flex h-9 w-9 items-center justify-center rounded-full border-2 text-xs font-bold transition-all duration-300',
                                'border-emerald-600 bg-emerald-600 text-white shadow-sm shadow-emerald-600/20 ring-4 ring-emerald-100' => $isCurrent,
                                'border-emerald-600 bg-emerald-600 text-white' => $isComplete,
                                'border-gray-200 bg-white text-gray-400' => $isUpcoming,
                            ])
                        >
                            @if($isComplete)
                                <i class="fas fa-check text-xs" aria-hidden="true"></i>
                                <span class="sr-only">{{ $step['label'] }} — {{ __('vendor.completed') }}</span>
                            @else
                                <span aria-hidden="true">{{ $stepNum }}</span>
                            @endif
                        </div>

                        <span
                            @class([
                                'mt-2 w-full px-0.5 text-center text-[11px] font-medium leading-tight',
                                'font-semibold text-emerald-800' => $isCurrent,
                                'text-emerald-700' => $isComplete,
                                'text-gray-400' => $isUpcoming,
                            ])
                        >
                            {{ $step['label'] }}
                        </span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

    <p class="sr-only" role="status" aria-live="polite">
        {{ __('vendor.order_wizard_step_of', ['current' => $current, 'total' => $total]) }} — {{ $currentStep['label'] }}
    </p>
</nav>
