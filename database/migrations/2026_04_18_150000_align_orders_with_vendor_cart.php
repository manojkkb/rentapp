<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Snapshot cart fulfillment, discounts, coupon, security deposit, and payments on orders.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('fulfillment_type', 20)->default('pickup')->after('end_at');
            $table->text('delivery_address')->nullable()->after('fulfillment_type');
            $table->timestamp('pickup_at')->nullable()->after('delivery_address');
            $table->decimal('delivery_charge', 12, 2)->default(0)->after('pickup_at');

            $table->string('discount_type', 32)->nullable()->after('delivery_charge');
            $table->decimal('discount_value', 12, 2)->nullable()->after('discount_type');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_value');

            $table->foreignId('coupon_id')->nullable()->after('discount_amount')->constrained('coupons')->nullOnDelete();
            $table->string('coupon_code', 64)->nullable()->after('coupon_id');
            $table->decimal('coupon_discount', 12, 2)->default(0)->after('coupon_code');

            $table->decimal('security_deposit', 12, 2)->default(0)->after('coupon_discount');
            $table->string('security_deposit_type', 32)->default('none')->after('security_deposit');
            $table->decimal('security_deposit_value', 12, 2)->nullable()->after('security_deposit_type');

            $table->decimal('token_amount', 12, 2)->default(0)->after('security_deposit_value');
            $table->json('payment_detail')->nullable()->after('token_amount');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('price_type', 32)->nullable()->after('quantity');
            $table->decimal('billing_units', 12, 4)->nullable()->after('price_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['price_type', 'billing_units']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn([
                'fulfillment_type',
                'delivery_address',
                'pickup_at',
                'delivery_charge',
                'discount_type',
                'discount_value',
                'discount_amount',
                'coupon_id',
                'coupon_code',
                'coupon_discount',
                'security_deposit',
                'security_deposit_type',
                'security_deposit_value',
                'token_amount',
                'payment_detail',
            ]);
        });
    }
};
