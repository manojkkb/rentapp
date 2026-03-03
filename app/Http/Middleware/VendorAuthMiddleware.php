<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class VendorAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated 
        if (!Auth::check()) {
            return redirect()->route('vendor.login')->withErrors([
                'error' => 'Please login to access the vendor dashboard'
            ]);
        }
        
        // Check if user has selected a vendor
        $vendorId = session('current_vendor_id');
        
        if (!$vendorId) {
            // User is authenticated but hasn't selected a vendor
            return redirect()->route('vendor.select')->withErrors([
                'error' => 'Please select a vendor to continue'
            ]);
        }
        
        // Verify user has access to this vendor via vendor_users table
        $user = Auth::user();
        $hasAccess = $user->vendors()
            ->where('vendors.id', $vendorId)
            ->wherePivot('is_active', true)
            ->exists();
        
        if (!$hasAccess) {
            // User doesn't have access to this vendor
            Session::forget('current_vendor_id');
            return redirect()->route('vendor.select')->withErrors([
                'error' => 'You do not have access to this vendor'
            ]);
        }
        
        // Set app locale based on session language
        $language = session('language', 'en');
        app()->setLocale($language);
        
        return $next($request);
    }
}


