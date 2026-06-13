<?php

namespace App\Support;

use App\Models\Items;
use App\Models\ItemVariant;
use Illuminate\Support\Collection;

final class StorefrontCatalog
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function itemsPayload(Collection $items): array
    {
        return $items->map(fn (Items $item) => self::itemPayload($item))->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function itemPayload(Items $item): array
    {
        $rentalPeriod = in_array($item->rental_period ?? '', Items::rentalPeriodKeys(), true)
            ? $item->rental_period
            : 'per_day';

        $payload = [
            'id' => $item->id,
            'name' => $item->name,
            'photo_url' => $item->photo_url,
            'price' => (float) $item->price,
            'rental_period' => $rentalPeriod,
            'uses_billing_units' => Items::rentalPeriodUsesBillingUnits($rentalPeriod),
            'has_variants' => $item->usesVariants(),
            'variants' => [],
        ];

        if (! $item->usesVariants()) {
            return $payload;
        }

        $attributes = $item->relationLoaded('variantAttributes')
            ? $item->variantAttributes
            : $item->variantAttributes()->ordered()->get();
        $variants = $item->relationLoaded('variants')
            ? $item->variants
            : $item->variants()->ordered()->get();

        $activeVariants = $variants->filter(fn (ItemVariant $v) => $v->is_active && $v->is_available);
        $prices = $activeVariants->map(fn (ItemVariant $v) => (float) $v->price);

        $payload['variants'] = $variants->map(fn (ItemVariant $v) => [
            'id' => $v->id,
            'label' => $v->displayLabel($attributes),
            'price' => (float) $v->price,
            'stock' => $v->rentableAvailableStock(),
            'manage_stock' => (bool) $v->manage_stock,
            'is_available' => (bool) ($v->is_active && $v->is_available),
            'variant_code' => $v->variant_code,
        ])->values()->all();
        $payload['price'] = $prices->isNotEmpty() ? (float) $prices->min() : (float) $item->price;
        $payload['price_min'] = $prices->isNotEmpty() ? (float) $prices->min() : null;
        $payload['price_max'] = $prices->isNotEmpty() ? (float) $prices->max() : null;

        return $payload;
    }
}
