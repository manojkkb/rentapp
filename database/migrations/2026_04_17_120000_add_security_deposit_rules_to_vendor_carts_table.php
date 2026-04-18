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
            $table->string('security_deposit_type', 50)
                ->default('none')
                ->after('security_deposit');

            $table->decimal('security_deposit_value', 12, 2)
                ->nullable()
                ->after('security_deposit_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_carts', function (Blueprint $table) {
            $table->dropColumn(['security_deposit_type', 'security_deposit_value']);
        });
    }
};
