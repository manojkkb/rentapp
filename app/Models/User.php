<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasUuid, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'avatar',
        'mobile',
        'vendor_id',
        'password',
        'language',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get all vendors the user has access to (many-to-many)
     */
    public function vendors()
    {
        return $this->belongsToMany(Vendor::class, 'vendor_users')
            ->withPivot('is_owner', 'role', 'vendor_role_id', 'is_active', 'last_login_at', 'permissions')
            ->withTimestamps();
    }

    /**
     * Get all reviews written by this user
     */
    public function reviews()
    {
        return $this->hasMany(CustomerReview::class);
    }

    /**
     * Get the current active vendor for this user (stored on users.vendor_id).
     */
    public function currentVendor()
    {
        if (! $this->vendor_id) {
            return null;
        }

        return $this->vendors()
            ->where('vendors.id', $this->vendor_id)
            ->wherePivot('is_active', true)
            ->first();
    }

    /**
     * Persist the user's selected vendor in the database.
     */
    public function setCurrentVendorId(int $vendorId): void
    {
        $this->update(['vendor_id' => $vendorId]);
    }

    /**
     * Public URL for profile image on S3, or null.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return Storage::disk('s3')->url($this->avatar);
    }

    /**
     * Check if user has access to a vendor
     */
    public function hasVendorAccess($vendorId)
    {
        return $this->vendors()->where('vendors.id', $vendorId)->exists();
    }
}
