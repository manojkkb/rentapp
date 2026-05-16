<!-- Navigation Header -->
<nav x-data="{ mobileMenuOpen: false }" class="fixed top-0 w-full z-50 glass-effect shadow-lg">
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
                        <h1 class="text-xl md:text-2xl font-bold text-gradient">Rentkia</h1>
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

                <a href="{{ route('vendor.login') }}"
                   class="gradient-green text-white px-6 py-2.5 rounded-full font-semibold hover:shadow-lg transition inline-flex items-center space-x-2">
                    <i class="fas fa-store"></i>
                    <span>Vendor login</span>
                </a>
            </div>

            <!-- Mobile: Vendor login (right) -->
            <a href="{{ route('vendor.login') }}"
               class="md:hidden gradient-green text-white px-4 py-2 rounded-full font-semibold hover:shadow-lg transition inline-flex items-center space-x-2">
                <i class="fas fa-store"></i>
                <span>Vendor login</span>
            </a>
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
            <a href="{{ route('vendor.login') }}"
               class="mt-2 flex items-center justify-center gap-2 rounded-full bg-emerald-600 px-4 py-3 text-center font-semibold text-white hover:bg-emerald-700">
                <i class="fas fa-store"></i>
                <span>Vendor login</span>
            </a>
        </div>
    </div>
</nav>
