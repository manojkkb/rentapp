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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Silver, Gold, Diamond
            $table->string('slug')->unique();

            $table->string('type'); // silver/gold/diamond
            $table->string('billing_cycle'); // monthly/yearly
           
            $table->decimal('price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->integer('duration_days');
            $table->boolean('is_trial')->default(false);

            // PostgreSQL JSONB (better performance than JSON)
            $table->jsonb('features')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->timestamps();

            // Indexes for faster lookups
            $table->index('slug');
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
