@php
    $toneMap = [
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'teal' => 'bg-teal-50 text-teal-700 ring-teal-100',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];
    $tz = config('app.timezone');
@endphp

@if($activities->isEmpty())
    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/80 px-4 py-8 text-center">
        <i class="fas fa-clock-rotate-left mb-2 text-2xl text-gray-300" aria-hidden="true"></i>
        <p class="text-sm text-gray-600">{{ __('vendor.order_activity_empty') }}</p>
    </div>
@else
    <ol class="relative space-y-0">
        @foreach($activities as $activity)
            @php
                $tone = $toneMap[$activity['tone']] ?? $toneMap['emerald'];
                $when = $activity['at'] instanceof \Carbon\CarbonInterface
                    ? $activity['at']->copy()->timezone($tz)
                    : \Carbon\Carbon::parse($activity['at'])->timezone($tz);
                $actorDisplay = $activity['actor_display'] ?? ($activity['actor'] ?? __('vendor.order_activity_system'));
            @endphp
            <li class="relative flex gap-3 pb-5 last:pb-0 sm:gap-4">
                @if(!$loop->last)
                    <span class="absolute left-[1.125rem] top-9 bottom-0 w-px bg-gray-200 sm:left-5" aria-hidden="true"></span>
                @endif
                <span class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ring-1 {{ $tone }}">
                    <i class="fas {{ $activity['icon'] }} text-sm" aria-hidden="true"></i>
                </span>
                <div class="min-w-0 flex-1 pt-0.5">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm leading-snug text-gray-900">
                                <span class="font-semibold text-gray-900">{{ $actorDisplay }}</span>
                                <span class="mx-1.5 font-normal text-gray-300" aria-hidden="true">·</span>
                                <span class="font-medium text-gray-800">{{ $activity['label'] }}</span>
                            </p>
                            <p class="mt-0.5 text-sm text-gray-600">{{ $activity['description'] }}</p>
                            @if(!empty($activity['meta']))
                                <p class="mt-1 text-[11px] text-gray-400">{{ $activity['meta'] }}</p>
                            @endif
                        </div>
                        <time datetime="{{ $when->toIso8601String() }}"
                              class="shrink-0 text-right text-[11px] font-medium leading-snug text-gray-500"
                              title="{{ $when->format('M j, Y g:i A') }}">
                            <span class="block">{{ $when->diffForHumans() }}</span>
                            <span class="block text-[10px] font-normal text-gray-400">{{ $when->format('M j, g:i A') }}</span>
                        </time>
                    </div>
                </div>
            </li>
        @endforeach
    </ol>
@endif
