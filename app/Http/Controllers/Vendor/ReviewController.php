<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Vendor\Concerns\RedirectsIfNumericRouteKey;
use App\Models\CustomerReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    use RedirectsIfNumericRouteKey;

    /**
     * Display a listing of the reviews.
     */
    public function index(Request $request)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return redirect()->route('vendor.select')->withErrors(['error' => 'Please select a vendor']);
        }

        return view('vendor.reviews.index');
    }

    /**
     * Reply to a review.
     */
    public function reply(Request $request, CustomerReview $review)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return back()->withErrors(['error' => 'Please select a vendor']);
        }

        $this->authorizeReview($vendor, $review);

        $request->validate([
            'vendor_reply' => 'required|string|max:1000',
        ]);

        $review->update([
            'vendor_reply' => $request->vendor_reply,
            'replied_at' => now(),
        ]);

        return back()->with('success', 'Reply posted successfully!');
    }

    /**
     * Toggle review approval status.
     */
    public function toggleApproval(CustomerReview $review)
    {
        $vendor = Auth::user()->currentVendor();

        if (! $vendor) {
            return back()->withErrors(['error' => 'Please select a vendor']);
        }

        $this->authorizeReview($vendor, $review);

        $review->update([
            'is_approved' => ! $review->is_approved,
        ]);

        // Update vendor rating
        $vendor->updateRating();

        return back()->with('success', 'Review status updated successfully!');
    }

    private function authorizeReview($vendor, CustomerReview $review): CustomerReview
    {
        if ($review->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access');
        }

        return $review;
    }
}
