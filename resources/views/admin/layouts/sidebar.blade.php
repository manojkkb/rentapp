<!-- Modern Green Sidebar -->
<aside
    class="admin-sidebar fixed inset-y-0 left-0 z-50 flex h-full w-72 max-h-[100dvh] min-h-0 flex-shrink-0 flex-col overflow-y-auto overscroll-contain border-r border-gray-200 bg-white shadow-xl transition-transform duration-300 ease-out dark:border-gray-700 dark:bg-gray-800 md:static md:z-auto md:max-h-none md:w-72 md:translate-x-0 md:shadow-none"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
>
    <!-- Logo -->
    <div class="sticky top-0 z-10 border-b border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
        <div class="flex items-center justify-between gap-2">
            <div class="flex min-w-0 items-center space-x-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-gradient">
                    <i class="fas fa-home text-xl text-white"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="truncate text-xl font-bold text-gray-900 dark:text-white">Rentkia</h1>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Admin Panel</p>
                </div>
            </div>
            <button
                type="button"
                @click="closeSidebar()"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 md:hidden touch-manipulation"
                aria-label="Close menu"
            >
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="space-y-1 p-3" @click="if ($event.target.closest('a[href]')) closeSidebar()">
        
        <!-- 1. Dashboard -->
        <div x-data="{ open: {{ request()->routeIs('admin.dashboard*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all group
                           {{ request()->routeIs('admin.dashboard*')
                               ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-semibold'
                               : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                <i class="fas fa-home text-base {{ request()->routeIs('admin.dashboard*') ? '' : 'text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400' }}"></i>
                <span class="{{ request()->routeIs('admin.dashboard*') ? '' : 'font-medium' }}">Dashboard</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition class="pl-10 pr-3 py-1 space-y-0.5">
                @php
                    $dashLinks = [
                        ['route' => 'admin.dashboard', 'label' => 'Overview'],
                        ['route' => 'admin.dashboard.revenue', 'label' => 'Revenue'],
                        ['route' => 'admin.dashboard.bookings', 'label' => 'Bookings'],
                        ['route' => 'admin.dashboard.vendors', 'label' => 'Vendors'],
                        ['route' => 'admin.dashboard.cities', 'label' => 'Cities'],
                    ];
                @endphp
                @foreach ($dashLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       class="block py-1.5 text-sm transition-colors {{ request()->routeIs($link['route']) ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
        
        <!-- 2. Users -->
        <div x-data="{ open: {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all group
                           {{ request()->routeIs('admin.users.*')
                               ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-semibold'
                               : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                <i class="fas fa-users text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Users</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition class="pl-10 pr-3 py-1 space-y-0.5">
                @php
                    $userLinks = [
                        ['route' => 'admin.users.index', 'label' => 'All'],
                        ['route' => 'admin.users.customers', 'label' => 'Customers'],
                        ['route' => 'admin.users.vendor-accounts', 'label' => 'Staff'],
                        ['route' => 'admin.users.admins', 'label' => 'Admins'],
                        ['route' => 'admin.users.kyc', 'label' => 'KYC', 'badge' => $pendingKycCount ?? 0],
                        ['route' => 'admin.users.suspended', 'label' => 'Suspended'],
                        ['route' => 'admin.users.login-activity', 'label' => 'Logins'],
                    ];
                @endphp
                @foreach ($userLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       class="flex items-center py-1.5 text-sm transition-colors {{ request()->routeIs($link['route']) ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400' }}">
                        <span>{{ $link['label'] }}</span>
                        @if(!empty($link['badge']))
                            <span class="ml-auto bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 text-xs px-1.5 py-0.5 rounded-full font-bold">{{ $link['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
        
        <!-- 3. Vendors -->
        <div x-data="{ open: {{ request()->routeIs('admin.vendors.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group {{ request()->routeIs('admin.vendors.*') ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' : '' }}">
                <i class="fas fa-store text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Vendors</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="{{ route('admin.vendors.index') }}"
                   class="block py-1.5 text-sm transition-colors {{ request()->routeIs('admin.vendors.*') ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400' }}">
                    All
                </a>
            </div>
        </div>

        <!-- Subscriptions -->
        <div x-data="{ open: {{ request()->routeIs('admin.subscriptions.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group {{ request()->routeIs('admin.subscriptions.*') ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' : '' }}">
                <i class="fas fa-credit-card text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Subscriptions</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="block py-1.5 text-sm transition-colors {{ request()->routeIs('admin.subscriptions.index') ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400' }}">
                    All
                </a>
                <a href="{{ route('admin.subscriptions.plans.index') }}"
                   class="block py-1.5 text-sm transition-colors {{ request()->routeIs('admin.subscriptions.plans.*') ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400' }}">
                    Plans
                </a>
            </div>
        </div>

        <!-- 4. Biz types -->
        <div x-data="{ open: {{ request()->routeIs('admin.business-categories.*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group {{ request()->routeIs('admin.business-categories.*') ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' : '' }}">
                <i class="fas fa-briefcase text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Biz Types</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="{{ route('admin.business-categories.index') }}"
                   class="block py-1.5 text-sm transition-colors {{ request()->routeIs('admin.business-categories.*') ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400' }}">
                    All
                </a>
            </div>
        </div>
        
        <!-- Reports -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-chart-bar text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Reports</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Daily</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Monthly</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Yearly</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Category</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Vendor</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">City</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Top items</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Retention</a>
            </div>
        </div>
        
        <!-- Marketing -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-bullhorn text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Marketing</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Banner</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Coupons</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Promos</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Email</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">SMS</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Push</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Blog</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">SEO</a>
            </div>
        </div>
        
        <!-- Settings -->
        <div x-data="{ open: {{ request()->routeIs('admin.settings*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group {{ request()->routeIs('admin.settings*') ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400' : '' }}">
                <i class="fas fa-cog text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Settings</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="{{ route('admin.settings') }}"
                   class="block py-1.5 text-sm transition-colors {{ request()->routeIs('admin.settings*') ? 'text-green-600 dark:text-green-400 font-medium' : 'text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400' }}">
                    Platform
                </a>
            </div>
        </div>
        
        <!-- Roles -->
        <div x-data="{ open: false }">
            <button @click="open = !open" 
                    class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all group">
                <i class="fas fa-lock text-base text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400"></i>
                <span class="font-medium">Roles</span>
                <i class="fas fa-chevron-down text-xs ml-auto transform transition-transform" :class="{ 'rotate-180': open }"></i>
            </button>
            <div x-show="open" x-transition class="pl-10 pr-3 py-1 space-y-0.5">
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Roles</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Permissions</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Assign</a>
                <a href="#" class="block py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">Logs</a>
            </div>
        </div>
        
        <!-- Support -->
        <a href="{{ route('admin.support.index') }}"
           class="flex items-center space-x-3 px-3 py-2.5 rounded-lg transition-all group
                  {{ request()->routeIs('admin.support.*')
                      ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-semibold'
                      : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
            <i class="fas fa-headset text-base {{ request()->routeIs('admin.support.*') ? '' : 'text-gray-500 dark:text-gray-400 group-hover:text-green-600 dark:group-hover:text-green-400' }}"></i>
            <span class="font-medium">Support</span>
            @if(($openSupportTicketsCount ?? 0) > 0)
                <span class="ml-auto rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-900 dark:text-amber-200">{{ $openSupportTicketsCount }}</span>
            @endif
        </a>
        
    </nav>
    
    <!-- Help Card -->
    <div class="m-3 p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-100 dark:border-green-800">
        <div class="w-10 h-10 bg-green-gradient rounded-lg flex items-center justify-center mb-2">
            <i class="fas fa-lightbulb text-white"></i>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-1">Help</h3>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">Docs &amp; support</p>
        <button class="w-full bg-white dark:bg-gray-700 text-green-700 dark:text-green-400 py-2 rounded-lg text-sm font-bold hover:bg-green-50 dark:hover:bg-gray-600 transition-all">
            Help
        </button>
    </div>
</aside>