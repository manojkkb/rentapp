@extends('vendor.layouts.app')

@section('title', 'Carts - RentApp')
@section('page-title', 'Carts')

@section('content')
<!-- Header with Add Button -->
<div class="mb-6 flex items-start justify-between gap-3">
    <div class="flex-1">
        <div class="flex items-center space-x-3 mb-2">
            <div class="w-12 h-12 flex items-center justify-center bg-blue-100 rounded-xl">
                <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Carts</h2>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-layer-group text-blue-600 mr-1"></i>
                    <span class="font-medium">{{ $carts->total() }}</span> carts
                </p>
            </div>
        </div>
    </div>
    <a href="{{ route('vendor.carts.create') }}" 
       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold rounded-lg transition-all shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
        <i class="fas fa-plus mr-2"></i>
        Add<span class="hidden sm:inline ml-1">Cart</span>
    </a>
</div>

<!-- Messages -->
@if (session('success'))
    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
            <p class="text-emerald-700 text-sm">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
            <div class="text-red-700 text-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- Carts List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    @if($carts->count() > 0)
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-blue-50 to-blue-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Cart Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Items
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Grand Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Created
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($carts as $cart)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <!-- Cart Name -->
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-shopping-cart text-white text-sm"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-semibold text-gray-900">{{ $cart->cart_name }}</p>
                                    <p class="text-xs text-gray-500">ID: #{{ $cart->id }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Customer -->
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $cart->customer->name ?? 'N/A' }}</p>
                                @if($cart->customer)
                                    <p class="text-xs text-gray-500">
                                        <i class="fas fa-phone text-xs mr-1"></i>
                                        {{ $cart->customer->mobile }}
                                    </p>
                                @endif
                            </div>
                        </td>

                        <!-- Items Count -->
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">
                                <i class="fas fa-box text-xs mr-1"></i>
                                {{ $cart->items->count() }} items
                            </span>
                        </td>

                        <!-- Grand Total -->
                        <td class="px-6 py-4">
                            <p class="text-sm font-bold text-gray-900">₹{{ number_format($cart->grand_total, 2) }}</p>
                            @if($cart->paid_amount > 0)
                                <p class="text-xs text-emerald-600">
                                    Paid: ₹{{ number_format($cart->paid_amount, 2) }}
                                </p>
                            @endif
                        </td>

                        <!-- Created -->
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $cart->created_at->format('M d, Y') }}
                            <p class="text-xs text-gray-500">
                                {{ $cart->created_at->diffForHumans() }}
                            </p>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('vendor.carts.show', $cart->id) }}" 
                               class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 shadow-sm">
                                <span>Continue</span>
                                <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-gray-200">
            @foreach($carts as $cart)
            <div class="p-4">
                <!-- Cart Card -->
                <div class="space-y-3">
                    <!-- Header -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-shopping-cart text-white text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-semibold text-gray-900 truncate">
                                    {{ $cart->cart_name }}
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <i class="fas fa-user text-xs mr-1"></i>
                                    {{ $cart->customer->name ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Grand Total</p>
                            <p class="text-lg font-bold text-gray-900">₹{{ number_format($cart->grand_total, 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 mb-1">Items</p>
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">
                                <i class="fas fa-box text-xs mr-1"></i>
                                {{ $cart->items->count() }}
                            </span>
                        </div>
                    </div>

                    <!-- Footer Info -->
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>
                            <i class="fas fa-calendar mr-1"></i>
                            {{ $cart->created_at->format('M d, Y') }}
                        </span>
                        @if($cart->paid_amount > 0)
                            <span class="text-emerald-600 font-medium">
                                <i class="fas fa-check-circle mr-1"></i>
                                Paid: ₹{{ number_format($cart->paid_amount, 2) }}
                            </span>
                        @endif
                    </div>

                    <!-- Continue Button -->
                    <div>
                        <a href="{{ route('vendor.carts.show', $cart->id) }}" 
                           class="block w-full text-center px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 shadow-sm">
                            <span>Continue</span>
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($carts->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $carts->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <i class="fas fa-shopping-cart text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Carts Yet</h3>
            <p class="text-sm text-gray-500 mb-6">Start creating carts for your customers</p>
            <a href="{{ route('vendor.carts.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Create Your First Cart
            </a>
        </div>
    @endif
</div>
@endsection
