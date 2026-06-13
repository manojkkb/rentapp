<?php

namespace App\Support;

final class StorefrontThemeTemplate
{
    /** @return array<string, string> */
    public static function defaultAccents(): array
    {
        return [
            'classic' => '#059669',
            'modern' => '#0d9488',
            'minimal' => '#64748b',
            'bold' => '#2563eb',
            'boutique' => '#a8a29e',
            'neon' => '#9333ea',
            'nature' => '#65a30d',
            'ocean' => '#0284c7',
            'sunset' => '#ea580c',
            'mono' => '#18181b',
        ];
    }

    public static function defaultAccent(string $template): string
    {
        return self::defaultAccents()[$template] ?? '#059669';
    }

    public static function previewGradient(string $template): string
    {
        return match ($template) {
            'classic' => 'linear-gradient(180deg, #059669 30%, #f9fafb 30%)',
            'modern' => 'linear-gradient(135deg, #0d9488 0%, #ccfbf1 50%, #fff 100%)',
            'minimal' => 'linear-gradient(180deg, #fff 35%, #e2e8f0 35%)',
            'bold' => 'linear-gradient(180deg, #2563eb 25%, #e2e8f0 25%)',
            'boutique' => 'linear-gradient(90deg, #fafaf9 42%, #d6d3d1 42%, #a8a29e 100%)',
            'neon' => 'linear-gradient(135deg, #4c1d95 0%, #c026d3 45%, #faf5ff 100%)',
            'nature' => 'linear-gradient(180deg, #365314 22%, #fefce8 22%, #ecfccb 100%)',
            'ocean' => 'linear-gradient(180deg, #0c4a6e 20%, #e0f2fe 20%, #f0f9ff 100%)',
            'sunset' => 'linear-gradient(135deg, #c2410c 0%, #db2777 40%, #fff7ed 100%)',
            'mono' => 'linear-gradient(180deg, #18181b 28%, #fff 28%)',
            default => 'linear-gradient(180deg, #059669 30%, #f9fafb 30%)',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function lightScheme(string $template): array
    {
        return match ($template) {
            'modern' => [
                'secondary' => '#0f766e',
                'text' => '#134e4a',
                'heading' => '#042f2e',
                'link' => '#0f766e',
                'body_bg' => '#f8fafc',
                'section_bg' => '#ffffff',
                'card_bg' => '#ffffff',
                'footer_bg' => '#f1f5f9',
                'header_bg' => '#0d9488',
                'btn_secondary_bg' => '#ccfbf1',
                'btn_secondary_text' => '#115e59',
                'success' => '#0d9488',
                'info' => '#0891b2',
            ],
            'minimal' => [
                'secondary' => '#94a3b8',
                'text' => '#475569',
                'heading' => '#1e293b',
                'link' => '#64748b',
                'body_bg' => '#ffffff',
                'section_bg' => '#fafafa',
                'card_bg' => '#ffffff',
                'footer_bg' => '#f8fafc',
                'header_bg' => '#ffffff',
                'btn_secondary_bg' => '#f1f5f9',
                'btn_secondary_text' => '#475569',
                'success' => '#64748b',
                'warning' => '#78716c',
            ],
            'bold' => [
                'secondary' => '#1e40af',
                'text' => '#1e3a8a',
                'heading' => '#172554',
                'link' => '#1d4ed8',
                'body_bg' => '#f1f5f9',
                'section_bg' => '#ffffff',
                'card_bg' => '#ffffff',
                'footer_bg' => '#e2e8f0',
                'header_bg' => '#2563eb',
                'btn_secondary_bg' => '#dbeafe',
                'btn_secondary_text' => '#1e40af',
                'success' => '#2563eb',
                'info' => '#3b82f6',
            ],
            'boutique' => [
                'secondary' => '#78716c',
                'text' => '#57534e',
                'heading' => '#44403c',
                'link' => '#78716c',
                'body_bg' => '#fafaf9',
                'section_bg' => '#ffffff',
                'card_bg' => '#ffffff',
                'footer_bg' => '#f5f5f4',
                'header_bg' => '#ffffff',
                'btn_secondary_bg' => '#f5f5f4',
                'btn_secondary_text' => '#57534e',
                'success' => '#78716c',
                'warning' => '#a8a29e',
            ],
            'neon' => [
                'secondary' => '#c026d3',
                'text' => '#581c87',
                'heading' => '#3b0764',
                'link' => '#a21caf',
                'body_bg' => '#faf5ff',
                'section_bg' => '#fdf4ff',
                'card_bg' => '#ffffff',
                'footer_bg' => '#f3e8ff',
                'header_bg' => '#4c1d95',
                'btn_secondary_bg' => '#fae8ff',
                'btn_secondary_text' => '#86198f',
                'nav_text' => '#f5d0fe',
                'nav_hover' => '#ffffff',
                'nav_active' => '#f0abfc',
                'success' => '#a855f7',
                'info' => '#c026d3',
                'warning' => '#d946ef',
            ],
            'nature' => [
                'secondary' => '#b45309',
                'text' => '#365314',
                'heading' => '#1a2e05',
                'link' => '#4d7c0f',
                'body_bg' => '#fefce8',
                'section_bg' => '#fef9c3',
                'card_bg' => '#fffbeb',
                'footer_bg' => '#ecfccb',
                'header_bg' => '#365314',
                'btn_secondary_bg' => '#ecfccb',
                'btn_secondary_text' => '#3f6212',
                'success' => '#65a30d',
                'warning' => '#ca8a04',
                'info' => '#4d7c0f',
            ],
            'ocean' => [
                'secondary' => '#0369a1',
                'text' => '#0c4a6e',
                'heading' => '#082f49',
                'link' => '#0284c7',
                'body_bg' => '#f0f9ff',
                'section_bg' => '#e0f2fe',
                'card_bg' => '#ffffff',
                'footer_bg' => '#bae6fd',
                'header_bg' => '#0c4a6e',
                'btn_secondary_bg' => '#e0f2fe',
                'btn_secondary_text' => '#075985',
                'success' => '#0284c7',
                'info' => '#0ea5e9',
                'warning' => '#0891b2',
            ],
            'sunset' => [
                'secondary' => '#db2777',
                'text' => '#9a3412',
                'heading' => '#7c2d12',
                'link' => '#c2410c',
                'body_bg' => '#fff7ed',
                'section_bg' => '#ffedd5',
                'card_bg' => '#ffffff',
                'footer_bg' => '#fed7aa',
                'header_bg' => '#c2410c',
                'btn_secondary_bg' => '#ffedd5',
                'btn_secondary_text' => '#9a3412',
                'success' => '#ea580c',
                'warning' => '#f97316',
                'error' => '#e11d48',
                'info' => '#db2777',
            ],
            'mono' => [
                'secondary' => '#52525b',
                'text' => '#3f3f46',
                'heading' => '#18181b',
                'link' => '#27272a',
                'body_bg' => '#ffffff',
                'section_bg' => '#fafafa',
                'card_bg' => '#ffffff',
                'footer_bg' => '#f4f4f5',
                'header_bg' => '#18181b',
                'btn_secondary_bg' => '#f4f4f5',
                'btn_secondary_text' => '#3f3f46',
                'nav_text' => '#e4e4e7',
                'nav_hover' => '#ffffff',
                'nav_active' => '#ffffff',
                'success' => '#52525b',
                'warning' => '#71717a',
                'info' => '#3f3f46',
            ],
            default => [
                'secondary' => '#047857',
                'text' => '#374151',
                'heading' => '#111827',
                'link' => '#047857',
                'body_bg' => '#f9fafb',
                'section_bg' => '#ffffff',
                'card_bg' => '#ffffff',
                'footer_bg' => '#ffffff',
                'header_bg' => '#059669',
                'btn_secondary_bg' => '#f3f4f6',
                'btn_secondary_text' => '#374151',
                'success' => '#059669',
                'warning' => '#d97706',
                'error' => '#dc2626',
                'info' => '#2563eb',
            ],
        };
    }
}
