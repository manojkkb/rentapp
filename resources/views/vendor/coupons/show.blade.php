@extends('vendor.layouts.app')

@section('title', __('vendor.coupon_details'))
@section('page-title', __('vendor.coupon_details'))

@section('content')
@php
    $card = 'overflow-hidden rounded-xl border border-gray-200/90 bg-white shadow-sm';
    $life = $coupon->lifecycleStatus();
    $statusTone = [
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'scheduled' => 'bg-amber-50 text-amber-800 ring-amber-100',
        'expired' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'exhausted' => 'bg-orange-50 text-orange-800 ring-orange-100',
        'inactive' => 'bg-gray-100 text-gray-600 ring-gray-200',
    ];
    $statusLabel = [
        'active' => __('vendor.active'),
        'scheduled' => __('vendor.scheduled'),
        'expired' => __('vendor.expired'),
        'exhausted' => __('vendor.coupon_exhausted'),
        'inactive' => __('vendor.inactive'),
    ];
@endphp

<div class="mx-auto w-full max-w-4xl space-y-3 sm:space-y-4">
    <header class="flex flex-wrap items-start justify-between gap-2">
        <div class="min-w-0">
            <a href="{{ route('vendor.coupons.index') }}"
               wire:navigate
               class="mb-1.5 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-600">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                {{ __('vendor.back_to_coupons') }}
            </a>
            <h1 class="font-mono text-lg font-bold tracking-wide text-gray-900 sm:text-xl">{{ $coupon->code }}</h1>
            @if($coupon->name)
                <p class="mt-0.5 text-sm text-gray-600">{{ $coupon->name }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <form method="POST" action="{{ route('vendor.coupons.toggle', $coupon) }}">
                @csrf
                <button type="submit"
                        class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="fas {{ $coupon->is_active ? 'fa-toggle-on text-emerald-600' : 'fa-toggle-off text-gray-400' }}" aria-hidden="true"></i>
                    {{ $coupon->is_active ? __('vendor.deactivate') : __('vendor.activate') }}
                </button>
            </form>
            <a href="{{ route('vendor.coupons.edit', $coupon) }}"
               wire:navigate
               class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                <i class="fas fa-edit text-xs" aria-hidden="true"></i>
                {{ __('vendor.edit_coupon') }}
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-900">
            <i class="fas fa-check-circle mt-0.5 text-emerald-600" aria-hidden="true"></i>
            <p class="flex-1">{{ session('success') }}</p>
        </div>
    @endif

    <section class="{{ $card }}">
        <div class="bg-gradient-to-br from-violet-600 to-indigo-700 p-5 text-white sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-white/70">{{ __('vendor.coupons') }}</p>
                    <p class="mt-2 text-3xl font-bold">{{ $coupon->discountLabel() }}</p>
                    <p class="mt-1 text-sm text-white/80">
                        {{ $coupon->type === 'percent' ? __('vendor.percentage') : __('vendor.fixed_amount') }}
                    </p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15">
                    <i class="fas fa-ticket-alt text-xl" aria-hidden="true"></i>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $statusTone[$life] ?? $statusTone['inactive'] }}">
                    {{ $statusLabel[$life] ?? $life }}
                </span>
                <span class="inline-flex rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-semibold text-white">
                    @if($coupon->usage_limit)
                        {{ __('vendor.coupon_usage_progress', ['used' => $coupon->used_count, 'limit' => $coupon->usage_limit]) }}
                    @else
                        {{ __('vendor.unlimited') }}
                    @endif
                </span>
            </div>
        </div>
    </section>

    <section class="{{ $card }}">
        <div class="border-b border-gray-100 bg-gray-50/80 px-4 py-3">
            <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.coupon_details') }}</h2>
        </div>
        <dl class="grid grid-cols-1 gap-px bg-gray-100 sm:grid-cols-2">
            <div class="bg-white px-4 py-3">
                <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.min_order') }}</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-900">
                    {{ $coupon->min_order_amount > 0 ? '₹'.number_format($coupon->min_order_amount, 2) : __('vendor.no_limit') }}
                </dd>
            </div>
            <div class="bg-white px-4 py-3">
                <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.max_discount') }}</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-900">
                    {{ $coupon->max_discount_amount ? '₹'.number_format($coupon->max_discount_amount, 2) : __('vendor.no_limit') }}
                </dd>
            </div>
            <div class="bg-white px-4 py-3">
                <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.coupon_form_validity') }}</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-900">
                    @if($coupon->start_date && $coupon->end_date)
                        {{ $coupon->start_date->format('j M Y') }} – {{ $coupon->end_date->format('j M Y') }}
                    @elseif($coupon->end_date)
                        {{ __('vendor.until') }} {{ $coupon->end_date->format('j M Y') }}
                    @elseif($coupon->start_date)
                        {{ __('vendor.from') }} {{ $coupon->start_date->format('j M Y') }}
                    @else
                        {{ __('vendor.coupon_no_expiry') }}
                    @endif
                </dd>
            </div>
            <div class="bg-white px-4 py-3">
                <dt class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.status') }}</dt>
                <dd class="mt-0.5 text-sm font-medium text-gray-900">
                    {{ $coupon->is_active ? __('vendor.active') : __('vendor.inactive') }}
                </dd>
            </div>
        </dl>
    </section>

    <section class="{{ $card }}">
        <div class="p-4 sm:p-5">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                <div class="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.coupon_orders_used') }}</p>
                    <p class="mt-0.5 text-lg font-bold tabular-nums text-gray-900">{{ $usageStats['orders'] }}</p>
                </div>
                <div class="rounded-lg border border-violet-100 bg-violet-50/50 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-violet-800/80">{{ __('vendor.coupon_total_discount') }}</p>
                    <p class="mt-0.5 text-lg font-bold tabular-nums text-violet-900">₹{{ number_format($usageStats['total_discount'], 2) }}</p>
                </div>
                <div class="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5 col-span-2 sm:col-span-1">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.category_created_on') }}</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-900">{{ $coupon->created_at->format('j M Y') }}</p>
                </div>
            </div>
        </div>
    </section>

    <form method="POST"
          action="{{ route('vendor.coupons.destroy', $coupon) }}"
          onsubmit="return confirm(@js(__('vendor.confirm_delete')));"
          class="flex justify-end">
        @csrf
        @method('DELETE')
        <button type="submit"
                class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg border border-rose-200 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">
            <i class="fas fa-trash-alt text-xs" aria-hidden="true"></i>
            {{ __('vendor.delete_coupon') }}
        </button>
    </form>
</div>
@endsection
