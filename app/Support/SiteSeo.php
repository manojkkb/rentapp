<?php

namespace App\Support;

use App\Models\Vendor;

final class SiteSeo
{
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
        $title = 'Rentkia — Rent Cameras, Tools & Equipment Online in India';
        $description = 'Rentkia is India\'s rental marketplace. Rent cameras, tools, electronics, party gear and more from trusted local vendors near you. Book online with pickup or delivery.';
        $keywords = 'Rentkia, rental marketplace India, rent camera, rent tools, equipment rental, party equipment rent, vendor rental store';
        $canonical = route('welcome');
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
                    'name' => 'Rentkia',
                    'url' => $siteUrl,
                    'logo' => $logo,
                    'email' => 'hello@rentkia.com',
                    'description' => $description,
                    'sameAs' => [],
                ],
                [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => 'Rentkia',
                    'url' => $siteUrl,
                    'description' => $description,
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => 'Rentkia',
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => $logo,
                        ],
                    ],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => route('stores.index').'?q={search_term_string}',
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
                        'name' => 'Rentkia',
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
            ['loc' => route('welcome'), 'changefreq' => 'weekly', 'priority' => '1.0', 'lastmod' => $now],
            ['loc' => route('stores.index'), 'changefreq' => 'daily', 'priority' => '0.9', 'lastmod' => $now],
            ['loc' => route('pages.about'), 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => $now],
            ['loc' => route('pages.how-it-works'), 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => $now],
            ['loc' => route('pages.team'), 'changefreq' => 'monthly', 'priority' => '0.6', 'lastmod' => $now],
            ['loc' => route('pages.contact'), 'changefreq' => 'monthly', 'priority' => '0.6', 'lastmod' => $now],
            ['loc' => route('legal.privacy'), 'changefreq' => 'yearly', 'priority' => '0.4', 'lastmod' => $now],
            ['loc' => route('legal.terms'), 'changefreq' => 'yearly', 'priority' => '0.4', 'lastmod' => $now],
        ];

        Vendor::query()
            ->active()
            ->with('storeSettings')
            ->orderByDesc('updated_at')
            ->get()
            ->filter(fn (Vendor $vendor) => StorefrontPublish::isLive($vendor, $vendor->storeSettings))
            ->each(function (Vendor $vendor) use (&$entries) {
                $entries[] = [
                    'loc' => route('storefront.show', $vendor->slug),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                    'lastmod' => $vendor->updated_at?->toAtomString(),
                ];
            });

        return $entries;
    }
}
