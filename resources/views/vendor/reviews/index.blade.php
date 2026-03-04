@extends('vendor.layouts.app')

@section('title', __('vendor.reviews'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ __('vendor.reviews') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('vendor.manage_customer_reviews') }}</p>
        </div>
    </div>

    <!-- Statistics Dashboard -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <!-- Total Reviews -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-star text-emerald-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('vendor.total_reviews') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $statistics['total'] }}</p>
                </div>
            </div>
        </div>

        <!-- Average Rating -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-star-half-alt text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('vendor.average_rating') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($statistics['average_rating'], 1) }}</p>
                </div>
            </div>
        </div>

        <!-- Approved Reviews -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('vendor.approved') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $statistics['approved'] }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Reviews -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-orange-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('vendor.pending') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $statistics['pending'] }}</p>
                </div>
            </div>
        </div>

        <!-- Replied Reviews -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-reply text-blue-600 text-xl"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">{{ __('vendor.replied') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $statistics['replied'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Distribution -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('vendor.rating_distribution') }}</h3>
        <div class="space-y-3">
            @for ($i = 5; $i >= 1; $i--)
                @php
                    $count = $statistics['rating_distribution'][$i] ?? 0;
                    $percentage = $statistics['total'] > 0 ? ($count / $statistics['total']) * 100 : 0;
                @endphp
                <div class="flex items-center">
                    <div class="w-16 flex items-center">
                        <span class="text-sm font-medium text-gray-700">{{ $i }}</span>
                        <i class="fas fa-star text-yellow-400 text-xs ml-1"></i>
                    </div>
                    <div class="flex-1 mx-4">
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div class="bg-emerald-500 h-4 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    <div class="w-20 text-right">
                        <span class="text-sm font-medium text-gray-700">{{ $count }}</span>
                        <span class="text-xs text-gray-500">({{ number_format($percentage, 1) }}%)</span>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('vendor.reviews.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Rating Filter -->
            <div>
                <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">{{ __('vendor.filter_by_rating') }}</label>
                <select name="rating" id="rating" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">{{ __('vendor.all_ratings') }}</option>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                            {{ $i }} {{ __('vendor.stars') }}
                        </option>
                    @endfor
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('vendor.filter_by_status') }}</label>
                <select name="status" id="status" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">{{ __('vendor.all_statuses') }}</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ __('vendor.approved') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('vendor.pending') }}</option>
                </select>
            </div>

            <!-- Reply Filter -->
            <div>
                <label for="replied" class="block text-sm font-medium text-gray-700 mb-1">{{ __('vendor.filter_by_reply') }}</label>
                <select name="replied" id="replied" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">{{ __('vendor.all') }}</option>
                    <option value="yes" {{ request('replied') == 'yes' ? 'selected' : '' }}>{{ __('vendor.replied') }}</option>
                    <option value="no" {{ request('replied') == 'no' ? 'selected' : '' }}>{{ __('vendor.not_replied') }}</option>
                </select>
            </div>

            <div class="md:col-span-3 flex gap-2">
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                    <i class="fas fa-filter mr-2"></i>{{ __('vendor.apply_filters') }}
                </button>
                <a href="{{ route('vendor.reviews.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    <i class="fas fa-times mr-2"></i>{{ __('vendor.clear_filters') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Reviews List -->
    <div class="space-y-4">
        @forelse ($reviews as $review)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" x-data="{ showReplyForm: false }">
                <!-- Review Header -->
                <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-4">
                    <div class="flex items-start space-x-4">
                        <!-- Customer Avatar -->
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                                <span class="text-emerald-600 font-semibold text-lg">
                                    {{ strtoupper(substr($review->user->name, 0, 1)) }}
                                </span>
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div class="flex-1">
                            <h4 class="text-lg font-semibold text-gray-900">{{ $review->user->name }}</h4>
                            <div class="flex items-center mt-1 space-x-2">
                                <!-- Star Rating -->
                                <div class="flex items-center">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-sm {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                    @endfor
                                </div>
                                <!-- Date -->
                                <span class="text-sm text-gray-500">• {{ $review->created_at->diffForHumans() }}</span>
                                <!-- Order Reference -->
                                @if ($review->order)
                                    <span class="text-sm text-gray-500">• Order #{{ $review->order->id }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 mt-4 md:mt-0">
                        <!-- Approval Toggle -->
                        <form method="POST" action="{{ route('vendor.reviews.toggle', $review->id) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                                           {{ $review->is_approved 
                                               ? 'bg-green-100 text-green-700 hover:bg-green-200' 
                                               : 'bg-orange-100 text-orange-700 hover:bg-orange-200' }}">
                                <i class="fas {{ $review->is_approved ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                                {{ $review->is_approved ? __('vendor.approved') : __('vendor.pending') }}
                            </button>
                        </form>

                        <!-- Reply Button -->
                        @if (!$review->vendor_reply)
                            <button @click="showReplyForm = !showReplyForm" 
                                    class="px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 text-sm font-medium transition-colors">
                                <i class="fas fa-reply mr-1"></i>{{ __('vendor.reply') }}
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Review Text -->
                <div class="mb-4">
                    <p class="text-gray-700 leading-relaxed">{{ $review->review }}</p>
                </div>

                <!-- Helpful Count -->
                @if ($review->helpful_count > 0)
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-600">
                            <i class="fas fa-thumbs-up mr-2"></i>
                            {{ $review->helpful_count }} {{ __('vendor.found_helpful') }}
                        </span>
                    </div>
                @endif

                <!-- Vendor Reply -->
                @if ($review->vendor_reply)
                    <div class="mt-4 ml-16 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-store text-emerald-600 mt-1 mr-3"></i>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <h5 class="font-semibold text-emerald-900">{{ __('vendor.vendor_response') }}</h5>
                                    <span class="text-sm text-emerald-600">{{ $review->replied_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-gray-700">{{ $review->vendor_reply }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Reply Form -->
                <div x-show="showReplyForm" x-cloak class="mt-4 ml-16">
                    <form method="POST" action="{{ route('vendor.reviews.reply', $review->id) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label for="reply-{{ $review->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                                {{ __('vendor.your_response') }}
                            </label>
                            <textarea name="reply" 
                                      id="reply-{{ $review->id }}" 
                                      rows="3" 
                                      maxlength="1000"
                                      class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"
                                      placeholder="{{ __('vendor.enter_your_response') }}"
                                      required></textarea>
                            <p class="mt-1 text-xs text-gray-500">{{ __('vendor.max_1000_characters') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" 
                                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                <i class="fas fa-paper-plane mr-2"></i>{{ __('vendor.send_reply') }}
                            </button>
                            <button type="button" 
                                    @click="showReplyForm = false"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                                {{ __('vendor.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <i class="fas fa-star text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('vendor.no_reviews_found') }}</h3>
                <p class="text-gray-600">{{ __('vendor.no_reviews_message') }}</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($reviews->hasPages())
        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
    @endif
</div>

@if (session('success'))
<div x-data="{ show: true }" 
     x-show="show" 
     x-init="setTimeout(() => show = false, 3000)"
     class="fixed bottom-4 right-4 bg-emerald-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        <span>{{ session('success') }}</span>
    </div>
</div>
@endif

@if (session('error'))
<div x-data="{ show: true }" 
     x-show="show" 
     x-init="setTimeout(() => show = false, 3000)"
     class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span>{{ session('error') }}</span>
    </div>
</div>
@endif

<style>
[x-cloak] {
    display: none !important;
}
</style>
@endsection
