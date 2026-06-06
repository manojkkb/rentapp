@extends('vendor.layouts.app')

@section('title', __('vendor.title'))
@section('page-title', __('vendor.home'))

@php
    $s = $dashboard['stats'];
    $ost = $dashboard['order_status_counts'] ?? [];
    $rc = $dashboard['resource_counts'] ?? ['items' => 0, 'staff' => 0, 'customers' => 0];
    $log = $dashboard['logistics'] ?? ['outgoing_count' => 0, 'outgoing_orders' => [], 'return_count' => 0, 'return_orders' => []];
    $recent = $dashboard['recent_activities'];
    $popular = $dashboard['popular_items'];
    $vendorName = Auth::user()->currentVendor()->name ?? 'Vendor';
@endphp

@section('content')
@if(($vendorSubscriptionStatus ?? '') === 'trial' && isset($vendorTrialDaysRemaining, $vendorTrialEndsAt))
    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-amber-300/70 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 px-4 py-3.5 shadow-sm ring-1 ring-amber-200/60">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700 shadow-sm">
            <i class="fas fa-gift text-base" aria-hidden="true"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-amber-950">
                {{ __('vendor.dashboard_trial_heading') }}
            </p>
            <p class="mt-0.5 text-sm leading-snug text-amber-900/90">
                {{ __('vendor.subscription_trial_banner', [
                    'days' => $vendorTrialDaysRemaining,
                    'date' => $vendorTrialEndsAt->format('d M Y'),
                ]) }}
            </p>
        </div>
        <a href="{{ route('vendor.subscription.plans') }}"
           class="inline-flex shrink-0 items-center gap-1 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-amber-700">
            {{ __('vendor.dashboard_trial_cta') }}
            <i class="fas fa-arrow-right text-[10px]" aria-hidden="true"></i>
        </a>
    </div>
@endif

{{-- Mobile PWA install strip --}}
<div x-data="vendorDashboardPwaInstall()"
     x-show="show"
     x-transition.opacity.duration.200ms
     x-cloak
     class="md:hidden -mx-4 -mt-4 mb-3 border-b border-emerald-200/80 bg-gradient-to-b from-emerald-50 to-white px-4 py-2.5"
     style="display: none;">
    <div class="mx-auto flex max-w-6xl items-start gap-2.5">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
            <i class="fas fa-mobile-screen-button text-sm" aria-hidden="true"></i>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold leading-snug text-gray-900">{{ __('vendor.install_app_title') }}</p>
            <p class="mt-0.5 text-[11px] leading-snug text-gray-600">{{ __('vendor.install_app_body') }}</p>
            <p class="mt-1 text-[11px] leading-snug text-gray-500" x-show="isIOS">{{ __('vendor.install_app_ios_help') }}</p>
            <p class="mt-1 text-[11px] leading-snug text-gray-500" x-show="!isIOS && !canPrompt">{{ __('vendor.install_app_android_hint') }}</p>
        </div>
        <button type="button"
                x-show="!isIOS && canPrompt"
                @click="install()"
                class="inline-flex shrink-0 items-center justify-center self-center rounded-lg bg-emerald-600 px-2.5 py-1.5 text-[11px] font-semibold text-white transition-colors hover:bg-emerald-700 active:bg-emerald-800">
            {{ __('vendor.install_app_cta') }}
        </button>
    </div>
</div>

