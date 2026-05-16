<?php

namespace App\Services;

use App\Models\Vendor;
use App\Models\VendorPermission;
use App\Models\VendorRole;
use Illuminate\Support\Collection;

class VendorRoleProvisioner
{
    /**
     * Default system roles created for every new vendor.
     *
     * @return array<string, array{name: string, description: string, sort_order: int, permissions: list<string>}>
     */
    public static function defaultRoleDefinitions(): array
    {
        return [
            'manager' => [
                'name' => 'Manager',
                'description' => 'Full store operations except owner-only settings',
                'sort_order' => 1,
                'permissions' => [
                    'dashboard.view',
                    'items.view', 'items.create', 'items.edit', 'items.delete',
                    'categories.manage',
                    'orders.view', 'orders.create', 'orders.edit', 'orders.cancel',
                    'customers.view', 'customers.manage',
                    'staff.view', 'staff.invite', 'staff.edit',
                    'calendar.view',
                    'coupons.manage',
                    'reviews.view',
                    'settings.view',
                    'reports.view',
                ],
            ],
            'staff' => [
                'name' => 'Staff',
                'description' => 'Day-to-day rentals and catalog',
                'sort_order' => 2,
                'permissions' => [
                    'dashboard.view',
                    'items.view',
                    'orders.view', 'orders.create', 'orders.edit',
                    'customers.view',
                    'calendar.view',
                    'reviews.view',
                ],
            ],
            'cashier' => [
                'name' => 'Cashier',
                'description' => 'Orders and customers at the counter',
                'sort_order' => 3,
                'permissions' => [
                    'dashboard.view',
                    'orders.view', 'orders.create',
                    'customers.view',
                ],
            ],
        ];
    }

    /**
     * Ensure the three default roles exist for a vendor (idempotent).
     */
    public function ensureDefaultRoles(Vendor $vendor, ?int $createdBy = null): void
    {
        $permissionIds = $this->permissionIdsByKey();

        foreach (self::defaultRoleDefinitions() as $slug => $definition) {
            $role = VendorRole::query()->firstOrCreate(
                [
                    'vendor_id' => $vendor->id,
                    'slug' => $slug,
                ],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'is_system' => true,
                    'sort_order' => $definition['sort_order'],
                    'created_by' => $createdBy,
                ]
            );

            $ids = collect($definition['permissions'])
                ->map(fn (string $key) => $permissionIds->get($key))
                ->filter()
                ->values()
                ->all();

            if ($role->wasRecentlyCreated || $role->permissions()->count() === 0) {
                $role->permissions()->sync($ids);
            }
        }
    }

    /**
     * @return Collection<string, int>
     */
    private function permissionIdsByKey(): Collection
    {
        return VendorPermission::query()->pluck('id', 'key');
    }
}
