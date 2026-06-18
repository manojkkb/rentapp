@extends('pages.partials.layout')

@section('page-content')
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach([
            ['name' => 'Rahul Mehta', 'role' => 'Co-founder & CEO', 'bio' => 'Product visionary focused on making rentals accessible across India.', 'img' => 'photo-1507003211169-0a1dd7228f2d'],
            ['name' => 'Priya Sharma', 'role' => 'Co-founder & COO', 'bio' => 'Operations lead ensuring vendors and customers have a seamless experience.', 'img' => 'photo-1494790108377-be9c29b29330'],
            ['name' => 'Arjun Patel', 'role' => 'Head of Technology', 'bio' => 'Engineering the platform, storefronts, and tools vendors rely on every day.', 'img' => 'photo-1472099645785-5658abf4ff4e'],
            ['name' => 'Sneha Reddy', 'role' => 'Vendor Success', 'bio' => 'Helps rental businesses onboard, grow, and succeed on Rentkia.', 'img' => 'photo-1438761681033-6461ffad8d80'],
            ['name' => 'Vikram Singh', 'role' => 'Customer Support', 'bio' => 'Dedicated to resolving issues quickly and keeping rentals worry-free.', 'img' => 'photo-1500648767791-00dcc994a43e'],
            ['name' => 'Ananya Iyer', 'role' => 'Marketing & Growth', 'bio' => 'Spreading the word about smart renting and building the Rentkia community.', 'img' => 'photo-1544005313-94ddf0286df2'],
        ] as $member)
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-500/10">
                <div class="aspect-[4/3] overflow-hidden bg-slate-100">
                    <img src="https://images.unsplash.com/{{ $member['img'] }}?w=600&h=450&fit=crop&crop=face"
                         alt="{{ $member['name'] }}"
                         class="h-full w-full object-cover"
                         loading="lazy"
                         decoding="async">
                </div>
                <div class="p-5 sm:p-6">
                    <h2 class="text-lg font-bold text-slate-900">{{ $member['name'] }}</h2>
                    <p class="mt-1 text-sm font-semibold text-emerald-700">{{ $member['role'] }}</p>
                    <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ $member['bio'] }}</p>
                </div>
            </article>
        @endforeach
    </div>

    <article class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm text-center">
        <h2 class="text-xl font-bold text-slate-900">Want to join us?</h2>
        <p class="mt-3 text-slate-600 max-w-xl mx-auto">
            We are always looking for passionate people in product, engineering, and operations.
        </p>
        <a href="{{ route('pages.contact') }}"
           class="mt-5 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
            Get in touch
            <i class="fas fa-arrow-right text-xs"></i>
        </a>
    </article>
@endsection
