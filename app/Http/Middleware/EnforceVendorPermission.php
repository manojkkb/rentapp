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
        $permission = VendorAccess::permissionForRoute($request->route()?->getName());

        if ($permission === null) {
            return $next($request);
        }

        $access = VendorAccess::current();

        if (! $access || ! $access->can($permission)) {
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
