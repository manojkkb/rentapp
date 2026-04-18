@extends('vendor.layouts.app')

@section('title', __('vendor.items_management'))
@section('page-title', __('vendor.items'))

@section('content')
<!-- Header with Add Button -->
<div class="mb-6 flex items-start justify-between gap-3">
    <div class="flex-1">
        <div class="flex items-center space-x-3 mb-2">
           
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('vendor.items') }}</h2>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-layer-group text-emerald-600 mr-1"></i>
                    <span class="font-medium" id="items-total-count">{{ __('vendor.total_items_count', ['count' => $items->total()]) }}</span>
                </p>
            </div>
        </div>
    </div>
    <button type="button" 
            @click="$dispatch('open-create-item-modal')"
            class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold rounded-lg transition-all shadow-sm hover:shadow active:scale-95 whitespace-nowrap">
        <i class="fas fa-plus mr-2"></i>
        {{ __('vendor.add_item') }}
    </button>
</div>

<!-- Filters Section -->
<div class="mb-6 bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div class="md:col-span-2">
            <label for="search" class="block text-xs font-medium text-gray-700 mb-2">
                <i class="fas fa-search mr-1"></i>{{ __('vendor.search') }}
            </label>
            <input type="text" 
                   id="search" 
                   placeholder="Search items by name, description..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
        </div>
        
        <!-- Category Filter -->
        <div>
            <label for="category_filter" class="block text-xs font-medium text-gray-700 mb-2">
                <i class="fas fa-tag mr-1"></i>{{ __('vendor.category') }}
            </label>
            <select id="category_filter" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    
    <!-- Filter Actions -->
    <div class="mt-4 flex items-center justify-between">
        <button type="button" 
                id="clear-filters" 
                class="text-sm text-gray-600 hover:text-gray-900 font-medium">
            <i class="fas fa-times-circle mr-1"></i>Clear Filters
        </button>
        <div class="text-xs text-gray-500">
            <i class="fas fa-info-circle mr-1"></i>Filters apply automatically
        </div>
    </div>
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
<div id="loading-indicator" class="hidden">
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
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-32"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                            <th class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg shimmer"></div>
                                    <div>
                                        <div class="h-4 bg-gray-200 rounded shimmer w-40 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></td>
                            <td class="px-6 py-4"><div class="h-6 w-16 bg-gray-200 rounded-full shimmer"></div></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg shimmer"></div>
                                    <div>
                                        <div class="h-4 bg-gray-200 rounded shimmer w-40 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></td>
                            <td class="px-6 py-4"><div class="h-6 w-16 bg-gray-200 rounded-full shimmer"></div></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg shimmer"></div>
                                    <div>
                                        <div class="h-4 bg-gray-200 rounded shimmer w-40 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></td>
                            <td class="px-6 py-4"><div class="h-6 w-16 bg-gray-200 rounded-full shimmer"></div></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
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
            <div class="p-4">
                <div class="flex items-start space-x-4 mb-4">
                    <div class="w-24 h-24 bg-gray-200 rounded-lg shimmer flex-shrink-0"></div>
                    <div class="flex-1">
                        <div class="h-5 bg-gray-200 rounded shimmer w-full mb-2"></div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-3/4 mb-3"></div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="h-4 bg-gray-200 rounded shimmer w-16"></div>
                            <div class="h-6 w-12 bg-gray-200 rounded-full shimmer"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                    <div class="flex gap-2">
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <div class="flex items-start space-x-4 mb-4">
                    <div class="w-24 h-24 bg-gray-200 rounded-lg shimmer flex-shrink-0"></div>
                    <div class="flex-1">
                        <div class="h-5 bg-gray-200 rounded shimmer w-full mb-2"></div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-3/4 mb-3"></div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="h-4 bg-gray-200 rounded shimmer w-16"></div>
                            <div class="h-6 w-12 bg-gray-200 rounded-full shimmer"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                    <div class="flex gap-2">
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <div class="flex items-start space-x-4 mb-4">
                    <div class="w-24 h-24 bg-gray-200 rounded-lg shimmer flex-shrink-0"></div>
                    <div class="flex-1">
                        <div class="h-5 bg-gray-200 rounded shimmer w-full mb-2"></div>
                        <div class="h-3 bg-gray-200 rounded shimmer w-3/4 mb-3"></div>
                        <div class="flex items-center gap-2 mb-2">
                            <div class="h-4 bg-gray-200 rounded shimmer w-16"></div>
                            <div class="h-6 w-12 bg-gray-200 rounded-full shimmer"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                    <div class="h-3 bg-gray-200 rounded shimmer w-24"></div>
                    <div class="flex gap-2">
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                        <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Items List -->
<div id="items-container" class="bg-white rounded-xl shadow-sm border border-gray-200">
    <!-- Items Content -->
    <div id="items-content">
    @if($items->count() > 0)
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-emerald-50 to-emerald-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            {{ __('vendor.item') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            {{ __('vendor.price') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            {{ __('vendor.stock') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            {{ __('vendor.status') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            {{ __('vendor.actions') }}
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
                                {{ $priceTypes[$item->price_type] ?? $item->price_type }}
                            </p>
                        </td>

                        <!-- Stock -->
                        <td class="px-6 py-4">
                            <div>
                                @if($item->manage_stock)
                                    <span class="text-sm {{ $item->stock > 0 ? 'text-gray-900' : 'text-red-600 font-semibold' }}">
                                        {{ $item->stock }} {{ __('vendor.available_units') }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">{{ __('vendor.not_available') }}</span>
                                @endif
                                <div class="mt-1">
                                    @if($item->is_available)
                                        <span class="text-xs text-green-600">● {{ __('vendor.available_for_rent') }}</span>
                                    @else
                                        <span class="text-xs text-orange-600">● {{ __('vendor.not_available') }}</span>
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
                                            class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500" 
                                            :class="isActive ? 'bg-emerald-500' : 'bg-gray-300'"
                                            :title="isActive ? 'Click to deactivate' : 'Click to activate'">
                                        <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform" 
                                              :class="isActive ? 'translate-x-6' : 'translate-x-1'"></span>
                                    </button>
                                </form>
                                <div class="mt-1">
                                    <span class="text-xs font-medium" :class="isActive ? 'text-emerald-700' : 'text-gray-500'" x-text="isActive ? '{{ __('vendor.active') }}' : '{{ __('vendor.inactive') }}'"></span>
                                </div>
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-right">
                            <div class="relative inline-block" x-data="{ dropdownOpen: false }">
                                <button @click="dropdownOpen = !dropdownOpen" 
                                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                                        type="button"
                                        x-ref="dropdownButton">
                                    <i class="fas fa-ellipsis-vertical text-gray-600"></i>
                                </button>
                                
                                <div x-show="dropdownOpen" 
                                     @click.away="dropdownOpen = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="fixed w-48 bg-white rounded-lg shadow-2xl border border-gray-200 py-1"
                                     style="display: none; z-index: 9999;"
                                     x-init="$watch('dropdownOpen', value => {
                                         if(value) {
                                             let rect = $refs.dropdownButton.getBoundingClientRect();
                                             $el.style.top = rect.bottom + 5 + 'px';
                                             $el.style.left = (rect.right - 192) + 'px';
                                         }
                                     })">
                                    <a href="{{ route('vendor.items.edit', $item->id) }}" 
                                       class="block text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <i class="fas fa-edit w-5 text-emerald-500 mr-3"></i>
                                        {{ __('vendor.edit') }}
                                    </a>
                                    <form action="{{ route('vendor.items.destroy', $item->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('{{ __('vendor.confirm_delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-full text-left block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <i class="fas fa-trash w-5 mr-3"></i>
                                            {{ __('vendor.delete') }}
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
                            <div class="w-12 h-12 flex items-center justify-center bg-emerald-100 rounded-xl flex-shrink-0">
                                <i class="fas fa-box text-emerald-600 text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-semibold text-gray-900 truncate">
                                    {{ $item->name }}
                                </h3>
                                @if($item->manage_stock)
                                    <p class="text-xs text-gray-500 mt-0.5 flex items-center">
                                        <i class="fas fa-box-open text-xs mr-1"></i>
                                        <span class="{{ $item->stock > 0 ? '' : 'text-red-600 font-semibold' }}">
                                            {{ $item->stock }} {{ __('vendor.stock') }}
                                        </span>
                                    </p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- 3-Dot Menu -->
                        <div class="relative ml-2 flex-shrink-0" x-data="{ mobileDropdownOpen: false }">
                            <button @click="mobileDropdownOpen = !mobileDropdownOpen" 
                                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors active:bg-gray-200"
                                    type="button">
                                <i class="fas fa-ellipsis-vertical text-gray-600 text-lg"></i>
                            </button>
                            
                            <div x-show="mobileDropdownOpen" 
                                 @click.away="mobileDropdownOpen = false"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-50"
                                 style="display: none;">
                                <a href="{{ route('vendor.items.edit', $item->id) }}" 
                                   class="block text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                                    <i class="fas fa-edit w-5 text-emerald-500 mr-3"></i>
                                    {{ __('vendor.edit_item') }}
                                </a>
                                <form action="{{ route('vendor.items.destroy', $item->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('{{ __('vendor.confirm_delete') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full text-left block px-4 py-3 text-sm text-red-600 hover:bg-red-50 active:bg-red-100 transition-colors">
                                        <i class="fas fa-trash w-5 mr-3"></i>
                                        {{ __('vendor.delete') }}
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
                                {{ $priceTypes[$item->price_type] ?? $item->price_type }}
                            </p>
                        </div>
                        @if($item->is_available)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-full">
                                <i class="fas fa-check-circle text-xs mr-1"></i>
                                {{ __('vendor.available_for_rent') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-orange-100 text-orange-700 rounded-full">
                                <i class="fas fa-times-circle text-xs mr-1"></i>
                                {{ __('vendor.not_available') }}
                            </span>
                        @endif
                    </div>

                    <!-- Status Toggle -->
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">{{ __('vendor.status') }}</span>
                        <div class="inline-block" x-data="{ isActive: {{ $item->is_active ? 'true' : 'false' }} }">
                            <form action="{{ route('vendor.items.toggle', $item->id) }}" method="POST" @submit.prevent="$el.submit(); isActive = !isActive">
                                @csrf
                                <button type="submit"
                                        class="relative inline-flex items-center h-7 rounded-full w-12 transition-colors focus:outline-none active:ring-2 active:ring-offset-2 active:ring-emerald-500" 
                                        :class="isActive ? 'bg-emerald-500' : 'bg-gray-300'"
                                        :title="isActive ? '{{ __('vendor.inactive') }}' : '{{ __('vendor.active') }}'">
                                    <span class="inline-block w-5 h-5 transform bg-white rounded-full transition-transform shadow-md" 
                                          :class="isActive ? 'translate-x-6' : 'translate-x-1'"></span>
                                </button>
                            </form>
                            <span class="ml-2 text-xs font-semibold" :class="isActive ? 'text-emerald-700' : 'text-gray-600'" x-text="isActive ? '{{ __('vendor.active') }}' : '{{ __('vendor.inactive') }}'"></span>
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
            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('vendor.no_items_yet') }}</h3>
            <p class="text-sm text-gray-500 mb-6">{{ __('vendor.add_items_see_popular') }}</p>
            <button type="button" 
                    @click="$dispatch('open-create-item-modal')"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>
                {{ __('vendor.add_first_item') }}
            </button>
        </div>
    @endif
    </div>
</div>

<!-- Create Item Modal -->
<div x-data="{ showCreateModal: false }" 
     @open-create-item-modal.window="showCreateModal = true"
     @close-create-modal.window="showCreateModal = false"
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
            
            <form id="createItemForm" method="POST" action="{{ route('vendor.items.store') }}">
                @csrf
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-emerald-50 to-green-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.add_new_item_title') }}</h3>
                        <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                    <!-- Item Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('vendor.item_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Category -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('vendor.category') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                            <option value="">{{ __('vendor.select_category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('vendor.description') }}</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <!-- Pricing -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('vendor.price') }} (₹) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="price" step="0.01" min="0" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('vendor.price_type') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="price_type" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                @foreach($priceTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Stock -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('vendor.stock_quantity') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="stock" min="0" value="1" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="manage_stock" value="1" checked
                                       class="w-4 h-4 text-emerald-600 border-gray-300 rounded">
                                <span class="ml-2 text-sm font-medium text-gray-700">Track stock</span>
                            </label>
                        </div>
                    </div>

                    <!-- Status Checkboxes -->
                    <div class="space-y-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_available" value="1" checked
                                   class="w-4 h-4 text-emerald-600 border-gray-300 rounded">
                            <span class="ml-2 text-sm font-medium text-gray-700">{{ __('vendor.available_for_rent') }}</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="w-4 h-4 text-emerald-600 border-gray-300 rounded">
                            <span class="ml-2 text-sm font-medium text-gray-700">{{ __('vendor.active') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 border-t border-gray-200">
                    <button type="button" @click="showCreateModal = false"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                        <i class="fas fa-plus mr-2"></i>{{ __('vendor.add_item') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div x-data="{ showEditModal: false, editItem: {} }" 
     @open-edit-item-modal.window="showEditModal = true; editItem = $event.detail"
     @close-edit-modal.window="showEditModal = false"
     x-show="showEditModal" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     @click.self="showEditModal = false">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
        <!-- Background overlay -->
        <div x-show="showEditModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-black/50" 
             @click="showEditModal = false"></div>

        <!-- Modal panel -->
        <div x-show="showEditModal" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full z-10"
             @click.stop>
            
            <form id="editItemForm" :action="`{{ url('vendor/items') }}/${editItem.id}`" method="POST">
                @csrf
                @method('PUT')
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-emerald-50 to-green-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.edit_item') }}</h3>
                        <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                    <!-- Item Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('vendor.item_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" :value="editItem.name" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <!-- Category -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('vendor.category') }} <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                            <option value="">{{ __('vendor.select_category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" :selected="editItem.category_id == {{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('vendor.description') }}</label>
                        <textarea name="description" rows="3" x-text="editItem.description"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <!-- Pricing -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('vendor.price') }} (₹) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="price" step="0.01" min="0" :value="editItem.price" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('vendor.price_type') }} <span class="text-red-500">*</span>
                            </label>
                            <select name="price_type" required
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                                @foreach($priceTypes as $key => $label)
                                    <option value="{{ $key }}" :selected="editItem.price_type == '{{ $key }}'">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Stock -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('vendor.stock_quantity') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="stock" min="0" :value="editItem.stock" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="manage_stock" value="1" :checked="editItem.manage_stock"
                                       class="w-4 h-4 text-emerald-600 border-gray-300 rounded">
                                <span class="ml-2 text-sm font-medium text-gray-700">Track stock</span>
                            </label>
                        </div>
                    </div>

                    <!-- Status Checkboxes -->
                    <div class="space-y-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_available" value="1" :checked="editItem.is_available"
                                   class="w-4 h-4 text-emerald-600 border-gray-300 rounded">
                            <span class="ml-2 text-sm font-medium text-gray-700">{{ __('vendor.available_for_rent') }}</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" :checked="editItem.is_active"
                                   class="w-4 h-4 text-emerald-600 border-gray-300 rounded">
                            <span class="ml-2 text-sm font-medium text-gray-700">{{ __('vendor.active') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 border-t border-gray-200">
                    <button type="button" @click="showEditModal = false"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                        <i class="fas fa-save mr-2"></i>{{ __('vendor.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const itemsTotalCountTemplate = @json(__('vendor.total_items_count', ['count' => '__COUNT__']));
document.addEventListener('DOMContentLoaded', function() {
    let debounceTimer;
    const searchInput = document.getElementById('search');
    const categoryFilter = document.getElementById('category_filter');
    const clearFiltersBtn = document.getElementById('clear-filters');
    const itemsContainer = document.getElementById('items-container');
    const loadingIndicator = document.getElementById('loading-indicator');
    const itemsTotalCount = document.getElementById('items-total-count');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const itemPriceTypeLabels = @json($priceTypes);

    console.log('Filter script loaded');
    
    // Fetch items function (global scope for access from form handlers)
    window.fetchItems = fetchItems;
    function fetchItems(page = 1) {
        console.log('Fetching items...', {
            search: searchInput.value,
            category: categoryFilter.value
        });
        
        const params = new URLSearchParams({
            search: searchInput.value || '',
            category_id: categoryFilter.value || '',
            page: page
        });
        
        // Show loading indicator
        if (loadingIndicator && itemsContainer) {
            loadingIndicator.classList.remove('hidden');
            itemsContainer.classList.add('hidden');
        }
        
        fetch('{{ route("vendor.items.fetch") }}?' + params.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(response => {
            console.log('Response received:', response.status);
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.success) {
                renderItems(data.items);
                if (itemsTotalCount) {
                    itemsTotalCount.textContent = itemsTotalCountTemplate.replace('__COUNT__', String(data.pagination.total));
                }
            } else {
                console.error('API returned success: false', data);
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error fetching items:', error);
            alert('Error loading items. Please check console for details.');
        })
        .finally(() => {
            if (loadingIndicator && itemsContainer) {
                loadingIndicator.classList.add('hidden');
                itemsContainer.classList.remove('hidden');
            }
        });
    }
    
    // Render items function
    function renderItems(items) {
        console.log('Rendering items:', items.length);
        
        const itemsContent = document.getElementById('items-content');
        
        if (!items || items.length === 0) {
            itemsContent.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-box text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No items found</h3>
                    <p class="text-sm text-gray-500 mb-6">Try adjusting your filters or add a new item</p>
                    <a href="{{ route('vendor.items.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        {{ __('vendor.add_item') }}
                    </a>
                </div>
            `;
            return;
        }
        
        // Desktop Table
        let desktopTableHtml = `
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-emerald-50 to-emerald-100 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('vendor.item') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('vendor.price') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('vendor.stock') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('vendor.status') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">{{ __('vendor.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
        `;
        
        items.forEach(item => {
            const priceType = itemPriceTypeLabels[item.price_type] || item.price_type;
            const categoryName = item.category ? item.category.name : 'N/A';
            const itemPrice = parseFloat(item.price).toFixed(2);
            
            desktopTableHtml += `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">${item.name}</p>
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full mt-1">
                                <i class="fas fa-tag mr-1.5"></i>${categoryName}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900">₹${itemPrice}</p>
                        <p class="text-xs text-gray-500">${priceType}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${
                            item.stock > 10 ? 'bg-emerald-100 text-emerald-700' : 
                            (item.stock > 0 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700')
                        }">
                            <i class="fas fa-cubes text-xs mr-1"></i>${item.stock}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        ${item.is_available ? 
                            '<span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-full"><i class="fas fa-check-circle mr-1"></i>{{ __("vendor.available_for_rent") }}</span>' :
                            '<span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-full"><i class="fas fa-times-circle mr-1"></i>{{ __("vendor.not_available") }}</span>'
                        }
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            <form action="{{ url('vendor/items') }}/${item.id}/toggle" method="POST" class="inline">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <button type="submit" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none ${item.is_active ? 'bg-emerald-500' : 'bg-gray-300'}">
                                    <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform shadow-md ${item.is_active ? 'translate-x-6' : 'translate-x-1'}"></span>
                                </button>
                            </form>
                            <button type="button" onclick="openEditModal(${item.id})" class="text-emerald-600 hover:text-emerald-900 text-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ url('vendor/items') }}/${item.id}" method="POST" class="inline" onsubmit="return confirm('{{ __('vendor.confirm_delete') }}');">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        desktopTableHtml += `
                    </tbody>
                </table>
            </div>
        `;
        
        // Mobile Cards
        let mobileCardsHtml = `
            <div class="md:hidden divide-y divide-gray-200">
        `;
        
        items.forEach(item => {
            const priceType = itemPriceTypeLabels[item.price_type] || item.price_type;
            const categoryName = item.category ? item.category.name : 'N/A';
            const itemPrice = parseFloat(item.price).toFixed(2);
            
            mobileCardsHtml += `
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-gray-900 mb-1">${item.name}</h3>
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                <i class="fas fa-tag mr-1"></i>${categoryName}
                            </span>
                        </div>
                        <button type="button" onclick="openEditModal(${item.id})" class="text-emerald-600 hover:text-emerald-900 ml-2">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                        <div>
                            <p class="text-lg font-bold text-gray-900">₹${itemPrice}</p>
                            <p class="text-xs text-gray-500">${priceType}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold ${
                            item.stock > 10 ? 'bg-emerald-100 text-emerald-700' : 'bg-orange-100 text-orange-700'
                        } rounded-full">
                            <i class="fas fa-cubes mr-1"></i>${item.stock}
                        </span>
                    </div>
                </div>
            `;
        });
        
        mobileCardsHtml += `
            </div>
        `;
        
        itemsContent.innerHTML = desktopTableHtml + mobileCardsHtml;
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            console.log('Category changed:', this.value);
            fetchItems();
        });
    }
    
    // Search with debounce
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            console.log('Search input:', this.value);
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchItems();
            }, 500);
        });
    }
    
    // Clear filters
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            console.log('Clearing filters');
            searchInput.value = '';
            categoryFilter.value = '';
            fetchItems();
        });
    }
    
    // Fetch items on load
    fetchItems();
});

// Function to open edit modal with item data
function openEditModal(itemId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`{{ url('vendor/items') }}/${itemId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.dispatchEvent(new CustomEvent('open-edit-item-modal', { 
                detail: data.item 
            }));
        } else {
            alert('Error loading item data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading item data');
    });
}

// Handle create item form submission
document.addEventListener('DOMContentLoaded', function() {
    const createForm = document.getElementById('createItemForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Handle checkboxes - ensure unchecked boxes send '0'
            ['manage_stock', 'is_available', 'is_active'].forEach(field => {
                if (!formData.has(field)) {
                    formData.set(field, '0');
                } else {
                    formData.set(field, '1');
                }
            });
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Item created successfully!', 'success');
                    createForm.reset();
                    window.dispatchEvent(new CustomEvent('close-create-modal'));
                    fetchItems(); // Refresh items list
                } else {
                    showToast(data.message || 'Error creating item', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error creating item', 'error');
            });
        });
    }
    
    const editForm = document.getElementById('editItemForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Handle checkboxes - ensure unchecked boxes send '0'
            ['manage_stock', 'is_available', 'is_active'].forEach(field => {
                if (!formData.has(field)) {
                    formData.set(field, '0');
                } else {
                    formData.set(field, '1');
                }
            });
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Item updated successfully!', 'success');
                    window.dispatchEvent(new CustomEvent('close-edit-modal'));
                    fetchItems(); // Refresh items list
                } else {
                    showToast(data.message || 'Error updating item', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating item', 'error');
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
</script>
@endsection
