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
        Schema::create('vendor_cart_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Belongs to vendor cart
            $table->foreignId('vendor_cart_id')
                ->constrained()
                ->onDelete('cascade');

            // Selected rental item
            $table->foreignId('item_id')
                ->constrained()
                ->onDelete('cascade');

            // Quantity
            $table->integer('quantity')->default(1);

            $table->timestamps();

            // Performance indexes
            $table->index('vendor_cart_id');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_cart_items');
    }
};
