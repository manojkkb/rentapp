<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends ApiController
{
    use ResolvesApiVendor;

    public function index(Request $request): JsonResponse
    {
        $this->requirePermission('coupons.manage');
        $vendor = $this->vendor();

        $query = Coupon::query()->where('vendor_id', $vendor->id);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $coupons = $query->latest()->paginate($request->integer('per_page', 20));

        return $this->ok([
            'coupons' => $coupons->items(),
            'meta' => $this->paginationMeta($coupons),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requirePermission('coupons.manage');
        $vendor = $this->vendor();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:fixed,percent'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['boolean'],
        ]);

        $code = strtoupper(trim($validated['code']));
        if (Coupon::query()->where('vendor_id', $vendor->id)->where('code', $code)->exists()) {
            return $this->fail('Coupon code already exists.', 422);
        }

        $coupon = Coupon::query()->create([
            ...$validated,
            'code' => $code,
            'vendor_id' => $vendor->id,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return $this->ok(['coupon' => $coupon], 'Coupon created.', 201);
    }

    public function update(Request $request, Coupon $coupon): JsonResponse
    {
        $this->requirePermission('coupons.manage');
        abort_if($coupon->vendor_id !== $this->vendor()->id, 404);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'in:fixed,percent'],
            'value' => ['sometimes', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        $coupon->update($validated);

        return $this->ok(['coupon' => $coupon->fresh()], 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        $this->requirePermission('coupons.manage');
        abort_if($coupon->vendor_id !== $this->vendor()->id, 404);

        $coupon->delete();

        return $this->ok(null, 'Coupon deleted.');
    }
}
