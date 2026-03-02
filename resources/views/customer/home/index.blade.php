<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Home - RentApp</title>
    
    <!-- Vite Assets (includes Tailwind CSS, Alpine.js, Font Awesome, and Poppins Font) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        .gradient-green {
            background: linear-gradient(135deg, #10b981 0%, #34d399 50%, #6ee7b7 100%);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .hover-lift {
            transition: all 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .search-focus:focus {
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #10b981;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #059669;
        }
        
        /* Product card image container */
        .product-image-container {
            position: relative;
            overflow: hidden;
            padding-bottom: 75%; /* 4:3 aspect ratio */
        }
        
        .product-image-container img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image-container img {
            transform: scale(1.1);
        }
        
        /* Category scroll container */
        .category-scroll {
            overflow-x: auto;
            scrollbar-width: thin;
            scrollbar-color: #10b981 #f1f1f1;
        }
        
        .category-scroll::-webkit-scrollbar {
            height: 6px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 min-h-screen" x-data="{ 
    mobileMenuOpen: false, 
    searchFocused: false,
    userMenuOpen: false 
}">
    
    <!-- Top Navigation Bar -->
    <nav class="glass-effect sticky top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 lg:h-20">
                
                <!-- Logo & Mobile Menu Button -->
                <div class="flex items-center space-x-4">
                    <!-- Mobile Menu Toggle -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-gray-700 focus:outline-none">
                        <i class="fas text-2xl" :class="mobileMenuOpen ? 'fa-times' : 'fa-bars'"></i>
                    </button>
                    
                    <!-- Logo -->
                    <a href="/" class="flex items-center space-x-3">
                        <div class="gradient-green w-10 h-10 lg:w-12 lg:h-12 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-box-open text-white text-xl lg:text-2xl"></i>
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-xl lg:text-2xl font-bold text-gradient">RentApp</h1>
                            <p class="text-xs text-gray-600 -mt-1">Rent. Share. Save.</p>
                        </div>
                    </a>
                </div>
                
                <!-- Search Bar (Desktop & Tablet) -->
                <div class="hidden md:flex flex-1 max-w-2xl mx-4 lg:mx-8">
                    <div class="relative w-full">
                        <input 
                            type="text" 
                            placeholder="Search for items, categories..." 
                            @focus="searchFocused = true"
                            @blur="searchFocused = false"
                            class="w-full px-5 py-3 pl-12 rounded-full border-2 border-gray-200 focus:border-green-500 focus:outline-none search-focus transition"
                        >
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" :class="{ 'text-green-500': searchFocused }"></i>
                        <button class="absolute right-2 top-1/2 transform -translate-y-1/2 gradient-green text-white px-6 py-2 rounded-full font-semibold hover:shadow-lg transition">
                            Search
                        </button>
                    </div>
                </div>
                
                <!-- Right Side Icons -->
                <div class="flex items-center space-x-2 lg:space-x-4">
                    <!-- Notifications (Hidden on mobile) -->
                    <button class="hidden lg:flex relative text-gray-700 hover:text-green-600 p-2 rounded-lg hover:bg-green-50 transition">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                    </button>
                    
                    <!-- Favorites (Hidden on mobile) -->
                    <button class="hidden lg:flex relative text-gray-700 hover:text-green-600 p-2 rounded-lg hover:bg-green-50 transition">
                        <i class="fas fa-heart text-xl"></i>
                        <span class="absolute top-1 right-1 bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">5</span>
                    </button>
                    
                    <!-- Cart -->
                    <button class="relative text-gray-700 hover:text-green-600 p-2 rounded-lg hover:bg-green-50 transition">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">2</span>
                    </button>
                    
                    <!-- User Menu -->
                    <div class="relative" @click.away="userMenuOpen = false">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2 p-2 rounded-lg hover:bg-green-50 transition">
                            <div class="w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white font-bold shadow-lg">
                                <i class="fas fa-user"></i>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-gray-600 hidden lg:block"></i>
                        </button>
                        
                        <!-- User Dropdown -->
                        <div x-show="userMenuOpen"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="absolute right-0 mt-2 w-56 rounded-2xl glass-effect shadow-2xl py-2">
                            
                            <div class="px-4 py-3 border-b border-gray-200">
                                <p class="font-semibold text-gray-800">John Doe</p>
                                <p class="text-sm text-gray-500">john@example.com</p>
                            </div>
                            
                            <a href="#" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-50 transition">
                                <i class="fas fa-user-circle text-green-600"></i>
                                <span class="text-gray-700">My Profile</span>
                            </a>
                            
                            <a href="#" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-50 transition">
                                <i class="fas fa-box text-green-600"></i>
                                <span class="text-gray-700">My Rentals</span>
                            </a>
                            
                            <a href="#" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-50 transition">
                                <i class="fas fa-heart text-green-600"></i>
                                <span class="text-gray-700">Favorites</span>
                            </a>
                            
                            <a href="#" class="flex items-center space-x-3 px-4 py-3 hover:bg-green-50 transition">
                                <i class="fas fa-cog text-green-600"></i>
                                <span class="text-gray-700">Settings</span>
                            </a>
                            
                            <div class="border-t border-gray-200 mt-2"></div>
                            
                            <a href="#" class="flex items-center space-x-3 px-4 py-3 hover:bg-red-50 transition text-red-600">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Search Bar -->
            <div class="md:hidden pb-4">
                <div class="relative">
                    <input 
                        type="text" 
                        placeholder="Search items..." 
                        class="w-full px-4 py-2.5 pl-10 rounded-full border-2 border-gray-200 focus:border-green-500 focus:outline-none search-focus transition text-sm"
                    >
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                    <button class="absolute right-2 top-1/2 transform -translate-y-1/2 gradient-green text-white px-4 py-1.5 rounded-full text-sm font-semibold">
                        Go
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Side Menu -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         @click="mobileMenuOpen = false"
         class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"></div>
    
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         class="fixed top-0 left-0 h-full w-80 glass-effect z-50 lg:hidden shadow-2xl overflow-y-auto">
        
        <div class="p-6">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gradient">Menu</h2>
                <button @click="mobileMenuOpen = false" class="text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <nav class="space-y-2">
                <a href="#" class="flex items-center space-x-3 p-3 rounded-xl bg-green-50 text-green-700 font-medium">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-green-50 text-gray-700">
                    <i class="fas fa-th-large"></i>
                    <span>Categories</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-green-50 text-gray-700">
                    <i class="fas fa-box"></i>
                    <span>My Rentals</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-green-50 text-gray-700 relative">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">3</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-green-50 text-gray-700 relative">
                    <i class="fas fa-heart"></i>
                    <span>Favorites</span>
                    <span class="ml-auto bg-green-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">5</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-green-50 text-gray-700">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="#" class="flex items-center space-x-3 p-3 rounded-xl hover:bg-green-50 text-gray-700">
                    <i class="fas fa-question-circle"></i>
                    <span>Help & Support</span>
                </a>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
        
        <!-- Welcome Banner -->
        <div class="gradient-green rounded-2xl lg:rounded-3xl p-6 lg:p-8 mb-6 lg:mb-8 shadow-xl relative overflow-hidden">
            <div class="relative z-10">
                <h2 class="text-2xl lg:text-3xl font-bold text-white mb-2">Welcome back, John! 👋</h2>
                <p class="text-green-50 text-sm lg:text-base mb-4 lg:mb-6">Discover amazing items available for rent near you</p>
                <button class="bg-white text-green-600 px-6 py-2.5 lg:px-8 lg:py-3 rounded-full font-bold hover:shadow-lg transition text-sm lg:text-base">
                    <i class="fas fa-plus-circle mr-2"></i>Post Your Item
                </button>
            </div>
            <div class="absolute right-0 top-0 w-48 h-48 bg-white opacity-10 rounded-full -mr-24 -mt-24"></div>
            <div class="absolute right-20 bottom-0 w-32 h-32 bg-white opacity-10 rounded-full -mb-16"></div>
        </div>
        
        <!-- Categories Section -->
        <section class="mb-6 lg:mb-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl lg:text-2xl font-bold text-gray-800">Browse Categories</h3>
                <a href="#" class="text-green-600 hover:text-green-700 font-semibold text-sm lg:text-base">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            <div class="category-scroll flex gap-3 lg:gap-4 pb-2 overflow-x-auto">
                <!-- Category Cards -->
                <div class="flex-shrink-0 glass-effect rounded-2xl p-4 lg:p-6 hover-lift cursor-pointer text-center min-w-[120px] lg:min-w-[140px]">
                    <div class="bg-gradient-to-br from-blue-400 to-blue-600 w-12 h-12 lg:w-16 lg:h-16 rounded-xl lg:rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <i class="fas fa-laptop text-white text-xl lg:text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-1">Electronics</h4>
                    <p class="text-xs lg:text-sm text-gray-500">250+ items</p>
                </div>
                
                <div class="flex-shrink-0 glass-effect rounded-2xl p-4 lg:p-6 hover-lift cursor-pointer text-center min-w-[120px] lg:min-w-[140px]">
                    <div class="bg-gradient-to-br from-purple-400 to-purple-600 w-12 h-12 lg:w-16 lg:h-16 rounded-xl lg:rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <i class="fas fa-camera text-white text-xl lg:text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-1">Cameras</h4>
                    <p class="text-xs lg:text-sm text-gray-500">180+ items</p>
                </div>
                
                <div class="flex-shrink-0 glass-effect rounded-2xl p-4 lg:p-6 hover-lift cursor-pointer text-center min-w-[120px] lg:min-w-[140px]">
                    <div class="bg-gradient-to-br from-orange-400 to-orange-600 w-12 h-12 lg:w-16 lg:h-16 rounded-xl lg:rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <i class="fas fa-tools text-white text-xl lg:text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-1">Tools</h4>
                    <p class="text-xs lg:text-sm text-gray-500">320+ items</p>
                </div>
                
                <div class="flex-shrink-0 glass-effect rounded-2xl p-4 lg:p-6 hover-lift cursor-pointer text-center min-w-[120px] lg:min-w-[140px]">
                    <div class="bg-gradient-to-br from-pink-400 to-pink-600 w-12 h-12 lg:w-16 lg:h-16 rounded-xl lg:rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <i class="fas fa-gamepad text-white text-xl lg:text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-1">Gaming</h4>
                    <p class="text-xs lg:text-sm text-gray-500">150+ items</p>
                </div>
                
                <div class="flex-shrink-0 glass-effect rounded-2xl p-4 lg:p-6 hover-lift cursor-pointer text-center min-w-[120px] lg:min-w-[140px]">
                    <div class="bg-gradient-to-br from-red-400 to-red-600 w-12 h-12 lg:w-16 lg:h-16 rounded-xl lg:rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <i class="fas fa-bicycle text-white text-xl lg:text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-1">Sports</h4>
                    <p class="text-xs lg:text-sm text-gray-500">200+ items</p>
                </div>
                
                <div class="flex-shrink-0 glass-effect rounded-2xl p-4 lg:p-6 hover-lift cursor-pointer text-center min-w-[120px] lg:min-w-[140px]">
                    <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 w-12 h-12 lg:w-16 lg:h-16 rounded-xl lg:rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <i class="fas fa-birthday-cake text-white text-xl lg:text-2xl"></i>
                    </div>
                    <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-1">Party</h4>
                    <p class="text-xs lg:text-sm text-gray-500">175+ items</p>
                </div>
            </div>
        </section>
        
        <!-- Featured Items -->
        <section class="mb-6 lg:mb-8">
            <div class="flex justify-between items-center mb-4 lg:mb-6">
                <h3 class="text-xl lg:text-2xl font-bold text-gray-800">Featured Items</h3>
                <a href="#" class="text-green-600 hover:text-green-700 font-semibold text-sm lg:text-base">
                    See All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 lg:gap-6">
                <!-- Product Card 1 -->
                <div class="glass-effect rounded-2xl overflow-hidden hover-lift cursor-pointer product-card">
                    <div class="product-image-container">
                        <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=300&fit=crop" alt="Product">
                        <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                            Featured
                        </div>
                        <button class="absolute top-2 left-2 bg-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg hover:bg-red-50 transition">
                            <i class="far fa-heart text-gray-600 hover:text-red-500"></i>
                        </button>
                    </div>
                    <div class="p-3 lg:p-4">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-gray-800 text-sm lg:text-base line-clamp-2">Premium Headphones</h4>
                        </div>
                        <div class="flex items-center text-yellow-500 text-xs lg:text-sm mb-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span class="text-gray-500 ml-1">(4.5)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 font-bold text-base lg:text-lg">₹199<span class="text-xs lg:text-sm text-gray-500">/day</span></p>
                            </div>
                            <button class="gradient-green text-white p-2 rounded-lg hover:shadow-lg transition">
                                <i class="fas fa-shopping-cart text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Product Card 2 -->
                <div class="glass-effect rounded-2xl overflow-hidden hover-lift cursor-pointer product-card">
                    <div class="product-image-container">
                        <img src="https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=400&h=300&fit=crop" alt="Product">
                        <button class="absolute top-2 left-2 bg-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg hover:bg-red-50 transition">
                            <i class="far fa-heart text-gray-600 hover:text-red-500"></i>
                        </button>
                    </div>
                    <div class="p-3 lg:p-4">
                        <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-2 line-clamp-2">DSLR Camera Kit</h4>
                        <div class="flex items-center text-yellow-500 text-xs lg:text-sm mb-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <span class="text-gray-500 ml-1">(5.0)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 font-bold text-base lg:text-lg">₹899<span class="text-xs lg:text-sm text-gray-500">/day</span></p>
                            </div>
                            <button class="gradient-green text-white p-2 rounded-lg hover:shadow-lg transition">
                                <i class="fas fa-shopping-cart text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Product Card 3 -->
                <div class="glass-effect rounded-2xl overflow-hidden hover-lift cursor-pointer product-card">
                    <div class="product-image-container">
                        <img src="https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=400&h=300&fit=crop" alt="Product">
                        <div class="absolute top-2 right-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                            Hot
                        </div>
                        <button class="absolute top-2 left-2 bg-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg hover:bg-red-50 transition">
                            <i class="fas fa-heart text-red-500"></i>
                        </button>
                    </div>
                    <div class="p-3 lg:p-4">
                        <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-2 line-clamp-2">Gaming Console</h4>
                        <div class="flex items-center text-yellow-500 text-xs lg:text-sm mb-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            <span class="text-gray-500 ml-1">(4.0)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 font-bold text-base lg:text-lg">₹599<span class="text-xs lg:text-sm text-gray-500">/day</span></p>
                            </div>
                            <button class="gradient-green text-white p-2 rounded-lg hover:shadow-lg transition">
                                <i class="fas fa-shopping-cart text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Product Card 4 -->
                <div class="glass-effect rounded-2xl overflow-hidden hover-lift cursor-pointer product-card">
                    <div class="product-image-container">
                        <img src="https://images.unsplash.com/photo-1485965120184-e220f721d03e?w=400&h=300&fit=crop" alt="Product">
                        <button class="absolute top-2 left-2 bg-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg hover:bg-red-50 transition">
                            <i class="far fa-heart text-gray-600 hover:text-red-500"></i>
                        </button>
                    </div>
                    <div class="p-3 lg:p-4">
                        <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-2 line-clamp-2">Mountain Bike</h4>
                        <div class="flex items-center text-yellow-500 text-xs lg:text-sm mb-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span class="text-gray-500 ml-1">(4.7)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 font-bold text-base lg:text-lg">₹299<span class="text-xs lg:text-sm text-gray-500">/day</span></p>
                            </div>
                            <button class="gradient-green text-white p-2 rounded-lg hover:shadow-lg transition">
                                <i class="fas fa-shopping-cart text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Product Card 5 -->
                <div class="glass-effect rounded-2xl overflow-hidden hover-lift cursor-pointer product-card">
                    <div class="product-image-container">
                        <img src="https://images.unsplash.com/photo-1484788984921-03950022c9ef?w=400&h=300&fit=crop" alt="Product">
                        <button class="absolute top-2 left-2 bg-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg hover:bg-red-50 transition">
                            <i class="far fa-heart text-gray-600 hover:text-red-500"></i>
                        </button>
                    </div>
                    <div class="p-3 lg:p-4">
                        <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-2 line-clamp-2">MacBook Pro</h4>
                        <div class="flex items-center text-yellow-500 text-xs lg:text-sm mb-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <span class="text-gray-500 ml-1">(5.0)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 font-bold text-base lg:text-lg">₹1,299<span class="text-xs lg:text-sm text-gray-500">/day</span></p>
                            </div>
                            <button class="gradient-green text-white p-2 rounded-lg hover:shadow-lg transition">
                                <i class="fas fa-shopping-cart text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Product Card 6 -->
                <div class="glass-effect rounded-2xl overflow-hidden hover-lift cursor-pointer product-card">
                    <div class="product-image-container">
                        <img src="https://images.unsplash.com/photo-1581235720704-06d3acfcb36f?w=400&h=300&fit=crop" alt="Product">
                        <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                            New
                        </div>
                        <button class="absolute top-2 left-2 bg-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg hover:bg-red-50 transition">
                            <i class="far fa-heart text-gray-600 hover:text-red-500"></i>
                        </button>
                    </div>
                    <div class="p-3 lg:p-4">
                        <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-2 line-clamp-2">Drone with Camera</h4>
                        <div class="flex items-center text-yellow-500 text-xs lg:text-sm mb-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span class="text-gray-500 ml-1">(4.6)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 font-bold text-base lg:text-lg">₹799<span class="text-xs lg:text-sm text-gray-500">/day</span></p>
                            </div>
                            <button class="gradient-green text-white p-2 rounded-lg hover:shadow-lg transition">
                                <i class="fas fa-shopping-cart text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Product Card 7 -->
                <div class="glass-effect rounded-2xl overflow-hidden hover-lift cursor-pointer product-card">
                    <div class="product-image-container">
                        <img src="https://images.unsplash.com/photo-1583394838336-acd977736f90?w=400&h=300&fit=crop" alt="Product">
                        <button class="absolute top-2 left-2 bg-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg hover:bg-red-50 transition">
                            <i class="far fa-heart text-gray-600 hover:text-red-500"></i>
                        </button>
                    </div>
                    <div class="p-3 lg:p-4">
                        <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-2 line-clamp-2">Projector HD</h4>
                        <div class="flex items-center text-yellow-500 text-xs lg:text-sm mb-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            <span class="text-gray-500 ml-1">(4.2)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 font-bold text-base lg:text-lg">₹499<span class="text-xs lg:text-sm text-gray-500">/day</span></p>
                            </div>
                            <button class="gradient-green text-white p-2 rounded-lg hover:shadow-lg transition">
                                <i class="fas fa-shopping-cart text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Product Card 8 -->
                <div class="glass-effect rounded-2xl overflow-hidden hover-lift cursor-pointer product-card">
                    <div class="product-image-container">
                        <img src="https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=400&h=300&fit=crop" alt="Product">
                        <button class="absolute top-2 left-2 bg-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg hover:bg-red-50 transition">
                            <i class="far fa-heart text-gray-600 hover:text-red-500"></i>
                        </button>
                    </div>
                    <div class="p-3 lg:p-4">
                        <h4 class="font-semibold text-gray-800 text-sm lg:text-base mb-2 line-clamp-2">Electric Guitar</h4>
                        <div class="flex items-center text-yellow-500 text-xs lg:text-sm mb-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                            <span class="text-gray-500 ml-1">(4.8)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 font-bold text-base lg:text-lg">₹399<span class="text-xs lg:text-sm text-gray-500">/day</span></p>
                            </div>
                            <button class="gradient-green text-white p-2 rounded-lg hover:shadow-lg transition">
                                <i class="fas fa-shopping-cart text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Promotional Banner -->
        <section class="mb-6 lg:mb-8">
            <div class="glass-effect rounded-2xl lg:rounded-3xl p-6 lg:p-8 grid md:grid-cols-2 gap-6 items-center">
                <div>
                    <div class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold mb-4">
                        <i class="fas fa-gift mr-1"></i> Limited Offer
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-3">Get 20% Off on Your First Rental</h3>
                    <p class="text-gray-600 mb-6">Use code <span class="font-bold text-green-600">FIRST20</span> at checkout</p>
                    <button class="gradient-green text-white px-6 py-3 rounded-full font-bold hover:shadow-lg transition">
                        Browse Items <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
                <div class="hidden md:flex justify-center">
                    <img src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=400&h=300&fit=crop" alt="Promotion" class="rounded-2xl shadow-lg">
                </div>
            </div>
        </section>
        
    </main>
    
    <!-- Bottom Navigation (Mobile Only) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 glass-effect border-t border-gray-200 z-40">
        <div class="flex justify-around items-center h-16">
            <a href="#" class="flex flex-col items-center text-green-600">
                <i class="fas fa-home text-xl mb-1"></i>
                <span class="text-xs font-medium">Home</span>
            </a>
            <a href="#" class="flex flex-col items-center text-gray-600 hover:text-green-600">
                <i class="fas fa-search text-xl mb-1"></i>
                <span class="text-xs font-medium">Search</span>
            </a>
            <a href="#" class="flex flex-col items-center text-gray-600 hover:text-green-600 relative">
                <i class="fas fa-bell text-xl mb-1"></i>
                <span class="text-xs font-medium">Alerts</span>
                <span class="absolute top-0 right-3 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">3</span>
            </a>
            <a href="#" class="flex flex-col items-center text-gray-600 hover:text-green-600">
                <i class="fas fa-user text-xl mb-1"></i>
                <span class="text-xs font-medium">Profile</span>
            </a>
        </div>
    </nav>
    
</body>
</html>
