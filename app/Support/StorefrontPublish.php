<?php

namespace App\Support;

use App\Models\Vendor;
use App\Models\VendorStoreSetting;

final class StorefrontPublish
{
    /**
     * @return list<string>
     */
    public static function missingRequirements(Vendor $vendor, VendorStoreSetting $store): array
    {
        $missing = [];

        if (! $vendor->name || ! $vendor->business_category_id) {
            $missing[] = __('vendor.store_missing_company');
        }
        if (! $store->business_phone) {
            $missing[] = __('vendor.store_missing_phone');
        }
        if (! $vendor->address_line1 || ! $vendor->city || ! $vendor->state) {
            $missing[] = __('vendor.store_missing_address');
        }

        return $missing;
    }

    public static function canPublish(Vendor $vendor, VendorStoreSetting $store): bool
    {
        return self::missingRequirements($vendor, $store) === [];
    }

    /** Whether customers can open the public storefront URL. */
    public static function isLive(Vendor $vendor, ?VendorStoreSetting $store): bool
    {
        if (! $vendor->is_active || ! $store || ! $store->is_published) {
            return false;
        }

        return self::canPublish($vendor, $store);
    }

    /**
     * @return list<string>
     */
    public static function liveBlockers(Vendor $vendor, ?VendorStoreSetting $store): array
    {
        if (! $vendor->is_active) {
            return [__('vendor.store_vendor_inactive')];
        }

        if (! $store) {
            return [__('vendor.store_settings_missing')];
        }

        if (! $store->is_published) {
            return [__('vendor.store_not_published_yet')];
        }

        return self::missingRequirements($vendor, $store);
    }
}
