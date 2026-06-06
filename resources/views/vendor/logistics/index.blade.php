@extends('vendor.layouts.app')

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
@php
    $isReturns = $type === 'returns';
@endphp
<div class="mx-auto max-w-3xl space-y-3 pb-[max(1rem,env(safe-area-inset-bottom))] sm:space-y-4">
    <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-4">
        <h1 class="text-lg font-bold tracking-tight text-gray-900 sm:text-xl">{{ $pageTitle }}</h1>
        <p class="mt-1 text-xs leading-snug text-gray-600 sm:text-sm">{{ $pageSubtitle }}</p>
        <p class="mt-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $countBadgeClass }}">
            {{ $countLabel }}
        </p>
    </div>

    <section class="overflow-hidden rounded-xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-100/80">
        <div id="logistics-order-list" class="max-h-[min(70vh,32rem)] overflow-y-auto overscroll-y-contain p-2 [-webkit-overflow-scrolling:touch]">
            @forelse($orders as $row)
                @php
                    $isDel = ($row['fulfillment_type'] ?? 'pickup') === 'delivery';
                @endphp
                <div data-logistics-row
                     class="mb-1.5 flex flex-col gap-2.5 rounded-lg border border-transparent px-2 py-2.5 last:mb-0 sm:mb-1 sm:flex-row sm:items-center sm:gap-3 sm:py-2
                            @if(! empty($row['is_highlight_today'])) {{ $todayRingClass }} @elseif(! empty($row['is_highlight_tomorrow'])) {{ $tomorrowRingClass }} @endif">
                    <a href="{{ route('vendor.orders.show', $row['id']) }}"
                       class="min-w-0 flex-1 rounded-lg hover:bg-gray-50/80 sm:py-0.5">
                        <p class="truncate text-sm font-bold leading-snug text-gray-900">{{ $row['title_line'] ?? $row['customer_name'] }}</p>
                        <p class="mt-0.5 truncate font-mono text-xs font-semibold text-gray-600">{{ $row['order_number'] }}</p>
                        @if(isset($row['total_units']))
                            <p class="mt-1 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-[10px] font-semibold leading-snug tabular-nums sm:text-[11px]">
                                <span class="text-gray-600">{{ trans_choice('vendor.logistics_row_total_items', $row['total_units'], ['count' => $row['total_units']]) }}</span>
                                <span class="text-gray-300" aria-hidden="true">|</span>
                                @if($isReturns)
                                    <span class="text-teal-800">{{ trans_choice('vendor.logistics_row_returned_items', $row['returned_units'], ['count' => $row['returned_units']]) }}</span>
                                @else
                                    <span class="text-teal-800">{{ trans_choice('vendor.logistics_row_delivered_items', $row['delivered_units'], ['count' => $row['delivered_units']]) }}</span>
                                @endif
                            </p>
                        @endif
                    </a>
                    <div class="flex shrink-0 flex-col items-start justify-center px-1 text-left sm:min-w-[6.25rem]">
                        <span class="text-xs font-bold uppercase tracking-wide text-gray-800">{{ $row['day_line'] }}</span>
                        @if(! empty($row['time_line']))
                            <span class="mt-0.5 inline-block rounded px-1.5 py-0.5 text-[11px] font-bold tabular-nums tracking-tight {{ $timeBadgeClass }}">{{ $row['time_line'] }}</span>
                        @endif
                    </div>
                    <div class="flex shrink-0 justify-start">
                        @if($isReturns)
                            <span class="inline-flex rounded-md bg-teal-100 px-2 py-1 text-[10px] font-semibold text-teal-800">
                                <i class="fas fa-rotate-left mr-1 text-[9px]" aria-hidden="true"></i>{{ __('vendor.dashboard_return_badge') }}
                            </span>
                        @else
                            <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold {{ $isDel ? 'bg-teal-100 text-teal-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $isDel ? __('vendor.dashboard_fulfillment_delivery') : __('vendor.dashboard_fulfillment_pickup') }}
                            </span>
                        @endif
                    </div>
                    @if($isReturns)
                        <button type="button"
                                class="logistics-mark-returned w-full shrink-0 rounded-lg bg-teal-600 px-3 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto sm:py-2 sm:text-[11px]"
                                data-order-id="{{ $row['id'] }}"
                                data-order-number="{{ $row['order_number'] }}"
                                data-rental-url="{{ route('vendor.orders.rental-status', $row['id']) }}"
                                data-lines-b64="{{ $row['lines_b64'] ?? '' }}">
                            <i class="fas fa-rotate-left mr-1.5 text-[10px]" aria-hidden="true"></i>{{ __('vendor.mark_returned') }}
                        </button>
                    @else
                        <button type="button"
                                class="logistics-mark-delivered w-full shrink-0 rounded-lg bg-teal-600 px-3 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto sm:py-2 sm:text-[11px]"
                                data-order-id="{{ $row['id'] }}"
                                data-order-number="{{ $row['order_number'] }}"
                                data-rental-url="{{ route('vendor.orders.rental-status', $row['id']) }}"
                                data-lines-b64="{{ $row['lines_b64'] ?? '' }}">
                            <i class="fas fa-truck mr-1.5 text-[10px]" aria-hidden="true"></i>{{ __('vendor.mark_delivered') }}
                        </button>
                    @endif
                </div>
            @empty
                <p id="logistics-empty-message" class="px-2 py-10 text-center text-sm text-gray-500">{{ $emptyMessage }}</p>
            @endforelse
        </div>
    </section>

    @if($orders->hasPages())
        <div class="rounded-xl border border-gray-200 bg-white px-3 py-2 shadow-sm">
            {{ $orders->links() }}
        </div>
    @endif
</div>

@if($type === 'deliveries')
    @include('vendor.partials.mark-delivered-modal')
@elseif($type === 'returns')
    @include('vendor.partials.mark-returned-modal')
@endif
<div id="logisticsToast" class="pointer-events-none fixed bottom-20 left-1/2 z-[70] hidden max-w-sm -translate-x-1/2 rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg sm:bottom-6" role="status"></div>
@endsection

@section('scripts')
@if($type === 'deliveries')
    @include('vendor.partials.mark-delivered-handoff-script')
@elseif($type === 'returns')
    @include('vendor.partials.mark-returned-handoff-script')
@endif
<script>
(function () {
    var listEl = document.getElementById('logistics-order-list');
    var emptyMsg = @json($emptyMessage);
    var toastEl = document.getElementById('logisticsToast');
    var toastTimer = null;
    var isReturns = @json($isReturns);

    function showLogisticsToast(message, type) {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.className = 'pointer-events-none fixed bottom-20 left-1/2 z-[70] max-w-sm -translate-x-1/2 rounded-xl px-4 py-3 text-sm font-medium text-white shadow-lg sm:bottom-6 ' +
            (type === 'error' ? 'bg-red-600' : type === 'info' ? 'bg-gray-800' : 'bg-emerald-600');
        toastEl.classList.remove('hidden');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.classList.add('hidden'); }, 3200);
    }

    function ensureEmptyState() {
        if (!listEl) return;
        if (listEl.querySelector('[data-logistics-row]')) return;
        if (listEl.querySelector('#logistics-empty-message')) return;
        var p = document.createElement('p');
        p.id = 'logistics-empty-message';
        p.className = 'px-2 py-10 text-center text-sm text-gray-500';
        p.textContent = emptyMsg;
        listEl.appendChild(p);
    }

    function logisticsOnSuccess(btn, data) {
        var rs = data && data.rental_status;
        var done = isReturns ? (rs && rs.returned_at) : (rs && rs.delivered_at);
        if (!done) {
            window.location.reload();
            return;
        }
        var row = btn.closest('[data-logistics-row]');
        if (row) row.remove();
        ensureEmptyState();
    }

    if (window.VendorMarkDelivered) {
        window.VendorMarkDelivered.toast = showLogisticsToast;
        window.VendorMarkDelivered._defaultOnSuccess = logisticsOnSuccess;
        document.querySelectorAll('.logistics-mark-delivered').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                window.VendorMarkDelivered.openFromButton(btn);
            });
        });
    }

    if (window.VendorMarkReturned) {
        window.VendorMarkReturned.toast = showLogisticsToast;
        window.VendorMarkReturned._defaultOnSuccess = logisticsOnSuccess;
        document.querySelectorAll('.logistics-mark-returned').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                window.VendorMarkReturned.openFromButton(btn);
            });
        });
    }
})();
</script>
@endsection
