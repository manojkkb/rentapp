<?php

namespace App\Http\Middleware;

use App\Support\VendorAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceVendorPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();
        $permission = VendorAccess::permissionForRoute($routeName);

        if ($permission === null && $routeName === 'vendor.orders.rental-status') {
            $permission = VendorAccess::permissionForRentalStatusRequest($request);
        }

        if ($permission === null) {
            return $next($request);
        }

        $access = VendorAccess::current();

        $allowed = $access && $access->can($permission);

        if (! $allowed && $routeName === 'vendor.orders.rental-status') {
            $fallback = $request->filled('delivered') || $request->filled('returned')
                ? 'orders.edit'
                : null;
            $allowed = $fallback && $access && $access->can($fallback);
        }

        if (! $allowed) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('vendor.permission_denied'),
                ], 403);
            }

            return redirect()
                ->route('vendor.home')
                ->withErrors(['error' => __('vendor.permission_denied')]);
        }

        return $next($request);
    }
}
