@php
    $booking = $booking ?? ['is_set' => false];
@endphp
@if($booking['is_set'] ?? false)
    <div class="store-booking-strip-active shadow-sm">
        <div class="store-site-container py-3.5 sm:py-4">
            <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
                <div class="flex min-w-0 flex-1 items-center gap-3 sm:gap-4">
                    <span class="store-booking-strip-icon flex h-11 w-11 shrink-0 items-center justify-center rounded-xl sm:h-12 sm:w-12">
                        <i class="fas fa-calendar-check text-base sm:text-lg" aria-hidden="true"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="store-booking-strip-label text-[10px] font-bold uppercase tracking-widest sm:text-xs">{{ __('vendor.store_booking_window') }}</p>
                        <p class="mt-0.5 text-sm font-bold leading-snug sm:text-base">
                            <span class="block sm:inline">{{ $booking['start_label'] }}</span>
                            <span class="store-booking-strip-meta mx-1.5 hidden font-normal sm:inline" aria-hidden="true">→</span>
                            <span class="store-booking-strip-meta block sm:inline">{{ $booking['end_label'] }}</span>
                        </p>
                    </div>
                    @if(! empty($booking['rent_days']))
                        <span class="store-booking-strip-badge hidden shrink-0 rounded-full px-3 py-1 text-xs font-bold sm:inline-flex">
                            {{ trans_choice('vendor.store_rent_days', $booking['rent_days'], ['days' => $booking['rent_days']]) }}
                        </span>
                    @endif
                </div>
                <button type="button" @click="openBookingModal()"
                        class="{{ $theme['classes']['btn'] }} store-booking-strip-edit shrink-0 px-4 py-2 text-xs font-semibold sm:text-sm">
                    <i class="fas fa-pen mr-1.5 text-[10px]" aria-hidden="true"></i>{{ __('vendor.edit') }}
                </button>
            </div>
            @if(! empty($booking['rent_days']))
                <p class="store-booking-strip-meta mt-2 text-xs font-semibold sm:hidden">
                    {{ trans_choice('vendor.store_rent_days', $booking['rent_days'], ['days' => $booking['rent_days']]) }}
                </p>
            @endif
        </div>
    </div>
@else
    <div class="store-booking-strip-prompt">
        <div class="store-site-container py-3.5 sm:py-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="store-booking-strip-prompt-icon flex h-10 w-10 shrink-0 items-center justify-center rounded-full">
                        <i class="fas fa-calendar-plus text-sm" aria-hidden="true"></i>
                    </span>
                    <div class="text-sm">
                        <p class="font-bold">{{ __('vendor.store_booking_required_title') }}</p>
                        <p class="store-booking-strip-prompt-hint mt-0.5">{{ __('vendor.store_booking_required_hint') }}</p>
                    </div>
                </div>
                <button type="button" @click="openBookingModal()"
                        class="{{ $theme['classes']['btn'] }} store-btn-primary shrink-0 px-4 py-2.5 text-xs font-bold shadow-sm sm:text-sm">
                    {{ __('vendor.store_set_booking_dates') }}
                </button>
            </div>
        </div>
    </div>
@endif
