<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_variants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('variant_code', 32);
            $table->string('name')->nullable();
            $table->json('attributes')->nullable();
            $table->string('photo')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('security_deposit', 12, 2)->default(0);
            $table->decimal('replacement_cost', 12, 2)->default(0);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->string('condition_status', 32)->default('good');
            $table->unsignedInteger('damaged_stock')->default(0);
            $table->unsignedInteger('maintenance_stock')->default(0);
            $table->integer('stock')->default(1);
            $table->boolean('manage_stock')->default(true);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unique(['item_id', 'variant_code']);
            $table->index(['item_id', 'is_active', 'is_available']);
            $table->index(['item_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_variants');
    }
};
