@extends('vendor.layouts.app')

@section('title', __('vendor.category_details'))
@section('page-title', __('vendor.category_details'))

@section('content')
@php
    $card = 'overflow-hidden rounded-xl border border-gray-200/90 bg-white';
    $head = 'border-b border-gray-100 bg-gradient-to-r from-slate-50 to-emerald-50/20 px-3 py-2.5 sm:px-4 sm:py-3';
    $body = 'p-3 sm:p-4';
    $dl = 'text-[11px] font-semibold uppercase tracking-wide text-gray-500';
    $dv = 'mt-0.5 text-sm font-medium text-gray-900';
    $isParent = $category->parent_id === null;
@endphp

<div class="mx-auto w-full max-w-4xl space-y-3 sm:space-y-4">
    <header class="flex flex-wrap items-start justify-between gap-2">
        <div class="min-w-0">
            <a href="{{ route('vendor.categories.index') }}"
               wire:navigate
               class="mb-1.5 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-600">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                {{ __('vendor.back_to_categories') }}
            </a>
            <h1 class="truncate text-lg font-bold text-gray-900 sm:text-xl">{{ $category->name }}</h1>
            @if($category->parent)
                <p class="mt-0.5 text-sm text-gray-600">
                    {{ __('vendor.parent_category') }}:
                    <a href="{{ route('vendor.categories.show', $category->parent) }}"
                       wire:navigate
                       class="font-medium text-emerald-700 hover:underline">{{ $category->parent->name }}</a>
                </p>
            @endif
        </div>
        @vendorCan('categories.manage')
        <a href="{{ route('vendor.categories.edit', $category) }}"
           wire:navigate
           class="inline-flex min-h-[40px] shrink-0 items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            <i class="fas fa-edit text-xs" aria-hidden="true"></i>
            {{ __('vendor.edit_category') }}
        </a>
        @endvendorCan
    </header>

    @if(session('success'))
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-900">
            <i class="fas fa-check-circle mt-0.5 text-emerald-600" aria-hidden="true"></i>
            <p class="flex-1">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Profile --}}
    <section class="{{ $card }}">
        <div class="grid grid-cols-1 gap-0 sm:grid-cols-[8rem_minmax(0,1fr)]">
            <div class="flex items-center justify-center border-b border-gray-100 bg-gradient-to-br from-emerald-50/50 to-slate-50 p-4 sm:border-b-0 sm:border-r">
                @if($category->image_url)
                    <img src="{{ $category->image_url }}" alt=""
                         class="h-28 w-28 rounded-xl border border-gray-200 object-cover sm:h-24 sm:w-24">
                @else
                    <div class="flex h-28 w-28 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 sm:h-24 sm:w-24">
                        <i class="fas fa-tag text-3xl text-emerald-500" aria-hidden="true"></i>
                    </div>
                @endif
            </div>
            <div class="{{ $body }} space-y-3">
                <div class="flex flex-wrap gap-1.5">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $category->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-gray-50 text-gray-600 ring-gray-100' }}">
                        {{ $category->is_active ? __('vendor.active') : __('vendor.inactive') }}
                    </span>
                    @if($isParent)
                        <span class="inline-flex items-center rounded-full bg-teal-50 px-2 py-0.5 text-[11px] font-semibold text-teal-700 ring-1 ring-teal-100">
                            <i class="fas fa-folder mr-1 text-[9px]" aria-hidden="true"></i>{{ __('vendor.category') }}
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-700 ring-1 ring-slate-100">
                            <i class="fas fa-folder-tree mr-1 text-[9px]" aria-hidden="true"></i>{{ __('vendor.subcategories') }}
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <div>
                        <p class="{{ $dl }}">{{ __('vendor.current_url') }}</p>
                        <p class="{{ $dv }}"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs">{{ $category->slug }}</code></p>
                    </div>
                    <div>
                        <p class="{{ $dl }}">{{ __('vendor.category_created_on') }}</p>
                        <p class="{{ $dv }}">{{ $category->created_at->format('M j, Y') }}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <p class="{{ $dl }}">ID</p>
                        <p class="{{ $dv }}"><span class="font-mono text-xs text-gray-600">{{ $category->uuid }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="{{ $card }}">
        <div class="{{ $body }}">
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3">
                <div class="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.total_items') }}</p>
                    <p class="mt-0.5 text-lg font-bold tabular-nums text-gray-900">{{ $stats['items'] }}</p>
                </div>
                <div class="rounded-lg border border-emerald-100 bg-emerald-50/50 px-3 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-800/80">{{ __('vendor.active') }} {{ __('vendor.items') }}</p>
                    <p class="mt-0.5 text-lg font-bold tabular-nums text-emerald-900">{{ $stats['active_items'] }}</p>
                </div>
                @if($isParent)
                    <div class="rounded-lg border border-teal-100 bg-teal-50/50 px-3 py-2.5 col-span-2 sm:col-span-1">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-teal-800/80">{{ __('vendor.subcategories') }}</p>
                        <p class="mt-0.5 text-lg font-bold tabular-nums text-teal-900">{{ $stats['subcategories'] }}</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Subcategories --}}
    @if($isParent && $category->subcategories->isNotEmpty())
        <section class="{{ $card }}">
            <div class="{{ $head }} flex items-center justify-between gap-2">
                <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.subcategories') }}</h2>
                <a href="{{ route('vendor.categories.subcategories', $category) }}"
                   wire:navigate
                   class="text-xs font-semibold text-emerald-700 hover:underline">
                    {{ __('vendor.view_subcategories') }} →
                </a>
            </div>
            <div class="{{ $body }}">
                <div class="divide-y divide-gray-100 rounded-xl border border-gray-100">
                    @foreach($category->subcategories as $sub)
                        <a href="{{ route('vendor.categories.show', $sub) }}"
                           wire:navigate
                           class="flex items-center justify-between gap-3 px-3 py-3 transition hover:bg-emerald-50/40 sm:px-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                    <i class="fas fa-tag text-xs" aria-hidden="true"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $sub->name }}</p>
                                    <p class="text-xs text-gray-500">{{ __('vendor.category_items_count', ['count' => $sub->items_count]) }}</p>
                                </div>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 {{ $sub->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-gray-50 text-gray-600 ring-gray-100' }}">
                                {{ $sub->is_active ? __('vendor.active') : __('vendor.inactive') }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Recent items --}}
    <section class="{{ $card }}">
        <div class="{{ $head }} flex items-center justify-between gap-2">
            <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.category_recent_items') }}</h2>
            @if($stats['items'] > 0)
                <a href="{{ route('vendor.items.index') }}?categoryId={{ $category->id }}"
                   wire:navigate
                   class="text-xs font-semibold text-emerald-700 hover:underline">
                    {{ __('vendor.view') }} {{ __('vendor.all') }} →
                </a>
            @endif
        </div>
        <div class="{{ $body }}">
            @if($recentItems->isNotEmpty())
                <div class="divide-y divide-gray-100 rounded-xl border border-gray-100">
                    @foreach($recentItems as $item)
                        <a href="{{ route('vendor.items.show', $item) }}"
                           wire:navigate
                           class="flex items-center gap-3 px-3 py-3 transition hover:bg-emerald-50/40 sm:px-4">
                            @if($item->photo_url)
                                <img src="{{ $item->photo_url }}" alt="" class="h-10 w-10 shrink-0 rounded-lg border border-gray-200 object-cover" loading="lazy">
                            @else
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                    <i class="fas fa-box text-sm" aria-hidden="true"></i>
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-900">{{ $item->name }}</p>
                                <p class="text-xs text-gray-500">₹{{ number_format($item->price, 2) }}</p>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-gray-50 text-gray-600 ring-gray-100' }}">
                                {{ $item->is_active ? __('vendor.active') : __('vendor.inactive') }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/80 px-4 py-10 text-center">
                    <i class="fas fa-box-open mb-3 text-3xl text-gray-300" aria-hidden="true"></i>
                    <p class="text-sm text-gray-600">{{ __('vendor.category_no_items') }}</p>
                    @vendorCan('items.create')
                    <a href="{{ route('vendor.items.create') }}"
                       wire:navigate
                       class="mt-4 inline-flex min-h-[40px] items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                        {{ __('vendor.add_item') }}
                    </a>
                    @endvendorCan
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
