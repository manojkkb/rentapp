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

    public function label(): string
    {
        $translationKey = 'vendor.permission_'.str_replace('.', '_', $this->key);
        $translated = __($translationKey);

        if ($translated !== $translationKey) {
            return $translated;
        }

        return $this->description ?? $this->key;
    }

    public static function groupLabel(?string $group): string
    {
        $slug = $group ?: 'general';
        $translationKey = 'vendor.permission_group_'.$slug;
        $translated = __($translationKey);

        if ($translated !== $translationKey) {
            return $translated;
        }

        return $slug;
    }
}
