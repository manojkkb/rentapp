@php
    $headerSolid = $headerSolid ?? false;
@endphp
<nav x-data="{ mobileMenuOpen: false, scrolled: {{ $headerSolid ? 'true' : 'false' }} }"
     x-init="window.addEventListener('scroll', () => { scrolled = {{ $headerSolid ? 'true' : 'false' }} || window.scrollY > 20 })"
     :class="scrolled ? 'bg-white/90 shadow-lg shadow-slate-900/5 border-b border-slate-200/60' : 'bg-transparent'"
     class="fixed top-0 w-full z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">

            <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl transition"
                    :class="scrolled ? 'text-slate-600 hover:bg-slate-100' : '{{ $headerSolid ? 'text-slate-600 hover:bg-slate-100' : 'text-white hover:bg-white/10' }}'"
                    aria-label="Toggle menu">
                <i class="fas fa-bars text-xl" x-show="!mobileMenuOpen"></i>
                <i class="fas fa-times text-xl" x-show="mobileMenuOpen"></i>
            </button>

            <a href="{{ route('welcome') }}" class="flex items-center gap-3 group">
                <div class="gradient-green w-11 h-11 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30 group-hover:scale-105 transition-transform">
                    <i class="fas fa-box-open text-white text-lg"></i>
                </div>
                <div>
                    <span class="text-xl font-bold tracking-tight"
                          :class="scrolled ? 'text-slate-900' : '{{ $headerSolid ? 'text-slate-900' : 'text-white' }}'">Rentkia</span>
                    <p class="text-[11px] font-medium tracking-wide uppercase"
                       :class="scrolled ? 'text-slate-500' : '{{ $headerSolid ? 'text-slate-500' : 'text-emerald-200/80' }}'">Rent · Share · Save</p>
                </div>
            </a>

            <div class="hidden md:flex items-center gap-1">
                @foreach(['home' => 'Home', 'features' => 'Features', 'categories' => 'Categories', 'testimonials' => 'Reviews'] as $id => $label)
                    <a href="{{ $headerSolid ? route('welcome').'#'.$id : '#'.$id }}"
                       class="px-4 py-2 rounded-lg text-sm font-medium transition"
                       :class="scrolled ? 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' : '{{ $headerSolid ? 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' : 'text-white/80 hover:text-white hover:bg-white/10' }}'">
                        {{ $label }}
                    </a>
                @endforeach
                <a href="{{ route('pages.about') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition"
                   :class="scrolled ? 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' : '{{ $headerSolid ? 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' : 'text-white/80 hover:text-white hover:bg-white/10' }}'">
                    About
                </a>
                <a href="{{ route('pages.how-it-works') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition"
                   :class="scrolled ? 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' : '{{ $headerSolid ? 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' : 'text-white/80 hover:text-white hover:bg-white/10' }}'">
                    How It Works
                </a>
                <a href="{{ route('stores.index') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium transition"
                   :class="scrolled ? 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' : '{{ $headerSolid ? 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' : 'text-white/80 hover:text-white hover:bg-white/10' }}'">
                    Stores
                </a>

                <a href="{{ \App\Support\VendorPortal::entryUrl() }}"
                   class="ml-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-400 shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5">
                    <i class="fas fa-store text-xs"></i>
                    {{ \App\Support\VendorPortal::entryLabel() }}
                </a>
            </div>

            <a href="{{ \App\Support\VendorPortal::entryUrl() }}"
               class="md:hidden inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold text-white bg-emerald-500 shadow-md">
                <i class="fas fa-store"></i>
                {{ \App\Support\VendorPortal::entryLabel() }}
            </a>
        </div>
    </div>

    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden bg-white border-t border-slate-100 shadow-xl">
        <div class="px-4 py-5 space-y-1">
            @foreach(['home' => 'Home', 'features' => 'Features', 'categories' => 'Categories', 'testimonials' => 'Reviews'] as $id => $label)
                <a href="{{ $headerSolid ? route('welcome').'#'.$id : '#'.$id }}" @click="mobileMenuOpen = false"
                   class="block px-4 py-3 rounded-xl text-slate-700 font-medium hover:bg-emerald-50 hover:text-emerald-700 transition">
                    {{ $label }}
                </a>
            @endforeach
            <a href="{{ route('pages.about') }}" @click="mobileMenuOpen = false"
               class="block px-4 py-3 rounded-xl text-slate-700 font-medium hover:bg-emerald-50 hover:text-emerald-700 transition">
                About Us
            </a>
            <a href="{{ route('pages.how-it-works') }}" @click="mobileMenuOpen = false"
               class="block px-4 py-3 rounded-xl text-slate-700 font-medium hover:bg-emerald-50 hover:text-emerald-700 transition">
                How It Works
            </a>
            <a href="{{ route('pages.team') }}" @click="mobileMenuOpen = false"
               class="block px-4 py-3 rounded-xl text-slate-700 font-medium hover:bg-emerald-50 hover:text-emerald-700 transition">
                Our Team
            </a>
            <a href="{{ route('pages.contact') }}" @click="mobileMenuOpen = false"
               class="block px-4 py-3 rounded-xl text-slate-700 font-medium hover:bg-emerald-50 hover:text-emerald-700 transition">
                Contact Us
            </a>
            <a href="{{ route('stores.index') }}" @click="mobileMenuOpen = false"
               class="block px-4 py-3 rounded-xl text-slate-700 font-medium hover:bg-emerald-50 hover:text-emerald-700 transition">
                Stores
            </a>
            <a href="{{ \App\Support\VendorPortal::entryUrl() }}"
               class="mt-3 flex items-center justify-center gap-2 rounded-full bg-emerald-600 px-4 py-3.5 text-center font-semibold text-white">
                <i class="fas fa-store"></i>
                {{ \App\Support\VendorPortal::entryLabel() }}
            </a>
        </div>
    </div>
</nav>
