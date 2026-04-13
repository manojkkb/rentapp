<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vendor Login - Rentkia</title>
    
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
        
        input {
            max-width: 100%;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        }
        
        .btn-gradient:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .animated-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(40px);
            opacity: 0.6;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
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
        
        .otp-input {
            width: 3rem;
            height: 3.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937 !important;
            background-color: #ffffff;
        }
        
        .otp-container {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }
        
        /* Tablet and smaller devices */
        @media (max-width: 768px) {
            .glass-effect {
                padding: 1.25rem !important;
            }
        }
        
        /* Mobile devices */
        @media (max-width: 640px) {
            .otp-input {
                width: 2.5rem !important;
                height: 3rem !important;
                font-size: 1.25rem !important;
                color: #1f2937 !important;
                background-color: #ffffff !important;
            }
            
            .otp-container {
                gap: 0.5rem !important;
            }
            
            input[type="text"] {
                font-size: 1rem !important;
            }
            
            .glass-effect {
                padding: 0.875rem !important;
                max-width: calc(100vw - 1rem) !important;
                margin: 0 auto !important;
                width: 100% !important;
            }
            
            body {
                padding: 0.5rem !important;
                padding-top: 0.875rem !important;
                align-items: flex-start !important;
            }
            
            .animated-shape {
                display: none;
            }
            
            .max-w-md {
                max-width: 100% !important;
                width: 100% !important;
            }
            
            .space-x-4 > * + * {
                margin-left: 0.75rem !important;
            }
            
            .mb-8 {
                margin-bottom: 1rem !important;
            }
            
            h2 {
                font-size: 1.5rem !important;
            }
            
            .step-indicator-text {
                display: none !important;
            }
            
            .space-y-6 > * + * {
                margin-top: 1.25rem !important;
            }
            
            .mb-6 {
                margin-bottom: 1.25rem !important;
            }
        }
        
        /* Extra small devices (iPhone SE, etc.) */
        @media (max-width: 400px) {
            .otp-input {
                width: 2.2rem !important;
                height: 2.7rem !important;
                font-size: 1.125rem !important;
                color: #1f2937 !important;
                background-color: #ffffff !important;
            }
            
            .otp-container {
                gap: 0.35rem !important;
                flex-wrap: nowrap !important;
            }
            
            .glass-effect {
                padding: 0.75rem !important;
                max-width: calc(100vw - 0.75rem) !important;
            }
            
            body {
                padding: 0.375rem !important;
                padding-top: 0.5rem !important;
            }
            
            h2 {
                font-size: 1.25rem !important;
                margin-bottom: 0.5rem !important;
            }
            
            .text-2xl {
                font-size: 1.25rem !important;
            }
            
            
            .space-x-4 > * + * {
                margin-left: 0.375rem !important;
            }
            
            button {
                font-size: 0.875rem !important;
                padding-top: 0.625rem !important;
                padding-bottom: 0.625rem !important;
            }
            
            .mb-8 {
                margin-bottom: 0.625rem !important;
            }
            
            .step-indicator {
                transform: scale(0.85);
                margin-bottom: 0.75rem !important;
            }
            
            .space-y-6 > * + * {
                margin-top: 0.875rem !important;
            }
            
            .mb-6 {
                margin-bottom: 0.875rem !important;
            }
            
            label {
                font-size: 0.8125rem !important;
                margin-bottom: 0.375rem !important;
            }
            
            p {
                font-size: 0.75rem !important;
            }
        }
        
        /* iPhone SE and similar (375px) */
        @media (max-width: 375px) {
            .otp-input {
                width: 2.1rem !important;
                height: 2.6rem !important;
                font-size: 1.0625rem !important;
            }
            
            .otp-container {
                gap: 0.3rem !important;
            }
            
            .glass-effect {
                padding: 0.625rem !important;
                max-width: calc(100vw - 0.5rem) !important;
            }
            
            body {
                padding: 0.25rem !important;
                padding-top: 0.375rem !important;
            }
            
            h2 {
                font-size: 1.125rem !important;
            }
            
            input[type="text"] {
                font-size: 0.8125rem !important;
            }
            
            button {
                font-size: 0.8125rem !important;
            }
            
            .step-indicator {
                transform: scale(0.8);
            }
        }
        
        /* Landscape mode on small devices */
        @media (max-width: 896px) and (max-height: 500px) and (orientation: landscape) {
            body {
                padding-top: 0.5rem !important;
            }
            
            .mb-8 {
                margin-bottom: 0.5rem !important;
            }
            
            .glass-effect {
                padding: 1rem !important;
            }
            
            .space-y-6 > * + * {
                margin-top: 1rem !important;
            }
        }
        
        @keyframes pulse-ring {
            0% { transform: scale(0.95); opacity: 1; }
            50% { transform: scale(1); opacity: 0.7; }
            100% { transform: scale(0.95); opacity: 1; }
        }
        
        .pulse-animation {
            animation: pulse-ring 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4 relative overflow-hidden" 
      x-data="otpLogin()"
      x-init="init()">
    
    <!-- Animated Background Shapes -->
    <div class="animated-shape shape-1"></div>
    <div class="animated-shape shape-2"></div>
    
    <!-- Login Container -->
    <div class="w-full max-w-md relative z-10">
        
        <!-- Back Button -->
        <div class="text-left mb-6 sm:mb-8 px-2 sm:px-0">
            <a href="{{ route('welcome') }}" class="inline-flex items-center text-white hover:text-green-100 transition-colors group text-sm sm:text-base">
                <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
                <span class="font-medium">Back to Home</span>
            </a>
        </div>
        
        <!-- Login Form Card -->
        <div class="glass-effect rounded-2xl p-6 sm:p-8 shadow-2xl">
            
            <!-- Step Indicator -->
            <div class="flex items-center justify-center mb-8 step-indicator">
                <div class="flex items-center space-x-3 sm:space-x-4">
                    <!-- Step 1 -->
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full transition-all"
                             :class="step === 1 ? 'bg-green-600 text-white' : 'bg-green-100 text-green-600'">
                            <i class="fas fa-mobile-alt text-sm"></i>
                        </div>
                        <span class="ml-2 text-sm font-medium step-indicator-text" 
                              :class="step === 1 ? 'text-green-600' : 'text-gray-400'">Mobile</span>
                    </div>
                    
                    <!-- Arrow -->
                    <i class="fas fa-arrow-right text-gray-400 text-sm"></i>
                    
                    <!-- Step 2 -->
                    <div class="flex items-center">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full transition-all"
                             :class="step === 2 ? 'bg-green-600 text-white' : 'bg-green-100 text-green-600'">
                            <i class="fas fa-key text-sm"></i>
                        </div>
                        <span class="ml-2 text-sm font-medium step-indicator-text" 
                              :class="step === 2 ? 'text-green-600' : 'text-gray-400'">OTP</span>
                    </div>
                </div>
            </div>
            
            <!-- Error/Success Messages -->
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
            
            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded" x-data x-init="setTimeout(() => $el.remove(), 5000)">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <p class="text-red-700 text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            @endif
            
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded" x-data x-init="setTimeout(() => $el.remove(), 5000)">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <p class="text-green-700 text-sm">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            
            <!-- Step 1: Mobile Number -->
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-4"
                 x-transition:enter-end="opacity-100 transform translate-x-0">
                
                <div class="text-center mb-6">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-2">Enter Mobile Number</h2>
                    <p class="text-sm sm:text-base text-gray-600">We'll send you a verification code</p>
                </div>
                
                <form @submit.prevent="sendOTP" class="space-y-6">
                    @csrf
                    
                    <!-- Mobile Number Field -->
                    <div>
                        <label for="mobile" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-mobile-alt mr-2 text-green-600"></i>Mobile Number
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 sm:pl-4 text-gray-500 font-semibold text-sm sm:text-base">
                                +91
                            </span>
                            <input 
                                type="text" 
                                x-model="mobile"
                                id="mobile" 
                                placeholder="10-digit mobile number"
                                class="input-focus w-full pl-12 sm:pl-14 pr-3 sm:pr-4 py-3 sm:py-3.5 border-2 border-gray-200 rounded-lg focus:outline-none transition-all text-base sm:text-lg"
                                maxlength="10"
                                pattern="[0-9]{10}"
                                inputmode="numeric"
                                required
                                autofocus
                                @input="mobile = mobile.replace(/[^0-9]/g, '')"
                            >
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            <i class="fas fa-info-circle mr-1"></i><span class="hidden sm:inline">Enter your registered</span> 10-digit mobile number
                        </p>
                    </div>
                    
                    <!-- Send OTP Button -->
                    <button 
                        type="submit"
                        :disabled="loading || mobile.length !== 10"
                        class="btn-gradient w-full text-white font-semibold py-3 sm:py-3.5 rounded-lg focus:outline-none focus:ring-4 focus:ring-green-300 flex items-center justify-center text-sm sm:text-base"
                    >
                        <span x-show="!loading" class="flex items-center">
                            <i class="fas fa-paper-plane mr-2"></i>Send OTP
                        </span>
                        <span x-show="loading" class="flex items-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Sending...
                        </span>
                    </button>
                </form>
            </div>
            
            <!-- Step 2: OTP Verification -->
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-4"
                 x-transition:enter-end="opacity-100 transform translate-x-0">
                
                <div class="text-center mb-6">
                    <p class="text-sm sm:text-base text-gray-600">We've sent a 6-digit code to</p>
                    <p class="text-base sm:text-lg text-green-600 font-semibold">+91 <span x-text="mobile"></span></p>
                    <button @click="changeNumber" class="text-xs sm:text-sm text-green-600 hover:text-green-800 mt-2">
                        <i class="fas fa-edit mr-1"></i>Change Number
                    </button>
                </div>
                
                <form @submit.prevent="verifyOTP" class="space-y-6">
                    @csrf
                    
                    <!-- OTP Input Fields -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3 text-center">
                            Enter 6-Digit OTP
                        </label>
                        <div class="otp-container">
                            <template x-for="i in 6" :key="i">
                                <input 
                                    type="text" 
                                    maxlength="1"
                                    pattern="[0-9]"
                                    inputmode="numeric"
                                    class="otp-input input-focus border-2 border-gray-200 rounded-lg focus:outline-none transition-all"
                                    :id="'otp' + i"
                                    @input="handleOTPInput($event, i)"
                                    @keydown="handleOTPKeydown($event, i)"
                                    @paste="handleOTPPaste($event)"
                                />
                            </template>
                        </div>
                    </div>
                    
                    <!-- Resend OTP -->
                    <div class="text-center">
                        <p class="text-xs sm:text-sm text-gray-600 mb-2">Didn't receive the code?</p>
                        <button 
                            type="button"
                            @click="resendOTP"
                            :disabled="resendTimer > 0 || loading"
                            class="text-xs sm:text-sm font-semibold transition-colors"
                            :class="resendTimer > 0 ? 'text-gray-400 cursor-not-allowed' : 'text-green-600 hover:text-green-800'"
                        >
                            <span x-show="resendTimer > 0">
                                <i class="fas fa-clock mr-1"></i>Resend in <span x-text="resendTimer"></span>s
                            </span>
                            <span x-show="resendTimer === 0">
                                <i class="fas fa-redo mr-1"></i>Resend OTP
                            </span>
                        </button>
                    </div>
                    
                    <!-- Verify OTP Button -->
                    <button 
                        type="submit"
                        :disabled="loading || otp.length !== 6"
                        class="btn-gradient w-full text-white font-semibold py-3 sm:py-3.5 rounded-lg focus:outline-none focus:ring-4 focus:ring-green-300 flex items-center justify-center text-sm sm:text-base"
                    >
                        <span x-show="!loading" class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>Verify & Login
                        </span>
                        <span x-show="loading" class="flex items-center">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Verifying...
                        </span>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Footer Links -->
        <div class="mt-6 sm:mt-8 text-center text-white text-xs sm:text-sm px-4">
            <p class="mb-2">© 2026 Rentkia. All rights reserved.</p>
            <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-4">
                <a href="#" class="hover:underline">Privacy Policy</a>
                <span class="hidden sm:inline">•</span>
                <a href="#" class="hover:underline">Terms of Service</a>
                <span class="hidden sm:inline">•</span>
                <a href="#" class="hover:underline">Support</a>
            </div>
        </div>
    </div>
    
    <!-- Alpine.js OTP Login Logic -->
    <script>
        function otpLogin() {
            return {
                step: 1,
                mobile: '{{ old('mobile', '') }}',
                otp: '',
                loading: false,
                resendTimer: 0,
                resendInterval: null,
                
                init() {
                    // Check if there's a session OTP sent flag
                    @if(session('otp_sent'))
                        this.step = 2;
                        this.mobile = '{{ session('otp_mobile', '') }}';
                        this.startResendTimer();
                    @endif
                },
                
                async sendOTP() {
                    if (this.mobile.length !== 10) return;
                    
                    this.loading = true;
                    
                    try {
                        const response = await fetch('{{ route('vendor.otp.send') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ mobile: this.mobile })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.step = 2;
                            this.startResendTimer();
                            this.showNotification('OTP sent successfully!', 'success');
                            
                            // Focus first OTP input
                            setTimeout(() => document.getElementById('otp1')?.focus(), 100);
                        } else {
                            this.showNotification(data.message || 'Failed to send OTP', 'error');
                        }
                    } catch (error) {
                        this.showNotification('Network error. Please try again.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },
                
                async verifyOTP() {
                    if (this.otp.length !== 6) return;
                    
                    this.loading = true;
                    
                    try {
                        const response = await fetch('{{ route('vendor.otp.verify') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ 
                                mobile: this.mobile,
                                otp: this.otp
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.showNotification('Login successful! Redirecting...', 'success');
                            setTimeout(() => {
                                window.location.href = data.redirect || '{{ route('vendor.home') }}';
                            }, 1000);
                        } else {
                            this.showNotification(data.message || 'Invalid OTP', 'error');
                            this.clearOTP();
                        }
                    } catch (error) {
                        this.showNotification('Network error. Please try again.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },
                
                async resendOTP() {
                    if (this.resendTimer > 0) return;
                    
                    await this.sendOTP();
                },
                
                changeNumber() {
                    this.step = 1;
                    this.otp = '';
                    this.clearResendTimer();
                    setTimeout(() => document.getElementById('mobile')?.focus(), 100);
                },
                
                handleOTPInput(event, index) {
                    const input = event.target;
                    const value = input.value.replace(/[^0-9]/g, '');
                    
                    input.value = value;
                    
                    // Update OTP string
                    this.updateOTP();
                    
                    // Move to next input if value entered
                    if (value && index < 6) {
                        const nextInput = document.getElementById('otp' + (index + 1));
                        nextInput?.focus();
                    }
                },
                
                handleOTPKeydown(event, index) {
                    // Handle backspace
                    if (event.key === 'Backspace' && !event.target.value && index > 1) {
                        const prevInput = document.getElementById('otp' + (index - 1));
                        prevInput?.focus();
                    }
                },
                
                handleOTPPaste(event) {
                    event.preventDefault();
                    const pastedData = event.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                    
                    for (let i = 0; i < pastedData.length; i++) {
                        const input = document.getElementById('otp' + (i + 1));
                        if (input) input.value = pastedData[i];
                    }
                    
                    this.updateOTP();
                    
                    // Focus last filled input or first empty
                    const nextIndex = Math.min(pastedData.length, 5) + 1;
                    document.getElementById('otp' + nextIndex)?.focus();
                },
                
                updateOTP() {
                    let otpValue = '';
                    for (let i = 1; i <= 6; i++) {
                        const input = document.getElementById('otp' + i);
                        otpValue += input?.value || '';
                    }
                    this.otp = otpValue;
                    
                    // Auto-submit when all 6 digits are entered
                    if (otpValue.length === 6) {
                        setTimeout(() => this.verifyOTP(), 300);
                    }
                },
                
                clearOTP() {
                    for (let i = 1; i <= 6; i++) {
                        const input = document.getElementById('otp' + i);
                        if (input) input.value = '';
                    }
                    this.otp = '';
                    document.getElementById('otp1')?.focus();
                },
                
                startResendTimer() {
                    this.resendTimer = 60;
                    this.clearResendTimer();
                    
                    this.resendInterval = setInterval(() => {
                        this.resendTimer--;
                        if (this.resendTimer <= 0) {
                            this.clearResendTimer();
                        }
                    }, 1000);
                },
                
                clearResendTimer() {
                    if (this.resendInterval) {
                        clearInterval(this.resendInterval);
                        this.resendInterval = null;
                    }
                },
                
                showNotification(message, type) {
                    // Simple notification system (you can enhance this)
                    const container = document.createElement('div');
                    container.className = `fixed top-4 right-4 z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white px-6 py-3 rounded-lg shadow-lg`;
                    container.innerHTML = `
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                            <span>${message}</span>
                        </div>
                    `;
                    document.body.appendChild(container);
                    
                    setTimeout(() => container.remove(), 3000);
                }
            }
        }
    </script>
</body>
</html>
