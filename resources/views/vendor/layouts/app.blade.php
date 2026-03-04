<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vendor Home - RentApp')</title>
    
    <!-- Vite Assets (includes Tailwind CSS, Alpine.js, Font Awesome, and Inter Font) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
 
    <style>
        [x-cloak] { display: none !important; }
    </style>
 
    @yield('styles')
</head>
<body class="bg-gray-50 min-h-screen" 
      x-data="{ 
          sidebarOpen: localStorage.getItem('sidebarOpen') !== null 
              ? localStorage.getItem('sidebarOpen') === 'true' 
              : window.innerWidth >= 768,
          toggleSidebar() {
              this.sidebarOpen = !this.sidebarOpen;
              localStorage.setItem('sidebarOpen', this.sidebarOpen);
          }
      }"
      @resize.window="if (window.innerWidth < 768 && sidebarOpen) { sidebarOpen = false; localStorage.setItem('sidebarOpen', false); }">
    
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('vendor.layouts.sidebar')
        
        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Navbar -->
            @include('vendor.layouts.navbar')
            
            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-gray-50 pb-20 md:pb-6">
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Mobile Bottom Navigation -->
    @include('vendor.layouts.mobile-nav')
    
    @yield('scripts')
</body>
</html>