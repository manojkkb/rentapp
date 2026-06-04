<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Vendor;
use Carbon\CarbonInterface;
use Carbon\CarbonInterval;

class InvoiceDocument
{
    /**
     * @return array<string, string>
     */
    public static function paymentMethodLabels(): array
    {
        return [
            'card' => __('vendor.payment_method_card'),
            'cash' => __('vendor.payment_method_cash'),
            'upi' => __('vendor.payment_method_upi'),
            'bank_transfer' => __('vendor.payment_method_bank_transfer'),
            'wallet' => __('vendor.payment_method_wallet'),
            'other' => __('vendor.payment_method_other'),
        ];
    }

    public static function paymentMethodLabel(?string $method): string
    {
        $key = strtolower((string) $method);

        return self::paymentMethodLabels()[$key]
            ?? ($method !== '' && $method !== null ? ucfirst(str_replace('_', ' ', $key)) : '—');
    }

    public static function formatDate(?CarbonInterface $date, bool $withTime = true): string
    {
        if ($date === null) {
            return '—';
        }

        $localized = $date->copy()->timezone(config('app.timezone'))->locale(app()->getLocale());

        return $withTime
            ? $localized->translatedFormat('d M Y, h:i A')
            : $localized->translatedFormat('d M Y');
    }

    public static function rentalDurationHuman(Order $order): ?string
    {
        if (! $order->start_at || ! $order->end_at) {
            return null;
        }

        $startTs = $order->start_at->getTimestamp();
        $endTs = $order->end_at->getTimestamp();

        if ($endTs <= $startTs) {
            return null;
        }

        return CarbonInterval::seconds($endTs - $startTs)
            ->locale(app()->getLocale())
            ->cascade()
            ->forHumans(['parts' => 4, 'join' => true]);
    }

    public static function orderStatusLabel(?string $status): string
    {
        $key = strtolower((string) $status);

        return match ($key) {
            'pending' => __('vendor.pending'),
            'confirmed' => __('vendor.confirmed'),
            'completed' => __('vendor.completed'),
            'cancelled' => __('vendor.cancelled'),
            default => $status !== '' && $status !== null ? ucfirst(str_replace('_', ' ', $key)) : '—',
        };
    }

    public static function vendorAddress(?Vendor $vendor): ?string
    {
        if ($vendor === null) {
            return null;
        }

        $parts = array_filter([
            $vendor->address_line1,
            $vendor->address_line2,
            collect([$vendor->city, $vendor->state, $vendor->postal_code])->filter()->implode(', '),
            $vendor->country,
        ], static fn ($part) => filled($part));

        return $parts === [] ? null : implode(', ', $parts);
    }

    public static function money(float|string|null $amount): string
    {
        return '₹'.number_format((float) ($amount ?? 0), 2);
    }
}
