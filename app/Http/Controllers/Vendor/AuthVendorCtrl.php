<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\OtpVerification;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorUser;
use App\Services\VendorRoleProvisioner;
use App\Support\VendorAccess;
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
            $user = Auth::user();

            if ($user->vendor_id && $user->currentVendor()) {
                return redirect()->route('vendor.home');
            }

            return redirect()->route('vendor.select');
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
        
        $user = User::where('mobile', $request->mobile)->first();

        if (! $user || ! $user->password || ! Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('mobile'))
                ->withErrors(['mobile' => 'Invalid mobile number or password']);
        }

        Auth::login($user, $request->has('remember'));

        return $this->completeWebLogin($user);
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
                'email' => $request->mobile . '@rentkia.temp', // Temporary email using mobile number
                'password' => Hash::make(uniqid()), // Random password
            ]
        );
        
        Auth::login($user, true);

        return $this->completeJsonLogin($user);
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

        $memberships = VendorUser::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['vendor', 'vendorRole'])
            ->get()
            ->filter(fn (VendorUser $membership) => $membership->vendor !== null)
            ->sortByDesc('is_owner')
            ->values();

        if ($memberships->isEmpty()) {
            return redirect()->route('vendor.create');
        }

        $isSwitching = (bool) $user->vendor_id;

        return view('vendor.auth.select', compact('memberships', 'isSwitching'));
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
        
        $isSwitching = (bool) $user->vendor_id;

        $user->vendors()->updateExistingPivot($vendor->id, [
            'last_login_at' => now(),
        ]);

        $user->setCurrentVendorId($vendor->id);
        VendorAccess::flush();

        app(VendorRoleProvisioner::class)->ensureDefaultRoles($vendor, $user->id);

        Session::put('language', $vendor->language ?? $user->language ?? 'en');

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
        
        $request->validate([
            'name' => 'required|string|max:255',
            'business_category_id' => 'required|exists:business_categories,id',
            'owner_name' => 'nullable|string|max:255',
        ]);

        $language = $user->language ?: config('app.locale', 'en');
        
        // Create slug from name
        $slug = \Illuminate\Support\Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        
        // Ensure slug is unique
        while (Vendor::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'business_category_id' => $request->business_category_id,
            'owner_name' => $request->owner_name,
            'slug' => $slug,
            'language' => $language,
            'is_verified' => false,
            'is_active' => true,
            'rating' => 0,
            'total_reviews' => 0,
        ]);

        $user->update([
            'name' => $request->owner_name ?: $request->name,
        ]);
        
        // Add user to vendor_users pivot table as owner (full access; never role-gated)
        VendorUser::link($vendor->id, $user->id, [
            'is_owner' => true,
            'role' => 'owner',
            'last_login_at' => now(),
        ]);

        app(VendorRoleProvisioner::class)->ensureDefaultRoles($vendor, $user->id);
        
        $user->setCurrentVendorId($vendor->id);
        VendorAccess::flush();

        Session::put('language', $vendor->language ?? 'en');

        Session::forget(['vendor_id', 'vendor_user_id', 'vendor_name', 'vendor_email', 'vendor_mobile', 'vendor_auth_user_id', 'vendor_auth_mobile']);
        
        // Redirect to vendor home
        return redirect()->route('vendor.home')->with('success', 'Vendor profile created successfully! Welcome to ' . $vendor->name . '!');
    }

    /**
     * Ensure pivot rows exist for vendors this user owns directly.
     */
    private function syncOwnedVendorMemberships(User $user): void
    {
        $provisioner = app(VendorRoleProvisioner::class);

        foreach (Vendor::query()->where('user_id', $user->id)->get() as $vendor) {
            if (! $user->vendors()->where('vendors.id', $vendor->id)->exists()) {
                VendorUser::link($vendor->id, $user->id, [
                    'is_owner' => true,
                    'role' => 'owner',
                    'last_login_at' => now(),
                ]);
            }

            $provisioner->ensureDefaultRoles($vendor, $user->id);
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Vendor>
     */
    private function activeVendorsForUser(User $user)
    {
        $this->syncOwnedVendorMemberships($user);

        return $user->vendors()
            ->where('vendors.is_active', true)
            ->wherePivot('is_active', true)
            ->get();
    }

    private function completeWebLogin(User $user)
    {
        $vendors = $this->activeVendorsForUser($user);

        if ($vendors->isEmpty()) {
            return redirect()->route('vendor.create');
        }

        if ($vendors->count() === 1) {
            return $this->activateVendorSession($user, $vendors->first());
        }

        if ($user->vendor_id) {
            $defaultVendor = $vendors->firstWhere('id', $user->vendor_id);

            if ($defaultVendor && $defaultVendor->is_verified) {
                return $this->activateVendorSession($user, $defaultVendor);
            }
        }

        return redirect()->route('vendor.select');
    }

    private function completeJsonLogin(User $user)
    {
        $vendors = $this->activeVendorsForUser($user);

        if ($vendors->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'OTP verified. Please create your vendor profile.',
                'redirect' => route('vendor.create'),
            ]);
        }

        if ($vendors->count() === 1) {
            $vendor = $vendors->first();

            if (! $vendor->is_verified) {
                Auth::logout();

                return response()->json([
                    'success' => false,
                    'message' => 'Your vendor account is pending verification. Please contact support.',
                ], 403);
            }

            $user->setCurrentVendorId($vendor->id);
            $user->vendors()->updateExistingPivot($vendor->id, ['last_login_at' => now()]);
            Session::put('language', $vendor->language ?? $user->language ?? 'en');

            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'redirect' => route('vendor.home'),
            ]);
        }

        if ($user->vendor_id) {
            $defaultVendor = $vendors->firstWhere('id', $user->vendor_id);

            if ($defaultVendor && $defaultVendor->is_verified) {
                $user->setCurrentVendorId($defaultVendor->id);
                $user->vendors()->updateExistingPivot($defaultVendor->id, ['last_login_at' => now()]);
                Session::put('language', $defaultVendor->language ?? $user->language ?? 'en');

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful.',
                    'redirect' => route('vendor.home'),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified. Please select your vendor.',
            'redirect' => route('vendor.select'),
        ]);
    }

    private function activateVendorSession(User $user, Vendor $vendor)
    {
        if (! $vendor->is_verified) {
            Auth::logout();

            return back()->withErrors([
                'mobile' => 'Your vendor account is pending verification. Please contact support.',
            ]);
        }

        $user->vendors()->updateExistingPivot($vendor->id, ['last_login_at' => now()]);
        $user->setCurrentVendorId($vendor->id);
        Session::put('language', $vendor->language ?? $user->language ?? 'en');

        return redirect()
            ->route('vendor.home')
            ->with('success', 'Welcome back to '.$vendor->name.'!');
    }
}

