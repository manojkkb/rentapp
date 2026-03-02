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
        Schema::create('category_activities', function (Blueprint $table) {
             $table->bigIncrements('id');

            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            $table->string('action', 50); 
            // created, updated, deleted, restored, status_changed

            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Performance indexes
            $table->index('category_id');
            $table->index('user_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_activities');
    }
};