<div class="mx-auto max-w-6xl space-y-3 pb-4 md:space-y-4">
    {{-- Welcome + primary actions (single compact row) --}}
    <div class="relative overflow-hidden rounded-xl border border-emerald-200/60 bg-gradient-to-br from-emerald-50/90 via-white/75 to-teal-50/50 px-3 py-3 shadow-sm ring-1 ring-emerald-100/50 backdrop-blur-sm sm:px-4 sm:py-3.5">
        <div class="pointer-events-none absolute -right-6 -top-8 h-28 w-28 rounded-full bg-emerald-400/15 blur-2xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-10 left-1/3 h-24 w-24 rounded-full bg-teal-400/10 blur-2xl" aria-hidden="true"></div>
        <div class="relative flex flex-wrap items-center justify-between gap-2">
            <div class="min-w-0 flex-1">
                <h1 class="truncate text-base font-bold leading-tight text-gray-900 sm:text-lg md:text-xl">
                    {{ __('vendor.welcome_back', ['name' => $vendorName]) }}
                </h1>
                <p class="mt-0.5 text-[11px] text-gray-600 sm:text-xs">{{ __('vendor.today_summary') }}</p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-1.5 sm:gap-2">
                <a href="{{ route('vendor.items.create') }}"
                   class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-emerald-200/80 bg-white/70 px-2.5 text-xs font-semibold text-emerald-800 shadow-sm backdrop-blur-sm transition hover:border-emerald-300 hover:bg-emerald-50/80 sm:px-3 sm:text-sm">
                    <i class="fas fa-plus text-[11px]" aria-hidden="true"></i>
                    {{ __('vendor.add_item') }}
                </a>
                <a href="{{ route('vendor.orders.new') }}"
                   class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-emerald-600/90 px-2.5 text-xs font-semibold text-white shadow-sm shadow-emerald-600/15 backdrop-blur-sm transition hover:bg-emerald-700 sm:px-3 sm:text-sm">
                    <i class="fas fa-file-circle-plus text-[11px]" aria-hidden="true"></i>
                    {{ __('vendor.create_order') }}
                </a>
            </div>
        </div>
    </div>

    {{-- Stats: compact cards — minimal vertical space --}}
    <div class="grid grid-cols-2 gap-1.5 sm:gap-2 lg:grid-cols-4">
        <div class="rounded-lg border border-gray-200/90 bg-white px-2 py-1.5 shadow-sm ring-1 ring-gray-100/80 sm:px-2.5 sm:py-2">
            <div class="mb-0.5 flex items-center gap-1.5">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                    <i class="fas fa-box text-[10px]" aria-hidden="true"></i>
                </div>
                <h3 class="min-w-0 truncate text-[10px] font-semibold uppercase leading-tight tracking-wide text-gray-500">{{ __('vendor.total_items') }}</h3>
            </div>
            <p class="text-lg font-bold leading-none tabular-nums text-gray-900 sm:text-xl">{{ (int) $s['total_items'] }}</p>
            <p class="mt-0.5 truncate text-[10px] leading-tight text-gray-500">{{ (int) $s['active_items'] }} {{ __('vendor.active_listings') }}</p>
        </div>
        <div class="rounded-lg border border-gray-200/90 bg-white px-2 py-1.5 shadow-sm ring-1 ring-gray-100/80 sm:px-2.5 sm:py-2">
            <div class="mb-0.5 flex items-center gap-1.5">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-teal-100 text-teal-600">
                    <i class="fas fa-receipt text-[10px]" aria-hidden="true"></i>
                </div>
                <h3 class="min-w-0 truncate text-[10px] font-semibold uppercase leading-tight tracking-wide text-gray-500">{{ __('vendor.total_orders') }}</h3>
            </div>
            <p class="text-lg font-bold leading-none tabular-nums text-gray-900 sm:text-xl">{{ (int) $s['total_orders'] }}</p>
            <p class="mt-0.5 truncate text-[10px] leading-tight text-gray-500">{{ (int) $s['monthly_orders'] }} {{ __('vendor.this_month') }}</p>
        </div>
        <div class="rounded-lg border border-gray-200/90 bg-white px-2 py-1.5 shadow-sm ring-1 ring-gray-100/80 sm:px-2.5 sm:py-2">
            <div class="mb-0.5 flex items-center gap-1.5">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                    <i class="fas fa-rupee-sign text-[10px]" aria-hidden="true"></i>
                </div>
                <h3 class="min-w-0 truncate text-[10px] font-semibold uppercase leading-tight tracking-wide text-gray-500">{{ __('vendor.revenue') }}</h3>
            </div>
            <p class="text-lg font-bold leading-none tabular-nums text-gray-900 sm:text-xl">₹{{ number_format((int) round($s['total_revenue']), 0, '.', ',') }}</p>
            <p class="mt-0.5 truncate text-[10px] leading-tight text-gray-500">₹{{ number_format((int) round($s['monthly_revenue']), 0, '.', ',') }} {{ __('vendor.this_month') }}</p>
        </div>
        <div class="rounded-lg border border-gray-200/90 bg-white px-2 py-1.5 shadow-sm ring-1 ring-gray-100/80 sm:px-2.5 sm:py-2">
            <div class="mb-0.5 flex items-center gap-1.5">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-green-100 text-green-600">
                    <i class="fas fa-star text-[10px]" aria-hidden="true"></i>
                </div>
                <h3 class="min-w-0 truncate text-[10px] font-semibold uppercase leading-tight tracking-wide text-gray-500">{{ __('vendor.rating') }}</h3>
            </div>
            <p class="text-lg font-bold leading-none tabular-nums text-gray-900 sm:text-xl">{{ number_format((float) $s['average_rating'], 1) }}</p>
            <p class="mt-0.5 truncate text-[10px] leading-tight text-gray-500">{!! __('vendor.from_reviews', ['count' => '<span class="font-semibold text-gray-700">'.(int) $s['total_reviews'].'</span>']) !!}</p>
        </div>
    </div>

    {{-- Order status + Items / Staff / Customers (one row on sm+) --}}
    <div class="flex flex-col gap-2 rounded-lg border border-gray-200/90 bg-white px-2 py-1.5 shadow-sm ring-1 ring-gray-100/80 sm:flex-row sm:items-stretch sm:gap-2 sm:px-2.5 sm:py-2">
        <div class="min-w-0 flex-1">
            <p class="mb-1 text-[10px] font-bold uppercase tracking-wide text-gray-400">{{ __('vendor.order_status') }}</p>
            <div class="flex flex-wrap gap-1 sm:gap-1.5">
                @foreach (\App\Models\Order::STATUSES as $st)
                    @php
                        $n = (int) ($ost[$st] ?? 0);
                        $chip = match ($st) {
                            'pending' => 'border-amber-200/90 bg-amber-50 text-amber-900 hover:bg-amber-100/90',
                            'confirmed' => 'border-teal-200/90 bg-teal-50 text-teal-900 hover:bg-teal-100/90',
                            'completed' => 'border-emerald-200/90 bg-emerald-50 text-emerald-900 hover:bg-emerald-100/90',
                            'cancelled' => 'border-rose-200/90 bg-rose-50 text-rose-900 hover:bg-rose-100/90',
                            default => 'border-gray-200 bg-gray-50 text-gray-800',
                        };
                        $label = match ($st) {
                            'pending' => __('vendor.pending'),
                            'confirmed' => __('vendor.confirmed'),
                            'completed' => __('vendor.completed'),
                            'cancelled' => __('vendor.cancelled'),
                            default => $st,
                        };
                    @endphp
                    <a href="{{ route('vendor.orders.index', ['status' => $st]) }}"
                       class="inline-flex min-h-[32px] items-center gap-1 rounded-md border px-2 py-0.5 text-[10px] font-semibold leading-tight shadow-sm transition sm:min-h-0 sm:px-2.5 sm:py-1 sm:text-[11px] {{ $chip }}">
                        <span class="max-w-[5.5rem] truncate sm:max-w-none">{{ $label }}</span>
                        <span class="rounded bg-white/80 px-1 py-px text-[10px] font-bold tabular-nums text-current ring-1 ring-black/5 sm:text-[11px]">{{ $n }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="grid shrink-0 grid-cols-3 gap-1.5 border-t border-gray-100 pt-2 sm:w-[13.5rem] sm:border-l sm:border-t-0 sm:pl-2 sm:pt-0 md:w-[15rem]">
            <a href="{{ route('vendor.items.index') }}" class="flex flex-col items-center justify-center rounded-md border border-emerald-200/80 bg-emerald-50/90 px-1 py-1.5 text-center shadow-sm ring-1 ring-emerald-100/80 transition hover:bg-emerald-100/90 sm:py-2">
                <span class="text-[9px] font-bold uppercase leading-tight tracking-wide text-emerald-800/90">{{ __('vendor.items') }}</span>
                <span class="mt-0.5 text-lg font-bold tabular-nums leading-none text-emerald-950 sm:text-xl">{{ (int) ($rc['items'] ?? 0) }}</span>
            </a>
            <a href="{{ route('vendor.staff.index') }}" class="flex flex-col items-center justify-center rounded-md border border-teal-200/80 bg-teal-50/90 px-1 py-1.5 text-center shadow-sm ring-1 ring-teal-100/80 transition hover:bg-teal-100/90 sm:py-2">
                <span class="text-[9px] font-bold uppercase leading-tight tracking-wide text-teal-800/90">{{ __('vendor.staff') }}</span>
                <span class="mt-0.5 text-lg font-bold tabular-nums leading-none text-teal-950 sm:text-xl">{{ (int) ($rc['staff'] ?? 0) }}</span>
            </a>
            <a href="{{ route('vendor.customers.index') }}" class="flex flex-col items-center justify-center rounded-md border border-green-200/80 bg-green-50/90 px-1 py-1.5 text-center shadow-sm ring-1 ring-green-100/80 transition hover:bg-green-100/90 sm:py-2">
                <span class="text-[9px] font-bold uppercase leading-tight tracking-wide text-green-800/90">{{ __('vendor.customers') }}</span>
                <span class="mt-0.5 text-lg font-bold tabular-nums leading-none text-green-950 sm:text-xl">{{ (int) ($rc['customers'] ?? 0) }}</span>
            </a>
        </div>
    </div>

    {{-- Upcoming delivery & returns (before recent activity) --}}
    <section class="overflow-hidden rounded-xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-100/80">
        <div class="flex flex-col gap-2 border-b border-gray-100 px-3 py-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between">
            <h2 class="flex items-center gap-1.5 text-sm font-bold text-gray-900">
                <i class="fas fa-truck-fast text-emerald-600 text-xs" aria-hidden="true"></i>
                {{ __('vendor.dashboard_upcoming_title') }}
            </h2>
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 ring-1 ring-emerald-100">
                    @choice('vendor.dashboard_outgoing_choice', (int) $log['outgoing_count'])
                </span>
                <span class="inline-flex items-center rounded-full bg-teal-50 px-2 py-0.5 text-[11px] font-semibold text-teal-800 ring-1 ring-teal-100">
                    @choice('vendor.dashboard_returns_choice', (int) $log['return_count'])
                </span>
            </div>
        </div>
        <div class="grid gap-0 divide-y divide-gray-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
            <div class="flex min-h-0 flex-col p-2">
                <p class="mb-1.5 px-1 text-[10px] font-bold uppercase tracking-wide text-gray-400">{{ __('vendor.dashboard_upcoming_outgoing_heading') }}</p>
                <div class="max-h-40 overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch]">
                    @forelse($log['outgoing_orders'] as $row)
                        @php $isDel = ($row['fulfillment_type'] ?? 'pickup') === 'delivery'; @endphp
                        <a href="{{ route('vendor.orders.show', $row['id']) }}"
                           class="mb-1 flex items-center justify-between gap-2 rounded-lg border border-transparent px-2 py-1.5 last:mb-0 hover:border-gray-100 hover:bg-gray-50/80 @if(! empty($row['is_handoff_today'])) ring-1 ring-emerald-200/80 bg-emerald-50/40 @elseif(! empty($row['is_handoff_tomorrow'])) ring-1 ring-emerald-200/60 bg-emerald-50/25 @endif">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-gray-900">{{ $row['customer_name'] }}</p>
                                <p class="truncate text-[10px] leading-snug text-gray-500">
                                    <span class="text-gray-400">#{{ $row['order_number'] }}</span>
                                    <span class="mx-1 text-gray-300">·</span>
                                    <span class="font-semibold text-gray-800">{{ $row['day_line'] ?? ($row['when_label'] ?? '—') }}</span>
                                    @if(! empty($row['time_line']))
                                        <span class="mx-1 text-gray-300">·</span>
                                        <span class="inline-block rounded px-1.5 py-0.5 text-[11px] font-bold tabular-nums tracking-tight text-emerald-900 ring-1 ring-emerald-300/70 bg-emerald-100">{{ $row['time_line'] }}</span>
                                    @endif
                                </p>
                            </div>
                            <span class="shrink-0 self-start rounded-md px-1.5 py-0.5 text-[10px] font-semibold {{ $isDel ? 'bg-emerald-100 text-emerald-800' : 'bg-teal-100 text-teal-800' }}">
                                {{ $isDel ? __('vendor.dashboard_fulfillment_delivery') : __('vendor.dashboard_fulfillment_pickup') }}
                            </span>
                        </a>
                    @empty
                        <p class="px-2 py-6 text-center text-[11px] text-gray-500">{{ __('vendor.dashboard_outgoing_empty') }}</p>
                    @endforelse
                </div>
            </div>
            <div class="flex min-h-0 flex-col p-2">
                <p class="mb-1.5 px-1 text-[10px] font-bold uppercase tracking-wide text-gray-400">{{ __('vendor.dashboard_upcoming_returns_heading') }}</p>
                <div class="max-h-40 overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch]">
                    @forelse($log['return_orders'] as $row)
                        <a href="{{ route('vendor.orders.show', $row['id']) }}"
                           class="mb-1 flex items-center justify-between gap-2 rounded-lg border border-transparent px-2 py-1.5 last:mb-0 hover:border-gray-100 hover:bg-gray-50/80 @if(! empty($row['is_return_today'])) ring-1 ring-teal-200/80 bg-teal-50/40 @elseif(! empty($row['is_return_tomorrow'])) ring-1 ring-teal-200/50 bg-teal-50/25 @endif">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-gray-900">{{ $row['customer_name'] }}</p>
                                <p class="truncate text-[10px] leading-snug text-gray-500">
                                    <span class="text-gray-400">#{{ $row['order_number'] }}</span>
                                    <span class="mx-1 text-gray-300">·</span>
                                    <span class="font-semibold text-gray-800">{{ $row['day_line'] ?? ($row['when_label'] ?? '—') }}</span>
                                    @if(! empty($row['time_line']))
                                        <span class="mx-1 text-gray-300">·</span>
                                        <span class="inline-block rounded px-1.5 py-0.5 text-[11px] font-bold tabular-nums tracking-tight text-teal-900 ring-1 ring-teal-300/70 bg-teal-100">{{ $row['time_line'] }}</span>
                                    @endif
                                </p>
                            </div>
                            <span class="shrink-0 self-start rounded-md bg-teal-100 px-1.5 py-0.5 text-[10px] font-semibold text-teal-800">
                                <i class="fas fa-rotate-left mr-0.5 text-[9px]" aria-hidden="true"></i>{{ __('vendor.dashboard_return_badge') }}
                            </span>
                        </a>
                    @empty
                        <p class="px-2 py-6 text-center text-[11px] text-gray-500">{{ __('vendor.dashboard_returns_empty') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- Recent orders + popular items: side by side, scroll inside --}}
    <div class="grid gap-3 lg:grid-cols-2">
        <section class="flex min-h-0 flex-col overflow-hidden rounded-xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-100/80">
            <div class="flex items-center justify-between border-b border-gray-100 px-3 py-2">
                <h2 class="flex items-center gap-1.5 text-sm font-bold text-gray-900">
                    <i class="fas fa-clock text-emerald-600 text-xs" aria-hidden="true"></i>
                    {{ __('vendor.recent_activity') }}
                </h2>
                <a href="{{ route('vendor.orders.index') }}" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700">{{ __('vendor.view_all') }}</a>
            </div>
            <div class="max-h-56 overflow-y-auto overscroll-y-contain p-2 [-webkit-overflow-scrolling:touch]">
                @forelse($recent as $activity)
                    @php
                        $st = $activity['status'] ?? '';
                        $badge = match ($st) {
                            'pending' => 'bg-amber-100 text-amber-800',
                            'confirmed' => 'bg-teal-100 text-teal-800',
                            'completed' => 'bg-emerald-100 text-emerald-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <a href="{{ route('vendor.orders.show', $activity['id']) }}"
                       class="mb-1.5 flex items-center justify-between gap-2 rounded-lg border border-transparent px-2 py-1.5 last:mb-0 hover:border-gray-100 hover:bg-gray-50/80">
                        <div class="min-w-0 flex items-center gap-2">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                <i class="fas fa-user text-xs" aria-hidden="true"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-gray-900">{{ $activity['customer_name'] }}</p>
                                <p class="truncate text-[10px] text-gray-500">{{ __('vendor.total_items_count', ['count' => (int) $activity['items_count']]) }} · ₹{{ number_format((int) round($activity['total_amount']), 0, '.', ',') }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-0.5">
                            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold capitalize {{ $badge }}">{{ str_replace('_', ' ', $st) }}</span>
                            <span class="text-[10px] text-gray-400">{{ $activity['created_at'] }}</span>
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                            <i class="fas fa-inbox text-lg" aria-hidden="true"></i>
                        </div>
                        <p class="text-xs font-semibold text-gray-800">{{ __('vendor.no_activity') }}</p>
                        <p class="mt-0.5 max-w-xs text-[11px] text-gray-500">{{ __('vendor.start_adding_items') }}</p>
                        <a href="{{ route('vendor.items.create') }}" class="mt-3 inline-flex h-9 items-center rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white hover:bg-emerald-700">
                            <i class="fas fa-plus mr-1.5 text-[10px]" aria-hidden="true"></i>{{ __('vendor.add_first_item') }}
                        </a>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="flex min-h-0 flex-col overflow-hidden rounded-xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-100/80">
            <div class="flex items-center justify-between border-b border-gray-100 px-3 py-2">
                <h2 class="flex items-center gap-1.5 text-sm font-bold text-gray-900">
                    <i class="fas fa-fire text-emerald-600 text-xs" aria-hidden="true"></i>
                    {{ __('vendor.popular_items') }}
                </h2>
                <a href="{{ route('vendor.items.index') }}" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700">{{ __('vendor.view_all') }}</a>
            </div>
            <div class="max-h-56 overflow-y-auto overscroll-y-contain p-2 [-webkit-overflow-scrolling:touch]">
                @forelse($popular as $index => $item)
                    <a href="{{ route('vendor.items.show', $item['id']) }}"
                       class="mb-1.5 flex items-center justify-between gap-2 rounded-lg border border-transparent px-2 py-1.5 last:mb-0 hover:border-gray-100 hover:bg-gray-50/80">
                        <div class="flex min-w-0 items-center gap-2">
                            @if(! empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="" class="h-8 w-8 shrink-0 rounded-md object-cover ring-1 ring-gray-100">
                            @else
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-gradient-to-br from-emerald-400 to-green-600 text-[10px] font-bold text-white">#{{ $index + 1 }}</span>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-gray-900">{{ $item['name'] }}</p>
                                <p class="text-[10px] text-gray-500">₹{{ number_format((int) round($item['price']), 0, '.', ',') }}</p>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <p class="text-xs font-bold tabular-nums text-emerald-700">{{ (int) $item['orders_count'] }}</p>
                            <p class="text-[10px] text-gray-400">{{ __('vendor.orders') }}</p>
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                            <i class="fas fa-box-open text-lg" aria-hidden="true"></i>
                        </div>
                        <p class="text-xs font-semibold text-gray-800">{{ __('vendor.no_items_yet') }}</p>
                        <p class="mt-0.5 max-w-xs text-[11px] text-gray-500">{{ __('vendor.add_items_see_popular') }}</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</div>

@if (session('success'))
    <div x-data="{ show: true }"
         x-show="show"
         x-transition
         x-init="setTimeout(() => show = false, 5000)"
         class="fixed bottom-20 right-4 z-50 flex max-w-sm items-center space-x-3 rounded-lg bg-emerald-500 px-4 py-3 text-white shadow-lg md:bottom-4">
        <i class="fas fa-check-circle text-xl shrink-0" aria-hidden="true"></i>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium">{{ __('vendor.success') }}!</p>
            <p class="truncate text-xs text-emerald-50">{{ session('success') }}</p>
        </div>
        <button type="button" @click="show = false" class="shrink-0 text-white hover:text-emerald-100" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif
@endsection
