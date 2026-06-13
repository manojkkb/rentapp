<nav x-data="{ mobileMenuOpen: false, scrolled: false }"
     x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 })"
     :class="scrolled ? 'bg-white/90 shadow-lg shadow-slate-900/5 border-b border-slate-200/60' : 'bg-transparent'"
     class="fixed top-0 w-full z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">

            <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl text-slate-600 hover:bg-slate-100 transition"
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
                          :class="scrolled ? 'text-slate-900' : 'text-white'">Rentkia</span>
                    <p class="text-[11px] font-medium tracking-wide uppercase"
                       :class="scrolled ? 'text-slate-500' : 'text-emerald-200/80'">Rent · Share · Save</p>
                </div>
            </a>

            <div class="hidden md:flex items-center gap-1">
                @foreach(['home' => 'Home', 'features' => 'Features', 'how-it-works' => 'How It Works', 'categories' => 'Categories', 'testimonials' => 'Reviews'] as $id => $label)
                    <a href="#{{ $id }}"
                       class="px-4 py-2 rounded-lg text-sm font-medium transition"
                       :class="scrolled ? 'text-slate-600 hover:text-emerald-600 hover:bg-emerald-50' : 'text-white/80 hover:text-white hover:bg-white/10'">
                        {{ $label }}
                    </a>
                @endforeach

                <a href="{{ route('vendor.login') }}"
                   class="ml-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold text-white bg-emerald-500 hover:bg-emerald-400 shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5">
                    <i class="fas fa-store text-xs"></i>
                    Login
                </a>
            </div>

            <a href="{{ route('vendor.login') }}"
               class="md:hidden inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-semibold text-white bg-emerald-500 shadow-md">
                <i class="fas fa-store"></i>
                Login
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
            @foreach(['home' => 'Home', 'features' => 'Features', 'how-it-works' => 'How It Works', 'categories' => 'Categories', 'testimonials' => 'Reviews', 'contact' => 'Contact'] as $id => $label)
                <a href="#{{ $id }}" @click="mobileMenuOpen = false"
                   class="block px-4 py-3 rounded-xl text-slate-700 font-medium hover:bg-emerald-50 hover:text-emerald-700 transition">
                    {{ $label }}
                </a>
            @endforeach
            <a href="{{ route('vendor.login') }}"
               class="mt-3 flex items-center justify-center gap-2 rounded-full bg-emerald-600 px-4 py-3.5 text-center font-semibold text-white">
                <i class="fas fa-store"></i>
                Login
            </a>
        </div>
    </div>
</nav>
