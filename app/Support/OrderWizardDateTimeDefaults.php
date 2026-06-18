<?php

namespace App\Support;

use Carbon\Carbon;

final class OrderWizardDateTimeDefaults
{
    public static function nextAvailableSlot(?Carbon $from = null): Carbon
    {
        $from = ($from ?? now())->copy()->timezone(config('app.timezone'));
        $slot = $from->copy()->second(0)->microsecond(0);
        $minute = (int) $slot->format('i');
        $remainder = $minute % 30;

        if ($remainder === 0) {
            if ($slot->lte($from)) {
                $slot->addMinutes(30);
            }
        } else {
            $slot->addMinutes(30 - $remainder);
        }

        return $slot;
    }

    public static function defaultStartAt(): Carbon
    {
        return self::nextAvailableSlot();
    }

    public static function defaultEndAt(?Carbon $startAt = null): Carbon
    {
        $start = $startAt ?? self::defaultStartAt();

        return $start->copy()->addDay();
    }

    public static function defaultFulfillmentAt(): Carbon
    {
        return self::nextAvailableSlot();
    }

    public static function format(Carbon $dt): string
    {
        return $dt->format('Y-m-d H:i');
    }
}
