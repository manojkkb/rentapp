@extends('vendor.layouts.app')

@section('title', 'Order Details - RentApp')
@section('page-title', 'Order Details')

@section('content')
<div>
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('vendor.orders.index') }}" 
           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 hover:text-emerald-600 bg-white hover:bg-emerald-50 rounded-lg border border-gray-200 transition-all active:scale-95">
            <i class="fas fa-arrow-left mr-2"></i>
            <span class="hidden sm:inline">Back to Orders</span>
            <span class="sm:hidden">Back</span>
        </a>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start space-x-3">
            <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
            <div class="flex-1">
                <p class="text-sm font-medium text-green-900">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Order Details & Items -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Order Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $order->order_number }}</h2>
                            <p class="text-sm text-gray-600">Placed {{ $order->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @php
                            $statusColors = [
                                'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700'],
                                'confirmed' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
                                'ongoing' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
                                'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-700'],
                                'cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-700'],
                            ];
                            $color = $statusColors[$order->status];
                        @endphp
                        <span class="px-4 py-2 text-sm font-semibold rounded-full {{ $color['bg'] }} {{ $color['text'] }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Customer -->
                        <div class="flex items-start space-x-3">
                            <div class="w-12 h-12 flex items-center justify-center bg-emerald-100 rounded-lg">
                                <i class="fas fa-user text-emerald-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Customer</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $order->customer->name }}</p>
                                <p class="text-xs text-gray-600">{{ $order->customer->mobile }}</p>
                                @if($order->customer->email)
                                    <p class="text-xs text-gray-600">{{ $order->customer->email }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Rental Period -->
                        <div class="flex items-start space-x-3">
                            <div class="w-12 h-12 flex items-center justify-center bg-purple-100 rounded-lg">
                                <i class="fas fa-calendar text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Rental Period</p>
                                @if($order->start_at && $order->end_at)
                                    <p class="text-sm font-semibold text-gray-900">{{ $order->start_at->format('M d, Y h:i A') }}</p>
                                    <p class="text-xs text-gray-600">to {{ $order->end_at->format('M d, Y h:i A') }}</p>
                                    <p class="text-xs text-purple-600 font-medium mt-1">
                                        {{ $order->start_at->diffInDays($order->end_at) ?: 1 }} day(s)
                                    </p>
                                @else
                                    <p class="text-sm text-gray-500 italic">Not specified</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <h3 class="text-lg font-bold text-gray-900">Order Items ({{ $order->items->count() }})</h3>
                </div>

                <!-- Items List -->
                <div class="py-4">
                    @foreach($order->items as $orderItem)
                        <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0">
                            <!-- Item Info -->
                            <div class="flex-1">
                                <div class="flex items-start space-x-3">
                                    <div class="w-12 h-12 flex items-center justify-center bg-white rounded-lg border border-gray-200">
                                        <i class="fas fa-box text-gray-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900">{{ $orderItem->item_name }}</h4>
                                        <p class="text-xs text-gray-600">{{ $orderItem->item->category->name ?? 'No Category' }}</p>
                                        <div class="flex items-center space-x-4 mt-2">
                                            <span class="text-sm font-bold text-emerald-600">₹{{ number_format($orderItem->price, 2) }}</span>
                                            <span class="text-xs text-gray-500">×</span>
                                            <span class="text-sm font-medium text-gray-900">Qty: {{ $orderItem->quantity }}</span>
                                            @if($orderItem->rent_days > 1)
                                                <span class="text-xs text-gray-500">×</span>
                                                <span class="text-sm font-medium text-gray-900">{{ $orderItem->rent_days }} days</span>
                                            @endif
                                            <span class="text-xs text-gray-500">=</span>
                                            <span class="text-sm font-bold text-gray-900">₹{{ number_format($orderItem->total_price, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right Column: Summary & Actions -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Order Summary -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                <!-- Header -->
                <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
                    <h3 class="text-lg font-bold text-gray-900">Order Summary</h3>
                </div>

                <!-- Summary Details -->
                <div class="p-6 space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Sub Total</span>
                        <span class="text-sm font-semibold text-gray-900">₹{{ number_format($order->sub_total, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Tax</span>
                        <span class="text-sm font-semibold text-gray-900">₹{{ number_format($order->tax_total, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">Discount</span>
                        <span class="text-sm font-semibold text-red-600">-₹{{ number_format($order->discount_total, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-t-2 border-gray-200 mt-2">
                        <span class="text-base font-bold text-gray-900">Grand Total</span>
                        <span class="text-lg font-bold text-emerald-600">₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>
                </div>

                <!-- Payment Status -->
                <div class="p-6 border-t border-gray-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Paid Amount</span>
                        <span class="text-sm font-semibold text-emerald-600">₹{{ number_format($order->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-t border-gray-200">
                        <span class="text-sm font-bold text-gray-900">Balance Due</span>
                        <span class="text-sm font-bold text-red-600">₹{{ number_format($order->grand_total - $order->paid_amount, 2) }}</span>
                    </div>
                </div>

                <!-- Update Status -->
                @if($order->status !== 'cancelled' && $order->status !== 'completed')
                    <div class="p-6 border-t border-gray-200">
                        <form action="{{ route('vendor.orders.update-status', $order->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Update Status</label>
                            <select name="status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 mb-3">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="ongoing" {{ $order->status === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            <button type="submit" 
                                    class="w-full px-4 py-3 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-95 shadow-sm">
                                <i class="fas fa-check mr-2"></i>
                                Update Status
                            </button>
                        </form>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="p-6 border-t border-gray-200 space-y-2">
                    <button class="w-full px-4 py-3 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        <i class="fas fa-print mr-2"></i>
                        Print Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
