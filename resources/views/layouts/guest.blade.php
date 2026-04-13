<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Rentkia - Rent Anything, Anytime')</title>
    
    <!-- Vite Assets (includes Tailwind CSS, Alpine.js, Font Awesome, and Poppins Font) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        .gradient-green {
            background: linear-gradient(135deg, #10b981 0%, #34d399 50%, #6ee7b7 100%);
        }
        
        .gradient-green-light {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 50%, #6ee7b7 100%);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .hover-scale {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .hover-scale:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.2);
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .fade-in {
            animation: fadeIn 1s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(70px);
            opacity: 0.5;
            animation: blob 7s infinite;
        }
        
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        
        .scroll-smooth {
            scroll-behavior: smooth;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #10b981;
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #059669;
        }
    </style>
    
    @yield('styles')
</head>
<body class="scroll-smooth bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50">
    
    @include('partials.frontend-header')
    
    @yield('content')
    
    @include('partials.frontend-footer')
    
    @yield('scripts')
</body>
</html>
