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
            $table->dateTime('pickup_at')->nullable()->after('delivery_address');
            $table->decimal('delivery_charge', 12, 2)->default(0)->after('pickup_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_carts', function (Blueprint $table) {
            $table->dropColumn(['pickup_at', 'delivery_charge']);
        });
    }
};
