<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_customers', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendor_customers', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_customers', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
