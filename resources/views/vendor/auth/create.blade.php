<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Vendor - RentApp</title>
    
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
        
        .input-focus:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        input, select, textarea {
            max-width: 100%;
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
            
            .grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Animated Background Shapes -->
    <div class="animated-shape shape-1"></div>
    <div class="animated-shape shape-2"></div>
    
    <!-- Form Container -->
    <div class="w-full max-w-2xl relative z-10">
        
        <!-- Back Button -->
        <div class="text-left mb-8">
            @if(Session::has('vendor_id'))
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
        
        <!-- Form Card -->
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="flex justify-center mb-4">
                    <div class="bg-green-600 rounded-full p-4">
                        <i class="fas fa-store-alt text-white text-3xl"></i>
                    </div>
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Create Your Vendor Profile</h1>
                <p class="text-gray-600">Fill in your business details to get started</p>
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
            
            <!-- Vendor Creation Form -->
            <form action="{{ route('vendor.create.submit') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    
                    <!-- Business Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-store mr-2 text-green-600"></i>Business Name <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="name"
                            name="name" 
                            value="{{ old('name') }}"
                            placeholder="Enter your business name"
                            class="input-focus w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none transition-all"
                            required
                            autofocus
                        >
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Business Category -->
                    <div>
                        <label for="business_category_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-briefcase mr-2 text-green-600"></i>Business Category <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="business_category_id" 
                            name="business_category_id" 
                            class="input-focus w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none transition-all"
                            required
                        >
                            <option value="">Select your business category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('business_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('business_category_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>Select the primary category that best describes your business
                        </p>
                    </div>
                    
                    <!-- Owner Name -->
                    <div>
                        <label for="owner_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-green-600"></i>Owner/Contact Name
                        </label>
                        <input 
                            type="text" 
                            id="owner_name"
                            name="owner_name" 
                            value="{{ old('owner_name') }}"
                            placeholder="Enter owner or contact person name"
                            class="input-focus w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none transition-all"
                        >
                        @error('owner_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- City and State -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-city mr-2 text-green-600"></i>City
                            </label>
                            <input 
                                type="text" 
                                id="city"
                                name="city" 
                                value="{{ old('city') }}"
                                placeholder="City"
                                class="input-focus w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none transition-all"
                            >
                            @error('city')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="state" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-map mr-2 text-green-600"></i>State
                            </label>
                            <input 
                                type="text" 
                                id="state"
                                name="state" 
                                value="{{ old('state') }}"
                                placeholder="State"
                                class="input-focus w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none transition-all"
                            >
                            @error('state')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- GST Number -->
                    <div>
                        <label for="gst_number" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-file-invoice mr-2 text-green-600"></i>GST Number (Optional)
                        </label>
                        <input 
                            type="text" 
                            id="gst_number"
                            name="gst_number" 
                            value="{{ old('gst_number') }}"
                            placeholder="Enter GST number"
                            class="input-focus w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none transition-all"
                            maxlength="15"
                        >
                        @error('gst_number')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i>GST number is optional but recommended for verified status
                        </p>
                    </div>
                    
                    <!-- Language Preference -->
                    <div>
                        <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-globe mr-2 text-green-600"></i>Preferred Language <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="language"
                            name="language" 
                            class="input-focus w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:outline-none transition-all"
                            required
                        >
                            <option value="en" {{ old('language') == 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                            <option value="hi" {{ old('language') == 'hi' ? 'selected' : '' }}>🇮🇳 हिन्दी (Hindi)</option>
                            <option value="bn" {{ old('language') == 'bn' ? 'selected' : '' }}>🇮🇳 বাংলা (Bengali)</option>
                            <option value="mr" {{ old('language') == 'mr' ? 'selected' : '' }}>🇮🇳 मराठी (Marathi)</option>
                            <option value="te" {{ old('language') == 'te' ? 'selected' : '' }}>🇮🇳 తెలుగు (Telugu)</option>
                            <option value="ta" {{ old('language') == 'ta' ? 'selected' : '' }}>🇮🇳 தமிழ் (Tamil)</option>
                            <option value="gu" {{ old('language') == 'gu' ? 'selected' : '' }}>🇮🇳 ગુજરાતી (Gujarati)</option>
                            <option value="ur" {{ old('language') == 'ur' ? 'selected' : '' }}>🇮🇳 اردو (Urdu)</option>
                            <option value="kn" {{ old('language') == 'kn' ? 'selected' : '' }}>🇮🇳 ಕನ್ನಡ (Kannada)</option>
                            <option value="or" {{ old('language') == 'or' ? 'selected' : '' }}>🇮🇳 ଓଡ଼ିଆ (Odia)</option>
                            <option value="ml" {{ old('language') == 'ml' ? 'selected' : '' }}>🇮🇳 മലയാളം (Malayalam)</option>
                            <option value="pa" {{ old('language') == 'pa' ? 'selected' : '' }}>🇮🇳 ਪੰਜਾਬੀ (Punjabi)</option>
                        </select>
                        @error('language')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Important Note -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mr-3 mt-1"></i>
                            <div class="text-sm text-blue-700">
                                <p class="font-semibold mb-1">Important Note:</p>
                                <p>Your vendor account will be created and activated immediately. However, verification may take 24-48 hours for full access to all features.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3.5 rounded-lg transition-colors shadow-lg hover:shadow-xl flex items-center justify-center"
                    >
                        <i class="fas fa-check-circle mr-2"></i>Create Vendor Profile
                    </button>
                    
                </div>
            </form>
            
            <!-- Already have vendors link -->
            @php
                $userId = Session::get('vendor_auth_user_id');
                $hasVendors = false;
                if ($userId) {
                    $hasVendors = \App\Models\Vendor::where('user_id', $userId)->exists();
                }
            @endphp
            
            @if($hasVendors)
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 mb-3">Already have a vendor?</p>
                    <a href="{{ route('vendor.select') }}" class="inline-flex items-center text-green-600 hover:text-green-800 font-semibold transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Vendor Selection
                    </a>
                </div>
            @endif
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
</body>
</html>
