<!-- Sidebar for larger screens -->
<aside 
    x-show="sidebarOpen" 
    @click.away="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed md:static inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-200 transform md:translate-x-0 transition-transform duration-300 flex flex-col"
>
    <!-- Sidebar Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50">
        <div class="flex items-center space-x-3 flex-1 min-w-0">
            @if(Auth::user()->currentVendor()->logo)
                <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0 border-2 border-emerald-500 shadow-sm">
                    <img src="{{ asset('storage/' . Auth::user()->currentVendor()->logo) }}" 
                         alt="{{ Auth::user()->currentVendor()->name }}"
                         class="w-full h-full object-cover">
                </div>
            @else
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                    <span class="text-white font-bold text-xl">
                        {{ strtoupper(substr(Auth::user()->currentVendor()->name ?? 'V', 0, 1)) }}
                    </span>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <h2 class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->currentVendor()->name ?? __('vendor.vendor') }}</h2>
                <p class="text-xs text-gray-600 truncate flex items-center">
                    <i class="fas fa-phone text-[10px] mr-1.5 text-emerald-600"></i>
                    {{ Auth::user()->mobile ?? __('vendor.no_phone') }}
                </p>
            </div>
        </div>
        <button @click="sidebarOpen = false; localStorage.setItem('sidebarOpen', false);" class="md:hidden text-gray-500 hover:text-gray-700 ml-2 flex-shrink-0">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 overflow-y-auto">
        <ul class="space-y-1">
            <!-- Home -->
            <li>
                <a href="{{ route('vendor.home') }}" 
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors
                          {{ request()->routeIs('vendor.home') 
                              ? 'bg-emerald-500 text-white' 
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    <i class="fas fa-home w-5"></i>
                    <span class="ml-3">{{ __('vendor.home') }}</span>
                </a>
            </li>

            <!-- Items -->
            <li>
                <a href="{{ route('vendor.items.index') }}" 
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors
                   {{ request()->routeIs('vendor.items.*') ? 'bg-emerald-500 text-white' : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    <i class="fas fa-box w-5"></i>
                    <span class="ml-3">{{ __('vendor.items') }}</span>
                </a>
            </li>

            <!-- Categories -->
            <li>
                <a href="{{ route('vendor.categories.index') }}" 
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors
                          {{ request()->routeIs('vendor.categories.*') 
                              ? 'bg-emerald-500 text-white' 
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    <i class="fas fa-tags w-5"></i>
                    <span class="ml-3">{{ __('vendor.categories') }}</span>
                </a>
            </li>

            <!-- Orders -->
            <li>
                <a href="{{ route('vendor.orders.index') }}" 
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors
                          {{ request()->routeIs('vendor.orders.*') 
                              ? 'bg-emerald-500 text-white' 
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    <i class="fas fa-receipt w-5"></i>
                    <span class="ml-3">{{ __('vendor.orders') }}</span>
                </a>
            </li>

            <!-- Cart -->
            <li>
                <a href="{{ route('vendor.carts.index') }}" 
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors
                          {{ request()->routeIs('vendor.carts.*') 
                              ? 'bg-emerald-500 text-white' 
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    <i class="fas fa-shopping-cart w-5"></i>
                    <span class="ml-3">{{ __('vendor.cart') }}</span>
                </a>
            </li>

            <!-- Reviews -->
            <li>
                <a href="#" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                    <i class="fas fa-star w-5"></i>
                    <span class="ml-3">{{ __('vendor.reviews') }}</span>
                </a>
            </li>

            <!-- Customers -->
            <li>
                <a href="{{ route('vendor.customers.index') }}" 
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors
                          {{ request()->routeIs('vendor.customers.*') 
                              ? 'bg-emerald-500 text-white' 
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    <i class="fas fa-user-friends w-5"></i>
                    <span class="ml-3">{{ __('vendor.customers') }}</span>
                </a>
            </li>

            <!-- Staff -->
            <li>
                <a href="{{ route('vendor.staff.index') }}" 
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors
                          {{ request()->routeIs('vendor.staff.*') 
                              ? 'bg-emerald-500 text-white' 
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    <i class="fas fa-users w-5"></i>
                    <span class="ml-3">{{ __('vendor.staff') }}</span>
                </a>
            </li>

            <li class="pt-4 mt-4 border-t border-gray-200">
                <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">{{ __('vendor.settings') }}</p>
            </li>

            <!-- Profile -->
            <li>
                <a href="{{ route('vendor.profile') }}" 
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors
                          {{ request()->routeIs('vendor.profile') 
                              ? 'bg-emerald-500 text-white' 
                              : 'text-gray-700 hover:bg-emerald-50 hover:text-emerald-600' }}">
                    <i class="fas fa-user w-5"></i>
                    <span class="ml-3">{{ __('vendor.profile') }}</span>
                </a>
            </li>

            <!-- Switch Vendor -->
            <li>
                <a href="{{ route('vendor.select') }}" 
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-purple-50 hover:text-purple-600 transition-colors">
                    <i class="fas fa-exchange-alt w-5"></i>
                    <span class="ml-3">{{ __('vendor.switch_vendor') }}</span>
                </a>
            </li>

            <!-- Create New Vendor -->
            <li>
                <a href="{{ route('vendor.create') }}" 
                   @click="if (window.innerWidth < 768) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }"
                   class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors">
                    <i class="fas fa-plus-circle w-5"></i>
                    <span class="ml-3">{{ __('vendor.create_new_vendor') }}</span>
                </a>
            </li>

            <!-- Settings -->
            <li>
                <a href="#" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                    <i class="fas fa-cog w-5"></i>
                    <span class="ml-3">{{ __('vendor.settings_menu') }}</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Logout Button -->
    <div class="p-4 border-t border-gray-200">
        <form action="{{ route('vendor.logout') }}" method="POST">
            @csrf
            <button type="submit" 
                    class="w-full flex items-center justify-center px-4 py-3 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>
                {{ __('vendor.logout') }}
            </button>
        </form>
    </div>
</aside>

<!-- Overlay for mobile -->
<div x-show="sidebarOpen" 
     @click="sidebarOpen = false; localStorage.setItem('sidebarOpen', false);"
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-black bg-opacity-50 z-20 md:hidden">
</div>