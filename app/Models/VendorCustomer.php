<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorCustomer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'user_id',
        'name',
        'mobile',
        'address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the vendor that owns the customer
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the user associated with the customer (if registered)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
