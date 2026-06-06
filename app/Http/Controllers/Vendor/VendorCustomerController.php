<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\Concerns\RedirectsIfNumericRouteKey;
use App\Models\User;
use App\Models\VendorCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorCustomerController extends Controller
{
    use RedirectsIfNumericRouteKey;

    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        return view('vendor.customers.index');
    }
    
    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        return view('vendor.customers.create');
    }
    
    /**
     * Store a newly created customer
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
            'name' => 'required|string|max:255',
            'mobile' => 'required|digits:10|unique:vendor_customers,mobile,NULL,id,vendor_id,' . $vendor->id,
            'address' => 'nullable|string|max:500',
        ]);

        // Check if user exists with this mobile number
        $user = User::where('mobile', $request->mobile)->first();
        
        // If user doesn't exist, create a new user
        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->mobile . '@rentkia.temp',
                'password' => Hash::make(Str::random(16)),
            ]);
        }

        $customer = VendorCustomer::create([
            'vendor_id' => $vendor->id,
            'user_id' => $user->id,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'is_active' => true,
        ]);
        
        // AJAX response
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully!',
                'customer' => $customer
            ]);
        }
        
        return redirect()->route('vendor.customers.index')
            ->with('success', 'Customer created successfully!');
    }
    
    /**
     * Show the form for editing a customer
     */
    public function edit(Request $request, VendorCustomer $customer)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $customer->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }

        if ($redirect = $this->redirectIfNumericRouteKey($request, $customer, 'vendor.customers.edit')) {
            return $redirect;
        }
        
        return view('vendor.customers.edit', compact('customer'));
    }
    
    /**
     * Update the specified customer
     */
    public function update(Request $request, VendorCustomer $customer)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $customer->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|digits:10|unique:vendor_customers,mobile,' . $customer->id . ',id,vendor_id,' . $vendor->id,
            'address' => 'nullable|string|max:500',
        ]);

        // Check if user exists with this mobile number
        $user = User::where('mobile', $request->mobile)->first();
        
        // If user doesn't exist, create a new user
        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->mobile . '@rentkia.temp',
                'password' => Hash::make(Str::random(16)),
            ]);
        }

        $customer->update([
            'user_id' => $user->id,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'address' => $request->address,
        ]);
        
        return redirect()->route('vendor.customers.index')
            ->with('success', 'Customer updated successfully!');
    }
    
    /**
     * Toggle customer active status.
     */
    public function toggleStatus(VendorCustomer $customer)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor || $customer->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access');
        }

        $customer->update([
            'is_active' => ! $customer->is_active,
        ]);

        $message = $customer->is_active
            ? __('vendor.customer_activated')
            : __('vendor.customer_deactivated');

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $customer->is_active,
            ]);
        }

        return back()->with('success', $message);
    }
}
