<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Select Vendor - RentApp</title>
    
    <!-- Vite Assets (includes Tailwind CSS, Alpine.js, Font Awesome, and Inter Font) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            max-width: 100vw;
        }
        
        html {
            overflow-x: hidden;
            max-width: 100vw;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            max-width: 100%;
        }
        
        .vendor-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .vendor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(16, 185, 129, 0.2);
        }
        
        .vendor-card.selected {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        
        .animated-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.15;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            background: #10b981;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            background: #34d399;
            bottom: -50px;
            right: -50px;
            animation-delay: 2s;
        }
        
        @media (max-width: 640px) {
            .animated-shape {
                display: none;
            }
            
            .glass-effect {
                padding: 1.5rem !important;
                max-width: calc(100vw - 2rem) !important;
                margin: 0 auto !important;
                width: 100% !important;
            }
            
            body {
                padding: 0.75rem !important;
                padding-top: 1.5rem !important;
                align-items: flex-start !important;
            }
            
            .max-w-2xl {
                max-width: 100% !important;
                width: 100% !important;
            }
            
            .mb-8 {
                margin-bottom: 1rem !important;
            }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Animated Background Shapes -->
    <div class="animated-shape shape-1"></div>
    <div class="animated-shape shape-2"></div>
    
    <!-- Selection Container -->
    <div class="w-full max-w-2xl relative z-10">
        
        <!-- Back Button -->
        <div class="text-left mb-8">
            @if(isset($isSwitching) && $isSwitching)
                <a href="{{ route('vendor.home') }}" class="inline-flex items-center text-white hover:text-green-100 transition-colors group">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    <span class="font-medium">Back to Home</span>
                </a>
            @else
                <a href="{{ route('vendor.login') }}" class="inline-flex items-center text-white hover:text-green-100 transition-colors group">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                    <span class="font-medium">Back to Login</span>
                </a>
            @endif
        </div>
        
        <!-- Selection Card -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="flex justify-center mb-4">
                    <div class="bg-green-600 rounded-full p-4">
                        <i class="fas fa-store text-white text-3xl"></i>
                    </div>
                </div>
                @if(isset($isSwitching) && $isSwitching)
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Switch Vendor</h1>
                    <p class="text-gray-600">Select a different vendor account to manage</p>
                @else
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Select Your Vendor</h1>
                    <p class="text-gray-600">Choose which vendor account you want to access</p>
                @endif
            </div>
            
            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <p class="text-green-700 text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p class="text-red-700 text-sm">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Vendor Selection Form -->
            <form action="{{ route('vendor.select.submit') }}" method="POST" id="selectVendorForm">
                @csrf
                
                <input type="hidden" name="vendor_id" id="selectedVendorId" required>
                
                <!-- Vendor Cards -->
                <div class="space-y-4 mb-6">
                    @foreach($vendors as $vendor)
                        <div class="vendor-card border-2 border-gray-200 rounded-lg p-4 hover:border-green-500"
                             onclick="selectVendor({{ $vendor->id }})"
                             id="vendor-card-{{ $vendor->id }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="bg-green-100 rounded-full p-3">
                                        <i class="fas fa-store text-green-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800 text-lg">{{ $vendor->name }}</h3>
                                        <div class="text-sm text-gray-600 space-y-1">
                                            @if($vendor->city || $vendor->state)
                                                <p><i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>
                                                    {{ $vendor->city }}@if($vendor->city && $vendor->state), @endif{{ $vendor->state }}
                                                </p>
                                            @endif
                                            @if($vendor->gst_number)
                                                <p><i class="fas fa-file-invoice mr-1 text-gray-400"></i>GST: {{ $vendor->gst_number }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="vendor-status">
                                    @if($vendor->is_verified)
                                        <span class="text-green-600 text-sm">
                                            <i class="fas fa-check-circle"></i> Verified
                                        </span>
                                    @else
                                        <span class="text-yellow-600 text-sm">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3.5 rounded-lg transition-colors shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                    id="submitBtn"
                    disabled
                >
                    <i class="fas fa-arrow-right mr-2"></i>Continue with Selected Vendor
                </button>
            </form>
            
            <!-- Create New Vendor Button -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 mb-3">Want to add another vendor?</p>
                <a href="{{ route('vendor.create') }}" class="inline-flex items-center text-green-600 hover:text-green-800 font-semibold transition-colors">
                    <i class="fas fa-plus-circle mr-2"></i>Create New Vendor
                </a>
            </div>
        </div>
        
        <!-- Footer Links -->
        <div class="mt-8 text-center text-white text-sm">
            <p class="mb-2">© 2026 RentApp. All rights reserved.</p>
            <div class="space-x-4">
                <a href="#" class="hover:underline">Privacy Policy</a>
                <span>•</span>
                <a href="#" class="hover:underline">Terms of Service</a>
                <span>•</span>
                <a href="#" class="hover:underline">Support</a>
            </div>
        </div>
    </div>
    
    <script>
        let selectedVendorId = null;
        
        function selectVendor(vendorId) {
            // Remove selection from all cards
            document.querySelectorAll('.vendor-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection to clicked card
            const card = document.getElementById('vendor-card-' + vendorId);
            card.classList.add('selected');
            
            // Update hidden input
            document.getElementById('selectedVendorId').value = vendorId;
            selectedVendorId = vendorId;
            
            // Enable submit button
            document.getElementById('submitBtn').disabled = false;
        }
    </script>
</body>
</html>
