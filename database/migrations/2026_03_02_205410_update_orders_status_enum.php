<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old check constraint and create a new one with all status values
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status::text = ANY (ARRAY['pending'::character varying, 'confirmed'::character varying, 'ongoing'::character varying, 'cancelled'::character varying, 'completed'::character varying]::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status::text = ANY (ARRAY['confirmed'::character varying, 'ongoing'::character varying, 'cancelled'::character varying, 'completed'::character varying]::text[]))");
    }
};
