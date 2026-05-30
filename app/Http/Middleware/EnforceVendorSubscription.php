<?php

namespace App\Http\Middleware;

use App\Support\VendorSubscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceVendorSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, VendorSubscription::exemptRouteNames(), true)) {
            return $next($request);
        }

        $vendor = Auth::user()?->currentVendor();

        if (! $vendor || VendorSubscription::canAccess($vendor)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => __('vendor.subscription_expired_message'),
                'redirect' => route('vendor.subscription.plans'),
            ], 402);
        }

        return redirect()->route('vendor.subscription.plans');
    }
}
