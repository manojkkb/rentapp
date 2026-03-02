<!-- Mobile Bottom Navigation (App-style) -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50 shadow-lg">
    <div class="flex items-center justify-around h-16">
        
        <!-- Home -->
        <a href="{{ route('vendor.home') }}" 
           class="flex flex-col items-center justify-center flex-1 h-full transition-colors
                  {{ request()->routeIs('vendor.home') 
                      ? 'text-emerald-600' 
                      : 'text-gray-600 hover:text-emerald-600' }}">
            <i class="fas fa-home text-xl mb-1"></i>
            <span class="text-xs font-medium">Home</span>
        </a>

        <!-- Orders -->
        <a href="{{ route('vendor.orders.index') }}" 
           class="flex flex-col items-center justify-center flex-1 h-full transition-colors
                  {{ request()->routeIs('vendor.orders.*') 
                      ? 'text-emerald-600' 
                      : 'text-gray-600 hover:text-emerald-600' }} relative">
            <i class="fas fa-receipt text-xl mb-1"></i>
            <span class="text-xs font-medium">Orders</span>
        </a>

        <!-- Cart -->
        <a href="{{ route('vendor.carts.index') }}" 
           class="flex flex-col items-center justify-center flex-1 h-full transition-colors
                  {{ request()->routeIs('vendor.carts.*') 
                      ? 'text-blue-600' 
                      : 'text-gray-600 hover:text-blue-600' }}">
            <i class="fas fa-shopping-cart text-xl mb-1"></i>
            <span class="text-xs font-medium">Cart</span>
        </a>

        <!-- Items -->
        <a href="{{ route('vendor.items.index') }}" 
           class="flex flex-col items-center justify-center flex-1 h-full transition-colors
                  {{ request()->routeIs('vendor.items.*') 
                      ? 'text-emerald-600' 
                      : 'text-gray-600 hover:text-emerald-600' }}">
            <i class="fas fa-box text-xl mb-1"></i>
            <span class="text-xs font-medium">Items</span>
        </a>

        <!-- Menu (Opens Sidebar) -->
        <button @click="toggleSidebar()" 
                class="flex flex-col items-center justify-center flex-1 h-full transition-colors text-gray-600 hover:text-emerald-600">
            <i class="fas fa-bars text-xl mb-1"></i>
            <span class="text-xs font-medium">Menu</span>
        </button>
        
    </div>
</nav>
