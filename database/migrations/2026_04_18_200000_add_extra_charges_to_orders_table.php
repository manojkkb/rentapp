<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('extra_charges_total', 12, 2)->default(0)->after('grand_total');
            $table->json('extra_charges_lines')->nullable()->after('extra_charges_total');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['extra_charges_total', 'extra_charges_lines']);
        });
    }
};
