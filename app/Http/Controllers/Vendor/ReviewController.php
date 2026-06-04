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

        // Get filter parameters
        $rating = $request->get('rating');
        $status = $request->get('status');

        // Build query
        $query = $vendor->reviews()
            ->with(['user', 'order'])
            ->latest();

        // Apply filters
        if ($rating) {
            $query->where('rating', $rating);
        }

        if ($status === 'approved') {
            $query->where('is_approved', true);
        } elseif ($status === 'pending') {
            $query->where('is_approved', false);
        }

        if ($request->get('replied') === 'yes') {
            $query->whereNotNull('vendor_reply');
        } elseif ($request->get('replied') === 'no') {
            $query->whereNull('vendor_reply');
        }

        // Paginate results
        $reviews = $query->paginate(15);

        // Calculate statistics
        $statistics = [
            'total' => $vendor->reviews()->count(),
            'approved' => $vendor->reviews()->where('is_approved', true)->count(),
            'pending' => $vendor->reviews()->where('is_approved', false)->count(),
            'replied' => $vendor->reviews()->whereNotNull('vendor_reply')->count(),
            'average_rating' => $vendor->rating ?? 0,
            'rating_distribution' => [
                5 => $vendor->reviews()->where('is_approved', true)->where('rating', 5)->count(),
                4 => $vendor->reviews()->where('is_approved', true)->where('rating', 4)->count(),
                3 => $vendor->reviews()->where('is_approved', true)->where('rating', 3)->count(),
                2 => $vendor->reviews()->where('is_approved', true)->where('rating', 2)->count(),
                1 => $vendor->reviews()->where('is_approved', true)->where('rating', 1)->count(),
            ],
        ];

        return view('vendor.reviews.index', compact('reviews', 'statistics'));
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
