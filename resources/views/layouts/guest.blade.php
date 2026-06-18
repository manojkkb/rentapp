<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.site-seo-head', ['seo' => $seo ?? []])

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --rk-emerald: #10b981;
            --rk-emerald-dark: #059669;
            --rk-teal: #14b8a6;
            --rk-mint: #6ee7b7;
            --rk-ink: #0f172a;
            --rk-slate: #64748b;
        }

        body {
            font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif;
        }

        .gradient-green {
            background: linear-gradient(135deg, #059669 0%, #10b981 45%, #34d399 100%);
        }

        .gradient-green-light {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 50%, #6ee7b7 100%);
        }

        .text-gradient {
            background: linear-gradient(135deg, #34d399 0%, #10b981 50%, #059669 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .text-gradient-gold {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.35);
        }

        .glass-dark {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .hero-mesh {
            background-color: #0b1120;
            background-image:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(16, 185, 129, 0.35) 0%, transparent 55%),
                radial-gradient(ellipse 60% 50% at 80% 20%, rgba(20, 184, 166, 0.25) 0%, transparent 50%),
                radial-gradient(ellipse 50% 40% at 60% 80%, rgba(52, 211, 153, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse 40% 30% at 10% 70%, rgba(5, 150, 105, 0.2) 0%, transparent 50%);
        }

        .grid-pattern {
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .hover-lift {
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.35s ease;
        }

        .hover-lift:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 48px -12px rgba(16, 185, 129, 0.25);
        }

        .floating {
            animation: floating 4s ease-in-out infinite;
        }

        .floating-delayed {
            animation: floating 4s ease-in-out 1.5s infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-14px); }
        }

        .fade-up {
            animation: fadeUp 0.8s ease-out both;
        }

        .fade-up-delay-1 { animation-delay: 0.15s; }
        .fade-up-delay-2 { animation-delay: 0.3s; }
        .fade-up-delay-3 { animation-delay: 0.45s; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(28px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .marquee-track {
            animation: marquee 32s linear infinite;
        }

        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .shine {
            position: relative;
            overflow: hidden;
        }

        .shine::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg, transparent 40%, rgba(255,255,255,0.12) 50%, transparent 60%);
            transform: translateX(-100%);
            animation: shine 4s ease-in-out infinite;
        }

        @keyframes shine {
            0%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: blob 8s ease-in-out infinite;
        }

        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(24px, -40px) scale(1.08); }
            66% { transform: translate(-16px, 24px) scale(0.94); }
        }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #10b981; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #059669; }

        .reveal {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>

    @yield('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

    @include('partials.frontend-header', ['headerSolid' => trim($__env->yieldContent('headerSolid')) === '1'])

    @yield('content')

    @include('partials.frontend-footer')

    @stack('scripts')
    @yield('scripts')

    @livewireScriptConfig
</body>
</html>
