<?php

namespace App\Support;

use App\Models\Items;
use App\Models\Vendor;
use App\Models\VendorStoreSetting;

final class StorefrontWhatsApp
{
    public static function isEnabled(VendorStoreSetting $store): bool
    {
        if (! $store->whatsapp_enabled) {
            return false;
        }

        return self::waMeDigits($store) !== null;
    }

    public static function displayNumber(VendorStoreSetting $store): ?string
    {
        $number = trim((string) ($store->whatsapp_number ?: $store->business_phone));

        return $number !== '' ? $number : null;
    }

    public static function waMeDigits(VendorStoreSetting $store): ?string
    {
        $raw = trim((string) ($store->whatsapp_number ?: $store->business_phone));
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 10) {
            return '91'.$digits;
        }

        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            return $digits;
        }

        return $digits;
    }

    public static function url(VendorStoreSetting $store, string $message = ''): ?string
    {
        if (! self::isEnabled($store)) {
            return null;
        }

        $digits = self::waMeDigits($store);
        if (! $digits) {
            return null;
        }

        $base = 'https://wa.me/'.$digits;

        return $message !== ''
            ? $base.'?text='.rawurlencode($message)
            : $base;
    }

    public static function contactMessage(Vendor $vendor): string
    {
        return __('vendor.store_whatsapp_contact_msg', ['name' => $vendor->name]);
    }

    public static function itemMessage(Vendor $vendor, Items $item, string $itemUrl, ?string $rentalHint = null): string
    {
        $params = [
            'store' => $vendor->name,
            'item' => $item->name,
            'url' => $itemUrl,
        ];

        if ($rentalHint) {
            return __('vendor.store_whatsapp_item_msg_with_dates', $params + ['dates' => $rentalHint]);
        }

        return __('vendor.store_whatsapp_item_msg', $params);
    }
}
