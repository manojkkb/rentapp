<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Support\VendorAccess;

trait ResolvesApiVendor
{
    protected function user(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    protected function vendor(): Vendor
    {
        $vendor = $this->user()->currentVendor();

        abort_if(! $vendor, 403, 'Please select a vendor account first.');

        return $vendor;
    }

    protected function authorizeVendorOrder(Order $order): Order
    {
        abort_if($order->vendor_id !== $this->vendor()->id, 403, 'Unauthorized access to this order.');

        return $order;
    }

    protected function can(string $permission): bool
    {
        $access = VendorAccess::current();

        return $access && $access->can($permission);
    }

    protected function requirePermission(string $permission): void
    {
        abort_if(! $this->can($permission), 403, __('vendor.permission_denied'));
    }
}
