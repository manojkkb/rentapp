@extends('pages.partials.layout')

@section('page-content')
    <div class="relative">
        <div class="grid gap-10 md:grid-cols-3 md:gap-8 lg:gap-12">
            {{-- Step 1 --}}
            <article class="relative text-center">
                <div class="hidden md:block absolute top-14 left-[58%] w-[84%] h-px bg-gradient-to-r from-emerald-300 to-transparent"></div>
                <div class="relative mx-auto mb-6 inline-flex">
                    <div class="flex h-28 w-28 items-center justify-center rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-500 shadow-xl shadow-emerald-500/25 transition-transform hover:scale-105">
                        <i class="fas fa-search text-3xl text-white"></i>
                    </div>
                    <span class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">01</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 mb-3">Discover &amp; Compare</h2>
                <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-xs mx-auto">
                    Browse vendor stores on Rentkia, explore categories, and compare items by price, location, and ratings.
                </p>
            </article>

            {{-- Step 2 --}}
            <article class="relative text-center">
                <div class="hidden md:block absolute top-14 left-[58%] w-[84%] h-px bg-gradient-to-r from-teal-300 to-transparent"></div>
                <div class="relative mx-auto mb-6 inline-flex">
                    <div class="flex h-28 w-28 items-center justify-center rounded-3xl bg-gradient-to-br from-teal-500 to-cyan-500 shadow-xl shadow-teal-500/25 transition-transform hover:scale-105">
                        <i class="fas fa-calendar-check text-3xl text-white"></i>
                    </div>
                    <span class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">02</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 mb-3">Book &amp; Pay Securely</h2>
                <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-xs mx-auto">
                    Choose rental dates, add items to cart, and checkout with UPI, card, or other supported payment methods.
                </p>
            </article>

            {{-- Step 3 --}}
            <article class="relative text-center">
                <div class="relative mx-auto mb-6 inline-flex">
                    <div class="flex h-28 w-28 items-center justify-center rounded-3xl bg-gradient-to-br from-cyan-500 to-emerald-500 shadow-xl shadow-cyan-500/25 transition-transform hover:scale-105">
                        <i class="fas fa-smile-beam text-3xl text-white"></i>
                    </div>
                    <span class="absolute -right-2 -top-2 flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-xs font-bold text-white">03</span>
                </div>
                <h2 class="text-xl font-bold text-slate-900 mb-3">Enjoy &amp; Return</h2>
                <p class="text-sm sm:text-base text-slate-600 leading-relaxed max-w-xs mx-auto">
                    Pick up or get delivery, use the item worry-free, return on time, and share your experience.
                </p>
            </article>
        </div>
    </div>

    <div class="mt-12 grid gap-6 lg:grid-cols-2">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                <i class="fas fa-user text-lg"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900 mb-3">For renters</h2>
            <ul class="space-y-3 text-sm text-slate-600 leading-relaxed">
                <li class="flex items-start gap-3">
                    <i class="fas fa-check-circle mt-0.5 text-emerald-500"></i>
                    <span>Find stores near you and view live availability.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fas fa-check-circle mt-0.5 text-emerald-500"></i>
                    <span>Book online with clear pricing and rental dates.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fas fa-check-circle mt-0.5 text-emerald-500"></i>
                    <span>Choose pickup or delivery based on the vendor.</span>
                </li>
            </ul>
            <a href="{{ route('stores.index') }}"
               class="mt-6 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                Browse stores
                <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </article>

        <article class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-100 text-teal-700">
                <i class="fas fa-store text-lg"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900 mb-3">For vendors</h2>
            <ul class="space-y-3 text-sm text-slate-600 leading-relaxed">
                <li class="flex items-start gap-3">
                    <i class="fas fa-check-circle mt-0.5 text-teal-500"></i>
                    <span>Create your store and publish your online storefront.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fas fa-check-circle mt-0.5 text-teal-500"></i>
                    <span>Manage orders, customers, and inventory from one dashboard.</span>
                </li>
                <li class="flex items-start gap-3">
                    <i class="fas fa-check-circle mt-0.5 text-teal-500"></i>
                    <span>Turn idle inventory into steady rental income.</span>
                </li>
            </ul>
            <a href="{{ \App\Support\VendorPortal::entryUrl() }}"
               class="mt-6 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                <i class="fas fa-store"></i>
                {{ \App\Support\VendorPortal::entryLabel('Start as Vendor', 'Go to Dashboard') }}
            </a>
        </article>
    </div>
@endsection
