@extends('layouts.guest')

@section('title', 'Rentkia - Rent Anything, Anytime')

@section('content')
    <!-- Hero Section -->
    <section id="home" class="relative min-h-screen flex items-center justify-center px-4 pt-20 overflow-hidden">
        <!-- Animated Blobs -->
        <div class="blob bg-green-300 w-96 h-96 top-20 -left-20"></div>
        <div class="blob bg-emerald-300 w-96 h-96 bottom-20 -right-20" style="animation-delay: 2s;"></div>
        
        <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-12 items-center relative z-10 fade-in">
            <!-- Hero Content -->
            <div class="text-center md:text-left">
                <div class="inline-block bg-green-100 text-green-700 px-4 py-2 rounded-full text-sm font-semibold mb-6">
                    <i class="fas fa-sparkles mr-2"></i>Welcome to the Future of Renting
                </div>
                
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-800 mb-6 leading-tight">
                    Rent Anything,
                    <span class="text-gradient">Anytime</span>
                </h1>
                
                <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                    Discover a smarter way to access what you need. From tools to electronics, sports equipment to party supplies – rent it all with ease.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                    <button class="gradient-green text-white px-8 py-4 rounded-full font-bold text-lg hover:shadow-2xl transition transform hover:-translate-y-1 flex items-center justify-center space-x-2">
                        <span>Get Started</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    
                    <button class="bg-white text-green-600 px-8 py-4 rounded-full font-bold text-lg border-2 border-green-600 hover:bg-green-50 transition flex items-center justify-center space-x-2">
                        <i class="fas fa-play-circle"></i>
                        <span>Watch Demo</span>
                    </button>
                </div>
                
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-6 mt-12">
                    <div class="text-center md:text-left">
                        <h3 class="text-3xl font-bold text-green-600">{{ number_format($totalItems) }}+</h3>
                        <p class="text-gray-600 text-sm">Items Listed</p>
                    </div>
                    <div class="text-center md:text-left">
                        <h3 class="text-3xl font-bold text-green-600">{{ number_format($totalVendors) }}+</h3>
                        <p class="text-gray-600 text-sm">Vendors</p>
                    </div>
                    <div class="text-center md:text-left">
                        <h3 class="text-3xl font-bold text-green-600">{{ number_format($totalCategories) }}+</h3>
                        <p class="text-gray-600 text-sm">Categories</p>
                    </div>
                </div>
            </div>
            
            <!-- Hero Image -->
            <div class="relative">
                <div class="floating">
                    <div class="glass-effect p-8 rounded-3xl shadow-2xl">
                        <img src="https://images.unsplash.com/photo-1573164713714-d95e436ab8d6?w=600&h=600&fit=crop" alt="Happy customer" class="rounded-2xl w-full h-auto shadow-lg">
                    </div>
                </div>
                
                <!-- Floating Cards -->
                <div class="absolute -top-10 -left-10 glass-effect p-4 rounded-2xl shadow-xl hidden lg:block">
                    <div class="flex items-center space-x-3">
                        <div class="bg-green-500 w-12 h-12 rounded-full flex items-center justify-center">
                            <i class="fas fa-check text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">{{ number_format($totalVendors) }}+ Verified</p>
                            <p class="text-sm text-gray-600">Trusted Vendors</p>
                        </div>
                    </div>
                </div>
                
                <div class="absolute -bottom-10 -right-10 glass-effect p-4 rounded-2xl shadow-xl hidden lg:block">
                    <div class="flex items-center space-x-3">
                        <div class="bg-yellow-500 w-12 h-12 rounded-full flex items-center justify-center">
                            <i class="fas fa-star text-white text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">{{ number_format($totalItems) }}+ Items</p>
                            <p class="text-sm text-gray-600">Ready to Rent</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
            <i class="fas fa-chevron-down text-green-600 text-2xl"></i>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="features" class="py-20 px-4 relative">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Why Choose <span class="text-gradient">Rentkia?</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Experience a seamless rental platform with features designed for your convenience
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="glass-effect p-8 rounded-3xl hover-scale text-center">
                    <div class="gradient-green w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <i class="fas fa-shield-alt text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Secure & Safe</h3>
                    <p class="text-gray-600">All transactions are encrypted and vendor-verified for your safety</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="glass-effect p-8 rounded-3xl hover-scale text-center">
                    <div class="gradient-green w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <i class="fas fa-bolt text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Instant Booking</h3>
                    <p class="text-gray-600">Book items instantly with our fast and easy booking process</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="glass-effect p-8 rounded-3xl hover-scale text-center">
                    <div class="gradient-green w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <i class="fas fa-hand-holding-usd text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Best Prices</h3>
                    <p class="text-gray-600">Get competitive rental rates with transparent pricing</p>
                </div>
                
                <!-- Feature 4 -->
                <div class="glass-effect p-8 rounded-3xl hover-scale text-center">
                    <div class="gradient-green w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <i class="fas fa-headset text-white text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">24/7 Support</h3>
                    <p class="text-gray-600">Round-the-clock customer support for any assistance</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 px-4 bg-white relative">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    How It <span class="text-gradient">Works?</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Start renting in 3 simple steps
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-12">
                <!-- Step 1 -->
                <div class="text-center relative">
                    <div class="gradient-green-light w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-5xl font-bold text-green-700">1</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Browse Items</h3>
                    <p class="text-gray-600 text-lg">Search through thousands of rental items in various categories</p>
                    
                    <!-- Connector Line -->
                    <div class="hidden md:block absolute top-12 left-2/3 w-1/3 h-1 bg-green-300"></div>
                </div>
                
                <!-- Step 2 -->
                <div class="text-center relative">
                    <div class="gradient-green-light w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-5xl font-bold text-green-700">2</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Book & Pay</h3>
                    <p class="text-gray-600 text-lg">Select your rental period and make secure payment</p>
                    
                    <!-- Connector Line -->
                    <div class="hidden md:block absolute top-12 left-2/3 w-1/3 h-1 bg-green-300"></div>
                </div>
                
                <!-- Step 3 -->
                <div class="text-center">
                    <div class="gradient-green-light w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-5xl font-bold text-green-700">3</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Enjoy & Return</h3>
                    <p class="text-gray-600 text-lg">Use the item and return it on time. It's that simple!</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Featured Items Section -->
    <section class="py-20 px-4 bg-white relative">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Featured <span class="text-gradient">Items</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Browse our latest and most popular rental items
                </p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($featuredItems as $item)
                    <div class="glass-effect rounded-2xl overflow-hidden hover-scale cursor-pointer group">
                        <!-- Item Image -->
                        <div class="relative h-48 bg-gradient-to-br from-green-100 to-emerald-100 overflow-hidden">
                            @if($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-box text-6xl text-green-300"></i>
                                </div>
                            @endif
                            
                            <!-- Availability Badge -->
                            @if($item->is_available)
                                <div class="absolute top-3 right-3 bg-green-500 text-white text-xs font-semibold px-3 py-1 rounded-full">
                                    Available
                                </div>
                            @else
                                <div class="absolute top-3 right-3 bg-red-500 text-white text-xs font-semibold px-3 py-1 rounded-full">
                                    Rented
                                </div>
                            @endif
                        </div>
                        
                        <!-- Item Details -->
                        <div class="p-5">
                            <!-- Category -->
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">
                                    {{ $item->category->name ?? 'Uncategorized' }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-store mr-1"></i>{{ $item->vendor->vendor_name ?? 'N/A' }}
                                </span>
                            </div>
                            
                            <!-- Item Name -->
                            <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">{{ $item->name }}</h3>
                            
                            <!-- Item Description -->
                            @if($item->description)
                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $item->description }}</p>
                            @endif
                            
                            <!-- Price and Stock -->
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-500">Price</p>
                                    <p class="text-xl font-bold text-green-600">₹{{ number_format($item->price, 2) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Stock</p>
                                    <p class="text-sm font-semibold text-gray-900">{{ $item->stock }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-12">
                        <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center bg-gray-100 rounded-full">
                            <i class="fas fa-box-open text-3xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500 text-lg">No items available yet</p>
                        <p class="text-gray-400 text-sm mt-2">Check back later for amazing rental items!</p>
                    </div>
                @endforelse
            </div>
            
            @if($featuredItems->count() > 0)
                <div class="text-center mt-12">
                    <button class="gradient-green text-white px-8 py-4 rounded-full font-bold text-lg hover:shadow-2xl transition transform hover:-translate-y-1">
                        <span>View All Items</span>
                        <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            @endif
        </div>
    </section>
    
    <!-- Categories Section -->
    <section id="categories" class="py-20 px-4 relative">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Popular <span class="text-gradient">Categories</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Explore our wide range of rental categories
                </p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                @php
                    $colors = [
                        ['bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
                        ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
                        ['bg' => 'bg-orange-100', 'text' => 'text-orange-600'],
                        ['bg' => 'bg-pink-100', 'text' => 'text-pink-600'],
                        ['bg' => 'bg-red-100', 'text' => 'text-red-600'],
                        ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600'],
                        ['bg' => 'bg-green-100', 'text' => 'text-green-600'],
                        ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600'],
                    ];
                @endphp
                
                @forelse($categories as $index => $category)
                    @php
                        $colorSet = $colors[$index % count($colors)];
                    @endphp
                    <div class="glass-effect p-6 rounded-2xl hover-scale text-center cursor-pointer">
                        <div class="{{ $colorSet['bg'] }} w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4">
                            <i class="{{ $category->icon ?? 'fas fa-box' }} {{ $colorSet['text'] }} text-2xl"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800">{{ $category->name }}</h4>
                        <p class="text-sm text-gray-500 mt-1">{{ $category->items_count }}+ Items</p>
                    </div>
                @empty
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">No categories available yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="py-20 px-4 relative overflow-hidden">
        <div class="gradient-green rounded-3xl max-w-6xl mx-auto p-12 md:p-16 relative">
            <div class="relative z-10 text-center text-white">
                <h2 class="text-4xl md:text-5xl font-bold mb-6">Ready to Start Renting?</h2>
                <p class="text-xl mb-8 opacity-90">Join {{ number_format($totalVendors) }}+ vendors and thousands of happy customers on our platform</p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#" class="bg-white text-green-600 px-8 py-4 rounded-full font-bold text-lg hover:bg-gray-100 transition transform hover:-translate-y-1 shadow-xl inline-flex items-center justify-center">
                        Browse {{ number_format($totalItems) }}+ Items
                    </a>
                    <a href="{{ route('vendor.login') }}" class="bg-transparent text-white px-8 py-4 rounded-full font-bold text-lg border-2 border-white hover:bg-white hover:text-green-600 transition inline-flex items-center justify-center">
                        Become a Vendor
                    </a>
                </div>
            </div>
            
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full -mr-32 -mt-32"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-white opacity-10 rounded-full -ml-32 -mb-32"></div>
        </div>
    </section>
@endsection