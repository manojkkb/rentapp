@extends('vendor.layouts.app')

@section('title', __('vendor.title'))
@section('page-title', __('vendor.home'))

@section('content')
{{-- Mobile PWA install prompt (hidden when already installed or on md+ viewports) --}}
<div x-data="vendorDashboardPwaInstall()"
     x-show="show"
     x-transition.opacity.duration.200ms
     x-cloak
     class="md:hidden fixed bottom-20 left-3 right-3 z-[60] rounded-xl border border-emerald-200 bg-white p-4 shadow-lg ring-1 ring-black/5"
     style="display: none;">
    <div class="flex gap-3 items-start">
        <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-700">
            <i class="fas fa-mobile-screen-button text-lg" aria-hidden="true"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 text-sm leading-snug">{{ __('vendor.install_app_title') }}</p>
            <p class="text-xs text-gray-600 mt-1 leading-relaxed">{{ __('vendor.install_app_body') }}</p>
            <p class="text-xs text-gray-500 mt-2 leading-relaxed" x-show="isIOS">{{ __('vendor.install_app_ios_help') }}</p>
            <p class="text-xs text-gray-500 mt-2 leading-relaxed" x-show="!isIOS && !canPrompt">{{ __('vendor.install_app_android_hint') }}</p>
        </div>
        <button type="button"
                x-show="!isIOS && canPrompt"
                @click="install()"
                class="flex-shrink-0 mt-0.5 inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 transition-colors">
            {{ __('vendor.install_app_cta') }}
        </button>
    </div>
</div>

<!-- Shimmer Loading Indicator -->
<div id="dashboardLoadingIndicator" class="">
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
    
    <!-- Shimmer Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                <div class="w-16 h-6 bg-gray-200 rounded-full shimmer"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded shimmer mb-3 w-24"></div>
            <div class="h-8 bg-gray-200 rounded shimmer mb-2 w-20"></div>
            <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                <div class="w-16 h-6 bg-gray-200 rounded-full shimmer"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded shimmer mb-3 w-24"></div>
            <div class="h-8 bg-gray-200 rounded shimmer mb-2 w-20"></div>
            <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                <div class="w-16 h-6 bg-gray-200 rounded-full shimmer"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded shimmer mb-3 w-24"></div>
            <div class="h-8 bg-gray-200 rounded shimmer mb-2 w-20"></div>
            <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
        </div>
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                <div class="w-16 h-6 bg-gray-200 rounded-full shimmer"></div>
            </div>
            <div class="h-4 bg-gray-200 rounded shimmer mb-3 w-24"></div>
            <div class="h-8 bg-gray-200 rounded shimmer mb-2 w-20"></div>
            <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
        </div>
    </div>
    
    <!-- Shimmer Activity & Popular Items -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Quick Actions Shimmer -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                <div class="h-6 bg-gray-200 rounded shimmer mb-4 w-32"></div>
                <div class="space-y-3">
                    <div class="flex items-center p-3 rounded-lg border border-gray-100">
                        <div class="w-10 h-10 bg-gray-200 rounded-lg shimmer"></div>
                        <div class="ml-3 flex-1">
                            <div class="h-4 bg-gray-200 rounded shimmer mb-2 w-24"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                        </div>
                    </div>
                    <div class="flex items-center p-3 rounded-lg border border-gray-100">
                        <div class="w-10 h-10 bg-gray-200 rounded-lg shimmer"></div>
                        <div class="ml-3 flex-1">
                            <div class="h-4 bg-gray-200 rounded shimmer mb-2 w-24"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                        </div>
                    </div>
                    <div class="flex items-center p-3 rounded-lg border border-gray-100">
                        <div class="w-10 h-10 bg-gray-200 rounded-lg shimmer"></div>
                        <div class="ml-3 flex-1">
                            <div class="h-4 bg-gray-200 rounded shimmer mb-2 w-24"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-32"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity Shimmer -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="h-6 bg-gray-200 rounded shimmer w-40"></div>
                    <div class="h-4 bg-gray-200 rounded shimmer w-20"></div>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gray-200 rounded-full shimmer"></div>
                            <div>
                                <div class="h-4 bg-gray-200 rounded shimmer mb-2 w-32"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-40"></div>
                            </div>
                        </div>
                        <div class="h-6 bg-gray-200 rounded-full shimmer w-20"></div>
                    </div>
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gray-200 rounded-full shimmer"></div>
                            <div>
                                <div class="h-4 bg-gray-200 rounded shimmer mb-2 w-32"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-40"></div>
                            </div>
                        </div>
                        <div class="h-6 bg-gray-200 rounded-full shimmer w-20"></div>
                    </div>
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gray-200 rounded-full shimmer"></div>
                            <div>
                                <div class="h-4 bg-gray-200 rounded shimmer mb-2 w-32"></div>
                                <div class="h-3 bg-gray-200 rounded shimmer w-40"></div>
                            </div>
                        </div>
                        <div class="h-6 bg-gray-200 rounded-full shimmer w-20"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Shimmer Performance & Popular Items -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Performance Shimmer -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div class="h-6 bg-gray-200 rounded shimmer w-48"></div>
                <div class="h-8 bg-gray-200 rounded-lg shimmer w-32"></div>
            </div>
            <div class="h-64 bg-gray-200 rounded-lg shimmer"></div>
        </div>
        
        <!-- Popular Items Shimmer -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div class="h-6 bg-gray-200 rounded shimmer w-32"></div>
                <div class="h-4 bg-gray-200 rounded shimmer w-20"></div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                        <div>
                            <div class="h-4 bg-gray-200 rounded shimmer mb-2 w-32"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                        </div>
                    </div>
                    <div class="h-6 bg-gray-200 rounded shimmer w-12"></div>
                </div>
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                        <div>
                            <div class="h-4 bg-gray-200 rounded shimmer mb-2 w-32"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                        </div>
                    </div>
                    <div class="h-6 bg-gray-200 rounded shimmer w-12"></div>
                </div>
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gray-200 rounded-lg shimmer"></div>
                        <div>
                            <div class="h-4 bg-gray-200 rounded shimmer mb-2 w-32"></div>
                            <div class="h-3 bg-gray-200 rounded shimmer w-20"></div>
                        </div>
                    </div>
                    <div class="h-6 bg-gray-200 rounded shimmer w-12"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="dashboardContent" class="hidden">
