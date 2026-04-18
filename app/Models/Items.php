<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Items extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'price_type',
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
        'price' => 'decimal:2',
    ];
    
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
     * Get all cart items for this item
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(VendorCartItem::class, 'item_id');
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
     * Allowed values for items.price_type and vendor_cart_items.price_type.
     *
     * @return list<string>
     */
    public static function priceTypeKeys(): array
    {
        return ['per_minute', 'per_hour', 'per_day', 'per_week', 'per_month', 'per_year', 'fixed'];
    }

    /**
     * Localized labels for cart / item forms (vendor lang file).
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
     * Short label for the "how many units" field (days, hours, …).
     */
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
