<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - Rentkia</title>
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        
        /* Green gradient */
        .bg-green-gradient {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        /* Animated gradient background */
        .bg-animated-gradient {
            background: linear-gradient(-45deg, #059669, #10b981, #34d399, #6ee7b7);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .float {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen transition-colors">
    
    <div class="min-h-screen flex">
        <!-- Left Side - Login Form -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 relative">
            <!-- Dark Mode Toggle -->
            <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                    class="absolute top-6 right-6 p-3 text-gray-700 dark:text-gray-300 hover:bg-green-50 dark:hover:bg-gray-800 rounded-xl transition-all shadow-lg">
                <i class="fas text-xl" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
            </button>
            
            <div class="max-w-md w-full space-y-8">
                <!-- Logo & Header -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-gradient rounded-2xl shadow-2xl mb-4 float">
                        <i class="fas fa-home text-white text-3xl"></i>
                    </div>
                    <h2 class="text-4xl font-black text-gray-900 dark:text-white">
                        Welcome Back
                    </h2>
                    <p class="mt-2 text-lg text-gray-600 dark:text-gray-400">
                        Sign in to your admin account
                    </p>
                </div>

                <!-- Login Form -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 border-2 border-gray-100 dark:border-gray-700 transition-colors">
                    <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-6" x-data="{ loginType: 'email' }">
                        @csrf
                        <!-- Dynamic Login Type Switch -->
                        <div class="flex justify-center mb-6">
                            <button type="button" @click="loginType = 'email'" :class="loginType === 'email' ? 'bg-green-100 text-green-700 font-bold' : 'bg-gray-100 text-gray-500'" class="px-4 py-2 rounded-l-xl border border-gray-300 focus:outline-none">Email</button>
                            <button type="button" @click="loginType = 'phone'" :class="loginType === 'phone' ? 'bg-green-100 text-green-700 font-bold' : 'bg-gray-100 text-gray-500'" class="px-4 py-2 rounded-r-xl border border-gray-300 focus:outline-none">Phone</button>
                        </div>
                        <!-- Email Field -->
                        <div x-show="loginType === 'email'" class="transition-all">
                            <label for="email" class="block text-sm font-bold text-gray-900 dark:text-white mb-2">
                                Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400 dark:text-gray-500"></i>
                                </div>
                                <input id="email" 
                                       name="email" 
                                       type="email" 
                                       :required="loginType === 'email'"
                                       value="{{ old('email') }}"
                                       class="block w-full pl-11 pr-4 py-3.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white bg-white dark:bg-gray-700 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all font-medium"
                                       placeholder="admin@rentkia.com">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Phone Field -->
                        <div x-show="loginType === 'phone'" class="transition-all">
                            <label for="phone" class="block text-sm font-bold text-gray-900 dark:text-white mb-2">
                                Phone Number
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400 dark:text-gray-500"></i>
                                </div>
                                <input id="phone" 
                                       name="phone" 
                                       type="text" 
                                       :required="loginType === 'phone'"
                                       value="{{ old('phone') }}"
                                       class="block w-full pl-11 pr-4 py-3.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white bg-white dark:bg-gray-700 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all font-medium"
                                       placeholder="+91 98765 43210">
                            </div>
                            @error('phone')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-sm font-bold text-gray-900 dark:text-white mb-2">
                                Password
                            </label>
                            <div class="relative" x-data="{ showPassword: false }">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i class="fas fa-lock text-gray-400 dark:text-gray-500"></i>
                                </div>
                                <input id="password" 
                                       name="password" 
                                       :type="showPassword ? 'text' : 'password'"
                                       required
                                       class="block w-full pl-11 pr-12 py-3.5 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white bg-white dark:bg-gray-700 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all font-medium"
                                       placeholder="••••••••">
                                <button type="button" 
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-colors">
                                    <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember-me" 
                                       name="remember" 
                                       type="checkbox" 
                                       class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 dark:border-gray-600 rounded cursor-pointer">
                                <label for="remember-me" class="ml-2 block text-sm text-gray-700 dark:text-gray-300 font-medium cursor-pointer">
                                    Remember me
                                </label>
                            </div>
                            <div class="text-sm">
                                <a href="#" class="font-bold text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 transition-colors">
                                    Forgot password?
                                </a>
                            </div>
                        </div>
                        <!-- Submit Button -->
                        <div>
                            <button type="submit" 
                                    class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-lg text-white bg-green-gradient hover:shadow-2xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:-translate-y-0.5 font-bold text-base uppercase tracking-wide">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Sign In
                            </button>
                        </div>
                    </form>

                    <!-- Divider -->
                    <div class="mt-6">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-4 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 font-medium">
                                    Need help?
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Support Links -->
                    <div class="mt-6 flex justify-center space-x-6">
                        <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 font-medium transition-colors">
                            <i class="fas fa-question-circle mr-1"></i>
                            Support
                        </a>
                        <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 font-medium transition-colors">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Privacy
                        </a>
                        <a href="#" class="text-sm text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 font-medium transition-colors">
                            <i class="fas fa-file-alt mr-1"></i>
                            Terms
                        </a>
                    </div>
                </div>

                <!-- Footer -->
                <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                    © 2026 Rentkia. All rights reserved.
                </p>
            </div>
        </div>

        <!-- Right Side - Decorative Panel (Hidden on mobile) -->
        <div class="hidden lg:flex lg:flex-1 relative overflow-hidden bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50">
            
            <!-- Decorative Circles -->
            <div class="absolute top-20 right-20 w-64 h-64 bg-green-200 rounded-full opacity-20 blur-3xl"></div>
            <div class="absolute bottom-20 left-20 w-72 h-72 bg-emerald-200 rounded-full opacity-20 blur-3xl"></div>
            
            <!-- Content -->
            <div class="relative flex flex-col justify-center items-center p-12 z-10">
                <div class="max-w-md space-y-8">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-green-gradient rounded-3xl shadow-2xl mb-6 float">
                            <i class="fas fa-building text-white text-5xl"></i>
                        </div>
                        <h1 class="text-5xl font-black mb-4 text-gray-900">
                            Rentkia Admin
                        </h1>
                        <p class="text-xl text-gray-700 font-medium">
                            Manage your properties with ease
                        </p>
                    </div>

                    <!-- Features -->
                    <div class="space-y-4 mt-12">
                        <div class="flex items-center space-x-4 bg-white rounded-xl p-4 shadow-lg border border-green-100">
                            <div class="w-12 h-12 bg-green-gradient rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                <i class="fas fa-chart-line text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Real-time Analytics</h3>
                                <p class="text-sm text-gray-600">Track performance instantly</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4 bg-white rounded-xl p-4 shadow-lg border border-green-100">
                            <div class="w-12 h-12 bg-green-gradient rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                <i class="fas fa-shield-check text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Secure & Reliable</h3>
                                <p class="text-sm text-gray-600">Your data is safe with us</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4 bg-white rounded-xl p-4 shadow-lg border border-green-100">
                            <div class="w-12 h-12 bg-green-gradient rounded-xl flex items-center justify-center flex-shrink-0 shadow-md">
                                <i class="fas fa-mobile-alt text-2xl text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">Mobile Friendly</h3>
                                <p class="text-sm text-gray-600">Access anywhere, anytime</p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 mt-12">
                        <div class="text-center bg-white rounded-xl p-4 shadow-lg border border-green-100">
                            <div class="text-3xl font-black text-green-600">500+</div>
                            <div class="text-sm text-gray-700 mt-1 font-medium">Properties</div>
                        </div>
                        <div class="text-center bg-white rounded-xl p-4 shadow-lg border border-green-100">
                            <div class="text-3xl font-black text-green-600">1.2K</div>
                            <div class="text-sm text-gray-700 mt-1 font-medium">Tenants</div>
                        </div>
                        <div class="text-center bg-white rounded-xl p-4 shadow-lg border border-green-100">
                            <div class="text-3xl font-black text-green-600">98%</div>
                            <div class="text-sm text-gray-700 mt-1 font-medium">Satisfaction</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>