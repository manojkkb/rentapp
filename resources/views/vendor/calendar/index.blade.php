@extends('vendor.layouts.app')

@section('title', __('vendor.calendar'))
@section('page-title', __('vendor.calendar'))

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<style>
    #calendar {
        --fc-border-color: #e5e7eb;
        --fc-today-bg-color: #ecfdf5;
        --fc-page-bg-color: #fff;
    }
    .fc { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .fc .fc-toolbar-title { font-size: 1.1rem !important; font-weight: 700 !important; color: #111827; }
    .fc .fc-button { font-size: 0.8rem !important; padding: 6px 12px !important; border-radius: 8px !important; font-weight: 600 !important; text-transform: capitalize !important; }
    .fc .fc-button-primary { background-color: #059669 !important; border-color: #059669 !important; }
    .fc .fc-button-primary:hover { background-color: #047857 !important; border-color: #047857 !important; }
    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active { background-color: #047857 !important; border-color: #047857 !important; }
    .fc .fc-daygrid-day-number { font-size: 0.85rem; font-weight: 600; color: #374151; padding: 6px 8px; }
    .fc .fc-event { border-radius: 6px !important; padding: 2px 6px !important; font-size: 0.75rem !important; font-weight: 500 !important; cursor: pointer !important; }
    .fc .fc-daygrid-event { margin: 1px 3px !important; }
    .fc .fc-col-header-cell { background: #f9fafb; }
    .fc .fc-col-header-cell-cushion { font-size: 0.8rem; font-weight: 700; color: #6b7280; text-transform: uppercase; padding: 10px 4px; }
    .fc .fc-daygrid-day.fc-day-today { background-color: #ecfdf5 !important; }
    .fc .fc-toolbar { flex-wrap: wrap; gap: 8px; }
    .fc .fc-toolbar.fc-header-toolbar { margin-bottom: 1rem !important; }
    .fc .fc-scrollgrid { overflow: hidden; }
    .fc .fc-view-harness { overflow: visible !important; }

    /* Mobile */
    @media (max-width: 640px) {
        .fc .fc-toolbar { flex-direction: column; align-items: center; gap: 6px; }
        .fc .fc-toolbar-title { text-align: center; font-size: 0.95rem !important; }
        .fc .fc-toolbar-chunk { display: flex; justify-content: center; gap: 2px; }
        .fc .fc-toolbar-chunk:first-child { order: 2; }
        .fc .fc-toolbar-chunk:nth-child(2) { order: 1; width: 100%; }
        .fc .fc-toolbar-chunk:last-child { order: 3; }
        .fc .fc-button { padding: 6px 8px !important; font-size: 0.7rem !important; border-radius: 6px !important; }
        .fc .fc-button-group { gap: 0; }
        .fc .fc-daygrid-day-number { font-size: 0.7rem; padding: 3px 4px; }
        .fc .fc-event { font-size: 0.6rem !important; padding: 1px 2px !important; line-height: 1.3 !important; }
        .fc .fc-col-header-cell-cushion { font-size: 0.65rem; padding: 6px 1px; letter-spacing: 0; }
        .fc .fc-daygrid-more-link { font-size: 0.6rem; }
        .fc .fc-daygrid-day-frame { min-height: 50px !important; }
        .fc .fc-scrollgrid-sync-table { min-width: 0 !important; }
        .fc table { table-layout: fixed !important; width: 100% !important; }
        .fc .fc-scroller { overflow-x: hidden !important; }
        .fc .fc-list-event-title a { font-size: 0.8rem; }
        .fc .fc-list-day-cushion { font-size: 0.8rem; padding: 6px 10px; }
    }

    @media (max-width: 380px) {
        .fc .fc-button { padding: 5px 6px !important; font-size: 0.65rem !important; }
        .fc .fc-daygrid-day-number { font-size: 0.65rem; padding: 2px 3px; }
        .fc .fc-col-header-cell-cushion { font-size: 0.6rem; }
        .fc .fc-daygrid-day-frame { min-height: 42px !important; }
    }

    /* Event detail modal */
    .event-detail-row { display: flex; align-items: start; padding: 8px 0; border-bottom: 1px solid #f3f4f6; }
    .event-detail-row:last-child { border-bottom: none; }
    .event-detail-label { width: 100px; flex-shrink: 0; font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
    .event-detail-value { flex: 1; font-size: 0.875rem; color: #111827; font-weight: 500; word-break: break-word; }

    /* Status badge */
    .status-badge { display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-confirmed { background: #dbeafe; color: #1e40af; }
    .status-ongoing { background: #d1fae5; color: #065f46; }
    .status-completed { background: #f3f4f6; color: #374151; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .status-overdue { background: #ffedd5; color: #9a3412; }

    /* More link styling */
    .fc .fc-daygrid-more-link { font-weight: 700; color: #059669; font-size: 0.8rem; }
    .fc .fc-daygrid-more-link:hover { color: #047857; text-decoration: underline; }

    /* Popover styling */
    .fc .fc-popover { border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; overflow: hidden; }
    .fc .fc-popover-header { background: #059669 !important; color: #fff !important; font-weight: 700; padding: 8px 12px; font-size: 0.85rem; }
    .fc .fc-popover-body { padding: 6px; max-height: 250px; overflow-y: auto; }
    .fc .fc-popover-body .fc-daygrid-event { margin: 3px 0; border-radius: 6px; padding: 2px 6px; }
</style>
@endsection

@section('content')
<!-- Header -->
<div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center space-x-3">
        <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center bg-emerald-100 rounded-xl flex-shrink-0">
            <i class="fas fa-calendar-alt text-emerald-600 text-lg sm:text-xl"></i>
        </div>
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ __('vendor.booking_calendar') }}</h2>
            <p class="text-xs sm:text-sm text-gray-600">{{ __('vendor.view_all_bookings') }}</p>
        </div>
    </div>
    <!-- Legend Desktop -->
    <div class="hidden md:flex items-center gap-3 flex-wrap">
        <span class="flex items-center gap-1.5 text-xs font-medium text-gray-700">
            <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span> {{ __('vendor.pending') }}
        </span>
        <span class="flex items-center gap-1.5 text-xs font-medium text-gray-700">
            <span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> {{ __('vendor.confirmed') }}
        </span>
        <span class="flex items-center gap-1.5 text-xs font-medium text-gray-700">
            <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> {{ __('vendor.ongoing') }}
        </span>
        <span class="flex items-center gap-1.5 text-xs font-medium text-gray-700">
            <span class="w-3 h-3 rounded-full bg-gray-500 inline-block"></span> {{ __('vendor.completed') }}
        </span>
        <span class="flex items-center gap-1.5 text-xs font-medium text-gray-700">
            <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> {{ __('vendor.cancelled') }}
        </span>
    </div>
</div>

<!-- Mobile Legend -->
<div class="md:hidden mb-4 flex flex-wrap gap-2">
    <span class="flex items-center gap-1 text-[10px] font-semibold px-2 py-1 bg-amber-50 rounded-full text-amber-700">
        <span class="w-2 h-2 rounded-full bg-amber-500"></span> {{ __('vendor.pending') }}
    </span>
    <span class="flex items-center gap-1 text-[10px] font-semibold px-2 py-1 bg-blue-50 rounded-full text-blue-700">
        <span class="w-2 h-2 rounded-full bg-blue-500"></span> {{ __('vendor.confirmed') }}
    </span>
    <span class="flex items-center gap-1 text-[10px] font-semibold px-2 py-1 bg-emerald-50 rounded-full text-emerald-700">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ __('vendor.ongoing') }}
    </span>
    <span class="flex items-center gap-1 text-[10px] font-semibold px-2 py-1 bg-gray-100 rounded-full text-gray-700">
        <span class="w-2 h-2 rounded-full bg-gray-500"></span> {{ __('vendor.completed') }}
    </span>
    <span class="flex items-center gap-1 text-[10px] font-semibold px-2 py-1 bg-red-50 rounded-full text-red-700">
        <span class="w-2 h-2 rounded-full bg-red-500"></span> {{ __('vendor.cancelled') }}
    </span>
</div>

<!-- Calendar Container -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-2 sm:p-5 overflow-hidden">
    <div id="calendar" class="min-w-0"></div>
</div>

<!-- Event Detail Modal -->
<div id="eventModal" class="fixed inset-0 z-50 hidden" onclick="if(event.target===this)closeEventModal()">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative flex items-end sm:items-center justify-center min-h-full p-0 sm:p-4">
        <div class="bg-white w-full sm:w-[420px] sm:max-w-lg rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden max-h-[85vh] flex flex-col animate-slide-up">
            <!-- Modal Header -->
            <div class="px-5 pt-5 pb-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center bg-emerald-100 rounded-lg">
                            <i class="fas fa-receipt text-emerald-600"></i>
                        </div>
                        <div>
                            <h3 id="eventModalTitle" class="text-lg font-bold text-gray-900">{{ __('vendor.order_details') }}</h3>
                            <span id="eventModalStatus" class="status-badge status-pending">Pending</span>
                        </div>
                    </div>
                    <button onclick="closeEventModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-1">
                <div class="event-detail-row">
                    <span class="event-detail-label">{{ __('vendor.customer') }}</span>
                    <span class="event-detail-value" id="eventCustomer">-</span>
                </div>
                <div class="event-detail-row">
                    <span class="event-detail-label">{{ __('vendor.mobile') }}</span>
                    <span class="event-detail-value" id="eventMobile">-</span>
                </div>
                <div class="event-detail-row">
                    <span class="event-detail-label">{{ __('vendor.period') }}</span>
                    <span class="event-detail-value" id="eventPeriod">-</span>
                </div>
                <div class="event-detail-row">
                    <span class="event-detail-label">{{ __('vendor.total') }}</span>
                    <span class="event-detail-value font-bold text-emerald-700" id="eventTotal">-</span>
                </div>

                <!-- Items -->
                <div class="pt-3">
                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                        <i class="fas fa-box mr-1"></i>{{ __('vendor.items') }} (<span id="eventItemsCount">0</span>)
                    </h4>
                    <div id="eventItemsList" class="space-y-2"></div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
                <a id="eventViewLink" href="#" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-all active:scale-95">
                    <i class="fas fa-eye mr-2"></i>{{ __('vendor.view_full_order') }}
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

    const isMobile = window.innerWidth < 640;

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
        contentHeight: isMobile ? 400 : 'auto',
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
            calendar.setOption('contentHeight', mobile ? 400 : 'auto');
            calendar.setOption('dayMaxEvents', mobile ? 1 : true);
            calendar.setOption('headerToolbar', mobile
                ? { left: 'prev,next', center: 'title', right: 'listMonth,dayGridMonth' }
                : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listMonth' }
            );
        },
        events: calendarEvents,
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            showEventDetail(info.event);
        },
        loading: function(isLoading) {
            // Could add a loading spinner here
        }
    });

    calendar.render();

    window.showEventDetail = function(event) {
        const p = event.extendedProps;

        document.getElementById('eventModalTitle').textContent = '#' + p.order_number;

        // Status badge
        const statusEl = document.getElementById('eventModalStatus');
        statusEl.textContent = p.status.charAt(0).toUpperCase() + p.status.slice(1);
        statusEl.className = 'status-badge status-' + p.status;

        document.getElementById('eventCustomer').textContent = p.customer_name;
        document.getElementById('eventMobile').textContent = p.customer_mobile || @json(__('vendor.na'));

        // Format period
        document.getElementById('eventPeriod').textContent = p.start_at + '  →  ' + p.end_at;

        document.getElementById('eventTotal').textContent = '₹' + p.grand_total;
        document.getElementById('eventItemsCount').textContent = p.items_count;

        // Build items list
        const itemsList = document.getElementById('eventItemsList');
        if (p.items && p.items.length > 0) {
            itemsList.innerHTML = p.items.map(item => `
                <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                    <div>
                        <span class="text-sm font-semibold text-gray-800">${item.name}</span>
                        <span class="text-xs text-gray-500 ml-1">×${item.qty}</span>
                    </div>
                    <div class="text-xs text-gray-500">
                        ${item.start_at || ''} → ${item.end_at || ''}
                    </div>
                </div>
            `).join('');
        } else {
            itemsList.innerHTML = '<p class="text-sm text-gray-400 text-center py-3">' + @json(__('vendor.no_items')) + '</p>';
        }

        // View link
        document.getElementById('eventViewLink').href =
            '{{ route("vendor.orders.index") }}/' + event.id;

        document.getElementById('eventModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.closeEventModal = function() {
        document.getElementById('eventModal').classList.add('hidden');
        document.body.style.overflow = '';
    };

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeEventModal();
    });
});
</script>
@endsection
