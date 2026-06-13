<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_store_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_published')->default(false);
            $table->string('tagline', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('business_phone', 20)->nullable();
            $table->string('whatsapp_number', 20)->nullable();
            $table->boolean('whatsapp_enabled')->default(true);
            $table->string('business_email', 255)->nullable();
            $table->string('website', 500)->nullable();
            $table->string('default_security_deposit_type', 32)->default('none');
            $table->decimal('default_security_deposit_value', 12, 2)->nullable();
            $table->boolean('show_prices_online')->default(true);
            $table->boolean('show_gst_online')->default(false);
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->boolean('pickup_enabled')->default(true);
            $table->boolean('delivery_enabled')->default(true);
            $table->decimal('default_delivery_charge', 12, 2)->default(0);
            $table->decimal('free_delivery_min_amount', 12, 2)->nullable();
            $table->string('theme_template', 32)->default('classic');
            $table->string('theme_mode', 16)->default('light');
            $table->string('theme_font', 32)->default('inter');
            $table->string('theme_accent_color', 7)->default('#059669');
            $table->string('theme_background_color', 7)->nullable();
            $table->string('theme_surface_color', 7)->nullable();
            $table->string('theme_header_color', 7)->nullable();
            $table->string('theme_button_color', 7)->nullable();
            $table->string('theme_button_text_color', 7)->nullable();
            $table->string('theme_link_color', 7)->nullable();
            $table->string('theme_footer_color', 7)->nullable();
            $table->json('theme_colors')->nullable();
            $table->string('hero_image')->nullable();
            $table->boolean('banner_enabled')->default(true);
            $table->string('banner_title', 255)->nullable();
            $table->string('banner_subtitle', 500)->nullable();
            $table->string('banner_height', 16)->default('medium');
            $table->string('banner_overlay', 16)->default('gradient');
            $table->boolean('banner_show_cta')->default(false);
            $table->string('banner_cta_text', 120)->nullable();
            $table->string('banner_cta_url', 500)->nullable();
            $table->string('seo_title', 255)->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords', 500)->nullable();
            $table->unsignedBigInteger('primary_location_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_store_settings');
    }
};
