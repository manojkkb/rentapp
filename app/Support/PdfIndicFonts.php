<?php

namespace App\Support;

use FontLib\Font;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Noto fonts for Indian scripts in DomPDF invoices (DejaVu Sans has no Indic glyphs).
 */
class PdfIndicFonts
{
    private const BASE_URL = 'https://github.com/notofonts/noto-fonts/raw/main/hinted/ttf';

    /**
     * @var array<string, array{family: string, path: string}>
     */
    public const FONTS = [
        'NotoSansDevanagari-Regular' => [
            'family' => 'noto sans devanagari',
            'path' => 'NotoSansDevanagari/NotoSansDevanagari-Regular.ttf',
        ],
        'NotoSansBengali-Regular' => [
            'family' => 'noto sans bengali',
            'path' => 'NotoSansBengali/NotoSansBengali-Regular.ttf',
        ],
        'NotoSansTelugu-Regular' => [
            'family' => 'noto sans telugu',
            'path' => 'NotoSansTelugu/NotoSansTelugu-Regular.ttf',
        ],
        'NotoSansTamil-Regular' => [
            'family' => 'noto sans tamil',
            'path' => 'NotoSansTamil/NotoSansTamil-Regular.ttf',
        ],
        'NotoSansGujarati-Regular' => [
            'family' => 'noto sans gujarati',
            'path' => 'NotoSansGujarati/NotoSansGujarati-Regular.ttf',
        ],
        'NotoSansKannada-Regular' => [
            'family' => 'noto sans kannada',
            'path' => 'NotoSansKannada/NotoSansKannada-Regular.ttf',
        ],
        'NotoSansOriya-Regular' => [
            'family' => 'noto sans oriya',
            'path' => 'NotoSansOriya/NotoSansOriya-Regular.ttf',
        ],
        'NotoSansMalayalam-Regular' => [
            'family' => 'noto sans malayalam',
            'path' => 'NotoSansMalayalam/NotoSansMalayalam-Regular.ttf',
        ],
        'NotoSansGurmukhi-Regular' => [
            'family' => 'noto sans gurmukhi',
            'path' => 'NotoSansGurmukhi/NotoSansGurmukhi-Regular.ttf',
        ],
        'NotoNastaliqUrdu-Regular' => [
            'family' => 'noto nastaliq urdu',
            'path' => 'NotoNastaliqUrdu/NotoNastaliqUrdu-Regular.ttf',
        ],
    ];

    public static function fontDir(): string
    {
        return storage_path('fonts');
    }

    public static function bundledFontDir(): string
    {
        return resource_path('fonts/pdf');
    }

    public static function cssFontFamily(): string
    {
        $families = array_map(
            static fn (array $def): string => "'{$def['family']}'",
            self::FONTS
        );

        $families[] = "'dejavu sans'";

        return implode(', ', $families).', sans-serif';
    }

    public static function defaultFontFamily(): string
    {
        return 'noto sans devanagari';
    }

    public static function isInstalled(): bool
    {
        foreach (array_keys(self::FONTS) as $basename) {
            if (! is_file(self::fontDir().'/'.$basename.'.ufm')) {
                return false;
            }
        }

        return is_file(self::fontDir().'/installed-fonts.json');
    }

    public static function ensureInstalled(): void
    {
        if (self::isInstalled()) {
            return;
        }

        self::install();
    }

    /**
     * @return array<string, int> basename => bytes written
     */
    public static function install(): array
    {
        $fontDir = self::fontDir();
        File::ensureDirectoryExists($fontDir);

        $installed = [];
        $registry = [];

        if (is_file($fontDir.'/installed-fonts.json')) {
            $existing = json_decode((string) file_get_contents($fontDir.'/installed-fonts.json'), true);
            if (is_array($existing)) {
                $registry = $existing;
            }
        }

        foreach (self::FONTS as $basename => $def) {
            $ttfPath = $fontDir.'/'.$basename.'.ttf';
            $ufmPath = $fontDir.'/'.$basename.'.ufm';

            if (! is_file($ttfPath)) {
                $bundled = self::bundledFontDir().'/'.$basename.'.ttf';

                if (is_file($bundled)) {
                    File::copy($bundled, $ttfPath);
                } else {
                    self::downloadFont($basename, $def['path'], $ttfPath);
                }
            }

            if (! is_file($ufmPath)) {
                $font = Font::load($ttfPath);
                if (! $font) {
                    throw new RuntimeException("Failed to parse PDF font [{$basename}]");
                }
                $font->parse();
                $font->saveAdobeFontMetrics($ufmPath);
                $font->close();
            }

            $registry[$def['family']] = [
                'normal' => $basename,
                'bold' => $basename,
                'italic' => $basename,
                'bold_italic' => $basename,
            ];

            $installed[$basename] = (int) filesize($ttfPath);
        }

        file_put_contents(
            $fontDir.'/installed-fonts.json',
            json_encode($registry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $installed;
    }

    private static function downloadFont(string $basename, string $path, string $ttfPath): void
    {
        $url = self::BASE_URL.'/'.$path;

        try {
            $response = Http::timeout(120)->get($url);

            if ($response->successful()) {
                file_put_contents($ttfPath, $response->body());

                return;
            }
        } catch (Throwable) {
            // Fall through to file_get_contents (e.g. when local CA bundle is missing on Windows).
        }

        $context = stream_context_create([
            'http' => ['timeout' => 120],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $bytes = @file_get_contents($url, false, $context);

        if ($bytes === false || $bytes === '') {
            throw new RuntimeException(
                "Failed to load PDF font [{$basename}]. Run: php artisan pdf:install-indic-fonts (requires bundled fonts or network)."
            );
        }

        file_put_contents($ttfPath, $bytes);
    }
}
