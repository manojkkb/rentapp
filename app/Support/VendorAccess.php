<?php

namespace App\Support;

use App\Models\User;
use App\Models\VendorPermission;
use App\Models\VendorRole;
use App\Models\VendorUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class VendorAccess
{
    private static ?self $instance = null;

    /** @var Collection<int, string> */
    private Collection $permissions;

    private function __construct(
        private readonly VendorUser $membership,
    ) {
        $this->permissions = $this->resolvePermissionKeys();
    }

    public static function current(): ?self
    {
        if (! Auth::check()) {
            return null;
        }

        $vendorId = Auth::user()->vendor_id;
        if (! $vendorId) {
            return null;
        }

        if (
            static::$instance !== null
            && static::$instance->membership->vendor_id === (int) $vendorId
            && static::$instance->membership->user_id === Auth::id()
        ) {
            return static::$instance;
        }

        $membership = VendorUser::query()
            ->where('vendor_id', $vendorId)
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->with(['vendorRole.permissions'])
            ->first();

        if (! $membership) {
            static::$instance = null;

            return null;
        }

        static::$instance = new self($membership);

        return static::$instance;
    }

    public static function flush(): void
    {
        static::$instance = null;
    }

    public function isOwner(): bool
    {
        return (bool) $this->membership->is_owner;
    }

    public function can(string $permission): bool
    {
        if ($this->isOwner()) {
            return true;
        }

        return $this->permissions->contains($permission);
    }

    public function canAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    public function permissionKeys(): array
    {
        if ($this->isOwner()) {
            return VendorPermission::query()->orderBy('key')->pluck('key')->all();
        }

        return $this->permissions->values()->all();
    }

    /**
     * Resolve required permission for a named vendor route (null = no gate).
     */
    public static function permissionForRoute(?string $routeName): ?string
    {
        if (! $routeName || ! str_starts_with($routeName, 'vendor.')) {
            return null;
        }

        $alwaysAllowed = [
            'vendor.profile',
            'vendor.profile.update',
            'vendor.profile.update.personal',
            'vendor.profile.update.business',
            'vendor.language.switch',
            'vendor.logout',
            'vendor.manifest',
        ];

        if (in_array($routeName, $alwaysAllowed, true)) {
            return null;
        }

        if ($routeName === 'vendor.home' || $routeName === 'vendor.dashboard.stats') {
            return 'dashboard.view';
        }

        if (str_starts_with($routeName, 'vendor.staff-permissions.')) {
            return 'roles.manage';
        }

        if (str_starts_with($routeName, 'vendor.staff.')) {
            return match ($routeName) {
                'vendor.staff.index' => 'staff.view',
                'vendor.staff.create', 'vendor.staff.store' => 'staff.invite',
                'vendor.staff.destroy' => 'staff.remove',
                default => 'staff.edit',
            };
        }

        if ($routeName === 'vendor.deliveries.index') {
            return 'deliveries.view';
        }

        if ($routeName === 'vendor.returns.index') {
            return 'returns.view';
        }

        if ($routeName === 'vendor.orders.rental-status') {
            return null;
        }

        if (str_starts_with($routeName, 'vendor.support')) {
            return 'support.view';
        }

        if (str_starts_with($routeName, 'vendor.orders.create')) {
            return 'orders.create';
        }

        if (str_starts_with($routeName, 'vendor.orders.')) {
            if (in_array($routeName, ['vendor.orders.index', 'vendor.orders.show', 'vendor.orders.print', 'vendor.orders.invoice.download', 'vendor.orders.coupons.list'], true)) {
                return 'orders.view';
            }

            if ($routeName === 'vendor.orders.update-status') {
                return 'orders.cancel';
            }

            return 'orders.edit';
        }

        if (str_starts_with($routeName, 'vendor.items.')) {
            return match ($routeName) {
                'vendor.items.index', 'vendor.items.fetch', 'vendor.items.show' => 'items.view',
                'vendor.items.create', 'vendor.items.store', 'vendor.items.quick-store' => 'items.create',
                'vendor.items.destroy' => 'items.delete',
                default => 'items.edit',
            };
        }

        if (str_starts_with($routeName, 'vendor.categories.')) {
            return 'categories.manage';
        }

        if (str_starts_with($routeName, 'vendor.customers.')) {
            return match ($routeName) {
                'vendor.customers.index' => 'customers.view',
                default => 'customers.manage',
            };
        }

        if (str_starts_with($routeName, 'vendor.coupons.')) {
            return 'coupons.manage';
        }

        if (str_starts_with($routeName, 'vendor.reviews.')) {
            return 'reviews.view';
        }

        if (str_starts_with($routeName, 'vendor.calendar')) {
            return 'calendar.view';
        }

        if (str_starts_with($routeName, 'vendor.subscription.')) {
            return match ($routeName) {
                'vendor.subscription.plans' => 'settings.view',
                default => 'settings.edit',
            };
        }

        return null;
    }

    /**
     * Permission for rental-status PATCH (delivered vs returned actions).
     */
    public static function permissionForRentalStatusRequest(\Illuminate\Http\Request $request): ?string
    {
        if ($request->filled('delivered')) {
            return 'deliveries.manage';
        }

        if ($request->filled('returned')) {
            return 'returns.manage';
        }

        return 'orders.edit';
    }

    /** @return Collection<int, string> */
    private function resolvePermissionKeys(): Collection
    {
        if ($this->membership->is_owner) {
            return VendorPermission::query()->orderBy('key')->pluck('key');
        }

        $role = $this->membership->vendorRole;

        if (! $role && $this->membership->role) {
            $role = VendorRole::query()
                ->where('vendor_id', $this->membership->vendor_id)
                ->where('slug', $this->membership->role)
                ->with('permissions')
                ->first();
        }

        if ($role) {
            return $role->permissions->pluck('key');
        }

        $legacy = $this->membership->permissions;
        if (is_array($legacy) && $legacy !== []) {
            return collect($legacy);
        }

        return collect();
    }
}
