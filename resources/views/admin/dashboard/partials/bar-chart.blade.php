@php
    /** @var list<array{label: string, value: float}> $series */
    $max = max(1, (float) collect($series)->max('value'));
    $format = $format ?? fn ($v) => is_numeric($v) ? number_format((float) $v, 0) : $v;
    $prefix = $prefix ?? '';
    $suffix = $suffix ?? '';
@endphp

<div class="space-y-3">
    @foreach ($series as $row)
        @php
            $pct = min(100, round(((float) $row['value'] / $max) * 100));
        @endphp
        <div>
            <div class="mb-1 flex items-center justify-between text-sm">
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $row['label'] }}</span>
                <span class="font-bold tabular-nums text-gray-900 dark:text-white">{{ $prefix }}{{ $format($row['value']) }}{{ $suffix }}</span>
            </div>
            <div class="h-3 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                <div class="h-full rounded-full bg-green-gradient transition-all" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    @endforeach
</div>
