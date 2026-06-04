<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<array{key: string, group: string, description: string}>
     */
    private const PERMISSIONS = [
        ['key' => 'dashboard.view', 'group' => 'general', 'description' => 'View vendor dashboard'],
        ['key' => 'items.view', 'group' => 'catalog', 'description' => 'View items'],
        ['key' => 'items.create', 'group' => 'catalog', 'description' => 'Create items'],
        ['key' => 'items.edit', 'group' => 'catalog', 'description' => 'Edit items'],
        ['key' => 'items.delete', 'group' => 'catalog', 'description' => 'Delete items'],
        ['key' => 'categories.manage', 'group' => 'catalog', 'description' => 'Manage categories'],
        ['key' => 'orders.view', 'group' => 'orders', 'description' => 'View orders'],
        ['key' => 'orders.create', 'group' => 'orders', 'description' => 'Create orders'],
        ['key' => 'orders.edit', 'group' => 'orders', 'description' => 'Edit orders'],
        ['key' => 'orders.cancel', 'group' => 'orders', 'description' => 'Cancel orders'],
        ['key' => 'deliveries.view', 'group' => 'logistics', 'description' => 'View deliveries list'],
        ['key' => 'deliveries.manage', 'group' => 'logistics', 'description' => 'Mark items or orders as delivered'],
        ['key' => 'returns.view', 'group' => 'logistics', 'description' => 'View returns list'],
        ['key' => 'returns.manage', 'group' => 'logistics', 'description' => 'Mark items or orders as returned'],
        ['key' => 'customers.view', 'group' => 'customers', 'description' => 'View customers'],
        ['key' => 'customers.manage', 'group' => 'customers', 'description' => 'Manage customers'],
        ['key' => 'staff.view', 'group' => 'staff', 'description' => 'View staff list'],
        ['key' => 'staff.invite', 'group' => 'staff', 'description' => 'Invite or add staff'],
        ['key' => 'staff.edit', 'group' => 'staff', 'description' => 'Edit staff roles and status'],
        ['key' => 'staff.remove', 'group' => 'staff', 'description' => 'Remove staff from vendor'],
        ['key' => 'roles.manage', 'group' => 'staff', 'description' => 'Create and edit vendor roles and permissions'],
        ['key' => 'calendar.view', 'group' => 'calendar', 'description' => 'View booking calendar'],
        ['key' => 'coupons.manage', 'group' => 'marketing', 'description' => 'Manage coupons'],
        ['key' => 'reviews.view', 'group' => 'marketing', 'description' => 'View reviews'],
        ['key' => 'settings.view', 'group' => 'settings', 'description' => 'View vendor settings'],
        ['key' => 'settings.edit', 'group' => 'settings', 'description' => 'Edit vendor settings'],
        ['key' => 'reports.view', 'group' => 'reports', 'description' => 'View reports and analytics'],
        ['key' => 'support.view', 'group' => 'general', 'description' => 'Access help and support chat'],
    ];

    public function up(): void
    {
        Schema::create('vendor_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group', 64)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index('group');
        });

        Schema::create('vendor_roles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 64);
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['vendor_id', 'slug']);
            $table->index(['vendor_id', 'is_system', 'sort_order']);
        });

        Schema::create('vendor_role_permission', function (Blueprint $table) {
            $table->foreignId('vendor_role_id')->constrained('vendor_roles')->cascadeOnDelete();
            $table->foreignId('vendor_permission_id')->constrained('vendor_permissions')->cascadeOnDelete();

            $table->primary(['vendor_role_id', 'vendor_permission_id']);
            $table->index('vendor_permission_id');
        });

        $now = now();
        foreach (self::PERMISSIONS as $row) {
            DB::table('vendor_permissions')->insert([
                'key' => $row['key'],
                'group' => $row['group'],
                'description' => $row['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_role_permission');
        Schema::dropIfExists('vendor_roles');
        Schema::dropIfExists('vendor_permissions');
    }
};
