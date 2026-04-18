<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Replaces enum / constrained price_type with VARCHAR so all billing periods are allowed.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('price_type_new', 30)->default('per_day');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('UPDATE items SET price_type_new = price_type::text');
        } else {
            DB::statement('UPDATE items SET price_type_new = price_type');
        }

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('price_type');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->renameColumn('price_type_new', 'price_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank: reverting to a 3-value enum is driver-specific.
    }
};
