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
        Schema::create('vendor_carts', function (Blueprint $table) {
             $table->bigIncrements('id');

        // Who created the cart
        $table->foreignId('customer_id')
            ->constrained('vendor_customers')
            ->onDelete('cascade');

        // Which vendor this cart belongs to
        $table->foreignId('vendor_id')
            ->constrained()
            ->onDelete('cascade');

        // Cart name (example: Wedding Event, Birthday Party, Corporate Event)
        $table->string('cart_name');


        // Price summary
        $table->decimal('sub_total', 12, 2);
        $table->decimal('tax_total', 12, 2)->default(0);
        $table->decimal('discount_total', 12, 2)->default(0);

        $table->decimal('token_amount', 12, 2)->default(0); // advance paid
        $table->decimal('paid_amount', 12, 2)->default(0);  // total paid so far

        $table->decimal('grand_total', 12, 2);

        // Booking dates (important for rental)
        $table->dateTime('start_time')->nullable();
        $table->dateTime('end_time')->nullable();

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
        Schema::dropIfExists('vendor_carts');
    }
};
