<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\VendorPermission;
use App\Models\VendorRole;
use Illuminate\Http\Request;
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

    public function index()
    {
        $vendor = $this->vendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        $roles = VendorRole::query()
            ->where('vendor_id', $vendor->id)
            ->with('permissions')
            ->withCount(['permissions', 'vendorUsers'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $permissions = VendorPermission::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('vendor.staff-permissions.index', compact('roles', 'permissions', 'vendor'));
    }

    public function store(Request $request)
    {
        $vendor = $this->vendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

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
            ->route('vendor.staff-permissions.index')
            ->with('success', __('vendor.staff_permission_role_created'));
    }

    public function update(Request $request, VendorRole $staffPermission)
    {
        $vendor = $this->vendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        abort_unless($staffPermission->vendor_id === $vendor->id, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:vendor_permissions,id',
        ]);

        $slug = $staffPermission->slug;
        if ($staffPermission->name !== $validated['name']) {
            $slug = $this->uniqueSlug($vendor->id, Str::slug($validated['name']), $staffPermission->id);
        }

        $staffPermission->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        $staffPermission->permissions()->sync($validated['permissions'] ?? []);

        return redirect()
            ->route('vendor.staff-permissions.index')
            ->with('success', __('vendor.staff_permission_role_updated'));
    }

    public function destroy(VendorRole $staffPermission)
    {
        $vendor = $this->vendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => __('vendor.select_vendor_first')]);
        }

        abort_unless($staffPermission->vendor_id === $vendor->id, 404);

        if ($staffPermission->is_system) {
            return back()->withErrors(['error' => __('vendor.staff_permission_system_role_locked')]);
        }

        if ($staffPermission->vendorUsers()->exists()) {
            return back()->withErrors(['error' => __('vendor.staff_permission_role_in_use')]);
        }

        $staffPermission->permissions()->detach();
        $staffPermission->delete();

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
