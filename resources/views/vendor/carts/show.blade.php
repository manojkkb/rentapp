@extends('vendor.layouts.app')

@section('title', __('vendor.cart_details'))
@section('page-title', __('vendor.cart_details'))

@section('content')
<div x-data="{
    showAddItem: {{ $errors->any() ? 'true' : 'false' }},
    selectedItems: {},
    editingItem: null,
    editQuantity: 1,
    isSubmitting: false,
    searchQuery: '',
    selectedCategory: '',
    items: {{ $availableItems->toJson() }},
    toggleItem(itemId) {
        if (this.selectedItems[itemId]) {
            delete this.selectedItems[itemId];
        } else {
            this.selectedItems[itemId] = 1;
        }
    },
    updateQuantity(itemId, quantity) {
        if (quantity > 0) {
            this.selectedItems[itemId] = parseInt(quantity);
        }
    },
    get hasSelectedItems() {
        return Object.keys(this.selectedItems).length > 0;
    },
    get selectedCount() {
        return Object.keys(this.selectedItems).length;
    },
    get filteredItems() {
        return this.items.filter(item => {
            const matchesSearch = !this.searchQuery || 
                item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                (item.category && item.category.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
            const matchesCategory = !this.selectedCategory || 
                (item.category_id == this.selectedCategory);
            return matchesSearch && matchesCategory;
        });
    },
    selectAllFiltered() {
        this.filteredItems.forEach(item => {
            if (!this.selectedItems[item.id]) {
                this.selectedItems[item.id] = 1;
            }
        });
    },
    deselectAll() {
        this.selectedItems = {};
    }
}">
    
    <!-- Back Button -->
    <div class="mb-4 md:mb-6">
        <a href="{{ route('vendor.carts.index') }}" 
           class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 bg-white hover:bg-blue-50 rounded-lg border border-gray-200 transition-all active:scale-95">
            <i class="fas fa-arrow-left mr-2"></i>
            <span class="hidden sm:inline">{{ __('vendor.back') }}</span>
            <span class="sm:hidden">{{ __('vendor.back') }}</span>
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

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
            <div class="flex items-start space-x-3">
                <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-900 mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-sm text-red-800 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column: Cart Info & Items -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Cart Information -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="px-4 py-4 md:px-6 md:py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 md:w-12 md:h-12 flex items-center justify-center bg-blue-600 rounded-lg">
                                <i class="fas fa-shopping-cart text-white text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-lg md:text-xl font-bold text-gray-900">{{ $cart->cart_name }}</h2>
                                <p class="text-xs md:text-sm text-gray-600">Created {{ $cart->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('vendor.carts.edit', $cart->id) }}" 
                           class="px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                            <i class="fas fa-edit mr-1"></i>
                            Edit
                        </a>
                    </div>
                </div>

                <!-- Cart Details -->
                <div class="p-4 md:p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Customer -->
                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 flex items-center justify-center bg-emerald-100 rounded-lg">
                                <i class="fas fa-user text-emerald-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">{{ __('vendor.customer') }}</p>
                                <p class="text-sm font-semibold text-gray-900">{{ $cart->customer->name }}</p>
                                <p class="text-xs text-gray-600">{{ $cart->customer->mobile }}</p>
                            </div>
                        </div>

                        <!-- Booking Dates -->
                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 flex items-center justify-center bg-purple-100 rounded-lg">
                                <i class="fas fa-calendar text-purple-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-medium">Booking Period</p>
                                @if($cart->start_time && $cart->end_time)
                                    <p class="text-sm font-semibold text-gray-900">{{ $cart->start_time->format('M d, Y h:i A') }}</p>
                                    <p class="text-xs text-gray-600">to {{ $cart->end_time->format('M d, Y h:i A') }}</p>
                                @else
                                    <p class="text-sm text-gray-500 italic">Not specified</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="px-4 py-4 md:px-6 md:py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.cart_items') }}</h3>
                            <p class="text-sm text-gray-600">{{ $cart->items->count() }} {{ __('vendor.items') }}</p>
                        </div>
                        <button @click="showAddItem = true" 
                                class="flex items-center flex-shrink-0 px-3 py-2 md:px-4 md:py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 shadow-sm">
                            <i class="fas fa-plus mr-1 md:mr-2"></i>
                            <span class="text-xs md:text-sm">{{ __('vendor.add_item') }}</span>
                        </button>
                    </div>
                </div>

                <!-- Items List -->
                <div class="py-4 md:py-6">
                    @if($cart->items->count() > 0)
                        <div class="space-y-0 divide-y divide-gray-200">
                            @foreach($cart->items as $cartItem)
                                <div class="flex items-center justify-between px-4 py-4 md:px-6 bg-white hover:bg-gray-50 transition-colors">
                                    <!-- Item Info -->
                                    <div class="flex-1">
                                        <div class="flex items-start space-x-3">
                                            <div class="w-12 h-12 flex items-center justify-center bg-white rounded-lg border border-gray-200">
                                                <i class="fas fa-box text-gray-600"></i>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-gray-900">{{ $cartItem->item->name }}</h4>
                                                <p class="text-xs text-gray-600">{{ $cartItem->item->category->name ?? __('vendor.no_category') }}</p>
                                                <div class="flex items-center space-x-4 mt-2">
                                                    <span class="text-sm font-bold text-blue-600">₹{{ number_format($cartItem->item->price, 2) }}</span>
                                                    <span class="text-xs text-gray-500">x</span>
                                                    
                                                    <!-- Quantity Display/Edit -->
                                                    <div x-show="editingItem !== {{ $cartItem->item_id }}" class="flex items-center space-x-2">
                                                        <span class="text-sm font-medium text-gray-900">{{ __('vendor.quantity') }}: {{ $cartItem->quantity }}</span>
                                                        <button @click="editingItem = {{ $cartItem->item_id }}; editQuantity = {{ $cartItem->quantity }}" 
                                                                class="text-xs text-blue-600 hover:text-blue-700">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    <!-- Quantity Edit Form -->
                                                    <form x-show="editingItem === {{ $cartItem->item_id }}" 
                                                          action="{{ route('vendor.carts.items.update', [$cart->id, $cartItem->item_id]) }}" 
                                                          method="POST" 
                                                          class="flex items-center space-x-2"
                                                          style="display: none;">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="number" 
                                                               name="quantity" 
                                                               x-model="editQuantity"
                                                               min="1" 
                                                               class="w-16 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                                                        <button type="submit" class="text-green-600 hover:text-green-700">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button type="button" @click="editingItem = null" class="text-red-600 hover:text-red-700">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <span class="text-xs text-gray-500">=</span>
                                                    <span class="text-sm font-bold text-gray-900">₹{{ number_format($cartItem->item->price * $cartItem->quantity, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Remove Button -->
                                    <form action="{{ route('vendor.carts.items.remove', [$cart->id, $cartItem->item_id]) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('{{ __('vendor.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="ml-4 p-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center bg-gray-100 rounded-full">
                                <i class="fas fa-shopping-cart text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('vendor.no_items_yet') }}</h3>
                            <p class="text-sm text-gray-600 mb-4">{{ __('vendor.add_to_cart') }}</p>
                            <button @click="showAddItem = true" 
                                    class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95">
                                <i class="fas fa-plus mr-2"></i>
                                {{ __('vendor.add_item') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                <!-- Header -->
                <div class="px-4 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
                    <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.summary') }}</h3>
                </div>

                <!-- Summary Details -->
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">{{ __('vendor.sub_total') }}</span>
                        <span class="text-sm font-semibold text-gray-900">₹{{ number_format($cart->sub_total, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">{{ __('vendor.tax') }} (10%)</span>
                        <span class="text-sm font-semibold text-gray-900">₹{{ number_format($cart->tax_total, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm text-gray-600">{{ __('vendor.discount') }}</span>
                        <span class="text-sm font-semibold text-red-600">-₹{{ number_format($cart->discount_total, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-t-2 border-gray-200 mt-2">
                        <span class="text-base font-bold text-gray-900">{{ __('vendor.grand_total') }}</span>
                        <span class="text-lg font-bold text-emerald-600">₹{{ number_format($cart->grand_total, 2) }}</span>
                    </div>
                </div>

                <!-- Payment Status -->
                <div class="p-4 border-t border-gray-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ __('vendor.token_amount') }}</span>
                        <span class="text-sm font-semibold text-gray-900">₹{{ number_format($cart->token_amount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">{{ __('vendor.paid_amount') }}</span>
                        <span class="text-sm font-semibold text-emerald-600">₹{{ number_format($cart->paid_amount, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-t border-gray-200">
                        <span class="text-sm font-bold text-gray-900">{{ __('vendor.balance_due') }}</span>
                        <span class="text-sm font-bold text-red-600">₹{{ number_format($cart->grand_total - $cart->paid_amount, 2) }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="p-4 border-t border-gray-200 space-y-2">
                    @if($cart->items->count() > 0)
                        <form action="{{ route('vendor.carts.place-order', $cart->id) }}" 
                              method="POST" 
                              onsubmit="return confirm('{{ __('vendor.confirm_place_order') }}')">
                            @csrf
                            <button type="submit" 
                                    class="w-full px-4 py-3 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-95 shadow-sm">
                                <i class="fas fa-check-circle mr-2"></i>
                                {{ __('vendor.place_order') }}
                            </button>
                        </form>
                    @else
                        <button disabled 
                                class="w-full px-4 py-3 text-sm font-semibold text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ __('vendor.place_order') }}
                        </button>
                    @endif
                    <button class="w-full px-4 py-3 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        <i class="fas fa-print mr-2"></i>
                        {{ __('vendor.print') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Modal - Full Screen -->
    <div x-show="showAddItem" 
         x-cloak
         class="fixed inset-0 z-50">
        <!-- Background Overlay -->
        <div @click="showAddItem = false; selectedItems = {}; searchQuery = ''; selectedCategory = ''"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity"></div>

        <!-- Modal Content - Full Screen -->
        <div x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             class="relative bg-white h-full flex flex-col z-10 md:m-4 md:rounded-xl md:shadow-2xl md:h-auto md:max-h-[95vh]">
            
            <!-- Modal Header -->
            <div class="flex-shrink-0 px-4 md:px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-900">Add Items to Cart</h3>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">Select items and set quantities</p>
                    </div>
                    <button @click="showAddItem = false; selectedItems = {}; searchQuery = ''; selectedCategory = ''" 
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form action="{{ route('vendor.carts.items.add', $cart->id) }}" 
                  method="POST" 
                  class="flex-1 flex flex-col overflow-hidden"
                  @submit="isSubmitting = true">
                @csrf

                <!-- Search and Filter Section -->
                <div class="flex-shrink-0 px-4 md:px-6 py-4 bg-white border-b border-gray-200 space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <!-- Search Input -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   x-model="searchQuery"
                                   placeholder="{{ __('vendor.search_placeholder') }}"
                                   class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <button type="button" 
                                    x-show="searchQuery"
                                    @click="searchQuery = ''"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Category Filter -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-filter text-gray-400"></i>
                            </div>
                            <select x-model="selectedCategory"
                                    class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none bg-white">
                                <option value="">{{ __('vendor.all_categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filter Results Info -->
                    <div class="flex items-center justify-between text-sm">
                        <p class="text-gray-600">
                            <span x-text="filteredItems.length"></span> {{ __('vendor.items') }}
                            <span x-show="hasSelectedItems" class="ml-3 text-blue-600 font-medium">
                                • <span x-text="selectedCount"></span> {{ __('vendor.selected') }}
                            </span>
                        </p>
                        <div class="flex items-center space-x-2">
                            <button type="button"
                                    @click="selectAllFiltered()"
                                    x-show="filteredItems.length > 0"
                                    class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                {{ __('vendor.select_all') }}
                            </button>
                            <span x-show="hasSelectedItems" class="text-gray-300">|</span>
                            <button type="button"
                                    @click="deselectAll()"
                                    x-show="hasSelectedItems"
                                    class="text-xs text-red-600 hover:text-red-700 font-medium">
                                {{ __('vendor.deselect_all') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Items Table Container -->
                <div class="flex-1 overflow-y-auto">
                    <!-- No Items State -->
                    <div x-show="items.length === 0" class="flex items-center justify-center h-full p-8">
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-box-open text-3xl text-yellow-600"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Available Items</h3>
                            <p class="text-sm text-gray-600 mb-4">You don't have any active items yet</p>
                            <a href="{{ route('vendor.items.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all">
                                <i class="fas fa-plus mr-2"></i>
                                Create Your First Item
                            </a>
                        </div>
                    </div>

                    <!-- No Search Results State -->
                    <div x-show="items.length > 0 && filteredItems.length === 0" class="flex items-center justify-center h-full p-8">
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-search text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('vendor.no_items_found') }}</h3>
                            <p class="text-sm text-gray-600 mb-3">{{ __('vendor.adjust_search') }}</p>
                            <button type="button" 
                                    @click="searchQuery = ''; selectedCategory = ''"
                                    class="text-sm text-blue-600 hover:text-blue-700 font-semibold">
                                <i class="fas fa-redo mr-1"></i>
                                {{ __('vendor.clear_filters') }}
                            </button>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <table class="hidden md:table w-full" x-show="filteredItems.length > 0">
                        <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase w-12">
                                    <input type="checkbox" 
                                           @click="$event.target.checked ? selectAllFiltered() : deselectAll()"
                                           class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">{{ __('vendor.item') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">{{ __('vendor.price') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase w-32">{{ __('vendor.quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="item in filteredItems" :key="item.id">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <!-- Checkbox -->
                                    <td class="px-4 py-3">
                                        <input type="checkbox" 
                                               :checked="selectedItems[item.id] !== undefined"
                                               @change="toggleItem(item.id)"
                                               class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                    </td>
                                    
                                    <!-- Item Info -->
                                    <td class="px-4 py-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-box text-blue-600 text-sm"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-900 truncate" x-text="item.name"></p>
                                                <p class="text-xs text-gray-500 truncate" x-text="item.category ? item.category.name : '{{ __('vendor.no_category') }}'"></p>
                                                <p class="text-xs text-gray-500" x-show="item.manage_stock">
                                                    {{ __('vendor.stock') }}: <span x-text="item.stock"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Price -->
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-bold text-gray-900">₹<span x-text="parseFloat(item.price).toFixed(2)"></span></p>
                                    </td>
                                    
                                    <!-- Quantity Input -->
                                    <td class="px-4 py-3">
                                        <input type="number" 
                                               x-model="selectedItems[item.id]"
                                               @input="updateQuantity(item.id, $event.target.value)"
                                               min="1" 
                                               :disabled="selectedItems[item.id] === undefined"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
                                               placeholder="{{ __('vendor.quantity') }}">
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <!-- Mobile Card View -->
                    <div class="md:hidden divide-y divide-gray-200" x-show="filteredItems.length > 0">
                        <template x-for="item in filteredItems" :key="item.id">
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start space-x-3">
                                    <!-- Checkbox -->
                                    <div class="pt-1">
                                        <input type="checkbox" 
                                               :checked="selectedItems[item.id] !== undefined"
                                               @change="toggleItem(item.id)"
                                               class="w-5 h-5 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <!-- Item Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start">
                                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-box text-blue-600"></i>
                                            </div>
                                            <div class="ml-3 flex-1 min-w-0">
                                                <h4 class="text-sm font-semibold text-gray-900 mb-1" x-text="item.name"></h4>
                                                <p class="text-xs text-gray-500 mb-1" x-text="item.category ? item.category.name : '{{ __('vendor.no_category') }}'"></p>
                                                <p class="text-sm font-bold text-blue-600">₹<span x-text="parseFloat(item.price).toFixed(2)"></span></p>
                                                <p class="text-xs text-gray-500 mt-1" x-show="item.manage_stock">
                                                    {{ __('vendor.stock') }}: <span x-text="item.stock"></span>
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Quantity Input (only show when selected) -->
                                        <div class="mt-3" x-show="selectedItems[item.id] !== undefined">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('vendor.quantity') }}</label>
                                            <input type="number" 
                                                   x-model="selectedItems[item.id]"
                                                   @input="updateQuantity(item.id, $event.target.value)"
                                                   min="1"
                                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                   placeholder="{{ __('vendor.enter_quantity') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="flex-shrink-0 px-4 md:px-6 py-3 bg-red-50 border-t border-red-100">
                        <p class="text-sm text-red-800">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $errors->first() }}
                        </p>
                    </div>
                @endif

                <!-- Hidden inputs for selected items -->
                <template x-for="([itemId, quantity], index) in Object.entries(selectedItems)" :key="itemId">
                    <div>
                        <input type="hidden" :name="'items[' + index + '][item_id]'" :value="itemId">
                        <input type="hidden" :name="'items[' + index + '][quantity]'" :value="quantity">
                    </div>
                </template>

                <!-- Modal Footer -->
                <div class="flex-shrink-0 flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3 px-4 md:px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <button type="button" 
                            @click="showAddItem = false; selectedItems = {}; searchQuery = ''; selectedCategory = ''"
                            :disabled="isSubmitting"
                            class="w-full sm:w-auto px-6 py-3 text-sm font-semibold text-gray-700 bg-white hover:bg-gray-100 border border-gray-300 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-times mr-2"></i>
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit" 
                            :disabled="!hasSelectedItems || isSubmitting"
                            class="w-full sm:flex-1 px-6 py-3 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600">
                        <span x-show="!isSubmitting">
                            <i class="fas fa-plus mr-2"></i>
                            <span x-text="hasSelectedItems ? '{{ __('vendor.add') }} ' + selectedCount + ' {{ __('vendor.item') }}' + (selectedCount > 1 ? 's' : '') : '{{ __('vendor.add_item') }}'"></span>
                        </span>
                        <span x-show="isSubmitting">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            {{ __('vendor.adding') }}...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
