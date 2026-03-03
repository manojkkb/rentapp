<?php

use Illuminate\Container\Attributes\DB;
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
        Schema::create('categories', function (Blueprint $table) {
             $table->bigIncrements('id');

            $table->foreignId('vendor_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->onDelete('cascade');

            $table->string('name');
            $table->string('slug');

            $table->boolean('is_active')->default(true)->index();

            $table->timestampsTz();

            // ✅ Correct unique constraint
            $table->unique(['vendor_id', 'slug', 'parent_id']);

     
        });
        
             


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
