<?php

namespace App\Support;

final class OrderCreateWizardSession
{
    public const KEY = 'vendor_order_create_wizard';

    /** @return array<string, mixed> */
    public static function get(): array
    {
        $data = session(self::KEY, []);

        return is_array($data) ? $data : [];
    }

    /** @param array<string, mixed> $data */
    public static function put(array $data): void
    {
        session([self::KEY => array_merge(self::get(), $data)]);
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }

    /** @param array<string, mixed> $wizard */
    public static function hasStep1(array $wizard): bool
    {
        return isset($wizard['customer_id'], $wizard['event_name'], $wizard['start_time'], $wizard['end_time'])
            && $wizard['event_name'] !== ''
            && $wizard['start_time']
            && $wizard['end_time'];
    }

    /** @param array<string, mixed> $wizard */
    public static function hasFulfillment(array $wizard): bool
    {
        $type = $wizard['fulfillment_type'] ?? null;
        if ($type !== 'pickup' && $type !== 'delivery') {
            return false;
        }
        if ($type === 'delivery') {
            return trim((string) ($wizard['delivery_address'] ?? '')) !== '';
        }

        return ! empty($wizard['pickup_at']);
    }
}
