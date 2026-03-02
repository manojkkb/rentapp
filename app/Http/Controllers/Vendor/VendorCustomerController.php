<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorCustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index()
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        $customers = VendorCustomer::where('vendor_id', $vendor->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('vendor.customers.index', compact('customers'));
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
                'email' => $request->mobile . '@rentapp.temp',
                'password' => Hash::make(Str::random(16)),
            ]);
        }

        VendorCustomer::create([
            'vendor_id' => $vendor->id,
            'user_id' => $user->id,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'address' => $request->address,
        ]);
        
        return redirect()->route('vendor.customers.index')
            ->with('success', 'Customer created successfully!');
    }
    
    /**
     * Show the form for editing a customer
     */
    public function edit(VendorCustomer $customer)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $customer->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
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
                'email' => $request->mobile . '@rentapp.temp',
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
     * Remove the specified customer
     */
    public function destroy(VendorCustomer $customer)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor || $customer->vendor_id !== $vendor->id) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Unauthorized access']);
        }
        
        $customer->delete();
        
        return redirect()->route('vendor.customers.index')
            ->with('success', 'Customer deleted successfully!');
    }
}
