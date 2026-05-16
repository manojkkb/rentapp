<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vendor RBAC: global permission keys, per-vendor roles defined by the store owner,
     * and which permissions each role grants. Existing vendor_users.role / permissions JSON
     * remain until the application is switched to vendor_role_id.
     */
    public function up(): void
    {
        Schema::create('vendor_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group', 64)->nullable()->index();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 64);
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['vendor_id', 'slug']);
            $table->index(['vendor_id', 'sort_order']);
        });

        Schema::create('vendor_role_permission', function (Blueprint $table) {
            $table->foreignId('vendor_role_id')->constrained('vendor_roles')->cascadeOnDelete();
            $table->foreignId('vendor_permission_id')->constrained('vendor_permissions')->cascadeOnDelete();

            $table->primary(['vendor_role_id', 'vendor_permission_id']);
        });

        Schema::table('vendor_users', function (Blueprint $table) {
            $table->foreignId('vendor_role_id')
                ->nullable()
                ->after('role')
                ->constrained('vendor_roles')
                ->nullOnDelete();
            $table->index(['vendor_id', 'vendor_role_id']);
        });

        $now = now();
        $rows = [
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
        ];

        foreach ($rows as $row) {
            DB::table('vendor_permissions')->insert([
                'key' => $row['key'],
                'group' => $row['group'],
                'description' => $row['description'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_users', function (Blueprint $table) {
            $table->dropForeign(['vendor_role_id']);
            $table->dropColumn('vendor_role_id');
        });

        Schema::dropIfExists('vendor_role_permission');
        Schema::dropIfExists('vendor_roles');
        Schema::dropIfExists('vendor_permissions');
    }
};
