@php
    $parseBooking = function (?string $value): array {
        if (! $value) {
            return ['date' => '', 'time' => ''];
        }
        try {
            $dt = \Carbon\Carbon::parse($value);

            return ['date' => $dt->format('Y-m-d'), 'time' => $dt->format('H:i')];
        } catch (\Throwable) {
            return ['date' => '', 'time' => ''];
        }
    };
    $startParts = $parseBooking($booking['start_at'] ?? null);
    $endParts = $parseBooking($booking['end_at'] ?? null);
@endphp
<div x-show="showBookingModal" x-cloak
     class="fixed inset-0 z-[80] flex items-end justify-center sm:items-center sm:p-4"
     role="dialog" aria-modal="true" @keydown.escape.window="closeBookingModal()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[1px]" @click="closeBookingModal()" aria-hidden="true"></div>

    <div class="relative z-10 flex max-h-[92dvh] w-full max-w-2xl flex-col overflow-hidden rounded-t-2xl border border-gray-200 bg-white shadow-2xl sm:rounded-2xl"
         @click.stop>
        <div class="border-b border-gray-100 px-4 py-4 sm:px-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">{{ __('vendor.store_booking_modal_title') }}</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ __('vendor.store_booking_modal_hint') }}</p>
                </div>
                <button type="button" @click="closeBookingModal()" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100" aria-label="{{ __('vendor.cancel') }}">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-4 sm:px-6">
            <p x-show="bookingError" x-text="bookingError" x-cloak class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></p>

            <div x-data="storefrontBookingDates({
                     prefillStartTime: @js($startParts['time']),
                     prefillEndTime: @js($endParts['time']),
                     startDateValue: @js($startParts['date']),
                     endDateValue: @js($endParts['date']),
                 })"
                 x-init="init()"
                 @sync-storefront-booking.window="syncCombined()">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="space-y-2 rounded-xl border border-gray-100 bg-gray-50/80 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide store-accent-text-dark">{{ __('vendor.start_date_time') }}</p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div class="date-input-wrapper">
                                <input type="text" id="store_start_date" x-ref="startDate" readonly
                                       class="store-input cursor-pointer" placeholder="{{ __('vendor.select_date') }}">
                                <span class="date-icon"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <div class="time-input-wrapper">
                                <select id="store_start_time" x-ref="startTimeSelect"
                                        class="booking-time-select store-input cursor-pointer"
                                        data-placeholder="{{ __('vendor.select_time') }}">
                                    <option value="">{{ __('vendor.select_time') }}</option>
                                </select>
                                <span class="time-icon"><i class="fas fa-clock"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-2 rounded-xl border border-gray-100 bg-gray-50/80 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide store-accent-text-dark">{{ __('vendor.end_date_time') }}</p>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <div class="date-input-wrapper">
                                <input type="text" id="store_end_date" x-ref="endDate" readonly
                                       class="store-input cursor-pointer" placeholder="{{ __('vendor.select_date') }}">
                                <span class="date-icon"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <div class="time-input-wrapper">
                                <select id="store_end_time" x-ref="endTimeSelect"
                                        class="booking-time-select store-input cursor-pointer"
                                        data-placeholder="{{ __('vendor.select_time') }}">
                                    <option value="">{{ __('vendor.select_time') }}</option>
                                </select>
                                <span class="time-icon"><i class="fas fa-clock"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-200 bg-gray-50 px-4 py-4 sm:px-6">
            <button type="button" @click="saveBooking()" :disabled="bookingSaving"
                    class="{{ $theme['classes']['btn'] }} store-btn-primary flex h-11 w-full items-center justify-center gap-2 text-sm font-bold disabled:opacity-60">
                <span x-show="!bookingSaving">{{ __('vendor.store_booking_continue') }}</span>
                <span x-show="bookingSaving" x-cloak><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></span>
            </button>
        </div>
    </div>
</div>
