<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Http\Controllers\Vendor\Concerns\ListsVendorLogistics;
use App\Http\Controllers\Vendor\Concerns\ManagesOrderLive;
use App\Http\Controllers\Vendor\VendorOrderController as WebVendorOrderController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\OrderActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends ApiController
{
    use ListsVendorLogistics;
    use ManagesOrderLive;
    use ResolvesApiVendor;

    public function index(Request $request): JsonResponse
    {
        $this->requirePermission('orders.view');
        $vendor = $this->vendor();

        $query = Order::query()
            ->where('vendor_id', $vendor->id)
            ->with(['customer:id,name,mobile', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('event_name', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->latest()->paginate($request->integer('per_page', 20));

        return $this->ok([
            'orders' => $orders->getCollection()->map(fn (Order $order) => $this->orderListItem($order)),
            'meta' => $this->paginationMeta($orders),
            'status_counts' => $this->statusCounts($vendor->id),
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $this->requirePermission('orders.view');
        $this->authorizeVendorOrder($order);
        $order->load(['customer', 'items.item:id,photo', 'coupon']);

        return $this->ok(['order' => $this->presentOrderDetailForApi($order)]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requirePermission('orders.create');

        $order = app(WebVendorOrderController::class)->placeOrderFromApiRequest($request, $this->vendor());
        $order->load(['customer', 'items.item:id,photo', 'coupon']);

        return $this->ok(['order' => $this->presentOrderDetailForApi($order)], 'Order created.', 201);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $this->requirePermission('orders.cancel');
        $this->authorizeVendorOrder($order);

        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', Order::STATUSES)],
        ]);

        if ($order->isLockedForEditing()) {
            return $this->fail(__('vendor.order_edit_not_allowed_locked'), 422);
        }

        if (! $order->canTransitionTo($validated['status'])) {
            return $this->fail(__('vendor.order_invalid_status_transition'), 422);
        }

        $oldStatus = (string) $order->status;
        $order->update(['status' => $validated['status']]);
        $order->refresh();

        if ($oldStatus !== (string) $validated['status']) {
            OrderActivityLogger::logStatusChanged($order, $oldStatus, (string) $validated['status']);
        }

        return $this->ok(['order' => $this->orderListItem($order)], 'Status updated.');
    }

    public function deliveries(Request $request): JsonResponse
    {
        $this->requirePermission('deliveries.view');

        $orders = $this->deliveriesPaginator($this->vendor());

        return $this->ok([
            'orders' => $orders->getCollection()->map(fn (Order $order) => $this->orderListItem($order)),
            'meta' => $this->paginationMeta($orders),
        ]);
    }

    public function returns(Request $request): JsonResponse
    {
        $this->requirePermission('returns.view');

        $orders = $this->returnsPaginator($this->vendor());

        return $this->ok([
            'orders' => $orders->getCollection()->map(fn (Order $order) => $this->orderListItem($order)),
            'meta' => $this->paginationMeta($orders),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrderDetailForApi(Order $order): array
    {
        $payment = $order->paymentSummary();
        $securityDeposit = (float) ($order->security_deposit ?? 0);
        $paidAmount = (float) ($order->paid_amount ?? 0);
        $grandTotal = (float) $order->grand_total;
        $totalDue = round($grandTotal + $securityDeposit, 2);
        $balanceDue = max(0, round($totalDue - $paidAmount, 2));

        return [
            'id' => $order->id,
            'uuid' => $order->uuid,
            'order_number' => $order->order_number,
            'event_name' => $order->event_name,
            'status' => $order->status,
            'customer_id' => $order->customer_id,
            'start_at' => $order->start_at?->toIso8601String(),
            'end_at' => $order->end_at?->toIso8601String(),
            'pickup_at' => $order->pickup_at?->toIso8601String(),
            'delivery_at' => $order->delivery_at?->toIso8601String(),
            'delivered_at' => $order->delivered_at?->toIso8601String(),
            'returned_at' => $order->returned_at?->toIso8601String(),
            'is_delivered' => $order->delivered_at !== null,
            'is_returned' => $order->returned_at !== null,
            'fulfillment_type' => $order->fulfillment_type ?? 'pickup',
            'delivery_address' => $order->delivery_address,
            'delivery_charge' => (float) ($order->delivery_charge ?? 0),
            'sub_total' => (float) $order->sub_total,
            'tax_total' => (float) $order->tax_total,
            'discount_type' => $order->discount_type,
            'discount_value' => $order->discount_value !== null ? (float) $order->discount_value : null,
            'discount_amount' => (float) ($order->discount_amount ?? 0),
            'discount_total' => (float) $order->discount_total,
            'coupon_id' => $order->coupon_id,
            'coupon_code' => $order->coupon_code,
            'coupon_discount' => (float) ($order->coupon_discount ?? 0),
            'coupon' => $order->coupon ? [
                'id' => $order->coupon->id,
                'uuid' => $order->coupon->uuid,
                'code' => $order->coupon->code,
                'name' => $order->coupon->name,
            ] : null,
            'security_deposit' => $securityDeposit,
            'security_deposit_type' => $order->security_deposit_type ?? 'none',
            'security_deposit_value' => $order->security_deposit_value !== null ? (float) $order->security_deposit_value : null,
            'token_amount' => (float) ($order->token_amount ?? 0),
            'extra_charges_total' => (float) ($order->extra_charges_total ?? 0),
            'extra_charges_lines' => array_values(is_array($order->extra_charges_lines) ? $order->extra_charges_lines : []),
            'late_fees_total' => (float) ($order->late_fees_total ?? 0),
            'damage_fees_total' => (float) ($order->damage_fees_total ?? 0),
            'lost_fees_total' => (float) ($order->lost_fees_total ?? 0),
            'refunds_total' => (float) ($order->refunds_total ?? 0),
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'total_due' => $totalDue,
            'balance_due' => $balanceDue,
            'order_due' => $payment['order_due'],
            'deposit_due' => $payment['deposit_due'],
            'payment_summary' => $payment,
            'internal_notes' => $order->internal_notes,
            'created_at' => $order->created_at?->toIso8601String(),
            'updated_at' => $order->updated_at?->toIso8601String(),
            'customer' => $order->customer ? [
                'id' => $order->customer->id,
                'uuid' => $order->customer->uuid,
                'name' => $order->customer->name,
                'mobile' => $order->customer->mobile,
                'address' => $order->customer->address,
            ] : null,
            'items' => $order->items->map(fn (OrderItem $line) => $this->presentOrderLineForApi($line))->values()->all(),
            'cart' => $this->orderJsonPayload($order),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presentOrderLineForApi(OrderItem $line): array
    {
        $name = trim((string) ($line->item_name ?: $line->item?->name ?: ''));
        if ($line->variant_label && ! str_contains($name, (string) $line->variant_label)) {
            $name = $name !== '' ? $name.' — '.$line->variant_label : (string) $line->variant_label;
        }

        return [
            'id' => $line->id,
            'item_id' => $line->item_id,
            'item_variant_id' => $line->item_variant_id,
            'name' => $name,
            'variant_label' => $line->variant_label,
            'photo_url' => $line->item?->photo_url,
            'price' => (float) $line->price,
            'quantity' => (int) $line->quantity,
            'billing_units' => (float) ($line->billing_units ?? 1),
            'rental_period' => $line->rental_period,
            'start_at' => $line->start_at?->toIso8601String(),
            'end_at' => $line->end_at?->toIso8601String(),
            'total_price' => (float) $line->total_price,
            'subtotal' => (float) $line->subtotal,
            'final_amount' => (float) $line->final_amount,
            'security_deposit' => (float) ($line->security_deposit ?? 0),
            'item_status' => $line->item_status,
            'delivered_at' => $line->delivered_at?->toIso8601String(),
            'returned_at' => $line->returned_at?->toIso8601String(),
            'returned_qty' => (int) ($line->returned_qty ?? 0),
            'damaged_qty' => (int) ($line->damaged_qty ?? 0),
            'lost_qty' => (int) ($line->lost_qty ?? 0),
            'condition_out' => $line->condition_out,
            'condition_in' => $line->condition_in,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function orderListItem(Order $order): array
    {
        $securityDeposit = (float) ($order->security_deposit ?? 0);
        $paidAmount = (float) ($order->paid_amount ?? 0);
        $grandTotal = (float) $order->grand_total;
        $totalDue = round($grandTotal + $securityDeposit, 2);
        $balanceDue = max(0, round($totalDue - $paidAmount, 2));
        $payment = $order->paymentSummary();

        return [
            'id' => $order->id,
            'uuid' => $order->uuid,
            'order_number' => $order->order_number,
            'event_name' => $order->event_name,
            'status' => $order->status,
            'fulfillment_type' => $order->fulfillment_type ?? 'pickup',
            'delivery_address' => $order->delivery_address,
            'grand_total' => $grandTotal,
            'security_deposit' => $securityDeposit,
            'paid_amount' => $paidAmount,
            'total_due' => $totalDue,
            'balance_due' => $balanceDue,
            'order_due' => $payment['order_due'],
            'deposit_due' => $payment['deposit_due'],
            'start_at' => $order->start_at?->toIso8601String(),
            'end_at' => $order->end_at?->toIso8601String(),
            'pickup_at' => $order->pickup_at?->toIso8601String(),
            'delivery_at' => $order->delivery_at?->toIso8601String(),
            'delivered_at' => $order->delivered_at?->toIso8601String(),
            'returned_at' => $order->returned_at?->toIso8601String(),
            'is_delivered' => $order->delivered_at !== null,
            'is_returned' => $order->returned_at !== null,
            'customer' => $order->customer ? [
                'id' => $order->customer->id,
                'uuid' => $order->customer->uuid,
                'name' => $order->customer->name,
                'mobile' => $order->customer->mobile,
            ] : null,
            'items_count' => $order->relationLoaded('items') ? $order->items->count() : $order->items()->count(),
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function statusCounts(int $vendorId): array
    {
        $rows = Order::query()
            ->where('vendor_id', $vendorId)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = ['all' => (int) Order::query()->where('vendor_id', $vendorId)->count()];
        foreach (Order::STATUSES as $status) {
            $counts[$status] = (int) ($rows[$status] ?? 0);
        }

        return $counts;
    }
}
