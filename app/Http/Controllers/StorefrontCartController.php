<?php

namespace App\Http\Controllers;

use App\Models\Items;
use App\Support\StorefrontBooking;
use App\Support\StorefrontCart;
use App\Support\StorefrontContext;
use App\Support\StorefrontRentalPricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorefrontCartController extends Controller
{
    public function add(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            return $request->wantsJson()
                ? response()->json(['success' => false], 404)
                : redirect()->route('welcome');
        }

        $validated = $request->validate([
            'item_id' => ['required', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'item_variant_id' => ['nullable', 'integer'],
        ]);

        $booking = StorefrontBooking::forVendor($ctx->vendor->id);
        if (! $booking->isSet()) {
            $message = __('vendor.store_booking_required');
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message, 'needs_booking' => true], 422);
            }

            return back()->withErrors(['booking' => $message]);
        }

        $item = Items::query()
            ->where('vendor_id', $ctx->vendor->id)
            ->where('id', $validated['item_id'])
            ->active()
            ->available()
            ->with(['variants', 'variantAttributes'])
            ->firstOrFail();

        $variantId = isset($validated['item_variant_id']) ? (int) $validated['item_variant_id'] : null;
        if ($item->usesVariants()) {
            if (! $variantId) {
                $message = __('vendor.store_select_variant');
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message, 'needs_variant' => true], 422);
                }

                return back()->withErrors(['variant' => $message]);
            }
            $item->variants()
                ->where('id', $variantId)
                ->where('is_active', true)
                ->where('is_available', true)
                ->firstOrFail();
        } elseif ($variantId) {
            abort(422);
        }

        $cart = StorefrontCart::forVendor($ctx->vendor->id);
        $cart->add((int) $item->id, (int) ($validated['quantity'] ?? 1), $variantId ? (int) $variantId : null);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('vendor.store_added_to_cart'),
                'cart_count' => $cart->count(),
            ]);
        }

        return back()->with('success', __('vendor.store_added_to_cart'));
    }

    public function update(Request $request, string $slug, string $key): RedirectResponse
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            return redirect()->route('welcome');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $cart = StorefrontCart::forVendor($ctx->vendor->id);
        $cart->updateQuantity($key, (int) $validated['quantity']);

        return redirect()->route('storefront.cart', $slug);
    }

    public function remove(string $slug, string $key): RedirectResponse
    {
        $ctx = StorefrontContext::resolve($slug);
        if (! $ctx) {
            return redirect()->route('welcome');
        }

        StorefrontCart::forVendor($ctx->vendor->id)->remove($key);

        return redirect()->route('storefront.cart', $slug);
    }
}
