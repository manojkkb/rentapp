<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

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
        'is_super_admin' => 'boolean',
        'is_active' => 'boolean',
    ];  

    
}
