<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VendorStoreSetting extends Model
{
    public const SECURITY_DEPOSIT_TYPES = [
        'none',
        'order_amount',
        'product_security_deposit',
        'fixed_amount',
    ];

    public const THEME_TEMPLATES = [
        'classic',
        'modern',
        'minimal',
        'bold',
        'boutique',
        'neon',
        'nature',
        'ocean',
        'sunset',
        'mono',
    ];

    public const BANNER_HEIGHTS = ['compact', 'medium', 'tall'];

    public const BANNER_OVERLAYS = ['gradient', 'dark', 'light'];

    protected $fillable = [
        'vendor_id',
        'is_published',
        'tagline',
        'description',
        'business_phone',
        'whatsapp_number',
        'whatsapp_enabled',
        'business_email',
        'website',
        'default_security_deposit_type',
        'default_security_deposit_value',
        'show_prices_online',
        'show_gst_online',
        'min_order_amount',
        'pickup_enabled',
        'delivery_enabled',
        'theme_template',
        'theme_mode',
        'theme_font',
        'theme_accent_color',
        'theme_background_color',
        'theme_surface_color',
        'theme_header_color',
        'theme_button_color',
        'theme_button_text_color',
        'theme_link_color',
        'theme_footer_color',
        'theme_colors',
        'hero_image',
        'banner_enabled',
        'banner_title',
        'banner_subtitle',
        'banner_height',
        'banner_overlay',
        'banner_show_cta',
        'banner_cta_text',
        'banner_cta_url',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'primary_location_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'show_prices_online' => 'boolean',
        'show_gst_online' => 'boolean',
        'pickup_enabled' => 'boolean',
        'delivery_enabled' => 'boolean',
        'whatsapp_enabled' => 'boolean',
        'banner_enabled' => 'boolean',
        'banner_show_cta' => 'boolean',
        'default_security_deposit_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'default_delivery_charge' => 'decimal:2',
        'free_delivery_min_amount' => 'decimal:2',
        'theme_colors' => 'array',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function primaryLocation(): BelongsTo
    {
        return $this->belongsTo(VendorLocation::class, 'primary_location_id');
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        if (! $this->hero_image) {
            return null;
        }

        if (Storage::disk('public')->exists($this->hero_image)) {
            return Storage::disk('public')->url($this->hero_image);
        }

        return Storage::disk('s3')->url($this->hero_image);
    }

    public function resolveDeliveryCharge(float $cartSubtotal, string $fulfillmentType): float
    {
        if ($fulfillmentType !== 'delivery') {
            return 0.0;
        }

        $freeMin = $this->free_delivery_min_amount;
        if ($freeMin !== null && $cartSubtotal >= (float) $freeMin) {
            return 0.0;
        }

        return round((float) ($this->default_delivery_charge ?? 0), 2);
    }
}
