<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ItemAttribute extends Model
{
    protected $fillable = [
        'item_id',
        'name',
        'slug',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Items::class, 'item_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public static function slugFromName(string $name): string
    {
        $slug = Str::slug(trim($name), '_');

        return $slug !== '' ? $slug : 'attribute';
    }

    protected static function booted(): void
    {
        static::creating(function (ItemAttribute $attribute) {
            if (empty($attribute->slug) && ! empty($attribute->name)) {
                $attribute->slug = self::slugFromName($attribute->name);
            }
        });
    }
}
