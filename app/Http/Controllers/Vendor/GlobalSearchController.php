<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Services\VendorGlobalSearch;
use App\Support\VendorAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request, VendorGlobalSearch $search): JsonResponse
    {
        $vendor = Auth::user()?->currentVendor();

        if (! $vendor) {
            return response()->json(['groups' => [], 'query' => ''], 403);
        }

        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
        ]);

        $result = $search->search(
            $vendor,
            $validated['q'] ?? '',
            VendorAccess::current(),
        );

        return response()->json($result);
    }
}
