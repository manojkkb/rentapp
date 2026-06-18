@extends('layouts.guest')

@section('headerSolid', '1')

@section('content')
    <section class="relative overflow-hidden hero-mesh grid-pattern pt-28 pb-10 lg:pt-32 lg:pb-12 px-4 sm:px-6 lg:px-8">
        <div class="relative z-10 max-w-3xl mx-auto text-center">
            <span class="inline-block text-emerald-300 font-semibold text-sm uppercase tracking-widest mb-3">{{ \App\Support\SiteSeo::BRAND }}</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">{{ $title }}</h1>
            <p class="mt-3 text-sm text-slate-400">Last updated: {{ $updated ?? 'June 18, 2026' }}</p>
        </div>
    </section>

    <section class="px-4 sm:px-6 lg:px-8 pb-16 lg:pb-24 bg-slate-50">
        <article class="max-w-3xl mx-auto -mt-6 rounded-2xl border border-slate-200 bg-white p-6 sm:p-10 shadow-sm prose prose-slate prose-headings:font-bold prose-headings:text-slate-900 prose-a:text-emerald-600 hover:prose-a:text-emerald-700 max-w-none">
            @yield('legal-content')
        </article>

        <div class="max-w-3xl mx-auto mt-8 flex flex-wrap gap-4 text-sm">
            <a href="{{ route('welcome') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-emerald-700 transition">
                <i class="fas fa-arrow-left text-xs"></i>
                Back to home
            </a>
            @isset($alternateRoute)
                <a href="{{ $alternateRoute }}" class="inline-flex items-center gap-2 text-emerald-700 font-medium hover:text-emerald-800 transition">
                    {{ $alternateLabel }}
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            @endisset
        </div>
    </section>
@endsection
