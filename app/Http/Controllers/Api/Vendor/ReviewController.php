<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Concerns\ResolvesApiVendor;
use App\Models\CustomerReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends ApiController
{
    use ResolvesApiVendor;

    public function index(Request $request): JsonResponse
    {
        $this->requirePermission('reviews.view');
        $vendor = $this->vendor();

        $reviews = CustomerReview::query()
            ->where('vendor_id', $vendor->id)
            ->with(['user:id,name', 'item:id,name'])
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return $this->ok([
            'reviews' => $reviews->items(),
            'meta' => $this->paginationMeta($reviews),
        ]);
    }

    public function reply(Request $request, CustomerReview $review): JsonResponse
    {
        $this->requirePermission('reviews.view');
        abort_if($review->vendor_id !== $this->vendor()->id, 404);

        $validated = $request->validate([
            'vendor_reply' => ['required', 'string', 'max:2000'],
        ]);

        $review->update(['vendor_reply' => $validated['vendor_reply']]);

        return $this->ok(['review' => $review->fresh()], 'Reply saved.');
    }
}
