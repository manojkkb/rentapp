<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\Concerns\RedirectsIfNumericRouteKey;
use App\Models\CategoryActivity;
use App\Models\ItemActivity;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorRole;
use App\Models\VendorUser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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

        return view('vendor.staff.index');
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

                VendorUser::link($vendor->id, $user->id, [
                    'is_owner' => false,
                    'vendor_role_id' => $vendorRole->id,
                    'role' => $vendorRole->slug,
                    'is_active' => $request->boolean('is_active'),
                    'permissions' => [],
                ]);

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

            VendorUser::link($vendor->id, $user->id, [
                'is_owner' => false,
                'vendor_role_id' => $vendorRole->id,
                'role' => $vendorRole->slug,
                'is_active' => $request->boolean('is_active'),
                'permissions' => [],
            ]);

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

    public function show(Request $request, VendorUser $staff)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        $vendorUser = $this->authorizeStaff($vendor, $staff);

        if ($redirect = $this->redirectIfNumericRouteKey($request, $vendorUser, 'vendor.staff.show')) {
            return $redirect;
        }

        $staffUser = User::findOrFail($vendorUser->user_id);
        $activities = $this->staffActivities($vendor, $vendorUser);
        $activityStats = [
            'total' => $activities->count(),
            'logins' => $activities->where('type', 'session')->count(),
            'items' => $activities->where('type', 'item')->count(),
            'categories' => $activities->where('type', 'category')->count(),
        ];

        return view('vendor.staff.show', compact('vendor', 'vendorUser', 'staffUser', 'activities', 'activityStats'));
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

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function staffActivities(Vendor $vendor, VendorUser $vendorUser, int $limit = 100): Collection
    {
        $userId = $vendorUser->user_id;
        $activities = collect();

        $activities->push([
            'type' => 'membership',
            'action' => 'joined',
            'label' => __('vendor.staff_activity_joined'),
            'description' => __('vendor.staff_activity_joined_desc'),
            'meta' => null,
            'url' => null,
            'icon' => 'fa-user-plus',
            'tone' => 'emerald',
            'created_at' => $vendorUser->created_at,
        ]);

        $sessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->orderByDesc('last_activity')
            ->limit(30)
            ->get();

        foreach ($sessions as $session) {
            $activities->push([
                'type' => 'session',
                'action' => 'login',
                'label' => __('vendor.staff_activity_login'),
                'description' => $session->ip_address
                    ? __('vendor.staff_activity_login_from', ['ip' => $session->ip_address])
                    : __('vendor.staff_activity_login_desc'),
                'meta' => $this->shortUserAgent($session->user_agent ?? null),
                'url' => null,
                'icon' => 'fa-right-to-bracket',
                'tone' => 'teal',
                'created_at' => Carbon::createFromTimestamp((int) $session->last_activity),
            ]);
        }

        ItemActivity::query()
            ->where('user_id', $userId)
            ->whereHas('item', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->with(['item' => fn ($q) => $q->select('id', 'uuid', 'name', 'vendor_id')])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->each(function (ItemActivity $activity) use ($activities) {
                $activities->push([
                    'type' => 'item',
                    'action' => $activity->action,
                    'label' => $this->staffActivityLabel('item', $activity->action),
                    'description' => $activity->item?->name ?? '—',
                    'meta' => null,
                    'url' => $activity->item ? route('vendor.items.show', $activity->item) : null,
                    'icon' => 'fa-box',
                    'tone' => 'blue',
                    'created_at' => $activity->created_at,
                ]);
            });

        CategoryActivity::query()
            ->where('user_id', $userId)
            ->whereHas('category', fn ($q) => $q->where('vendor_id', $vendor->id))
            ->with(['category' => fn ($q) => $q->select('id', 'uuid', 'name', 'vendor_id')])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->each(function (CategoryActivity $activity) use ($activities) {
                $activities->push([
                    'type' => 'category',
                    'action' => $activity->action,
                    'label' => $this->staffActivityLabel('category', $activity->action),
                    'description' => $activity->category?->name ?? '—',
                    'meta' => null,
                    'url' => $activity->category ? route('vendor.categories.show', $activity->category) : null,
                    'icon' => 'fa-tag',
                    'tone' => 'amber',
                    'created_at' => $activity->created_at,
                ]);
            });

        return $activities
            ->filter(fn (array $row) => $row['created_at'] !== null)
            ->sortByDesc(fn (array $row) => $row['created_at'])
            ->take($limit)
            ->values();
    }

    private function staffActivityLabel(string $type, string $action): string
    {
        $key = "vendor.staff_activity_{$type}_{$action}";
        $translated = __($key);

        return $translated !== $key ? $translated : ucfirst(str_replace('_', ' ', $action));
    }

    private function shortUserAgent(?string $userAgent): ?string
    {
        if (! $userAgent) {
            return null;
        }

        if (strlen($userAgent) <= 72) {
            return $userAgent;
        }

        return substr($userAgent, 0, 69).'…';
    }
}
