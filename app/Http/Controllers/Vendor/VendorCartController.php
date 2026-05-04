<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Items;
use App\Models\VendorCart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VendorCartController extends Controller
{
    /**
     * Display a listing of carts
     */
    public function index()
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        $carts = VendorCart::where('vendor_id', $vendor->id)
            ->with(['customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Get customers for create modal
        $customers = \App\Models\VendorCustomer::where('vendor_id', $vendor->id)
            ->orderBy('name')
            ->get();
        
        return view('vendor.carts.index', compact('carts', 'customers'));
    }
    
    /**
     * Show the form for creating a new cart
     */
    public function create()
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        $customers = \App\Models\VendorCustomer::where('vendor_id', $vendor->id)
            ->orderBy('name')
            ->get();
        
        return view('vendor.carts.create', compact('customers'));
    }
    
    /**
     * Store a newly created cart
     */
    public function store(Request $request)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Please select a vendor'], 403);
            }
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        $request->validate([
            'customer_id' => 'required|exists:vendor_customers,id',
            'cart_name' => 'required|string|max:255',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
        ]);

        $cart = VendorCart::create([
            'vendor_id' => $vendor->id,
            'customer_id' => $request->customer_id,
            'cart_name' => $request->cart_name,
            'sub_total' => 0,
            'tax_total' => 0,
            'discount_total' => 0,
            'token_amount' => 0,
            'paid_amount' => 0,
            'grand_total' => 0,
            'security_deposit' => 0,
            'security_deposit_type' => 'none',
            'security_deposit_value' => null,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);
        
        // AJAX response
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart created successfully!',
                'cart' => $cart->load('customer')
            ]);
        }
        
        return redirect()->route('vendor.carts.index')
            ->with('success', 'Cart created successfully!');
    }
    
    /**
     * Display the specified cart
     */
    public function show(VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        $cart->load(['customer', 'items.item.category']);
        
        // Get available items for adding to cart
        $availableItems = \App\Models\Items::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->where('is_available', true)
            ->with('category')
            ->orderBy('name')
            ->get();
        
        // Get unique categories for filter
        $categories = \App\Models\Category::whereIn('id', $availableItems->pluck('category_id')->unique())
            ->orderBy('name')
            ->get();
        
        $cartBillingUnitsLabels = collect(Items::priceTypeKeys())
            ->filter(fn ($k) => Items::priceTypeUsesBillingUnits($k))
            ->mapWithKeys(fn ($k) => [$k => Items::billingUnitsFieldLabel($k)])
            ->all();

        return view('vendor.carts.show', compact('cart', 'availableItems', 'categories', 'cartBillingUnitsLabels'));
    }

    /**
     * Printable / shareable quote for the cart (HTML).
     */
    public function quote(VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $cart->vendor_id !== $vendor->id) {
            abort(403);
        }

        $cart->load(['customer', 'items.item.category', 'vendor']);

        return view('vendor.carts.quote', compact('cart'));
    }

    /**
     * Download quote as an HTML file (opens in browser or saves, depending on client).
     */
    public function downloadQuote(VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $cart->vendor_id !== $vendor->id) {
            abort(403);
        }

        $cart->load(['customer', 'items.item.category', 'vendor']);

        $filename = 'quote-cart-'.$cart->id.'.html';

        return response()->view('vendor.carts.quote', compact('cart'), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Printable cart sheet (opens in browser; use ?autoprint=1 to trigger print dialog).
     */
    public function printCart(Request $request, VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $cart->vendor_id !== $vendor->id) {
            abort(403);
        }

        $cart->load(['customer', 'items.item.category', 'vendor']);

        return view('vendor.carts.print', [
            'cart' => $cart,
            'autoprint' => $request->boolean('autoprint'),
        ]);
    }

    /**
     * Update pickup / delivery and optional delivery address.
     */
    public function updateFulfillment(Request $request, VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();

        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'fulfillment_type' => 'required|in:pickup,delivery',
            'delivery_address' => [
                Rule::requiredIf($request->input('fulfillment_type') === 'delivery'),
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

            $cart->update([
                'fulfillment_type' => 'pickup',
                'delivery_address' => $addr !== '' ? $addr : null,
                'delivery_charge' => 0,
                'pickup_at' => ! empty($validated['pickup_at']) ? $validated['pickup_at'] : null,
            ]);
        } else {
            $cart->update([
                'fulfillment_type' => 'delivery',
                'delivery_address' => trim((string) ($validated['delivery_address'] ?? '')),
                'pickup_at' => null,
                'delivery_charge' => round((float) ($validated['delivery_charge'] ?? 0), 2),
            ]);
        }

        $this->updateCartTotals($cart);
        $cart->refresh();

        return response()->json([
            'success' => true,
            'message' => __('vendor.fulfillment_saved'),
            'fulfillment_type' => $cart->fulfillment_type,
            'delivery_address' => $cart->delivery_address,
            'pickup_at' => $cart->pickup_at?->toIso8601String(),
            'delivery_charge' => $cart->delivery_charge,
            'cart' => $this->cartJsonPayload($cart),
        ]);
    }
    
    /**
     * Add item to cart (supports multiple items)
     */
    public function addItem(Request $request, VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.billing_units' => 'nullable|numeric|min:0.01|max:999999',
        ], [
            'items.required' => 'Please select at least one item',
            'items.min' => 'Please select at least one item',
        ]);
        
        $addedCount = 0;
        
        foreach ($request->items as $itemData) {
            // Check if item belongs to vendor
            $item = \App\Models\Items::where('id', $itemData['item_id'])
                ->where('vendor_id', $vendor->id)
                ->where('is_active', true)
                ->where('is_available', true)
                ->first();
            
            if (!$item) {
                continue; // Skip invalid items
            }
            
            // Check if item already exists in cart
            $existingItem = \App\Models\VendorCartItem::where('vendor_cart_id', $cart->id)
                ->where('item_id', $itemData['item_id'])
                ->first();
            
            $linePriceType = $item->price_type;
            if (! in_array($linePriceType, Items::priceTypeKeys(), true)) {
                $linePriceType = 'per_day';
            }

            $billingUnits = $this->normalizedBillingUnits(
                isset($itemData['billing_units']) ? (float) $itemData['billing_units'] : null,
                $linePriceType
            );

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $itemData['quantity'],
                    'price_type' => $linePriceType,
                    'billing_units' => $billingUnits,
                ]);
            } else {
                \App\Models\VendorCartItem::create([
                    'vendor_cart_id' => $cart->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'price_type' => $linePriceType,
                    'billing_units' => $billingUnits,
                ]);
            }
            
            $addedCount++;
        }
        
        // Update cart totals
        $this->updateCartTotals($cart);
        
        if ($addedCount === 0) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No valid items were added'], 422);
            }
            return back()->withErrors(['error' => 'No valid items were added']);
        }
        
        $message = $addedCount === 1 ? 'Item added to cart successfully!' : "$addedCount items added to cart successfully!";
        
        if ($request->wantsJson()) {
            $cart->refresh();
            return response()->json([
                'success' => true,
                'message' => $message,
                'cart' => $this->cartJsonPayload($cart),
            ]);
        }
        
        return back()->with('success', $message);
    }
    
    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, VendorCart $cart, $itemId)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'billing_units' => 'nullable|numeric|min:0.01|max:999999',
        ]);
        
        $cartItem = \App\Models\VendorCartItem::where('vendor_cart_id', $cart->id)
            ->where('item_id', $itemId)
            ->first();

        if (!$cartItem) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Item not found in cart'], 404);
            }
            return back()->withErrors(['error' => 'Item not found in cart']);
        }

        $cartItem->load('item');
        $nextPriceType = $cartItem->item?->price_type ?? $cartItem->price_type;
        if (! in_array($nextPriceType, Items::priceTypeKeys(), true)) {
            $nextPriceType = 'per_day';
        }

        $updates = [
            'quantity' => $request->quantity,
            'price_type' => $nextPriceType,
        ];

        if ($nextPriceType === 'fixed') {
            $updates['billing_units'] = 1;
        } elseif ($request->exists('billing_units') && $request->input('billing_units') !== null && $request->input('billing_units') !== '') {
            $updates['billing_units'] = $this->normalizedBillingUnits((float) $request->billing_units, $nextPriceType);
        }

        $cartItem->update($updates);
        
        // Update cart totals
        $this->updateCartTotals($cart);
        
        // Reload cart for updated totals
        $cart->refresh();
        $cartItem->load('item');
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item quantity updated successfully!',
                'item' => [
                    'item_id' => $cartItem->item_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->item->price,
                    'price_type' => $cartItem->item->price_type,
                    'billing_units' => (float) $cartItem->billing_units,
                    'line_total' => $cartItem->lineSubtotal(),
                ],
                'cart' => $this->cartJsonPayload($cart),
            ]);
        }
        
        return back()->with('success', 'Item quantity updated successfully!');
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem(Request $request, VendorCart $cart, $itemId)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        $cartItem = \App\Models\VendorCartItem::where('vendor_cart_id', $cart->id)
            ->where('item_id', $itemId)
            ->first();

        if (!$cartItem) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Item not found in cart'], 404);
            }
            return back()->withErrors(['error' => 'Item not found in cart']);
        }
        
        $cartItem->delete();
        
        // Update cart totals
        $this->updateCartTotals($cart);
        
        // Reload cart for updated totals
        $cart->refresh();
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully!',
                'cart' => $this->cartJsonPayload($cart),
            ]);
        }
        
        return back()->with('success', 'Item removed from cart successfully!');
    }

    /**
     * Apply discount to cart
     */
    public function applyDiscount(Request $request, VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();

        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }

        $request->validate([
            'discount_type' => 'required|in:fixed,percent',
            'discount_value' => 'required|numeric|min:0',
        ]);

        $discountType = $request->discount_type;
        $discountValue = $request->discount_value;
        $subTotal = $cart->sub_total;

        if ($discountType === 'percent') {
            if ($discountValue > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Percentage cannot exceed 100%',
                ], 422);
            }
            $discountAmount = round($subTotal * ($discountValue / 100), 2);
        } else {
            $discountAmount = round($discountValue, 2);
            if ($discountAmount > $subTotal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Discount cannot exceed subtotal',
                ], 422);
            }
        }

        $cart->update([
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
        ]);

        // Recalculate grand total
        $this->updateCartTotals($cart);
        $cart->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Discount applied successfully!',
                'discount' => [
                    'type' => $discountType,
                    'value' => $discountValue,
                    'amount' => $discountAmount,
                ],
                'cart' => $this->cartJsonPayload($cart),
            ]);
        }

        return back()->with('success', 'Discount applied successfully!');
    }

    /**
     * Remove discount from cart
     */
    public function removeDiscount(Request $request, VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();

        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }

        $cart->update([
            'discount_type' => null,
            'discount_value' => null,
            'discount_amount' => 0,
        ]);

        $this->updateCartTotals($cart);
        $cart->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Discount removed successfully!',
                'cart' => $this->cartJsonPayload($cart),
            ]);
        }

        return back()->with('success', 'Discount removed successfully!');
    }

    /**
     * Apply coupon to cart
     */
    public function applyCoupon(Request $request, VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();

        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        $coupon = \App\Models\Coupon::where('vendor_id', $vendor->id)
            ->where('code', strtoupper(trim($request->coupon_code)))
            ->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code'], 422);
        }

        if (!$coupon->isValid($cart->sub_total)) {
            $message = 'This coupon is not valid';
            if ($coupon->end_date && now()->gt($coupon->end_date)) {
                $message = 'This coupon has expired';
            } elseif ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                $message = 'This coupon has reached its usage limit';
            } elseif ($cart->sub_total < $coupon->min_order_amount) {
                $message = 'Minimum order amount is ₹' . number_format($coupon->min_order_amount, 2);
            }
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        $discountAmount = $coupon->calculateDiscount($cart->sub_total);

        $cart->update([
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'coupon_discount' => $discountAmount,
        ]);

        $this->updateCartTotals($cart);
        $cart->refresh();

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
            'cart' => $this->cartJsonPayload($cart),
        ]);
    }

    /**
     * Remove coupon from cart
     */
    public function removeCoupon(Request $request, VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();

        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $cart->update([
            'coupon_id' => null,
            'coupon_code' => null,
            'coupon_discount' => 0,
        ]);

        $this->updateCartTotals($cart);
        $cart->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully!',
            'cart' => $this->cartJsonPayload($cart),
        ]);
    }

    /**
     * List available coupons for the vendor
     */
    public function listCoupons(Request $request, VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();

        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $coupons = \App\Models\Coupon::where('vendor_id', $vendor->id)
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
            ->map(function ($coupon) use ($cart) {
                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'name' => $coupon->name,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'min_order_amount' => $coupon->min_order_amount,
                    'max_discount_amount' => $coupon->max_discount_amount,
                    'end_date' => $coupon->end_date ? $coupon->end_date->format('d M Y') : null,
                    'is_applicable' => $coupon->isValid($cart->sub_total),
                ];
            });

        return response()->json(['success' => true, 'coupons' => $coupons]);
    }
    
    /**
     * Compute security deposit from stored rule and current subtotal / order total.
     */
    private function computeSecurityDepositFromState(VendorCart $cart, float $subTotal, float $grandTotal): float
    {
        $type = $cart->security_deposit_type ?? 'none';
        $value = (float) ($cart->security_deposit_value ?? 0);

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
     * Cart totals payload for JSON responses (AJAX summary updates).
     *
     * @return array<string, mixed>
     */
    private function cartJsonPayload(VendorCart $cart): array
    {
        $detail = $cart->payment_detail ?? [];
        if (! is_array($detail)) {
            $detail = [];
        }

        return [
            'sub_total' => $cart->sub_total,
            'tax_total' => $cart->tax_total,
            'discount_amount' => $cart->discount_amount,
            'coupon_discount' => $cart->coupon_discount,
            'discount_total' => $cart->discount_total,
            'grand_total' => $cart->grand_total,
            'security_deposit' => $cart->security_deposit,
            'security_deposit_type' => $cart->security_deposit_type ?? 'none',
            'security_deposit_value' => $cart->security_deposit_value,
            'paid_amount' => $cart->paid_amount,
            'payment_detail' => array_values($detail),
            'fulfillment_type' => $cart->fulfillment_type ?? 'pickup',
            'delivery_address' => $cart->delivery_address,
            'pickup_at' => $cart->pickup_at?->toIso8601String(),
            'delivery_charge' => $cart->delivery_charge,
            'items_count' => $cart->items()->count(),
        ];
    }

    /**
     * Update cart totals based on items
     */
    private function updateCartTotals(VendorCart $cart)
    {
        $cartItems = \App\Models\VendorCartItem::where('vendor_cart_id', $cart->id)
            ->with('item')
            ->get();

        $subTotal = 0;

        foreach ($cartItems as $cartItem) {
            if ($cartItem->item) {
                $subTotal += $cartItem->lineSubtotal();
            }
        }

        $taxTotal = $subTotal * 0.10; // 10% tax (adjust as needed)
        $discountTotal = $cart->discount_amount + $cart->coupon_discount;

        $deliveryCharge = 0.0;
        if (($cart->fulfillment_type ?? 'pickup') === 'delivery') {
            $deliveryCharge = round((float) ($cart->delivery_charge ?? 0), 2);
        }

        $grandTotal = $subTotal + $taxTotal - $discountTotal + $deliveryCharge;

        $securityDeposit = $this->computeSecurityDepositFromState($cart, $subTotal, $grandTotal);

        $cart->update([
            'sub_total' => $subTotal,
            'tax_total' => $taxTotal,
            'discount_total' => $discountTotal,
            'grand_total' => $grandTotal,
            'security_deposit' => $securityDeposit,
        ]);
    }

    /**
     * Persist security deposit rule and recalculate stored security_deposit.
     */
    public function applySecurityDeposit(Request $request, VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $cart->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'security_deposit_type' => 'required|in:none,order_amount,product_security_deposit,fixed_amount',
            'security_deposit_value' => 'nullable|numeric|min:0',
        ]);

        $type = $validated['security_deposit_type'];

        if ($type === 'none') {
            $cart->update([
                'security_deposit_type' => 'none',
                'security_deposit_value' => null,
            ]);
        } else {
            $raw = $validated['security_deposit_value'];
            $num = round((float) $raw, 2);

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

            $cart->update([
                'security_deposit_type' => $type,
                'security_deposit_value' => $num,
            ]);
        }

        $this->updateCartTotals($cart);
        $cart->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Security deposit updated.',
            'cart' => $this->cartJsonPayload($cart),
        ]);
    }
    
    /**
     * Show the form for editing a cart
     */
    public function edit(VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        return view('vendor.carts.edit', compact('cart'));
    }
    
    /**
     * Update the specified cart
     */
    public function update(Request $request, VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
            }
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        $request->validate([
            'cart_name' => 'required|string|max:255',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
        ]);

        $cart->update([
            'cart_name' => $request->cart_name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);
        
        if ($request->wantsJson()) {
            $cart->refresh();
            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully!',
                'cart' => [
                    'cart_name' => $cart->cart_name,
                    'start_time' => $cart->start_time ? $cart->start_time->format('M d, Y h:i A') : null,
                    'end_time' => $cart->end_time ? $cart->end_time->format('M d, Y h:i A') : null,
                ],
            ]);
        }
        
        return redirect()->route('vendor.carts.index')
            ->with('success', 'Cart updated successfully!');
    }
    
    /**
     * Remove the specified cart
     */
    public function destroy(VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        $cart->delete();
        
        return redirect()->route('vendor.carts.index')
            ->with('success', 'Cart deleted successfully!');
    }

    /**
     * Remove all items from cart
     */
    public function emptyCart(Request $request, VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();

        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $cart->items()->delete();

        $cart->update([
            'discount_type' => null,
            'discount_value' => null,
            'discount_amount' => 0,
            'coupon_discount' => 0,
            'coupon_code' => null,
        ]);

        $this->updateCartTotals($cart);

        return response()->json([
            'success' => true,
            'message' => 'Cart emptied successfully!',
        ]);
    }

    /**
     * Record a payment against the cart (updates paid_amount and appends payment_detail).
     */
    public function recordPayment(Request $request, VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $cart->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_for' => 'required|in:order_amount,security_deposit',
            'method' => 'required|string|max:50',
            'paid_on' => 'nullable|date',
        ]);

        $amount = round((float) $validated['amount'], 2);

        try {
            DB::transaction(function () use ($cart, $amount, $validated) {
                $cart->refresh();
                $detail = $cart->payment_detail ?? [];
                if (! is_array($detail)) {
                    $detail = [];
                }
                $detail[] = [
                    'payment_for' => $validated['payment_for'],
                    'method' => $validated['method'],
                    'amount' => $amount,
                    'paid_on' => $validated['paid_on'] ?? null,
                    'recorded_at' => now()->toIso8601String(),
                ];

                $cart->payment_detail = $detail;
                $cart->paid_amount = round((float) $cart->paid_amount + $amount, 2);
                $cart->save();
            });

            $cart->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully.',
                'cart' => $this->cartJsonPayload($cart),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Record payment failed: '.$e->getMessage(), ['cart_id' => $cart->id]);

            return response()->json([
                'success' => false,
                'message' => 'Could not record payment.',
            ], 500);
        }
    }

    /**
     * Remove a recorded payment by index (payment_detail array position).
     */
    public function removePayment(Request $request, VendorCart $cart, int $paymentIndex)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $cart->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        $cart->refresh();
        $detail = $cart->payment_detail ?? [];
        if (! is_array($detail)) {
            $detail = [];
        }

        if (! array_key_exists($paymentIndex, $detail)) {
            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }

        $removed = $detail[$paymentIndex];
        $amount = round((float) ($removed['amount'] ?? 0), 2);

        array_splice($detail, $paymentIndex, 1);
        $cart->payment_detail = array_values($detail);
        $cart->paid_amount = max(0, round((float) $cart->paid_amount - $amount, 2));
        $cart->save();

        $cart->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Payment removed.',
            'cart' => $this->cartJsonPayload($cart),
        ]);
    }

    /**
     * Place order from cart
     */
    public function placeOrder(VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }

        $cart->load('items.item');

        // Validate cart has items
        if ($cart->items->count() === 0) {
            return back()->withErrors(['error' => 'Cannot place order. Cart is empty.']);
        }

        // Validate booking dates
        if (!$cart->start_time || !$cart->end_time) {
            return back()->withErrors(['error' => 'Please set booking start and end dates before placing order.']);
        }

        if ($cart->fulfillment_type === 'delivery' && trim((string) ($cart->delivery_address ?? '')) === '') {
            return back()->withErrors(['error' => __('vendor.delivery_address_required')]);
        }

        try {
            DB::beginTransaction();

            // Generate unique order number
            $orderNumber = 'ORD-' . strtoupper(uniqid());

            // Calculate rent days
            $rentDays = max(1, (int) ceil($cart->start_time->diffInDays($cart->end_time)));

            // Create order (snapshot cart pricing, fulfillment, discounts, payments)
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $cart->customer_id,
                'vendor_id' => $cart->vendor_id,
                'start_at' => $cart->start_time,
                'end_at' => $cart->end_time,
                'fulfillment_type' => $cart->fulfillment_type ?? 'pickup',
                'delivery_address' => $cart->delivery_address,
                'pickup_at' => $cart->pickup_at,
                'delivery_charge' => round((float) ($cart->delivery_charge ?? 0), 2),
                'discount_type' => $cart->discount_type,
                'discount_value' => $cart->discount_value,
                'discount_amount' => round((float) ($cart->discount_amount ?? 0), 2),
                'coupon_id' => $cart->coupon_id,
                'coupon_code' => $cart->coupon_code,
                'coupon_discount' => round((float) ($cart->coupon_discount ?? 0), 2),
                'security_deposit' => round((float) ($cart->security_deposit ?? 0), 2),
                'security_deposit_type' => $cart->security_deposit_type ?? 'none',
                'security_deposit_value' => $cart->security_deposit_value,
                'token_amount' => round((float) ($cart->token_amount ?? 0), 2),
                'payment_detail' => is_array($cart->payment_detail) ? $cart->payment_detail : [],
                'sub_total' => $cart->sub_total,
                'tax_total' => $cart->tax_total,
                'discount_total' => $cart->discount_total,
                'grand_total' => $cart->grand_total,
                'paid_amount' => $cart->paid_amount ?? 0,
                'status' => 'pending',
            ]);

            if ($cart->coupon_id) {
                $coupon = Coupon::where('id', $cart->coupon_id)->lockForUpdate()->first();
                if ($coupon) {
                    $coupon->increment('used_count');
                }
            }

            // Create order items from cart items
            foreach ($cart->items as $cartItem) {
                if ($cartItem->item) {
                    $itemTotal = $cartItem->lineSubtotal();
                    $linePriceType = $cartItem->item->price_type ?? $cartItem->price_type ?? 'per_day';
                    $lineBillingUnits = $this->normalizedBillingUnits(
                        $cartItem->billing_units !== null ? (float) $cartItem->billing_units : null,
                        $linePriceType
                    );

                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $cartItem->item_id,
                        'item_name' => $cartItem->item->name,
                        'price' => $cartItem->item->price,
                        'quantity' => $cartItem->quantity,
                        'price_type' => $linePriceType,
                        'billing_units' => Items::priceTypeUsesBillingUnits($linePriceType) ? $lineBillingUnits : null,
                        'start_at' => $cart->start_time,
                        'end_at' => $cart->end_time,
                        'rent_days' => $rentDays,
                        'total_price' => $itemTotal,
                    ]);
                }
            }

            // Delete cart after order is placed
            $cart->delete();

            DB::commit();

            return redirect()->route('vendor.carts.index')
                ->with('success', 'Order placed successfully! Order Number: ' . $orderNumber . '. You can find it in the Orders section.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Place order failed: ' . $e->getMessage(), [
                'cart_id' => $cart->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Failed to place order: ' . $e->getMessage()]);
        }
    }

    /**
     * Billing duration count (days, hours, …). Fixed price always uses 1.
     */
    private function normalizedBillingUnits(?float $value, string $linePriceType): float
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
}
