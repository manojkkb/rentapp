@extends('vendor.layouts.app')

@section('title', 'Home - RentApp')
@section('page-title', 'Home')

@section('content')
<!-- Welcome Section -->
<div class="mb-6">
    <div class="bg-gradient-to-r from-emerald-500 to-green-600 rounded-xl p-6 md:p-8 text-white shadow-lg">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="mb-4 md:mb-0">
                <h1 class="text-2xl md:text-3xl font-bold mb-2">
                    Welcome back, {{ Auth::user()->currentVendor()->name ?? 'Vendor' }}! 👋
                </h1>
                <p class="text-emerald-50 text-sm md:text-base">
                    Here's what's happening with your store today
                </p>
            </div>
            <div class="flex space-x-3">
                <button class="bg-white text-emerald-600 px-4 py-2 rounded-lg font-medium text-sm hover:bg-emerald-50 transition-colors flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Add Item
                </button>
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
        <h3 class="text-gray-600 text-sm font-medium mb-1">Total Items</h3>
        <p class="text-3xl font-bold text-gray-900">0</p>
        <p class="text-xs text-gray-500 mt-2">Active listings</p>
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
        <h3 class="text-gray-600 text-sm font-medium mb-1">Total Orders</h3>
        <p class="text-3xl font-bold text-gray-900">0</p>
        <p class="text-xs text-gray-500 mt-2">This month</p>
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
        <h3 class="text-gray-600 text-sm font-medium mb-1">Revenue</h3>
        <p class="text-3xl font-bold text-gray-900">₹0</p>
        <p class="text-xs text-gray-500 mt-2">This month</p>
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
        <h3 class="text-gray-600 text-sm font-medium mb-1">Rating</h3>
        <p class="text-3xl font-bold text-gray-900">0.0</p>
        <p class="text-xs text-gray-500 mt-2">From 0 reviews</p>
    </div>
</div>

<!-- Quick Actions & Recent Activity -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    
    <!-- Quick Actions -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-bolt text-emerald-600 mr-2"></i>
                Quick Actions
            </h2>
            <div class="space-y-3">
                <a href="#" class="flex items-center p-3 rounded-lg hover:bg-emerald-50 transition-colors group border border-gray-100">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:bg-emerald-200 transition-colors">
                        <i class="fas fa-plus text-emerald-600"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">Add New Item</p>
                        <p class="text-xs text-gray-500">List a new rental item</p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-emerald-600"></i>
                </a>

                <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition-colors group border border-gray-100">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <i class="fas fa-receipt text-blue-600"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">View Orders</p>
                        <p class="text-xs text-gray-500">Manage your orders</p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600"></i>
                </a>

                <a href="#" class="flex items-center p-3 rounded-lg hover:bg-purple-50 transition-colors group border border-gray-100">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                        <i class="fas fa-user text-purple-600"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">Edit Profile</p>
                        <p class="text-xs text-gray-500">Update your information</p>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-purple-600"></i>
                </a>

                <a href="#" class="flex items-center p-3 rounded-lg hover:bg-orange-50 transition-colors group border border-gray-100">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center group-hover:bg-orange-200 transition-colors">
                        <i class="fas fa-chart-line text-orange-600"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">View Analytics</p>
                        <p class="text-xs text-gray-500">Check your statistics</p>
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
                    Recent Activity
                </h2>
                <a href="#" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">View All</a>
            </div>

            <div class="space-y-4">
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-inbox text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Activity Yet</h3>
                    <p class="text-sm text-gray-500 mb-6">Start by adding your first item to see activity here</p>
                    <button class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-2 rounded-lg font-medium text-sm transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Your First Item
                    </button>
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
                Performance Overview
            </h2>
            <select class="text-sm border border-gray-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                <option>Last 7 days</option>
                <option>Last 30 days</option>
                <option>Last 3 months</option>
            </select>
        </div>
        
        <!-- Chart Placeholder -->
        <div class="h-64 flex items-center justify-center bg-gradient-to-br from-emerald-50 to-green-50 rounded-lg border border-emerald-100">
            <div class="text-center">
                <i class="fas fa-chart-line text-emerald-300 text-5xl mb-4"></i>
                <p class="text-gray-600 font-medium">Chart Coming Soon</p>
                <p class="text-sm text-gray-500 mt-1">Performance data will appear here</p>
            </div>
        </div>
    </div>

    <!-- Popular Items -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-fire text-emerald-600 mr-2"></i>
                Popular Items
            </h2>
            <a href="#" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">View All</a>
        </div>

        <!-- Empty State -->
        <div class="h-64 flex items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg border border-gray-200">
            <div class="text-center px-4">
                <i class="fas fa-box-open text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-600 font-medium">No Items Yet</p>
                <p class="text-sm text-gray-500 mt-1">Add items to see which ones are popular</p>
            </div>
        </div>
    </div>
</div>

<!-- Success Message -->
@if (session('success'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-transition
         x-init="setTimeout(() => show = false, 5000)"
         class="fixed bottom-20 md:bottom-4 right-4 bg-emerald-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 z-50">
        <i class="fas fa-check-circle text-2xl"></i>
        <div>
            <p class="font-medium">Success!</p>
            <p class="text-sm text-emerald-50">{{ session('success') }}</p>
        </div>
        <button @click="show = false" class="ml-4 text-white hover:text-emerald-100">
            <i class="fas fa-times"></i>
        </button>
    </div>
@endif

@endsection
