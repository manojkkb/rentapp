<?php

namespace App\Support;

final class VendorStoreSections
{
    /**
     * @return array<string, array{route: string, icon: string, partial: string}>
     */
    public static function all(): array
    {
        return [
            'general' => ['route' => 'vendor.store.general', 'icon' => 'fa-building', 'partial' => 'general'],
            'address' => ['route' => 'vendor.store.address', 'icon' => 'fa-map-marker-alt', 'partial' => 'address'],
            'pricing' => ['route' => 'vendor.store.pricing', 'icon' => 'fa-tags', 'partial' => 'pricing'],
            'delivery' => ['route' => 'vendor.store.delivery', 'icon' => 'fa-truck', 'partial' => 'delivery'],
            'locations' => ['route' => 'vendor.store.locations', 'icon' => 'fa-location-dot', 'partial' => 'locations'],
            'banner' => ['route' => 'vendor.store.banner', 'icon' => 'fa-image', 'partial' => 'banner'],
            'theme' => ['route' => 'vendor.store.theme', 'icon' => 'fa-palette', 'partial' => 'theme'],
            'seo' => ['route' => 'vendor.store.seo', 'icon' => 'fa-search', 'partial' => 'seo'],
            'pages' => ['route' => 'vendor.store.pages', 'icon' => 'fa-file-lines', 'partial' => 'pages'],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function isValid(string $section): bool
    {
        return isset(self::all()[$section]);
    }

    public static function routeFor(string $section): string
    {
        return self::all()[$section]['route'] ?? 'vendor.store.general';
    }

    public static function label(string $section): string
    {
        return __('vendor.store_tab_'.($section === 'privacy' ? 'legal' : $section));
    }
}
