<!-- Footer -->
<footer id="contact" class="bg-gray-900 text-white py-16 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="grid md:grid-cols-4 gap-12 mb-12">
            <!-- Company Info -->
            <div>
                <div class="flex items-center space-x-3 mb-6">
                    <div class="gradient-green w-12 h-12 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-box-open text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold">Rentkia</h3>
                        <p class="text-sm text-gray-400">Rent. Share. Save.</p>
                    </div>
                </div>
                <p class="text-gray-400 mb-6">Making rentals accessible, affordable, and sustainable for everyone.</p>
                
                <!-- Social Links -->
                <div class="flex space-x-4">
                    <a href="#" class="bg-gray-800 w-10 h-10 rounded-lg flex items-center justify-center hover:bg-green-600 transition">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="bg-gray-800 w-10 h-10 rounded-lg flex items-center justify-center hover:bg-green-600 transition">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="bg-gray-800 w-10 h-10 rounded-lg flex items-center justify-center hover:bg-green-600 transition">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="bg-gray-800 w-10 h-10 rounded-lg flex items-center justify-center hover:bg-green-600 transition">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h4 class="text-lg font-bold mb-6">Quick Links</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-400 hover:text-green-400 transition">About Us</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-green-400 transition">How It Works</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-green-400 transition">Pricing</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-green-400 transition">Blog</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-green-400 transition">FAQs</a></li>
                </ul>
            </div>
            
            <!-- Categories -->
            <div>
                <h4 class="text-lg font-bold mb-6">Categories</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-400 hover:text-green-400 transition">Electronics</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-green-400 transition">Cameras</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-green-400 transition">Tools & Equipment</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-green-400 transition">Sports & Fitness</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-green-400 transition">Party Supplies</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div>
                <h4 class="text-lg font-bold mb-6">Contact Us</h4>
                <ul class="space-y-4">
                    <li class="flex items-start space-x-3">
                        <i class="fas fa-map-marker-alt text-green-400 mt-1"></i>
                        <span class="text-gray-400">123 Rental Street, Mumbai, India</span>
                    </li>
                    <li class="flex items-center space-x-3">
                        <i class="fas fa-phone text-green-400"></i>
                        <span class="text-gray-400">+91 98765 43210</span>
                    </li>
                    <li class="flex items-center space-x-3">
                        <i class="fas fa-envelope text-green-400"></i>
                        <span class="text-gray-400">support@rentkia.com</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Bottom Bar -->
        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-400 text-sm mb-4 md:mb-0">© 2026 Rentkia. All rights reserved.</p>
            <div class="flex space-x-6 text-sm">
                <a href="#" class="text-gray-400 hover:text-green-400 transition">Privacy Policy</a>
                <a href="#" class="text-gray-400 hover:text-green-400 transition">Terms of Service</a>
                <a href="#" class="text-gray-400 hover:text-green-400 transition">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button id="scrollToTop" class="fixed bottom-8 right-8 gradient-green text-white w-14 h-14 rounded-full shadow-2xl hover:shadow-3xl transition transform hover:-translate-y-1 hidden z-50">
    <i class="fas fa-arrow-up"></i>
</button>

@section('scripts')
<script>
    // Scroll to Top Button
    const scrollToTopBtn = document.getElementById('scrollToTop');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            scrollToTopBtn.classList.remove('hidden');
        } else {
            scrollToTopBtn.classList.add('hidden');
        }
    });
    
    scrollToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
</script>
@endsection
