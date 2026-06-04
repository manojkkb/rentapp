<?php

namespace App\Support;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorUser;

class ApiUserPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function user(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile,
            'language' => $user->language,
            'avatar_url' => $user->avatar_url,
            'vendor_id' => $user->vendor_id,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function memberships(User $user): array
    {
        return VendorUser::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['vendor', 'vendorRole'])
            ->get()
            ->filter(fn (VendorUser $m) => $m->vendor !== null)
            ->sortByDesc('is_owner')
            ->values()
            ->map(fn (VendorUser $m) => [
                'vendor_id' => $m->vendor_id,
                'vendor_name' => $m->vendor->name,
                'vendor_logo_url' => $m->vendor->logo_url,
                'is_owner' => (bool) $m->is_owner,
                'role' => $m->vendorRole?->name ?? $m->role,
                'is_verified' => (bool) $m->vendor->is_verified,
                'is_active' => (bool) $m->vendor->is_active,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function vendorContext(User $user): ?array
    {
        $vendor = $user->currentVendor();
        if (! $vendor) {
            return null;
        }

        $access = VendorAccess::current();

        return [
            'vendor' => self::vendor($vendor),
            'permissions' => $access ? ($access->isOwner() ? ['*'] : $access->permissionKeys()) : [],
            'is_owner' => $access?->isOwner() ?? false,
            'subscription' => VendorSubscription::status($vendor),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function vendor(Vendor $vendor): array
    {
        return [
            'id' => $vendor->id,
            'name' => $vendor->name,
            'owner_name' => $vendor->owner_name,
            'slug' => $vendor->slug,
            'logo_url' => $vendor->logo_url,
            'city' => $vendor->city,
            'state' => $vendor->state,
            'language' => $vendor->language,
            'is_verified' => (bool) $vendor->is_verified,
            'is_active' => (bool) $vendor->is_active,
            'rating' => (float) $vendor->rating,
            'total_reviews' => (int) $vendor->total_reviews,
        ];
    }
}
