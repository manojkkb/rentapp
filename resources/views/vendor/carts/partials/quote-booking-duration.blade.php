@php
    $bookingDurationHuman = null;
    if ($cart->start_time && $cart->end_time) {
        $startTs = $cart->start_time->getTimestamp();
        $endTs = $cart->end_time->getTimestamp();
        if ($endTs > $startTs) {
            $bookingDurationHuman = \Carbon\CarbonInterval::seconds($endTs - $startTs)
                ->cascade()
                ->forHumans(['parts' => 4, 'join' => true]);
        }
    }
@endphp
@if($bookingDurationHuman)
    <div class="quote-panel__row">
        <span class="quote-panel__label">{{ __('vendor.quote_duration') }}</span>
        {{ $bookingDurationHuman }}
    </div>
@endif
