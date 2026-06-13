@extends('vendor.layouts.app')

@section('title', __('vendor.staff_details'))
@section('page-title', __('vendor.staff_details'))

@section('content')
@php
    $card = 'overflow-hidden rounded-xl border border-gray-200/90 bg-white';
    $head = 'border-b border-gray-100 bg-gradient-to-r from-slate-50 to-emerald-50/20 px-3 py-2.5 sm:px-4 sm:py-3';
    $body = 'p-3 sm:p-4';
    $initial = strtoupper(substr($staffUser->name, 0, 1));
    $toneMap = [
        'emerald' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'teal' => 'bg-teal-100 text-teal-700 ring-teal-200',
        'blue' => 'bg-blue-100 text-blue-700 ring-blue-200',
        'amber' => 'bg-amber-100 text-amber-700 ring-amber-200',
    ];
@endphp

<div class="mx-auto w-full max-w-4xl space-y-3 sm:space-y-4">
    <header class="flex flex-wrap items-start justify-between gap-2">
        <div class="min-w-0">
            <a href="{{ route('vendor.staff.index') }}"
               wire:navigate
               class="mb-1.5 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-600">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                {{ __('vendor.back_to_staff') }}
            </a>
            <h1 class="truncate text-lg font-bold text-gray-900 sm:text-xl">{{ $staffUser->name }}</h1>
            <p class="mt-0.5 flex items-center gap-1.5 text-sm text-gray-600">
                <i class="fas fa-phone text-[10px] text-emerald-600" aria-hidden="true"></i>
                <span class="font-medium tabular-nums">{{ $staffUser->mobile }}</span>
            </p>
        </div>
        @if(!$vendorUser->is_owner)
            @vendorCan('staff.edit')
            <a href="{{ route('vendor.staff.edit', $vendorUser) }}"
               wire:navigate
               class="inline-flex min-h-[40px] shrink-0 items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                <i class="fas fa-edit text-xs" aria-hidden="true"></i>
                {{ __('vendor.edit') }}
            </a>
            @endvendorCan
        @endif
    </header>

    @if(session('success'))
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-900">
            <i class="fas fa-check-circle mt-0.5 text-emerald-600" aria-hidden="true"></i>
            <p class="flex-1">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Profile --}}
    <section class="{{ $card }}">
        <div class="flex items-start gap-3 p-3 sm:items-center sm:gap-4 sm:p-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-xl font-bold text-white ring-2 ring-emerald-100">
                {{ $initial }}
            </div>
            <div class="min-w-0 flex-1 space-y-2">
                <div class="flex flex-wrap gap-1.5">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $vendorUser->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-gray-50 text-gray-600 ring-gray-100' }}">
                        {{ $vendorUser->is_active ? __('vendor.active') : __('vendor.inactive') }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-100">
                        <i class="fas fa-user-tag mr-1 text-[9px]" aria-hidden="true"></i>{{ $vendorUser->roleLabel() }}
                    </span>
                    @if($vendorUser->is_owner)
                        <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-0.5 text-[11px] font-semibold text-yellow-800 ring-1 ring-yellow-100">
                            <i class="fas fa-crown mr-1 text-[9px]" aria-hidden="true"></i>{{ __('vendor.owner') }}
                        </span>
                    @endif
                </div>
                @if($staffUser->email && !str_contains($staffUser->email, '@staff.temp') && !str_contains($staffUser->email, '@rentapp.temp') && !str_contains($staffUser->email, '@rentkia.temp'))
                    <p class="text-sm text-gray-700">
                        <i class="fas fa-envelope mr-1.5 text-xs text-gray-400" aria-hidden="true"></i>{{ $staffUser->email }}
                    </p>
                @endif
                <p class="text-xs text-gray-500">
                    {{ __('vendor.staff_member_since') }} {{ $vendorUser->created_at->format('M j, Y') }}
                    @if($vendorUser->last_login_at)
                        <span class="mx-1 text-gray-300">·</span>
                        {{ __('vendor.last_login') }} {{ $vendorUser->last_login_at->diffForHumans() }}
                    @endif
                </p>
            </div>
        </div>
    </section>

    {{-- Activity stats --}}
    <section class="{{ $card }}">
        <div class="{{ $body }}">
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3">
                <div class="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.staff_activity_total') }}</p>
                    <p class="mt-0.5 text-lg font-bold tabular-nums text-gray-900">{{ $activityStats['total'] }}</p>
                </div>
                <div class="rounded-lg border border-teal-100 bg-teal-50/50 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-teal-800/80">{{ __('vendor.staff_activity_logins') }}</p>
                    <p class="mt-0.5 text-lg font-bold tabular-nums text-teal-900">{{ $activityStats['logins'] }}</p>
                </div>
                <div class="rounded-lg border border-blue-100 bg-blue-50/50 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-blue-800/80">{{ __('vendor.staff_activity_item_actions') }}</p>
                    <p class="mt-0.5 text-lg font-bold tabular-nums text-blue-900">{{ $activityStats['items'] }}</p>
                </div>
                <div class="rounded-lg border border-amber-100 bg-amber-50/50 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-800/80">{{ __('vendor.staff_activity_category_actions') }}</p>
                    <p class="mt-0.5 text-lg font-bold tabular-nums text-amber-900">{{ $activityStats['categories'] }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Activity timeline --}}
    <section class="{{ $card }}">
        <div class="{{ $head }}">
            <h2 class="text-sm font-bold text-gray-900 sm:text-base">{{ __('vendor.staff_all_activities') }}</h2>
        </div>
        <div class="{{ $body }}">
            @if($activities->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/80 px-4 py-10 text-center">
                    <i class="fas fa-clock-rotate-left mb-3 text-3xl text-gray-300" aria-hidden="true"></i>
                    <p class="text-sm text-gray-600">{{ __('vendor.staff_no_activities') }}</p>
                </div>
            @else
                <ol class="relative space-y-0">
                    @foreach($activities as $activity)
                        @php
                            $tone = $toneMap[$activity['tone']] ?? $toneMap['emerald'];
                            $when = $activity['created_at'] instanceof \Carbon\CarbonInterface
                                ? $activity['created_at']
                                : \Carbon\Carbon::parse($activity['created_at']);
                        @endphp
                        <li class="relative flex gap-3 pb-6 last:pb-0 sm:gap-4">
                            @if(!$loop->last)
                                <span class="absolute left-[1.125rem] top-10 bottom-0 w-px bg-gray-200 sm:left-5" aria-hidden="true"></span>
                            @endif
                            <span class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ring-1 {{ $tone }}">
                                <i class="fas {{ $activity['icon'] }} text-sm" aria-hidden="true"></i>
                            </span>
                            <div class="min-w-0 flex-1 pt-0.5">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900">{{ $activity['label'] }}</p>
                                        @if($activity['url'])
                                            <a href="{{ $activity['url'] }}" wire:navigate class="mt-0.5 block truncate text-sm text-emerald-700 hover:underline">
                                                {{ $activity['description'] }}
                                            </a>
                                        @else
                                            <p class="mt-0.5 text-sm text-gray-600">{{ $activity['description'] }}</p>
                                        @endif
                                        @if($activity['meta'])
                                            <p class="mt-1 truncate text-[11px] text-gray-400">{{ $activity['meta'] }}</p>
                                        @endif
                                    </div>
                                    <time datetime="{{ $when->toIso8601String() }}" class="shrink-0 text-[11px] font-medium text-gray-500" title="{{ $when->format('M j, Y g:i A') }}">
                                        {{ $when->diffForHumans() }}
                                    </time>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </section>
</div>
@endsection
