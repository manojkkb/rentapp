<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorUser extends Model
{
    protected $fillable = [
        'vendor_id',
        'user_id',
        'is_owner',
        'role',
        'is_active',
        'last_login_at',
        'permissions',
    ];
    
    protected $casts = [
        'is_owner' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'permissions' => 'array',
    ];
    
    /**
     * Get the vendor
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
    
    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
