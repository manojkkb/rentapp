<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorCouponController extends Controller
{
    private function vendor()
    {
        return Auth::user()->currentVendor();
    }

    public function index(Request $request)
    {
        $vendor = $this->vendor();
        $query = Coupon::where('vendor_id', $vendor->id);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $coupons = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('vendor.coupons._list', compact('coupons'))->render(),
                'total' => $coupons->total(),
            ]);
        }

        return view('vendor.coupons.index', compact('coupons'));
    }

    public function store(Request $request)
    {
        $vendor = $this->vendor();

        $request->validate([
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

        $code = strtoupper(trim($request->code));

        if (Coupon::where('vendor_id', $vendor->id)->where('code', $code)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon code already exists',
                'errors' => ['code' => ['This coupon code already exists']],
            ], 422);
        }

        if ($request->type === 'percent' && $request->value > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Percentage cannot exceed 100%',
                'errors' => ['value' => ['Percentage cannot exceed 100%']],
            ], 422);
        }

        $coupon = Coupon::create([
            'vendor_id' => $vendor->id,
            'code' => $code,
            'name' => $request->name,
            'type' => $request->type,
            'value' => $request->value,
            'min_order_amount' => $request->min_order_amount ?? 0,
            'max_discount_amount' => $request->max_discount_amount,
            'usage_limit' => $request->usage_limit,
            'used_count' => 0,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon created successfully!',
            'coupon' => $coupon,
        ]);
    }

    public function show(Coupon $coupon)
    {
        $vendor = $this->vendor();
        if ($coupon->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'coupon' => $coupon,
        ]);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $vendor = $this->vendor();
        if ($coupon->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
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

        $code = strtoupper(trim($request->code));

        if (Coupon::where('vendor_id', $vendor->id)->where('code', $code)->where('id', '!=', $coupon->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon code already exists',
                'errors' => ['code' => ['This coupon code already exists']],
            ], 422);
        }

        if ($request->type === 'percent' && $request->value > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Percentage cannot exceed 100%',
                'errors' => ['value' => ['Percentage cannot exceed 100%']],
            ], 422);
        }

        $coupon->update([
            'code' => $code,
            'name' => $request->name,
            'type' => $request->type,
            'value' => $request->value,
            'min_order_amount' => $request->min_order_amount ?? 0,
            'max_discount_amount' => $request->max_discount_amount,
            'usage_limit' => $request->usage_limit,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon updated successfully!',
            'coupon' => $coupon->fresh(),
        ]);
    }

    public function destroy(Coupon $coupon)
    {
        $vendor = $this->vendor();
        if ($coupon->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupon deleted successfully!',
        ]);
    }

    public function toggleStatus(Coupon $coupon)
    {
        $vendor = $this->vendor();
        if ($coupon->vendor_id !== $vendor->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'success' => true,
            'message' => $coupon->is_active ? 'Coupon activated' : 'Coupon deactivated',
            'is_active' => $coupon->is_active,
        ]);
    }
}