<!-- Welcome Section -->
<div class="mb-6">
    <div class="bg-gradient-to-r from-emerald-500 to-green-600 rounded-xl p-6 md:p-8 text-white shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="mb-4 md:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold mb-2">
                    {{ __('vendor.welcome_back', ['name' => Auth::user()->currentVendor()->name ?? 'Vendor']) }} 👋
                </h1>
                <p class="text-emerald-50 text-sm md:text-base">
                    {{ __('vendor.today_summary') }}
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('vendor.items.create') }}" class="bg-white text-emerald-600 px-4 py-2 rounded-lg font-medium text-sm hover:bg-emerald-50 transition-colors flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    {{ __('vendor.add_item') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
    <!-- Total Items -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-box text-blue-600 text-xl"></i>
            </div>
            <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">
                <i class="fas fa-arrow-up text-[10px]"></i> 12%
            </span>
        </div>
        <h3 class="text-gray-600 text-sm font-medium mb-1">{{ __('vendor.total_items') }}</h3>
        <p class="text-3xl font-bold text-gray-900" id="stat-total-items">0</p>
        <p class="text-xs text-gray-500 mt-2"><span id="stat-active-items">0</span> {{ __('vendor.active_listings') }}</p>
    </div>

    <!-- Orders -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-receipt text-purple-600 text-xl"></i>
            </div>
            <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">
                <i class="fas fa-arrow-up text-[10px]"></i> 8%
            </span>
        </div>
        <h3 class="text-gray-600 text-sm font-medium mb-1">{{ __('vendor.total_orders') }}</h3>
        <p class="text-3xl font-bold text-gray-900" id="stat-total-orders">0</p>
        <p class="text-xs text-gray-500 mt-2"><span id="stat-monthly-orders">0</span> {{ __('vendor.this_month') }}</p>
    </div>

    <!-- Revenue -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-rupee-sign text-emerald-600 text-xl"></i>
            </div>
            <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">
                <i class="fas fa-arrow-up text-[10px]"></i> 23%
            </span>
        </div>
        <h3 class="text-gray-600 text-sm font-medium mb-1">{{ __('vendor.revenue') }}</h3>
        <p class="text-3xl font-bold text-gray-900" id="stat-revenue">₹0</p>
        <p class="text-xs text-gray-500 mt-2"><span id="stat-monthly-revenue">₹0</span> {{ __('vendor.this_month') }}</p>
    </div>

    <!-- Reviews -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-star text-yellow-600 text-xl"></i>
            </div>
            <span class="text-xs font-medium text-gray-600 bg-gray-50 px-2 py-1 rounded-full">
                0.0
            </span>
        </div>
        <h3 class="text-gray-600 text-sm font-medium mb-1">{{ __('vendor.rating') }}</h3>
        <p class="text-3xl font-bold text-gray-900" id="stat-rating">0.0</p>
        <p class="text-xs text-gray-500 mt-2">{{ __('vendor.from_reviews', ['count' => '<span id="stat-reviews">0</span>']) }}</p>
    </div>
</div>

<!-- Quick Actions & Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    
    <!-- Quick Actions -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-bolt text-emerald-600 mr-2"></i>
                {{ __('vendor.quick_actions') }}
            </h2>
            <div class="space-y-3">
                <a href="{{ route('vendor.items.create') }}" class="flex items-center p-3 rounded-lg hover:bg-emerald-50 transition-colors group border border-gray-100">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                        <i class="fas fa-plus text-emerald-600"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ __('vendor.add_new_item') }}</p>
                        <p class="text-xs text-gray-500">{{ __('vendor.list_rental_item') }}</p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-emerald-600"></i>
                </a>

                <a href="{{ route('vendor.orders.index') }}" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group border border-gray-100">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <i class="fas fa-receipt text-blue-600"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ __('vendor.view_orders') }}</p>
                        <p class="text-xs text-gray-500">{{ __('vendor.manage_orders') }}</p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600"></i>
                </a>

                <a href="{{ route('vendor.profile') }}" class="flex items-center p-3 rounded-lg hover:bg-purple-50 transition-colors group border border-gray-100">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                        <i class="fas fa-user text-purple-600"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ __('vendor.edit_profile') }}</p>
                        <p class="text-xs text-gray-500">{{ __('vendor.update_information') }}</p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-purple-600"></i>
                </a>

                <a href="{{ route('vendor.items.index') }}" class="flex items-center p-3 rounded-lg hover:bg-orange-50 transition-colors group border border-gray-100">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                        <i class="fas fa-chart-line text-orange-600"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ __('vendor.view_analytics') }}</p>
                        <p class="text-xs text-gray-500">{{ __('vendor.check_statistics') }}</p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-orange-600"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900 flex items-center">
                    <i class="fas fa-clock text-emerald-600 mr-2"></i>
                    {{ __('vendor.recent_activity') }}
                </h2>
                <a href="{{ route('vendor.orders.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">{{ __('vendor.view_all') }}</a>
            </div>

            <div class="space-y-4" id="recent-activities-container">
                <!-- Empty State -->
                <div class="text-center py-12" id="activities-empty-state">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-inbox text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('vendor.no_activity') }}</h3>
                    <p class="text-sm text-gray-500 mb-6">{{ __('vendor.start_adding_items') }}</p>
                    <a href="{{ route('vendor.items.create') }}" class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded-lg font-medium text-sm transition-colors">
                        <i class="fas fa-plus mr-2"></i>{{ __('vendor.add_first_item') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Overview & Popular Items -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- Performance Chart -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-chart-bar text-emerald-600 mr-2"></i>
                {{ __('vendor.performance_overview') }}
            </h2>
            <select class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option>{{ __('vendor.last_7_days') }}</option>
                <option>{{ __('vendor.last_30_days') }}</option>
                <option>{{ __('vendor.last_3_months') }}</option>
            </select>
        </div>
        
        <!-- Chart Placeholder -->
        <div class="h-64 flex items-center justify-center bg-gradient-to-br from-emerald-50 to-green-50 rounded-lg border border-emerald-100">
            <div class="text-center">
                <i class="fas fa-chart-line text-emerald-300 text-5xl mb-4"></i>
                <p class="text-gray-600 font-medium">{{ __('vendor.chart_coming_soon') }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ __('vendor.performance_data_here') }}</p>
            </div>
        </div>
    </div>

    <!-- Popular Items -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-fire text-emerald-600 mr-2"></i>
                {{ __('vendor.popular_items') }}
            </h2>
            <a href="{{ route('vendor.items.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">{{ __('vendor.view_all') }}</a>
        </div>

        <div id="popular-items-container">
            <!-- Empty State -->
            <div class="h-64 flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200" id="items-empty-state">
            <div class="text-center px-4">
                <i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-600 font-medium">{{ __('vendor.no_items_yet') }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ __('vendor.add_items_see_popular') }}</p>
            </div>
        </div>
    </div>
</div>

</div><!-- End dashboardContent -->

<!-- Success Message -->
@if (session('success'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-transition
         x-init="setTimeout(() => show = false, 5000)"
         class="fixed bottom-20 md:bottom-4 right-4 bg-emerald-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 z-50">
        <i class="fas fa-check-circle text-2xl"></i>
        <div>
            <p class="font-medium">{{ __('vendor.success') }}!</p>
            <p class="text-sm text-emerald-50">{{ session('success') }}</p>
        </div>
        <button @click="show = false" class="ml-4 text-white hover:text-emerald-100">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchDashboardStats();
});

function fetchDashboardStats() {
    const loader = document.getElementById('dashboardLoadingIndicator');
    const content = document.getElementById('dashboardContent');
    
    // Show loader
    loader.classList.remove('hidden');
    content.classList.add('hidden');
    
    fetch('{{ route("vendor.dashboard.stats") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Populate stats cards
            document.getElementById('stat-total-items').textContent = data.data.stats.total_items || 0;
            document.getElementById('stat-active-items').textContent = data.data.stats.active_items || 0;
            document.getElementById('stat-total-orders').textContent = data.data.stats.total_orders || 0;
            document.getElementById('stat-monthly-orders').textContent = data.data.stats.monthly_orders || 0;
            document.getElementById('stat-revenue').textContent = '₹' + (data.data.stats.total_revenue || 0).toLocaleString('en-IN');
            document.getElementById('stat-monthly-revenue').textContent = '₹' + (data.data.stats.monthly_revenue || 0).toLocaleString('en-IN');
            document.getElementById('stat-rating').textContent = (data.data.stats.average_rating || 0).toFixed(1);
            document.getElementById('stat-reviews').textContent = data.data.stats.total_reviews || 0;
            
            // Populate recent activities
            populateRecentActivities(data.data.recent_activities);
            
            // Populate popular items
            populatePopularItems(data.data.popular_items);
        }
    })
    .catch(error => {
        console.error('Error fetching dashboard stats:', error);
        showToast('{{ __("vendor.error_loading_dashboard") }}', 'error');
    })
    .finally(() => {
        // Hide loader and show content
        loader.classList.add('hidden');
        content.classList.remove('hidden');
    });
}

