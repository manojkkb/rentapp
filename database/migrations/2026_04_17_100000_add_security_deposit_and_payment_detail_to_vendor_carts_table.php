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
            $table->decimal('security_deposit', 12, 2)
                ->default(0)
                ->comment('Required security deposit amount for this cart');

            $table->json('payment_detail')
                ->nullable()
                ->comment('Structured payment metadata (method, amounts, dates, etc.)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_carts', function (Blueprint $table) {
            $table->dropColumn(['security_deposit', 'payment_detail']);
        });
    }
};
