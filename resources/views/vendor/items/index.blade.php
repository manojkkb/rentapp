@extends('vendor.layouts.app')

@section('title', __('vendor.items_management'))
@section('page-title', __('vendor.items'))

@section('content')
@php
    $inp = 'w-full min-h-[44px] rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm outline-none ring-emerald-500/15 transition placeholder:text-gray-400 focus:border-emerald-500 focus:ring-2 sm:min-h-[40px]';
    $lbl = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-500';
@endphp

<div class="mx-auto max-w-6xl space-y-3 pb-[max(1rem,env(safe-area-inset-bottom))] sm:space-y-4">
    {{-- Page header --}}
    <div class="space-y-3 rounded-lg border border-gray-200 bg-white p-3 shadow-sm sm:p-3.5">
        <div class="grid w-full min-w-0 grid-cols-[minmax(0,1fr)_auto] items-center gap-x-2 gap-y-1 sm:gap-x-4">
            <h1 class="col-start-1 row-start-1 min-w-0 text-lg font-bold tracking-tight text-gray-900 sm:text-xl">{{ __('vendor.items') }}</h1>
            <a href="{{ route('vendor.items.create') }}"
               class="col-start-2 row-span-2 row-start-1 inline-flex min-h-[44px] shrink-0 items-center justify-center gap-1.5 self-center rounded-lg bg-emerald-600 px-2.5 py-2 text-xs font-semibold text-white shadow-sm transition [touch-action:manipulation] hover:bg-emerald-700 active:scale-[0.98] sm:min-h-[40px] sm:gap-2 sm:px-4 sm:text-sm">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                <span class="whitespace-nowrap">{{ __('vendor.add_item') }}</span>
            </a>
            <div class="col-start-1 row-start-2 min-w-0 space-y-0.5">
                <p class="text-xs leading-snug text-gray-600 sm:text-sm">{{ __('vendor.items_page_subtitle') }}</p>
                <p class="text-[11px] font-medium text-gray-700">
                    <i class="fas fa-layer-group mr-1 text-emerald-600" aria-hidden="true"></i>
                    <span id="items-total-count">{{ __('vendor.total_items_count', ['count' => $items->total()]) }}</span>
                </p>
            </div>
        </div>
    </div>

    {{-- Search & filter --}}
    <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm sm:p-3.5">
        <p class="mb-2 text-[11px] font-bold uppercase tracking-wide text-emerald-900/90">{{ __('vendor.items_filter_section') }}</p>
        <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:gap-4">
            <div class="md:col-span-8">
                <label for="search" class="{{ $lbl }}">{{ __('vendor.search') }}</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <i class="fas fa-search text-xs sm:text-sm" aria-hidden="true"></i>
                    </span>
                    <input type="search"
                           id="search"
                           placeholder="{{ __('vendor.items_search_placeholder') }}"
                           autocomplete="off"
                           inputmode="search"
                           enterkeyhint="search"
                           class="{{ $inp }} pl-10">
                </div>
            </div>
            <div class="md:col-span-4">
                <label for="category_filter" class="{{ $lbl }}">{{ __('vendor.category') }}</label>
                <select id="category_filter" class="{{ $inp }}">
                    <option value="">{{ __('vendor.all_categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3">
            <button type="button"
                    id="clear-filters"
                    class="text-xs font-medium text-gray-600 transition hover:text-gray-900">
                <i class="fas fa-times-circle mr-1 text-emerald-600/80" aria-hidden="true"></i>{{ __('vendor.clear_filters') }}
            </button>
            <p class="text-[10px] leading-snug text-gray-500">
                <i class="fas fa-info-circle mr-1 text-gray-400" aria-hidden="true"></i>{{ __('vendor.items_filters_hint') }}
            </p>
        </div>
    </div>

@if (session('success'))
    <div class="flex items-start gap-3 rounded-lg border border-emerald-200/80 bg-emerald-50/90 p-3 text-emerald-950 shadow-sm sm:p-4" role="status">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
            <i class="fas fa-check text-sm" aria-hidden="true"></i>
        </span>
        <p class="min-w-0 flex-1 text-sm font-medium leading-snug">{{ session('success') }}</p>
        <button type="button" onclick="this.closest('[role=status]').remove()" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-emerald-700/80 transition hover:bg-emerald-100 hover:text-emerald-900">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50/90 p-3 text-red-900 shadow-sm sm:p-4" role="alert">
        <div class="flex items-start gap-2">
            <i class="fas fa-exclamation-circle mt-0.5 text-red-500" aria-hidden="true"></i>
            <div class="min-w-0 flex-1 text-sm">
                @foreach ($errors->all() as $error)
                    <p class="leading-snug">{{ $error }}</p>
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
    <div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
        <!-- Desktop Table Shimmer -->
        <div class="hidden md:block">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-32"></div></th>
                            <th class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                            <th class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                            <th class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                            <th class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></th>
                            <th class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-24"></div></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg shimmer"></div>
                                    <div>
                                        <div class="h-4 bg-gray-200 rounded shimmer w-40 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></td>
                            <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></td>
                            <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></td>
                            <td class="px-4 py-3"><div class="h-6 w-16 bg-gray-200 rounded-full shimmer"></div></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg shimmer"></div>
                                    <div>
                                        <div class="h-4 bg-gray-200 rounded shimmer w-40 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></td>
                            <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></td>
                            <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></td>
                            <td class="px-4 py-3"><div class="h-6 w-16 bg-gray-200 rounded-full shimmer"></div></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded shimmer"></div>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gray-200 rounded-lg shimmer"></div>
                                    <div>
                                        <div class="h-4 bg-gray-200 rounded shimmer w-40 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></td>
                            <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-16"></div></td>
                            <td class="px-4 py-3"><div class="h-4 bg-gray-200 rounded shimmer w-20"></div></td>
                            <td class="px-4 py-3"><div class="h-6 w-16 bg-gray-200 rounded-full shimmer"></div></td>
                            <td class="px-4 py-3">
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
        <div class="md:hidden space-y-2 p-2">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
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
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
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
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
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

<!-- Items list -->
<div id="items-container" class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-gray-200 bg-gray-50/80 px-3 py-2 sm:px-4">
        <p class="text-[11px] font-bold uppercase tracking-wide text-emerald-900/90">{{ __('vendor.items_list_section') }}</p>
    </div>
    <div id="items-content">
    @if($items->count() > 0)
        <!-- Desktop Table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="border-b border-gray-200 bg-gray-50">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            {{ __('vendor.item') }}
                        </th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            {{ __('vendor.price') }}
                        </th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            {{ __('vendor.stock') }}
                        </th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            {{ __('vendor.status') }}
                        </th>
                        <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                            {{ __('vendor.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($items as $item)
                    <tr class="transition-colors hover:bg-emerald-50/30">
                        <!-- Item Info -->
                        <td class="px-4 py-3">
                            <div class="flex items-start gap-3">
                                @if($item->photo_url)
                                    <img src="{{ $item->photo_url }}" alt="" class="w-10 h-10 rounded-lg object-cover border border-gray-200 flex-shrink-0" loading="lazy">
                                @else
                                    <div class="w-10 h-10 flex items-center justify-center bg-emerald-100 rounded-lg flex-shrink-0">
                                        <i class="fas fa-box text-emerald-600 text-sm"></i>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('vendor.items.show', $item) }}" class="text-sm font-semibold text-gray-900 hover:text-emerald-700">
                                        {{ $item->name }}
                                    </a>
                                    <p class="mt-0.5 font-mono text-[10px] text-gray-500">{{ $item->item_code }}</p>
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full mt-1">
                                    <i class="fas fa-tag mr-1.5"></i>
                                        {{ $item->category->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <!-- Price -->
                        <td class="px-4 py-3">
                            <p class="text-sm font-semibold text-gray-900">₹{{ number_format($item->price, 2) }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $rentalPeriods[$item->rental_period] ?? $item->rental_period }}
                            </p>
                        </td>

                        <!-- Stock -->
                        <td class="px-4 py-3">
                            <div>
                                @if($item->manage_stock)
                                    <span class="text-sm {{ ($item->stock) > 0 ? 'text-gray-900' : 'text-red-600 font-semibold' }}">
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
                        <td class="px-4 py-3">
                            @if($item->is_active)
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    {{ __('vendor.active') }}
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                    {{ __('vendor.inactive') }}
                                </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="px-4 py-3 text-right">
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
                                    <a href="{{ route('vendor.items.show', $item) }}"
                                       class="block text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-b border-gray-100">
                                        <i class="fas fa-eye w-5 text-gray-500 mr-3"></i>
                                        {{ __('vendor.view') }}
                                    </a>
                                    <a href="{{ route('vendor.items.edit', $item) }}" 
                                       class="block text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <i class="fas fa-edit w-5 text-emerald-500 mr-3"></i>
                                        {{ __('vendor.edit') }}
                                    </a>
                                    <form action="{{ route('vendor.items.destroy', $item) }}" 
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
        <div class="md:hidden space-y-2 p-2">
            @foreach($items as $item)
            <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                <!-- Item Card -->
                <div class="space-y-3">
                    <!-- Header -->
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            @if($item->photo_url)
                                <img src="{{ $item->photo_url }}" alt="" class="w-12 h-12 rounded-xl object-cover border border-gray-200 flex-shrink-0" loading="lazy">
                            @else
                                <div class="w-12 h-12 flex items-center justify-center bg-emerald-100 rounded-xl flex-shrink-0">
                                    <i class="fas fa-box text-emerald-600 text-lg"></i>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('vendor.items.show', $item) }}" class="block truncate text-base font-semibold text-gray-900 hover:text-emerald-700">
                                    {{ $item->name }}
                                </a>
                                <p class="mt-0.5 font-mono text-[10px] text-gray-500">{{ $item->item_code }}</p>
                                @if($item->manage_stock)
                                    <p class="text-xs text-gray-500 mt-0.5 flex items-center">
                                        <i class="fas fa-box-open text-xs mr-1"></i>
                                        <span class="{{ ($item->stock) > 0 ? '' : 'text-red-600 font-semibold' }}">
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
                                <a href="{{ route('vendor.items.show', $item) }}"
                                   class="block text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                                    <i class="fas fa-eye w-5 text-gray-500 mr-3"></i>
                                    {{ __('vendor.view') }}
                                </a>
                                <a href="{{ route('vendor.items.edit', $item) }}"
                                   class="block text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                                    <i class="fas fa-edit w-5 text-emerald-500 mr-3"></i>
                                    {{ __('vendor.edit_item') }}
                                </a>
                                <form action="{{ route('vendor.items.destroy', $item) }}" 
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
                                {{ $rentalPeriods[$item->rental_period] ?? $item->rental_period }}
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

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">{{ __('vendor.status') }}</span>
                        @if($item->is_active)
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                {{ __('vendor.active') }}
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                                {{ __('vendor.inactive') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($items->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50/80">
                {{ $items->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="p-6 sm:p-8">
            <div class="mx-auto max-w-md rounded-xl border border-dashed border-gray-200 bg-gray-50/80 px-6 py-10 text-center">
                <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white text-gray-300 shadow-sm ring-1 ring-gray-100">
                    <i class="fas fa-box text-2xl" aria-hidden="true"></i>
                </span>
                <h3 class="text-base font-semibold text-gray-900">{{ __('vendor.no_items_yet') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('vendor.add_items_see_popular') }}</p>
                <a href="{{ route('vendor.items.create') }}"
                   class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                    {{ __('vendor.add_first_item') }}
                </a>
            </div>
        </div>
    @endif
    </div>
</div>
</div>

@endsection

@section('scripts')
<script>
const itemsTotalCountTemplate = @json(__('vendor.total_items_count', ['count' => '__COUNT__']));
const itemsNoResultsTitle = @json(__('vendor.no_items_found'));
const itemsNoResultsHint = @json(__('vendor.adjust_search'));
document.addEventListener('DOMContentLoaded', function() {
    let debounceTimer;
    const searchInput = document.getElementById('search');
    const categoryFilter = document.getElementById('category_filter');
    const clearFiltersBtn = document.getElementById('clear-filters');
    const itemsContainer = document.getElementById('items-container');
    const loadingIndicator = document.getElementById('loading-indicator');
    const itemsTotalCount = document.getElementById('items-total-count');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const itemRentalPeriodLabels = @json($rentalPeriods);
    const itemsBaseUrl = @json(url('vendor/items'));
    const itemsViewLabel = @json(__('vendor.view'));
    const itemsEditLabel = @json(__('vendor.edit'));
    const itemsEditItemLabel = @json(__('vendor.edit_item'));
    const itemsDeleteLabel = @json(__('vendor.delete'));
    const itemsConfirmDelete = @json(__('vendor.confirm_delete'));

    function buildItemActionsMenu(uuid, placement = 'desktop') {
        const viewUrl = `${itemsBaseUrl}/${uuid}`;
        const editUrl = `${itemsBaseUrl}/${uuid}/edit`;
        const deleteUrl = `${itemsBaseUrl}/${uuid}`;

        if (placement === 'mobile') {
            return `
                <div class="relative ml-2 flex-shrink-0" x-data="{ mobileDropdownOpen: false }">
                    <button @click="mobileDropdownOpen = !mobileDropdownOpen"
                            class="p-2 hover:bg-gray-100 rounded-lg transition-colors active:bg-gray-200"
                            type="button">
                        <i class="fas fa-ellipsis-vertical text-gray-600 text-lg"></i>
                    </button>
                    <div x-show="mobileDropdownOpen"
                         @click.away="mobileDropdownOpen = false"
                         x-transition
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden z-50"
                         style="display: none;">
                        <a href="${viewUrl}"
                           class="block text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                            <i class="fas fa-eye w-5 text-gray-500 mr-3"></i>
                            ${itemsViewLabel}
                        </a>
                        <a href="${editUrl}"
                           class="block text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 active:bg-gray-100 transition-colors border-b border-gray-100">
                            <i class="fas fa-edit w-5 text-emerald-500 mr-3"></i>
                            ${itemsEditItemLabel}
                        </a>
                        <form action="${deleteUrl}" method="POST" onsubmit="return confirm(${JSON.stringify(itemsConfirmDelete)});">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit"
                                    class="w-full text-left block px-4 py-3 text-sm text-red-600 hover:bg-red-50 active:bg-red-100 transition-colors">
                                <i class="fas fa-trash w-5 mr-3"></i>
                                ${itemsDeleteLabel}
                            </button>
                        </form>
                    </div>
                </div>
            `;
        }

        return `
            <div class="relative inline-block" x-data="{ dropdownOpen: false }">
                <button @click="dropdownOpen = !dropdownOpen"
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                        type="button"
                        x-ref="dropdownButton">
                    <i class="fas fa-ellipsis-vertical text-gray-600"></i>
                </button>
                <div x-show="dropdownOpen"
                     @click.away="dropdownOpen = false"
                     x-transition
                     class="fixed w-48 bg-white rounded-lg shadow-2xl border border-gray-200 py-1"
                     style="display: none; z-index: 9999;"
                     x-init="$watch('dropdownOpen', value => {
                         if (value) {
                             let rect = $refs.dropdownButton.getBoundingClientRect();
                             $el.style.top = rect.bottom + 5 + 'px';
                             $el.style.left = (rect.right - 192) + 'px';
                         }
                     })">
                    <a href="${viewUrl}"
                       class="block text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-b border-gray-100">
                        <i class="fas fa-eye w-5 text-gray-500 mr-3"></i>
                        ${itemsViewLabel}
                    </a>
                    <a href="${editUrl}"
                       class="block text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-edit w-5 text-emerald-500 mr-3"></i>
                        ${itemsEditLabel}
                    </a>
                    <form action="${deleteUrl}" method="POST" onsubmit="return confirm(${JSON.stringify(itemsConfirmDelete)});">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit"
                                class="w-full text-left block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <i class="fas fa-trash w-5 mr-3"></i>
                            ${itemsDeleteLabel}
                        </button>
                    </form>
                </div>
            </div>
        `;
    }

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
                <div class="p-6 sm:p-8">
                    <div class="mx-auto max-w-md rounded-xl border border-dashed border-gray-200 bg-gray-50/80 px-6 py-10 text-center">
                        <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-white text-gray-300 shadow-sm ring-1 ring-gray-100">
                            <i class="fas fa-box text-2xl" aria-hidden="true"></i>
                        </span>
                        <h3 class="text-base font-semibold text-gray-900">${itemsNoResultsTitle}</h3>
                        <p class="mt-2 text-sm text-gray-600">${itemsNoResultsHint}</p>
                        <a href="{{ route('vendor.items.create') }}"
                           class="mt-6 inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                            {{ __('vendor.add_item') }}
                        </a>
                    </div>
                </div>
            `;
            return;
        }
        
        // Desktop Table
        let desktopTableHtml = `
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="border-b border-gray-200 bg-gray-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.item') }}</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.price') }}</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.stock') }}</th>
                            <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.status') }}</th>
                            <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
        `;
        
        items.forEach(item => {
            const priceType = itemRentalPeriodLabels[item.rental_period] || item.rental_period;
            const categoryName = item.category ? item.category.name : 'N/A';
            const itemPrice = parseFloat(item.price).toFixed(2);
            const safePhotoUrl = item.photo_url ? String(item.photo_url).replace(/"/g, '&quot;') : '';
            const thumbDesktop = item.photo_url
                ? `<img src="${safePhotoUrl}" alt="" class="w-10 h-10 rounded-lg object-cover border border-gray-200 flex-shrink-0" loading="lazy">`
                : `<div class="w-10 h-10 flex items-center justify-center bg-emerald-100 rounded-lg flex-shrink-0"><i class="fas fa-box text-emerald-600 text-sm"></i></div>`;
            
            desktopTableHtml += `
                <tr class="transition-colors hover:bg-emerald-50/30">
                    <td class="px-4 py-3">
                        <div class="flex items-start gap-3">
                            ${thumbDesktop}
                            <div class="min-w-0">
                            <a href="{{ url('vendor/items') }}/${item.uuid}" class="text-sm font-semibold text-gray-900 hover:text-emerald-700">${item.name}</a>
                            ${item.item_code ? `<p class="mt-0.5 font-mono text-[10px] text-gray-500">${item.item_code}</p>` : ''}
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full mt-1">
                                <i class="fas fa-tag mr-1.5"></i>${categoryName}
                            </span>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm font-semibold text-gray-900">₹${itemPrice}</p>
                        <p class="text-xs text-gray-500">${priceType}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${
                            (item.stock ?? 0) > 10 ? 'bg-emerald-100 text-emerald-700' : 
                            ((item.stock ?? 0) > 0 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700')
                        }">
                            <i class="fas fa-cubes text-xs mr-1"></i>${item.stock ?? 0}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        ${item.is_active
                            ? '<span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ __("vendor.active") }}</span>'
                            : '<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ __("vendor.inactive") }}</span>'
                        }
                    </td>
                    <td class="px-4 py-3 text-right">
                        ${buildItemActionsMenu(item.uuid, 'desktop')}
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
            <div class="md:hidden space-y-2 p-2">
        `;
        
        items.forEach(item => {
            const priceType = itemRentalPeriodLabels[item.rental_period] || item.rental_period;
            const categoryName = item.category ? item.category.name : 'N/A';
            const itemPrice = parseFloat(item.price).toFixed(2);
            const safePhotoUrlM = item.photo_url ? String(item.photo_url).replace(/"/g, '&quot;') : '';
            const thumbMobile = item.photo_url
                ? `<img src="${safePhotoUrlM}" alt="" class="w-12 h-12 rounded-xl object-cover border border-gray-200 flex-shrink-0" loading="lazy">`
                : `<div class="w-12 h-12 flex items-center justify-center bg-emerald-100 rounded-xl flex-shrink-0"><i class="fas fa-box text-emerald-600 text-lg"></i></div>`;
            
            mobileCardsHtml += `
                <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            ${thumbMobile}
                            <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 mb-1">
                                <a href="{{ url('vendor/items') }}/${item.uuid}" class="hover:text-emerald-700">${item.name}</a>
                            </h3>
                            ${item.item_code ? `<p class="mb-1 font-mono text-[10px] text-gray-500">${item.item_code}</p>` : ''}
                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                <i class="fas fa-tag mr-1"></i>${categoryName}
                            </span>
                            </div>
                        </div>
                        ${buildItemActionsMenu(item.uuid, 'mobile')}
                    </div>
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                        <div>
                            <p class="text-lg font-bold text-gray-900">₹${itemPrice}</p>
                            <p class="text-xs text-gray-500">${priceType}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold ${
                            (item.stock ?? 0) > 10 ? 'bg-emerald-100 text-emerald-700' : ((item.stock ?? 0) > 0 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700')
                        } rounded-full">
                            <i class="fas fa-cubes mr-1"></i>${item.stock ?? 0}
                        </span>
                    </div>
                </div>
            `;
        });
        
        mobileCardsHtml += `
            </div>
        `;
        
        itemsContent.innerHTML = desktopTableHtml + mobileCardsHtml;

        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
            window.Alpine.initTree(itemsContent);
        }
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
</script>
@endsection
