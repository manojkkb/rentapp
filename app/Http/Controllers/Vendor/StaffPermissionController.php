<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorPermission;
use App\Models\VendorRole;
use App\Models\VendorUser;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class StaffPermissionController extends Controller
{
    private function vendor()
    {
        return Auth::user()->currentVendor();
    }

    private function ensureVendor()
    {
        $vendor = $this->vendor();

        if (! $vendor) {
            abort(redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]));
        }

        return $vendor;
    }

    private function permissionsGrouped(): Collection
    {
        return VendorPermission::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');
    }

    private function authorizeRole(VendorRole $role): VendorRole
    {
        $vendor = $this->ensureVendor();
        abort_unless($role->vendor_id === $vendor->id, 404);

        return $role;
    }

    public function index()
    {
        $vendor = $this->ensureVendor();

        $roles = VendorRole::query()
            ->where('vendor_id', $vendor->id)
            ->with('permissions')
            ->withCount(['permissions', 'vendorUsers'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $hasPermissionDefinitions = VendorPermission::query()->exists();

        return view('vendor.staff-permissions.index', compact('roles', 'hasPermissionDefinitions', 'vendor'));
    }

    public function create()
    {
        $vendor = $this->ensureVendor();
        $permissions = $this->permissionsGrouped();

        return view('vendor.staff-permissions.create', compact('permissions', 'vendor'));
    }

    public function store(Request $request)
    {
        $vendor = $this->ensureVendor();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:vendor_permissions,id',
        ]);

        $slug = $this->uniqueSlug($vendor->id, Str::slug($validated['name']));

        $role = VendorRole::create([
            'vendor_id' => $vendor->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'sort_order' => (int) VendorRole::where('vendor_id', $vendor->id)->max('sort_order') + 1,
            'created_by' => Auth::id(),
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()
            ->route('vendor.staff-permissions.show', $role)
            ->with('success', __('vendor.staff_permission_role_created'));
    }

    public function show(VendorRole $staffPermission)
    {
        $role = $this->authorizeRole($staffPermission);
        $role->load(['permissions', 'creator']);
        $role->loadCount('vendorUsers');

        $permissionsByGroup = $role->permissions
            ->sortBy(fn (VendorPermission $p) => $p->group.'_'.$p->key)
            ->groupBy('group');

        $staffMembers = VendorUser::query()
            ->where('vendor_id', $role->vendor_id)
            ->where('vendor_role_id', $role->id)
            ->with('user')
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->get();

        return view('vendor.staff-permissions.show', compact('role', 'permissionsByGroup', 'staffMembers'));
    }

    public function edit(VendorRole $staffPermission)
    {
        $role = $this->authorizeRole($staffPermission);
        $role->load('permissions');
        $role->loadCount('vendorUsers');
        $permissions = $this->permissionsGrouped();

        return view('vendor.staff-permissions.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, VendorRole $staffPermission)
    {
        $role = $this->authorizeRole($staffPermission);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:vendor_permissions,id',
        ]);

        $slug = $role->slug;
        if ($role->name !== $validated['name']) {
            $slug = $this->uniqueSlug($role->vendor_id, Str::slug($validated['name']), $role->id);
        }

        $role->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()
            ->route('vendor.staff-permissions.show', $role)
            ->with('success', __('vendor.staff_permission_role_updated'));
    }

    public function destroy(VendorRole $staffPermission)
    {
        $role = $this->authorizeRole($staffPermission);

        if ($role->is_system) {
            return back()->withErrors(['error' => __('vendor.staff_permission_system_role_locked')]);
        }

        if ($role->vendorUsers()->exists()) {
            return back()->withErrors(['error' => __('vendor.staff_permission_role_in_use')]);
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()
            ->route('vendor.staff-permissions.index')
            ->with('success', __('vendor.staff_permission_role_deleted'));
    }

    private function uniqueSlug(int $vendorId, string $base, ?int $exceptId = null): string
    {
        $slug = $base !== '' ? $base : 'role';
        $candidate = $slug;
        $i = 1;

        while (
            VendorRole::query()
                ->where('vendor_id', $vendorId)
                ->where('slug', $candidate)
                ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->exists()
        ) {
            $candidate = $slug.'-'.$i;
            $i++;
        }

        return $candidate;
    }
}
