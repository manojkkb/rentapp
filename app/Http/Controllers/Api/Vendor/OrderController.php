<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Http\Controllers\Vendor\Concerns\ListsVendorLogistics;
use App\Http\Controllers\Vendor\Concerns\ManagesOrderLive;
use App\Models\Order;
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
        $order->load(['customer', 'items.item', 'coupon']);

        return $this->ok([
            'order' => array_merge($order->only([
                'id', 'order_number', 'event_name', 'status', 'start_at', 'end_at',
                'fulfillment_type', 'delivery_address', 'grand_total', 'paid_amount',
                'internal_notes', 'created_at',
            ]), [
                'customer' => $order->customer,
                'items' => $order->items,
                'cart' => $this->orderJsonPayload($order),
            ]),
        ]);
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

        $order->update(['status' => $validated['status']]);

        return $this->ok(['order' => $this->orderListItem($order->fresh())], 'Status updated.');
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
    private function orderListItem(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'event_name' => $order->event_name,
            'status' => $order->status,
            'grand_total' => (float) $order->grand_total,
            'paid_amount' => (float) ($order->paid_amount ?? 0),
            'start_at' => $order->start_at?->toIso8601String(),
            'end_at' => $order->end_at?->toIso8601String(),
            'customer' => $order->customer ? [
                'id' => $order->customer->id,
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