function populateRecentActivities(activities) {
    const container = document.getElementById('recent-activities-container');
    const emptyState = document.getElementById('activities-empty-state');
    
    if (!activities || activities.length === 0) {
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    
    const activitiesHtml = activities.map(activity => {
        const statusColors = {
            'pending': 'bg-yellow-100 text-yellow-700',
            'confirmed': 'bg-blue-100 text-blue-700',
            'processing': 'bg-purple-100 text-purple-700',
            'completed': 'bg-green-100 text-green-700',
            'cancelled': 'bg-red-100 text-red-700'
        };
        
        const statusColor = statusColors[activity.status] || 'bg-gray-100 text-gray-700';
        
        return `
            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">${activity.customer_name || 'Guest'}</p>
                        <p class="text-sm text-gray-500">${activity.items_count} ${activity.items_count === 1 ? 'item' : 'items'} • ₹${activity.total_amount.toLocaleString('en-IN')}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-3 py-1 rounded-full text-xs font-medium ${statusColor}">
                        ${activity.status}
                    </span>
                    <span class="text-xs text-gray-500">${activity.created_at}</span>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = activitiesHtml;
}

function populatePopularItems(items) {
    const container = document.getElementById('popular-items-container');
    const emptyState = document.getElementById('items-empty-state');
    
    if (!items || items.length === 0) {
        emptyState.classList.remove('hidden');
        return;
    }
    
    emptyState.classList.add('hidden');
    
    const itemsHtml = `
        <div class="space-y-3">
            ${items.map((item, index) => `
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-emerald-400 to-green-500 rounded-lg flex items-center justify-center text-white font-bold">
                            #${index + 1}
                        </div>
                        ${item.image ? `
                            <img src="${item.image}" alt="${item.name}" class="w-12 h-12 object-cover rounded-lg">
                        ` : ''}
                        <div>
                            <p class="font-medium text-gray-900">${item.name}</p>
                            <p class="text-sm text-gray-500">₹${item.price.toLocaleString('en-IN')}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-emerald-600">${item.orders_count}</p>
                        <p class="text-xs text-gray-500">orders</p>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
    
    container.innerHTML = itemsHtml;
}

function showToast(message, type = 'success') {
    const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const toast = document.createElement('div');
    toast.className = `fixed bottom-20 md:bottom-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 z-50`;
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
