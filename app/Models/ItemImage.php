<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ItemImage extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'uuid',
        'item_id',
        'path',
        'sort_order',
    ];

    protected $appends = ['url'];

    protected $hidden = [
        'path',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Items::class, 'item_id');
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        return Storage::disk('s3')->url($this->path);
    }
}
