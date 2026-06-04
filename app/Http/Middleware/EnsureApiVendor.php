<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiVendor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Invalid token.'], 401);
        }

        if (! $user->vendor_id || ! $user->currentVendor()) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a vendor account.',
                'code' => 'vendor_required',
            ], 403);
        }

        $hasAccess = $user->vendors()
            ->where('vendors.id', $user->vendor_id)
            ->wherePivot('is_active', true)
            ->exists();

        if (! $hasAccess) {
            $user->update(['vendor_id' => null]);

            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this vendor.',
                'code' => 'vendor_access_denied',
            ], 403);
        }

        app()->setLocale($user->language ?? 'en');

        return $next($request);
    }
}
