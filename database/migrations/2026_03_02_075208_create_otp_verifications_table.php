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
        Schema::create('otp_verifications', function (Blueprint $table) {
           
           $table->bigIncrements('id');

            // Phone number or Email
            $table->string('identifier', 191);

            // phone | email
            $table->enum('identifier_type', ['phone', 'email']);

            // OTP (store as string to preserve leading zeros)
            $table->string('otp', 10);

            // Expiry time (Recommended: 5 minutes)
            $table->timestampTz('expires_at')->index();

            // Failed attempts counter
            $table->unsignedSmallInteger('attempts')->default(0);

            // Mark when verified
            $table->timestampTz('verified_at')->nullable();

            $table->timestampsTz();

            // 🔥 Critical Composite Index for Fast Lookup
            $table->index(['identifier', 'identifier_type', 'expires_at'], 'otp_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};
