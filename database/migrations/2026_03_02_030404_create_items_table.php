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
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('vendor_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('category_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('name');
            $table->string('slug');
            $table->string('photo')->nullable();
            $table->text('description')->nullable();

            $table->decimal('price', 12, 2);
            $table->string('price_type', 30)->default('per_day');

            $table->decimal('security_deposit', 12, 2)->default(0);
            $table->decimal('replacement_cost', 12, 2)->default(0);
            $table->decimal('late_fee_per_day', 12, 2)->default(0);
            $table->boolean('is_damage_protection')->default(false);

            $table->unsignedInteger('minimum_rental_duration')->default(1)->comment('Days');
            $table->unsignedInteger('maximum_rental_duration')->default(90)->comment('Days');

            $table->decimal('weight', 10, 3)->default(0)->comment('kg');
            $table->decimal('dimension_length', 10, 2)->default(0)->comment('cm');
            $table->decimal('dimension_width', 10, 2)->default(0)->comment('cm');
            $table->decimal('dimension_height', 10, 2)->default(0)->comment('cm');

            $table->string('condition_status', 32)->default('good');

            $table->unsignedInteger('total_stock')->default(1);
            $table->unsignedInteger('available_stock')->default(1);
            $table->unsignedInteger('rented_stock')->default(0);
            $table->unsignedInteger('damaged_stock')->default(0);
            $table->unsignedInteger('maintenance_stock')->default(0);

            $table->integer('stock')->default(1);
            $table->boolean('manage_stock')->default(true);

            $table->boolean('is_available')->default(true)->index();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['vendor_id', 'category_id']);
            $table->unique(['vendor_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
