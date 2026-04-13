<!-- Modern Green Sidebar -->
<aside x-show="sidebarOpen" 
       x-transition:enter="transform transition ease-in-out duration-300"
       x-transition:enter-start="-translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transform transition ease-in-out duration-300"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="-translate-x-full"
       class="w-72 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex-shrink-0 overflow-y-auto transition-colors">
    
    <!-- Logo -->
    <div class="p-5 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-800 z-10">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-green-gradient rounded-xl flex items-center justify-center">
                <i class="fas fa-home text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Rentkia</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Admin Panel</p>
            </div>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="p-3 space-y-1">
        
        <!-- 1. Dashboard -->
        <div x-data="{ open: true }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-semibold transition-all group">
                <i class="fas fa-home text-base"></i>
                <span>Dashboard</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="{{ route('admin.dashboard') }}" class="block py-1.5 text-sm text-green-600 dark:text-green-400 font-medium transition-colors">Overview</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Revenue Analytics</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Booking Analytics</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Vendor Performance</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">City Analytics</a>
            </div>
        </div>
        
        <!-- 2. User Management -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-users text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">User Management</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">All Users</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Customers</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Vendors</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Admins</a>
                <a href="#" class="flex items-center py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                    KYC Verification
                    <span class="ml-auto bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 text-xs px-1.5 py-0.5 rounded-full font-bold">5</span>
                </a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Suspended Users</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Login Activity</a>
            </div>
        </div>
        
        <!-- 3. Vendor Management -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-store text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Vendor Management</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">All Vendors</a>
                <a href="#" class="flex items-center py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                    Pending Approval
                    <span class="ml-auto bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 text-xs px-1.5 py-0.5 rounded-full font-bold">12</span>
                </a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Approved Vendors</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Rejected Vendors</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Vendor Ratings</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Vendor Wallet</a>
                <a href="#" class="flex items-center py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                    Payout Requests
                    <span class="ml-auto bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs px-1.5 py-0.5 rounded-full font-bold">8</span>
                </a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Commission Settings</a>
            </div>
        </div>
        
        <!-- 4. Categories -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-folder text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Categories</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Main Categories</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Sub Categories</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Category Icons</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Category SEO</a>
            </div>
        </div>
        
        <!-- 5. Item Management -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-box text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Item Management</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">All Items</a>
                <a href="#" class="flex items-center py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                    Pending Items
                    <span class="ml-auto bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 text-xs px-1.5 py-0.5 rounded-full font-bold">15</span>
                </a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Active Items</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Inactive Items</a>
                <a href="#" class="flex items-center py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                    Low Stock Alerts
                    <span class="ml-auto bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs px-1.5 py-0.5 rounded-full font-bold">3</span>
                </a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Service Items</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Product Items</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Bulk Import</a>
            </div>
        </div>
        
        <!-- 6. Booking Management -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-calendar-check text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Booking Management</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">All Bookings</a>
                <a href="#" class="flex items-center py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                    Pending
                    <span class="ml-auto bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 text-xs px-1.5 py-0.5 rounded-full font-bold">23</span>
                </a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Confirmed</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Completed</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Cancelled</a>
                <a href="#" class="flex items-center py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                    Refund Requests
                    <span class="ml-auto bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs px-1.5 py-0.5 rounded-full font-bold">4</span>
                </a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Damage Reports</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Invoice Management</a>
            </div>
        </div>
        
        <!-- 7. Finance -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-wallet text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Finance</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Revenue Summary</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Commission Report</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Vendor Earnings</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Customer Payments</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Wallet Transactions</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Refund Logs</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Tax & GST Report</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Settlement Report</a>
            </div>
        </div>
        
        <!-- 8. Reports -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-chart-bar text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Reports</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Daily Report</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Monthly Report</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Yearly Report</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Category Wise</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Vendor Wise</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">City Wise</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Top Selling Items</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Customer Retention</a>
            </div>
        </div>
        
        <!-- 9. Marketing & CMS -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-bullhorn text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Marketing & CMS</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Homepage Banner</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Coupons</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Promo Codes</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Email Templates</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">SMS Templates</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Push Notifications</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Blog</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">SEO Settings</a>
            </div>
        </div>
        
        <!-- 10. Location Management -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-map-marker-alt text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Location Management</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Countries</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">States</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Cities</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Service Areas</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Delivery Charges</a>
            </div>
        </div>
        
        <!-- 11. System Settings -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-cog text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">System Settings</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">General Settings</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Booking Rules</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Cancellation Policy</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Deposit Rules</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Damage Charges Rules</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Currency Settings</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Tax Settings</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Minimum Order Rules</a>
            </div>
        </div>
        
        <!-- 12. Roles & Permissions -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-lock text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Roles & Permissions</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Roles</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Permissions</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Assign Permissions</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Admin Activity Logs</a>
            </div>
        </div>
        
        <!-- 13. Support System -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-headset text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Support System</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition x-cloak class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="flex items-center py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                    Support Tickets
                    <span class="ml-auto bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs px-1.5 py-0.5 rounded-full font-bold">7</span>
                </a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Customer Complaints</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Vendor Disputes</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Chat Logs</a>
            </div>
        </div>
        
    </nav>
    
    <!-- Help Card -->
    <div class="m-3 p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-100 dark:border-green-800">
        <div class="w-10 h-10 bg-green-gradient rounded-lg flex items-center justify-center mb-2">
            <i class="fas fa-lightbulb text-white"></i>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-1">Need Help?</h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">Check our documentation</p>
        <button class="w-full bg-white dark:bg-gray-700 text-green-700 dark:text-green-400 py-2 rounded-lg text-sm font-bold hover:bg-green-50 dark:hover:bg-gray-600 transition-all">
            Get Help
        </button>
    </div>
</aside>