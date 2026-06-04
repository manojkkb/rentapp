<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Http\Controllers\Vendor\SubscriptionVendorController;
use App\Models\SubscriptionPlan;
use App\Support\VendorSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends ApiController
{
    use ResolvesApiVendor;

    public function plans(): JsonResponse
    {
        $this->requirePermission('settings.view');
        $vendor = $this->vendor();

        $typeOrder = ['silver' => 1, 'gold' => 2, 'diamond' => 3];

        $plans = SubscriptionPlan::query()
            ->purchasable()
            ->orderBy('id')
            ->get()
            ->groupBy('type')
            ->sortBy(fn ($_, $type) => $typeOrder[$type] ?? 99)
            ->map(fn ($group) => $group->values());

        return $this->ok([
            'plans' => $plans,
            'subscription' => [
                'status' => VendorSubscription::status($vendor),
                'trial_ends_at' => VendorSubscription::trialEndsAt($vendor)?->toIso8601String(),
                'trial_days_remaining' => VendorSubscription::trialDaysRemaining($vendor),
                'active' => VendorSubscription::activeSubscription($vendor),
            ],
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $this->requirePermission('settings.edit');

        return app(SubscriptionVendorController::class)->createOrder($request);
    }

    public function verifyPayment(Request $request): JsonResponse
    {
        $this->requirePermission('settings.edit');

        return app(SubscriptionVendorController::class)->verifyPayment($request);
    }
}
