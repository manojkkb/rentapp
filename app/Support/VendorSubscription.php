<?php

namespace App\Support;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Vendor;
use Carbon\Carbon;

class VendorSubscription
{
    public static function trialDays(): int
    {
        return PlatformSettings::trialDays();
    }

    public static function trialEndsAt(Vendor $vendor): Carbon
    {
        return $vendor->created_at->copy()->addDays(static::trialDays());
    }

    public static function syncExpired(Vendor $vendor): void
    {
        Subscription::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now())
            ->update(['status' => 'expired']);
    }

    public static function hasActiveSubscription(Vendor $vendor): bool
    {
        static::syncExpired($vendor);

        return Subscription::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'active')
            ->where('expiry_date', '>', now())
            ->exists();
    }

    public static function isOnTrial(Vendor $vendor): bool
    {
        if (static::hasActiveSubscription($vendor)) {
            return false;
        }

        return now()->lt(static::trialEndsAt($vendor));
    }

    public static function canAccess(Vendor $vendor): bool
    {
        return static::isOnTrial($vendor) || static::hasActiveSubscription($vendor);
    }

    /**
     * trial | active | expired
     */
    public static function status(Vendor $vendor): string
    {
        if (static::hasActiveSubscription($vendor)) {
            return 'active';
        }

        if (static::isOnTrial($vendor)) {
            return 'trial';
        }

        return 'expired';
    }

    public static function trialDaysRemaining(Vendor $vendor): int
    {
        if (! static::isOnTrial($vendor)) {
            return 0;
        }

        return max(0, (int) now()->diffInDays(static::trialEndsAt($vendor), false));
    }

    public static function activeSubscription(Vendor $vendor): ?Subscription
    {
        static::syncExpired($vendor);

        return Subscription::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'active')
            ->where('expiry_date', '>', now())
            ->with('subscriptionPlan')
            ->latest('expiry_date')
            ->first();
    }

    public static function grantPlanManually(Vendor $vendor, SubscriptionPlan $plan, int $adminId): Subscription
    {
        static::syncExpired($vendor);

        Subscription::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'active')
            ->update(['status' => 'expired']);

        $start = now();
        $amount = (float) ($plan->discount_price ?? $plan->price);

        return Subscription::create([
            'user_id' => $vendor->user_id,
            'subscription_plan_id' => $plan->id,
            'vendor_id' => $vendor->id,
            'start_date' => $start,
            'expiry_date' => $start->copy()->addDays($plan->duration_days),
            'status' => 'active',
            'amount' => $amount,
            'payment_gateway' => 'manual',
            'payment_id' => 'admin:'.$adminId,
            'auto_renew' => false,
        ]);
    }

    /** @return list<string> */
    public static function exemptRouteNames(): array
    {
        return [
            'vendor.subscription.plans',
            'vendor.subscription.create-order',
            'vendor.subscription.verify-payment',
            'vendor.logout',
            'vendor.profile',
            'vendor.profile.update',
            'vendor.profile.update.personal',
            'vendor.profile.update.business',
            'vendor.language.switch',
            'vendor.select',
            'vendor.select.submit',
            'vendor.manifest',
        ];
    }
}
