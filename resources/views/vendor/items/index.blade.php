@extends('vendor.layouts.app')

@section('title', 'Items - RentApp')
@section('page-title', 'Items')

@section('content')
<!-- Header with Add Button -->
<div class="mb-6 flex items-start justify-between gap-3">
    <div class="flex-1">
        <div class="flex items-center space-x-3 mb-2">
            <div class="w-12 h-12 flex items-center justify-center bg-blue-100 rounded-xl">
                <i class="fas fa-box text-blue-600 text-xl"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Items</h2>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-layer-group text-blue-600 mr-1"></i>
                    <span class="font-medium">{{ $items->total() }}</span> items
                </p>
            </div>
        </div>
    </div>
    <a href="{{ route('vendor.items.create') }}" 
       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold rounded-lg transition-all shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
        <i class="fas fa-plus mr-2"></i>
        Add<span class="hidden sm:inline ml-1">Item</span>
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

<!-- Items List -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    @if($items->count() > 0)
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-blue-50 to-blue-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Item
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Price
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Stock
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($items as $item)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <!-- Item Info -->
                        <td class="px-6 py-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $item->name }}</p>
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                <i class="fas fa-tag mr-1.5"></i>
                                    {{ $item->category->name ?? 'N/A' }}
                                </span>
                            </div>
                        </td>

                        <!-- Price -->
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-gray-900">₹{{ number_format($item->price, 2) }}</p>
                            <p class="text-xs text-gray-500">
                                @if($item->price_type == 'per_day') Per Day
                                @elseif($item->price_type == 'per_hour') Per Hour
                                @else Fixed
                                @endif
                            </p>
                        </td>

                        <!-- Stock -->
                        <td class="px-6 py-4">
                            <div>
                                @if($item->manage_stock)
                                    <span class="text-sm {{ $item->stock > 0 ? 'text-gray-900' : 'text-red-600 font-semibold' }}">
                                        {{ $item->stock }} units
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">Not tracked</span>
                                @endif
                                <div class="mt-1">
                                    @if($item->is_available)
                                        <span class="text-xs text-green-600">● Available for rent</span>
                                    @else
                                        <span class="text-xs text-orange-600">● Not Available</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4">
                            <div class="inline-block" x-data="{ isActive: {{ $item->is_active ? 'true' : 'false' }} }">
                                <form action="{{ route('vendor.items.toggle', $item->id) }}" method="POST" @submit.prevent="$el.submit(); isActive = !isActive">
                                    @csrf
                                    <button type="submit"
                                            class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" 
                                            :class="isActive ? 'bg-emerald-500' : 'bg-gray-300'"
                                            :title="isActive ? 'Click to deactivate' : 'Click to activate'">
                                        <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform" 
                                              :class="isActive ? 'translate-x-6' : 'translate-x-1'"></span>
                                    </button>
                                </form>
                                <div class="mt-1">
                                    <span class="text-xs font-medium" :class="isActive ? 'text-emerald-700' : 'text-gray-500'" x-text="isActive ? 'Active' : 'Inactive'"></span>
                                </div>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-right">
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click.stop="open = !open" 
                                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                        type="button">
                                    <i class="fas fa-ellipsis-vertical text-gray-600"></i>
                                </button>
                                
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                    <a href="{{ route('vendor.items.edit', $item->id) }}" 
                                       class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <i class="fas fa-edit w-5 text-blue-500"></i>
                                        <span class="ml-3">Edit</span>
                                    </a>
                                    <form action="{{ route('vendor.items.destroy', $item->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this item?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-full flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <i class="fas fa-trash w-5"></i>
                                            <span class="ml-3">Delete</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden divide-y divide-gray-200">
            @foreach($items as $item)
            <div class="p-4">
                <!-- Item Card -->
                <div class="space-y-3">
                    <!-- Header -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <div class="w-12 h-12 flex items-center justify-center bg-blue-100 rounded-xl flex-shrink-0">
                                <i class="fas fa-box text-blue-600 text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-semibold text-gray-900 truncate">
                                    {{ $item->name }}
                                </h3>
                                @if($item->manage_stock)
                                    <p class="text-xs text-gray-500 mt-0.5 flex items-center">
                                        <i class="fas fa-box-open text-xs mr-1"></i>
                                        <span class="{{ $item->stock > 0 ? '' : 'text-red-600 font-semibold' }}">
                                            {{ $item->stock }} in stock
                                        </span>
                                    </p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- 3-Dot Menu -->
                        <div class="relative ml-2 flex-shrink-0" x-data="{ menuOpen: false }" @click.away="menuOpen = false">
                            <button @click.stop="menuOpen = !menuOpen" 
                                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors active:bg-gray-200"
                                    type="button">
                                <i class="fas fa-ellipsis-vertical text-gray-600 text-lg"></i>
                            </button>
                            
                            <div x-show="menuOpen" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-50">
                                <a href="{{ route('vendor.items.edit', $item->id) }}" 
                                   class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                                    <i class="fas fa-edit w-5 text-blue-500"></i>
                                    <span class="ml-3 font-medium">Edit Item</span>
                                </a>
                                <form action="{{ route('vendor.items.destroy', $item->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 active:bg-red-100 transition-colors">
                                        <i class="fas fa-trash w-5"></i>
                                        <span class="ml-3 font-medium">Delete Item</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Price & Details -->
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                        <div>
                            <p class="text-lg font-bold text-gray-900">₹{{ number_format($item->price, 2) }}</p>
                            <p class="text-xs text-gray-500">
                                @if($item->price_type == 'per_day') Per Day
                                @elseif($item->price_type == 'per_hour') Per Hour
                                @else Fixed Price
                                @endif
                            </p>
                        </div>
                        @if($item->is_available)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-full">
                                <i class="fas fa-check-circle text-xs mr-1"></i>
                                Available for rent
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-orange-100 text-orange-700 rounded-full">
                                <i class="fas fa-times-circle text-xs mr-1"></i>
                                Not Available
                            </span>
                        @endif
                    </div>

                    <!-- Status Toggle -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Status</span>
                        <div class="inline-block" x-data="{ isActive: {{ $item->is_active ? 'true' : 'false' }} }">
                            <form action="{{ route('vendor.items.toggle', $item->id) }}" method="POST" @submit.prevent="$el.submit(); isActive = !isActive">
                                @csrf
                                <button type="submit"
                                        class="relative inline-flex items-center h-7 rounded-full w-12 transition-colors focus:outline-none active:ring-2 active:ring-offset-2 active:ring-emerald-500" 
                                        :class="isActive ? 'bg-emerald-500' : 'bg-gray-300'"
                                        :title="isActive ? 'Tap to deactivate' : 'Tap to activate'">
                                    <span class="inline-block w-5 h-5 transform bg-white rounded-full transition-transform shadow-md" 
                                          :class="isActive ? 'translate-x-6' : 'translate-x-1'"></span>
                                </button>
                            </form>
                            <span class="ml-2 text-xs font-semibold" :class="isActive ? 'text-emerald-700' : 'text-gray-600'" x-text="isActive ? 'Active' : 'Inactive'"></span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $items->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <i class="fas fa-box text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Items Yet</h3>
            <p class="text-sm text-gray-500 mb-6">Start adding items to your rental inventory</p>
            <a href="{{ route('vendor.items.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Create Your First Item
            </a>
        </div>
    @endif
</div>
@endsection
