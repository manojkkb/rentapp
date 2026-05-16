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
        Schema::create('vendors', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('name');
            $table->string('owner_name')->nullable();
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->unsignedBigInteger('business_category_id')->nullable();

            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('gst_number')->nullable();
            $table->string('language', 10)->default('en');

            $table->boolean('is_verified')->default(false)->index();

            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_reviews')->default(0);

            $table->boolean('is_active')->default(true)->index();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['is_active', 'is_verified']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
