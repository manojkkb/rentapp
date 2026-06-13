<?php

namespace App\Support;

use App\Models\VendorStoreSetting;

class StorefrontTheme
{
    /**
     * @return array<string, mixed>
     */
    public static function resolve(VendorStoreSetting $store): array
    {
        $template = in_array($store->theme_template, VendorStoreSetting::THEME_TEMPLATES, true)
            ? $store->theme_template
            : 'classic';

        $mode = in_array($store->theme_mode, StorefrontThemePalette::MODES, true)
            ? $store->theme_mode
            : 'light';

        $palette = StorefrontThemePalette::resolve($store);
        $accent = $palette['accent'];
        $headerBg = $palette['header_bg'];

        $heroGradient = $mode === 'gradient'
            ? 'linear-gradient(135deg, '.self::mix($accent, '#000000', 0.2).' 0%, '.$accent.' 45%, '.self::mix($accent, '#ffffff', 0.2).' 100%)'
            : 'linear-gradient(135deg, '.self::mix($accent, '#000000', 0.12).' 0%, '.$accent.' 55%, '.self::mix($accent, '#ffffff', 0.15).' 100%)';

        return [
            'template' => $template,
            'mode' => $mode,
            'palette' => $palette,
            'accent' => $accent,
            'accent_rgb' => self::hexToRgb($accent),
            'accent_light' => self::mix($accent, '#ffffff', 0.88),
            'accent_soft' => self::mix($accent, '#ffffff', 0.92),
            'accent_dark' => self::mix($accent, '#000000', 0.18),
            'accent_ring' => self::mix($accent, '#ffffff', 0.75),
            'background' => $palette['body_bg'],
            'surface' => $palette['card_bg'],
            'header' => $headerBg,
            'button' => $palette['btn_primary_bg'],
            'button_text' => $palette['btn_primary_text'],
            'button_hover' => $palette['btn_hover_bg'],
            'button_secondary' => $palette['btn_secondary_bg'],
            'button_secondary_text' => $palette['btn_secondary_text'],
            'link' => $palette['link'],
            'link_hover' => $palette['link_hover'],
            'footer' => $palette['footer_bg'],
            'text' => $palette['text'],
            'heading' => $palette['heading'],
            'header_is_light' => self::isLight($headerBg),
            'hero_gradient' => $heroGradient,
            'font' => StorefrontGoogleFonts::resolve($store->theme_font),
            'classes' => self::templateClasses($template),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function defaultColorsForTemplate(string $template): array
    {
        return StorefrontThemePalette::modePreset('light', $template, StorefrontThemeTemplate::defaultAccent($template));
    }

    public static function defaultAccentForTemplate(string $template): string
    {
        return StorefrontThemeTemplate::defaultAccent($template);
    }

    public static function normalizeHex(string $hex): string
    {
        $hex = ltrim(trim($hex), '#');
        if (! preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
            return '#059669';
        }

        return '#'.strtolower($hex);
    }

    /**
     * @return array{r: int, g: int, b: int}
     */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim(self::normalizeHex($hex), '#');

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    public static function isLight(string $hex): bool
    {
        $rgb = self::hexToRgb($hex);
        $luminance = (0.299 * $rgb['r'] + 0.587 * $rgb['g'] + 0.114 * $rgb['b']) / 255;

        return $luminance > 0.62;
    }

    public static function mix(string $hex, string $target, float $weight): string
    {
        $a = self::hexToRgb($hex);
        $b = self::hexToRgb($target);
        $w = max(0, min(1, $weight));

        $r = (int) round($a['r'] * (1 - $w) + $b['r'] * $w);
        $g = (int) round($a['g'] * (1 - $w) + $b['g'] * $w);
        $bl = (int) round($a['b'] * (1 - $w) + $b['b'] * $w);

        return sprintf('#%02x%02x%02x', $r, $g, $bl);
    }

    /**
     * @return array<string, string>
     */
    private static function templateClasses(string $template): array
    {
        return match ($template) {
            'modern' => [
                'page' => 'store-theme-modern',
                'header' => '',
                'card' => 'rounded-2xl shadow-md ring-1 ring-black/5',
                'card_image' => 'rounded-t-2xl aspect-[4/3]',
                'chip' => 'rounded-full',
                'btn' => 'rounded-full shadow-md',
                'hero' => 'rounded-none',
                'section' => 'rounded-2xl shadow-sm ring-1 ring-black/5',
            ],
            'minimal' => [
                'page' => 'store-theme-minimal',
                'header' => '',
                'card' => 'rounded-sm border border-gray-200',
                'card_image' => 'rounded-none aspect-[4/3]',
                'chip' => 'rounded-sm border',
                'btn' => 'rounded-sm',
                'hero' => 'border-b border-gray-200',
                'section' => 'rounded-sm border border-gray-200',
            ],
            'bold' => [
                'page' => 'store-theme-bold',
                'header' => 'border-b-4 border-[var(--store-accent)]',
                'card' => 'rounded-md border-2 shadow-lg ring-1 ring-black/5',
                'card_image' => 'rounded-t-md aspect-square',
                'chip' => 'rounded-md border-2 font-bold uppercase tracking-wide',
                'btn' => 'rounded-md font-bold uppercase tracking-wide shadow-md',
                'hero' => 'rounded-none',
                'section' => 'rounded-md border-2 shadow-md',
            ],
            'boutique' => [
                'page' => 'store-theme-boutique',
                'header' => 'border-b border-gray-200/80',
                'card' => 'rounded-xl border border-gray-200/90 shadow-sm',
                'card_image' => 'rounded-t-xl aspect-[4/5]',
                'chip' => 'rounded-lg border font-medium',
                'btn' => 'rounded-lg font-medium tracking-wide',
                'hero' => 'rounded-none border-b border-gray-200/60',
                'section' => 'rounded-xl border border-gray-200/80 shadow-sm',
            ],
            'neon' => [
                'page' => 'store-theme-neon',
                'header' => '',
                'card' => 'rounded-2xl border-2 shadow-lg store-theme-neon-card',
                'card_image' => 'rounded-t-2xl aspect-[3/4]',
                'chip' => 'rounded-full border-2 font-bold',
                'btn' => 'rounded-full font-bold shadow-lg',
                'hero' => 'rounded-none',
                'section' => 'rounded-2xl border-2 shadow-md',
            ],
            'nature' => [
                'page' => 'store-theme-nature',
                'header' => '',
                'card' => 'rounded-3xl border border-lime-200/80 shadow-md',
                'card_image' => 'rounded-t-3xl aspect-[5/4]',
                'chip' => 'rounded-full border border-lime-200',
                'btn' => 'rounded-full font-semibold',
                'hero' => 'rounded-none',
                'section' => 'rounded-3xl border border-lime-100 shadow-sm',
            ],
            'ocean' => [
                'page' => 'store-theme-ocean',
                'header' => 'border-b-2 border-sky-200',
                'card' => 'store-theme-ocean-card rounded-xl border border-sky-100 shadow-sm overflow-hidden',
                'card_image' => 'aspect-[4/3]',
                'chip' => 'rounded-lg border border-sky-200',
                'btn' => 'rounded-lg font-semibold',
                'hero' => 'rounded-none store-theme-ocean-hero',
                'section' => 'rounded-xl border border-sky-100 shadow-sm',
            ],
            'sunset' => [
                'page' => 'store-theme-sunset',
                'header' => '',
                'card' => 'rounded-2xl border border-orange-100 shadow-md',
                'card_image' => 'rounded-t-2xl aspect-[4/3]',
                'chip' => 'rounded-full border border-orange-200',
                'btn' => 'rounded-xl font-bold shadow-sm',
                'hero' => 'rounded-none',
                'section' => 'rounded-2xl border border-orange-100 shadow-sm',
            ],
            'mono' => [
                'page' => 'store-theme-mono',
                'header' => 'border-b-2 border-zinc-900',
                'card' => 'rounded-none border-2 border-zinc-900 shadow-none',
                'card_image' => 'aspect-[16/10]',
                'chip' => 'rounded-none border-2 border-zinc-300 font-bold uppercase text-xs tracking-widest',
                'btn' => 'rounded-none font-bold uppercase tracking-widest',
                'hero' => 'rounded-none',
                'section' => 'rounded-none border-2 border-zinc-200',
            ],
            default => [
                'page' => 'store-theme-classic',
                'header' => '',
                'card' => 'rounded-xl border border-gray-200/80 shadow-sm',
                'card_image' => 'rounded-t-xl aspect-[4/3]',
                'chip' => 'rounded-full',
                'btn' => 'rounded-lg shadow-sm',
                'hero' => 'rounded-none',
                'section' => 'rounded-xl border border-gray-200/80 shadow-sm',
            ],
        };
    }
}
