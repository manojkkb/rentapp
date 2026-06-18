<?php

namespace App\Support;

use App\Models\Vendor;
use Illuminate\Support\Facades\Log;

final class SiteSeo
{
    public const BRAND = 'Rentkia';

    public static function title(string $pageTitle = ''): string
    {
        if ($pageTitle === '') {
            return self::BRAND.' | India\'s Rental Marketplace';
        }

        return self::BRAND.' | '.$pageTitle;
    }

    /**
     * @return array{
     *     title: string,
     *     description: string,
     *     keywords: string|null,
     *     canonical: string,
     *     og_type: string,
     *     og_image: string|null,
     *     robots: string,
     *     json_ld: list<array<string, mixed>>
     * }
     */
    public static function forPage(string $pageTitle, string $description, string $path): array
    {
        $title = self::title($pageTitle);
        $canonical = url($path);

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => self::BRAND.', rental marketplace India, rent equipment online',
            'canonical' => $canonical,
            'og_type' => 'website',
            'og_image' => asset('vendor/icons/icon-512.png'),
            'robots' => 'index, follow',
            'json_ld' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebPage',
                    'name' => $title,
                    'url' => $canonical,
                    'description' => $description,
                    'inLanguage' => 'en-IN',
                    'isPartOf' => [
                        '@type' => 'WebSite',
                        'name' => self::BRAND,
                        'url' => rtrim((string) config('app.url'), '/'),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{
     *     title: string,
     *     description: string,
     *     keywords: string|null,
     *     canonical: string,
     *     og_type: string,
     *     og_image: string|null,
     *     robots: string,
     *     json_ld: list<array<string, mixed>>
     * }
     */
    public static function forHome(): array
    {
        $siteUrl = rtrim((string) config('app.url'), '/');
        $title = self::title('Rent Cameras, Tools & Equipment Online in India');
        $description = 'Rentkia is India\'s rental marketplace. Rent cameras, tools, electronics, party gear and more from trusted local vendors near you. Book online with pickup or delivery.';
        $keywords = 'Rentkia, rental marketplace India, rent camera, rent tools, equipment rental, party equipment rent, vendor rental store';
        $canonical = url('/');
        $logo = asset('vendor/icons/icon-512.png');

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => $canonical,
            'og_type' => 'website',
            'og_image' => $logo,
            'robots' => 'index, follow',
            'json_ld' => [
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => self::BRAND,
                    'url' => $siteUrl,
                    'logo' => $logo,
                    'email' => 'hello@rentkia.com',
                    'description' => $description,
                    'sameAs' => [],
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => self::BRAND,
                    'url' => $siteUrl,
                    'description' => $description,
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => self::BRAND,
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => $logo,
                        ],
                    ],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => url('/stores').'?q={search_term_string}',
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebPage',
                    'name' => $title,
                    'url' => $canonical,
                    'description' => $description,
                    'inLanguage' => 'en-IN',
                    'isPartOf' => [
                        '@type' => 'WebSite',
                        'name' => self::BRAND,
                        'url' => $siteUrl,
                    ],
                ],
            ],
        ];
    }

    /**
     * @return list<array{loc: string, changefreq: string, priority: string, lastmod: string|null}>
     */
    public static function buildSitemapEntries(): array
    {
        $now = now()->toAtomString();

        $entries = [
            ['loc' => url('/'), 'changefreq' => 'weekly', 'priority' => '1.0', 'lastmod' => $now],
            ['loc' => url('/stores'), 'changefreq' => 'daily', 'priority' => '0.9', 'lastmod' => $now],
            ['loc' => url('/about-us'), 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => $now],
            ['loc' => url('/how-it-works'), 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => $now],
            ['loc' => url('/our-team'), 'changefreq' => 'monthly', 'priority' => '0.6', 'lastmod' => $now],
            ['loc' => url('/contact-us'), 'changefreq' => 'monthly', 'priority' => '0.6', 'lastmod' => $now],
            ['loc' => url('/privacy-policy'), 'changefreq' => 'yearly', 'priority' => '0.4', 'lastmod' => $now],
            ['loc' => url('/terms-and-conditions'), 'changefreq' => 'yearly', 'priority' => '0.4', 'lastmod' => $now],
        ];

        try {
            Vendor::query()
                ->active()
                ->with('storeSettings')
                ->orderByDesc('updated_at')
                ->get()
                ->filter(fn (Vendor $vendor) => StorefrontPublish::isLive($vendor, $vendor->storeSettings))
                ->each(function (Vendor $vendor) use (&$entries) {
                    $entries[] = [
                        'loc' => url('/store/'.$vendor->slug),
                        'changefreq' => 'weekly',
                        'priority' => '0.8',
                        'lastmod' => $vendor->updated_at?->toAtomString(),
                    ];
                });
        } catch (\Throwable $e) {
            Log::warning('Sitemap vendor entries skipped', ['error' => $e->getMessage()]);
        }

        return $entries;
    }

    /**
     * @param  list<array{loc: string, changefreq: string, priority: string, lastmod?: string|null}>  $entries
     */
    public static function toXml(array $entries): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($entries as $entry) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.htmlspecialchars($entry['loc'], ENT_XML1 | ENT_COMPAT, 'UTF-8').'</loc>';
            if (! empty($entry['lastmod'])) {
                $lines[] = '    <lastmod>'.htmlspecialchars((string) $entry['lastmod'], ENT_XML1 | ENT_COMPAT, 'UTF-8').'</lastmod>';
            }
            $lines[] = '    <changefreq>'.htmlspecialchars($entry['changefreq'], ENT_XML1 | ENT_COMPAT, 'UTF-8').'</changefreq>';
            $lines[] = '    <priority>'.htmlspecialchars($entry['priority'], ENT_XML1 | ENT_COMPAT, 'UTF-8').'</priority>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }
}
