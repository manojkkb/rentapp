<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VendorController extends Controller
{
    /**
     * Display the vendor dashboard home page
     */
    public function home()
    {
        return view('vendor.home.index');
    }

    /**
     * Display the vendor profile
     */
    public function profile()
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        return view('vendor.profile.index', compact('vendor'));
    }

    /**
     * Update the vendor profile
     */
    public function updateProfile(Request $request)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'gst_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only([
            'name', 'address_line1', 'address_line2', 
            'city', 'state', 'postal_code', 'country', 'gst_number'
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($vendor->logo && Storage::disk('public')->exists($vendor->logo)) {
                Storage::disk('public')->delete($vendor->logo);
            }

            $logo = $request->file('logo');
            $logoName = 'vendor_logo_' . $vendor->id . '_' . time() . '.' . $logo->getClientOriginalExtension();
            $logoPath = $logo->storeAs('vendors/logos', $logoName, 'public');
            $data['logo'] = $logoPath;
        }

        // Update slug if name changed
        if ($vendor->name !== $data['name']) {
            $data['slug'] = Str::slug($data['name']) . '-' . $vendor->id;
        }

        $vendor->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }
}
