<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VendorRole extends Model
{
    protected $fillable = [
        'vendor_id',
        'name',
        'slug',
        'description',
        'is_system',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            VendorPermission::class,
            'vendor_role_permission',
            'vendor_role_id',
            'vendor_permission_id'
        );
    }

    public function vendorUsers(): HasMany
    {
        return $this->hasMany(VendorUser::class);
    }
}
