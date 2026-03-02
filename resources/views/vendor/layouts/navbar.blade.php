<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="px-4 py-3">
        <div class="flex items-center justify-between">
            <!-- Left: Menu Toggle & Breadcrumb -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Menu Toggle -->
                <button @click="toggleSidebar()" 
                        class="text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Breadcrumb -->
                <div class="hidden md:flex items-center space-x-2 text-sm">
                    <a href="{{ route('vendor.home') }}" class="text-gray-500 hover:text-emerald-600 transition-colors">
                        <i class="fas fa-home"></i>
                    </a>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-700 font-medium">@yield('page-title', 'Home')</span>
                </div>
            </div>

            <!-- Right: Search, Notifications, Profile -->
            <div class="flex items-center space-x-2 md:space-x-4">
                
                <!-- Search Bar (Hidden on mobile) -->
                <div class="hidden lg:block">
                    <div class="relative">
                        <input type="text" 
                               placeholder="Search items, orders..." 
                               class="w-64 pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <!-- Mobile Search Icon -->
                <button class="lg:hidden text-gray-600 hover:text-gray-900 p-2">
                    <i class="fas fa-search text-lg"></i>
                </button>

                <!-- Notifications -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50"
                         style="display: none;">
                        
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                        </div>

                        <div class="max-h-96 overflow-y-auto">
                            <div class="p-4 text-center text-gray-500 text-sm">
                                No new notifications
                            </div>
                        </div>

                        <div class="p-3 border-t border-gray-200 text-center">
                            <a href="#" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50 focus:outline-none">
                        <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                            {{ strtoupper(substr(Auth::user()->currentVendor()->name ?? 'V', 0, 1)) }}
                        </div>
                        <span class="hidden md:block text-sm font-medium text-gray-700">
                            {{ Str::limit(Auth::user()->currentVendor()->name ?? 'Vendor', 15) }}
                        </span>
                        <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                    </button>

                    <!-- Profile Dropdown Menu -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50"
                         style="display: none;">
                        
                        <!-- Profile Info -->
                        <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->currentVendor()->name ?? 'Vendor' }}</p>
                            <p class="text-xs text-gray-600 truncate">{{ Auth::user()->mobile ?? '' }}</p>
                        </div>

                        <!-- Menu Items -->
                        <div class="py-2">
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                                <i class="fas fa-user w-5"></i>
                                <span class="ml-3">My Profile</span>
                            </a>
                            <a href="{{ route('vendor.select') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition-colors">
                                <i class="fas fa-exchange-alt w-5"></i>
                                <span class="ml-3">Switch Vendor</span>
                            </a>
                            <a href="{{ route('vendor.create') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                                <i class="fas fa-plus-circle w-5"></i>
                                <span class="ml-3">Create New Vendor</span>
                            </a>
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                                <i class="fas fa-cog w-5"></i>
                                <span class="ml-3">Settings</span>
                            </a>
                            <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                                <i class="fas fa-question-circle w-5"></i>
                                <span class="ml-3">Help & Support</span>
                            </a>
                        </div>

                        <!-- Logout -->
                        <div class="border-t border-gray-200">
                            <form action="{{ route('vendor.logout') }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span class="ml-3 font-medium">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>