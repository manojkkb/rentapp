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
            return back()->withErrors(['error' => 'No valid items were added']);
        }
        
        $message = $addedCount === 1 ? 'Item added to cart successfully!' : "$addedCount items added to cart successfully!";
        return back()->with('success', $message);
    }
    
    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, VendorCart $cart, $itemId)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        
        $cartItem = \App\Models\VendorCartItem::where('vendor_cart_id', $cart->id)
            ->where('item_id', $itemId)
            ->firstOrFail();
        
        $cartItem->update([
            'quantity' => $request->quantity
        ]);
        
        // Update cart totals
        $this->updateCartTotals($cart);
        
        return back()->with('success', 'Item quantity updated successfully!');
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem(VendorCart $cart, $itemId)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        $cartItem = \App\Models\VendorCartItem::where('vendor_cart_id', $cart->id)
            ->where('item_id', $itemId)
            ->firstOrFail();
        
        $cartItem->delete();
        
        // Update cart totals
        $this->updateCartTotals($cart);
        
        return back()->with('success', 'Item removed from cart successfully!');
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
        $grandTotal = $subTotal + $taxTotal - $cart->discount_total;
        
        $cart->update([
            'sub_total' => $subTotal,
            'tax_total' => $taxTotal,
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
     * Place order from cart
     */
    public function placeOrder(VendorCart $cart)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $cart->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }

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
            $rentDays = max(1, $cart->start_time->diffInDays($cart->end_time) ?: 1);

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
            return back()->withErrors(['error' => 'Failed to place order. Please try again.']);
        }
    }
}
