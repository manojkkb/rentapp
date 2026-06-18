@extends('pages.partials.layout')

@section('title', 'About Us — Rentkia')

@section('page-content')
    <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 mb-4">Who we are</h2>
            <p class="text-slate-600 leading-relaxed mb-4">
                Rentkia is a rental marketplace that connects people who need equipment, gear, and essentials for a short time
                with local vendors who already own them. From cameras and drones to tools, party setups, and gaming consoles —
                we make access easy, affordable, and sustainable.
            </p>
            <p class="text-slate-600 leading-relaxed">
                We believe ownership is not always the answer. Renting reduces waste, saves money, and helps small businesses
                turn idle inventory into steady income. Our platform gives vendors modern storefronts, booking tools, and payments —
                while customers get verified stores, transparent pricing, and a smooth rental experience.
            </p>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <h2 class="text-xl font-bold text-slate-900 mb-4">Our mission</h2>
            <p class="text-slate-600 leading-relaxed mb-6">
                To make renting as simple and trusted as ordering online — for every city, every category, and every occasion.
            </p>
            <div class="grid grid-cols-2 gap-4">
                @foreach([
                    ['value' => '850+', 'label' => 'Vendor stores'],
                    ['value' => '12K+', 'label' => 'Listed items'],
                    ['value' => '50+', 'label' => 'Categories'],
                    ['value' => '4.9★', 'label' => 'Avg. rating'],
                ] as $stat)
                    <div class="rounded-xl bg-emerald-50 px-4 py-4 text-center">
                        <p class="text-2xl font-extrabold text-emerald-700">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-xs font-medium uppercase tracking-wide text-emerald-800/70">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </article>
    </div>

    <article class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
        <h2 class="text-xl font-bold text-slate-900 mb-4">What we stand for</h2>
        <div class="grid gap-5 sm:grid-cols-3">
            @foreach([
                ['icon' => 'fa-shield-alt', 'title' => 'Trust', 'desc' => 'Verified vendors, clear policies, and secure checkout.'],
                ['icon' => 'fa-leaf', 'title' => 'Sustainability', 'desc' => 'Share resources instead of buying items used once.'],
                ['icon' => 'fa-handshake', 'title' => 'Community', 'desc' => 'Local businesses and renters growing together.'],
            ] as $item)
                <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-5">
                    <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                        <i class="fas {{ $item['icon'] }}"></i>
                    </div>
                    <h3 class="font-bold text-slate-900">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </article>
@endsection
