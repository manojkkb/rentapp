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

        // Get all business categories
        $categories = \App\Models\BusinessCategory::active()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('vendor.profile.index', compact('vendor', 'categories'));
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
            'business_category_id' => 'nullable|exists:business_categories,id',
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
            'name', 'business_category_id', 'address_line1', 'address_line2', 
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

    /**
     * Update the personal profile
     */
    public function updatePersonalProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Personal profile updated successfully!');
    }

    /**
     * Update the business profile
     */
    public function updateBusinessProfile(Request $request)
    {
        $user = Auth::user();
        $vendor = $user->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        // Check if user is owner
        $vendorUser = $user->vendors()->where('vendors.id', $vendor->id)->first();
        if (!$vendorUser || !$vendorUser->pivot->is_owner) {
            return back()->withErrors(['error' => 'Only the business owner can update the business profile.']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'business_category_id' => 'required|exists:business_categories,id',
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
            'name', 'business_category_id', 'address_line1', 'address_line2', 
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

        return back()->with('success', 'Business profile updated successfully!');
    }

    /**
     * Switch language
     */
    public function switchLanguage(Request $request)
    {
        $request->validate([
            'language' => 'required|string|in:en,hi,bn,mr,te,ta,gu,ur,kn,or,ml,pa',
        ]);

        // Store language in session
        session(['language' => $request->language]);

        // Update user's language preference
        $user = Auth::user();
        if ($user) {
            $user->update(['language' => $request->language]);
        }

        // Update vendor's language if exists
        $vendor = $user->currentVendor();
        if ($vendor) {
            $vendor->update(['language' => $request->language]);
        }

        return back()->with('success', 'Language changed successfully!');
    }
}
