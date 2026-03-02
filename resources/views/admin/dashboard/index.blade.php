@extends('admin.layouts.app')

@section('title', 'Dashboard - RentApp Admin')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-4xl font-black text-gray-900 dark:text-white">Dashboard</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2 text-lg">Welcome back! Here's what's happening today.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <button class="bg-green-gradient text-white px-8 py-3.5 rounded-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all font-bold text-sm uppercase tracking-wide">
                <i class="fas fa-plus mr-2"></i>Add New Property
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Properties Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl transition-all p-6 border-2 border-gray-100 dark:border-gray-700 hover:border-green-500 dark:hover:border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-semibold uppercase tracking-wide">Total Properties</p>
                    <h3 class="text-4xl font-black text-gray-900 dark:text-white mt-2">156</h3>
                    <p class="text-green-600 dark:text-green-400 text-sm mt-2 flex items-center font-bold">
                        <i class="fas fa-arrow-up mr-1"></i> 12% vs last month
                    </p>
                </div>
                <div class="w-16 h-16 bg-green-gradient rounded-2xl flex items-center justify-center">
                    <i class="fas fa-building text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Active Tenants Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl transition-all p-6 border-2 border-gray-100 dark:border-gray-700 hover:border-green-500 dark:hover:border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-semibold uppercase tracking-wide">Active Tenants</p>
                    <h3 class="text-4xl font-black text-gray-900 dark:text-white mt-2">289</h3>
                    <p class="text-green-600 dark:text-green-400 text-sm mt-2 flex items-center font-bold">
                        <i class="fas fa-arrow-up mr-1"></i> 8% vs last month
                    </p>
                </div>
                <div class="w-16 h-16 bg-green-gradient rounded-2xl flex items-center justify-center">
                    <i class="fas fa-users text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl transition-all p-6 border-2 border-gray-100 dark:border-gray-700 hover:border-green-500 dark:hover:border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-semibold uppercase tracking-wide">Monthly Revenue</p>
                    <h3 class="text-4xl font-black text-gray-900 dark:text-white mt-2">$48.5K</h3>
                    <p class="text-green-600 dark:text-green-400 text-sm mt-2 flex items-center font-bold">
                        <i class="fas fa-arrow-up mr-1"></i> 23% vs last month
                    </p>
                </div>
                <div class="w-16 h-16 bg-green-gradient rounded-2xl flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Maintenance Requests Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl transition-all p-6 border-2 border-gray-100 dark:border-gray-700 hover:border-green-500 dark:hover:border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm font-semibold uppercase tracking-wide">Pending Issues</p>
                    <h3 class="text-4xl font-black text-gray-900 dark:text-white mt-2">12</h3>
                    <p class="text-red-600 dark:text-red-400 text-sm mt-2 flex items-center font-bold">
                        <i class="fas fa-arrow-down mr-1"></i> 3 less than yesterday
                    </p>
                </div>
                <div class="w-16 h-16 bg-red-500 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-tools text-white text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Revenue Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border-2 border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white">Revenue Overview</h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-1 font-medium">Monthly revenue trends</p>
                </div>
                <select class="px-4 py-2.5 border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-xl text-sm font-bold focus:outline-none focus:border-green-500 transition-all">
                    <option>Last 6 months</option>
                    <option>Last year</option>
                    <option>All time</option>
                </select>
            </div>
            
            <!-- Simple Bar Chart Representation -->
            <div class="space-y-4">
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-900 dark:text-white font-bold w-16">Jan</span>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-10 overflow-hidden">
                        <div class="bg-green-gradient h-full rounded-full flex items-center justify-end pr-3 text-white text-xs font-bold" style="width: 65%">$32K</div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-900 dark:text-white font-bold w-16">Feb</span>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-10 overflow-hidden">
                        <div class="bg-green-gradient h-full rounded-full flex items-center justify-end pr-3 text-white text-xs font-bold" style="width: 75%">$38K</div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-900 dark:text-white font-bold w-16">Mar</span>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-10 overflow-hidden">
                        <div class="bg-green-gradient h-full rounded-full flex items-center justify-end pr-3 text-white text-xs font-bold" style="width: 85%">$42K</div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-900 dark:text-white font-bold w-16">Apr</span>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-10 overflow-hidden">
                        <div class="bg-green-gradient h-full rounded-full flex items-center justify-end pr-3 text-white text-xs font-bold" style="width: 70%">$35K</div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-900 dark:text-white font-bold w-16">May</span>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-10 overflow-hidden">
                        <div class="bg-green-gradient h-full rounded-full flex items-center justify-end pr-3 text-white text-xs font-bold" style="width: 90%">$45K</div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-900 dark:text-white font-bold w-16">Jun</span>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-10 overflow-hidden">
                        <div class="bg-green-gradient h-full rounded-full flex items-center justify-end pr-3 text-white text-xs font-bold" style="width: 95%">$48.5K</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-elegant border-2 border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-6">Recent Activity</h2>
            <div class="space-y-4">
                <div class="flex items-start space-x-3 pb-4 border-b-2 border-gray-100 dark:border-gray-700">
                    <div class="w-11 h-11 rounded-xl bg-green-gradient flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-plus text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900 dark:text-white font-bold">New tenant added</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 font-medium">John Doe - Apartment 4B</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">2 hours ago</p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-3 pb-4 border-b-2 border-gray-100 dark:border-gray-700">
                    <div class="w-11 h-11 rounded-xl bg-green-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900 dark:text-white font-bold">Payment received</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 font-medium">$2,500 from Jane Smith</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">4 hours ago</p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-3 pb-4 border-b-2 border-gray-100 dark:border-gray-700">
                    <div class="w-11 h-11 rounded-xl bg-red-500 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900 dark:text-white font-bold">Maintenance request</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 font-medium">Leaking faucet - Unit 3A</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">5 hours ago</p>
                    </div>
                </div>
                
                <div class="flex items-start space-x-3 pb-4">
                    <div class="w-11 h-11 rounded-xl bg-gray-800 dark:bg-gray-600 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-home text-white"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900 dark:text-white font-bold">New property listed</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 font-medium">Sunset Boulevard Villa</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">1 day ago</p>
                    </div>
                </div>
            </div>
            
            <button class="w-full mt-4 py-2.5 text-green-700 dark:text-green-400 hover:bg-green-50 dark:hover:bg-gray-700 rounded-xl transition-all text-sm font-bold border-2 border-gray-200 dark:border-gray-700 hover:border-green-500">
                View All Activity
            </button>
        </div>
    </div>

    <!-- Property Status & Quick Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Property Status -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-elegant border-2 border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-2xl font-black text-gray-900 dark:text-white mb-6">Property Status</h2>
            <div class="space-y-4">
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Occupied</span>
                        <span class="text-sm font-black text-gray-900 dark:text-white">85%</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                        <div class="bg-green-500 h-full rounded-full" style="width: 85%"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Available</span>
                        <span class="text-sm font-black text-gray-900 dark:text-white">12%</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                        <div class="bg-green-gradient h-full rounded-full" style="width: 12%"></div>
                    </div>
                </div>
                
                <div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Under Maintenance</span>
                        <span class="text-sm font-black text-gray-900 dark:text-white">3%</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                        <div class="bg-amber-500 h-full rounded-full" style="width: 3%"></div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t-2 border-gray-100 dark:border-gray-700">
                <div class="text-center">
                    <p class="text-3xl font-black text-gray-900 dark:text-white">132</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 font-semibold">Occupied</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-black text-gray-900 dark:text-white">19</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 font-semibold">Available</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-black text-gray-900 dark:text-white">5</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 font-semibold">Maintenance</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-green-gradient rounded-2xl shadow-2xl p-6 text-white">
            <h2 class="text-2xl font-black mb-6">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-4">
                <button class="bg-white text-green-700 hover:bg-green-50 rounded-xl p-4 text-left transition-all transform hover:scale-105 hover:shadow-xl">
                    <i class="fas fa-plus-circle text-2xl mb-3"></i>
                    <p class="font-bold">Add Property</p>
                    <p class="text-xs text-green-600 mt-1 font-medium">List new property</p>
                </button>
                
                <button class="bg-white text-green-700 hover:bg-green-50 rounded-xl p-4 text-left transition-all transform hover:scale-105 hover:shadow-xl">
                    <i class="fas fa-user-plus text-2xl mb-3"></i>
                    <p class="font-bold">Add Tenant</p>
                    <p class="text-xs text-green-600 mt-1 font-medium">Create new tenant</p>
                </button>
                
                <button class="bg-white text-green-700 hover:bg-green-50 rounded-xl p-4 text-left transition-all transform hover:scale-105 hover:shadow-xl">
                    <i class="fas fa-file-invoice-dollar text-2xl mb-3"></i>
                    <p class="font-bold">New Invoice</p>
                    <p class="text-xs text-green-600 mt-1 font-medium">Generate invoice</p>
                </button>
                
                <button class="bg-white text-green-700 hover:bg-green-50 rounded-xl p-4 text-left transition-all transform hover:scale-105 hover:shadow-xl">
                    <i class="fas fa-chart-line text-2xl mb-3"></i>
                    <p class="font-bold">View Reports</p>
                    <p class="text-xs text-green-600 mt-1 font-medium">View analytics</p>
                </button>
            </div>
            
            <div class="mt-6 pt-6 border-t-2 border-white border-opacity-20">
                <p class="text-sm font-semibold mb-3">System Status</p>
                <div class="flex items-center justify-between bg-white bg-opacity-20 backdrop-blur-lg rounded-lg p-3">
                    <span class="text-sm font-bold">All systems operational</span>
                    <span class="w-3 h-3 bg-green-300 rounded-full animate-pulse"></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection