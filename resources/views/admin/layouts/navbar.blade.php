<!-- Modern Green Navbar with Dark Mode Toggle -->
<nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-elegant transition-colors">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            <!-- Left side - Menu toggle & Search -->
            <div class="flex items-center space-x-4">
                <!-- Sidebar Toggle -->
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-700 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-gray-700 hover:text-green-600 dark:hover:text-green-400 p-2 rounded-lg focus:outline-none transition-all">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <!-- Search Bar -->
                <div class="relative hidden md:block">
                    <input type="text" 
                           placeholder="Search properties, tenants..." 
                           class="w-64 lg:w-96 pl-10 pr-4 py-2.5 rounded-xl border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-green-500 focus:ring-2 focus:ring-green-200 dark:focus:ring-green-800 focus:outline-none transition-all font-medium placeholder-gray-500 dark:placeholder-gray-400">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
                </div>
            </div>
            
            <!-- Right side - Dark Mode, Notifications & Profile -->
            <div class="flex items-center space-x-3">
                <!-- Dark Mode Toggle -->
                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                        class="p-2.5 text-gray-700 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-gray-700 hover:text-green-600 dark:hover:text-green-400 rounded-lg transition-all">
                    <i class="fas text-xl" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
                </button>
                
                <!-- Notifications -->
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen = !notifOpen" 
                            class="relative p-2.5 text-gray-700 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-gray-700 hover:text-green-600 dark:hover:text-green-400 rounded-lg transition-all">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></span>
                    </button>
                    
                    <!-- Notification Dropdown -->
                    <div x-show="notifOpen" 
                         @click.away="notifOpen = false"
                         x-transition
                         class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border-2 border-gray-200 dark:border-gray-700 overflow-hidden z-50">
                        <div class="p-4 bg-green-gradient text-white">
                            <h3 class="font-bold">Notifications</h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <a href="#" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-user text-green-600 dark:text-green-400"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800 dark:text-gray-200 font-medium">New user registered</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">2 minutes ago</p>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-file-alt text-green-600 dark:text-green-400"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-800 dark:text-gray-200 font-medium">New report generated</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">1 hour ago</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <a href="#" class="block p-3 text-center text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-gray-700 font-bold">
                            View all notifications
                        </a>
                    </div>
                </div>
                
                <!-- Profile -->
                <div class="relative" x-data="{ profileOpen: false }">
                    <button @click="profileOpen = !profileOpen" 
                            class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->guard('admin')->user()->name ?? 'Admin User') }}&background=10b981&color=fff" 
                             alt="Profile" 
                             class="w-10 h-10 rounded-full ring-2 ring-green-500">
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ auth()->guard('admin')->user()->name ?? 'Admin User' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->guard('admin')->user()->email ?? '' }}</p>
                        </div>
                        <i class="fas fa-chevron-down text-sm text-gray-400 dark:text-gray-500"></i>
                    </button>
                    
                    <!-- Profile Dropdown -->
                    <div x-show="profileOpen" 
                         @click.away="profileOpen = false"
                         x-transition
                         class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl border-2 border-gray-200 dark:border-gray-700 overflow-hidden z-50">
                        <div class="p-4 bg-green-gradient text-white">
                            <p class="font-bold">{{ auth()->guard('admin')->user()->name ?? 'Admin User' }}</p>
                            <p class="text-xs text-green-100">{{ auth()->guard('admin')->user()->email ?? '' }}</p>
                        </div>
                        <div class="py-2">
                            <a href="{{ route('admin.profile') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                <i class="fas fa-user-circle w-5"></i>
                                <span class="text-sm">My Profile</span>
                            </a>
                            <a href="{{ route('admin.settings') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                <i class="fas fa-cog w-5"></i>
                                <span class="text-sm">Settings</span>
                            </a>
                            <hr class="my-2 border-gray-200 dark:border-gray-700">
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span class="text-sm">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>