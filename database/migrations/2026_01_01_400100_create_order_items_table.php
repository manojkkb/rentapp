<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('rental_period', 32)->nullable();
            $table->decimal('billing_units', 12, 4)->nullable();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->unsignedInteger('rental_duration_minutes')->nullable();
            $table->unsignedSmallInteger('rent_days')->default(1);
            $table->decimal('total_price', 12, 2);
            $table->unsignedInteger('returned_qty')->default(0);
            $table->unsignedInteger('damaged_qty')->default(0);
            $table->unsignedInteger('lost_qty')->default(0);
            $table->string('rent_type', 32)->default('per_day');
            $table->decimal('security_deposit', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->decimal('damage_fee', 12, 2)->default(0);
            $table->decimal('lost_fee', 12, 2)->default(0);
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);
            $table->string('item_status', 32)->default('reserved');
            $table->string('condition_out', 20)->default('good');
            $table->string('condition_in', 20)->nullable();
            $table->text('damage_notes')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'item_id']);
            $table->index(['item_id', 'created_at']);
            $table->index(['order_id', 'item_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
