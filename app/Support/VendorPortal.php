<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

final class VendorPortal
{
    public static function entryUrl(): string
    {
        if (! Auth::check()) {
            return route('vendor.login');
        }

        $user = Auth::user();

        if ($user->vendor_id && $user->currentVendor()) {
            return route('vendor.home');
        }

        return route('vendor.select');
    }

    public static function entryLabel(string $guestLabel = 'Login', string $authenticatedLabel = 'Dashboard'): string
    {
        return Auth::check() ? $authenticatedLabel : $guestLabel;
    }
}
