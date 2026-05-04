<?php

namespace App\Http\Controllers\Vendor\Concerns;

use App\Models\Coupon;
use App\Models\Items;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'fulfillment_type' => $order->fulfillment_type ?? 'pickup',
            'delivery_address' => $order->delivery_address,
            'pickup_at' => $order->pickup_at?->toIso8601String(),
            'delivery_charge' => (float) ($order->delivery_charge ?? 0),
            'items_count' => $order->items()->count(),
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
            'pickup_at' => 'nullable|date',
            'delivery_charge' => 'nullable|numeric|min:0|max:999999',
        ]);

        $type = $validated['fulfillment_type'];

        if ($type === 'pickup') {
            $addr = trim((string) ($validated['delivery_address'] ?? ''));

            $order->update([
                'fulfillment_type' => 'pickup',
                'delivery_address' => $addr !== '' ? $addr : null,
                'delivery_charge' => 0,
                'pickup_at' => ! empty($validated['pickup_at']) ? $validated['pickup_at'] : null,
            ]);
        } else {
            $order->update([
                'fulfillment_type' => 'delivery',
                'delivery_address' => trim((string) ($validated['delivery_address'] ?? '')),
                'pickup_at' => null,
                'delivery_charge' => round((float) ($validated['delivery_charge'] ?? 0), 2),
            ]);
        }

        $this->recalculateOrderFinancials($order);
        $order->refresh();

        return response()->json([
            'success' => true,
            'message' => __('vendor.fulfillment_saved'),
            'fulfillment_type' => $order->fulfillment_type,
            'delivery_address' => $order->delivery_address,
            'pickup_at' => $order->pickup_at?->toIso8601String(),
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

            $linePriceType = $item->price_type;
            if (! in_array($linePriceType, Items::priceTypeKeys(), true)) {
                $linePriceType = 'per_day';
            }

            $billingUnits = $this->normalizedBillingUnits(
                isset($itemData['billing_units']) ? (float) $itemData['billing_units'] : null,
                $linePriceType
            );

            $existing = OrderItem::where('order_id', $order->id)->where('item_id', $item->id)->first();

            if ($existing) {
                $existing->update([
                    'quantity' => $existing->quantity + (int) $itemData['quantity'],
                    'price_type' => $linePriceType,
                    'billing_units' => Items::priceTypeUsesBillingUnits($linePriceType) ? $billingUnits : null,
                    'start_at' => $order->start_at,
                    'end_at' => $order->end_at,
                    'rent_days' => $rentDays,
                ]);
                $existing->refresh();
                $existing->update(['total_price' => $existing->lineSubtotal()]);
            } else {
                $oi = OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'price' => $item->price,
                    'quantity' => (int) $itemData['quantity'],
                    'price_type' => $linePriceType,
                    'billing_units' => Items::priceTypeUsesBillingUnits($linePriceType) ? $billingUnits : null,
                    'start_at' => $order->start_at,
                    'end_at' => $order->end_at,
                    'rent_days' => $rentDays,
                    'total_price' => 0,
                ]);
                $oi->refresh();
                $oi->update(['total_price' => $oi->lineSubtotal()]);
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
            'billing_units' => 'nullable|numeric|min:0.01|max:999999',
        ]);

        $orderItem = OrderItem::where('order_id', $order->id)
            ->where('item_id', $itemId)
            ->first();

        if (! $orderItem) {
            return response()->json(['success' => false, 'message' => 'Item not found on this order'], 404);
        }

        $orderItem->load('item');
        $nextPriceType = $orderItem->item?->price_type ?? $orderItem->price_type;
        if (! in_array($nextPriceType, Items::priceTypeKeys(), true)) {
            $nextPriceType = 'per_day';
        }

        $updates = [
            'quantity' => $request->quantity,
            'price_type' => $nextPriceType,
        ];

        if ($nextPriceType === 'fixed') {
            $updates['billing_units'] = null;
        } elseif ($request->exists('billing_units') && $request->input('billing_units') !== null && $request->input('billing_units') !== '') {
            $updates['billing_units'] = $this->normalizedBillingUnits((float) $request->billing_units, $nextPriceType);
        }

        $orderItem->update($updates);
        $orderItem->refresh();
        $orderItem->update(['total_price' => $orderItem->lineSubtotal()]);

        $this->recalculateOrderFinancials($order);
        $order->refresh();
        $orderItem->load('item');

        return response()->json([
            'success' => true,
            'message' => 'Item quantity updated successfully!',
            'item' => [
                'item_id' => $orderItem->item_id,
                'quantity' => $orderItem->quantity,
                'price' => $orderItem->item?->price ?? $orderItem->price,
                'price_type' => $nextPriceType,
                'billing_units' => (float) ($orderItem->billing_units ?? 1),
                'line_total' => $orderItem->lineSubtotal(),
            ],
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

        $order->update([
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
        ]);

        $this->recalculateOrderFinancials($order);
        $order->refresh();

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

        $order->update([
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'coupon_discount' => $discountAmount,
        ]);

        $this->recalculateOrderFinancials($order);
        $order->refresh();

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

        $order->update([
            'coupon_id' => null,
            'coupon_code' => null,
            'coupon_discount' => 0,
        ]);

        $this->recalculateOrderFinancials($order);
        $order->refresh();

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
        });

        $order->refresh();

        return response()->json([
            'success' => true,
            'message' => __('vendor.extra_charge_added'),
            'order' => $this->orderJsonPayload($order),
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
            $oi->update(['total_price' => $oi->lineSubtotal()]);
        }

        $this->recalculateOrderFinancials($order);
        $order->refresh();

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
}
