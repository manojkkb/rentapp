<?php

namespace App\Support;

final class StorefrontGoogleFonts
{
    public const DEFAULT = 'inter';

    /**
     * @return array<string, array{family: string, weights: string, category: string}>
     */
    public static function definitions(): array
    {
        return [
            'inter' => ['family' => 'Inter', 'weights' => '400;500;600;700', 'category' => 'sans'],
            'poppins' => ['family' => 'Poppins', 'weights' => '400;500;600;700', 'category' => 'sans'],
            'roboto' => ['family' => 'Roboto', 'weights' => '400;500;700', 'category' => 'sans'],
            'open-sans' => ['family' => 'Open Sans', 'weights' => '400;600;700', 'category' => 'sans'],
            'lato' => ['family' => 'Lato', 'weights' => '400;700', 'category' => 'sans'],
            'montserrat' => ['family' => 'Montserrat', 'weights' => '400;500;600;700', 'category' => 'sans'],
            'nunito' => ['family' => 'Nunito', 'weights' => '400;600;700', 'category' => 'sans'],
            'raleway' => ['family' => 'Raleway', 'weights' => '400;600;700', 'category' => 'sans'],
            'work-sans' => ['family' => 'Work Sans', 'weights' => '400;500;600;700', 'category' => 'sans'],
            'dm-sans' => ['family' => 'DM Sans', 'weights' => '400;500;700', 'category' => 'sans'],
            'rubik' => ['family' => 'Rubik', 'weights' => '400;500;700', 'category' => 'sans'],
            'plus-jakarta' => ['family' => 'Plus Jakarta Sans', 'weights' => '400;500;600;700', 'category' => 'sans'],
            'outfit' => ['family' => 'Outfit', 'weights' => '400;500;600;700', 'category' => 'sans'],
            'space-grotesk' => ['family' => 'Space Grotesk', 'weights' => '400;500;600;700', 'category' => 'display'],
            'oswald' => ['family' => 'Oswald', 'weights' => '400;500;600;700', 'category' => 'display'],
            'playfair' => ['family' => 'Playfair Display', 'weights' => '400;600;700', 'category' => 'serif'],
            'merriweather' => ['family' => 'Merriweather', 'weights' => '400;700', 'category' => 'serif'],
            'lora' => ['family' => 'Lora', 'weights' => '400;600;700', 'category' => 'serif'],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::definitions());
    }

    public static function isValid(string $key): bool
    {
        return isset(self::definitions()[$key]);
    }

    /**
     * @return array{key: string, family: string, weights: string, category: string, label: string, css_stack: string, stylesheet_url: string}
     */
    public static function resolve(?string $key): array
    {
        $key = is_string($key) && self::isValid($key) ? $key : self::DEFAULT;
        $def = self::definitions()[$key];

        return [
            'key' => $key,
            'family' => $def['family'],
            'weights' => $def['weights'],
            'category' => $def['category'],
            'label' => __('vendor.store_font_'.$key),
            'css_stack' => self::cssStack($key),
            'stylesheet_url' => self::stylesheetUrl($key),
        ];
    }

    public static function cssStack(string $key): string
    {
        $def = self::definitions()[self::isValid($key) ? $key : self::DEFAULT];
        $fallback = match ($def['category']) {
            'serif' => 'Georgia, serif',
            'display' => 'ui-sans-serif, system-ui, sans-serif',
            default => 'ui-sans-serif, system-ui, sans-serif',
        };

        return "'{$def['family']}', {$fallback}";
    }

    public static function stylesheetUrl(string $key): string
    {
        return '';
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        $out = [];
        foreach (self::keys() as $key) {
            $out[$key] = __('vendor.store_font_'.$key);
        }

        return $out;
    }

    /**
     * @return list<array{key: string, label: string, category: string, url: string, css: string}>
     */
    public static function forPicker(): array
    {
        return collect(self::keys())->map(function (string $key) {
            return [
                'key' => $key,
                'label' => __('vendor.store_font_'.$key),
                'category' => self::definitions()[$key]['category'],
                'url' => self::stylesheetUrl($key),
                'css' => self::cssStack($key),
            ];
        })->values()->all();
    }
}
