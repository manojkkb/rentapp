@extends('vendor.layouts.app')

@php
    $eventCount = count($events);
    $orderStatusLabels = [
        'pending' => __('vendor.pending'),
        'confirmed' => __('vendor.confirmed'),
        'ongoing' => __('vendor.ongoing'),
        'completed' => __('vendor.completed'),
        'cancelled' => __('vendor.cancelled'),
        'overdue' => __('vendor.overdue'),
    ];
    $orderShowBase = rtrim(route('vendor.orders.index'), '/') . '/';
@endphp

@section('title', __('vendor.booking_calendar'))
@section('page-title', __('vendor.calendar'))

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<style>
    @keyframes calendarModalIn {
        from { opacity: 0; transform: translateY(12px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .calendar-modal-panel { animation: calendarModalIn 0.25s ease-out; }

    #calendar {
        --fc-border-color: #e5e7eb;
        --fc-today-bg-color: #ecfdf5;
        --fc-page-bg-color: #ffffff;
        --fc-button-bg-color: #059669;
        --fc-button-border-color: #059669;
        --fc-button-hover-bg-color: #047857;
        --fc-button-hover-border-color: #047857;
        --fc-button-active-bg-color: #047857;
        --fc-button-active-border-color: #047857;
    }
    .fc { overflow-x: auto; -webkit-overflow-scrolling: touch; font-family: inherit; }
    .fc .fc-toolbar-title { font-size: 1.125rem !important; font-weight: 700 !important; color: #111827; letter-spacing: -0.02em; }
    .fc .fc-toolbar { gap: 0.75rem !important; padding: 0.35rem 0 0.5rem !important; }
    .fc .fc-toolbar-chunk { display: flex !important; align-items: center !important; flex-wrap: wrap !important; gap: 0.5rem !important; margin: 0.125rem 0 !important; }
    .fc .fc-button-group { gap: 0.5rem !important; margin: 0.125rem !important; }
    .fc .fc-button-group > .fc-button { margin-left: 0 !important; margin-right: 0 !important; }
    .fc .fc-button { font-size: 0.8125rem !important; padding: 0.5rem 0.95rem !important; border-radius: 0.625rem !important; font-weight: 600 !important; text-transform: capitalize !important; box-shadow: 0 1px 2px rgb(0 0 0 / 0.05); margin: 0.125rem 0.15rem !important; }
    .fc .fc-button-primary { background-color: #059669 !important; border-color: #059669 !important; }
    .fc .fc-button-primary:hover { background-color: #047857 !important; border-color: #047857 !important; }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active { background-color: #047857 !important; border-color: #047857 !important; }
    .fc .fc-daygrid-day-number { font-size: 0.8125rem; font-weight: 600; color: #374151; padding: 0.4rem 0.5rem; }
    .fc .fc-event { border-radius: 0.375rem !important; padding: 0.125rem 0.375rem !important; font-size: 0.75rem !important; font-weight: 500 !important; cursor: pointer !important; border-width: 1px !important; }
    .fc .fc-daygrid-event { margin: 0.125rem 0.25rem !important; }
    .fc .fc-col-header-cell { background: linear-gradient(to bottom, #f9fafb, #f3f4f6); }
    .fc .fc-col-header-cell-cushion { font-size: 0.75rem; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; padding: 0.65rem 0.25rem; }
    .fc .fc-daygrid-day.fc-day-today { background-color: rgba(16, 185, 129, 0.08) !important; }
    .fc .fc-toolbar { flex-wrap: wrap; gap: 0.5rem; }
    .fc .fc-toolbar.fc-header-toolbar { margin-bottom: 1rem !important; }
    .fc .fc-scrollgrid { border-radius: 0.75rem; overflow: hidden; border: 1px solid #e5e7eb !important; }
    .fc .fc-view-harness { overflow: visible !important; }

    @media (max-width: 640px) {
        .fc .fc-toolbar { flex-direction: column; align-items: stretch; gap: 0.5rem; }
        .fc .fc-toolbar-title { text-align: center; font-size: 1rem !important; }
        .fc .fc-toolbar-chunk { display: flex; justify-content: center; gap: 0.45rem !important; flex-wrap: wrap; margin: 0.2rem 0 !important; }
        .fc .fc-button-group { gap: 0.35rem !important; }
        .fc .fc-button { margin: 0.15rem !important; padding: 0.45rem 0.6rem !important; }
        .fc .fc-toolbar-chunk:first-child { order: 2; }
        .fc .fc-toolbar-chunk:nth-child(2) { order: 1; width: 100%; }
        .fc .fc-toolbar-chunk:last-child { order: 3; }
        .fc .fc-button { padding: 0.4rem 0.55rem !important; font-size: 0.7rem !important; }
        .fc .fc-daygrid-day-number { font-size: 0.7rem; padding: 0.2rem 0.35rem; }
        .fc .fc-event { font-size: 0.65rem !important; padding: 0.1rem 0.25rem !important; line-height: 1.25 !important; }
        .fc .fc-col-header-cell-cushion { font-size: 0.65rem; padding: 0.45rem 0.15rem; }
        .fc .fc-daygrid-more-link { font-size: 0.65rem; }
        .fc .fc-daygrid-day-frame { min-height: 52px !important; }
        .fc table { table-layout: fixed !important; width: 100% !important; }
        .fc .fc-scroller { overflow-x: hidden !important; }
    }

    .status-badge { display: inline-flex; align-items: center; padding: 0.2rem 0.65rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-confirmed { background: #dbeafe; color: #1e40af; }
    .status-ongoing { background: #d1fae5; color: #065f46; }
    .status-completed { background: #f3f4f6; color: #374151; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .status-overdue { background: #ffedd5; color: #9a3412; }

    .fc .fc-daygrid-more-link { font-weight: 700; color: #059669; font-size: 0.75rem; }
    .fc .fc-daygrid-more-link:hover { color: #047857; text-decoration: underline; }

    .fc .fc-popover { border-radius: 0.75rem; box-shadow: 0 20px 40px -12px rgb(0 0 0 / 0.2); border: 1px solid #e5e7eb; overflow: hidden; }
    .fc .fc-popover-header { background: linear-gradient(to right, #059669, #10b981) !important; color: #fff !important; font-weight: 700; padding: 0.5rem 0.75rem; font-size: 0.8125rem; }
    .fc .fc-popover-body { padding: 0.5rem; max-height: 260px; overflow-y: auto; }
    .fc .fc-popover-body .fc-daygrid-event { margin: 0.25rem 0; border-radius: 0.375rem; padding: 0.125rem 0.5rem; }
</style>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-0 sm:px-1">
    {{-- Page header: icon + title + total bookings --}}
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-center gap-3 sm:gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100 shadow-sm ring-1 ring-emerald-200/80 sm:h-14 sm:w-14">
                <i class="fas fa-calendar-alt text-xl text-emerald-600 sm:text-2xl" aria-hidden="true"></i>
            </div>
            <div class="min-w-0">
                <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">
                    {{ __('vendor.booking_calendar') }}
                </h1>
                <p class="mt-0.5 text-sm text-gray-500">
                    {{ __('vendor.view_all_bookings') }}
                </p>
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-3 self-start sm:self-center">
            <div class="inline-flex items-center gap-3 rounded-xl border border-emerald-200/90 bg-gradient-to-br from-emerald-50 to-white px-4 py-3 shadow-sm ring-1 ring-emerald-100/80">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600 text-white shadow-sm">
                    <i class="fas fa-calendar-check text-sm" aria-hidden="true"></i>
                </span>
                <div class="text-left leading-tight">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800/90">{{ __('vendor.calendar_total_bookings') }}</p>
                    <p class="text-2xl font-bold tabular-nums text-emerald-900">{{ $eventCount }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($eventCount === 0)
        <div class="mb-4 flex items-start gap-3 rounded-xl border border-dashed border-amber-200 bg-amber-50/60 px-4 py-3 text-sm text-amber-900">
            <i class="fas fa-info-circle mt-0.5 text-amber-600"></i>
            <p>{{ __('vendor.no_orders_yet') }}</p>
        </div>
    @endif

    {{-- Calendar --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md ring-1 ring-black/[0.04]">
        <div class="p-2 sm:p-4 md:p-5">
            {{-- Status filter --}}
            <div class="mb-4 flex flex-col gap-3 border-b border-gray-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 text-gray-600">
                        <i class="fas fa-filter text-sm"></i>
                    </span>
                    <span>{{ __('vendor.filter') }}</span>
                </div>
                <div class="flex w-full flex-col gap-2 sm:max-w-xs sm:flex-row sm:items-center">
                    <label for="calendarStatusFilter" class="sr-only">{{ __('vendor.filter') }}</label>
                    <select id="calendarStatusFilter" class="w-full rounded-xl border border-gray-300 bg-white py-2.5 pl-3 pr-10 text-sm font-medium text-gray-900 shadow-sm ring-1 ring-gray-200 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        <option value="">{{ __('vendor.all_statuses') }}</option>
                        <option value="pending">{{ __('vendor.pending') }}</option>
                        <option value="confirmed">{{ __('vendor.confirmed') }}</option>
                        <option value="ongoing">{{ __('vendor.ongoing') }}</option>
                        <option value="completed">{{ __('vendor.completed') }}</option>
                        <option value="cancelled">{{ __('vendor.cancelled') }}</option>
                        <option value="overdue">{{ __('vendor.overdue') }}</option>
                    </select>
                </div>
            </div>
            <div id="calendar" class="min-w-0"></div>
        </div>
    </div>
</div>

{{-- Event modal --}}
<div id="eventModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="eventModalTitle" onclick="if(event.target===this)closeEventModal()">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-[2px] transition-opacity"></div>
    <div class="relative flex min-h-full items-end justify-center sm:items-center sm:p-4">
        <div class="calendar-modal-panel flex max-h-[88vh] w-full max-w-lg flex-col overflow-hidden rounded-t-3xl border border-gray-100 bg-white shadow-2xl sm:max-h-[85vh] sm:rounded-2xl">
            <div class="border-b border-gray-100 bg-gradient-to-r from-emerald-50/90 to-white px-5 pb-4 pt-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 flex-1 items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 shadow-sm">
                            <i class="fas fa-receipt text-emerald-700"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 id="eventModalTitle" class="truncate text-lg font-bold text-gray-900">{{ __('vendor.order_details') }}</h3>
                            <div class="mt-2">
                                <span id="eventModalStatus" class="status-badge status-pending">—</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="closeEventModal()" class="shrink-0 rounded-xl p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700" aria-label="{{ __('vendor.cancel') }}">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-4">
                <dl class="space-y-0 divide-y divide-gray-100">
                    <div class="flex gap-3 py-3 first:pt-0">
                        <dt class="w-24 shrink-0 text-[11px] font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.customer') }}</dt>
                        <dd class="min-w-0 flex-1 text-sm font-medium text-gray-900" id="eventCustomer">—</dd>
                    </div>
                    <div class="flex gap-3 py-3">
                        <dt class="w-24 shrink-0 text-[11px] font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.mobile') }}</dt>
                        <dd class="min-w-0 flex-1 text-sm font-medium text-gray-900" id="eventMobile">—</dd>
                    </div>
                    <div class="flex gap-3 py-3">
                        <dt class="w-24 shrink-0 text-[11px] font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.period') }}</dt>
                        <dd class="min-w-0 flex-1 text-sm font-medium text-gray-900" id="eventPeriod">—</dd>
                    </div>
                    <div class="flex gap-3 py-3">
                        <dt class="w-24 shrink-0 text-[11px] font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.total') }}</dt>
                        <dd class="text-base font-bold text-emerald-700" id="eventTotal">—</dd>
                    </div>
                </dl>

                <div class="mt-5 rounded-xl border border-gray-100 bg-gray-50/80 p-3">
                    <h4 class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-500">
                        <i class="fas fa-box text-gray-400"></i>
                        {{ __('vendor.items') }} (<span id="eventItemsCount">0</span>)
                    </h4>
                    <div id="eventItemsList" class="space-y-2"></div>
                </div>
            </div>

            <div class="border-t border-gray-100 bg-gray-50/90 px-5 py-4">
                <a id="eventViewLink" href="#" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 active:scale-[0.99]">
                    <i class="fas fa-eye"></i>{{ __('vendor.view_full_order') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const calendarEvents = @json($events);
    const orderStatusLabels = @json($orderStatusLabels);
    const orderShowBase = @json($orderShowBase);
    const filterSelect = document.getElementById('calendarStatusFilter');

    const isMobile = window.innerWidth < 640;

    function loadFilteredEvents(fetchInfo, successCallback, failureCallback) {
        try {
            const v = (filterSelect && filterSelect.value) ? filterSelect.value : '';
            const list = !v
                ? calendarEvents.slice()
                : calendarEvents.filter(function(e) {
                    return (e.extendedProps && e.extendedProps.status) === v;
                });
            successCallback(list);
        } catch (err) {
            failureCallback(err);
        }
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: isMobile ? 'listMonth' : 'dayGridMonth',
        headerToolbar: isMobile
            ? { left: 'prev,next', center: 'title', right: 'listMonth,dayGridMonth' }
            : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listMonth' },
        buttonText: {
            today: @json(__('vendor.today')),
            month: @json(__('vendor.month')),
            week: @json(__('vendor.week')),
            list: @json(__('vendor.list_view'))
        },
        views: {
            listMonth: { buttonText: @json(__('vendor.list_view')) }
        },
        height: 'auto',
        contentHeight: isMobile ? 420 : 'auto',
        navLinks: true,
        editable: false,
        dayMaxEvents: isMobile ? 1 : true,
        moreLinkClick: 'popover',
        eventDisplay: 'block',
        fixedWeekCount: false,
        eventTimeFormat: {
            hour: 'numeric',
            minute: '2-digit',
            meridiem: 'short'
        },
        windowResize: function() {
            const mobile = window.innerWidth < 640;
            if (mobile && calendar.view.type !== 'listMonth') {
                calendar.changeView('listMonth');
            }
            calendar.setOption('contentHeight', mobile ? 420 : 'auto');
            calendar.setOption('dayMaxEvents', mobile ? 1 : true);
            calendar.setOption('headerToolbar', mobile
                ? { left: 'prev,next', center: 'title', right: 'listMonth,dayGridMonth' }
                : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listMonth' }
            );
        },
        events: loadFilteredEvents,
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            showEventDetail(info.event);
        }
    });

    calendar.render();

    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            calendar.refetchEvents();
        });
    }

    window.showEventDetail = function(event) {
        const p = event.extendedProps;

        document.getElementById('eventModalTitle').textContent = '#' + p.order_number;

        const statusEl = document.getElementById('eventModalStatus');
        const raw = (p.status || '').toLowerCase();
        statusEl.textContent = orderStatusLabels[raw] || (p.status ? p.status.charAt(0).toUpperCase() + p.status.slice(1) : '—');
        statusEl.className = 'status-badge status-' + raw;

        document.getElementById('eventCustomer').textContent = p.customer_name;
        document.getElementById('eventMobile').textContent = p.customer_mobile || @json(__('vendor.na'));

        document.getElementById('eventPeriod').textContent = p.start_at + '  →  ' + p.end_at;

        document.getElementById('eventTotal').textContent = '₹' + p.grand_total;
        document.getElementById('eventItemsCount').textContent = p.items_count;

        const itemsList = document.getElementById('eventItemsList');
        if (p.items && p.items.length > 0) {
            itemsList.innerHTML = p.items.map(item => `
                <div class="flex flex-col gap-1 rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <span class="text-sm font-semibold text-gray-900">${item.name}</span>
                        <span class="text-xs text-gray-500"> ×${item.qty}</span>
                    </div>
                    <div class="text-xs text-gray-500 sm:text-right">${item.start_at || ''}${item.start_at && item.end_at ? ' → ' : ''}${item.end_at || ''}</div>
                </div>
            `).join('');
        } else {
            itemsList.innerHTML = '<p class="py-4 text-center text-sm text-gray-400">' + @json(__('vendor.no_items')) + '</p>';
        }

        document.getElementById('eventViewLink').href = orderShowBase + event.id;

        document.getElementById('eventModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.closeEventModal = function() {
        document.getElementById('eventModal').classList.add('hidden');
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeEventModal();
    });
});
</script>
@endsection
