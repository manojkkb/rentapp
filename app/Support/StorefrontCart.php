<?php

namespace App\Support;

use App\Models\Items;
use App\Models\ItemVariant;
use App\Support\StorefrontBooking;
use App\Support\StorefrontRentalPricing;
use Illuminate\Support\Collection;

final class StorefrontCart
{
    private const SESSION_PREFIX = 'storefront_cart.';

    public function __construct(
        private readonly int $vendorId,
    ) {}

    public static function forVendor(int $vendorId): self
    {
        return new self($vendorId);
    }

    /** @return list<array{key: string, item_id: int, item_variant_id: int|null, quantity: int}> */
    public function lines(): array
    {
        $raw = session($this->sessionKey(), []);

        return is_array($raw) ? array_values($raw) : [];
    }

    public function count(): int
    {
        return array_sum(array_map(fn (array $line) => (int) ($line['quantity'] ?? 0), $this->lines()));
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function add(int $itemId, int $quantity = 1, ?int $variantId = null): void
    {
        $quantity = max(1, $quantity);
        $key = $this->lineKey($itemId, $variantId);
        $lines = $this->indexedLines();

        if (isset($lines[$key])) {
            $lines[$key]['quantity'] += $quantity;
        } else {
            $lines[$key] = [
                'key' => $key,
                'item_id' => $itemId,
                'item_variant_id' => $variantId,
                'quantity' => $quantity,
            ];
        }

        $this->persist($lines);
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        $lines = $this->indexedLines();
        if (! isset($lines[$key])) {
            return;
        }

        if ($quantity < 1) {
            unset($lines[$key]);
        } else {
            $lines[$key]['quantity'] = $quantity;
        }

        $this->persist($lines);
    }

    public function remove(string $key): void
    {
        $lines = $this->indexedLines();
        unset($lines[$key]);
        $this->persist($lines);
    }

    public function clear(): void
    {
        session()->forget($this->sessionKey());
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function enrichedLines(int $vendorId): Collection
    {
        $booking = StorefrontBooking::forVendor($vendorId);
        $start = $booking->startAt();
        $end = $booking->endAt();
        $billingDefaults = $booking->billingDefaultsByPriceType();

        return collect($this->lines())->map(function (array $line) use ($vendorId, $start, $end, $billingDefaults) {
            $item = Items::query()
                ->where('vendor_id', $vendorId)
                ->where('id', $line['item_id'])
                ->active()
                ->available()
                ->with('category')
                ->first();

            if (! $item) {
                return null;
            }

            $variant = null;
            $variantLabel = null;
            if (! empty($line['item_variant_id'])) {
                $variant = ItemVariant::query()
                    ->where('item_id', $item->id)
                    ->where('id', $line['item_variant_id'])
                    ->where('is_active', true)
                    ->where('is_available', true)
                    ->first();
                if ($variant) {
                    $variantLabel = $variant->displayLabel(
                        $item->relationLoaded('variantAttributes')
                            ? $item->variantAttributes
                            : $item->variantAttributes()->ordered()->get()
                    );
                }
            }

            $rentalPeriod = in_array($item->rental_period ?? '', Items::rentalPeriodKeys(), true)
                ? $item->rental_period
                : 'per_day';
            $unitPrice = $variant ? (float) $variant->price : (float) $item->price;
            $billingUnits = ($start && $end)
                ? StorefrontRentalPricing::billingUnitsForItem($start, $end, $item, $billingDefaults)
                : null;
            $lineTotal = ($start && $end)
                ? StorefrontRentalPricing::lineSubtotal($unitPrice, (int) $line['quantity'], $rentalPeriod, $billingUnits)
                : round($unitPrice * (int) $line['quantity'], 2);

            $periodLabel = Items::rentalPeriodSelectOptions()[$rentalPeriod] ?? $rentalPeriod;

            return [
                ...$line,
                'item' => $item,
                'variant' => $variant,
                'name' => $variantLabel ? $item->name.' ('.$variantLabel.')' : $item->name,
                'variant_label' => $variantLabel,
                'photo_url' => $item->photo_url,
                'category_name' => $item->category?->name,
                'unit_price' => $unitPrice,
                'rental_period' => $rentalPeriod,
                'rental_period_label' => $periodLabel,
                'uses_billing_units' => Items::rentalPeriodUsesBillingUnits($rentalPeriod),
                'billing_units' => $billingUnits,
                'line_total' => $lineTotal,
            ];
        })->filter()->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toOrderLines(): array
    {
        return array_map(fn (array $line) => [
            'item_id' => (int) $line['item_id'],
            'item_variant_id' => $line['item_variant_id'] ?? null,
            'quantity' => (int) $line['quantity'],
        ], $this->lines());
    }

    private function sessionKey(): string
    {
        return self::SESSION_PREFIX.$this->vendorId;
    }

    /** @return array<string, array<string, mixed>> */
    private function indexedLines(): array
    {
        $indexed = [];
        foreach ($this->lines() as $line) {
            $indexed[$line['key']] = $line;
        }

        return $indexed;
    }

    /** @param array<string, array<string, mixed>> $lines */
    private function persist(array $lines): void
    {
        session([$this->sessionKey() => array_values($lines)]);
    }

    private function lineKey(int $itemId, ?int $variantId): string
    {
        return $variantId ? "{$itemId}_v{$variantId}" : (string) $itemId;
    }
}
