<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('order_number', 32)->unique();
            $table->string('event_name')->nullable();
            $table->foreignId('customer_id')->constrained('vendor_customers')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('fulfillment_type', 20)->default('pickup');
            $table->text('delivery_address')->nullable();
            $table->timestamp('pickup_at')->nullable();
            $table->decimal('delivery_charge', 12, 2)->default(0);
            $table->string('discount_type', 32)->nullable();
            $table->decimal('discount_value', 12, 2)->nullable();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code', 64)->nullable();
            $table->decimal('coupon_discount', 12, 2)->default(0);
            $table->decimal('security_deposit', 12, 2)->default(0);
            $table->string('security_deposit_type', 32)->default('none');
            $table->decimal('security_deposit_value', 12, 2)->nullable();
            $table->decimal('token_amount', 12, 2)->default(0);
            $table->json('payment_detail')->nullable();
            $table->decimal('sub_total', 12, 2);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->decimal('extra_charges_total', 12, 2)->default(0);
            $table->json('extra_charges_lines')->nullable();
            $table->decimal('late_fees_total', 12, 2)->default(0);
            $table->decimal('damage_fees_total', 12, 2)->default(0);
            $table->decimal('lost_fees_total', 12, 2)->default(0);
            $table->decimal('refunds_total', 12, 2)->default(0);
            $table->text('internal_notes')->nullable();
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['vendor_id', 'status', 'created_at']);
            $table->index(['vendor_id', 'created_at']);
            $table->index(['vendor_id', 'customer_id']);
            $table->index(['vendor_id', 'status', 'delivered_at']);
            $table->index(['vendor_id', 'status', 'returned_at']);
            $table->index(['vendor_id', 'start_at']);
            $table->index(['vendor_id', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
