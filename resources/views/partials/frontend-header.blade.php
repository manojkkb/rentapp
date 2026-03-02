<!-- Navigation Header -->
<nav x-data="{ mobileMenuOpen: false, loginDropdown: false }" class="fixed top-0 w-full z-50 glass-effect shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            
            <!-- Mobile Menu Button (Left Side) -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-700 focus:outline-none">
                <i class="fas fa-bars text-2xl" x-show="!mobileMenuOpen"></i>
                <i class="fas fa-times text-2xl" x-show="mobileMenuOpen"></i>
            </button>
            
            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <a href="{{ route('welcome') }}" class="flex items-center space-x-3">
                    <div class="gradient-green w-12 h-12 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-box-open text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-gradient">RentApp</h1>
                        <p class="text-xs text-gray-600">Rent. Share. Save.</p>
                    </div>
                </a>
            </div>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="#home" class="text-gray-700 hover:text-green-600 font-medium transition">Home</a>
                <a href="#features" class="text-gray-700 hover:text-green-600 font-medium transition">Features</a>
                <a href="#how-it-works" class="text-gray-700 hover:text-green-600 font-medium transition">How It Works</a>
                <a href="#categories" class="text-gray-700 hover:text-green-600 font-medium transition">Categories</a>
                <a href="#contact" class="text-gray-700 hover:text-green-600 font-medium transition">Contact</a>
                
                <!-- Login Dropdown -->
                <div class="relative" @click.away="loginDropdown = false">
                    <button @click="loginDropdown = !loginDropdown" class="gradient-green text-white px-6 py-2.5 rounded-full font-semibold hover:shadow-lg transition flex items-center space-x-2">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login</span>
                        <i class="fas fa-chevron-down text-xs" :class="{ 'rotate-180': loginDropdown }"></i>
                    </button>
                    
                    <div x-show="loginDropdown" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="absolute right-0 mt-2 w-56 rounded-2xl glass-effect shadow-xl py-2">
                        
                        <a href="{{ route('vendor.login') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-50 transition">
                            <div class="bg-green-100 w-10 h-10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-store text-green-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Vendor Login</p>
                                <p class="text-xs text-gray-500">Manage your store</p>
                            </div>
                        </a>
                        
                        <a href="{{ route('admin.login') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-50 transition">
                            <div class="bg-blue-100 w-10 h-10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-shield text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Admin Login</p>
                                <p class="text-xs text-gray-500">Platform management</p>
                            </div>
                        </a>
                        
                        <div class="border-t border-gray-200 my-2"></div>
                        
                        <a href="{{ route('customer.home') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-50 transition">
                            <div class="bg-purple-100 w-10 h-10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Customer Login</p>
                                <p class="text-xs text-gray-500">Browse & rent items</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Login Button (Right Side) -->
            <div class="md:hidden relative" @click.away="loginDropdown = false">
                <button @click="loginDropdown = !loginDropdown" class="gradient-green text-white px-4 py-2 rounded-full font-semibold hover:shadow-lg transition flex items-center space-x-2">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                    <i class="fas fa-chevron-down text-xs" :class="{ 'rotate-180': loginDropdown }"></i>
                </button>
                
                <div x-show="loginDropdown" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="absolute right-0 mt-2 w-56 rounded-2xl glass-effect shadow-xl py-2">
                    
                    <a href="{{ route('vendor.login') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-50 transition">
                        <div class="bg-green-100 w-10 h-10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-store text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Vendor Login</p>
                            <p class="text-xs text-gray-500">Manage your store</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.login') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-50 transition">
                        <div class="bg-blue-100 w-10 h-10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-shield text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Admin Login</p>
                            <p class="text-xs text-gray-500">Platform management</p>
                        </div>
                    </a>
                    
                    <div class="border-t border-gray-200 my-2"></div>
                    
                    <a href="{{ route('customer.home') }}" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-50 transition">
                        <div class="bg-purple-100 w-10 h-10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">Customer Login</p>
                            <p class="text-xs text-gray-500">Browse & rent items</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-4"
         class="md:hidden glass-effect border-t">
        <div class="px-4 py-4 space-y-3">
            <a href="#home" class="block text-gray-700 hover:text-green-600 font-medium py-2">Home</a>
            <a href="#features" class="block text-gray-700 hover:text-green-600 font-medium py-2">Features</a>
            <a href="#how-it-works" class="block text-gray-700 hover:text-green-600 font-medium py-2">How It Works</a>
            <a href="#categories" class="block text-gray-700 hover:text-green-600 font-medium py-2">Categories</a>
            <a href="#contact" class="block text-gray-700 hover:text-green-600 font-medium py-2">Contact</a>
        </div>
    </div>
</nav>
