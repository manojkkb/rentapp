@extends('vendor.layouts.app')

@section('title', __('vendor.orders_management'))
@section('page-title', __('vendor.orders_management'))

@section('content')
<div>
    <!-- Header with Search and Filter -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <!-- Search Bar -->
        <form method="GET" action="{{ route('vendor.orders.index') }}" class="flex-1 max-w-md">
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="{{ __('vendor.search_by_order') }}" 
                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                @if(request('search'))
                    <a href="{{ route('vendor.orders.index') }}" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Status Filter Tabs -->
    <div class="mb-6 bg-white rounded-lg p-2 shadow-sm border border-gray-200">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('vendor.orders.index') }}" 
               class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ !request('status') ? 'bg-emerald-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                {{ __('vendor.all_orders') }}
                <span class="ml-1 text-xs px-2 py-0.5 rounded-full {{ !request('status') ? 'bg-white text-emerald-600' : 'bg-gray-200' }}">
                    {{ $statusCounts['all'] }}
                </span>
            </a>
            <a href="{{ route('vendor.orders.index', ['status' => 'pending']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ request('status') === 'pending' ? 'bg-yellow-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                {{ __('vendor.pending') }}
                <span class="ml-1 text-xs px-2 py-0.5 rounded-full {{ request('status') === 'pending' ? 'bg-white text-yellow-600' : 'bg-gray-200' }}">
                    {{ $statusCounts['pending'] }}
                </span>
            </a>
            <a href="{{ route('vendor.orders.index', ['status' => 'confirmed']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ request('status') === 'confirmed' ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                {{ __('vendor.confirmed') }}
                <span class="ml-1 text-xs px-2 py-0.5 rounded-full {{ request('status') === 'confirmed' ? 'bg-white text-blue-600' : 'bg-gray-200' }}">
                    {{ $statusCounts['confirmed'] }}
                </span>
            </a>
            <a href="{{ route('vendor.orders.index', ['status' => 'ongoing']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ request('status') === 'ongoing' ? 'bg-purple-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                {{ __('vendor.ongoing') }}
                <span class="ml-1 text-xs px-2 py-0.5 rounded-full {{ request('status') === 'ongoing' ? 'bg-white text-purple-600' : 'bg-gray-200' }}">
                    {{ $statusCounts['ongoing'] }}
                </span>
            </a>
            <a href="{{ route('vendor.orders.index', ['status' => 'completed']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ request('status') === 'completed' ? 'bg-green-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                {{ __('vendor.completed') }}
                <span class="ml-1 text-xs px-2 py-0.5 rounded-full {{ request('status') === 'completed' ? 'bg-white text-green-600' : 'bg-gray-200' }}">
                    {{ $statusCounts['completed'] }}
                </span>
            </a>
            <a href="{{ route('vendor.orders.index', ['status' => 'cancelled']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ request('status') === 'cancelled' ? 'bg-red-500 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                {{ __('vendor.cancelled') }}
                <span class="ml-1 text-xs px-2 py-0.5 rounded-full {{ request('status') === 'cancelled' ? 'bg-white text-red-600' : 'bg-gray-200' }}">
                    {{ $statusCounts['cancelled'] }}
                </span>
            </a>
        </div>
    </div>

    <!-- Success Message -->
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

    <!-- Shimmer Loading Indicator -->
    <div id="ordersLoadingIndicator" class="hidden">
        <style>
            @keyframes shimmer {
                0% { background-position: -1000px 0; }
                100% { background-position: 1000px 0; }
            }
            .shimmer {
                animation: shimmer 2s infinite linear;
                background: linear-gradient(to right, #f0f0f0 8%, #e0e0e0 18%, #f0f0f0 33%);
                background-size: 1000px 100%;
            }
        </style>
        
        <!-- Desktop Table Shimmer -->
        <div class="hidden md:block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-32"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-28"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-28 mb-2"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-32"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-6 w-10 bg-gray-200 rounded-full shimmer"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-20"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-6 w-20 bg-gray-200 rounded-full shimmer"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-8 w-16 bg-gray-200 rounded-lg shimmer"></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-28 mb-2"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-32"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-6 w-10 bg-gray-200 rounded-full shimmer"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-20"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-6 w-20 bg-gray-200 rounded-full shimmer"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-8 w-16 bg-gray-200 rounded-lg shimmer"></div>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-28 mb-2"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-32"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-6 w-10 bg-gray-200 rounded-full shimmer"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-20"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-6 w-20 bg-gray-200 rounded-full shimmer"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-8 w-16 bg-gray-200 rounded-lg shimmer"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Mobile Cards Shimmer -->
        <div class="md:hidden space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-28 mb-2"></div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                    </div>
                    <div class="h-6 w-20 bg-gray-200 rounded-full shimmer"></div>
                </div>
                <div class="flex items-center space-x-3 mb-4 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 bg-gray-200 rounded-full shimmer"></div>
                    <div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-32 mb-1"></div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-28"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-12 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-20"></div>
                    </div>
                    <div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-16 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-24"></div>
                    </div>
                </div>
                <div class="h-9 bg-gray-200 rounded-lg shimmer"></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-28 mb-2"></div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                    </div>
                    <div class="h-6 w-20 bg-gray-200 rounded-full shimmer"></div>
                </div>
                <div class="flex items-center space-x-3 mb-4 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 bg-gray-200 rounded-full shimmer"></div>
                    <div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-32 mb-1"></div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-28"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-12 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-20"></div>
                    </div>
                    <div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-16 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-24"></div>
                    </div>
                </div>
                <div class="h-9 bg-gray-200 rounded-lg shimmer"></div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-28 mb-2"></div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                    </div>
                    <div class="h-6 w-20 bg-gray-200 rounded-full shimmer"></div>
                </div>
                <div class="flex items-center space-x-3 mb-4 pb-4 border-b border-gray-100">
                    <div class="w-10 h-10 bg-gray-200 rounded-full shimmer"></div>
                    <div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-32 mb-1"></div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-28"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-12 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-20"></div>
                    </div>
                    <div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-16 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-24"></div>
                    </div>
                </div>
                <div class="h-9 bg-gray-200 rounded-lg shimmer"></div>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div id="ordersContainer">
    @if($orders->count() > 0)
        <!-- Desktop View -->
        <div class="hidden md:block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-emerald-50 to-green-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('vendor.order_details') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('vendor.customer') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('vendor.date') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('vendor.items_ordered') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('vendor.total') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('vendor.status') }}</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('vendor.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="far fa-calendar mr-1"></i>
                                            {{ $order->start_at?->format('M d') }} - {{ $order->end_at?->format('M d, Y') }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                            <span class="text-emerald-600 font-semibold text-sm">
                                                {{ strtoupper(substr($order->customer->name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $order->customer->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $order->customer->mobile }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-900">{{ $order->created_at->format('M d, Y') }}</p>
                                    <p class="text-xs text-gray-500">{{ $order->created_at->format('h:i A') }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-gray-900">{{ $order->items->count() }} items</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-bold text-emerald-600">₹{{ number_format($order->grand_total, 2) }}</p>
                                    @if($order->paid_amount > 0)
                                        <p class="text-xs text-gray-500">Paid: ₹{{ number_format($order->paid_amount, 2) }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'confirmed' => 'bg-blue-100 text-blue-700',
                                            'ongoing' => 'bg-purple-100 text-purple-700',
                                            'completed' => 'bg-green-100 text-green-700',
                                            'cancelled' => 'bg-red-100 text-red-700',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusColors[$order->status] }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('vendor.orders.show', $order->id) }}" 
                                       class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors">
                                        <i class="fas fa-eye mr-1"></i>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile View -->
        <div class="md:hidden space-y-4">
            @foreach($orders as $order)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <!-- Order Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $order->order_number }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'confirmed' => 'bg-blue-100 text-blue-700',
                                'ongoing' => 'bg-purple-100 text-purple-700',
                                'completed' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusColors[$order->status] }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>

                    <!-- Customer Info -->
                    <div class="flex items-center space-x-3 mb-4 pb-4 border-b border-gray-100">
                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                            <span class="text-emerald-600 font-semibold text-sm">
                                {{ strtoupper(substr($order->customer->name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $order->customer->name }}</p>
                            <p class="text-xs text-gray-500">{{ $order->customer->mobile }}</p>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Items</span>
                            <span class="text-sm font-medium text-gray-900">{{ $order->items->count() }} items</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total Amount</span>
                            <span class="text-sm font-bold text-emerald-600">₹{{ number_format($order->grand_total, 2) }}</span>
                        </div>
                        @if($order->start_at && $order->end_at)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Rental Period</span>
                                <span class="text-xs text-gray-900">
                                    {{ $order->start_at->format('M d') }} - {{ $order->end_at->format('M d, Y') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Action Button -->
                    <a href="{{ route('vendor.orders.show', $order->id) }}" 
                       class="block w-full text-center px-4 py-2.5 text-sm font-semibold text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors">
                        <i class="fas fa-eye mr-1"></i>
                        View Details
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12">
            <div class="text-center">
                <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center bg-gray-100 rounded-full">
                    <i class="fas fa-receipt text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Orders Found</h3>
                <p class="text-sm text-gray-600 mb-6">
                    @if(request('search'))
                        No orders match your search criteria.
                    @elseif(request('status'))
                        No orders with status "{{ request('status') }}".
                    @else
                        You don't have any orders yet. Orders will appear here once placed from carts.
                    @endif
                </p>
                @if(request('search') || request('status'))
                    <a href="{{ route('vendor.orders.index') }}" 
                       class="inline-flex items-center px-4 py-2 text-sm font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors">
                        <i class="fas fa-undo mr-2"></i>
                        Clear Filters
                    </a>
                @endif
            </div>
        </div>
    @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const loader = document.getElementById('ordersLoadingIndicator');
    const container = document.getElementById('ordersContainer');
    
    // Show shimmer briefly for better UX
    if (loader && container) {
        loader.classList.remove('hidden');
        container.classList.add('hidden');
        
        setTimeout(() => {
            loader.classList.add('hidden');
            container.classList.remove('hidden');
        }, 300); // Brief shimmer effect
    }
});
</script>
@endsection
