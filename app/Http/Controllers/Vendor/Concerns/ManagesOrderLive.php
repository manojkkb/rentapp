<?php

namespace App\Http\Controllers\Vendor\Concerns;

use App\Models\Coupon;
use App\Models\Items;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\OrderActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait ManagesOrderLive
{
    public function orderJsonPayload(Order $order): array
    {
        $order->refresh()->load('items');
        $detail = $order->payment_detail ?? [];
        if (! is_array($detail)) {
            $detail = [];
        }

        $detail = array_map(function ($row) {
            if (! is_array($row)) {
                return $row;
            }
            if (! isset($row['entry_kind'])) {
                $row['entry_kind'] = 'payment';
            }

            return $row;
        }, $detail);

        $rental = $this->rentalStatusPayload($order);
        $paymentSummary = $order->paymentSummary();

        return [
            'sub_total' => (float) $order->sub_total,
            'tax_total' => (float) $order->tax_total,
            'discount_amount' => (float) ($order->discount_amount ?? 0),
            'coupon_discount' => (float) ($order->coupon_discount ?? 0),
            'discount_total' => (float) $order->discount_total,
            'grand_total' => (float) $order->grand_total,
            'extra_charges_total' => (float) ($order->extra_charges_total ?? 0),
            'extra_charges_lines' => array_values(is_array($order->extra_charges_lines) ? $order->extra_charges_lines : []),
            'security_deposit' => (float) ($order->security_deposit ?? 0),
            'security_deposit_type' => $order->security_deposit_type ?? 'none',
            'security_deposit_value' => $order->security_deposit_value,
            'paid_amount' => (float) ($order->paid_amount ?? 0),
            'payment_detail' => array_values($detail),
            'payment_summary' => $paymentSummary,
            'fulfillment_type' => $order->fulfillment_type ?? 'pickup',
            'delivery_address' => $order->delivery_address,
            'pickup_at' => $order->pickup_at?->toIso8601String(),
            'delivery_at' => $order->delivery_at?->toIso8601String(),
            'delivery_charge' => (float) ($order->delivery_charge ?? 0),
            'items_count' => $order->items()->count(),
            'status' => $rental['status'],
            'delivered_at' => $rental['delivered_at'],
            'delivered_at_display' => $rental['delivered_at_display'],
            'returned_at' => $rental['returned_at'],
            'returned_at_display' => $rental['returned_at_display'],
            'delivered_units' => $rental['delivered_units'],
            'returned_units' => $rental['returned_units'],
            'total_units' => $rental['total_units'],
        ];
    }

    /**
     * @return array{delivered_units: int, returned_units: int, total_units: int}
     */
    protected function orderRentalUnitsStats(Order $order): array
    {
        $order->loadMissing('items');

        $deliveredUnits = 0;
        $returnedUnits = 0;
        $totalUnits = 0;

        foreach ($order->items as $line) {
            $qty = max(1, (int) $line->quantity);
            $totalUnits += $qty;
            if ($line->delivered_at) {
                $deliveredUnits += $qty;
            }
            $returnedUnits += min($qty, max(0, (int) ($line->returned_qty ?? 0)));
        }

        return [
            'delivered_units' => $deliveredUnits,
            'returned_units' => $returnedUnits,
            'total_units' => $totalUnits,
        ];
    }

    /**
     * @return array{status: string, delivered_at: string|null, delivered_at_display: string|null, returned_at: string|null, returned_at_display: string|null, delivered_units: int, returned_units: int, total_units: int}
     */
    protected function rentalStatusPayload(Order $order): array
    {
        $tz = config('app.timezone');
        $format = static function ($dt) use ($tz) {
            return $dt ? $dt->copy()->timezone($tz)->format('M j, Y g:i A') : null;
        };

        $unitStats = $this->orderRentalUnitsStats($order);

        return [
            'status' => (string) $order->status,
            'delivered_at' => $order->delivered_at?->toIso8601String(),
            'delivered_at_display' => $format($order->delivered_at),
            'returned_at' => $order->returned_at?->toIso8601String(),
            'returned_at_display' => $format($order->returned_at),
            'delivered_units' => $unitStats['delivered_units'],
            'returned_units' => $unitStats['returned_units'],
            'total_units' => $unitStats['total_units'],
        ];
    }

    /**
     * Net amounts allocated to order vs security deposit from payment rows (payments minus refunds).
     *
     * @return array{order: float, deposit: float}
     */
    protected function paymentBucketsFromDetail(array $detail): array
    {
        $orderNet = 0.0;
        $depositNet = 0.0;

        foreach ($detail as $row) {
            if (! is_array($row)) {
                continue;
            }
            $amt = round((float) ($row['amount'] ?? 0), 2);
            $kind = $row['entry_kind'] ?? 'payment';
            $sign = $kind === 'refund' ? -1.0 : 1.0;
            $for = $row['payment_for'] ?? 'order_amount';
            if ($for === 'security_deposit') {
                $depositNet += $sign * $amt;
            } else {
                $orderNet += $sign * $amt;
            }
        }

        return [
            'order' => round($orderNet, 2),
            'deposit' => round($depositNet, 2),
        ];
    }

    protected function ensureOrderEditable(Order $order): void
    {
        if ($order->isLockedForEditing()) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                response()->json(['success' => false, 'message' => __('vendor.order_edit_not_allowed_locked')], 422)
            );
        }
    }

    public function updateOrderFulfillment(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $this->ensureOrderEditable($order);

        $validated = $request->validate([
            'fulfillment_type' => 'required|in:pickup,delivery',
            'delivery_address' => [
                \Illuminate\Validation\Rule::requiredIf($request->input('fulfillment_type') === 'delivery'),
                'nullable',
                'string',
                'max:5000',
            ],
            'pickup_at' => [
                \Illuminate\Validation\Rule::requiredIf($request->input('fulfillment_type') === 'pickup'),
                'nullable',
                'date',
            ],
            'delivery_at' => 'nullable|date',
            'delivery_charge' => 'nullable|numeric|min:0|max:999999',
        ]);

        $type = $validated['fulfillment_type'];
        $oldFulfillment = [
            'fulfillment_type' => $order->fulfillment_type,
            'pickup_at' => $order->pickup_at?->toIso8601String(),
            'delivery_at' => $order->delivery_at?->toIso8601String(),
            'delivery_address' => $order->delivery_address,
        ];

        if ($type === 'pickup') {
            $addr = trim((string) ($validated['delivery_address'] ?? ''));

            $order->update([
                'fulfillment_type' => 'pickup',
                'delivery_address' => $addr !== '' ? $addr : null,
                'delivery_charge' => 0,
                'pickup_at' => ! empty($validated['pickup_at']) ? $validated['pickup_at'] : null,
                'delivery_at' => null,
            ]);
        } else {
            $order->update([
                'fulfillment_type' => 'delivery',
                'delivery_address' => trim((string) ($validated['delivery_address'] ?? '')),
                'pickup_at' => null,
                'delivery_at' => ! empty($validated['delivery_at']) ? $validated['delivery_at'] : null,
                'delivery_charge' => round((float) ($validated['delivery_charge'] ?? 0), 2),
            ]);
        }

        $this->recalculateOrderFinancials($order);
        $order->refresh();
        OrderActivityLogger::logFulfillmentUpdated($order, $oldFulfillment);

        return response()->json([
            'success' => true,
            'message' => __('vendor.fulfillment_saved'),
            'fulfillment_type' => $order->fulfillment_type,
            'delivery_address' => $order->delivery_address,
            'pickup_at' => $order->pickup_at?->toIso8601String(),
            'delivery_at' => $order->delivery_at?->toIso8601String(),
            'delivery_charge' => $order->delivery_charge,
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function addOrderLine(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $this->ensureOrderEditable($order);

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.billing_units' => 'nullable|numeric|min:0.01|max:999999',
        ]);

        $rentDays = $this->orderRentDays($order);
        $addedCount = 0;

        foreach ($request->items as $itemData) {
            $item = Items::where('id', $itemData['item_id'])
                ->where('vendor_id', $vendor->id)
                ->where('is_active', true)
                ->where('is_available', true)
                ->first();

            if (! $item) {
                continue;
            }

            $lineRentalPeriod = $item->rental_period;
            if (! in_array($lineRentalPeriod, Items::rentalPeriodKeys(), true)) {
                $lineRentalPeriod = 'per_day';
            }

            $billingUnits = $this->normalizedBillingUnits(
                isset($itemData['billing_units']) ? (float) $itemData['billing_units'] : null,
                $lineRentalPeriod
            );

            $existing = OrderItem::where('order_id', $order->id)->where('item_id', $item->id)->first();

            if ($existing) {
                $oldLine = [
                    'quantity' => (int) $existing->quantity,
                    'billing_units' => $existing->billing_units,
                    'item_name' => $existing->item_name,
                ];
                $existing->update([
                    'quantity' => $existing->quantity + (int) $itemData['quantity'],
                    'rental_period' => $lineRentalPeriod,
                    'billing_units' => Items::rentalPeriodUsesBillingUnits($lineRentalPeriod) ? $billingUnits : null,
                    'start_at' => $order->start_at,
                    'end_at' => $order->end_at,
                    'rent_days' => $rentDays,
                ]);
                $existing->refresh();
                $existing->refreshLineTotals();
                OrderActivityLogger::logItemUpdated($order, [
                    'order_item_id' => $existing->id,
                    'item_id' => $existing->item_id,
                    'item_name' => $existing->item_name,
                    'quantity' => (int) $existing->quantity,
                    'billing_units' => $existing->billing_units,
                ], $oldLine);
            } else {
                $oi = OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'price' => $item->price,
                    'quantity' => (int) $itemData['quantity'],
                    'rental_period' => $lineRentalPeriod,
                    'billing_units' => Items::rentalPeriodUsesBillingUnits($lineRentalPeriod) ? $billingUnits : null,
                    'start_at' => $order->start_at,
                    'end_at' => $order->end_at,
                    'rent_days' => $rentDays,
                    'total_price' => 0,
                ]);
                $oi->refresh();
                $oi->refreshLineTotals();
                OrderActivityLogger::logItemAdded($order, [
                    'order_item_id' => $oi->id,
                    'item_id' => $oi->item_id,
                    'item_name' => $oi->item_name,
                    'quantity' => (int) $oi->quantity,
                ]);
            }

            $addedCount++;
        }

        if ($addedCount === 0) {
            return response()->json(['success' => false, 'message' => 'No valid items were added'], 422);
        }

        $this->recalculateOrderFinancials($order);
        $order->refresh();

        return response()->json([
            'success' => true,
            'message' => $addedCount === 1 ? __('vendor.item_added') : "{$addedCount} items added successfully!",
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function updateOrderLine(Request $request, Order $order, $itemId)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $this->ensureOrderEditable($order);

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'rental_period' => ['required', 'string', Rule::in(Items::rentalPeriodKeys())],
            'billing_units' => 'nullable|numeric|min:0.01|max:999999',
        ]);

        $orderItem = OrderItem::where('order_id', $order->id)
            ->where('item_id', $itemId)
            ->first();

        if (! $orderItem) {
            return response()->json(['success' => false, 'message' => 'Item not found on this order'], 404);
        }

        $orderItem->load('item');

        $nextRentalPeriod = (string) $request->input('rental_period');
        if (! in_array($nextRentalPeriod, Items::rentalPeriodKeys(), true)) {
            $nextRentalPeriod = $orderItem->rental_period ?? 'per_day';
        }

        $price = round(max(0, (float) $request->input('price')), 2);

        $oldLine = [
            'quantity' => (int) $orderItem->quantity,
            'billing_units' => $orderItem->billing_units,
            'price' => (float) $orderItem->price,
            'rental_period' => $orderItem->rental_period,
            'item_name' => $orderItem->item_name,
        ];

        $updates = [
            'quantity' => $request->quantity,
            'rental_period' => $nextRentalPeriod,
            'price' => $price,
        ];

        if (! Items::rentalPeriodUsesBillingUnits($nextRentalPeriod)) {
            $updates['billing_units'] = null;
        } else {
            if ($request->input('billing_units') === null || $request->input('billing_units') === '') {
                throw ValidationException::withMessages([
                    'billing_units' => [__('vendor.order_wizard_billing_units_required')],
                ]);
            }
            $updates['billing_units'] = $this->normalizedBillingUnits((float) $request->billing_units, $nextRentalPeriod);
        }

        $orderItem->update($updates);
        $orderItem->refresh();
        $orderItem->refreshLineTotals();

        $this->recalculateOrderFinancials($order);
        $order->refresh();
        $orderItem->load('item');
        OrderActivityLogger::logItemUpdated($order, [
            'order_item_id' => $orderItem->id,
            'item_id' => $orderItem->item_id,
            'item_name' => $orderItem->item_name,
            'quantity' => (int) $orderItem->quantity,
            'billing_units' => $orderItem->billing_units,
            'price' => (float) $orderItem->price,
            'rental_period' => $orderItem->rental_period,
        ], $oldLine);

        return response()->json([
            'success' => true,
            'message' => __('vendor.item_updated'),
            'item' => [
                'item_id' => $orderItem->item_id,
                'quantity' => $orderItem->quantity,
                'price' => (float) $orderItem->price,
                'rental_period' => $orderItem->rental_period,
                'billing_units' => (float) ($orderItem->billing_units ?? 1),
                'line_total' => $orderItem->lineSubtotal(),
            ],
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function changeOrderLineVariant(Request $request, Order $order, OrderItem $orderItem)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        if ($orderItem->order_id !== $order->id) {
            return response()->json(['success' => false, 'message' => 'Item not found on this order'], 404);
        }

        $this->ensureOrderEditable($order);

        $request->validate([
            'item_variant_id' => ['required', 'integer'],
        ]);

        $newVariantId = (int) $request->input('item_variant_id');

        $orderItem->load([
            'item' => fn ($q) => $q->with(['variantAttributes', 'variants']),
        ]);

        $item = $orderItem->item;
        if (! $item || ! $item->usesVariants()) {
            throw ValidationException::withMessages([
                'item_variant_id' => [__('vendor.order_wizard_variant_invalid')],
            ]);
        }

        if ((int) $orderItem->item_variant_id === $newVariantId) {
            return response()->json([
                'success' => true,
                'message' => __('vendor.item_updated'),
                'order' => $this->orderJsonPayload($order),
            ]);
        }

        $variant = $item->variants->firstWhere('id', $newVariantId);
        if (! $variant || ! $variant->is_active || ! $variant->is_available) {
            throw ValidationException::withMessages([
                'item_variant_id' => [__('vendor.order_wizard_variant_invalid')],
            ]);
        }

        $qtyNeeded = (int) $orderItem->quantity;

        if ($variant->manage_stock) {
            $committedElsewhere = (int) OrderItem::query()
                ->where('order_id', $order->id)
                ->where('item_id', $item->id)
                ->where('item_variant_id', $newVariantId)
                ->where('id', '!=', $orderItem->id)
                ->sum('quantity');

            $available = max(0, (int) $variant->stock - $committedElsewhere);
            if ($available < $qtyNeeded) {
                throw ValidationException::withMessages([
                    'item_variant_id' => [__('vendor.order_wizard_variant_insufficient_stock', [
                        'label' => $variant->displayLabel($item->variantAttributes),
                    ])],
                ]);
            }
        }

        $oldLine = [
            'order_item_id' => $orderItem->id,
            'item_id' => $orderItem->item_id,
            'item_name' => $orderItem->item_name,
            'item_variant_id' => $orderItem->item_variant_id,
            'variant_label' => $orderItem->variant_label,
            'price' => (float) $orderItem->price,
            'quantity' => (int) $orderItem->quantity,
        ];

        $variantLabel = $variant->displayLabel($item->variantAttributes);
        $itemName = $item->name;
        if ($variantLabel) {
            $itemName .= ' ('.$variantLabel.')';
        }
        $unitPrice = round((float) $variant->price, 2);

        $duplicate = OrderItem::query()
            ->where('order_id', $order->id)
            ->where('item_id', $item->id)
            ->where('item_variant_id', $newVariantId)
            ->where('id', '!=', $orderItem->id)
            ->first();

        if ($duplicate) {
            $prevQty = (int) $duplicate->quantity;
            $duplicate->update([
                'quantity' => $prevQty + $qtyNeeded,
                'price' => $unitPrice,
            ]);
            $duplicate->refresh();
            $duplicate->refreshLineTotals();
            OrderActivityLogger::logItemUpdated($order, [
                'order_item_id' => $duplicate->id,
                'item_id' => $duplicate->item_id,
                'item_name' => $duplicate->item_name,
                'quantity' => (int) $duplicate->quantity,
                'item_variant_id' => $duplicate->item_variant_id,
                'variant_label' => $duplicate->variant_label,
            ], [
                'quantity' => $prevQty,
                'item_name' => $duplicate->item_name,
            ]);
            OrderActivityLogger::logItemRemoved($order, [
                'order_item_id' => $orderItem->id,
                'item_id' => $orderItem->item_id,
                'item_name' => $orderItem->item_name,
                'quantity' => $qtyNeeded,
            ]);
            $orderItem->delete();
        } else {
            $orderItem->update([
                'item_variant_id' => $newVariantId,
                'variant_label' => $variantLabel,
                'item_name' => $itemName,
                'price' => $unitPrice,
            ]);
            $orderItem->refresh();
            $orderItem->refreshLineTotals();
            OrderActivityLogger::logItemUpdated($order, [
                'order_item_id' => $orderItem->id,
                'item_id' => $orderItem->item_id,
                'item_name' => $orderItem->item_name,
                'quantity' => (int) $orderItem->quantity,
                'item_variant_id' => $orderItem->item_variant_id,
                'variant_label' => $orderItem->variant_label,
                'price' => (float) $orderItem->price,
            ], $oldLine);
        }

        $this->recalculateOrderFinancials($order);
        $order->refresh();

        return response()->json([
            'success' => true,
            'message' => __('vendor.item_updated'),
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function removeOrderLine(Request $request, Order $order, $itemId)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $this->ensureOrderEditable($order);

        $orderItem = OrderItem::where('order_id', $order->id)
            ->where('item_id', $itemId)
            ->first();

        if (! $orderItem) {
            return response()->json(['success' => false, 'message' => 'Item not found on this order'], 404);
        }

        OrderActivityLogger::logItemRemoved($order, [
            'order_item_id' => $orderItem->id,
            'item_id' => $orderItem->item_id,
            'item_name' => $orderItem->item_name,
            'quantity' => (int) $orderItem->quantity,
        ]);
        $orderItem->delete();

        if ($order->items()->count() === 0) {
            return response()->json(['success' => false, 'message' => __('vendor.order_needs_one_line')], 422);
        }

        $this->recalculateOrderFinancials($order);
        $order->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Item removed successfully!',
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function applyOrderDiscount(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $this->ensureOrderEditable($order);

        $request->validate([
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0',
        ]);

        $discountType = $request->discount_type;
        $discountValue = $request->discount_value;
        $subTotal = (float) $order->sub_total;

        if ($discountType === 'percent') {
            if ($discountValue > 100) {
                return response()->json(['success' => false, 'message' => 'Percentage cannot exceed 100%'], 422);
            }
            $discountAmount = round($subTotal * ($discountValue / 100), 2);
        } else {
            $discountAmount = round((float) $discountValue, 2);
            if ($discountAmount > $subTotal) {
                return response()->json(['success' => false, 'message' => 'Discount cannot exceed subtotal'], 422);
            }
        }

        $oldDiscount = [
            'discount_type' => $order->discount_type,
            'discount_value' => $order->discount_value,
            'discount_amount' => $order->discount_amount,
        ];

        $order->update([
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
        ]);

        $this->recalculateOrderFinancials($order);
        $order->refresh();
        OrderActivityLogger::logDiscountApplied($order, $oldDiscount);

        return response()->json([
            'success' => true,
            'message' => 'Discount applied successfully!',
            'discount' => [
                'type' => $discountType,
                'value' => $discountValue,
                'amount' => $discountAmount,
            ],
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function removeOrderDiscount(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $this->ensureOrderEditable($order);

        $order->update([
            'discount_type' => null,
            'discount_value' => null,
            'discount_amount' => 0,
        ]);

        $this->recalculateOrderFinancials($order);
        $order->refresh();
        OrderActivityLogger::logDiscountRemoved($order);

        return response()->json([
            'success' => true,
            'message' => 'Discount removed successfully!',
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function applyOrderCoupon(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $this->ensureOrderEditable($order);

        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        $coupon = Coupon::where('vendor_id', $vendor->id)
            ->where('code', strtoupper(trim($request->coupon_code)))
            ->first();

        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code'], 422);
        }

        if (! $coupon->isValid((float) $order->sub_total)) {
            $message = 'This coupon is not valid';
            if ($coupon->end_date && now()->gt($coupon->end_date)) {
                $message = 'This coupon has expired';
            } elseif ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                $message = 'This coupon has reached its usage limit';
            } elseif ($order->sub_total < $coupon->min_order_amount) {
                $message = 'Minimum order amount is ₹'.number_format($coupon->min_order_amount, 2);
            }

            return response()->json(['success' => false, 'message' => $message], 422);
        }

        $discountAmount = $coupon->calculateDiscount((float) $order->sub_total);
        $oldCoupon = [
            'coupon_code' => $order->coupon_code,
            'coupon_discount' => $order->coupon_discount,
        ];

        $order->update([
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'coupon_discount' => $discountAmount,
        ]);

        $this->recalculateOrderFinancials($order);
        $order->refresh();
        OrderActivityLogger::logCouponApplied($order, $oldCoupon);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'coupon' => [
                'code' => $coupon->code,
                'name' => $coupon->name,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount_amount' => $discountAmount,
            ],
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function removeOrderCoupon(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $this->ensureOrderEditable($order);

        $removedCode = $order->coupon_code;

        $order->update([
            'coupon_id' => null,
            'coupon_code' => null,
            'coupon_discount' => 0,
        ]);

        $this->recalculateOrderFinancials($order);
        $order->refresh();
        OrderActivityLogger::logCouponRemoved($order, $removedCode);

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully!',
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function listOrderCoupons(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $coupons = Coupon::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->orderBy('code')
            ->get()
            ->map(function ($coupon) use ($order) {
                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'min_order_amount' => $coupon->min_order_amount,
                    'is_valid' => $coupon->isValid((float) $order->sub_total),
                ];
            });

        return response()->json(['success' => true, 'coupons' => $coupons]);
    }

    public function recordOrderPayment(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_for' => 'required|in:order_amount,security_deposit',
            'method' => 'required|string|max:50',
            'paid_on' => 'nullable|date',
            'entry_kind' => 'nullable|in:payment,refund',
        ]);

        $amount = round((float) $validated['amount'], 2);
        $entryKind = $validated['entry_kind'] ?? 'payment';

        try {
            DB::transaction(function () use ($order, $amount, $validated, $entryKind) {
                $order->refresh();
                $detail = $order->payment_detail ?? [];
                if (! is_array($detail)) {
                    $detail = [];
                }

                $buckets = $this->paymentBucketsFromDetail($detail);
                if ($entryKind === 'refund') {
                    if ($validated['payment_for'] === 'order_amount' && $amount > $buckets['order'] + 0.009) {
                        throw new \InvalidArgumentException(__('vendor.refund_exceeds_order'));
                    }
                    if ($validated['payment_for'] === 'security_deposit' && $amount > $buckets['deposit'] + 0.009) {
                        throw new \InvalidArgumentException(__('vendor.refund_exceeds_deposit'));
                    }
                }

                $detail[] = [
                    'payment_for' => $validated['payment_for'],
                    'method' => $validated['method'],
                    'amount' => $amount,
                    'paid_on' => $validated['paid_on'] ?? null,
                    'recorded_at' => now()->toIso8601String(),
                    'entry_kind' => $entryKind,
                ];

                $order->payment_detail = $detail;
                if ($entryKind === 'refund') {
                    $newPaid = round((float) $order->paid_amount - $amount, 2);
                    if ($newPaid < -0.009) {
                        throw new \InvalidArgumentException(__('vendor.refund_invalid'));
                    }
                    $order->paid_amount = max(0.0, $newPaid);
                } else {
                    $order->paid_amount = round((float) $order->paid_amount + $amount, 2);
                }
                $order->save();
                $paymentRow = $detail[array_key_last($detail)] ?? null;
                if (is_array($paymentRow)) {
                    OrderActivityLogger::logPayment($order, $paymentRow);
                }
            });

            $order->refresh();

            return response()->json([
                'success' => true,
                'message' => $entryKind === 'refund'
                    ? __('vendor.refund_recorded')
                    : __('vendor.payment_recorded'),
                'order' => $this->orderJsonPayload($order),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => 'Could not record payment.'], 500);
        }
    }

    public function removeOrderPayment(Request $request, Order $order, int $paymentIndex)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $order->refresh();
        $detail = $order->payment_detail ?? [];
        if (! is_array($detail)) {
            $detail = [];
        }

        if (! array_key_exists($paymentIndex, $detail)) {
            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }

        try {
            DB::transaction(function () use ($order, $paymentIndex) {
                $order->refresh();
                $detail = $order->payment_detail ?? [];
                if (! is_array($detail)) {
                    $detail = [];
                }
                if (! array_key_exists($paymentIndex, $detail)) {
                    throw new \RuntimeException('Payment not found.');
                }

                $removed = $detail[$paymentIndex];
                $amount = round((float) ($removed['amount'] ?? 0), 2);
                $entryKind = $removed['entry_kind'] ?? 'payment';
                OrderActivityLogger::logPaymentRemoved($order, $removed);

                array_splice($detail, $paymentIndex, 1);
                $order->payment_detail = array_values($detail);
                if ($entryKind === 'refund') {
                    $order->paid_amount = round((float) $order->paid_amount + $amount, 2);
                } else {
                    $order->paid_amount = max(0, round((float) $order->paid_amount - $amount, 2));
                }
                $order->save();
            });
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }

        $order->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Payment removed.',
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function addOrderExtraCharge(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:200',
            'amount' => 'required|numeric|min:0.01|max:999999',
        ]);

        $amount = round((float) $validated['amount'], 2);

        DB::transaction(function () use ($order, $validated, $amount) {
            $order->refresh();
            $lines = $order->extra_charges_lines;
            if (! is_array($lines)) {
                $lines = [];
            }
            $lines[] = [
                'label' => $validated['label'],
                'amount' => $amount,
                'recorded_at' => now()->toIso8601String(),
            ];
            $order->extra_charges_lines = array_values($lines);
            $order->extra_charges_total = round(array_sum(array_map(
                fn ($row) => is_array($row) ? (float) ($row['amount'] ?? 0) : 0.0,
                $lines
            )), 2);
            $order->save();
            $this->recalculateOrderFinancials($order);
            $chargeRow = $lines[array_key_last($lines)] ?? null;
            if (is_array($chargeRow)) {
                OrderActivityLogger::logExtraChargeAdded($order, $chargeRow);
            }
        });

        $order->refresh();

        return response()->json([
            'success' => true,
            'message' => __('vendor.extra_charge_added'),
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function removeOrderExtraCharge(Request $request, Order $order, int $lineIndex)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        try {
            DB::transaction(function () use ($order, $lineIndex) {
                $order->refresh();
                $lines = $order->extra_charges_lines;
                if (! is_array($lines)) {
                    $lines = [];
                }
                if (! array_key_exists($lineIndex, $lines)) {
                    throw new \RuntimeException('Extra charge not found.');
                }

                $removedCharge = $lines[$lineIndex];
                if (is_array($removedCharge)) {
                    OrderActivityLogger::logExtraChargeRemoved($order, $removedCharge);
                }

                array_splice($lines, $lineIndex, 1);
                $lines = array_values($lines);
                $order->extra_charges_lines = $lines;
                $order->extra_charges_total = round(array_sum(array_map(
                    fn ($row) => is_array($row) ? (float) ($row['amount'] ?? 0) : 0.0,
                    $lines
                )), 2);
                $order->save();
                $this->recalculateOrderFinancials($order);
            });
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }

        $order->refresh();

        return response()->json([
            'success' => true,
            'message' => __('vendor.extra_charge_removed'),
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function updateOrderRentalStatus(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $this->ensureOrderEditable($order);

        $validated = $request->validate([
            'delivered' => 'nullable|in:mark,clear',
            'returned' => 'nullable|in:mark,clear',
            'order_item_ids' => 'nullable|array|min:1',
            'order_item_ids.*' => 'integer',
            'return_lines' => 'nullable|array|min:1',
            'return_lines.*.order_item_id' => 'required|integer',
            'return_lines.*.quantity' => 'required|integer|min:1',
        ]);

        if (($validated['delivered'] ?? null) === null && ($validated['returned'] ?? null) === null) {
            return response()->json(['success' => false, 'message' => 'No action specified.'], 422);
        }

        DB::transaction(function () use ($order, $validated) {
            $order->refresh();

            if (($validated['delivered'] ?? null) === 'clear') {
                $this->clearOrderItemsDeliveredAndReturned($order);
                OrderActivityLogger::logRentalCleared($order, 'both');
            } elseif (($validated['delivered'] ?? null) === 'mark') {
                $itemIds = $validated['order_item_ids'] ?? null;
                if (is_array($itemIds) && $itemIds !== []) {
                    $this->markSelectedOrderItemsDelivered($order, $itemIds);
                } else {
                    $now = now();
                    if (! $order->delivered_at) {
                        $order->delivered_at = $now;
                    }
                    $order->save();
                    $this->syncOrderItemsRentalFromOrder($order);
                    $order->refresh()->load('items');
                    $unitCount = $this->orderRentalUnitsStats($order)['delivered_units'];
                    OrderActivityLogger::logAllDelivered($order, $unitCount, $now);
                }
            }

            if (($validated['returned'] ?? null) === 'clear') {
                $this->clearOrderItemsReturned($order);
                if (($validated['delivered'] ?? null) !== 'clear') {
                    OrderActivityLogger::logRentalCleared($order, 'return');
                }
            } elseif (($validated['returned'] ?? null) === 'mark') {
                $returnLines = $validated['return_lines'] ?? null;
                if (is_array($returnLines) && $returnLines !== []) {
                    $this->markSelectedOrderItemsReturned($order, $returnLines);
                } else {
                    $this->markAllOrderItemsReturned($order);
                }
            }
        });

        $order->refresh();

        return response()->json([
            'success' => true,
            'message' => __('vendor.rental_status_updated'),
            'rental_status' => $this->rentalStatusPayload($order),
        ]);
    }

    public function applyOrderSecurityDeposit(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $this->ensureOrderEditable($order);

        $validated = $request->validate([
            'security_deposit_type' => 'required|in:none,order_amount,product_security_deposit,fixed_amount',
            'security_deposit_value' => 'nullable|numeric|min:0',
        ]);

        $type = $validated['security_deposit_type'];
        $oldDeposit = [
            'security_deposit_type' => $order->security_deposit_type,
            'security_deposit_value' => $order->security_deposit_value,
        ];

        if ($type === 'none') {
            $order->update([
                'security_deposit_type' => 'none',
                'security_deposit_value' => null,
            ]);
        } else {
            $num = round((float) ($validated['security_deposit_value'] ?? 0), 2);

            if ($num <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a valid value.',
                ], 422);
            }

            if (in_array($type, ['order_amount', 'product_security_deposit'], true) && $num > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Percentage cannot be more than 100.',
                ], 422);
            }

            $order->update([
                'security_deposit_type' => $type,
                'security_deposit_value' => $num,
            ]);
        }

        $this->recalculateOrderFinancials($order);
        $order->refresh();
        OrderActivityLogger::logSecurityDepositUpdated($order, $oldDeposit);

        return response()->json([
            'success' => true,
            'message' => 'Security deposit updated.',
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    public function updateOrderBooking(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $this->ensureOrderEditable($order);

        $request->validate([
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
        ]);

        $start = $request->start_at ? Carbon::parse($request->start_at) : null;
        $end = $request->end_at ? Carbon::parse($request->end_at) : null;
        $oldBooking = [
            'start_at' => $order->start_at?->toIso8601String(),
            'end_at' => $order->end_at?->toIso8601String(),
        ];

        $order->update([
            'start_at' => $start,
            'end_at' => $end,
        ]);

        $rentDays = $this->orderRentDays($order);

        foreach ($order->items as $oi) {
            $oi->update([
                'start_at' => $order->start_at,
                'end_at' => $order->end_at,
                'rent_days' => $rentDays,
            ]);
            $oi->refresh();
            $oi->refreshLineTotals();
        }

        $this->recalculateOrderFinancials($order);
        $order->refresh();
        OrderActivityLogger::logBookingUpdated($order, $oldBooking);

        return response()->json([
            'success' => true,
            'message' => 'Booking dates updated.',
            'order' => $this->orderJsonPayload($order),
        ]);
    }

    protected function orderRentDays(Order $order): int
    {
        if ($order->start_at && $order->end_at) {
            return max(1, (int) ceil($order->start_at->diffInDays($order->end_at)));
        }

        return 1;
    }

    /**
     * @param  list<int>  $orderItemIds
     */
    private function markSelectedOrderItemsDelivered(Order $order, array $orderItemIds): void
    {
        $order->load('items');

        $validIds = $order->items->pluck('id')->map(fn ($id) => (int) $id)->all();
        $selected = array_values(array_intersect(
            array_map('intval', $orderItemIds),
            $validIds
        ));

        if ($selected === []) {
            throw ValidationException::withMessages([
                'order_item_ids' => [__('vendor.deliver_items_required')],
            ]);
        }

        $now = now();

        foreach ($order->items as $line) {
            if (! in_array((int) $line->id, $selected, true)) {
                continue;
            }
            if (! $line->delivered_at) {
                $line->delivered_at = $now;
                $line->save();
                OrderActivityLogger::logItemDelivered($line, $now);
            }
        }

        $this->syncOrderDeliveredTimestampFromItems($order);
    }

    private function syncOrderDeliveredTimestampFromItems(Order $order): void
    {
        $order->load('items');

        if ($order->items->isEmpty()) {
            $order->delivered_at = null;
            $order->save();

            return;
        }

        $allDelivered = $order->items->every(fn (OrderItem $line) => $line->delivered_at !== null);

        if ($allDelivered) {
            if (! $order->delivered_at) {
                $order->delivered_at = now();
            }
        } else {
            $order->delivered_at = null;
            $order->returned_at = null;
            foreach ($order->items as $line) {
                if ($line->returned_at) {
                    $line->returned_at = null;
                    $line->rental_duration_minutes = null;
                    $line->save();
                }
            }
        }

        $order->save();
    }

    /**
     * @param  list<array{order_item_id: int, quantity: int}>  $returnLines
     */
    private function markSelectedOrderItemsReturned(Order $order, array $returnLines): void
    {
        $order->load('items');
        $linesById = $order->items->keyBy('id');
        $now = now();
        $touched = false;

        foreach ($returnLines as $row) {
            $lineId = (int) ($row['order_item_id'] ?? 0);
            $qty = (int) ($row['quantity'] ?? 0);
            if ($lineId < 1 || $qty < 1) {
                continue;
            }

            /** @var OrderItem|null $line */
            $line = $linesById->get($lineId);
            if (! $line) {
                continue;
            }

            if (! $line->delivered_at) {
                throw ValidationException::withMessages([
                    'return_lines' => [__('vendor.return_item_not_delivered', ['name' => $line->item_name ?? __('vendor.item')])],
                ]);
            }

            $maxQty = max(1, (int) $line->quantity);
            $returnedQty = min($maxQty, $qty);
            $line->returned_qty = $returnedQty;

            if ($returnedQty >= $maxQty) {
                if (! $line->returned_at) {
                    $line->returned_at = $now;
                }
                if ($line->delivered_at) {
                    $line->rental_duration_minutes = (int) max(0, round($line->delivered_at->diffInSeconds($line->returned_at) / 60));
                }
            } else {
                $line->returned_at = null;
                $line->rental_duration_minutes = null;
            }

            $line->save();
            OrderActivityLogger::logItemReturned($line, $now);
            $touched = true;
        }

        if (! $touched) {
            throw ValidationException::withMessages([
                'return_lines' => [__('vendor.return_items_required')],
            ]);
        }

        $this->syncOrderReturnedTimestampFromItems($order);
    }

    private function markAllOrderItemsReturned(Order $order): void
    {
        $order->load('items');
        $now = now();

        foreach ($order->items as $line) {
            if (! $line->delivered_at) {
                $line->delivered_at = $now;
            }
            $line->returned_qty = max(1, (int) $line->quantity);
            $line->returned_at = $now;
            if ($line->delivered_at) {
                $line->rental_duration_minutes = (int) max(0, round($line->delivered_at->diffInSeconds($line->returned_at) / 60));
            }
            $line->save();
        }

        if (! $order->delivered_at) {
            $order->delivered_at = $now;
        }
        $order->returned_at = $now;
        $order->save();

        $unitCount = $this->orderRentalUnitsStats($order)['returned_units'];
        OrderActivityLogger::logAllReturned($order, $unitCount, $now);
    }

    private function clearOrderItemsReturned(Order $order): void
    {
        $order->load('items');
        $order->returned_at = null;
        $order->save();

        foreach ($order->items as $line) {
            $line->returned_at = null;
            $line->returned_qty = 0;
            $line->rental_duration_minutes = null;
            $line->save();
        }
    }

    private function clearOrderItemsDeliveredAndReturned(Order $order): void
    {
        $order->delivered_at = null;
        $order->returned_at = null;
        $order->save();

        $order->load('items');

        foreach ($order->items as $line) {
            $line->delivered_at = null;
            $line->returned_at = null;
            $line->returned_qty = 0;
            $line->rental_duration_minutes = null;
            $line->save();
        }
    }

    private function syncOrderReturnedTimestampFromItems(Order $order): void
    {
        $order->load('items');

        if ($order->items->isEmpty()) {
            $order->returned_at = null;
            $order->save();

            return;
        }

        $allReturned = $order->items->every(function (OrderItem $line) {
            $maxQty = max(1, (int) $line->quantity);

            return $line->returned_at !== null || (int) $line->returned_qty >= $maxQty;
        });

        if ($allReturned) {
            if (! $order->returned_at) {
                $order->returned_at = now();
            }
        } else {
            $order->returned_at = null;
        }

        $order->save();
    }

    /**
     * Mirror order-level handout/return timestamps onto every line (same UX as order rental buttons).
     */
    private function syncOrderItemsRentalFromOrder(Order $order): void
    {
        $order->loadMissing('items');

        $delivered = $order->delivered_at;
        $returned = $order->returned_at;

        $duration = null;
        if ($delivered && $returned) {
            $duration = (int) max(0, round($delivered->diffInSeconds($returned) / 60));
        }

        foreach ($order->items as $line) {
            $line->delivered_at = $delivered;
            $line->returned_at = $returned;
            $line->rental_duration_minutes = $duration;
            $line->save();
        }
    }

    /**
     * When completing with settlement confirmed: deliver/return all lines and record pending payments/refunds.
     */
    protected function applyCompletionSettlement(Order $order): void
    {
        $order->refresh()->load('items');
        $checklist = $order->completionChecklist();

        $this->markAllOrderItemsReturned($order);

        $order->refresh();
        $detail = is_array($order->payment_detail) ? $order->payment_detail : [];
        $paidDelta = 0.0;

        if ($checklist['order_due'] > 0.009) {
            $amount = round($checklist['order_due'], 2);
            $row = $this->appendSettlementPaymentEntry($detail, 'order_amount', 'payment', $amount);
            OrderActivityLogger::logPayment($order, $row);
            $paidDelta += $amount;
        }

        if ($checklist['deposit_due'] > 0.009) {
            $amount = round($checklist['deposit_due'], 2);
            $row = $this->appendSettlementPaymentEntry($detail, 'security_deposit', 'payment', $amount);
            OrderActivityLogger::logPayment($order, $row);
            $paidDelta += $amount;
        }

        if ($checklist['order_refund_pending'] > 0.009) {
            $amount = round($checklist['order_refund_pending'], 2);
            $row = $this->appendSettlementPaymentEntry($detail, 'order_amount', 'refund', $amount);
            OrderActivityLogger::logPayment($order, $row);
            $paidDelta -= $amount;
        }

        if ($checklist['deposit_refund_pending'] > 0.009) {
            $amount = round($checklist['deposit_refund_pending'], 2);
            $row = $this->appendSettlementPaymentEntry($detail, 'security_deposit', 'refund', $amount);
            OrderActivityLogger::logPayment($order, $row);
            $paidDelta -= $amount;
        }

        $order->payment_detail = $detail;
        $order->paid_amount = max(0.0, round((float) $order->paid_amount + $paidDelta, 2));
        $order->save();
    }

    /**
     * @param  list<array<string, mixed>>  $detail
     */
    /**
     * @param  list<array<string, mixed>>  $detail
     * @return array<string, mixed>
     */
    private function appendSettlementPaymentEntry(array &$detail, string $paymentFor, string $entryKind, float $amount): array
    {
        if ($amount < 0.01) {
            return [];
        }

        $row = [
            'payment_for' => $paymentFor,
            'method' => 'settlement',
            'amount' => round($amount, 2),
            'paid_on' => now()->toDateString(),
            'recorded_at' => now()->toIso8601String(),
            'entry_kind' => $entryKind,
            'settlement' => true,
        ];
        $detail[] = $row;

        return $row;
    }
}
