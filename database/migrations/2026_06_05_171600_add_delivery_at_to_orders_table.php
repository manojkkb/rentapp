<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'delivery_at')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('delivery_at')->nullable()->after('pickup_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('orders', 'delivery_at')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_at');
        });
    }
};
