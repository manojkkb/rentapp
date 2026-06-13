<?php

namespace App\Support;

use App\Models\Items;
use Carbon\Carbon;

final class StorefrontRentalPricing
{
    /**
     * @return array<string, float>
     */
    public static function defaultBillingUnitsByPriceType(Carbon $start, Carbon $end): array
    {
        if ($end->lte($start)) {
            return [];
        }

        $out = [];
        foreach (Items::rentalPeriodKeys() as $key) {
            if ($key === 'fixed' || ! Items::rentalPeriodUsesBillingUnits($key)) {
                continue;
            }
            $out[$key] = self::billingUnitsBetween($start, $end, $key);
        }

        return $out;
    }

    public static function billingUnitsBetween(Carbon $start, Carbon $end, string $rentalPeriod): float
    {
        if ($rentalPeriod === 'per_day') {
            return round((float) max(1, (int) ceil($start->diffInDays($end))), 2);
        }

        $seconds = abs($start->diffInSeconds($end));
        $raw = match ($rentalPeriod) {
            'per_minute' => $seconds / 60,
            'per_hour' => $seconds / 3600,
            'per_week' => $seconds / (86400 * 7),
            'per_month' => $seconds / (86400 * 30),
            'per_year' => $seconds / (86400 * 365.25),
            default => $seconds / 86400,
        };

        return round(max(0.01, $raw), 2);
    }

    public static function lineSubtotal(
        float $unitPrice,
        int $quantity,
        string $rentalPeriod,
        ?float $billingUnits = null,
    ): float {
        if ($rentalPeriod === 'fixed') {
            return round($unitPrice * $quantity, 2);
        }

        $units = (float) ($billingUnits ?? 1);
        if ($units < 0.0001) {
            $units = 1.0;
        }

        return round($unitPrice * $quantity * $units, 2);
    }

    public static function billingUnitsForItem(Carbon $start, Carbon $end, Items $item, ?array $defaults = null): ?float
    {
        $rentalPeriod = in_array($item->rental_period ?? '', Items::rentalPeriodKeys(), true)
            ? $item->rental_period
            : 'per_day';

        if (! Items::rentalPeriodUsesBillingUnits($rentalPeriod)) {
            return null;
        }

        if ($defaults !== null && isset($defaults[$rentalPeriod])) {
            return (float) $defaults[$rentalPeriod];
        }

        return self::billingUnitsBetween($start, $end, $rentalPeriod);
    }
}
