<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('item_code', 32);
            $table->string('slug');
            $table->string('photo')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('rental_period', 30)->default('per_day');
            $table->decimal('security_deposit', 12, 2)->default(0);
            $table->decimal('replacement_cost', 12, 2)->default(0);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->unsignedSmallInteger('min_rental_duration')->default(1);
            $table->unsignedSmallInteger('max_rental_duration')->default(90);
            $table->string('condition_status', 32)->default('good');
            $table->unsignedInteger('damaged_stock')->default(0);
            $table->unsignedInteger('maintenance_stock')->default(0);
            $table->integer('stock')->default(1);
            $table->boolean('manage_stock')->default(true);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('has_variants')->default(false);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['vendor_id', 'item_code']);
            $table->unique(['vendor_id', 'slug']);
            $table->index(['vendor_id', 'category_id']);
            $table->index(['vendor_id', 'is_active', 'is_available']);
            $table->index(['vendor_id', 'category_id', 'is_active']);
            $table->index(['vendor_id', 'has_variants']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
