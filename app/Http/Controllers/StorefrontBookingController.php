<?php

namespace App\Http\Controllers;

use App\Support\StorefrontBooking;
use App\Support\StorefrontContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorefrontBookingController extends Controller
{
    public function save(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Store not found'], 404)
                : redirect()->route('welcome');
        }

        try {
            $data = $request->validate([
                'start_time' => ['required', 'string'],
                'end_time' => ['required', 'string'],
            ]);
            StorefrontBooking::forVendor($ctx->vendor->id)->save($data);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => collect($e->errors())->flatten()->first(),
                    'errors' => $e->errors(),
                ], 422);
            }

            return back()->withInput()->withErrors($e->errors());
        }

        $booking = StorefrontBooking::forVendor($ctx->vendor->id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('vendor.store_booking_saved'),
                'booking' => $booking->presentation(),
                'billing_defaults' => $booking->billingDefaultsByPriceType(),
            ]);
        }

        return redirect()
            ->route('storefront.show', $slug)
            ->with('success', __('vendor.store_booking_saved'));
    }
}
