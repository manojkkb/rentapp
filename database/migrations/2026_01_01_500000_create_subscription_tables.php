<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 32);
            $table->string('billing_cycle', 32);
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->unsignedSmallInteger('duration_days');
            $table->boolean('is_trial')->default(false);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'is_trial']);
            $table->index(['type', 'billing_cycle']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->timestamp('start_date');
            $table->timestamp('expiry_date')->nullable();
            $table->string('status', 32)->default('active');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payment_gateway')->nullable();
            $table->string('payment_id')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();

            $table->index(['vendor_id', 'status', 'expiry_date']);
            $table->index(['user_id', 'vendor_id', 'status']);
            $table->index(['subscription_plan_id', 'status']);
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_gateway', 32);
            $table->string('payment_id')->unique();
            $table->string('status', 32)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->string('receipt_url')->nullable();
            $table->string('description')->nullable();
            $table->string('order_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['vendor_id', 'status', 'created_at']);
            $table->index(['subscription_id', 'status']);
            $table->index(['user_id', 'vendor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
