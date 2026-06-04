<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#10b981">
    <title>Create Vendor - Rentkia</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            max-width: 100vw;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.2);
            max-width: 100%;
        }

        .input-focus:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.12);
        }

        .form-field {
            width: 100%;
            min-height: 3rem;
            font-size: 1rem;
            -webkit-appearance: none;
            appearance: none;
        }

        @supports (padding: max(0px)) {
            .safe-x {
                padding-left: max(0.75rem, env(safe-area-inset-left));
                padding-right: max(0.75rem, env(safe-area-inset-right));
            }
            .safe-bottom {
                padding-bottom: max(1rem, env(safe-area-inset-bottom));
            }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-16px); }
        }

        .animated-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.12;
            animation: float 6s ease-in-out infinite;
            pointer-events: none;
        }

        .shape-1 {
            width: 280px;
            height: 280px;
            background: #10b981;
            top: -80px;
            left: -80px;
        }

        .shape-2 {
            width: 180px;
            height: 180px;
            background: #34d399;
            bottom: -40px;
            right: -40px;
            animation-delay: 2s;
        }

        @media (max-width: 640px) {
            .animated-shape { display: none; }
            .glass-effect {
                padding: 1rem !important;
                border-radius: 1rem !important;
            }
            .page-title { font-size: 1.375rem !important; line-height: 1.3 !important; }
            .form-stack > * + * { margin-top: 1.125rem !important; }
        }

        @media (max-width: 400px) {
            .glass-effect { padding: 0.875rem !important; }
            .page-title { font-size: 1.25rem !important; }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen min-h-[100dvh] flex flex-col sm:items-center sm:justify-center safe-x safe-bottom relative overflow-x-hidden">

    <div class="animated-shape shape-1" aria-hidden="true"></div>
    <div class="animated-shape shape-2" aria-hidden="true"></div>

    <main class="w-full max-w-lg mx-auto relative z-10 flex-1 flex flex-col py-3 sm:py-4 px-0 sm:px-4">

        <div class="mb-4 sm:mb-6 shrink-0">
            @if(Session::has('vendor_id'))
                <a href="{{ route('vendor.home') }}" class="inline-flex items-center min-h-[44px] text-white hover:text-green-100 transition-colors group text-sm sm:text-base">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-0.5 transition-transform" aria-hidden="true"></i>
                    <span class="font-medium">Back to Home</span>
                </a>
            @else
                <a href="{{ route('vendor.login') }}" class="inline-flex items-center min-h-[44px] text-white hover:text-green-100 transition-colors group text-sm sm:text-base">
                    <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-0.5 transition-transform" aria-hidden="true"></i>
                    <span class="font-medium">Back to Login</span>
                </a>
            @endif
        </div>

        <div class="glass-effect rounded-2xl p-5 sm:p-8 shadow-2xl flex-1 sm:flex-none w-full">

            <header class="text-center mb-6 sm:mb-8">
                <div class="flex justify-center mb-3 sm:mb-4">
                    <div class="bg-green-600 rounded-full p-3 sm:p-4">
                        <i class="fas fa-store-alt text-white text-2xl sm:text-3xl" aria-hidden="true"></i>
                    </div>
                </div>
                <h1 class="page-title text-2xl sm:text-3xl font-bold text-gray-800 mb-1.5 sm:mb-2 px-1">
                    Create Your Vendor Profile
                </h1>
                <p class="text-gray-600 text-sm sm:text-base px-2 leading-snug">
                    Add your business details to get started
                </p>
            </header>

            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-3 sm:p-4 mb-4 sm:mb-6 rounded" role="status">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 shrink-0" aria-hidden="true"></i>
                        <p class="text-green-700 text-sm leading-relaxed">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-3 sm:p-4 mb-4 sm:mb-6 rounded" role="alert">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5 shrink-0" aria-hidden="true"></i>
                        <div class="min-w-0">
                            @foreach ($errors->all() as $error)
                                <p class="text-red-700 text-sm leading-relaxed">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('vendor.create.submit') }}" method="POST" class="w-full min-w-0">
                @csrf

                <div class="form-stack space-y-5 sm:space-y-6">

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                            <i class="fas fa-store mr-1.5 text-green-600" aria-hidden="true"></i>
                            Business Name <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Your business name"
                            autocomplete="organization"
                            class="form-field input-focus px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                            required
                            autofocus
                        >
                        @error('name')
                            <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="business_category_id" class="block text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                            <i class="fas fa-briefcase mr-1.5 text-green-600" aria-hidden="true"></i>
                            Business Category <span class="text-red-500" aria-label="required">*</span>
                        </label>
                        <select
                            id="business_category_id"
                            name="business_category_id"
                            class="form-field input-focus px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all bg-white"
                            required
                        >
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('business_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('business_category_id')
                            <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="owner_name" class="block text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                            <i class="fas fa-user mr-1.5 text-green-600" aria-hidden="true"></i>
                            Owner / Contact Name
                            <span class="text-gray-400 font-normal">(optional)</span>
                        </label>
                        <input
                            type="text"
                            id="owner_name"
                            name="owner_name"
                            value="{{ old('owner_name') }}"
                            placeholder="Contact person name"
                            autocomplete="name"
                            class="form-field input-focus px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none transition-all"
                        >
                        @error('owner_name')
                            <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full min-h-[48px] bg-green-600 hover:bg-green-700 active:bg-green-800 text-white font-semibold py-3.5 px-4 rounded-xl transition-colors shadow-lg flex items-center justify-center text-base touch-manipulation"
                    >
                        <i class="fas fa-check-circle mr-2" aria-hidden="true"></i>
                        Create Vendor Profile
                    </button>

                </div>
            </form>

            @php
                $hasVendors = auth()->check() && \App\Models\VendorUser::query()
                    ->where('user_id', auth()->id())
                    ->where('is_active', true)
                    ->exists();
            @endphp

            @if($hasVendors)
                <div class="mt-5 sm:mt-6 text-center pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-600 mb-2">Already have a vendor?</p>
                    <a href="{{ route('vendor.select') }}" class="inline-flex items-center justify-center min-h-[44px] text-green-600 hover:text-green-800 font-semibold transition-colors text-sm sm:text-base touch-manipulation">
                        <i class="fas fa-arrow-left mr-2" aria-hidden="true"></i>
                        Back to Vendor Selection
                    </a>
                </div>
            @endif
        </div>

        <footer class="mt-6 sm:mt-8 text-center text-white text-xs sm:text-sm shrink-0 pb-2">
            <p class="mb-2 opacity-90">© 2026 Rentkia. All rights reserved.</p>
            <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1">
                <a href="#" class="hover:underline min-h-[44px] inline-flex items-center px-1">Privacy</a>
                <a href="#" class="hover:underline min-h-[44px] inline-flex items-center px-1">Terms</a>
                <a href="#" class="hover:underline min-h-[44px] inline-flex items-center px-1">Support</a>
            </div>
        </footer>
    </main>
</body>
</html>
