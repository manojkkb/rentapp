<?php

namespace App\Support;

use App\Models\Vendor;
use App\Models\VendorStoreSetting;

final class StorefrontBanner
{
    /**
     * @return array{
     *     enabled: bool,
     *     title: string,
     *     subtitle: ?string,
     *     height: string,
     *     overlay: string,
     *     show_cta: bool,
     *     cta_text: ?string,
     *     cta_url: ?string,
     *     image_url: ?string,
     *     padding_class: string,
     * }
     */
    public static function resolve(VendorStoreSetting $store, Vendor $vendor): array
    {
        $height = in_array($store->banner_height, VendorStoreSetting::BANNER_HEIGHTS, true)
            ? $store->banner_height
            : 'medium';

        $overlay = in_array($store->banner_overlay, VendorStoreSetting::BANNER_OVERLAYS, true)
            ? $store->banner_overlay
            : 'gradient';

        $title = trim((string) ($store->banner_title ?? ''));
        $subtitle = trim((string) ($store->banner_subtitle ?? ''));

        return [
            'enabled' => (bool) ($store->banner_enabled ?? true),
            'title' => $title !== '' ? $title : $vendor->name,
            'subtitle' => $subtitle !== '' ? $subtitle : ($store->tagline ?: null),
            'height' => $height,
            'overlay' => $overlay,
            'show_cta' => (bool) $store->banner_show_cta,
            'cta_text' => $store->banner_cta_text,
            'cta_url' => $store->banner_cta_url,
            'image_url' => $store->hero_image_url,
            'padding_class' => match ($height) {
                'compact' => 'py-8 sm:py-10',
                'tall' => 'py-14 sm:py-20 md:py-28',
                default => 'py-10 sm:py-14 md:py-16 lg:py-20',
            },
        ];
    }

    public static function overlayClass(string $overlay, bool $hasImage): string
    {
        if (! $hasImage) {
            return '';
        }

        return match ($overlay) {
            'dark' => 'bg-black/55',
            'light' => 'bg-white/25',
            default => 'bg-gradient-to-t from-black/70 via-black/30 to-black/10',
        };
    }
}
