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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();

            $table->timestamp('start_date');
            $table->timestamp('expiry_date')->nullable();

            $table->string('status')->default('active'); 
            // active, expired, cancelled, trial

            $table->decimal('amount', 10, 2)->default(0);

            $table->string('payment_gateway')->nullable();
            $table->string('payment_id')->nullable();

            $table->boolean('auto_renew')->default(true);

            $table->timestamps();
            // Indexes for faster lookups
            $table->index(['user_id', 'vendor_id']);
            $table->index('subscription_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
