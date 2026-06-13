<?php

namespace App\Support;

use App\Models\Vendor;
use App\Models\VendorLocation;
use App\Models\VendorStorePage;
use App\Models\VendorStoreSetting;
use Illuminate\Support\Collection;

final class StorefrontPages
{
    public static function aboutContent(Vendor $vendor, VendorStoreSetting $store): ?string
    {
        $custom = VendorStorePage::contentFor($vendor->id, VendorStorePage::KEY_ABOUT);
        if ($custom !== null) {
            return $custom;
        }

        $description = trim((string) $store->description);

        return $description !== '' ? $description : null;
    }

    public static function hasAbout(Vendor $vendor, VendorStoreSetting $store): bool
    {
        return self::aboutContent($vendor, $store) !== null;
    }

    public static function hasContact(Vendor $vendor, VendorStoreSetting $store): bool
    {
        if (VendorStorePage::contentFor($vendor->id, VendorStorePage::KEY_CONTACT) !== null) {
            return true;
        }

        if ($store->business_phone || $store->business_email || $store->website) {
            return true;
        }

        if (StorefrontWhatsApp::isEnabled($store)) {
            return true;
        }

        if (trim((string) $vendor->full_address) !== '') {
            return true;
        }

        return VendorLocation::query()
            ->where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->exists();
    }

    public static function hasFaq(Vendor $vendor): bool
    {
        return VendorStorePage::contentFor($vendor->id, VendorStorePage::KEY_FAQ) !== null;
    }

    public static function hasReturnPolicy(Vendor $vendor): bool
    {
        return VendorStorePage::contentFor($vendor->id, VendorStorePage::KEY_RETURNS) !== null;
    }

    public static function hasPrivacy(Vendor $vendor): bool
    {
        return VendorStorePage::contentFor($vendor->id, VendorStorePage::KEY_PRIVACY) !== null;
    }

    public static function hasTerms(Vendor $vendor): bool
    {
        return VendorStorePage::contentFor($vendor->id, VendorStorePage::KEY_TERMS) !== null;
    }

    public static function content(Vendor $vendor, string $pageKey): ?string
    {
        return VendorStorePage::contentFor($vendor->id, $pageKey);
    }

    /**
     * @return Collection<int, array{label: string, url: string, active: bool}>
     */
    public static function footerLinks(Vendor $vendor, VendorStoreSetting $store): Collection
    {
        $slug = $vendor->slug;
        $links = collect([
            [
                'label' => __('vendor.store_nav_shop'),
                'url' => route('storefront.show', $slug),
                'active' => request()->routeIs('storefront.show'),
                'sort' => 10,
            ],
            [
                'label' => __('vendor.store_nav_cart'),
                'url' => route('storefront.cart', $slug),
                'active' => request()->routeIs('storefront.cart'),
                'sort' => 20,
            ],
            [
                'label' => __('vendor.store_nav_orders'),
                'url' => route('storefront.orders', $slug),
                'active' => request()->routeIs('storefront.orders*'),
                'sort' => 30,
            ],
        ]);

        if (self::hasAbout($vendor, $store)) {
            $links->push([
                'label' => __('vendor.store_about_us'),
                'url' => route('storefront.about', $slug),
                'active' => request()->routeIs('storefront.about'),
                'sort' => 40,
            ]);
        }

        if (self::hasContact($vendor, $store)) {
            $links->push([
                'label' => __('vendor.store_contact_us'),
                'url' => route('storefront.contact', $slug),
                'active' => request()->routeIs('storefront.contact'),
                'sort' => 50,
            ]);
        }

        if (self::hasFaq($vendor)) {
            $links->push([
                'label' => __('vendor.store_faq'),
                'url' => route('storefront.faq', $slug),
                'active' => request()->routeIs('storefront.faq'),
                'sort' => 60,
            ]);
        }

        if (self::hasReturnPolicy($vendor)) {
            $links->push([
                'label' => __('vendor.store_return_policy'),
                'url' => route('storefront.returns', $slug),
                'active' => request()->routeIs('storefront.returns'),
                'sort' => 70,
            ]);
        }

        if (self::hasPrivacy($vendor)) {
            $links->push([
                'label' => __('vendor.store_privacy_policy'),
                'url' => route('storefront.privacy', $slug),
                'active' => request()->routeIs('storefront.privacy'),
                'sort' => 80,
            ]);
        }

        if (self::hasTerms($vendor)) {
            $links->push([
                'label' => __('vendor.store_terms_conditions'),
                'url' => route('storefront.terms', $slug),
                'active' => request()->routeIs('storefront.terms'),
                'sort' => 90,
            ]);
        }

        return $links->sortBy('sort')->values();
    }
}
