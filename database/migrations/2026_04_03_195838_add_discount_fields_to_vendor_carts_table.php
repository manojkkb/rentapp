<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendor_carts', function (Blueprint $table) {
            $table->string('discount_type', 10)->nullable()->after('discount_total');
            $table->decimal('discount_value', 12, 2)->nullable()->after('discount_type');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_value');
            $table->decimal('coupon_discount', 12, 2)->default(0)->after('coupon_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_carts', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value', 'discount_amount', 'coupon_discount']);
        });
    }
};
