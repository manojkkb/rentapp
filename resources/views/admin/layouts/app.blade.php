<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>
    
    <!-- Vite Assets (includes Tailwind CSS, Alpine.js, Font Awesome, and Inter Font) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        
        /* Custom scrollbar - Light Theme */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f8fafc;
        }
        .dark ::-webkit-scrollbar-track {
            background: #1f2937;
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #059669 0%, #10b981 100%);
        }
        
        /* Smooth transitions */
        * {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        /* Removed shadow utilities for flat UI */
        
        /* Green gradient */
        .bg-green-gradient {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
    </style>
    
    @yield('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen transition-colors" x-data="{ sidebarOpen: true }">
    
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('admin.layouts.sidebar')
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Navbar -->
            @include('admin.layouts.navbar')
            
            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>
    
    @yield('scripts')
</body>
</html>