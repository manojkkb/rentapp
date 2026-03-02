<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorOrderController extends Controller
{
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
        
        $order->load(['customer', 'items.item.category']);
        
        return view('vendor.orders.show', compact('order'));
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
            'status' => 'required|in:pending,confirmed,ongoing,cancelled,completed'
        ]);
        
        $order->update([
            'status' => $request->status
        ]);
        
        return back()->with('success', 'Order status updated successfully!');
    }
}
