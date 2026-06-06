<div>
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 md:text-3xl">{{ __('vendor.reviews') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('vendor.manage_customer_reviews') }}</p>
        </div>
    </div>

    @if($flashMessage)
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ $flashMessage }}</div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-5">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-center">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-100">
                    <i class="fas fa-star text-xl text-emerald-600" aria-hidden="true"></i>
                </div>
                <div class="ml-4"><p class="text-sm font-medium text-gray-600">{{ __('vendor.total_reviews') }}</p><p class="text-2xl font-bold text-gray-900">{{ $statistics['total'] }}</p></div>
            </div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-center">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-yellow-100">
                    <i class="fas fa-star-half-alt text-xl text-yellow-600" aria-hidden="true"></i>
                </div>
                <div class="ml-4"><p class="text-sm font-medium text-gray-600">{{ __('vendor.average_rating') }}</p><p class="text-2xl font-bold text-gray-900">{{ number_format($statistics['average_rating'], 1) }}</p></div>
            </div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-center">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-green-100">
                    <i class="fas fa-check-circle text-xl text-green-600" aria-hidden="true"></i>
                </div>
                <div class="ml-4"><p class="text-sm font-medium text-gray-600">{{ __('vendor.approved') }}</p><p class="text-2xl font-bold text-gray-900">{{ $statistics['approved'] }}</p></div>
            </div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-center">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-orange-100">
                    <i class="fas fa-clock text-xl text-orange-600" aria-hidden="true"></i>
                </div>
                <div class="ml-4"><p class="text-sm font-medium text-gray-600">{{ __('vendor.pending') }}</p><p class="text-2xl font-bold text-gray-900">{{ $statistics['pending'] }}</p></div>
            </div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-center">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-emerald-100">
                    <i class="fas fa-reply text-xl text-emerald-600" aria-hidden="true"></i>
                </div>
                <div class="ml-4"><p class="text-sm font-medium text-gray-600">{{ __('vendor.replied') }}</p><p class="text-2xl font-bold text-gray-900">{{ $statistics['replied'] }}</p></div>
            </div>
        </div>
    </div>

    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.filter_by_rating') }}</label>
                <select wire:model.live="rating" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">{{ __('vendor.all_ratings') }}</option>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}">{{ $i }} {{ __('vendor.stars') }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.filter_by_status') }}</label>
                <select wire:model.live="status" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">{{ __('vendor.all_statuses') }}</option>
                    <option value="approved">{{ __('vendor.approved') }}</option>
                    <option value="pending">{{ __('vendor.pending') }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.filter_by_reply') }}</label>
                <select wire:model.live="replied" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">{{ __('vendor.all') }}</option>
                    <option value="yes">{{ __('vendor.replied') }}</option>
                    <option value="no">{{ __('vendor.not_replied') }}</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="button" wire:click="clearFilters" class="rounded-lg bg-gray-200 px-4 py-2 text-sm text-gray-700 transition-colors hover:bg-gray-300">
                {{ __('vendor.clear_filters') }}
            </button>
        </div>
    </div>

    <div class="space-y-4" wire:loading.class="opacity-60" wire:target="rating,status,replied,toggleApproval,postReply">
        @forelse ($reviews as $review)
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm" wire:key="review-{{ $review->id }}">
                <div class="mb-4 flex flex-col md:flex-row md:items-start md:justify-between">
                    <div class="flex items-start space-x-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                            <span class="text-lg font-semibold text-emerald-600">{{ strtoupper(substr($review->user->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">{{ $review->user->name }}</h4>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <div class="flex items-center">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" aria-hidden="true"></i>
                                    @endfor
                                </div>
                                <span class="text-sm text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                @if ($review->order)
                                    <span class="text-sm text-gray-500">Order {{ $review->order->uuid }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2 md:mt-0">
                        <button type="button"
                                wire:click="toggleApproval({{ $review->id }})"
                                wire:loading.attr="disabled"
                                wire:target="toggleApproval({{ $review->id }})"
                                class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ $review->is_approved ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-orange-100 text-orange-700 hover:bg-orange-200' }}">
                            {{ $review->is_approved ? __('vendor.approved') : __('vendor.pending') }}
                        </button>
                        @if (!$review->vendor_reply && $replyingToId !== $review->id)
                            <button type="button"
                                    wire:click="openReply({{ $review->id }})"
                                    class="rounded-lg bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 transition-colors hover:bg-emerald-200">
                                {{ __('vendor.reply') }}
                            </button>
                        @endif
                    </div>
                </div>

                <p class="leading-relaxed text-gray-700">{{ $review->review }}</p>

                @if ($review->vendor_reply)
                    <div class="ml-0 mt-4 rounded-r-lg border-l-4 border-emerald-500 bg-emerald-50 p-4 md:ml-16">
                        <div class="mb-2 flex items-center justify-between">
                            <h5 class="font-semibold text-emerald-900">{{ __('vendor.vendor_response') }}</h5>
                            <span class="text-sm text-emerald-600">{{ $review->replied_at?->diffForHumans() }}</span>
                        </div>
                        <p class="text-gray-700">{{ $review->vendor_reply }}</p>
                    </div>
                @endif

                @if($replyingToId === $review->id)
                    <div class="ml-0 mt-4 md:ml-16">
                        <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.your_response') }}</label>
                        <textarea wire:model="replyText"
                                  rows="3"
                                  maxlength="1000"
                                  class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                                  placeholder="{{ __('vendor.enter_your_response') }}"></textarea>
                        @error('replyText') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <div class="mt-3 flex gap-2">
                            <button type="button"
                                    wire:click="postReply({{ $review->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="postReply"
                                    class="rounded-lg bg-emerald-600 px-4 py-2 text-white transition-colors hover:bg-emerald-700 disabled:opacity-70">
                                <span wire:loading.remove wire:target="postReply">{{ __('vendor.send_reply') }}</span>
                                <span wire:loading wire:target="postReply"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></span>
                            </button>
                            <button type="button" wire:click="cancelReply" class="rounded-lg bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300">
                                {{ __('vendor.cancel') }}
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-lg border border-gray-200 bg-white p-12 text-center shadow-sm">
                <h3 class="mb-2 text-lg font-semibold text-gray-900">{{ __('vendor.no_reviews_found') }}</h3>
                <p class="text-gray-600">{{ __('vendor.no_reviews_message') }}</p>
            </div>
        @endforelse
    </div>

    @if ($reviews->hasPages())
        <div class="mt-6">{{ $reviews->links() }}</div>
    @endif
</div>
