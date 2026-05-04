<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\Concerns\ManagesOrderLive;
use App\Models\Category;
use App\Models\Items;
use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VendorOrderController extends Controller
{
    use ManagesOrderLive;

    private const TAX_RATE = 0.10;
    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        $query = Order::where('vendor_id', $vendor->id)
            ->with(['customer', 'items']);
        
        // Filter by status if provided
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Search by order number or customer name
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('order_number', 'like', "%{$searchTerm}%")
                  ->orWhereHas('customer', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }
        
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Get status counts for filter badges
        $statusCounts = [
            'all' => Order::where('vendor_id', $vendor->id)->count(),
            'pending' => Order::where('vendor_id', $vendor->id)->where('status', 'pending')->count(),
            'confirmed' => Order::where('vendor_id', $vendor->id)->where('status', 'confirmed')->count(),
            'ongoing' => Order::where('vendor_id', $vendor->id)->where('status', 'ongoing')->count(),
            'completed' => Order::where('vendor_id', $vendor->id)->where('status', 'completed')->count(),
            'cancelled' => Order::where('vendor_id', $vendor->id)->where('status', 'cancelled')->count(),
        ];
        
        return view('vendor.orders.index', compact('orders', 'statusCounts'));
    }
    
    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        $order->load(['customer', 'items.item.category', 'coupon']);

        $catalogItems = Items::query()
            ->where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->where('is_available', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        $orderBillingLabels = collect(Items::priceTypeKeys())
            ->filter(fn ($k) => Items::priceTypeUsesBillingUnits($k))
            ->mapWithKeys(fn ($k) => [$k => Items::billingUnitsFieldLabel($k)])
            ->all();

        $availableItems = $catalogItems;
        $cartBillingUnitsLabels = $orderBillingLabels;

        $categories = Category::query()
            ->whereIn('id', $catalogItems->pluck('category_id')->unique()->filter())
            ->orderBy('name')
            ->get();

        $orderCartJson = $this->orderJsonPayload($order);

        return view('vendor.orders.show', compact('order', 'catalogItems', 'orderBillingLabels', 'availableItems', 'cartBillingUnitsLabels', 'categories', 'orderCartJson'));
    }
    

    /**
     * Printable order sheet (opens in browser; use ?autoprint=1 to trigger print dialog).
     */
    public function printOrder(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            abort(403);
        }

        $order->load(['customer', 'items.item.category', 'vendor']);

        return view('vendor.orders.print', [
            'order' => $order,
            'autoprint' => $request->boolean('autoprint'),
            'forPdf' => false,
        ]);
    }

    /**
     * Download invoice file for an order.
     */
    public function downloadInvoice(Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            abort(403);
        }

        $order->load(['customer', 'items.item.category', 'vendor']);

        $safeOrderNumber = preg_replace('/[^A-Za-z0-9_\-]/', '-', (string) ($order->order_number ?? 'invoice-'.$order->id));
        $filename = 'invoice-'.$safeOrderNumber.'.pdf';

        $pdf = Pdf::loadView('vendor.orders.print', [
            'order' => $order,
            'autoprint' => false,
            'forPdf' => true,
        ])->setPaper('a4')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        return $pdf->download($filename);
    }

    /**
     * Update order (dates, fulfillment, lines, discounts, deposit, payments, status).
     */
    public function update(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }

        if ($order->isLockedForEditing()) {
            return back()->withErrors(['error' => __('vendor.order_edit_not_allowed_locked')]);
        }

        $validated = $request->validate([
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'fulfillment_type' => 'required|in:pickup,delivery',
            'delivery_address' => [
                Rule::requiredIf($request->input('fulfillment_type') === 'delivery'),
                'nullable',
                'string',
                'max:5000',
            ],
            'pickup_at' => 'nullable|date',
            'delivery_charge' => 'nullable|numeric|min:0|max:999999',
            'discount_amount' => 'nullable|numeric|min:0',
            'coupon_discount' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string|max:64',
            'security_deposit_type' => 'required|in:none,order_amount,product_security_deposit,fixed_amount',
            'security_deposit_value' => 'nullable|numeric|min:0',
            'token_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_detail_json' => 'nullable|string|max:65535',
            'status' => 'required|in:'.implode(',', Order::STATUSES),
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => [
                'nullable',
                'integer',
                Rule::exists('order_items', 'id')->where(fn ($q) => $q->where('order_id', $order->id)),
            ],
            'items.*.item_id' => [
                'nullable',
                'integer',
                Rule::exists('items', 'id')->where(fn ($q) => $q->where('vendor_id', $vendor->id)),
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.billing_units' => 'nullable|numeric|min:0',
            'items.*.price_type' => ['nullable', 'string', Rule::in(Items::priceTypeKeys())],
            'remove_item_ids' => 'nullable|array',
            'remove_item_ids.*' => [
                'integer',
                Rule::exists('order_items', 'id')->where(fn ($q) => $q->where('order_id', $order->id)),
            ],
        ]);

        if (! $order->canTransitionTo((string) $validated['status'])) {
            return back()->withErrors(['status' => __('vendor.order_invalid_status_transition')])->withInput();
        }

        $paymentDetail = [];
        if ($request->has('payment_detail_json')) {
            $raw = trim((string) $request->input('payment_detail_json', ''));
            if ($raw === '') {
                $paymentDetail = [];
            } else {
                $decoded = json_decode($raw, true);
                if (! is_array($decoded)) {
                    return back()->withErrors(['payment_detail_json' => __('vendor.order_invalid_payment_json')])->withInput();
                }
                $paymentDetail = array_values($decoded);
            }
        } else {
            $paymentDetail = is_array($order->payment_detail) ? $order->payment_detail : [];
        }

        $removeIds = array_values(array_map('intval', array_filter((array) $request->input('remove_item_ids', []))));

        $lineRows = collect($validated['items'])->filter(function ($row) {
            return ! empty($row['order_item_id']) || ! empty($row['item_id']);
        })->filter(function ($row) use ($removeIds) {
            if (! empty($row['order_item_id']) && in_array((int) $row['order_item_id'], $removeIds, true)) {
                return false;
            }

            return true;
        })->values()->all();

        if (count($lineRows) < 1) {
            return back()->withErrors(['items' => __('vendor.order_needs_one_line')])->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $order, $vendor, $paymentDetail, $removeIds, $lineRows) {
                $startAt = ! empty($validated['start_at']) ? Carbon::parse($validated['start_at']) : null;
                $endAt = ! empty($validated['end_at']) ? Carbon::parse($validated['end_at']) : null;
                $rentDays = ($startAt && $endAt) ? max(1, (int) ceil($startAt->diffInDays($endAt))) : 1;

                if (count($removeIds)) {
                    OrderItem::where('order_id', $order->id)->whereIn('id', $removeIds)->delete();
                }

                $addr = trim((string) ($validated['delivery_address'] ?? ''));
                $deliveryAddress = $addr !== '' ? $addr : null;

                $prevCouponCode = $order->coupon_code;

                $order->fill([
                    'start_at' => $startAt,
                    'end_at' => $endAt,
                    'fulfillment_type' => $validated['fulfillment_type'],
                    'delivery_address' => $deliveryAddress,
                    'pickup_at' => ! empty($validated['pickup_at']) ? Carbon::parse($validated['pickup_at']) : null,
                    'delivery_charge' => $validated['fulfillment_type'] === 'delivery'
                        ? round((float) ($validated['delivery_charge'] ?? 0), 2)
                        : 0,
                    'discount_amount' => round((float) ($validated['discount_amount'] ?? 0), 2),
                    'coupon_discount' => round((float) ($validated['coupon_discount'] ?? 0), 2),
                    'coupon_code' => $validated['coupon_code'] ?: null,
                    'security_deposit_type' => $validated['security_deposit_type'],
                    'security_deposit_value' => isset($validated['security_deposit_value'])
                        ? round((float) $validated['security_deposit_value'], 2)
                        : null,
                    'token_amount' => round((float) ($validated['token_amount'] ?? 0), 2),
                    'paid_amount' => round((float) ($validated['paid_amount'] ?? 0), 2),
                    'payment_detail' => $paymentDetail,
                    'status' => $validated['status'],
                ]);

                if (($validated['coupon_code'] ?? '') !== ($prevCouponCode ?? '')) {
                    $order->coupon_id = null;
                }

                $order->save();

                foreach ($lineRows as $row) {
                    $qty = (int) $row['quantity'];
                    $priceTypeInput = $row['price_type'] ?? null;
                    $billingIn = isset($row['billing_units']) ? (float) $row['billing_units'] : null;

                    if (! empty($row['order_item_id'])) {
                        $oi = OrderItem::where('order_id', $order->id)->where('id', $row['order_item_id'])->first();
                        if (! $oi) {
                            continue;
                        }
                        $lineType = $priceTypeInput ?: ($oi->price_type ?? ($oi->item?->price_type ?? 'per_day'));
                        $lineType = in_array($lineType, Items::priceTypeKeys(), true) ? $lineType : 'per_day';
                        $units = $this->normalizedBillingUnits($billingIn, $lineType);

                        $oi->update([
                            'quantity' => $qty,
                            'price_type' => $lineType,
                            'billing_units' => Items::priceTypeUsesBillingUnits($lineType) ? $units : null,
                            'start_at' => $order->start_at,
                            'end_at' => $order->end_at,
                            'rent_days' => $rentDays,
                        ]);
                        $oi->refresh();
                        $oi->update(['total_price' => $oi->lineSubtotal()]);
                    } elseif (! empty($row['item_id'])) {
                        $item = Items::where('vendor_id', $vendor->id)->where('id', $row['item_id'])->firstOrFail();
                        $lineType = $priceTypeInput ?: ($item->price_type ?? 'per_day');
                        $lineType = in_array($lineType, Items::priceTypeKeys(), true) ? $lineType : 'per_day';
                        $units = $this->normalizedBillingUnits($billingIn, $lineType);

                        $oi = OrderItem::create([
                            'order_id' => $order->id,
                            'item_id' => $item->id,
                            'item_name' => $item->name,
                            'price' => $item->price,
                            'quantity' => $qty,
                            'price_type' => $lineType,
                            'billing_units' => Items::priceTypeUsesBillingUnits($lineType) ? $units : null,
                            'start_at' => $order->start_at,
                            'end_at' => $order->end_at,
                            'rent_days' => $rentDays,
                            'total_price' => 0,
                        ]);
                        $oi->refresh();
                        $oi->update(['total_price' => $oi->lineSubtotal()]);
                    }
                }

                $order->refresh()->load('items');

                if ($order->items->isEmpty()) {
                    throw new \InvalidArgumentException(__('vendor.order_needs_one_line'));
                }

                $this->recalculateOrderFinancials($order);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('vendor.orders.show', $order)
            ->with('success', __('vendor.order_updated'));
    }

    protected function recalculateOrderFinancials(Order $order): void
    {
        $order->load('items');

        $subTotal = round($order->items->sum(fn (OrderItem $i) => $i->lineSubtotal()), 2);
        $taxTotal = round($subTotal * self::TAX_RATE, 2);
        $discountAmount = round((float) ($order->discount_amount ?? 0), 2);
        $couponDiscount = round((float) ($order->coupon_discount ?? 0), 2);
        $discountTotal = round($discountAmount + $couponDiscount, 2);

        $deliveryCharge = 0.0;
        if (($order->fulfillment_type ?? 'pickup') === 'delivery') {
            $deliveryCharge = round((float) ($order->delivery_charge ?? 0), 2);
        }

        $lineGrand = round($subTotal + $taxTotal - $discountTotal + $deliveryCharge, 2);
        $extraChargesTotal = round((float) ($order->extra_charges_total ?? 0), 2);
        $grandTotal = round($lineGrand + $extraChargesTotal, 2);

        $securityDeposit = $this->computeSecurityDepositFromState($order, $subTotal, $lineGrand);

        $order->update([
            'sub_total' => $subTotal,
            'tax_total' => $taxTotal,
            'discount_total' => $discountTotal,
            'grand_total' => $grandTotal,
            'security_deposit' => $securityDeposit,
        ]);
    }

    protected function computeSecurityDepositFromState(Order $order, float $subTotal, float $grandTotal): float
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

    protected function normalizedBillingUnits(?float $value, string $linePriceType): float
    {
        if (! Items::priceTypeUsesBillingUnits($linePriceType)) {
            return 1.0;
        }

        $v = $value !== null ? (float) $value : 1.0;
        if ($v < 0.01) {
            $v = 1.0;
        }

        return round($v, 2);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $order->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        $request->validate([
            'status' => 'required|in:'.implode(',', Order::STATUSES),
        ]);

        if ($order->isLockedForEditing()) {
            return back()->withErrors(['status' => __('vendor.order_edit_not_allowed_locked')]);
        }

        if (! $order->canTransitionTo((string) $request->input('status'))) {
            return back()->withErrors(['status' => __('vendor.order_invalid_status_transition')]);
        }

        $order->update([
            'status' => $request->input('status'),
        ]);

        return back()->with('success', __('vendor.status_updated'));
    }
}
