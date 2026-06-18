@extends('pages.partials.layout')

@section('title', 'Contact Us — Rentkia')

@section('page-content')
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-5">
        <div class="lg:col-span-2 space-y-4">
            @foreach([
                ['icon' => 'fa-map-marker-alt', 'label' => 'Address', 'value' => '123 Rental Street, Bandra West, Mumbai 400050'],
                ['icon' => 'fa-phone', 'label' => 'Phone', 'value' => '+91 98765 43210', 'href' => 'tel:+919876543210'],
                ['icon' => 'fa-envelope', 'label' => 'Email', 'value' => 'hello@rentkia.com', 'href' => 'mailto:hello@rentkia.com'],
                ['icon' => 'fa-clock', 'label' => 'Hours', 'value' => 'Mon – Sat, 9:00 AM – 7:00 PM IST'],
            ] as $item)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                            <i class="fas {{ $item['icon'] }}"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $item['label'] }}</p>
                            @if(! empty($item['href']))
                                <a href="{{ $item['href'] }}" class="mt-1 block text-sm font-medium text-slate-900 hover:text-emerald-700 transition">{{ $item['value'] }}</a>
                            @else
                                <p class="mt-1 text-sm font-medium text-slate-900">{{ $item['value'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <form action="{{ route('pages.contact.submit') }}" method="POST"
              class="lg:col-span-3 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-5">
            @csrf
            <div>
                <label for="contact_name" class="mb-1.5 block text-sm font-semibold text-slate-800">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="contact_name" value="{{ old('name') }}" required maxlength="255"
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 @error('name') border-red-500 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="contact_email" class="mb-1.5 block text-sm font-semibold text-slate-800">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="contact_email" value="{{ old('email') }}" required maxlength="255"
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 @error('email') border-red-500 @enderror">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="contact_subject" class="mb-1.5 block text-sm font-semibold text-slate-800">Subject</label>
                <input type="text" name="subject" id="contact_subject" value="{{ old('subject') }}" maxlength="255"
                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
            </div>
            <div>
                <label for="contact_message" class="mb-1.5 block text-sm font-semibold text-slate-800">Message <span class="text-red-500">*</span></label>
                <textarea name="message" id="contact_message" rows="5" required maxlength="5000"
                          class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-900 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3.5 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:w-auto">
                <i class="fas fa-paper-plane text-xs"></i>
                Send message
            </button>
        </form>
    </div>
@endsection
