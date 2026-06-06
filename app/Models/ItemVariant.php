<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Concerns\RoutesByUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ItemVariant extends Model
{
    use HasUuid, RoutesByUuid, SoftDeletes;

    protected $appends = ['photo_url'];

    protected $hidden = [
        'photo',
    ];

    protected $fillable = [
        'uuid',
        'item_id',
        'variant_code',
        'name',
        'attributes',
        'photo',
        'price',
        'security_deposit',
        'replacement_cost',
        'late_fee',
        'condition_status',
        'damaged_stock',
        'maintenance_stock',
        'stock',
        'manage_stock',
        'is_available',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'manage_stock' => 'boolean',
        'price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'replacement_cost' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'damaged_stock' => 'integer',
        'maintenance_stock' => 'integer',
        'stock' => 'integer',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Items::class, 'item_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'item_variant_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public static function codeFromId(int $id): string
    {
        return 'VRN-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    public static function normalizeVariantCode(string $code): string
    {
        return strtoupper(preg_replace('/\s+/', '-', trim($code)) ?? '');
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array<string, string>
     */
    public static function normalizeAttributesForDefinitions(array $values, Collection $definitions): array
    {
        $normalized = [];

        foreach ($definitions as $definition) {
            $slug = $definition->slug;
            $normalized[$slug] = trim((string) ($values[$slug] ?? ''));
        }

        return $normalized;
    }

    /**
     * Human-readable label from attribute values (e.g. "Red / Large / 5KW").
     */
    public function displayLabel(?Collection $definitions = null): string
    {
        if ($this->name) {
            return $this->name;
        }

        $definitions ??= $this->relationLoaded('item')
            ? ($this->item?->relationLoaded('variantAttributes')
                ? $this->item->variantAttributes
                : $this->item?->variantAttributes()->ordered()->get())
            : collect();

        $attributeValues = $this->getAttribute('attributes');
        $attributeValues = is_array($attributeValues) ? $attributeValues : [];

        if ($definitions instanceof Collection && $definitions->isNotEmpty()) {
            $parts = [];
            foreach ($definitions as $definition) {
                $value = trim((string) ($attributeValues[$definition->slug] ?? ''));
                if ($value !== '') {
                    $parts[] = $value;
                }
            }

            if ($parts !== []) {
                return implode(' / ', $parts);
            }
        }

        $values = array_values(array_filter(array_map(
            static fn ($value) => trim((string) $value),
            $attributeValues
        )));

        return $values !== [] ? implode(' / ', $values) : $this->variant_code;
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

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo) {
            return Storage::disk('s3')->url($this->photo);
        }

        if ($this->relationLoaded('item') && $this->item?->photo_url) {
            return $this->item->photo_url;
        }

        return null;
    }

    protected static function booted(): void
    {
        static::creating(function (ItemVariant $variant) {
            if (empty($variant->variant_code)) {
                $variant->variant_code = 'PENDING';
            }
        });

        static::created(function (ItemVariant $variant) {
            if ($variant->variant_code === 'PENDING') {
                $variant->updateQuietly(['variant_code' => self::codeFromId($variant->id)]);
            }
        });
    }
}
