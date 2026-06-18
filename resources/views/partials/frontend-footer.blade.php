<footer id="contact" class="relative bg-slate-950 text-white overflow-hidden">
    <div class="absolute inset-0 hero-mesh opacity-40"></div>
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-emerald-500/50 to-transparent"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-8 mb-14">
            <div class="lg:col-span-1">
                <div class="flex items-center gap-3 mb-5">
                    <div class="gradient-green w-11 h-11 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                        <i class="fas fa-box-open text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Rentkia</h3>
                        <p class="text-xs text-emerald-400/80 uppercase tracking-wider">Rent · Share · Save</p>
                    </div>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed mb-6">
                    India's smartest rental marketplace. Access premium gear without the price tag — sustainable, affordable, and always nearby.
                </p>
                <div class="flex gap-3">
                    @foreach(['facebook-f', 'twitter', 'instagram', 'linkedin-in'] as $icon)
                        <a href="#" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:bg-emerald-500 hover:text-white hover:border-emerald-500 transition-all">
                            <i class="fab fa-{{ $icon }} text-sm"></i>
                        </a>
                    @endforeach
                </div>
            </div>

            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-emerald-400 mb-5">Company</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="{{ route('pages.about') }}" class="text-slate-400 hover:text-white transition">About Us</a></li>
                    <li><a href="{{ route('pages.how-it-works') }}" class="text-slate-400 hover:text-white transition">How It Works</a></li>
                    <li><a href="{{ route('pages.team') }}" class="text-slate-400 hover:text-white transition">Our Team</a></li>
                    <li><a href="{{ route('pages.contact') }}" class="text-slate-400 hover:text-white transition">Contact Us</a></li>
                    <li><a href="{{ route('stores.index') }}" class="text-slate-400 hover:text-white transition">Stores</a></li>
                    <li><a href="{{ \App\Support\VendorPortal::entryUrl() }}" class="text-slate-400 hover:text-white transition">For Vendors</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-emerald-400 mb-5">Legal</h4>
                <ul class="space-y-3 text-sm">
                    <li>
                        <a href="{{ route('legal.privacy') }}" class="text-slate-400 hover:text-white transition">Privacy Policy</a>
                    </li>
                    <li>
                        <a href="{{ route('legal.terms') }}" class="text-slate-400 hover:text-white transition">Terms &amp; Conditions</a>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-emerald-400 mb-5">Get in Touch</h4>
                <ul class="space-y-4 text-sm">
                    <li class="flex items-start gap-3 text-slate-400">
                        <i class="fas fa-map-marker-alt text-emerald-400 mt-0.5"></i>
                        <span>123 Rental Street, Bandra West, Mumbai 400050</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-400">
                        <i class="fas fa-phone text-emerald-400"></i>
                        <span>+91 98765 43210</span>
                    </li>
                    <li class="flex items-center gap-3 text-slate-400">
                        <i class="fas fa-envelope text-emerald-400"></i>
                        <a href="{{ route('pages.contact') }}" class="hover:text-white transition">hello@rentkia.com</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
            <p class="text-slate-500 text-sm">&copy; {{ date('Y') }} Rentkia. All rights reserved.</p>
            <div class="flex flex-wrap justify-center sm:justify-end gap-4 sm:gap-6 text-sm">
                <a href="{{ route('legal.privacy') }}" class="text-slate-500 hover:text-emerald-400 transition">Privacy Policy</a>
                <a href="{{ route('legal.terms') }}" class="text-slate-500 hover:text-emerald-400 transition">Terms &amp; Conditions</a>
            </div>
        </div>
    </div>
</footer>

<button id="scrollToTop"
        class="fixed bottom-8 right-8 w-12 h-12 rounded-2xl gradient-green text-white shadow-xl shadow-emerald-500/30 hover:shadow-emerald-500/50 transition-all hover:-translate-y-1 hidden z-50"
        aria-label="Scroll to top">
    <i class="fas fa-arrow-up"></i>
</button>

@push('scripts')
<script>
    const scrollToTopBtn = document.getElementById('scrollToTop');

    window.addEventListener('scroll', () => {
        scrollToTopBtn.classList.toggle('hidden', window.scrollY <= 300);
    });

    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
</script>
@endpush
