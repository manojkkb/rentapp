@extends('layouts.guest')

@section('headerSolid', '1')

@section('content')
    <section class="relative overflow-hidden hero-mesh grid-pattern pt-28 pb-12 lg:pt-32 lg:pb-16 px-4 sm:px-6 lg:px-8">
        <div class="blob bg-emerald-500 w-[380px] h-[380px] -top-20 -right-20 opacity-25"></div>
        <div class="relative z-10 max-w-3xl mx-auto text-center">
            <span class="inline-block text-emerald-300 font-semibold text-sm uppercase tracking-widest mb-3">{{ $pageBadge ?? 'Rentkia' }}</span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight">
                @if(! empty($pageTitleHtml))
                    {!! $pageTitleHtml !!}
                @else
                    {{ $pageTitle ?? 'Rentkia' }}
                @endif
            </h1>
            @if(! empty($pageSubtitle))
                <p class="mt-4 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto">{{ $pageSubtitle }}</p>
            @endif
        </div>
    </section>

    <section class="px-4 sm:px-6 lg:px-8 pb-16 lg:pb-24 bg-slate-50">
        <div class="@if(($contentWidth ?? '') === '6xl') max-w-6xl @else max-w-5xl @endif mx-auto pt-2 lg:pt-4">
            @yield('page-content')
        </div>

        <div class="max-w-5xl mx-auto mt-10 flex flex-wrap gap-4 text-sm">
            <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-emerald-700 transition">
                <i class="fas fa-arrow-left text-xs"></i>
                Back to home
            </a>
        </div>
    </section>
@endsection
