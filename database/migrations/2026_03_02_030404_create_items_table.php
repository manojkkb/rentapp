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

            // Relationships
            $table->foreignId('vendor_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('category_id')
                ->constrained()
                ->onDelete('cascade');

            // Basic Info
            $table->string('name');
            $table->string('slug');
            $table->string('photo')->nullable();
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('price', 12, 2); // base price
            $table->enum('price_type', ['per_day', 'per_hour', 'fixed'])
                ->default('per_day');

            // Inventory
            $table->integer('stock')->default(1);
            $table->boolean('manage_stock')->default(true);

            // Availability
            $table->boolean('is_available')->default(true)->index();

            // Optional SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Status
            $table->boolean('is_active')->default(true)->index();

            $table->timestampsTz();
            $table->softDeletesTz();

            // Important indexes for performance
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
