<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>

    @stack('vite-before-app')
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }

        * { font-family: 'Inter', sans-serif; }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        .dark ::-webkit-scrollbar-track { background: #1f2937; }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
            border-radius: 10px;
        }

        .bg-green-gradient {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        /* Sidebar visible on desktop even before Alpine loads */
        @media (min-width: 768px) {
            .admin-sidebar { transform: translateX(0) !important; }
        }
    </style>

    @yield('styles')
</head>
<body
    class="h-full overflow-hidden bg-gray-50 dark:bg-gray-900"
    x-data="{
        darkMode: localStorage.getItem('darkMode') === 'true',
        sidebarOpen: false,
        toggleSidebar() { this.sidebarOpen = !this.sidebarOpen },
        toggleDark() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('darkMode', this.darkMode);
        }
    }"
    x-init="sidebarOpen = window.innerWidth < 768 ? false : true"
    :class="{ 'dark': darkMode }"
    @resize.window="if (window.innerWidth >= 768) { sidebarOpen = true }"
>
    {{-- Mobile overlay only; hidden by default so page is never blocked without JS --}}
    <div
        class="fixed inset-0 z-20 hidden bg-black/50 md:hidden"
        :class="sidebarOpen ? 'block' : 'hidden'"
        @click="sidebarOpen = false"
        aria-hidden="true"
    ></div>

    <div class="flex h-full min-h-0 overflow-hidden">
        @include('admin.layouts.sidebar')

        <div class="relative z-10 flex min-h-0 min-w-0 flex-1 flex-col overflow-hidden">
            @include('admin.layouts.navbar')

            <main class="min-h-0 flex-1 overflow-x-hidden @yield('main_class', 'overflow-y-auto p-4 sm:p-6')">
                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
</body>
</html>
