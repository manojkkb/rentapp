<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="{{ $theme['accent'] }}">
    <title>{{ $vendor->name }} — {{ __('vendor.online_store') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --store-accent: {{ $theme['accent'] }};
            --store-accent-light: {{ $theme['accent_light'] }};
            --store-accent-soft: {{ $theme['accent_soft'] }};
            --store-accent-dark: {{ $theme['accent_dark'] }};
            --store-accent-ring: {{ $theme['accent_ring'] }};
            --store-accent-rgb: {{ $theme['accent_rgb']['r'] }}, {{ $theme['accent_rgb']['g'] }}, {{ $theme['accent_rgb']['b'] }};
        }
        .store-accent-bg { background-color: var(--store-accent); }
        .store-accent-bg-light { background-color: var(--store-accent-light); }
        .store-accent-bg-soft { background-color: var(--store-accent-soft); }
        .store-accent-text { color: var(--store-accent); }
        .store-accent-text-dark { color: var(--store-accent-dark); }
        .store-accent-border { border-color: var(--store-accent); }
        .store-accent-ring:focus { box-shadow: 0 0 0 3px var(--store-accent-ring); }
        .store-btn-primary {
            background-color: var(--store-accent);
            color: #fff;
        }
        .store-btn-primary:hover { background-color: var(--store-accent-dark); }
        .store-hero-gradient { background: {{ $theme['hero_gradient'] }}; }
        .store-chip-active {
            background-color: var(--store-accent);
            color: #fff;
            border-color: var(--store-accent);
        }
        .store-price { color: var(--store-accent-dark); }
        [x-cloak] { display: none !important; }
        .store-hide-scrollbar::-webkit-scrollbar { display: none; }
        .store-hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @stack('styles')
</head>
<body class="min-h-dvh bg-gray-50 text-gray-900 antialiased {{ $theme['classes']['page'] }}">
    @yield('content')
    @stack('scripts')
</body>
</html>
