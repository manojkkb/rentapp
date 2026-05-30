{{-- Mark delivered: item checklist (shared by order show & logistics deliveries) --}}
<div id="markDeliveredModal" class="fixed inset-0 z-[73] hidden" role="dialog" aria-modal="true" aria-labelledby="markDeliveredModalTitle">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeMarkDeliveredModal()"></div>
    <div class="fixed inset-0 flex items-end justify-center p-0 sm:items-center sm:p-4">
        <div class="relative flex max-h-[min(92dvh,640px)] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl ring-1 ring-gray-200 sm:rounded-2xl" onclick="event.stopPropagation()">
            <div class="shrink-0 border-b border-gray-100 bg-gradient-to-r from-teal-50 to-emerald-50 px-4 py-3.5 sm:px-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 id="markDeliveredModalTitle" class="text-base font-bold text-gray-900 sm:text-lg">{{ __('vendor.mark_delivered_modal_title') }}</h3>
                        <p class="mt-1 text-xs leading-snug text-gray-600 sm:text-sm">{{ __('vendor.mark_delivered_modal_hint') }}</p>
                        <p id="markDeliveredOrderRef" class="mt-1 hidden text-xs font-semibold text-teal-900"></p>
                        <p id="markDeliveredAlreadySummary" class="mt-2 hidden rounded-lg border border-teal-200/80 bg-white/80 px-2.5 py-1.5 text-xs font-semibold text-teal-900"></p>
                    </div>
                    <button type="button" onclick="closeMarkDeliveredModal()" class="shrink-0 rounded-lg p-2 text-gray-500 transition hover:bg-white/80 hover:text-gray-800" aria-label="{{ __('vendor.cancel') }}">
                        <i class="fas fa-times text-lg" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div id="markDeliveredItemList" class="min-h-0 flex-1 space-y-2 overflow-y-auto overscroll-y-contain px-4 py-3 sm:px-5 [-webkit-overflow-scrolling:touch]"></div>
            <div class="shrink-0 border-t border-gray-200 bg-white px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-5">
                <button type="button" id="markDeliveredConfirmBtn" onclick="confirmMarkDelivered()"
                        class="flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-3 text-sm font-bold text-white shadow-md transition hover:bg-teal-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="fas fa-check" aria-hidden="true"></i><span id="markDeliveredConfirmLabel">{{ __('vendor.mark_delivered_confirm') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
