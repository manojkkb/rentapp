<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#059669" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#047857" media="(prefers-color-scheme: dark)">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('pwa.vendor.short_name', 'Rentkia App') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="{{ config('pwa.vendor.short_name', 'Rentkia App') }}">
    <link rel="manifest" href="{{ route('vendor.manifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('vendor/icons/icon-192.png') }}">
    <title>@yield('title', 'Rentkia')</title>
    
    <!-- Vite Assets (includes Tailwind CSS, Alpine.js, Font Awesome, and Inter Font) -->
    @stack('vite-before-app')
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
 
    <style>
        [x-cloak] { display: none !important; }

        /* Vendor panel — emerald theme defaults */
        .vendor-panel input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([type="hidden"]):not([type="range"]):focus,
        .vendor-panel select:focus,
        .vendor-panel textarea:focus {
            --tw-ring-color: rgb(16 185 129 / 0.5);
        }
    </style>
 
    @yield('styles')
</head>
<body class="vendor-panel h-full overflow-hidden bg-gray-50"
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
    <div id="vendor-nav-progress" class="pointer-events-none fixed inset-x-0 top-0 z-[100] h-0.5 opacity-0 transition-opacity duration-200" aria-hidden="true">
        <div id="vendor-nav-progress-bar" class="h-full w-0 bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)] transition-[width] duration-300 ease-out"></div>
    </div>
    
    <div class="flex h-full min-h-0 overflow-hidden">
        <!-- Sidebar -->
        @include('vendor.layouts.sidebar')
        
        <!-- Main Content Area -->
        <div class="flex min-h-0 flex-1 flex-col overflow-hidden">
            <!-- Navbar -->
            @include('vendor.layouts.navbar')
            
            <!-- Main Content -->
            <main class="min-h-0 flex-1 overflow-y-auto overflow-x-hidden bg-gray-50 p-3 sm:p-4 md:p-6 @yield('main_bottom_class', 'pb-20 md:pb-6')">
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Mobile Bottom Navigation -->
    @include('vendor.layouts.mobile-nav')

    @stack('modals')

    @php
        $pwaHomePath = parse_url(route('vendor.home', [], false), PHP_URL_PATH) ?: '/vendor/home';
        $pwaSwScope = rtrim(str_replace('\\', '/', dirname($pwaHomePath)), '/') . '/';
    @endphp
    <script>
        (function () {
            if (!('serviceWorker' in navigator)) return;
            var swUrl = @json(asset('vendor/sw.js'));
            var scope = @json($pwaSwScope);
            window.addEventListener('load', function () {
                navigator.serviceWorker.register(swUrl, { scope: scope }).catch(function () {});
            });
        })();
    </script>
    
    @livewireScriptConfig
    @yield('scripts')
</body>
</html>