<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Support\VendorDashboardMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends ApiController
{
    use ResolvesApiVendor;

    public function index(Request $request): JsonResponse
    {
        $this->requirePermission('dashboard.view');

        $validated = $request->validate([
            'period' => ['nullable', 'string', Rule::in(VendorDashboardMetrics::PERIODS)],
            'start_date' => ['required_if:period,custom', 'nullable', 'date'],
            'end_date' => ['required_if:period,custom', 'nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $period = $validated['period'] ?? 'monthly';

        return $this->ok(VendorDashboardMetrics::apiPayload(
            $this->vendor(),
            $period,
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null,
        ));
    }
}
