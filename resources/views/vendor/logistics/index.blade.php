@extends('vendor.layouts.app')

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
<div class="mx-auto max-w-3xl space-y-3 pb-[max(1rem,env(safe-area-inset-bottom))] sm:space-y-4">
    <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-4">
        <h1 class="text-lg font-bold tracking-tight text-gray-900 sm:text-xl">{{ $pageTitle }}</h1>
        <p class="mt-1 text-xs leading-snug text-gray-600 sm:text-sm">{{ $pageSubtitle }}</p>
        <p class="mt-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $countBadgeClass }}">
            {{ $countLabel }}
        </p>
    </div>

    <section class="overflow-hidden rounded-xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-100/80">
        <div class="max-h-[min(70vh,32rem)] overflow-y-auto overscroll-y-contain p-2 [-webkit-overflow-scrolling:touch]">
            @forelse($orders as $row)
                <a href="{{ route('vendor.orders.show', $row['id']) }}"
                   class="mb-1 flex items-center justify-between gap-2 rounded-lg border border-transparent px-2 py-2 last:mb-0 hover:border-gray-100 hover:bg-gray-50/80
                          @if(! empty($row['is_highlight_today'])) {{ $todayRingClass }} @elseif(! empty($row['is_highlight_tomorrow'])) {{ $tomorrowRingClass }} @endif">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-gray-900">{{ $row['customer_name'] }}</p>
                        <p class="truncate text-xs leading-snug text-gray-500">
                            <span class="text-gray-400">#{{ $row['order_number'] }}</span>
                            <span class="mx-1 text-gray-300">·</span>
                            <span class="font-semibold text-gray-800">{{ $row['day_line'] }}</span>
                            @if(! empty($row['time_line']))
                                <span class="mx-1 text-gray-300">·</span>
                                <span class="inline-block rounded px-1.5 py-0.5 text-[11px] font-bold tabular-nums tracking-tight {{ $timeBadgeClass }}">{{ $row['time_line'] }}</span>
                            @endif
                        </p>
                    </div>
                    @if($type === 'deliveries')
                        @php $isDel = ($row['fulfillment_type'] ?? 'pickup') === 'delivery'; @endphp
                        <span class="shrink-0 self-start rounded-md px-1.5 py-0.5 text-[10px] font-semibold {{ $isDel ? 'bg-sky-100 text-sky-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $isDel ? __('vendor.dashboard_fulfillment_delivery') : __('vendor.dashboard_fulfillment_pickup') }}
                        </span>
                    @else
                        <span class="shrink-0 self-start rounded-md bg-violet-100 px-1.5 py-0.5 text-[10px] font-semibold text-violet-800">
                            <i class="fas fa-rotate-left mr-0.5 text-[9px]" aria-hidden="true"></i>{{ __('vendor.dashboard_return_badge') }}
                        </span>
                    @endif
                </a>
            @empty
                <p class="px-2 py-10 text-center text-sm text-gray-500">{{ $emptyMessage }}</p>
            @endforelse
        </div>
    </section>

    @if($orders->hasPages())
        <div class="rounded-xl border border-gray-200 bg-white px-3 py-2 shadow-sm">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
