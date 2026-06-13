<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Items;
use App\Models\OrderItem;
use App\Models\Vendor;
use Illuminate\Support\Collection;

final class StorefrontHome
{
    public const SECTION_LIMIT = 8;

    public const CATEGORY_SECTION_LIMIT = 6;

    /**
     * @param  Collection<int, Category>  $categories
     * @param  Collection<int, Items>  $allItems
     * @return array{
     *     trending: Collection<int, Items>,
     *     new: Collection<int, Items>,
     *     latest: Collection<int, Items>,
     *     by_category: list<array{category: Category, items: Collection<int, Items>, url: string}>
     * }
     */
    public static function sections(Vendor $vendor, Collection $categories, Collection $allItems): array
    {
        if ($allItems->isEmpty()) {
            return [
                'trending' => collect(),
                'new' => collect(),
                'latest' => collect(),
                'by_category' => [],
            ];
        }

        $trendingIds = self::trendingItemIds($vendor->id, $allItems->pluck('id')->all());
        $trending = self::orderedSubset($allItems, $trendingIds);

        if ($trending->isEmpty()) {
            $trending = $allItems->sortByDesc(fn (Items $item) => (int) $item->stock)->take(self::SECTION_LIMIT)->values();
        }

        $new = $allItems
            ->filter(fn (Items $item) => $item->created_at?->gte(now()->subDays(45)) ?? false)
            ->sortByDesc('created_at')
            ->take(self::SECTION_LIMIT)
            ->values();

        $latest = $allItems
            ->sortByDesc('updated_at')
            ->take(self::SECTION_LIMIT)
            ->values();

        $byCategory = [];
        foreach ($categories as $category) {
            $catItems = $allItems
                ->where('category_id', $category->id)
                ->take(self::CATEGORY_SECTION_LIMIT)
                ->values();

            if ($catItems->isEmpty()) {
                continue;
            }

            $byCategory[] = [
                'category' => $category,
                'items' => $catItems,
                'url' => route('storefront.category', [$vendor->slug, $category->slug]),
            ];
        }

        return [
            'trending' => $trending,
            'new' => $new,
            'latest' => $latest,
            'by_category' => $byCategory,
        ];
    }

    /** @param  list<int>  $itemIds */
    private static function trendingItemIds(int $vendorId, array $itemIds): array
    {
        if ($itemIds === []) {
            return [];
        }

        return OrderItem::query()
            ->selectRaw('order_items.item_id, SUM(order_items.quantity) as order_total')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.vendor_id', $vendorId)
            ->where('orders.created_at', '>=', now()->subDays(90))
            ->whereNotIn('order_items.item_status', ['cancelled', 'returned'])
            ->whereIn('order_items.item_id', $itemIds)
            ->groupBy('order_items.item_id')
            ->orderByDesc('order_total')
            ->limit(self::SECTION_LIMIT)
            ->pluck('item_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  Collection<int, Items>  $allItems
     * @param  list<int>  $orderedIds
     * @return Collection<int, Items>
     */
    private static function orderedSubset(Collection $allItems, array $orderedIds): Collection
    {
        if ($orderedIds === []) {
            return collect();
        }

        $indexed = $allItems->keyBy('id');

        return collect($orderedIds)
            ->map(fn (int $id) => $indexed->get($id))
            ->filter()
            ->values();
    }
}
