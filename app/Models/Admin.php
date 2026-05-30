<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;

class Admin extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'otp',
        'otp_expires_at',
        'avatar',
        'is_super_admin',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'otp',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'is_super_admin' => 'boolean',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        if (Storage::disk('public')->exists($this->avatar)) {
            return Storage::disk('public')->url($this->avatar);
        }

        if (Storage::disk('s3')->exists($this->avatar)) {
            return Storage::disk('s3')->url($this->avatar);
        }

        return null;
    }

    public function initialsAvatarUrl(): string
    {
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=10b981&color=fff';
    }
}
