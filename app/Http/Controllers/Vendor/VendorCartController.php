<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorCart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        
        return view('vendor.carts.show', compact('cart', 'availableItems', 'categories'));
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
            
            if ($existingItem) {
                // Update quantity
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $itemData['quantity']
                ]);
            } else {
                // Add new item
                \App\Models\VendorCartItem::create([
                    'vendor_cart_id' => $cart->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
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
                'cart' => [
                    'sub_total' => $cart->sub_total,
                    'tax_total' => $cart->tax_total,
                    'discount_amount' => $cart->discount_amount,
                    'coupon_discount' => $cart->coupon_discount,
                    'discount_total' => $cart->discount_total,
                    'grand_total' => $cart->grand_total,
                    'items_count' => $cart->items()->count(),
                ],
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
        
        $cartItem->update([
            'quantity' => $request->quantity
        ]);
        
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
                    'line_total' => $cartItem->item->price * $cartItem->quantity,
                ],
                'cart' => [
                    'sub_total' => $cart->sub_total,
                    'tax_total' => $cart->tax_total,
                    'discount_amount' => $cart->discount_amount,
                    'coupon_discount' => $cart->coupon_discount,
                    'discount_total' => $cart->discount_total,
                    'grand_total' => $cart->grand_total,
                    'items_count' => $cart->items()->count(),
                ],
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
                'cart' => [
                    'sub_total' => $cart->sub_total,
                    'tax_total' => $cart->tax_total,
                    'discount_amount' => $cart->discount_amount,
                    'coupon_discount' => $cart->coupon_discount,
                    'discount_total' => $cart->discount_total,
                    'grand_total' => $cart->grand_total,
                    'items_count' => $cart->items()->count(),
                ],
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
                'cart' => [
                    'sub_total' => $cart->sub_total,
                    'tax_total' => $cart->tax_total,
                    'discount_total' => $cart->discount_total,
                    'discount_amount' => $cart->discount_amount,
                    'coupon_discount' => $cart->coupon_discount,
                    'grand_total' => $cart->grand_total,
                    'items_count' => $cart->items()->count(),
                ],
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
                'cart' => [
                    'sub_total' => $cart->sub_total,
                    'tax_total' => $cart->tax_total,
                    'discount_total' => $cart->discount_total,
                    'discount_amount' => $cart->discount_amount,
                    'coupon_discount' => $cart->coupon_discount,
                    'grand_total' => $cart->grand_total,
                    'items_count' => $cart->items()->count(),
                ],
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
            'cart' => [
                'sub_total' => $cart->sub_total,
                'tax_total' => $cart->tax_total,
                'discount_total' => $cart->discount_total,
                'discount_amount' => $cart->discount_amount,
                'coupon_discount' => $cart->coupon_discount,
                'grand_total' => $cart->grand_total,
                'items_count' => $cart->items()->count(),
            ],
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
            'cart' => [
                'sub_total' => $cart->sub_total,
                'tax_total' => $cart->tax_total,
                'discount_total' => $cart->discount_total,
                'discount_amount' => $cart->discount_amount,
                'coupon_discount' => $cart->coupon_discount,
                'grand_total' => $cart->grand_total,
                'items_count' => $cart->items()->count(),
            ],
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
                $subTotal += $cartItem->item->price * $cartItem->quantity;
            }
        }
        
        $taxTotal = $subTotal * 0.10; // 10% tax (adjust as needed)
        $discountTotal = $cart->discount_amount + $cart->coupon_discount;
        $grandTotal = $subTotal + $taxTotal - $discountTotal;
        
        $cart->update([
            'sub_total' => $subTotal,
            'tax_total' => $taxTotal,
            'discount_total' => $discountTotal,
            'grand_total' => $grandTotal,
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

        // Reset totals
        $cart->update([
            'sub_total' => 0,
            'tax_total' => 0,
            'discount_total' => 0,
            'discount_type' => null,
            'discount_value' => null,
            'discount_amount' => 0,
            'coupon_discount' => 0,
            'coupon_code' => null,
            'grand_total' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cart emptied successfully!',
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

        try {
            DB::beginTransaction();

            // Generate unique order number
            $orderNumber = 'ORD-' . strtoupper(uniqid());

            // Calculate rent days
            $rentDays = max(1, (int) ceil($cart->start_time->diffInDays($cart->end_time)));

            // Create order
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_id' => $cart->customer_id,
                'vendor_id' => $cart->vendor_id,
                'start_at' => $cart->start_time,
                'end_at' => $cart->end_time,
                'sub_total' => $cart->sub_total,
                'tax_total' => $cart->tax_total,
                'discount_total' => $cart->discount_total,
                'grand_total' => $cart->grand_total,
                'paid_amount' => $cart->paid_amount ?? 0,
                'status' => 'pending',
            ]);

            // Create order items from cart items
            foreach ($cart->items as $cartItem) {
                if ($cartItem->item) {
                    $itemTotal = $cartItem->item->price * $cartItem->quantity * $rentDays;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $cartItem->item_id,
                        'item_name' => $cartItem->item->name, // Snapshot
                        'price' => $cartItem->item->price, // Snapshot
                        'quantity' => $cartItem->quantity,
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
}
