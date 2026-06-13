<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('India');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['vendor_id', 'is_active']);
        });

        Schema::table('vendor_store_settings', function (Blueprint $table) {
            $table->foreign('primary_location_id')
                ->references('id')
                ->on('vendor_locations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_store_settings', function (Blueprint $table) {
            $table->dropForeign(['primary_location_id']);
        });

        Schema::dropIfExists('vendor_locations');
    }
};
