<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * For time-based price types (per day, per hour, etc.): number of those units (e.g. days, hours).
     * For fixed price: stored as 1.
     */
    public function up(): void
    {
        Schema::table('vendor_cart_items', function (Blueprint $table) {
            $table->decimal('billing_units', 12, 2)->default(1)->after('price_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_cart_items', function (Blueprint $table) {
            $table->dropColumn('billing_units');
        });
    }
};
