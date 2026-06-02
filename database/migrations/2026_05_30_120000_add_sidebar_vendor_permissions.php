<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permissions for sidebar sections that were gated only via orders.* or open access.
     *
     * @var list<array{key: string, group: string, description: string}>
     */
    private const PERMISSIONS = [
        ['key' => 'deliveries.view', 'group' => 'logistics', 'description' => 'View deliveries list'],
        ['key' => 'deliveries.manage', 'group' => 'logistics', 'description' => 'Mark items or orders as delivered'],
        ['key' => 'returns.view', 'group' => 'logistics', 'description' => 'View returns list'],
        ['key' => 'returns.manage', 'group' => 'logistics', 'description' => 'Mark items or orders as returned'],
        ['key' => 'support.view', 'group' => 'general', 'description' => 'Access help and support chat'],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::PERMISSIONS as $row) {
            $exists = DB::table('vendor_permissions')->where('key', $row['key'])->exists();

            if ($exists) {
                continue;
            }

            DB::table('vendor_permissions')->insert([
                'key' => $row['key'],
                'group' => $row['group'],
                'description' => $row['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissionIds = DB::table('vendor_permissions')
            ->whereIn('key', array_column(self::PERMISSIONS, 'key'))
            ->pluck('id', 'key');

        $rolePermissionMap = [
            'manager' => ['deliveries.view', 'deliveries.manage', 'returns.view', 'returns.manage', 'support.view'],
            'staff' => ['deliveries.view', 'deliveries.manage', 'returns.view', 'returns.manage', 'support.view'],
            'cashier' => ['deliveries.view', 'returns.view', 'support.view'],
        ];

        foreach ($rolePermissionMap as $slug => $keys) {
            $roles = DB::table('vendor_roles')
                ->where('slug', $slug)
                ->where('is_system', true)
                ->pluck('id');

            $ids = collect($keys)
                ->map(fn (string $key) => $permissionIds[$key] ?? null)
                ->filter()
                ->values()
                ->all();

            foreach ($roles as $roleId) {
                foreach ($ids as $permissionId) {
                    DB::table('vendor_role_permission')->insertOrIgnore([
                        'vendor_role_id' => $roleId,
                        'vendor_permission_id' => $permissionId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $keys = array_column(self::PERMISSIONS, 'key');

        $permissionIds = DB::table('vendor_permissions')->whereIn('key', $keys)->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('vendor_role_permission')->whereIn('vendor_permission_id', $permissionIds)->delete();
            DB::table('vendor_permissions')->whereIn('key', $keys)->delete();
        }
    }
};
