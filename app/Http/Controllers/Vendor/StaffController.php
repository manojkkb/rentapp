<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    /**
     * Display a listing of staff users
     */
    public function index()
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Get all users for this vendor with pivot data
        $staff = $vendor->users()
            ->withPivot('id', 'role', 'is_owner', 'is_active', 'last_login_at', 'permissions')
            ->orderBy('vendor_users.created_at', 'desc')
            ->paginate(15);
        
        return view('vendor.staff.index', compact('staff', 'vendor'));
    }
    
    /**
     * Show the form for creating a new staff user
     */
    public function create()
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Define available roles
        $roles = [
            'manager' => 'Manager',
            'staff' => 'Staff',
            'cashier' => 'Cashier',
        ];
        
        return view('vendor.staff.create', compact('vendor', 'roles'));
    }
    
    /**
     * Store a newly created staff user
     */
    public function store(Request $request)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Validate request
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|digits:10',
            'email' => 'nullable|email|max:255',
            'role' => 'required|in:manager,staff,cashier',
            'is_active' => 'boolean',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Check if user with this mobile already exists
            $user = User::where('mobile', $request->mobile)->first();
            
            if ($user) {
                // Check if user is already added to this vendor
                $existingVendorUser = $vendor->users()->where('user_id', $user->id)->exists();
                
                if ($existingVendorUser) {
                    DB::rollBack();
                    return back()->withInput()
                        ->withErrors(['mobile' => 'This user is already added to your vendor. Please edit the existing staff member.']);
                }
                
                // User exists but not in this vendor, add them
                $vendor->users()->attach($user->id, [
                    'is_owner' => false,
                    'role' => $request->role,
                    'is_active' => $request->has('is_active') ? true : false,
                    'permissions' => json_encode([]),
                ]);
                
                DB::commit();
                
                return redirect()->route('vendor.staff.index')
                    ->with('success', 'Existing user added to your vendor successfully!');
            }
            
            // Create new user
            $user = User::create([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email ?? $request->mobile . '@staff.temp',
                'password' => Hash::make('password123'), // Default password
            ]);
            
            // Add user to vendor_users pivot
            $vendor->users()->attach($user->id, [
                'is_owner' => false,
                'role' => $request->role,
                'is_active' => $request->has('is_active') ? true : false,
                'permissions' => json_encode([]),
            ]);
            
            DB::commit();
            
            return redirect()->route('vendor.staff.index')
                ->with('success', 'Staff user added successfully! Default password: password123');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Failed to create staff user: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show the form for editing a staff user
     */
    public function edit($id)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Get the vendor_user pivot record
        $vendorUser = VendorUser::where('id', $id)
            ->where('vendor_id', $vendor->id)
            ->firstOrFail();
        
        $staffUser = User::findOrFail($vendorUser->user_id);
        
        // Prevent editing owner
        if ($vendorUser->is_owner) {
            return back()->withErrors(['error' => 'Cannot edit owner account']);
        }
        
        // Define available roles
        $roles = [
            'manager' => 'Manager',
            'staff' => 'Staff',
            'cashier' => 'Cashier',
        ];
        
        return view('vendor.staff.edit', compact('vendor', 'staffUser', 'vendorUser', 'roles'));
    }
    
    /**
     * Update the specified staff user
     */
    public function update(Request $request, $id)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Get the vendor_user pivot record
        $vendorUser = VendorUser::where('id', $id)
            ->where('vendor_id', $vendor->id)
            ->firstOrFail();
        
        // Prevent editing owner
        if ($vendorUser->is_owner) {
            return back()->withErrors(['error' => 'Cannot edit owner account']);
        }
        
        $staffUser = User::findOrFail($vendorUser->user_id);
        
        // Validate request
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|digits:10|unique:users,mobile,' . $staffUser->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $staffUser->id,
            'role' => 'required|in:manager,staff,cashier',
            'is_active' => 'boolean',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update user
            $staffUser->update([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email ?? $staffUser->email,
            ]);
            
            // Update password if provided
            if ($request->filled('password')) {
                $request->validate([
                    'password' => 'min:6|confirmed',
                ]);
                
                $staffUser->update([
                    'password' => Hash::make($request->password),
                ]);
            }
            
            // Update pivot record
            $vendorUser->update([
                'role' => $request->role,
                'is_active' => $request->has('is_active') ? true : false,
            ]);
            
            DB::commit();
            
            return redirect()->route('vendor.staff.index')
                ->with('success', 'Staff user updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Failed to update staff user: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Remove the specified staff user
     */
    public function destroy($id)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Get the vendor_user pivot record
        $vendorUser = VendorUser::where('id', $id)
            ->where('vendor_id', $vendor->id)
            ->firstOrFail();
        
        // Prevent deleting owner
        if ($vendorUser->is_owner) {
            return back()->withErrors(['error' => 'Cannot remove owner account']);
        }
        
        // Delete the pivot record (removes access to this vendor)
        $vendorUser->delete();
        
        return redirect()->route('vendor.staff.index')
            ->with('success', 'Staff user removed successfully!');
    }
    
    /**
     * Toggle staff active status
     */
    public function toggleStatus($id)
    {
        $vendor = Auth::user()->currentVendor();
        
        if (!$vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }
        
        // Get the vendor_user pivot record
        $vendorUser = VendorUser::where('id', $id)
            ->where('vendor_id', $vendor->id)
            ->firstOrFail();
        
        // Prevent toggling owner
        if ($vendorUser->is_owner) {
            return back()->withErrors(['error' => 'Cannot change owner status']);
        }
        
        $vendorUser->update([
            'is_active' => !$vendorUser->is_active
        ]);
        
        $status = $vendorUser->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Staff user {$status} successfully!");
    }
}
