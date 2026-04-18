<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'vendor_id',
        'password',
        'language',
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
            ->withPivot('is_owner', 'role', 'is_active', 'last_login_at', 'permissions')
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
     * Get the current active vendor for this user
     */
    public function currentVendor()
    {
        $vendorId = session('current_vendor_id');
        if ($vendorId) {
            return $this->vendors()->where('vendors.id', $vendorId)->first();
        }

        return null;
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
