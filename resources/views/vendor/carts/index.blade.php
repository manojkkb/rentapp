@extends('vendor.layouts.app')

@section('title', __('vendor.cart'))
@section('page-title', __('vendor.cart'))

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
<style>
    .flatpickr-calendar { border-radius: 12px !important; box-shadow: 0 10px 40px rgba(0,0,0,.15) !important; border: 1px solid #e5e7eb !important; font-family: 'Inter', sans-serif !important; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #059669 !important; border-color: #059669 !important; }
    .flatpickr-day.today { border-color: #059669 !important; }
    .flatpickr-day:hover { background: #d1fae5 !important; }
    .flatpickr-months .flatpickr-month { height: 40px !important; }
    .flatpickr-current-month { font-size: 1rem !important; font-weight: 600 !important; }
    .flatpickr-time input { font-size: 1rem !important; }
    .date-input-wrapper { position: relative; }
    .date-input-wrapper .date-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 14px; }
    .date-input-wrapper input { padding-right: 32px; }
    .date-clear-btn { position: absolute; right: 28px; top: 50%; transform: translateY(-50%); color: #9ca3af; cursor: pointer; font-size: 12px; padding: 2px 4px; display: none; }
    .date-clear-btn:hover { color: #ef4444; }
    .date-input-wrapper input:not([value=""]) ~ .date-clear-btn,
    .date-input-wrapper input.has-value ~ .date-clear-btn { display: block; }
</style>
@endsection

@section('content')
<!-- Header with Add Button -->
<div class="mb-6 flex items-start justify-between gap-3">
    <div class="flex-1">
        <div class="flex items-center space-x-3 mb-2">
           
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('vendor.cart') }}</h2>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-layer-group text-emerald-600 mr-1"></i>
                    <span class="font-medium">{{ $carts->total() }}</span> {{ __('vendor.cart_items') }}
                </p>
            </div>
        </div>
    </div>
    <button type="button"
            @click="$dispatch('open-create-cart-modal')" 
            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold rounded-lg transition-all shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
        <i class="fas fa-plus mr-2"></i>
        {{ __('vendor.create') }} {{ __('vendor.cart') }}
    </button>
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

<!-- Shimmer Loading Indicator -->
<div id="cartsLoadingIndicator" class="hidden">
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
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Desktop Table Shimmer -->
        <div class="hidden md:block">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-200 rounded-lg shimmer"></div>
                                    <div class="ml-3">
                                        <div class="h-4 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-28 mb-1"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-6 w-12 bg-gray-200 rounded-full shimmer"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-24"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end space-x-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-200 rounded-lg shimmer"></div>
                                    <div class="ml-3">
                                        <div class="h-4 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-28 mb-1"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-6 w-12 bg-gray-200 rounded-full shimmer"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-24"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end space-x-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-200 rounded-lg shimmer"></div>
                                    <div class="ml-3">
                                        <div class="h-4 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-28 mb-1"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-6 w-12 bg-gray-200 rounded-full shimmer"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded shimmer w-24"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end space-x-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Mobile Cards Shimmer -->
        <div class="md:hidden divide-y divide-gray-200">
            <div class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                        <div>
                            <div class="h-5 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-16 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-24"></div>
                    </div>
                    <div class="text-right">
                        <div class="h-3 bg-gray-200 rounded shimmer w-12 mb-2 ml-auto"></div>
                        <div class="h-6 w-12 bg-gray-200 rounded-full shimmer ml-auto"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                    <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                </div>
                <div class="w-full h-10 bg-gray-200 rounded-lg shimmer"></div>
            </div>
            <div class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                        <div>
                            <div class="h-5 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-16 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-24"></div>
                    </div>
                    <div class="text-right">
                        <div class="h-3 bg-gray-200 rounded shimmer w-12 mb-2 ml-auto"></div>
                        <div class="h-6 w-12 bg-gray-200 rounded-full shimmer ml-auto"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                    <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                </div>
                <div class="w-full h-10 bg-gray-200 rounded-lg shimmer"></div>
            </div>
            <div class="p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                        <div>
                            <div class="h-5 bg-gray-200 rounded shimmer w-32 mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-16 mb-2"></div>
                        <div class="h-4 bg-gray-200 rounded shimmer w-24"></div>
                    </div>
                    <div class="text-right">
                        <div class="h-3 bg-gray-200 rounded shimmer w-12 mb-2 ml-auto"></div>
                        <div class="h-6 w-12 bg-gray-200 rounded-full shimmer ml-auto"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                    <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                </div>
                <div class="w-full h-10 bg-gray-200 rounded-lg shimmer"></div>
            </div>
        </div>
    </div>
</div>

<!-- Carts List -->
<div id="cartsContainer" class="bg-white rounded-xl shadow-sm border border-gray-200">
    @if($carts->count() > 0)
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-emerald-50 to-emerald-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            {{ __('vendor.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            {{ __('vendor.customer') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            {{ __('vendor.items') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            {{ __('vendor.grand_total') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            {{ __('vendor.date') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            {{ __('vendor.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($carts as $cart)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <!-- Cart Name -->
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
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
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-full">
                                <i class="fas fa-box text-xs mr-1"></i>
                                {{ $cart->items->count() }} {{ __('vendor.items') }}
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
                               class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-95 shadow-sm">
                                <span>{{ __('vendor.view') }}</span>
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
                            <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
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
                            <p class="text-xs text-gray-500 mb-1">{{ __('vendor.grand_total') }}</p>
                            <p class="text-lg font-bold text-gray-900">₹{{ number_format($cart->grand_total, 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500 mb-1">{{ __('vendor.items') }}</p>
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-full">
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
                           class="block w-full text-center px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-95 shadow-sm">
                            <span>{{ __('vendor.view') }}</span>
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
            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('vendor.empty_cart') }}</h3>
            <p class="text-sm text-gray-500 mb-6">{{ __('vendor.create') }} {{ __('vendor.cart') }}</p>
            <button type="button"
                    @click="$dispatch('open-create-cart-modal')" 
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>
                {{ __('vendor.create') }} {{ __('vendor.cart') }}
            </button>
        </div>
    @endif
</div>

<!-- Create Cart Modal -->
<div x-data="{ showCreateModal: false }" 
     @open-create-cart-modal.window="showCreateModal = true"
     @close-create-cart-modal.window="showCreateModal = false"
     x-show="showCreateModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @click.self="showCreateModal = false">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
        <!-- Background overlay -->
        <div x-show="showCreateModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-black/50" 
             @click="showCreateModal = false"></div>

        <!-- Modal panel -->
        <div x-show="showCreateModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-10"
             @click.stop>
            
            <form id="createCartForm" method="POST" action="{{ route('vendor.carts.store') }}">
                @csrf
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-emerald-50 to-green-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 flex items-center justify-center bg-emerald-600 rounded-lg">
                                <i class="fas fa-shopping-cart text-white text-lg"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.create') }} {{ __('vendor.cart') }}</h3>
                                <p class="text-sm text-gray-600">{{ __('vendor.fill_information') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                    <!-- Customer Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('vendor.select') }} {{ __('vendor.customer') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="hidden" name="customer_id" id="modal_customer_id" required>
                        <div class="relative" id="customerSearchWrapper">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   id="customerSearchInput" 
                                   class="w-full pl-11 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                   placeholder="Search customer by name or mobile..."
                                   autocomplete="off">
                            <button type="button" id="customerClearBtn" class="absolute inset-y-0 right-0 pr-4 hidden items-center text-gray-400 hover:text-gray-600" onclick="clearCustomerSelection()">
                                <i class="fas fa-times"></i>
                            </button>
                            <!-- Dropdown -->
                            <div id="customerDropdown" class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-52 overflow-y-auto hidden">
                                <!-- Add Customer Option -->
                                <div id="addCustomerBtn" 
                                     class="px-4 py-3 flex items-center gap-2 text-emerald-600 font-semibold cursor-pointer hover:bg-emerald-50 border-b border-gray-100 sticky top-0 bg-white"
                                     onclick="openAddCustomerInline()">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Add New Customer</span>
                                </div>
                                <!-- Customer List -->
                                <div id="customerList">
                                    @foreach($customers as $customer)
                                        <div class="customer-option px-4 py-2.5 cursor-pointer hover:bg-emerald-50 flex items-center justify-between transition-colors"
                                             data-id="{{ $customer->id }}" 
                                             data-name="{{ $customer->name }}" 
                                             data-mobile="{{ $customer->mobile }}"
                                             onclick="selectCustomer(this)">
                                            <div>
                                                <span class="text-sm font-medium text-gray-900">{{ $customer->name }}</span>
                                                <span class="text-xs text-gray-500 ml-2">{{ $customer->mobile }}</span>
                                            </div>
                                            <i class="fas fa-check text-emerald-500 hidden check-icon"></i>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="customerNoResults" class="px-4 py-3 text-sm text-gray-500 text-center hidden">
                                    <i class="fas fa-search mr-1"></i> No customers found
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inline Add Customer Form -->
                    <div id="addCustomerInline" class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-lg hidden">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-emerald-800">
                                <i class="fas fa-user-plus mr-1"></i> Add New Customer
                            </h4>
                            <button type="button" onclick="closeAddCustomerInline()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Name <span class="text-red-500">*</span></label>
                                <input type="text" id="newCustomerName" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Customer name">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Mobile <span class="text-red-500">*</span></label>
                                <input type="text" id="newCustomerMobile" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="10 digit mobile" maxlength="10">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Address</label>
                            <input type="text" id="newCustomerAddress" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent" placeholder="Address (optional)">
                        </div>
                        <p id="addCustomerError" class="text-xs text-red-600 mb-2 hidden"></p>
                        <button type="button" onclick="saveNewCustomer()" id="saveCustomerBtn"
                                class="w-full px-4 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-95">
                            <i class="fas fa-save mr-1"></i> Save & Select Customer
                        </button>
                    </div>

                    <!-- Cart Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('vendor.name') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-tag text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   name="cart_name" 
                                   id="modal_cart_name" 
                                   class="w-full pl-11 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                                   placeholder="{{ __('vendor.cart_name_placeholder') }}"
                                   required>
                        </div>
                    </div>

                    <!-- Booking Dates -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-calendar text-emerald-600 mr-1"></i>
                            {{ __('vendor.booking_dates') }} <span class="text-gray-500 text-xs">({{ __('vendor.optional') }})</span>
                        </label>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Start Time -->
                            <div>
                                <label for="modal_start_time" class="block text-sm font-semibold text-gray-700 mb-1">
                                    <i class="far fa-calendar-alt text-emerald-600 mr-1"></i>{{ __('vendor.start_date_time') }}
                                </label>
                                <div class="date-input-wrapper">
                                    <input type="text" 
                                           name="start_time" 
                                           id="modal_start_time" 
                                           readonly
                                           class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white cursor-pointer"
                                           placeholder="Select start date">
                                    <span class="date-clear-btn" onclick="clearModalDate('start')" title="Clear">
                                        <i class="fas fa-times"></i>
                                    </span>
                                    <span class="date-icon"><i class="fas fa-chevron-down"></i></span>
                                </div>
                            </div>

                            <!-- End Time -->
                            <div>
                                <label for="modal_end_time" class="block text-sm font-semibold text-gray-700 mb-1">
                                    <i class="far fa-calendar-alt text-emerald-600 mr-1"></i>{{ __('vendor.end_date_time') }}
                                </label>
                                <div class="date-input-wrapper">
                                    <input type="text" 
                                           name="end_time" 
                                           id="modal_end_time" 
                                           readonly
                                           class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white cursor-pointer"
                                           placeholder="Select end date">
                                    <span class="date-clear-btn" onclick="clearModalDate('end')" title="Clear">
                                        <i class="fas fa-times"></i>
                                    </span>
                                    <span class="date-icon"><i class="fas fa-chevron-down"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Banner -->
                    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-info-circle text-emerald-600 mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm text-emerald-900 font-semibold mb-1">{{ __('vendor.after_creating_cart') }}</p>
                                <p class="text-sm text-emerald-800">
                                    {{ __('vendor.cart_management_info') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col-reverse sm:flex-row gap-3">
                    <button type="button" 
                            @click="showCreateModal = false"
                            class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition-all">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit" 
                            id="createCartSubmitBtn"
                            class="flex-1 sm:flex-none px-6 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-lg transition-all shadow-sm hover:shadow active:scale-95">
                        <i class="fas fa-save mr-2"></i>
                        <span id="createCartSubmitBtnText">{{ __('vendor.create') }} {{ __('vendor.cart') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Flatpickr Date Pickers ---
    function toggleClearBtn(instance) {
        const wrapper = instance.element.closest('.date-input-wrapper');
        if (!wrapper) return;
        const clearBtn = wrapper.querySelector('.date-clear-btn');
        if (clearBtn) {
            clearBtn.style.display = instance.selectedDates.length > 0 ? 'block' : 'none';
        }
    }

    const fpConfig = {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'M j, Y h:i K',
        time_24hr: false,
        allowInput: false,
        disableMobile: false,
        monthSelectorType: 'dropdown',
        animate: true,
        onReady: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
        },
        onChange: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
        }
    };

    const modalStartPicker = flatpickr('#modal_start_time', {
        ...fpConfig,
        onChange: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
            if (selectedDates.length > 0) {
                modalEndPicker.set('minDate', selectedDates[0]);
            } else {
                modalEndPicker.set('minDate', null);
            }
        }
    });

    const modalEndPicker = flatpickr('#modal_end_time', {
        ...fpConfig,
        onChange: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
            if (selectedDates.length > 0) {
                modalStartPicker.set('maxDate', selectedDates[0]);
            } else {
                modalStartPicker.set('maxDate', null);
            }
        }
    });

    window.clearModalDate = function(which) {
        if (which === 'start') {
            modalStartPicker.clear();
            modalEndPicker.set('minDate', null);
        } else {
            modalEndPicker.clear();
            modalStartPicker.set('maxDate', null);
        }
        toggleClearBtn(which === 'start' ? modalStartPicker : modalEndPicker);
    };

    const loader = document.getElementById('cartsLoadingIndicator');
    const container = document.getElementById('cartsContainer');
    
    // Show shimmer briefly for better UX
    if (loader && container) {
        loader.classList.remove('hidden');
        container.classList.add('hidden');
        
        setTimeout(() => {
            loader.classList.add('hidden');
            container.classList.remove('hidden');
        }, 300); // Brief shimmer effect
    }

    // Handle create cart form submission
    const createCartForm = document.getElementById('createCartForm');
    if (createCartForm) {
        createCartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('createCartSubmitBtn');
            const submitBtnText = document.getElementById('createCartSubmitBtnText');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creating...';
            
            const formData = new FormData(createCartForm);
            
            fetch('{{ route('vendor.carts.store') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Cart created successfully!', 'success');
                    
                    // Reset form
                    createCartForm.reset();
                    clearCustomerSelection();
                    closeAddCustomerInline();
                    modalStartPicker.clear();
                    modalEndPicker.clear();
                    modalEndPicker.set('minDate', null);
                    modalStartPicker.set('maxDate', null);
                    
                    // Close modal
                    window.dispatchEvent(new CustomEvent('close-create-cart-modal'));
                    
                    // Reload page to show new cart
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    showToast(data.message || 'Error creating cart', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error creating cart', 'error');
            })
            .finally(() => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtnText.innerHTML = '<i class="fas fa-save mr-2"></i>{{ __('vendor.create') }} {{ __('vendor.cart') }}';
            });
        });
    }
});

// Toast notification function
function showToast(message, type = 'success') {
    const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 z-50`;
    toast.innerHTML = `
        <i class="fas ${icon} text-2xl"></i>
        <div>
            <p class="font-medium">${message}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-100">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// --- Customer Search & Add ---
const customerSearchInput = document.getElementById('customerSearchInput');
const customerDropdown = document.getElementById('customerDropdown');
const customerList = document.getElementById('customerList');
const customerNoResults = document.getElementById('customerNoResults');
const customerClearBtn = document.getElementById('customerClearBtn');
const customerIdInput = document.getElementById('modal_customer_id');

customerSearchInput.addEventListener('focus', function() {
    customerDropdown.classList.remove('hidden');
    filterCustomers();
});

customerSearchInput.addEventListener('input', function() {
    customerDropdown.classList.remove('hidden');
    filterCustomers();
});

document.addEventListener('click', function(e) {
    if (!document.getElementById('customerSearchWrapper').contains(e.target)) {
        customerDropdown.classList.add('hidden');
    }
});

function filterCustomers() {
    const query = customerSearchInput.value.toLowerCase().trim();
    const options = customerList.querySelectorAll('.customer-option');
    let hasVisible = false;

    options.forEach(function(opt) {
        const name = opt.dataset.name.toLowerCase();
        const mobile = opt.dataset.mobile.toLowerCase();
        if (name.includes(query) || mobile.includes(query)) {
            opt.classList.remove('hidden');
            hasVisible = true;
        } else {
            opt.classList.add('hidden');
        }
    });

    customerNoResults.classList.toggle('hidden', hasVisible || query === '');
}

function selectCustomer(el) {
    customerIdInput.value = el.dataset.id;
    customerSearchInput.value = el.dataset.name + ' - ' + el.dataset.mobile;
    customerDropdown.classList.add('hidden');
    customerClearBtn.classList.remove('hidden');
    customerClearBtn.classList.add('flex');

    // Show check on selected
    customerList.querySelectorAll('.check-icon').forEach(i => i.classList.add('hidden'));
    el.querySelector('.check-icon').classList.remove('hidden');
}

function clearCustomerSelection() {
    customerIdInput.value = '';
    customerSearchInput.value = '';
    customerClearBtn.classList.add('hidden');
    customerClearBtn.classList.remove('flex');
    customerList.querySelectorAll('.check-icon').forEach(i => i.classList.add('hidden'));
    customerSearchInput.focus();
}

function openAddCustomerInline() {
    customerDropdown.classList.add('hidden');
    document.getElementById('addCustomerInline').classList.remove('hidden');
    document.getElementById('newCustomerName').value = customerSearchInput.value.trim();
    document.getElementById('newCustomerMobile').value = '';
    document.getElementById('newCustomerAddress').value = '';
    document.getElementById('addCustomerError').classList.add('hidden');
    document.getElementById('newCustomerName').focus();
}

function closeAddCustomerInline() {
    document.getElementById('addCustomerInline').classList.add('hidden');
}

function saveNewCustomer() {
    const name = document.getElementById('newCustomerName').value.trim();
    const mobile = document.getElementById('newCustomerMobile').value.trim();
    const address = document.getElementById('newCustomerAddress').value.trim();
    const errorEl = document.getElementById('addCustomerError');
    const saveBtn = document.getElementById('saveCustomerBtn');

    if (!name || !mobile) {
        errorEl.textContent = 'Name and mobile are required.';
        errorEl.classList.remove('hidden');
        return;
    }
    if (!/^\d{10}$/.test(mobile)) {
        errorEl.textContent = 'Mobile must be exactly 10 digits.';
        errorEl.classList.remove('hidden');
        return;
    }

    errorEl.classList.add('hidden');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('{{ route("vendor.customers.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name, mobile, address })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.customer) {
            const c = data.customer;
            // Add to dropdown list
            const optionHtml = `<div class="customer-option px-4 py-2.5 cursor-pointer hover:bg-emerald-50 flex items-center justify-between transition-colors"
                 data-id="${c.id}" data-name="${c.name}" data-mobile="${c.mobile}"
                 onclick="selectCustomer(this)">
                <div>
                    <span class="text-sm font-medium text-gray-900">${c.name}</span>
                    <span class="text-xs text-gray-500 ml-2">${c.mobile}</span>
                </div>
                <i class="fas fa-check text-emerald-500 hidden check-icon"></i>
            </div>`;
            customerList.insertAdjacentHTML('afterbegin', optionHtml);

            // Auto-select the new customer
            const newOpt = customerList.querySelector('.customer-option');
            selectCustomer(newOpt);

            closeAddCustomerInline();
            showToast('Customer added successfully!', 'success');
        } else {
            errorEl.textContent = data.message || 'Error creating customer.';
            errorEl.classList.remove('hidden');
        }
    })
    .catch(err => {
        console.error(err);
        errorEl.textContent = 'Something went wrong. Please try again.';
        errorEl.classList.remove('hidden');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-save mr-1"></i> Save & Select Customer';
    });
}
</script>
@endsection
