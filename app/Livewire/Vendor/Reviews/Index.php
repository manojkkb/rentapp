<?php

namespace App\Livewire\Vendor\Reviews;

use App\Livewire\Vendor\VendorComponent;
use App\Models\CustomerReview;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class Index extends VendorComponent
{
    use WithPagination;

    #[Url(except: '')]
    public string $rating = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $replied = '';

    public ?int $replyingToId = null;

    public string $replyText = '';

    public ?string $flashMessage = null;

    public function updatingRating(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingReplied(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->rating = '';
        $this->status = '';
        $this->replied = '';
        $this->resetPage();
    }

    public function openReply(int $reviewId): void
    {
        $this->replyingToId = $reviewId;
        $this->replyText = '';
        $this->resetErrorBag('replyText');
    }

    public function cancelReply(): void
    {
        $this->replyingToId = null;
        $this->replyText = '';
    }

    public function postReply(int $reviewId): void
    {
        $this->validate([
            'replyText' => ['required', 'string', 'max:1000'],
        ], [], [
            'replyText' => __('vendor.your_response'),
        ]);

        $review = $this->findReview($reviewId);
        $review->update([
            'vendor_reply' => $this->replyText,
            'replied_at' => now(),
        ]);

        $this->flashMessage = __('vendor.reply_posted') ?? 'Reply posted successfully!';
        $this->cancelReply();
    }

    public function toggleApproval(int $reviewId): void
    {
        $review = $this->findReview($reviewId);
        $review->update(['is_approved' => ! $review->is_approved]);
        $this->vendor->updateRating();
        $this->flashMessage = __('vendor.review_status_updated') ?? 'Review status updated successfully!';
    }

    public function render()
    {
        $query = $this->vendor->reviews()
            ->with(['user', 'order'])
            ->latest();

        if ($this->rating !== '') {
            $query->where('rating', (int) $this->rating);
        }

        if ($this->status === 'approved') {
            $query->where('is_approved', true);
        } elseif ($this->status === 'pending') {
            $query->where('is_approved', false);
        }

        if ($this->replied === 'yes') {
            $query->whereNotNull('vendor_reply');
        } elseif ($this->replied === 'no') {
            $query->whereNull('vendor_reply');
        }

        $reviews = $query->paginate(15);

        $statistics = [
            'total' => $this->vendor->reviews()->count(),
            'approved' => $this->vendor->reviews()->where('is_approved', true)->count(),
            'pending' => $this->vendor->reviews()->where('is_approved', false)->count(),
            'replied' => $this->vendor->reviews()->whereNotNull('vendor_reply')->count(),
            'average_rating' => $this->vendor->rating ?? 0,
            'rating_distribution' => [
                5 => $this->vendor->reviews()->where('is_approved', true)->where('rating', 5)->count(),
                4 => $this->vendor->reviews()->where('is_approved', true)->where('rating', 4)->count(),
                3 => $this->vendor->reviews()->where('is_approved', true)->where('rating', 3)->count(),
                2 => $this->vendor->reviews()->where('is_approved', true)->where('rating', 2)->count(),
                1 => $this->vendor->reviews()->where('is_approved', true)->where('rating', 1)->count(),
            ],
        ];

        return view('livewire.vendor.reviews.index', compact('reviews', 'statistics'));
    }

    private function findReview(int $reviewId): CustomerReview
    {
        return CustomerReview::query()
            ->where('vendor_id', $this->vendorId())
            ->whereKey($reviewId)
            ->firstOrFail();
    }
}
