<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VendorPermission extends Model
{
    protected $fillable = [
        'key',
        'group',
        'description',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            VendorRole::class,
            'vendor_role_permission',
            'vendor_permission_id',
            'vendor_role_id'
        );
    }
}
