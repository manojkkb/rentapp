@extends('admin.layouts.app')

@section('title', 'Settings - Admin')

@section('content')
@php
    $groups = [
        'general' => ['title' => 'General', 'icon' => 'fa-cog'],
        'subscription' => ['title' => 'Subscription', 'icon' => 'fa-credit-card'],
        'booking' => ['title' => 'Booking', 'icon' => 'fa-calendar-check'],
        'tax' => ['title' => 'Tax', 'icon' => 'fa-percent'],
    ];
@endphp
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white sm:text-3xl">Platform Settings</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rentkia marketplace configuration (stored in database)</p>
    </div>

    @include('admin.users.partials.alerts')

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach($groups as $groupKey => $group)
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-5 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-gray-500">
                    <i class="fas {{ $group['icon'] }} text-green-600"></i>
                    {{ $group['title'] }}
                </h2>
                <div class="space-y-4">
                    @foreach($definitions as $key => $def)
                        @if($def['group'] !== $groupKey)
                            @continue
                        @endif
                        @php $val = old($key, $settings[$key] ?? $def['default']); @endphp

                        @if($def['type'] === 'bool')
                            <label class="flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-gray-100 px-4 py-3 dark:border-gray-700">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $def['label'] }}</span>
                                <input type="hidden" name="{{ $key }}" value="0">
                                <input type="checkbox" name="{{ $key }}" value="1"
                                       @checked((bool) old($key, $val))
                                       class="h-5 w-5 rounded border-gray-300 text-green-600">
                            </label>
                        @elseif($def['type'] === 'int' || $def['type'] === 'float')
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $def['label'] }}</label>
                                <input type="number"
                                       name="{{ $key }}"
                                       value="{{ $val }}"
                                       step="{{ $def['type'] === 'float' ? '0.01' : '1' }}"
                                       min="0"
                                       @if($key === 'trial_days') max="365" @endif
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            </div>
                        @else
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $def['label'] }}</label>
                                <input type="{{ $key === 'support_email' ? 'email' : 'text' }}"
                                       name="{{ $key }}"
                                       value="{{ $val }}"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex gap-3">
            <button type="submit" class="rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-green-700">
                Save settings
            </button>
            <a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
