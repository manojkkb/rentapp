<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Items extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    public const CONDITION_STATUSES = [
        'excellent',
        'good',
        'average',
        'damaged',
    ];

    protected $appends = ['photo_url'];

    protected $hidden = [
        'photo',
    ];

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'photo',
        'description',
        'price',
        'price_type',
        'security_deposit',
        'replacement_cost',
        'late_fee_per_day',
        'is_damage_protection',
        'minimum_rental_duration',
        'maximum_rental_duration',
        'weight',
        'dimension_length',
        'dimension_width',
        'dimension_height',
        'condition_status',
        'total_stock',
        'available_stock',
        'rented_stock',
        'damaged_stock',
        'maintenance_stock',
        'stock',
        'manage_stock',
        'is_available',
        'is_active',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'manage_stock' => 'boolean',
        'is_damage_protection' => 'boolean',
        'price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'replacement_cost' => 'decimal:2',
        'late_fee_per_day' => 'decimal:2',
        'weight' => 'decimal:3',
        'dimension_length' => 'decimal:2',
        'dimension_width' => 'decimal:2',
        'dimension_height' => 'decimal:2',
        'minimum_rental_duration' => 'integer',
        'maximum_rental_duration' => 'integer',
        'total_stock' => 'integer',
        'available_stock' => 'integer',
        'rented_stock' => 'integer',
        'damaged_stock' => 'integer',
        'maintenance_stock' => 'integer',
    ];

    /**
     * @return array<string, string>
     */
    public static function conditionStatusOptions(): array
    {
        $out = [];
        foreach (self::CONDITION_STATUSES as $key) {
            $out[$key] = __("vendor.item_condition_{$key}");
        }

        return $out;
    }

    /**
     * Get the vendor that owns the item
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the category of the item
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all order items for this item
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'item_id');
    }

    /**
     * Get all activities for this item
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ItemActivity::class, 'item_id');
    }

    /**
     * Scope a query to only include active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include available items
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Allowed values for items.price_type and order_items.price_type.
     *
     * @return list<string>
     */
    public static function priceTypeKeys(): array
    {
        return ['per_minute', 'per_hour', 'per_day', 'per_week', 'per_month', 'per_year', 'fixed'];
    }

    /**
     * Localized labels for item / order line forms (vendor lang file).
     *
     * @return array<string, string>
     */
    public static function priceTypeSelectOptions(): array
    {
        $out = [];
        foreach (self::priceTypeKeys() as $key) {
            $out[$key] = $key === 'fixed'
                ? __('vendor.price_fixed')
                : __("vendor.{$key}");
        }

        return $out;
    }

    public static function priceTypeUsesBillingUnits(string $priceType): bool
    {
        return $priceType !== 'fixed';
    }

    /**
     * Public URL for the item photo on S3, or null.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        return Storage::disk('s3')->url($this->photo);
    }

    public static function billingUnitsFieldLabel(string $priceType): string
    {
        return match ($priceType) {
            'per_minute' => __('vendor.number_of_minutes'),
            'per_hour' => __('vendor.number_of_hours'),
            'per_day' => __('vendor.number_of_days'),
            'per_week' => __('vendor.number_of_weeks'),
            'per_month' => __('vendor.number_of_months'),
            'per_year' => __('vendor.number_of_years'),
            default => '',
        };
    }
}
