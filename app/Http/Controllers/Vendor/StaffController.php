<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\Concerns\RedirectsIfNumericRouteKey;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorRole;
use App\Models\VendorUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    use RedirectsIfNumericRouteKey;

    public function index(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $query = VendorUser::query()
            ->where('vendor_id', $vendor->id)
            ->with(['user', 'vendorRole']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('mobile', 'like', '%'.$search.'%');
                })
                    ->orWhereHas('vendorRole', function ($roleQuery) use ($search) {
                        $roleQuery->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhere('role', 'like', '%'.$search.'%');
            });
        }

        $staff = $query->orderByDesc('created_at')->paginate(15);
        $roles = $this->vendorRoles($vendor);

        if ($request->ajax() || $request->wantsJson()) {
            return view('vendor.staff.partials.staff-list', compact('staff'))->render();
        }

        return view('vendor.staff.index', compact('staff', 'vendor', 'roles'));
    }

    public function create()
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $roles = $this->vendorRoles($vendor);

        return view('vendor.staff.create', compact('vendor', 'roles'));
    }

    public function store(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Please select a vendor'], 403);
            }

            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'mobile' => 'required|digits:10',
                'email' => 'nullable|email|max:255',
                'vendor_role_id' => [
                    'required',
                    Rule::exists('vendor_roles', 'id')->where('vendor_id', $vendor->id),
                ],
                'is_active' => 'boolean',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->validator->errors()->first(),
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }

        $vendorRole = $this->resolveVendorRole($vendor, (int) $request->vendor_role_id);

        DB::beginTransaction();

        try {
            $user = User::where('mobile', $request->mobile)->first();

            if ($user) {
                if ($vendor->users()->where('user_id', $user->id)->exists()) {
                    DB::rollBack();

                    $message = __('vendor.staff_already_exists');

                    if ($request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => $message], 422);
                    }

                    return back()->withInput()->withErrors(['mobile' => $message]);
                }

                $vendor->users()->attach($user->id, $this->pivotPayload($vendorRole, $request->boolean('is_active')));

                DB::commit();

                $message = __('vendor.staff_existing_user_added');

                if ($request->wantsJson()) {
                    return response()->json(['success' => true, 'message' => $message]);
                }

                return redirect()->route('vendor.staff.index')->with('success', $message);
            }

            $user = User::create([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email ?? $request->mobile.'@staff.temp',
                'password' => Hash::make('password123'),
            ]);

            $vendor->users()->attach($user->id, $this->pivotPayload($vendorRole, $request->boolean('is_active')));

            DB::commit();

            $message = __('vendor.staff_added_with_password');

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }

            return redirect()->route('vendor.staff.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('vendor.staff_create_failed', ['error' => $e->getMessage()]),
                ], 500);
            }

            return back()->withInput()
                ->withErrors(['error' => __('vendor.staff_create_failed', ['error' => $e->getMessage()])]);
        }
    }

    public function edit(Request $request, VendorUser $staff)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $vendorUser = $this->authorizeStaff($vendor, $staff);

        if ($redirect = $this->redirectIfNumericRouteKey($request, $vendorUser, 'vendor.staff.edit')) {
            return $redirect;
        }

        if ($vendorUser->is_owner) {
            return back()->withErrors(['error' => __('vendor.staff_owner_locked')]);
        }

        $staffUser = User::findOrFail($vendorUser->user_id);
        $roles = $this->vendorRoles($vendor);

        return view('vendor.staff.edit', compact('vendor', 'staffUser', 'vendorUser', 'roles'));
    }

    public function update(Request $request, VendorUser $staff)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $vendorUser = $this->authorizeStaff($vendor, $staff);

        if ($vendorUser->is_owner) {
            return back()->withErrors(['error' => __('vendor.staff_owner_locked')]);
        }

        $staffUser = User::findOrFail($vendorUser->user_id);

        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|digits:10|unique:users,mobile,'.$staffUser->id,
            'email' => 'nullable|email|max:255|unique:users,email,'.$staffUser->id,
            'vendor_role_id' => [
                'required',
                Rule::exists('vendor_roles', 'id')->where('vendor_id', $vendor->id),
            ],
            'is_active' => 'boolean',
        ]);

        $vendorRole = $this->resolveVendorRole($vendor, (int) $request->vendor_role_id);

        DB::beginTransaction();

        try {
            $staffUser->update([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email ?? $staffUser->email,
            ]);

            if ($request->filled('password')) {
                $request->validate([
                    'password' => 'min:6|confirmed',
                ]);

                $staffUser->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            $vendorUser->update([
                'vendor_role_id' => $vendorRole->id,
                'role' => $vendorRole->slug,
                'is_active' => $request->boolean('is_active'),
            ]);

            DB::commit();

            return redirect()->route('vendor.staff.index')
                ->with('success', __('vendor.staff_updated'));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withInput()
                ->withErrors(['error' => __('vendor.staff_update_failed', ['error' => $e->getMessage()])]);
        }
    }

    public function destroy(VendorUser $staff)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $vendorUser = $this->authorizeStaff($vendor, $staff);

        if ($vendorUser->is_owner) {
            return back()->withErrors(['error' => __('vendor.staff_owner_locked')]);
        }

        $vendorUser->delete();

        return redirect()->route('vendor.staff.index')
            ->with('success', __('vendor.staff_deleted'));
    }

    public function toggleStatus(VendorUser $staff)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $vendorUser = $this->authorizeStaff($vendor, $staff);

        if ($vendorUser->is_owner) {
            return back()->withErrors(['error' => __('vendor.staff_owner_locked')]);
        }

        $vendorUser->update([
            'is_active' => ! $vendorUser->is_active,
        ]);

        $status = $vendorUser->is_active ? __('vendor.activated') : __('vendor.deactivated');

        return back()->with('success', __('vendor.staff_status_changed', ['status' => $status]));
    }

    private function authorizeStaff(Vendor $vendor, VendorUser $staff): VendorUser
    {
        if ($staff->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access');
        }

        return $staff->loadMissing('vendorRole');
    }

    private function vendorRoles(Vendor $vendor)
    {
        return VendorRole::query()
            ->where('vendor_id', $vendor->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    private function resolveVendorRole(Vendor $vendor, int $vendorRoleId): VendorRole
    {
        return VendorRole::query()
            ->where('vendor_id', $vendor->id)
            ->findOrFail($vendorRoleId);
    }

    private function pivotPayload(VendorRole $role, bool $isActive): array
    {
        return [
            'is_owner' => false,
            'vendor_role_id' => $role->id,
            'role' => $role->slug,
            'is_active' => $isActive,
            'permissions' => json_encode([]),
        ];
    }
}
