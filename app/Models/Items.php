<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\RoutesByUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Items extends Model
{
    use HasUuid, RoutesByUuid, SoftDeletes;

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
        'uuid',
        'vendor_id',
        'category_id',
        'name',
        'item_code',
        'slug',
        'photo',
        'description',
        'price',
        'rental_period',
        'security_deposit',
        'replacement_cost',
        'late_fee',
        'min_rental_duration',
        'max_rental_duration',
        'condition_status',
        'damaged_stock',
        'maintenance_stock',
        'stock',
        'manage_stock',
        'is_available',
        'is_active',
        'has_variants',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'has_variants' => 'boolean',
        'manage_stock' => 'boolean',
        'price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'replacement_cost' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'min_rental_duration' => 'integer',
        'max_rental_duration' => 'integer',
        'damaged_stock' => 'integer',
        'maintenance_stock' => 'integer',
        'stock' => 'integer',
        'deleted_at' => 'datetime',
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

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'item_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ItemActivity::class, 'item_id');
    }

    public function variantAttributes(): HasMany
    {
        return $this->hasMany(ItemAttribute::class, 'item_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ItemVariant::class, 'item_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ItemImage::class, 'item_id')->orderBy('sort_order');
    }

    public function usesVariants(): bool
    {
        return (bool) $this->has_variants;
    }

    /**
     * Rentable stock for catalog display and availability checks.
     */
    public function effectiveStock(): int
    {
        if ($this->usesVariants()) {
            if ($this->relationLoaded('variants')) {
                return (int) $this->variants->sum('stock');
            }

            return (int) $this->variants()->sum('stock');
        }

        return (int) $this->stock;
    }

    public function orderReservedQuantity(): int
    {
        return (int) ($this->reserved_qty ?? 0);
    }

    public function orderRentedQuantity(): int
    {
        return (int) ($this->rented_qty ?? 0);
    }

    public function orderCommittedQuantity(): int
    {
        return $this->orderReservedQuantity() + $this->orderRentedQuantity();
    }

    /**
     * Units free to rent right now (inventory minus open order commitments).
     */
    public function rentableAvailableStock(): int
    {
        if ($this->usesVariants()) {
            $variants = $this->relationLoaded('variants')
                ? $this->variants
                : $this->variants()->withOrderStockBreakdown()->ordered()->get();

            return (int) $variants->sum(fn (ItemVariant $v) => $v->rentableAvailableStock());
        }

        return max(0, (int) $this->stock - $this->orderCommittedQuantity());
    }

    /**
     * @return array<string, string>
     */
    public function emptyAttributeValues(): array
    {
        $values = [];
        foreach ($this->variantAttributes()->ordered()->pluck('slug') as $slug) {
            $values[$slug] = '';
        }

        return $values;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Reserved and rented quantities from open order lines (for list/detail stock breakdown).
     */
    public function scopeWithOrderStockBreakdown($query)
    {
        if ($query->getQuery()->columns === null) {
            $query->select('items.*');
        }

        $reservedStatuses = OrderItem::reservedStatuses();
        $rentedStatuses = OrderItem::rentedStatuses();

        return $query
            ->selectSub(
                OrderItem::query()
                    ->selectRaw('COALESCE(SUM(order_items.quantity), 0)')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereColumn('order_items.item_id', 'items.id')
                    ->where('orders.status', '!=', 'cancelled')
                    ->whereNotIn('order_items.item_status', ['cancelled', 'returned'])
                    ->whereIn('order_items.item_status', $reservedStatuses),
                'reserved_qty'
            )
            ->selectSub(
                OrderItem::query()
                    ->selectRaw('COALESCE(SUM(GREATEST(order_items.quantity - order_items.returned_qty, 0)), 0)')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->whereColumn('order_items.item_id', 'items.id')
                    ->where('orders.status', '!=', 'cancelled')
                    ->whereNotIn('order_items.item_status', ['cancelled', 'returned'])
                    ->whereIn('order_items.item_status', $rentedStatuses),
                'rented_qty'
            );
    }

    public static function codeFromId(int $id): string
    {
        return 'ITM-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    public static function normalizeItemCode(string $code): string
    {
        return strtoupper(preg_replace('/\s+/', '-', trim($code)) ?? '');
    }

    protected static function booted(): void
    {
        static::creating(function (Items $item) {
            if (empty($item->item_code)) {
                $item->item_code = 'PENDING';
            }
        });

        static::created(function (Items $item) {
            if ($item->item_code === 'PENDING') {
                $item->updateQuietly(['item_code' => self::codeFromId($item->id)]);
            }
        });
    }

    /**
     * Total units (rentable + damaged + maintenance).
     */
    public function getTotalStockAttribute(): int
    {
        return (int) $this->stock
            + (int) $this->damaged_stock
            + (int) $this->maintenance_stock;
    }

    /**
     * Allowed values for items.rental_period and order_items.rental_period.
     *
     * @return list<string>
     */
    public static function rentalPeriodKeys(): array
    {
        return ['per_minute', 'per_hour', 'per_day', 'per_week', 'per_month', 'per_year', 'fixed'];
    }

    /**
     * @return array<string, string>
     */
    public static function rentalPeriodSelectOptions(): array
    {
        $out = [];
        foreach (self::rentalPeriodKeys() as $key) {
            $out[$key] = $key === 'fixed'
                ? __('vendor.price_fixed')
                : __("vendor.{$key}");
        }

        return $out;
    }

    public static function rentalPeriodUsesBillingUnits(string $rentalPeriod): bool
    {
        return $rentalPeriod !== 'fixed';
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        return Storage::disk('s3')->url($this->photo);
    }

    public static function billingUnitsFieldLabel(string $rentalPeriod): string
    {
        return match ($rentalPeriod) {
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
