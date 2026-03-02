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
        Schema::create('vendor_users', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('vendor_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            $table->boolean('is_owner')->default(false);
            $table->string('role')->nullable(); // e.g., admin, manager, staff
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
            $table->unique(['vendor_id', 'user_id']);
            $table->index('vendor_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_users');
    }
};
