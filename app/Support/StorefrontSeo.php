<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Items;
use App\Models\OrderItem;
use App\Models\Vendor;
use App\Models\VendorStoreSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class StorefrontSeo
{
    /**
     * @return array{
     *     title: string,
     *     description: string|null,
     *     keywords: string|null,
     *     canonical: string,
     *     og_type: string,
     *     og_image: string|null,
     *     robots: string,
     *     json_ld: list<array<string, mixed>>
     * }
     */
    public static function forHome(Vendor $vendor, VendorStoreSetting $store): array
    {
        $title = trim((string) $store->seo_title) !== ''
            ? (string) $store->seo_title
            : $vendor->name.' — '.__('vendor.online_store');

        $description = trim((string) $store->seo_description);
        if ($description === '' && $store->description) {
            $description = Str::limit(strip_tags((string) $store->description), 160);
        }
        if ($description === '' && $store->tagline) {
            $description = Str::limit((string) $store->tagline, 160);
        }

        return self::base(
            $vendor,
            $store,
            $title,
            $description !== '' ? $description : null,
            route('storefront.show', $vendor->slug),
            'website',
            self::organizationSchema($vendor, $store),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function forCategory(Vendor $vendor, VendorStoreSetting $store, Category $category, int $itemCount): array
    {
        $title = $category->name.' — '.$vendor->name;
        $location = trim($vendor->city.' '.($vendor->state ?? ''));
        $description = __('vendor.store_seo_category_description', [
            'category' => $category->name,
            'vendor' => $vendor->name,
            'count' => $itemCount,
            'location' => $location !== '' ? $location : $vendor->name,
        ]);

        $url = route('storefront.category', [$vendor->slug, $category->slug]);

        return self::base(
            $vendor,
            $store,
            $title,
            Str::limit($description, 160),
            $url,
            'website',
            [
                self::organizationSchema($vendor, $store),
                self::breadcrumbSchema($vendor, [
                    ['name' => $vendor->name, 'url' => route('storefront.show', $vendor->slug)],
                    ['name' => $category->name, 'url' => $url],
                ]),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function forItem(Vendor $vendor, VendorStoreSetting $store, Items $item): array
    {
        $title = $item->name.' — '.$vendor->name;
        $description = trim(strip_tags((string) $item->description));
        if ($description === '') {
            $description = __('vendor.store_seo_item_description', [
                'item' => $item->name,
                'vendor' => $vendor->name,
                'category' => $item->category?->name ?? __('vendor.items'),
            ]);
        }

        $url = route('storefront.items.show', [$vendor->slug, $item]);

        $schemas = [
            self::organizationSchema($vendor, $store),
            self::breadcrumbSchema($vendor, array_filter([
                ['name' => $vendor->name, 'url' => route('storefront.show', $vendor->slug)],
                $item->category
                    ? ['name' => $item->category->name, 'url' => route('storefront.category', [$vendor->slug, $item->category->slug])]
                    : null,
                ['name' => $item->name, 'url' => $url],
            ])),
        ];

        if ($store->show_prices_online) {
            $schemas[] = self::productSchema($vendor, $item, $url);
        }

        return self::base(
            $vendor,
            $store,
            $title,
            Str::limit($description, 160),
            $url,
            'product',
            $schemas,
            $item->photo_url,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function forPage(Vendor $vendor, VendorStoreSetting $store, string $pageTitle, ?string $description, string $canonical): array
    {
        return self::base(
            $vendor,
            $store,
            $pageTitle.' — '.$vendor->name,
            $description ? Str::limit(strip_tags($description), 160) : null,
            $canonical,
            'website',
            [
                self::organizationSchema($vendor, $store),
                self::breadcrumbSchema($vendor, [
                    ['name' => $vendor->name, 'url' => route('storefront.show', $vendor->slug)],
                    ['name' => $pageTitle, 'url' => $canonical],
                ]),
            ],
        );
    }

    /**
     * @param  list<array<string, mixed>>  $extraSchemas
     * @return array<string, mixed>
     */
    private static function base(
        Vendor $vendor,
        VendorStoreSetting $store,
        string $title,
        ?string $description,
        string $canonical,
        string $ogType,
        array $extraSchemas,
        ?string $ogImage = null,
    ): array {
        $keywords = trim((string) $store->seo_keywords);

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords !== '' ? $keywords : null,
            'canonical' => $canonical,
            'og_type' => $ogType,
            'og_image' => $ogImage ?? $vendor->logo_url,
            'robots' => 'index, follow',
            'json_ld' => array_values(array_filter($extraSchemas)),
        ];
    }

    /** @return array<string, mixed> */
    private static function organizationSchema(Vendor $vendor, VendorStoreSetting $store): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $vendor->name,
            'url' => route('storefront.show', $vendor->slug),
            'logo' => $vendor->logo_url,
            'description' => $store->tagline ?: Str::limit((string) $store->description, 200),
            'telephone' => $store->business_phone,
            'email' => $store->business_email,
            'address' => $vendor->full_address !== ''
                ? ['@type' => 'PostalAddress', 'streetAddress' => $vendor->full_address]
                : null,
        ]);
    }

    /**
     * @param  list<array{name: string, url: string}|null>  $crumbs
     * @return array<string, mixed>
     */
    private static function breadcrumbSchema(Vendor $vendor, array $crumbs): array
    {
        $items = [];
        foreach (array_values(array_filter($crumbs)) as $index => $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /** @return array<string, mixed> */
    private static function productSchema(Vendor $vendor, Items $item, string $url): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $item->name,
            'description' => Str::limit(strip_tags((string) $item->description), 300),
            'image' => $item->photo_url,
            'sku' => $item->item_code,
            'url' => $url,
            'category' => $item->category?->name,
            'brand' => [
                '@type' => 'Brand',
                'name' => $vendor->name,
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => (float) $item->price,
                'priceCurrency' => 'INR',
                'availability' => $item->is_available
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => $url,
            ],
        ]);
    }
}
