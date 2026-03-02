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
        Schema::create('order_items', function (Blueprint $table) {
             $table->bigIncrements('id');

            $table->foreignId('order_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('item_id')
                ->constrained()
                ->onDelete('cascade');

            // Snapshot data (VERY IMPORTANT)
            $table->string('item_name');
            $table->decimal('price', 12, 2);

            $table->integer('quantity')->default(1);

            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();

            $table->integer('rent_days')->default(1);

            $table->decimal('total_price', 12, 2);

            $table->timestamps();

            $table->index('order_id');
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
