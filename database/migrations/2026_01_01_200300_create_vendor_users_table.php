<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_owner')->default(false);
            $table->string('role', 32)->nullable();
            $table->foreignId('vendor_role_id')->nullable()->constrained('vendor_roles')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();

            $table->unique(['vendor_id', 'user_id']);
            $table->index(['user_id', 'is_active']);
            $table->index(['vendor_id', 'is_active']);
            $table->index(['vendor_id', 'vendor_role_id']);
            $table->index(['vendor_id', 'is_owner']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_users');
    }
};
