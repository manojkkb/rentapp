<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\OtpVerification;
use App\Models\User;
use App\Models\Vendor;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AuthVendorCtrl extends Controller
{
    protected $otpService;
    
    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }
    /**
     * Show the vendor login form
     */
    public function loginForm()
    {
        // If already logged in, redirect appropriately
        if (Auth::check()) {
            $vendorId = session('current_vendor_id');
            
            if ($vendorId) {
                // User has selected a vendor, go to home
                return redirect()->route('vendor.home');
            } else {
                // User is authenticated but needs to select vendor
                return redirect()->route('vendor.select');
            }
        }
        
        return view('vendor.auth.login');
    }
    
    /**
     * Handle vendor login
     */
    public function login(Request $request)
    {
        // Validate the request
        $request->validate([
            'mobile' => 'required|digits:10',
            'password' => 'required|string|min:6',
        ], [
            'mobile.required' => 'Mobile number is required',
            'mobile.digits' => 'Mobile number must be exactly 10 digits',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
        ]);
        
        // Find user by mobile number
        $user = User::where('mobile', $request->mobile)
                    ->first();
        
        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('mobile'))
                ->withErrors(['mobile' => 'Invalid mobile number or password']);
        }
        
        // Get vendors this user has access to
        $vendors = $user->vendors()->where('is_active', true)->get();
        
        // Check if user has any active vendors
        if ($vendors->isEmpty()) {
            return back()
                ->withInput($request->only('mobile'))
                ->withErrors(['mobile' => 'No active vendor accounts found for this user']);
        }
        
        // Log in the user
        Auth::login($user, $request->has('remember'));
        
        // If user has only one vendor, select it automatically
        if ($vendors->count() === 1) {
            $vendor = $vendors->first();
            
            // Check if vendor is verified
            if (!$vendor->is_verified) {
                Auth::logout();
                return back()
                    ->withInput($request->only('mobile'))
                    ->withErrors(['mobile' => 'Your vendor account is pending verification. Please contact support.']);
            }
            
            // Update last login time
            $user->vendors()->updateExistingPivot($vendor->id, [
                'last_login_at' => now(),
            ]);
            
            // Store current vendor in session
            Session::put('current_vendor_id', $vendor->id);
            
            // Set language in session
            Session::put('language', $vendor->language ?? $user->language ?? 'en');
            
            // Redirect to vendor home/dashboard
            return redirect()
                ->route('vendor.home')
                ->with('success', 'Welcome back to ' . $vendor->name . '!');
        }
        
        // Multiple vendors - redirect to selection page
        return redirect()->route('vendor.select');
    }
    
    /**
     * Handle vendor logout
     */
    public function logout(Request $request)
    {
       
        Auth::logout(); // 🔥 Proper logout

        $request->session()->invalidate(); // Session destroy
        $request->session()->regenerateToken(); // CSRF regenerate

        return redirect()
            ->route('vendor.login')
            ->with('success', 'You have been logged out successfully');
    }
    
    /**
     * Send OTP to vendor's mobile number
     */
    public function sendOTP(Request $request)
    {
        // Validate mobile number
        $request->validate([
            'mobile' => 'required|digits:10',
        ]);

        $mobile = trim($request->mobile);
        
        // Send OTP using OtpService (stored in otp_verifications table)
        $otp = $this->otpService->sendOtp($mobile, 'phone');

        // Return response in format expected by frontend
        $response = [
            'success' => true,
            'message' => 'OTP sent successfully to your mobile number',
        ];

        // Show OTP in debug mode only
        if (config('app.debug')) {
            $response['otp'] = $otp;
        }

        return response()->json($response);
    }
    
    /**
     * Verify OTP and proceed to vendor selection
     */
    public function verifyOTP(Request $request)
    {
        // Validate request
        $request->validate([
            'mobile' => 'required|digits:10',
            'otp' => 'required|digits:6',
        ]);
        
        Log::info('Verify OTP Request Received', [
            'mobile' => $request->mobile,
            'otp' => $request->otp,
            'otp_length' => strlen($request->otp),
            'all_data' => $request->all()
        ]);
        
        // Verify OTP using OtpService (checks otp_verifications table)
        $isValid = $this->otpService->verifyOtp($request->mobile, $request->otp);
        
        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP. Please try again.'
            ], 400);
        }
        
        // OTP is verified successfully - NOW create or find user
        $user = User::firstOrCreate(
            [
                'mobile' => $request->mobile
            ],
            [
                'name' => 'Vendor User', // Temporary name, will be updated during vendor creation
                'email' => $request->mobile . '@rentapp.temp', // Temporary email using mobile number
                'password' => Hash::make(uniqid()), // Random password
            ]
        );
        
        // Log in the user
        Auth::login($user, true);
        
        // Get vendors where user is the owner (user_id column)
        $ownedVendors = Vendor::where('user_id', $user->id)->get();
        
        // Ensure vendor_users pivot entries exist for owned vendors
        foreach ($ownedVendors as $vendor) {
            if (!$user->vendors()->where('vendors.id', $vendor->id)->exists()) {
                $user->vendors()->attach($vendor->id, [
                    'is_owner' => true,
                    'role' => 'owner',
                    'is_active' => true,
                    'last_login_at' => now(),
                ]);
            }
        }
        
        // Check if user has any vendors (through vendor_users pivot)
        $vendors = $user->vendors()->get();
        
        if ($vendors->isEmpty()) {
            // No vendors found - redirect to create vendor
            return response()->json([
                'success' => true,
                'message' => 'OTP verified. Please create your vendor profile.',
                'redirect' => route('vendor.create')
            ]);
        } else {
            // Vendors exist - redirect to select vendor
            return response()->json([
                'success' => true,
                'message' => 'OTP verified. Please select your vendor.',
                'redirect' => route('vendor.select')
            ]);
        }
    }
    
    /**
     * Show vendor selection page
     */
    public function selectVendorForm()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('vendor.login')->withErrors(['error' => 'Please login first']);
        }
        
        $user = Auth::user();
        
        // Get vendors through vendor_users pivot
        $vendors = $user->vendors()->get();
        
        if ($vendors->isEmpty()) {
            return redirect()->route('vendor.create');
        }
        
        // Check if switching vendors (already logged in)
        $isSwitching = Session::has('current_vendor_id');
        
        return view('vendor.auth.select', compact('vendors', 'isSwitching'));
    }
    
    /**
     * Handle vendor selection
     */
    public function selectVendor(Request $request)
    {
        // Validate request
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
        ]);
        
        // Get authenticated user
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('vendor.login')->withErrors(['error' => 'Please login first']);
        }
        
        // Get the selected vendor
        $vendor = Vendor::find($request->vendor_id);
        
        if (!$vendor) {
            return back()->withErrors(['error' => 'Invalid vendor selection']);
        }
        
        // Check if user has access to this vendor
        $vendorUser = $user->vendors()->where('vendors.id', $vendor->id)->first();
        
        if (!$vendorUser) {
            return back()->withErrors(['error' => 'You do not have access to this vendor']);
        }
        
        // Check if switching vendors (already had current_vendor_id)
        $isSwitching = Session::has('current_vendor_id');
        
        // Update last login time in vendor_users pivot
        $user->vendors()->updateExistingPivot($vendor->id, [
            'last_login_at' => now(),
        ]);
        
        // Store only vendor ID in session
        Session::put('current_vendor_id', $vendor->id);
        
        // Set language in session
        Session::put('language', $vendor->language ?? $user->language ?? 'en');
        
        // Clear old session data
        Session::forget(['vendor_id', 'vendor_user_id', 'vendor_name', 'vendor_email', 'vendor_mobile', 'vendor_auth_user_id', 'vendor_auth_mobile']);
        
        // Redirect to vendor home with appropriate message
        $message = $isSwitching 
            ? 'Successfully switched to ' . $vendor->name 
            : 'Welcome back to ' . $vendor->name . '!';
            
        return redirect()->route('vendor.home')->with('success', $message);
    }
    
    /**
     * Show vendor creation form
     */
    public function createVendorForm()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('vendor.login')->withErrors(['error' => 'Please login first']);
        }
        
        // Get all business categories
        $categories = \App\Models\BusinessCategory::active()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
        
        return view('vendor.auth.create', compact('categories'));
    }
    
    /**
     * Store new vendor
     */
    public function storeVendor(Request $request)
    {
        // Get authenticated user
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('vendor.login')->withErrors(['error' => 'Please login first']);
        }
        
        // Validate request
        $request->validate([
            'name' => 'required|string|max:255',
            'business_category_id' => 'required|exists:business_categories,id',
            'owner_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:15',
            'language' => 'required|string|in:en,hi,bn,mr,te,ta,gu,ur,kn,or,ml,pa',
        ]);
        
        // Create slug from name
        $slug = \Illuminate\Support\Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        
        // Ensure slug is unique
        while (Vendor::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        // Create vendor
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'business_category_id' => $request->business_category_id,
            'owner_name' => $request->owner_name,
            'slug' => $slug,
            'city' => $request->city,
            'state' => $request->state,
            'gst_number' => $request->gst_number,
            'language' => $request->language,
            'is_verified' => false,
            'is_active' => true,
            'rating' => 0,
            'total_reviews' => 0,
        ]);
        
        // Update user's name and language
        $user->update([
            'name' => $request->owner_name ?: $request->name,
            'language' => $request->language,
        ]);
        
        // Add user to vendor_users pivot table as owner
        $user->vendors()->attach($vendor->id, [
            'is_owner' => true,
            'role' => 'owner',
            'is_active' => true,
            'last_login_at' => now(),
        ]);
        
        // Store only vendor ID in session
        Session::put('current_vendor_id', $vendor->id);
        
        // Set language in session
        Session::put('language', $vendor->language ?? 'en');
        
        // Clear old session data
        Session::forget(['vendor_id', 'vendor_user_id', 'vendor_name', 'vendor_email', 'vendor_mobile', 'vendor_auth_user_id', 'vendor_auth_mobile']);
        
        // Redirect to vendor home
        return redirect()->route('vendor.home')->with('success', 'Vendor profile created successfully! Welcome to ' . $vendor->name . '!');
    }
}

