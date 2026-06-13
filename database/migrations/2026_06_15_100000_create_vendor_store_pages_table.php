<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_store_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('page_key', 32);
            $table->string('title', 255)->nullable();
            $table->longText('content')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['vendor_id', 'page_key']);
            $table->index(['vendor_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_store_pages');
    }
};
