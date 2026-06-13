<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class VendorCouponController extends Controller
{
    private function vendor()
    {
        return Auth::user()->currentVendor();
    }

    private function authorizeCoupon(Coupon $coupon): void
    {
        $vendor = $this->vendor();

        if (! $vendor || $coupon->vendor_id !== $vendor->id) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        return view('vendor.coupons.index');
    }

    public function create()
    {
        $this->vendor();

        return view('vendor.coupons.create');
    }

    public function store(Request $request)
    {
        $vendor = $this->vendor();

        $validated = $this->validateCoupon($request);

        $code = strtoupper(trim($validated['code']));

        if (Coupon::where('vendor_id', $vendor->id)->where('code', $code)->exists()) {
            throw ValidationException::withMessages([
                'code' => [__('vendor.coupon_code_exists')],
            ]);
        }

        $coupon = Coupon::create([
            'vendor_id' => $vendor->id,
            'code' => $code,
            'name' => $validated['name'] ?? null,
            'type' => $validated['type'],
            'value' => $validated['value'],
            'min_order_amount' => $validated['min_order_amount'] ?? 0,
            'max_discount_amount' => $validated['max_discount_amount'] ?? null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'used_count' => 0,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('vendor.coupon_created'),
                'coupon' => $coupon,
            ]);
        }

        return redirect()
            ->route('vendor.coupons.show', $coupon)
            ->with('success', __('vendor.coupon_created'));
    }

    public function show(Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);

        $coupon->loadCount('orders');

        $usageStats = [
            'orders' => (int) $coupon->orders_count,
            'total_discount' => (float) $coupon->orders()->sum('coupon_discount'),
        ];

        return view('vendor.coupons.show', compact('coupon', 'usageStats'));
    }

    public function edit(Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);

        return view('vendor.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $vendor = $this->vendor();
        $this->authorizeCoupon($coupon);

        $validated = $this->validateCoupon($request);

        $code = strtoupper(trim($validated['code']));

        if (Coupon::where('vendor_id', $vendor->id)->where('code', $code)->where('id', '!=', $coupon->id)->exists()) {
            throw ValidationException::withMessages([
                'code' => [__('vendor.coupon_code_exists')],
            ]);
        }

        $coupon->update([
            'code' => $code,
            'name' => $validated['name'] ?? null,
            'type' => $validated['type'],
            'value' => $validated['value'],
            'min_order_amount' => $validated['min_order_amount'] ?? 0,
            'max_discount_amount' => $validated['max_discount_amount'] ?? null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('vendor.coupon_updated'),
                'coupon' => $coupon->fresh(),
            ]);
        }

        return redirect()
            ->route('vendor.coupons.show', $coupon)
            ->with('success', __('vendor.coupon_updated'));
    }

    public function destroy(Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);

        $coupon->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('vendor.coupon_deleted'),
            ]);
        }

        return redirect()
            ->route('vendor.coupons.index')
            ->with('success', __('vendor.coupon_deleted'));
    }

    public function toggleStatus(Coupon $coupon)
    {
        $this->authorizeCoupon($coupon);

        $coupon->update(['is_active' => ! $coupon->is_active]);

        $message = $coupon->is_active
            ? __('vendor.coupon_activated')
            : __('vendor.coupon_deactivated');

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_active' => $coupon->is_active,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateCoupon(Request $request): array
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'nullable|string|max:255',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'boolean',
        ]);

        if ($validated['type'] === 'percent' && (float) $validated['value'] > 100) {
            throw ValidationException::withMessages([
                'value' => [__('vendor.coupon_percent_max')],
            ]);
        }

        return $validated;
    }
}
