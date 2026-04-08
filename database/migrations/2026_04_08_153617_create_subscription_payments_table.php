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
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_gateway');
            $table->string('payment_id')->unique();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->timestamp('paid_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->string('receipt_url')->nullable();
            $table->string('description')->nullable();
            $table->string('order_id')->nullable();
            $table->jsonb('metadata')->nullable(); // For storing additional info from payment gateway
            $table->timestamps();

            // Indexes for faster lookups
            $table->index(['user_id', 'vendor_id']);
            $table->index('subscription_id');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
