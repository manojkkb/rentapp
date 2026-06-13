<?php

namespace App\Support;

use App\Models\VendorStoreSetting;

final class StorefrontThemePalette
{
    public const MODES = ['light', 'dark', 'gradient', 'custom'];

    /** @var list<string> */
    public const KEYS = [
        'primary', 'secondary', 'accent', 'text', 'heading', 'link', 'link_hover',
        'body_bg', 'section_bg', 'card_bg', 'footer_bg', 'header_bg',
        'btn_primary_bg', 'btn_primary_text', 'btn_secondary_bg', 'btn_secondary_text', 'btn_hover_bg',
        'nav_text', 'nav_hover', 'nav_active', 'mobile_menu_bg',
        'input_bg', 'input_border', 'input_focus_border', 'placeholder',
        'success', 'warning', 'error', 'info',
    ];

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'primary' => __('vendor.store_palette_primary'),
            'secondary' => __('vendor.store_palette_secondary'),
            'accent' => __('vendor.store_palette_accent'),
            'text' => __('vendor.store_palette_text'),
            'heading' => __('vendor.store_palette_heading'),
            'link' => __('vendor.store_palette_link'),
            'link_hover' => __('vendor.store_palette_link_hover'),
            'body_bg' => __('vendor.store_palette_body_bg'),
            'section_bg' => __('vendor.store_palette_section_bg'),
            'card_bg' => __('vendor.store_palette_card_bg'),
            'footer_bg' => __('vendor.store_palette_footer_bg'),
            'header_bg' => __('vendor.store_palette_header_bg'),
            'btn_primary_bg' => __('vendor.store_palette_btn_primary_bg'),
            'btn_primary_text' => __('vendor.store_palette_btn_primary_text'),
            'btn_secondary_bg' => __('vendor.store_palette_btn_secondary_bg'),
            'btn_secondary_text' => __('vendor.store_palette_btn_secondary_text'),
            'btn_hover_bg' => __('vendor.store_palette_btn_hover_bg'),
            'nav_text' => __('vendor.store_palette_nav_text'),
            'nav_hover' => __('vendor.store_palette_nav_hover'),
            'nav_active' => __('vendor.store_palette_nav_active'),
            'mobile_menu_bg' => __('vendor.store_palette_mobile_menu_bg'),
            'input_bg' => __('vendor.store_palette_input_bg'),
            'input_border' => __('vendor.store_palette_input_border'),
            'input_focus_border' => __('vendor.store_palette_input_focus_border'),
            'placeholder' => __('vendor.store_palette_placeholder'),
            'success' => __('vendor.store_palette_success'),
            'warning' => __('vendor.store_palette_warning'),
            'error' => __('vendor.store_palette_error'),
            'info' => __('vendor.store_palette_info'),
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function groups(): array
    {
        return [
            'basic' => ['primary', 'secondary', 'accent', 'text', 'heading', 'link', 'link_hover'],
            'backgrounds' => ['body_bg', 'section_bg', 'card_bg', 'footer_bg', 'header_bg'],
            'buttons' => ['btn_primary_bg', 'btn_primary_text', 'btn_secondary_bg', 'btn_secondary_text', 'btn_hover_bg'],
            'navigation' => ['nav_text', 'nav_hover', 'nav_active', 'mobile_menu_bg'],
            'forms' => ['input_bg', 'input_border', 'input_focus_border', 'placeholder'],
            'status' => ['success', 'warning', 'error', 'info'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function resolve(VendorStoreSetting $store): array
    {
        $template = in_array($store->theme_template, VendorStoreSetting::THEME_TEMPLATES, true)
            ? $store->theme_template
            : 'classic';

        $mode = in_array($store->theme_mode, self::MODES, true) ? $store->theme_mode : 'light';
        $accentSeed = StorefrontTheme::normalizeHex($store->theme_accent_color ?? '#059669');

        $palette = self::modePreset($mode, $template, $accentSeed);
        $palette = self::applyLegacyColumns($palette, $store);

        if ($mode !== 'custom') {
            $palette['accent'] = $accentSeed;
            $palette['primary'] = $accentSeed;
        } elseif ($store->theme_colors) {
            $palette = self::applyCustomColors($palette, $store->theme_colors);
        }

        return self::deriveComputed($palette);
    }

    /**
     * @return array<string, string>
     */
    public static function modePreset(string $mode, string $template, string $accent): array
    {
        $light = self::lightPreset($template, $accent);

        return match ($mode) {
            'dark' => self::darkPreset($light, $accent),
            'gradient' => self::gradientPreset($light, $accent),
            default => $light,
        };
    }

    /**
     * @return array<string, string>
     */
    private static function lightPreset(string $template, string $accent): array
    {
        $accent = StorefrontTheme::normalizeHex($accent);
        $scheme = StorefrontThemeTemplate::lightScheme($template);

        $headerBg = $scheme['header_bg'] ?? $accent;
        $secondary = $scheme['secondary'] ?? '#047857';

        $palette = array_merge([
            'primary' => $accent,
            'secondary' => $secondary,
            'accent' => $accent,
            'text' => '#374151',
            'heading' => '#111827',
            'link' => $secondary,
            'link_hover' => StorefrontTheme::mix($secondary, '#000000', 0.15),
            'body_bg' => '#f9fafb',
            'section_bg' => '#ffffff',
            'card_bg' => '#ffffff',
            'footer_bg' => '#ffffff',
            'header_bg' => $headerBg,
            'btn_primary_bg' => $accent,
            'btn_primary_text' => '#ffffff',
            'btn_secondary_bg' => '#f3f4f6',
            'btn_secondary_text' => '#374151',
            'btn_hover_bg' => StorefrontTheme::mix($accent, '#000000', 0.12),
            'nav_text' => StorefrontTheme::isLight($headerBg) ? '#374151' : '#ffffff',
            'nav_hover' => StorefrontTheme::isLight($headerBg) ? '#111827' : '#ffffff',
            'nav_active' => StorefrontTheme::isLight($headerBg) ? $accent : '#ffffff',
            'mobile_menu_bg' => '#ffffff',
            'input_bg' => '#ffffff',
            'input_border' => '#e5e7eb',
            'input_focus_border' => $accent,
            'placeholder' => '#9ca3af',
            'success' => '#059669',
            'warning' => '#d97706',
            'error' => '#dc2626',
            'info' => '#2563eb',
        ], $scheme);

        $palette['primary'] = $accent;
        $palette['accent'] = $accent;
        $palette['btn_primary_bg'] = $accent;
        $palette['input_focus_border'] = $accent;
        $palette['link_hover'] = StorefrontTheme::mix($palette['link'], '#000000', 0.15);
        $palette['btn_hover_bg'] = StorefrontTheme::mix($accent, '#000000', 0.12);

        if (! isset($scheme['nav_text'])) {
            $palette['nav_text'] = StorefrontTheme::isLight($palette['header_bg']) ? '#374151' : '#ffffff';
            $palette['nav_hover'] = StorefrontTheme::isLight($palette['header_bg']) ? '#111827' : '#ffffff';
            $palette['nav_active'] = StorefrontTheme::isLight($palette['header_bg']) ? $accent : '#ffffff';
        }

        return self::basePalette($palette);
    }

    /**
     * @param  array<string, string>  $light
     * @return array<string, string>
     */
    private static function darkPreset(array $light, string $accent): array
    {
        return self::basePalette([
            'primary' => $accent,
            'secondary' => StorefrontTheme::mix($accent, '#ffffff', 0.35),
            'accent' => $accent,
            'text' => '#e5e7eb',
            'heading' => '#f9fafb',
            'link' => StorefrontTheme::mix($accent, '#ffffff', 0.25),
            'link_hover' => '#ffffff',
            'body_bg' => '#0f172a',
            'section_bg' => '#1e293b',
            'card_bg' => '#1e293b',
            'footer_bg' => '#0f172a',
            'header_bg' => '#111827',
            'btn_primary_bg' => $accent,
            'btn_primary_text' => '#ffffff',
            'btn_secondary_bg' => '#334155',
            'btn_secondary_text' => '#f1f5f9',
            'btn_hover_bg' => StorefrontTheme::mix($accent, '#ffffff', 0.1),
            'nav_text' => '#e5e7eb',
            'nav_hover' => '#ffffff',
            'nav_active' => StorefrontTheme::mix($accent, '#ffffff', 0.2),
            'mobile_menu_bg' => '#1e293b',
            'input_bg' => '#0f172a',
            'input_border' => '#475569',
            'input_focus_border' => $accent,
            'placeholder' => '#94a3b8',
            'success' => '#34d399',
            'warning' => '#fbbf24',
            'error' => '#f87171',
            'info' => '#60a5fa',
        ]);
    }

    /**
     * @param  array<string, string>  $light
     * @return array<string, string>
     */
    private static function gradientPreset(array $light, string $accent): array
    {
        $palette = $light;
        $palette['body_bg'] = StorefrontTheme::mix($accent, '#f8fafc', 0.92);
        $palette['header_bg'] = $accent;
        $palette['section_bg'] = '#ffffff';
        $palette['card_bg'] = '#ffffff';
        $palette['accent'] = $accent;
        $palette['primary'] = $accent;

        return self::deriveComputed($palette);
    }

    /**
     * @param  array<string, string>  $palette
     * @return array<string, string>
     */
    private static function applyLegacyColumns(array $palette, VendorStoreSetting $store): array
    {
        $map = [
            'theme_background_color' => 'body_bg',
            'theme_surface_color' => 'card_bg',
            'theme_header_color' => 'header_bg',
            'theme_button_color' => 'btn_primary_bg',
            'theme_button_text_color' => 'btn_primary_text',
            'theme_link_color' => 'link',
            'theme_footer_color' => 'footer_bg',
        ];

        foreach ($map as $column => $key) {
            $value = $store->{$column} ?? null;
            if (is_string($value) && trim($value) !== '') {
                $palette[$key] = StorefrontTheme::normalizeHex($value);
            }
        }

        if ($store->theme_accent_color) {
            $palette['accent'] = StorefrontTheme::normalizeHex($store->theme_accent_color);
        }

        return $palette;
    }

    /**
     * @param  array<string, mixed>  $custom
     * @param  array<string, string>  $palette
     * @return array<string, string>
     */
    private static function applyCustomColors(array $palette, array $custom): array
    {
        foreach (self::KEYS as $key) {
            $value = $custom[$key] ?? null;
            if (is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                $palette[$key] = StorefrontTheme::normalizeHex($value);
            }
        }

        return $palette;
    }

    /**
     * @param  array<string, string>  $palette
     * @return array<string, string>
     */
    private static function deriveComputed(array $palette): array
    {
        $palette['link_hover'] = $palette['link_hover'] ?? StorefrontTheme::mix($palette['link'], '#000000', 0.15);
        $palette['btn_hover_bg'] = $palette['btn_hover_bg'] ?? StorefrontTheme::mix($palette['btn_primary_bg'], '#000000', 0.12);
        $palette['section_bg'] = $palette['section_bg'] ?? $palette['card_bg'];

        return $palette;
    }

    /**
     * @param  array<string, string>  $partial
     * @return array<string, string>
     */
    private static function basePalette(array $partial): array
    {
        $out = [];
        foreach (self::KEYS as $key) {
            $out[$key] = $partial[$key] ?? '#059669';
        }

        return self::deriveComputed($out);
    }
}
