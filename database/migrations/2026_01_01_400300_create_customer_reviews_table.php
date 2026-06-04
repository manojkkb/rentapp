<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('review')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->text('vendor_reply')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->unsignedInteger('helpful_count')->default(0);
            $table->timestampsTz();

            $table->unique('order_id');
            $table->index(['vendor_id', 'is_approved', 'created_at']);
            $table->index(['vendor_id', 'rating', 'is_approved']);
            $table->index(['vendor_id', 'replied_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_reviews');
    }
};
