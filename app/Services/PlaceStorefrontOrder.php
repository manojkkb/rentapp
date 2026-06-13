<?php

namespace App\Services;

use App\Models\Items;
use App\Models\ItemVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Vendor;
use App\Models\VendorCustomer;
use App\Models\VendorStoreSetting;
use App\Support\StorefrontRentalPricing;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceStorefrontOrder
{
    /**
     * @param  list<array{item_id: int, item_variant_id?: int|null, quantity: int}>  $cartLines
     * @param  array<string, mixed>  $checkout
     */
    public function place(
        Vendor $vendor,
        VendorStoreSetting $store,
        VendorCustomer $customer,
        array $cartLines,
        array $checkout,
    ): Order {
        if ($cartLines === []) {
            throw ValidationException::withMessages(['cart' => [__('vendor.store_cart_empty')]]);
        }

        $startAt = Carbon::parse($checkout['start_at']);
        $endAt = Carbon::parse($checkout['end_at']);
        if ($endAt->lte($startAt)) {
            throw ValidationException::withMessages(['end_at' => [__('vendor.store_rental_end_after_start')]]);
        }

        $fulfillmentType = $checkout['fulfillment_type'] ?? 'pickup';
        $deliveryAddress = trim((string) ($checkout['delivery_address'] ?? ''));

        if ($fulfillmentType === 'delivery' && $deliveryAddress === '') {
            throw ValidationException::withMessages(['delivery_address' => [__('vendor.delivery_address_required')]]);
        }

        if ($fulfillmentType === 'pickup' && ! $store->pickup_enabled) {
            throw ValidationException::withMessages(['fulfillment_type' => [__('vendor.store_pickup_unavailable')]]);
        }

        if ($fulfillmentType === 'delivery' && ! $store->delivery_enabled) {
            throw ValidationException::withMessages(['fulfillment_type' => [__('vendor.store_delivery_unavailable')]]);
        }

        $cartSubtotal = $this->estimateCartSubtotal($vendor, $cartLines, $startAt, $endAt);

        if ($store->min_order_amount && $cartSubtotal < (float) $store->min_order_amount) {
            throw ValidationException::withMessages([
                'cart' => [__('vendor.store_min_order_not_met', ['amount' => number_format((float) $store->min_order_amount, 0)])],
            ]);
        }

        $deliveryCharge = $store->resolveDeliveryCharge($cartSubtotal, $fulfillmentType);
        $checkout['delivery_charge'] = $deliveryCharge;

        $secType = $store->default_security_deposit_type ?? 'none';
        $secValue = $store->default_security_deposit_value;

        return DB::transaction(function () use (
            $vendor,
            $store,
            $customer,
            $cartLines,
            $checkout,
            $startAt,
            $endAt,
            $fulfillmentType,
            $deliveryAddress,
            $secType,
            $secValue,
        ) {
            $attrs = [
                'order_number' => 'ORD-'.strtoupper(uniqid()),
                'vendor_id' => $vendor->id,
                'customer_id' => $customer->id,
                'event_name' => __('vendor.store_online_order'),
                'start_at' => $startAt,
                'end_at' => $endAt,
                'fulfillment_type' => $fulfillmentType,
                'delivery_address' => $fulfillmentType === 'delivery' ? $deliveryAddress : null,
                'pickup_at' => $fulfillmentType === 'pickup'
                    ? Carbon::parse($checkout['pickup_at'] ?? $checkout['start_at'])
                    : null,
                'delivery_at' => $fulfillmentType === 'delivery'
                    ? Carbon::parse($checkout['delivery_at'] ?? $checkout['start_at'])
                    : null,
                'delivery_charge' => $fulfillmentType === 'delivery' ? round((float) ($checkout['delivery_charge'] ?? 0), 2) : 0,
                'security_deposit_type' => $secType,
                'security_deposit_value' => $secType === 'none' ? null : $secValue,
                'status' => 'pending',
                'payment_detail' => [],
                'paid_amount' => 0,
                'sub_total' => 0,
                'grand_total' => 0,
                'tax_total' => 0,
                'coupon_discount' => 0,
                'discount_total' => 0,
                'extra_charges_total' => 0,
                'extra_charges_lines' => [],
            ];

            $order = Order::create($attrs);
            $this->persistLines($vendor, $order, $cartLines, $startAt, $endAt);
            $this->recalculateFinancials($order);

            if ($deliveryAddress !== '' && $customer->address !== $deliveryAddress) {
                $customer->update(['address' => $deliveryAddress]);
            }

            return $order->fresh(['items']);
        });
    }

    /**
     * @param  list<array{item_id: int, item_variant_id?: int|null, quantity: int}>  $lines
     */
    private function persistLines(Vendor $vendor, Order $order, array $lines, Carbon $startAt, Carbon $endAt): void
    {
        $rentDays = max(1, (int) ceil($startAt->diffInDays($endAt)));
        $billingUnits = StorefrontRentalPricing::defaultBillingUnitsByPriceType($startAt, $endAt);

        foreach ($lines as $row) {
            $item = Items::query()
                ->where('vendor_id', $vendor->id)
                ->where('id', $row['item_id'])
                ->active()
                ->available()
                ->with(['variantAttributes', 'variants'])
                ->first();

            if (! $item) {
                continue;
            }

            $variantId = isset($row['item_variant_id']) ? (int) $row['item_variant_id'] : null;
            $variant = $variantId ? $item->variants->firstWhere('id', $variantId) : null;
            $rentalPeriod = $item->rental_period ?? 'per_day';
            $unitPrice = $variant ? (float) $variant->price : (float) $item->price;
            $variantLabel = $variant ? $variant->displayLabel($item->variantAttributes) : null;
            $itemName = $variantLabel ? $item->name.' ('.$variantLabel.')' : $item->name;

            $units = Items::rentalPeriodUsesBillingUnits($rentalPeriod)
                ? ($billingUnits[$rentalPeriod] ?? 1.0)
                : null;

            OrderItem::create([
                'order_id' => $order->id,
                'item_id' => $item->id,
                'item_variant_id' => $variantId,
                'item_name' => $itemName,
                'variant_label' => $variantLabel,
                'price' => $unitPrice,
                'quantity' => (int) $row['quantity'],
                'rental_period' => $rentalPeriod,
                'billing_units' => $units,
                'start_at' => $order->start_at,
                'end_at' => $order->end_at,
                'rent_days' => $rentDays,
                'total_price' => 0,
            ])->refresh()->refreshLineTotals();
        }

        $order->refresh()->load('items');
        if ($order->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => [__('vendor.store_cart_invalid_items')]]);
        }
    }

    private function recalculateFinancials(Order $order): void
    {
        $order->load('items');
        $subTotal = round($order->items->sum(fn (OrderItem $i) => $i->lineSubtotal()), 2);
        $deliveryCharge = ($order->fulfillment_type ?? '') === 'delivery'
            ? round((float) ($order->delivery_charge ?? 0), 2)
            : 0.0;
        $grandTotal = round($subTotal + $deliveryCharge, 2);
        $securityDeposit = $this->computeSecurityDeposit($order, $subTotal, $grandTotal);

        $order->update([
            'sub_total' => $subTotal,
            'tax_total' => 0,
            'discount_total' => 0,
            'grand_total' => $grandTotal,
            'security_deposit' => $securityDeposit,
        ]);
    }

    private function computeSecurityDeposit(Order $order, float $subTotal, float $grandTotal): float
    {
        $type = $order->security_deposit_type ?? 'none';
        $value = (float) ($order->security_deposit_value ?? 0);
        if ($type === 'none' || $value <= 0) {
            return 0.0;
        }
        if ($type === 'fixed_amount') {
            return round($value, 2);
        }
        if ($type === 'order_amount') {
            return round($grandTotal * $value / 100, 2);
        }
        if ($type === 'product_security_deposit') {
            return round($subTotal * $value / 100, 2);
        }

        return 0.0;
    }

    /**
     * @param  list<array{item_id: int, item_variant_id?: int|null, quantity: int}>  $lines
     */
    private function estimateCartSubtotal(Vendor $vendor, array $lines, Carbon $startAt, Carbon $endAt): float
    {
        $billingUnits = StorefrontRentalPricing::defaultBillingUnitsByPriceType($startAt, $endAt);
        $subtotal = 0.0;

        foreach ($lines as $row) {
            $item = Items::query()
                ->where('vendor_id', $vendor->id)
                ->where('id', $row['item_id'])
                ->active()
                ->available()
                ->with(['variants'])
                ->first();

            if (! $item) {
                continue;
            }

            $variantId = isset($row['item_variant_id']) ? (int) $row['item_variant_id'] : null;
            $variant = $variantId ? $item->variants->firstWhere('id', $variantId) : null;
            $rentalPeriod = $item->rental_period ?? 'per_day';
            $unitPrice = $variant ? (float) $variant->price : (float) $item->price;
            $units = Items::rentalPeriodUsesBillingUnits($rentalPeriod)
                ? ($billingUnits[$rentalPeriod] ?? 1.0)
                : null;
            $qty = max(1, (int) ($row['quantity'] ?? 1));

            $subtotal += StorefrontRentalPricing::lineSubtotal($unitPrice, $qty, $rentalPeriod, $units);
        }

        return round($subtotal, 2);
    }
}
