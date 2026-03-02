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
        Schema::create('vendor_customers', function (Blueprint $table) {
             $table->bigIncrements('id');

            $table->foreignId('vendor_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');

            $table->string('name')->nullable();
            $table->string('mobile', 10)->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes (Important for large scale)
            $table->index('vendor_id');
            $table->index('mobile');

            $table->unique(['vendor_id', 'mobile']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_customers');
    }
};
