<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Models\VendorRole;
use App\Models\VendorUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends ApiController
{
    use ResolvesApiVendor;

    public function index(Request $request): JsonResponse
    {
        $this->requirePermission('staff.view');
        $vendor = $this->vendor();

        $query = VendorUser::query()
            ->where('vendor_id', $vendor->id)
            ->with(['user:id,name,mobile,email', 'vendorRole:id,name']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('mobile', 'like', "%{$search}%"))
                    ->orWhereHas('vendorRole', fn ($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        $staff = $query->latest()->paginate($request->integer('per_page', 20));

        return $this->ok([
            'staff' => $staff->getCollection()->map(fn (VendorUser $member) => [
                'id' => $member->id,
                'user_id' => $member->user_id,
                'name' => $member->user?->name,
                'mobile' => $member->user?->mobile,
                'email' => $member->user?->email,
                'role' => $member->vendorRole?->name ?? $member->role,
                'is_owner' => (bool) $member->is_owner,
                'is_active' => (bool) $member->is_active,
            ]),
            'meta' => $this->paginationMeta($staff),
            'roles' => VendorRole::query()->where('vendor_id', $vendor->id)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function toggle(VendorUser $staff): JsonResponse
    {
        $this->requirePermission('staff.edit');
        abort_if($staff->vendor_id !== $this->vendor()->id, 404);
        abort_if($staff->is_owner, 422, 'Cannot deactivate the owner account.');

        $staff->update(['is_active' => ! $staff->is_active]);

        return $this->ok(['staff' => $staff->fresh()], 'Staff status updated.');
    }
}
