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
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('order_number')->unique(); // invoice reference

            $table->foreignId('customer_id')
                ->constrained('vendor_customers')
                ->onDelete('cascade');

            $table->foreignId('vendor_id')
                ->constrained()
                ->onDelete('cascade');

            // Rental duration (date + time)
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();

            // Financial summary
            $table->decimal('sub_total', 12, 2);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);

            // Payment tracking
            $table->decimal('paid_amount', 12, 2)->default(0);

            $table->enum('status', [
                'pending',
                'confirmed',
                'ongoing',
                'cancelled',
                'completed',
            ])->default('pending')->index();

            $table->timestamps();
            $table->softDeletes();

            // Performance indexes
            $table->index('customer_id');
            $table->index('vendor_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
