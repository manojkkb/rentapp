<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OtpVerification extends Model
{
    protected $table = 'otp_verifications';

    protected $fillable = [
        'identifier',
        'identifier_type',
        'otp',
        'expires_at',
        'attempts',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts'    => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    // Only non-expired OTP
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    // Only not verified OTP
    public function scopeNotVerified(Builder $query): Builder
    {
        return $query->whereNull('verified_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    // Check if expired
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    // Mark OTP as verified
    public function markAsVerified(): void
    {
        $this->update([
            'verified_at' => now()
        ]);
    }

    // Increment failed attempts
    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    // Check max attempts (default 5)
    public function hasExceededAttempts(int $max = 5): bool
    {
        return $this->attempts >= $max;
    }
}
