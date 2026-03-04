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
        Schema::create('customer_reviews', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Who gave review
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Vendor being reviewed
            $table->foreignId('vendor_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Booking reference (important)
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->cascadeOnDelete()
                  ->unique();  // One review per booking

            $table->tinyInteger('rating') // 1–5
                  ->unsigned();

            $table->text('review')->nullable();

            // Review moderation
            $table->boolean('is_approved')->default(true);
            
            // Vendor response
            $table->text('vendor_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            
            // Engagement metrics
            $table->integer('helpful_count')->default(0);

            $table->timestampsTz();
            
            // Indexes for performance
            $table->index(['vendor_id', 'rating']);
            $table->index(['vendor_id', 'is_approved', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_reviews');
    }
};
