@extends('vendor.layouts.app')

@section('title', __('vendor.orders'))
@section('page-title', __('vendor.orders'))

@php
    $searchQ = request('search');
    $statusQ = request('status');
    $indexParams = fn (?string $status = null) => array_filter(
        ['status' => $status, 'search' => $searchQ],
        fn ($v) => $v !== null && $v !== ''
    );
    $statusMeta = [
        'pending' => ['label' => __('vendor.pending'), 'icon' => 'fa-clock', 'tab' => 'border-amber-400/80 bg-amber-50 text-amber-900 shadow-sm', 'pill' => 'bg-amber-100 text-amber-800 ring-1 ring-amber-200/70'],
        'confirmed' => ['label' => __('vendor.confirmed'), 'icon' => 'fa-check', 'tab' => 'border-sky-400/80 bg-sky-50 text-sky-900 shadow-sm', 'pill' => 'bg-sky-100 text-sky-800 ring-1 ring-sky-200/70'],
        'ongoing' => ['label' => __('vendor.ongoing'), 'icon' => 'fa-spinner', 'tab' => 'border-violet-400/80 bg-violet-50 text-violet-900 shadow-sm', 'pill' => 'bg-violet-100 text-violet-800 ring-1 ring-violet-200/70'],
        'completed' => ['label' => __('vendor.completed'), 'icon' => 'fa-circle-check', 'tab' => 'border-emerald-400/80 bg-emerald-50 text-emerald-900 shadow-sm', 'pill' => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200/70'],
        'cancelled' => ['label' => __('vendor.cancelled'), 'icon' => 'fa-ban', 'tab' => 'border-rose-400/80 bg-rose-50 text-rose-900 shadow-sm', 'pill' => 'bg-rose-100 text-rose-800 ring-1 ring-rose-200/70'],
    ];
@endphp

@section('content')
<div class="mx-auto max-w-7xl space-y-3 pb-[max(1rem,env(safe-area-inset-bottom))] sm:space-y-4">
    {{-- Page header: title + Create Order (right); search full width below --}}
    <div class="space-y-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-3.5">
        {{-- Grid: title + subtitle left, button fixed right (mobile + desktop) --}}
        <div class="grid w-full min-w-0 grid-cols-[minmax(0,1fr)_auto] items-center gap-x-2 gap-y-1 sm:gap-x-4">
            <h1 class="col-start-1 row-start-1 min-w-0 text-lg font-bold tracking-tight text-gray-900 sm:text-xl">{{ __('vendor.orders') }}</h1>
            <a href="{{ route('vendor.orders.create') }}"
               class="col-start-2 row-span-2 row-start-1 inline-flex min-h-[44px] shrink-0 items-center justify-center gap-1.5 self-center rounded-lg bg-emerald-600 px-2.5 py-2 text-xs font-semibold text-white shadow-sm transition [touch-action:manipulation] hover:bg-emerald-700 active:scale-[0.98] sm:min-h-[40px] sm:gap-2 sm:px-4 sm:text-sm">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                <span class="whitespace-nowrap">{{ __('vendor.create_order') }}</span>
            </a>
            <p class="col-start-1 row-start-2 min-w-0 text-xs leading-snug text-gray-600 sm:text-sm">{{ __('vendor.orders_page_subtitle') }}</p>
        </div>
        <form method="GET" action="{{ route('vendor.orders.index') }}" class="relative w-full min-w-0 sm:max-w-md lg:max-w-lg">
            @if($statusQ)
                <input type="hidden" name="status" value="{{ $statusQ }}">
            @endif
            <label for="orders-search" class="sr-only">{{ __('vendor.search_by_order') }}</label>
            <input id="orders-search"
                   type="search"
                   name="search"
                   value="{{ $searchQ }}"
                   autocomplete="off"
                   inputmode="search"
                   enterkeyhint="search"
                   placeholder="{{ __('vendor.search_by_order') }}"
                   class="min-h-[44px] w-full rounded-lg border border-gray-200 bg-white py-2.5 pl-10 pr-10 text-sm text-gray-900 shadow-sm outline-none ring-emerald-500/15 transition placeholder:text-gray-400 focus:border-emerald-500 focus:ring-2 sm:min-h-[40px]">
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <i class="fas fa-search text-xs sm:text-sm" aria-hidden="true"></i>
            </span>
            @if($searchQ)
                <a href="{{ route('vendor.orders.index', $indexParams($statusQ ?: null)) }}"
                   class="absolute right-1 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 active:bg-gray-200"
                   title="{{ __('vendor.clear') }}"
                   aria-label="{{ __('vendor.clear') }}">
                    <i class="fas fa-times text-sm" aria-hidden="true"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- Status filters --}}
    <div class="rounded-xl border border-gray-200 bg-white p-2 shadow-sm sm:p-2.5">
        <p class="mb-1.5 hidden px-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500 sm:block">{{ __('vendor.order_status') }}</p>
        <div class="-mx-0.5 flex gap-1.5 overflow-x-auto overscroll-x-contain px-0.5 pb-0.5 [-webkit-overflow-scrolling:touch] [scrollbar-width:thin] snap-x snap-mandatory sm:flex-wrap sm:overflow-x-visible sm:snap-none sm:gap-2">
            <a href="{{ route('vendor.orders.index', $indexParams(null)) }}"
               class="flex min-h-[44px] shrink-0 snap-start items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-semibold transition [touch-action:manipulation] active:opacity-90 sm:min-h-0 sm:px-3.5 sm:py-2
                      {{ !$statusQ ? 'border-emerald-500/80 bg-emerald-600 text-white shadow-md shadow-emerald-600/15' : 'border-gray-200 bg-white text-gray-700 hover:border-emerald-200 hover:bg-emerald-50/60' }}">
                <i class="fas fa-layer-group text-xs opacity-90" aria-hidden="true"></i>
                <span>{{ __('vendor.all_orders') }}</span>
                <span class="rounded-full px-2 py-0.5 text-xs font-bold tabular-nums {{ !$statusQ ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $statusCounts['all'] }}</span>
            </a>
            @foreach($statusMeta as $key => $meta)
                <a href="{{ route('vendor.orders.index', $indexParams($key)) }}"
                   class="flex min-h-[44px] shrink-0 snap-start items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-semibold transition [touch-action:manipulation] active:opacity-90 sm:min-h-0 sm:px-3.5 sm:py-2
                          {{ $statusQ === $key ? $meta['tab'] : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50' }}">
                    <i class="fas {{ $meta['icon'] }} text-xs opacity-90" aria-hidden="true"></i>
                    <span>{{ $meta['label'] }}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-bold tabular-nums {{ $statusQ === $key ? 'bg-white/70 text-current' : 'bg-gray-100 text-gray-600' }}">{{ $statusCounts[$key] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200/80 bg-emerald-50/90 p-3 text-emerald-950 shadow-sm sm:p-4" role="status">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 sm:h-9 sm:w-9">
                <i class="fas fa-check" aria-hidden="true"></i>
            </span>
            <p class="min-w-0 flex-1 text-sm font-medium leading-snug sm:pt-0.5">{{ session('success') }}</p>
            <button type="button" onclick="this.closest('[role=status]').remove()" class="flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-xl text-emerald-700/80 transition hover:bg-emerald-100 hover:text-emerald-900 active:bg-emerald-200/80 [touch-action:manipulation]">
                <i class="fas fa-times text-base" aria-hidden="true"></i>
            </button>
        </div>
    @endif

    <div id="ordersContainer">
        @if(!$orders->isEmpty())
            {{-- Desktop --}}
            <div class="hidden overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm md:block">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                        <thead>
                            <tr class="bg-gray-50/90 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th scope="col" class="px-5 py-4 sm:px-6">{{ __('vendor.order_details') }}</th>
                                <th scope="col" class="px-5 py-4 sm:px-6">{{ __('vendor.customer') }}</th>
                                <th scope="col" class="px-5 py-4 sm:px-6">{{ __('vendor.date') }}</th>
                                <th scope="col" class="px-5 py-4 sm:px-6">{{ __('vendor.items_ordered') }}</th>
                                <th scope="col" class="px-5 py-4 sm:px-6">{{ __('vendor.total') }}</th>
                                <th scope="col" class="px-5 py-4 sm:px-6">{{ __('vendor.status') }}</th>
                                <th scope="col" class="px-5 py-4 sm:px-6"><span class="sr-only">{{ __('vendor.actions') }}</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($orders as $order)
                                @php
                                    $pill = $statusMeta[$order->status]['pill'] ?? 'bg-gray-100 text-gray-800 ring-1 ring-gray-200/70';
                                @endphp
                                <tr class="group transition-colors hover:bg-emerald-50/35">
                                    <td class="px-5 py-4 align-top sm:px-6">
                                        <p class="font-mono text-sm font-semibold text-gray-900">{{ $order->order_number }}</p>
                                        @if($order->event_name)
                                            <p class="mt-0.5 max-w-[14rem] truncate text-xs font-medium text-gray-600 sm:max-w-xs" title="{{ $order->event_name }}">{{ $order->event_name }}</p>
                                        @endif
                                        @if($order->start_at && $order->end_at)
                                            <p class="mt-1.5 flex items-center gap-1.5 text-xs text-gray-500">
                                                <i class="far fa-calendar-alt shrink-0 text-emerald-600/80" aria-hidden="true"></i>
                                                <span>{{ $order->start_at->format('M j') }} — {{ $order->end_at->format('M j, Y') }}</span>
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 align-top sm:px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-100 to-teal-100 text-xs font-bold text-emerald-800 ring-1 ring-emerald-200/50">
                                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($order->customer->name, 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate font-medium text-gray-900">{{ $order->customer->name }}</p>
                                                <p class="truncate text-xs text-gray-500">{{ $order->customer->mobile }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 align-top text-gray-700 sm:px-6">
                                        <p class="font-medium">{{ $order->created_at->format('M j, Y') }}</p>
                                        <p class="text-xs text-gray-500">{{ $order->created_at->format('g:i A') }}</p>
                                    </td>
                                    <td class="px-5 py-4 align-top sm:px-6">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                            <i class="fas fa-box text-[10px] text-gray-500" aria-hidden="true"></i>
                                            {{ $order->items->count() }} {{ __('vendor.items') }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 align-top sm:px-6">
                                        <p class="font-semibold tabular-nums text-emerald-700">₹{{ number_format($order->grand_total, 2) }}</p>
                                        @if($order->paid_amount > 0)
                                            <p class="mt-0.5 text-xs text-gray-500">{{ __('vendor.paid') }}: ₹{{ number_format($order->paid_amount, 2) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 align-top sm:px-6">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $pill }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 align-top sm:px-6">
                                        <a href="{{ route('vendor.orders.show', $order) }}"
                                           class="inline-flex items-center gap-2 rounded-xl border border-emerald-200/80 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 group-hover:border-emerald-300">
                                            <span>{{ __('vendor.view_order') }}</span>
                                            <i class="fas fa-chevron-right text-[10px] opacity-70" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile cards --}}
            <div class="space-y-4 md:hidden [touch-action:manipulation]">
                @foreach($orders as $order)
                    @php
                        $pill = $statusMeta[$order->status]['pill'] ?? 'bg-gray-100 text-gray-800 ring-1 ring-gray-200/70';
                    @endphp
                    <article class="overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-black/[0.03]">
                        <div class="flex items-start justify-between gap-3 border-b border-gray-100 bg-gradient-to-r from-gray-50/80 to-white px-4 py-3.5">
                            <div class="min-w-0">
                                <p class="font-mono text-sm font-bold text-gray-900">{{ $order->order_number }}</p>
                                @if($order->event_name)
                                    <p class="mt-0.5 truncate text-xs font-medium text-gray-600">{{ $order->event_name }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-bold {{ $pill }}">{{ ucfirst($order->status) }}</span>
                        </div>
                        <div class="space-y-4 p-4 sm:p-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-100 to-teal-100 text-sm font-bold text-emerald-800 ring-1 ring-emerald-200/50">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($order->customer->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-base font-semibold text-gray-900">{{ $order->customer->name }}</p>
                                    @php $telRaw = preg_replace('/[^0-9+]/', '', (string) $order->customer->mobile); @endphp
                                    @if(strlen($telRaw) >= 7)
                                        <a href="tel:{{ $telRaw }}" class="mt-0.5 block truncate text-sm text-emerald-700 underline-offset-2 hover:underline [touch-action:manipulation]">{{ $order->customer->mobile }}</a>
                                    @else
                                        <p class="mt-0.5 truncate text-sm text-gray-500">{{ $order->customer->mobile }}</p>
                                    @endif
                                </div>
                            </div>
                            <dl class="grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-xl bg-gray-50/90 p-3.5 ring-1 ring-gray-100">
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.date') }}</dt>
                                    <dd class="mt-1 font-medium text-gray-900">{{ $order->created_at->format('M j, Y') }}</dd>
                                    <dd class="text-xs text-gray-500">{{ $order->created_at->format('g:i A') }}</dd>
                                </div>
                                <div class="rounded-xl bg-gray-50/90 p-3.5 ring-1 ring-gray-100">
                                    <dt class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.items') }}</dt>
                                    <dd class="mt-1 font-semibold text-gray-900">{{ $order->items->count() }}</dd>
                                </div>
                                @if($order->start_at && $order->end_at)
                                    <div class="col-span-2 rounded-xl bg-emerald-50/50 p-3.5 ring-1 ring-emerald-100/80">
                                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-emerald-800/80">{{ __('vendor.booking_dates') }}</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-900">{{ $order->start_at->format('M j') }} — {{ $order->end_at->format('M j, Y') }}</dd>
                                    </div>
                                @endif
                                <div class="col-span-2 flex items-center justify-between rounded-xl border border-emerald-100/80 bg-emerald-50/30 px-3.5 py-3">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-emerald-900/80">{{ __('vendor.total') }}</span>
                                    <span class="text-lg font-bold tabular-nums text-emerald-700 sm:text-xl">₹{{ number_format($order->grand_total, 2) }}</span>
                                </div>
                            </dl>
                            @if($order->paid_amount > 0)
                                <p class="text-center text-sm text-gray-500">{{ __('vendor.paid') }}: ₹{{ number_format($order->paid_amount, 2) }}</p>
                            @endif
                            <a href="{{ route('vendor.orders.show', $order) }}"
                               class="flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3.5 text-base font-semibold text-white shadow-md shadow-emerald-600/20 transition [touch-action:manipulation] active:bg-emerald-700 sm:text-sm">
                                {{ __('vendor.view_order') }}
                                <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="-mx-1 flex justify-center overflow-x-auto px-1 pb-2 pt-2 [-webkit-overflow-scrolling:touch]">
                <div class="inline-flex min-w-0 justify-center">
                    {{ $orders->onEachSide(1)->links() }}
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-gray-300/90 bg-gradient-to-b from-white to-gray-50/80 px-6 py-14 text-center shadow-sm sm:py-16">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 ring-1 ring-emerald-200/60">
                    <i class="fas fa-receipt text-2xl" aria-hidden="true"></i>
                </div>
                <h3 class="mt-5 text-lg font-bold text-gray-900">{{ __('vendor.no_orders_found') }}</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-gray-600">
                    @if($searchQ)
                        {{ __('vendor.orders_empty_search') }}
                    @elseif($statusQ)
                        {{ __('vendor.orders_empty_status') }}
                    @else
                        {{ __('vendor.orders_empty_default') }}
                    @endif
                </p>
                <div class="mt-8 flex w-full max-w-md flex-col gap-3 sm:mx-auto sm:max-w-none sm:flex-row sm:flex-wrap sm:justify-center">
                    @if($searchQ || $statusQ)
                        <a href="{{ route('vendor.orders.index') }}"
                           class="inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-base font-semibold text-gray-700 shadow-sm transition [touch-action:manipulation] hover:bg-gray-50 sm:w-auto sm:py-2.5 sm:text-sm">
                            <i class="fas fa-rotate-left text-xs" aria-hidden="true"></i>
                            {{ __('vendor.clear_filters') }}
                        </a>
                    @endif
                    <a href="{{ route('vendor.orders.create') }}"
                       class="inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-base font-semibold text-white shadow-md shadow-emerald-600/20 transition [touch-action:manipulation] hover:bg-emerald-700 sm:w-auto sm:py-2.5 sm:text-sm">
                        <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                        {{ __('vendor.create_order') }}
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
