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
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // English, Hindi
            $table->string('code', 10)->unique();  // en, hi
            $table->string('native_name')->nullable(); // हिन्दी
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->string('flag_icon')->nullable(); // e.g., country code for flag icons

            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
        //  add indexes for performance
        Schema::table('languages', function (Blueprint $table) {
            $table->index('code');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
