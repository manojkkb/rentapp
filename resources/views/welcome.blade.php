@extends('layouts.guest')

@section('content')
<main id="main-content">

    {{-- ═══════════════════ HERO ═══════════════════ --}}
    <section id="home" class="relative min-h-screen flex items-center hero-mesh grid-pattern overflow-hidden">
        <div class="blob bg-emerald-500 w-[500px] h-[500px] -top-32 -left-32"></div>
        <div class="blob bg-teal-400 w-[400px] h-[400px] top-1/2 -right-32" style="animation-delay: 3s;"></div>
        <div class="blob bg-green-600 w-[300px] h-[300px] bottom-0 left-1/3" style="animation-delay: 5s;"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20 lg:pt-32 lg:pb-28 w-full">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

                <div class="text-center lg:text-left">
                    <div class="fade-up inline-flex items-center gap-2 px-4 py-2 rounded-full glass-dark text-emerald-300 text-sm font-medium mb-8 border border-emerald-500/20">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                        </span>
                        India's #1 Rental Marketplace
                    </div>

                    <h1 class="fade-up fade-up-delay-1 text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-extrabold text-white leading-[1.08] tracking-tight mb-6">
                        <span class="block text-emerald-400 text-2xl sm:text-3xl lg:text-4xl font-bold mb-3 tracking-tight">Rentkia</span>
                        Rent what you need.
                        <span class="block text-gradient mt-1">Own the moment.</span>
                    </h1>

                    <p class="fade-up fade-up-delay-2 text-lg sm:text-xl text-slate-300 leading-relaxed max-w-xl mx-auto lg:mx-0 mb-10">
                        From DSLR cameras to power tools, party tents to gaming consoles — access premium gear from verified local vendors, without the full price tag.
                    </p>

                    {{-- Search --}}
                    <form action="{{ route('stores.index') }}" method="GET" class="fade-up fade-up-delay-3 max-w-lg mx-auto lg:mx-0 mb-8" role="search" aria-label="Search rental stores">
                        <div class="flex items-center gap-2 p-2 rounded-2xl glass-dark border border-white/10 shadow-2xl shadow-black/20">
                            <label for="homeSearch" class="sr-only">Search rentals</label>
                            <div class="flex-1 flex items-center gap-3 px-4 py-3">
                                <i class="fas fa-search text-emerald-400" aria-hidden="true"></i>
                                <input type="search"
                                       id="homeSearch"
                                       name="q"
                                       placeholder="Search cameras, tools, bikes…"
                                       autocomplete="off"
                                       class="w-full bg-transparent text-sm sm:text-base text-white placeholder:text-slate-400 focus:outline-none">
                            </div>
                            <button type="submit" class="gradient-green px-5 sm:px-7 py-3 rounded-xl text-white font-semibold text-sm sm:text-base shine whitespace-nowrap">
                                Explore
                            </button>
                        </div>
                    </form>

                    <div class="fade-up fade-up-delay-3 flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('stores.index') }}"
                           class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl text-slate-900 font-bold bg-white hover:bg-emerald-50 shadow-xl shadow-black/20 transition-all hover:-translate-y-0.5">
                            <i class="fas fa-compass text-emerald-600"></i>
                            Browse Stores
                        </a>
                        <a href="{{ \App\Support\VendorPortal::entryUrl() }}"
                           class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl font-bold text-white border border-white/20 hover:bg-white/10 transition-all hover:-translate-y-0.5">
                            <i class="fas fa-store"></i>
                            {{ \App\Support\VendorPortal::entryLabel('List as Vendor', 'Go to Dashboard') }}
                        </a>
                    </div>

                    {{-- Stats --}}
                    <div class="fade-up fade-up-delay-3 grid grid-cols-3 gap-4 sm:gap-8 mt-14 pt-8 border-t border-white/10">
                        @foreach([
                            ['value' => '12K+', 'label' => 'Items Listed'],
                            ['value' => '850+', 'label' => 'Vendors'],
                            ['value' => '50+', 'label' => 'Categories'],
                        ] as $stat)
                            <div class="text-center lg:text-left">
                                <p class="text-2xl sm:text-3xl font-extrabold text-white">{{ $stat['value'] }}</p>
                                <p class="text-xs sm:text-sm text-slate-400 mt-1">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Hero visual --}}
                <div class="relative hidden lg:block">
                    <div class="relative mx-auto w-full max-w-md">
                        <div class="floating">
                            <div class="rounded-3xl overflow-hidden shadow-2xl shadow-emerald-500/20 border border-white/10">
                                <img src="https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600&h=700&fit=crop"
                                     alt="Rental equipment including cameras and tools available on Rentkia"
                                     class="w-full h-[420px] object-cover"
                                     width="600"
                                     height="420"
                                     loading="eager"
                                     fetchpriority="high">
                            </div>
                        </div>

                        <div class="absolute -top-6 -left-8 glass-dark rounded-2xl p-4 shadow-xl border border-white/10 floating-delayed">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-emerald-500 flex items-center justify-center">
                                    <i class="fas fa-shield-alt text-white"></i>
                                </div>
                                <div>
                                    <p class="text-white font-bold text-sm">850+ Verified</p>
                                    <p class="text-slate-400 text-xs">Trusted Vendors</p>
                                </div>
                            </div>
                        </div>

                        <div class="absolute -bottom-4 -right-6 glass-dark rounded-2xl p-4 shadow-xl border border-white/10">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-amber-500 flex items-center justify-center">
                                    <i class="fas fa-star text-white"></i>
                                </div>
                                <div>
                                    <p class="text-white font-bold text-sm">4.9 / 5 Rating</p>
                                    <p class="text-slate-400 text-xs">12,000+ Reviews</p>
                                </div>
                            </div>
                        </div>

                        <div class="absolute top-1/2 -right-12 glass-dark rounded-2xl px-4 py-3 shadow-xl border border-white/10">
                            <div class="flex items-center gap-2">
                                <div class="flex -space-x-2">
                                    @foreach(['photo-1494790108377-be9c29b29330', 'photo-1507003211169-0a1dd7228f2d', 'photo-1438761681033-6461ffad8d80'] as $avatar)
                                        <img src="https://images.unsplash.com/{{ $avatar }}?w=64&h=64&fit=crop&crop=face"
                                             alt="" class="w-8 h-8 rounded-full border-2 border-slate-800 object-cover">
                                    @endforeach
                                </div>
                                <p class="text-white text-xs font-medium">2k+ rented today</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce text-emerald-400/60">
            <i class="fas fa-chevron-down text-xl"></i>
        </div>
    </section>

    {{-- ═══════════════════ TRUST BAR ═══════════════════ --}}
    <section class="relative bg-white border-b border-slate-100 py-6 overflow-hidden">
        <div class="flex whitespace-nowrap">
            <div class="marquee-track flex items-center gap-12 px-6">
                @foreach(array_merge(
                    ['Canon', 'Sony', 'Bosch', 'Makita', 'GoPro', 'DJI', 'Nikon', 'DeWalt'],
                    ['Canon', 'Sony', 'Bosch', 'Makita', 'GoPro', 'DJI', 'Nikon', 'DeWalt']
                ) as $brand)
                    <span class="text-slate-300 font-bold text-lg tracking-wider uppercase">{{ $brand }}</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════ FEATURES (Bento) ═══════════════════ --}}
    <section id="features" class="py-20 lg:py-28 px-4 sm:px-6 lg:px-8 bg-slate-50">
        <div class="max-w-7xl mx-auto">
            <div class="reveal text-center max-w-2xl mx-auto mb-16">
                <span class="inline-block text-emerald-600 font-semibold text-sm uppercase tracking-widest mb-3">Why Rentkia</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">
                    Everything you need to <span class="text-gradient">rent smarter</span>
                </h2>
                <p class="text-lg text-slate-500">A platform built for convenience, trust, and unbeatable value — for renters and vendors alike.</p>
            </div>

            <div class="reveal grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                {{-- Large feature --}}
                <div class="md:col-span-2 lg:row-span-2 group relative rounded-3xl overflow-hidden bg-gradient-to-br from-emerald-600 to-teal-700 p-8 lg:p-10 text-white hover-lift min-h-[280px] flex flex-col justify-end">
                    <div class="absolute inset-0 opacity-20">
                        <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=800&h=600&fit=crop"
                             alt="" class="w-full h-full object-cover">
                    </div>
                    <div class="relative">
                        <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center mb-5">
                            <i class="fas fa-shield-alt text-2xl"></i>
                        </div>
                        <h3 class="text-2xl lg:text-3xl font-bold mb-3">Bank-grade Security</h3>
                        <p class="text-emerald-100 text-base lg:text-lg max-w-md">Every vendor is KYC-verified. Payments are encrypted end-to-end. Your deposits are protected until you confirm delivery.</p>
                    </div>
                </div>

                @foreach([
                    ['icon' => 'fa-bolt', 'title' => 'Instant Booking', 'desc' => 'Reserve in seconds with real-time availability and instant confirmation.'],
                    ['icon' => 'fa-hand-holding-usd', 'title' => 'Best Prices', 'desc' => 'Transparent daily rates with no hidden fees. Save up to 80% vs buying.'],
                    ['icon' => 'fa-truck', 'title' => 'Doorstep Delivery', 'desc' => 'Get items delivered to your door or pick up from a nearby vendor.'],
                    ['icon' => 'fa-headset', 'title' => '24/7 Support', 'desc' => 'Our team is always ready to help — before, during, and after your rental.'],
                ] as $feature)
                    <div class="group rounded-3xl bg-white p-7 border border-slate-100 shadow-sm hover-lift">
                        <div class="w-12 h-12 rounded-2xl gradient-green-light flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <i class="fas {{ $feature['icon'] }} text-emerald-700 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">{{ $feature['title'] }}</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════ HOW IT WORKS ═══════════════════ --}}
    <section id="how-it-works" class="py-20 lg:py-28 px-4 sm:px-6 lg:px-8 bg-white relative overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-50 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>

        <div class="max-w-7xl mx-auto relative">
            <div class="reveal text-center max-w-2xl mx-auto mb-16">
                <span class="inline-block text-emerald-600 font-semibold text-sm uppercase tracking-widest mb-3">Simple Process</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">
                    Rent in <span class="text-gradient">3 easy steps</span>
                </h2>
                <p class="text-lg text-slate-500">From browsing to returning — the entire journey takes minutes, not hours.</p>
            </div>

            <div class="reveal grid md:grid-cols-3 gap-8 lg:gap-12">
                @foreach([
                    ['step' => '01', 'icon' => 'fa-search', 'title' => 'Discover & Compare', 'desc' => 'Browse thousands of items across 50+ categories. Filter by price, location, and ratings.', 'color' => 'from-emerald-500 to-teal-500'],
                    ['step' => '02', 'icon' => 'fa-calendar-check', 'title' => 'Book & Pay Securely', 'desc' => 'Pick your dates, pay with UPI or card, and get instant booking confirmation on your phone.', 'color' => 'from-teal-500 to-cyan-500'],
                    ['step' => '03', 'icon' => 'fa-smile-beam', 'title' => 'Enjoy & Return', 'desc' => 'Use the item worry-free. Return on time and rate your experience to help the community.', 'color' => 'from-cyan-500 to-emerald-500'],
                ] as $i => $step)
                    <div class="relative text-center group">
                        @if($i < 2)
                            <div class="hidden md:block absolute top-16 left-[60%] w-[80%] h-px bg-gradient-to-r from-emerald-200 to-transparent"></div>
                        @endif
                        <div class="relative inline-flex mb-6">
                            <div class="w-28 h-28 rounded-3xl bg-gradient-to-br {{ $step['color'] }} flex items-center justify-center shadow-xl shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                                <i class="fas {{ $step['icon'] }} text-white text-3xl"></i>
                            </div>
                            <span class="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-slate-900 text-white text-xs font-bold flex items-center justify-center">{{ $step['step'] }}</span>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-3">{{ $step['title'] }}</h3>
                        <p class="text-slate-500 leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════ FEATURED ITEMS ═══════════════════ --}}
    <section class="py-20 lg:py-28 px-4 sm:px-6 lg:px-8 bg-slate-50">
        <div class="max-w-7xl mx-auto">
            <div class="reveal flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12">
                <div>
                    <span class="inline-block text-emerald-600 font-semibold text-sm uppercase tracking-widest mb-3">Trending Now</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                        Popular <span class="text-gradient">rentals</span>
                    </h2>
                </div>
                <a href="{{ route('stores.index') }}" class="inline-flex items-center gap-2 text-emerald-600 font-semibold hover:text-emerald-700 transition group">
                    Browse all stores
                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>

            <div class="reveal grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach([
                    ['name' => 'Sony A7 III Camera', 'cat' => 'Cameras', 'price' => '899', 'img' => 'photo-1516035069371-29a1b244cc32', 'vendor' => 'LensCraft Mumbai'],
                    ['name' => 'Bosch Drill Set Pro', 'cat' => 'Tools', 'price' => '199', 'img' => 'photo-1504149926900-34a400a43e0f', 'vendor' => 'ToolHub Pune'],
                    ['name' => 'DJI Mini 3 Drone', 'cat' => 'Drones', 'price' => '1,299', 'img' => 'photo-1473968512647-3e447244af8f', 'vendor' => 'SkyRent Delhi'],
                    ['name' => 'PS5 + 2 Controllers', 'cat' => 'Gaming', 'price' => '349', 'img' => 'photo-1606144042614-b2417e99c4e3', 'vendor' => 'GameZone BLR'],
                ] as $item)
                    <article class="group rounded-2xl bg-white overflow-hidden border border-slate-100 shadow-sm hover-lift cursor-pointer">
                        <div class="relative h-48 overflow-hidden">
                            <img src="https://images.unsplash.com/{{ $item['img'] }}?w=500&h=400&fit=crop"
                                 alt="{{ $item['name'] }}"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute top-3 left-3 px-2.5 py-1 rounded-lg bg-white/90 backdrop-blur text-xs font-semibold text-emerald-700">
                                {{ $item['cat'] }}
                            </div>
                            <div class="absolute top-3 right-3 px-2.5 py-1 rounded-lg bg-emerald-500 text-white text-xs font-semibold">
                                Available
                            </div>
                        </div>
                        <div class="p-5">
                            <p class="text-xs text-slate-400 mb-1"><i class="fas fa-store mr-1"></i>{{ $item['vendor'] }}</p>
                            <h3 class="font-bold text-slate-900 mb-3 line-clamp-1">{{ $item['name'] }}</h3>
                            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                                <div>
                                    <p class="text-xs text-slate-400">Per day</p>
                                    <p class="text-xl font-extrabold text-emerald-600">₹{{ $item['price'] }}</p>
                                </div>
                                <button class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                                    <i class="fas fa-arrow-right text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════ CATEGORIES ═══════════════════ --}}
    <section id="categories" class="py-20 lg:py-28 px-4 sm:px-6 lg:px-8 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="reveal text-center max-w-2xl mx-auto mb-14">
                <span class="inline-block text-emerald-600 font-semibold text-sm uppercase tracking-widest mb-3">Categories</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight mb-4">
                    Find your <span class="text-gradient">perfect rental</span>
                </h2>
                <p class="text-lg text-slate-500">Whatever the occasion, we've got you covered with 50+ categories nationwide.</p>
            </div>

            <div class="reveal grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 lg:gap-5">
                @foreach([
                    ['name' => 'Electronics', 'count' => '2,400+', 'icon' => 'fa-laptop', 'img' => 'photo-1498049794561-7780e7231661'],
                    ['name' => 'Cameras', 'count' => '890+', 'icon' => 'fa-camera', 'img' => 'photo-1516035069371-29a1b244cc32'],
                    ['name' => 'Tools', 'count' => '1,200+', 'icon' => 'fa-tools', 'img' => 'photo-1504149926900-34a400a43e0f'],
                    ['name' => 'Sports', 'count' => '760+', 'icon' => 'fa-bicycle', 'img' => 'photo-1571068316344-75bc76f77890'],
                    ['name' => 'Party', 'count' => '540+', 'icon' => 'fa-glass-cheers', 'img' => 'photo-1530103862676-de8c9debad1d'],
                    ['name' => 'Gaming', 'count' => '430+', 'icon' => 'fa-gamepad', 'img' => 'photo-1606144042614-b2417e99c4e3'],
                ] as $cat)
                    <a href="{{ route('stores.index') }}" class="group relative rounded-2xl overflow-hidden aspect-[3/4] hover-lift block">
                        <img src="https://images.unsplash.com/{{ $cat['img'] }}?w=300&h=400&fit=crop"
                             alt="Rent {{ $cat['name'] }} on Rentkia"
                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                             loading="lazy"
                             width="300"
                             height="400">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/30 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center mb-2 group-hover:bg-emerald-500 transition-colors">
                                <i class="fas {{ $cat['icon'] }} text-white text-sm"></i>
                            </div>
                            <h3 class="text-white font-bold text-sm">{{ $cat['name'] }}</h3>
                            <p class="text-emerald-300 text-xs mt-0.5">{{ $cat['count'] }} items</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════ TESTIMONIALS ═══════════════════ --}}
    <section id="testimonials" class="py-20 lg:py-28 px-4 sm:px-6 lg:px-8 bg-slate-50">
        <div class="max-w-7xl mx-auto">
            <div class="reveal text-center max-w-2xl mx-auto mb-14">
                <span class="inline-block text-emerald-600 font-semibold text-sm uppercase tracking-widest mb-3">Testimonials</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-4">
                    Loved by <span class="text-gradient">thousands</span>
                </h2>
                <p class="text-lg text-slate-500">Real stories from renters and vendors across India.</p>
            </div>

            <div class="reveal grid md:grid-cols-3 gap-6">
                @foreach([
                    ['name' => 'Priya Sharma', 'role' => 'Wedding Photographer', 'city' => 'Mumbai', 'text' => 'Rented a Sony lens for a destination wedding. Saved ₹80,000 and the vendor was incredibly professional. Will never buy gear I use once!', 'avatar' => 'photo-1494790108377-be9c29b29330'],
                    ['name' => 'Rahul Mehta', 'role' => 'DIY Enthusiast', 'city' => 'Pune', 'text' => 'Needed a tile cutter for one weekend project. Found one 2 km away, booked in 5 minutes, and returned it Monday. Brilliant platform.', 'avatar' => 'photo-1507003211169-0a1dd7228f2d'],
                    ['name' => 'Ananya Reddy', 'role' => 'Vendor — GameZone', 'city' => 'Bangalore', 'text' => 'Listing my gaming consoles on Rentkia turned idle inventory into steady income. The dashboard and payouts are seamless.', 'avatar' => 'photo-1438761681033-6461ffad8d80'],
                ] as $review)
                    <div class="rounded-2xl bg-white p-7 border border-slate-100 shadow-sm hover-lift">
                        <div class="flex gap-1 mb-4">
                            @for($s = 0; $s < 5; $s++)
                                <i class="fas fa-star text-amber-400 text-sm"></i>
                            @endfor
                        </div>
                        <p class="text-slate-600 leading-relaxed mb-6 text-sm">"{{ $review['text'] }}"</p>
                        <div class="flex items-center gap-3 pt-5 border-t border-slate-100">
                            <img src="https://images.unsplash.com/{{ $review['avatar'] }}?w=80&h=80&fit=crop&crop=face"
                                 alt="{{ $review['name'] }}"
                                 class="w-11 h-11 rounded-full object-cover">
                            <div>
                                <p class="font-bold text-slate-900 text-sm">{{ $review['name'] }}</p>
                                <p class="text-xs text-slate-400">{{ $review['role'] }} · {{ $review['city'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════ VENDOR CTA ═══════════════════ --}}
    <section class="py-20 lg:py-28 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">
            <div class="reveal relative rounded-3xl overflow-hidden">
                <div class="absolute inset-0 gradient-green"></div>
                <div class="absolute inset-0 grid-pattern opacity-30"></div>
                <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/3 blur-2xl"></div>

                <div class="relative px-8 py-14 sm:px-14 sm:py-20 text-center">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-white/20 text-white text-sm font-medium mb-6">For Vendors</span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white mb-5 tracking-tight">
                        Turn idle inventory into income
                    </h2>
                    <p class="text-lg text-emerald-100 max-w-2xl mx-auto mb-10">
                        Join 850+ vendors earning passive income by listing cameras, tools, electronics, and more. Free to start, powerful tools included.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ \App\Support\VendorPortal::entryUrl() }}"
                           class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-white text-emerald-700 font-bold shadow-xl hover:bg-emerald-50 transition-all hover:-translate-y-0.5">
                            <i class="fas fa-store"></i>
                            {{ \App\Support\VendorPortal::entryLabel('Start as Vendor', 'Go to Dashboard') }}
                        </a>
                        <a href="{{ route('pages.how-it-works') }}"
                           class="inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl text-white font-bold border border-white/30 hover:bg-white/10 transition-all">
                            Learn More
                            <i class="fas fa-arrow-right text-sm"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-3 gap-6 max-w-lg mx-auto mt-14 pt-10 border-t border-white/20">
                        @foreach([
                            ['val' => '₹0', 'lbl' => 'To Start'],
                            ['val' => '2 min', 'lbl' => 'Setup Time'],
                            ['val' => 'Weekly', 'lbl' => 'Payouts'],
                        ] as $v)
                            <div>
                                <p class="text-2xl font-extrabold text-white">{{ $v['val'] }}</p>
                                <p class="text-emerald-200 text-xs mt-1">{{ $v['lbl'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>
@endsection
