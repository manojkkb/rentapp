<?php

namespace App\Services;

use App\Models\ItemVariant;
use App\Models\Items;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RentalStockAvailability
{
    public const BUFFER_MINUTES = 20;

    /**
     * @param  Collection<string, Collection<int, array{start: Carbon, end: Carbon, quantity: int}>>  $commitmentsByKey
     */
    public function __construct(
        private readonly ?Carbon $windowStart,
        private readonly ?Carbon $windowEnd,
        private readonly Collection $commitmentsByKey,
    ) {}

    public static function forWizard(int $vendorId, array $wizard): self
    {
        $start = self::parseWizardTime($wizard['start_time'] ?? null);
        $end = self::parseWizardTime($wizard['end_time'] ?? null);

        if (! $start || ! $end || $end->lte($start)) {
            return new self(null, null, collect());
        }

        $buffer = self::BUFFER_MINUTES;
        $searchFrom = $start->copy()->subMinutes($buffer);
        $searchTo = $end->copy()->addMinutes($buffer);

        $rows = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.vendor_id', $vendorId)
            ->where('orders.status', '!=', 'cancelled')
            ->whereNotIn('order_items.item_status', ['cancelled', 'returned'])
            ->where(function ($q) use ($searchFrom, $searchTo) {
                $q->where(function ($inner) use ($searchFrom) {
                    $inner->whereNotNull('order_items.end_at')
                        ->where('order_items.end_at', '>', $searchFrom);
                })->orWhere(function ($inner) use ($searchFrom) {
                    $inner->whereNull('order_items.end_at')
                        ->whereNotNull('orders.end_at')
                        ->where('orders.end_at', '>', $searchFrom);
                });
            })
            ->where(function ($q) use ($searchTo) {
                $q->where(function ($inner) use ($searchTo) {
                    $inner->whereNotNull('order_items.start_at')
                        ->where('order_items.start_at', '<', $searchTo);
                })->orWhere(function ($inner) use ($searchTo) {
                    $inner->whereNull('order_items.start_at')
                        ->whereNotNull('orders.start_at')
                        ->where('orders.start_at', '<', $searchTo);
                });
            })
            ->get([
                'order_items.item_id',
                'order_items.item_variant_id',
                'order_items.quantity',
                'order_items.returned_qty',
                'order_items.start_at',
                'order_items.end_at',
                'orders.start_at as order_start_at',
                'orders.end_at as order_end_at',
            ]);

        $grouped = collect();

        foreach ($rows as $row) {
            $lineStart = $row->start_at ?? $row->order_start_at;
            $lineEnd = $row->end_at ?? $row->order_end_at;

            if (! $lineStart || ! $lineEnd) {
                continue;
            }

            $lineStart = Carbon::parse($lineStart);
            $lineEnd = Carbon::parse($lineEnd);

            if (! self::bufferedIntervalOverlapsWindow($lineStart, $lineEnd, $start, $end, $buffer)) {
                continue;
            }

            $qty = max(0, (int) $row->quantity - (int) $row->returned_qty);

            if ($qty < 1) {
                continue;
            }

            $key = self::stockKey((int) $row->item_id, $row->item_variant_id ? (int) $row->item_variant_id : null);

            if (! $grouped->has($key)) {
                $grouped->put($key, collect());
            }

            $grouped->get($key)->push([
                'start' => $lineStart,
                'end' => $lineEnd,
                'quantity' => $qty,
            ]);
        }

        return new self($start, $end, $grouped);
    }

    public function hasBookingWindow(): bool
    {
        return $this->windowStart !== null && $this->windowEnd !== null;
    }

    public function availableForItem(Items $item): int
    {
        if (! $this->hasBookingWindow()) {
            return $item->rentableAvailableStock();
        }

        if ($item->usesVariants()) {
            $variants = $item->relationLoaded('variants')
                ? $item->variants
                : $item->variants()->get();

            return (int) $variants->sum(fn (ItemVariant $v) => $this->availableForVariant($item, $v));
        }

        if (! ($item->manage_stock ?? false)) {
            return (int) $item->stock;
        }

        return $this->availableQuantity((int) $item->stock, (int) $item->id, null);
    }

    public function availableForVariant(Items $item, ItemVariant $variant): int
    {
        if (! $this->hasBookingWindow()) {
            return $variant->rentableAvailableStock();
        }

        if (! ($variant->manage_stock ?? false)) {
            return (int) $variant->stock;
        }

        return $this->availableQuantity((int) $variant->stock, (int) $item->id, (int) $variant->id);
    }

    /**
     * Available units for a line, counting other wizard cart lines but not the current line.
     *
     * @param  array<string, mixed>  $wizard
     */
    public function availableForWizardLine(
        int $inventoryStock,
        int $itemId,
        ?int $variantId,
        array $wizard,
        ?string $excludeLineKey = null,
    ): int {
        if (! $this->hasBookingWindow()) {
            return max(0, $inventoryStock);
        }

        $extra = $this->wizardLineCommitments($wizard, $excludeLineKey)
            ->filter(fn (array $c) => $c['key'] === self::stockKey($itemId, $variantId))
            ->map(fn (array $c) => [
                'start' => $c['start'],
                'end' => $c['end'],
                'quantity' => $c['quantity'],
            ])
            ->values();

        return $this->availableQuantity($inventoryStock, $itemId, $variantId, $extra);
    }

    public static function stockKey(int $itemId, ?int $variantId): string
    {
        return $variantId ? "{$itemId}:{$variantId}" : (string) $itemId;
    }

    /**
     * @return Collection<int, array{key: string, start: Carbon, end: Carbon, quantity: int}>
     */
    public function wizardLineCommitments(array $wizard, ?string $excludeLineKey = null): Collection
    {
        if (! $this->hasBookingWindow()) {
            return collect();
        }

        $out = collect();

        foreach ($wizard['lines'] ?? [] as $row) {
            if (! is_array($row) || empty($row['item_id'])) {
                continue;
            }

            $lineKey = (string) ($row['line_key'] ?? '');

            if ($excludeLineKey !== null && $lineKey === $excludeLineKey) {
                continue;
            }

            $qty = (int) ($row['quantity'] ?? 0);

            if ($qty < 1) {
                continue;
            }

            $itemId = (int) $row['item_id'];
            $variantId = ! empty($row['item_variant_id']) ? (int) $row['item_variant_id'] : null;

            $out->push([
                'key' => self::stockKey($itemId, $variantId),
                'start' => $this->windowStart->copy(),
                'end' => $this->windowEnd->copy(),
                'quantity' => $qty,
            ]);
        }

        return $out;
    }

    /**
     * @param  Collection<int, array{start: Carbon, end: Carbon, quantity: int}>|null  $extraCommitments
     */
    private function availableQuantity(
        int $stock,
        int $itemId,
        ?int $variantId,
        ?Collection $extraCommitments = null,
    ): int {
        if ($stock <= 0) {
            return 0;
        }

        $key = self::stockKey($itemId, $variantId);
        $commitments = $this->commitmentsForKey($key);

        if ($extraCommitments !== null && $extraCommitments->isNotEmpty()) {
            $commitments = $commitments->concat($extraCommitments);
        }

        if ($commitments->isEmpty()) {
            return $stock;
        }

        $maxConcurrent = $this->maxConcurrentInWindow($commitments);

        return max(0, $stock - $maxConcurrent);
    }

    /**
     * @param  Collection<int, array{start: Carbon, end: Carbon, quantity: int}>  $commitments
     */
    private function maxConcurrentInWindow(Collection $commitments): int
    {
        $buffer = self::BUFFER_MINUTES;
        $events = [];

        foreach ($commitments as $commitment) {
            if (! $this->intervalOverlapsWindow($commitment['start'], $commitment['end'])) {
                continue;
            }

            $blockedStart = $commitment['start']->copy()->subMinutes($buffer);
            $blockedEnd = $commitment['end']->copy()->addMinutes($buffer);

            $events[] = ['t' => $blockedStart->timestamp, 'delta' => $commitment['quantity']];
            $events[] = ['t' => $blockedEnd->timestamp, 'delta' => -$commitment['quantity']];
        }

        if ($events === []) {
            return 0;
        }

        usort($events, function (array $a, array $b): int {
            if ($a['t'] !== $b['t']) {
                return $a['t'] <=> $b['t'];
            }

            return $a['delta'] <=> $b['delta'];
        });

        $current = 0;
        $max = 0;

        foreach ($events as $event) {
            $current += $event['delta'];
            $max = max($max, $current);
        }

        return $max;
    }

    private function intervalOverlapsWindow(Carbon $start, Carbon $end): bool
    {
        return self::bufferedIntervalOverlapsWindow(
            $start,
            $end,
            $this->windowStart,
            $this->windowEnd,
            self::BUFFER_MINUTES,
        );
    }

    private static function bufferedIntervalOverlapsWindow(
        Carbon $start,
        Carbon $end,
        Carbon $windowStart,
        Carbon $windowEnd,
        int $bufferMinutes,
    ): bool {
        $blockedStart = $start->copy()->subMinutes($bufferMinutes);
        $blockedEnd = $end->copy()->addMinutes($bufferMinutes);

        return $blockedStart->lt($windowEnd) && $windowStart->lt($blockedEnd);
    }

    /**
     * @return Collection<int, array{start: Carbon, end: Carbon, quantity: int}>
     */
    private function commitmentsForKey(string $key): Collection
    {
        return $this->commitmentsByKey->get($key, collect());
    }

    private static function parseWizardTime(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
