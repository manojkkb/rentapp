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
            $table->string('fulfillment_type', 20)->default('pickup')->after('end_time');
            $table->text('delivery_address')->nullable()->after('fulfillment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_carts', function (Blueprint $table) {
            $table->dropColumn(['fulfillment_type', 'delivery_address']);
        });
    }
};
