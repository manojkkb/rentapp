<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        
        $user = Auth::user();
        $vendorId = $user->vendor_id;

        if (! $vendorId) {
            return redirect()->route('vendor.select')->withErrors([
                'error' => 'Please select a vendor to continue',
            ]);
        }

        $hasAccess = $user->vendors()
            ->where('vendors.id', $vendorId)
            ->wherePivot('is_active', true)
            ->exists();

        if (! $hasAccess) {
            $user->update(['vendor_id' => null]);

            return redirect()->route('vendor.select')->withErrors([
                'error' => 'You do not have access to this vendor',
            ]);
        }
        
        // Set app locale based on session language
        $language = session('language', 'en');
        app()->setLocale($language);
        
        return $next($request);
    }
}


