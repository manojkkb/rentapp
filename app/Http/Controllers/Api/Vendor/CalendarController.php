<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Http\Controllers\Vendor\VendorCalendarController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends ApiController
{
    use ResolvesApiVendor;

    public function events(Request $request): JsonResponse
    {
        $this->requirePermission('calendar.view');

        return app(VendorCalendarController::class)->events($request);
    }
}
