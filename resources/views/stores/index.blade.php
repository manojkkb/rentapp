@extends('layouts.guest')

@section('headerSolid', '1')

@section('content')
    {{-- Dark hero — matches home page so nav stays readable --}}
    <section class="relative overflow-hidden hero-mesh grid-pattern pt-28 pb-12 lg:pt-32 lg:pb-16 px-4 sm:px-6 lg:px-8">
        <div class="blob bg-emerald-500 w-[420px] h-[420px] -top-24 -right-24 opacity-30"></div>
        <div class="blob bg-teal-400 w-[320px] h-[320px] bottom-0 -left-20 opacity-25" style="animation-delay: 2s;"></div>

        <div class="relative z-10 max-w-7xl mx-auto text-center">
            <span class="inline-block text-emerald-300 font-semibold text-sm uppercase tracking-widest mb-3">Rentkia</span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight">
                Browse <span class="text-gradient">stores</span>
            </h1>
            <p class="mt-4 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto">
                Discover rental shops near you — cameras, tools, party gear, and more from local vendors.
            </p>

            <form action="{{ route('stores.index') }}" method="GET" class="mx-auto mt-8 max-w-2xl">
                <div class="relative flex flex-col gap-2 rounded-2xl border border-white/10 bg-white/95 p-2 shadow-xl shadow-black/20 backdrop-blur-sm sm:flex-row sm:items-center">
                    <div class="pointer-events-none absolute left-5 top-1/2 hidden -translate-y-1/2 text-slate-400 sm:block">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="search"
                           name="q"
                           value="{{ $search }}"
                           placeholder="Search by store name, category, or location…"
                           autocomplete="off"
                           class="min-h-12 w-full flex-1 rounded-xl border-0 bg-transparent py-3 pl-4 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-0 sm:pl-10 sm:text-base">
                    <div class="flex items-center gap-2 px-1 sm:px-0">
                        @if($search !== '')
                            <a href="{{ route('stores.index') }}"
                               class="inline-flex flex-1 items-center justify-center rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 sm:flex-none">
                                Clear
                            </a>
                        @endif
                        <button type="submit"
                                class="inline-flex flex-1 items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:flex-none">
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <section class="relative px-4 sm:px-6 lg:px-8 pb-16 lg:pb-24 bg-slate-50 min-h-[50vh]">
        <div class="max-w-7xl mx-auto pt-10">
            @if($search !== '' && $stores->isNotEmpty())
                <p class="mb-6 text-center text-sm text-slate-500">
                    {{ $stores->total() }} {{ $stores->total() === 1 ? 'store' : 'stores' }} found for
                    <span class="font-semibold text-slate-700">&ldquo;{{ $search }}&rdquo;</span>
                </p>
            @endif

            @if($stores->isEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                        <i class="fas fa-{{ $search !== '' ? 'search' : 'store' }} text-2xl"></i>
                    </div>
                    @if($search !== '')
                        <h2 class="text-xl font-bold text-slate-900">No stores found</h2>
                        <p class="mt-2 text-slate-500">Try a different name, category, or location.</p>
                        <a href="{{ route('stores.index') }}"
                           class="mt-5 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            View all stores
                        </a>
                    @else
                        <h2 class="text-xl font-bold text-slate-900">No stores yet</h2>
                        <p class="mt-2 text-slate-500">Check back soon as new vendors join Rentkia.</p>
                    @endif
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach($stores as $store)
                        @php
                            $location = $store->storeSettings?->primaryLocation
                                ?? $store->locations->firstWhere('is_default', true)
                                ?? $store->locations->first();

                            $locationName = $location?->name
                                ?: trim(collect([$store->city, $store->state])->filter()->implode(', '))
                                ?: '—';
                        @endphp
                        <a href="{{ route('storefront.show', $store->slug) }}"
                           class="group flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-500/10">
                            <div class="mb-4 flex items-start gap-4">
                                @if($store->logo_url)
                                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-2xl border border-slate-100 bg-slate-50 shadow-sm">
                                        <img src="{{ $store->logo_url }}"
                                             alt="{{ $store->name }}"
                                             class="h-full w-full object-cover"
                                             loading="lazy"
                                             decoding="async">
                                    </div>
                                @else
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-xl font-bold text-white shadow-md shadow-emerald-500/20">
                                        {{ strtoupper(substr($store->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1 pt-0.5">
                                    <h2 class="truncate text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition-colors">
                                        {{ $store->name }}
                                    </h2>
                                    @if($store->businessCategory)
                                        <p class="mt-1 inline-flex items-center gap-1.5 text-sm font-medium text-emerald-700">
                                            <i class="fas fa-tag text-[10px] opacity-70"></i>
                                            {{ $store->businessCategory->name }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-auto flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2.5 text-sm text-slate-600">
                                <i class="fas fa-map-marker-alt shrink-0 text-emerald-500"></i>
                                <span class="truncate font-medium">{{ $locationName }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($stores->hasPages())
                    <div class="mt-10">
                        {{ $stores->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection
