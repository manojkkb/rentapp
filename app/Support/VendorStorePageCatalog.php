<?php

namespace App\Support;

use App\Models\Vendor;
use App\Models\VendorStorePage;
use App\Models\VendorStoreSetting;

final class VendorStorePageCatalog
{
    /**
     * @return array<string, array{
     *     label: string,
     *     icon: string,
     *     placeholder: string,
     *     hint: string,
     *     storefront_route: string,
     *     group: string
     * }>
     */
    public static function definitions(): array
    {
        return [
            VendorStorePage::KEY_ABOUT => [
                'label' => 'vendor.store_about_us',
                'icon' => 'fa-building',
                'placeholder' => 'vendor.store_about_placeholder',
                'hint' => 'vendor.store_about_hint',
                'storefront_route' => 'storefront.about',
                'group' => 'content',
            ],
            VendorStorePage::KEY_CONTACT => [
                'label' => 'vendor.store_contact_us',
                'icon' => 'fa-envelope',
                'placeholder' => 'vendor.store_contact_placeholder',
                'hint' => 'vendor.store_contact_hint',
                'storefront_route' => 'storefront.contact',
                'group' => 'content',
            ],
            VendorStorePage::KEY_FAQ => [
                'label' => 'vendor.store_faq',
                'icon' => 'fa-circle-question',
                'placeholder' => 'vendor.store_faq_placeholder',
                'hint' => 'vendor.store_faq_hint',
                'storefront_route' => 'storefront.faq',
                'group' => 'content',
            ],
            VendorStorePage::KEY_RETURNS => [
                'label' => 'vendor.store_return_policy',
                'icon' => 'fa-rotate-left',
                'placeholder' => 'vendor.store_return_placeholder',
                'hint' => 'vendor.store_return_hint',
                'storefront_route' => 'storefront.returns',
                'group' => 'content',
            ],
            VendorStorePage::KEY_PRIVACY => [
                'label' => 'vendor.store_privacy_policy',
                'icon' => 'fa-shield-alt',
                'placeholder' => 'vendor.store_privacy_placeholder',
                'hint' => 'vendor.store_privacy_hint',
                'storefront_route' => 'storefront.privacy',
                'group' => 'legal',
            ],
            VendorStorePage::KEY_TERMS => [
                'label' => 'vendor.store_terms_conditions',
                'icon' => 'fa-file-contract',
                'placeholder' => 'vendor.store_terms_placeholder',
                'hint' => 'vendor.store_terms_hint',
                'storefront_route' => 'storefront.terms',
                'group' => 'legal',
            ],
        ];
    }

    public static function isValidKey(string $pageKey): bool
    {
        return isset(self::definitions()[$pageKey]);
    }

    public static function label(string $pageKey): string
    {
        $definition = self::definitions()[$pageKey] ?? null;

        return $definition ? __($definition['label']) : $pageKey;
    }

    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     icon: string,
     *     group: string,
     *     has_content: bool,
     *     is_live: bool,
     *     excerpt: string|null,
     *     edit_url: string,
     *     live_url: string|null
     * }>
     */
    public static function listFor(Vendor $vendor, VendorStoreSetting $store): array
    {
        $contents = VendorStorePage::contentsByKey($vendor->id);
        $slug = $vendor->slug;
        $items = [];

        foreach (self::definitions() as $key => $definition) {
            $hasCustomContent = trim((string) ($contents[$key] ?? '')) !== '';
            $isLive = self::isLive($vendor, $store, $key, $hasCustomContent);
            $items[] = [
                'key' => $key,
                'label' => __($definition['label']),
                'icon' => $definition['icon'],
                'group' => $definition['group'],
                'has_content' => $hasCustomContent || self::hasFallbackContent($vendor, $store, $key),
                'is_live' => $isLive,
                'excerpt' => self::excerpt($contents[$key] ?? null),
                'edit_url' => route('vendor.store.pages.edit', $key),
                'live_url' => $isLive ? route($definition['storefront_route'], $slug) : null,
            ];
        }

        return $items;
    }

    /**
     * @return array{
     *     key: string,
     *     label: string,
     *     icon: string,
     *     placeholder: string,
     *     hint: string,
     *     content: string|null,
     *     is_live: bool,
     *     live_url: string|null
     * }
     */
    public static function editFormFor(Vendor $vendor, VendorStoreSetting $store, string $pageKey): array
    {
        $definition = self::definitions()[$pageKey];
        $contents = VendorStorePage::contentsByKey($vendor->id);
        $hasCustomContent = trim((string) ($contents[$pageKey] ?? '')) !== '';
        $isLive = self::isLive($vendor, $store, $pageKey, $hasCustomContent);

        return [
            'key' => $pageKey,
            'label' => __($definition['label']),
            'icon' => $definition['icon'],
            'placeholder' => __($definition['placeholder']),
            'hint' => __($definition['hint']),
            'content' => $contents[$pageKey] ?? null,
            'is_live' => $isLive,
            'live_url' => $isLive ? route($definition['storefront_route'], $vendor->slug) : null,
        ];
    }

    private static function isLive(Vendor $vendor, VendorStoreSetting $store, string $pageKey, bool $hasCustomContent): bool
    {
        return match ($pageKey) {
            VendorStorePage::KEY_ABOUT => StorefrontPages::hasAbout($vendor, $store),
            VendorStorePage::KEY_CONTACT => StorefrontPages::hasContact($vendor, $store),
            VendorStorePage::KEY_FAQ => StorefrontPages::hasFaq($vendor),
            VendorStorePage::KEY_RETURNS => StorefrontPages::hasReturnPolicy($vendor),
            VendorStorePage::KEY_PRIVACY => StorefrontPages::hasPrivacy($vendor),
            VendorStorePage::KEY_TERMS => StorefrontPages::hasTerms($vendor),
            default => $hasCustomContent,
        };
    }

    private static function hasFallbackContent(Vendor $vendor, VendorStoreSetting $store, string $pageKey): bool
    {
        return match ($pageKey) {
            VendorStorePage::KEY_ABOUT => trim((string) $store->description) !== '',
            VendorStorePage::KEY_CONTACT => (bool) ($store->business_phone || $store->business_email || $store->website),
            default => false,
        };
    }

    private static function excerpt(?string $content): ?string
    {
        if ($content === null || trim($content) === '') {
            return null;
        }

        $text = trim(preg_replace('/\s+/', ' ', strip_tags($content)) ?? '');

        if ($text === '') {
            return null;
        }

        return mb_strlen($text) > 120 ? mb_substr($text, 0, 117).'…' : $text;
    }
}
