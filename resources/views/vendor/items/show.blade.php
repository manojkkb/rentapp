@extends('vendor.layouts.app')

@section('title', __('vendor.item_details'))
@section('page-title', __('vendor.item_details'))

@section('content')
@php
    $dl = 'text-[11px] font-semibold uppercase tracking-wide text-gray-500';
    $dv = 'mt-0.5 text-sm text-gray-900';
    $card = 'rounded-lg border border-gray-200 bg-white shadow-sm';
    $sectionTitle = 'text-[11px] font-bold uppercase tracking-wide text-emerald-900 border-b border-emerald-200/70 pb-2 mb-3';
    $rentalLabel = $rentalPeriods[$item->rental_period] ?? $item->rental_period;
    $conditionLabel = $conditionLabels[$item->condition_status] ?? $item->condition_status;
@endphp

<div class="mx-auto max-w-6xl space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('vendor.items.index') }}"
           class="inline-flex items-center text-sm text-gray-600 hover:text-emerald-600">
            <i class="fas fa-arrow-left mr-1.5 text-xs"></i>
            {{ __('vendor.back_to_items') }}
        </a>
        <div class="flex flex-wrap items-center gap-2">
            @vendorCan('items.edit')
            <a href="{{ route('vendor.items.edit', $item) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                <i class="fas fa-edit text-xs"></i>
                {{ __('vendor.edit_item') }}
            </a>
            @endvendorCan
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
        {{-- Summary --}}
        <div class="{{ $card }} overflow-hidden lg:col-span-4">
            <div class="border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-50 px-4 py-4">
                @if($item->photo_url)
                    <img src="{{ $item->photo_url }}"
                         alt=""
                         class="mx-auto h-48 w-48 rounded-xl border border-gray-200 object-cover shadow-sm">
                @else
                    <div class="mx-auto flex h-48 w-48 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-100">
                        <i class="fas fa-box text-5xl text-emerald-600"></i>
                    </div>
                @endif
            </div>
            <div class="space-y-3 p-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $item->name }}</h1>
                    <p class="mt-1 font-mono text-xs text-gray-500">{{ $item->item_code }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if($item->is_active)
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                            <i class="fas fa-circle text-[6px] mr-1.5"></i>{{ __('vendor.active') }}
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">
                            <i class="fas fa-circle text-[6px] mr-1.5"></i>{{ __('vendor.inactive') }}
                        </span>
                    @endif
                    @if($item->is_available)
                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                            <i class="fas fa-check-circle text-[10px] mr-1"></i>{{ __('vendor.available_for_rent') }}
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-orange-700">
                            <i class="fas fa-times-circle text-[10px] mr-1"></i>{{ __('vendor.not_available') }}
                        </span>
                    @endif
                </div>

                <div class="rounded-lg bg-gray-50 px-3 py-3">
                    <p class="text-2xl font-bold text-gray-900">₹{{ number_format((float) $item->price, 2) }}</p>
                    <p class="text-xs text-gray-500">{{ $rentalLabel }}</p>
                </div>

                <div>
                    <p class="{{ $dl }}">{{ __('vendor.category') }}</p>
                    <p class="{{ $dv }}">{{ $item->category?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="{{ $dl }}">{{ __('vendor.current_url') }}</p>
                    <p class="{{ $dv }}"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs">{{ $item->slug }}</code></p>
                </div>
            </div>
        </div>

        {{-- Details --}}
        <div class="space-y-4 lg:col-span-8">
            @if(filled($item->description))
            <section class="{{ $card }} p-4 sm:p-5">
                <h2 class="{{ $sectionTitle }}">{{ __('vendor.description') }}</h2>
                <p class="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{{ $item->description }}</p>
            </section>
            @endif

            <section class="{{ $card }} p-4 sm:p-5">
                <h2 class="{{ $sectionTitle }}">{{ __('vendor.item_fees_section') }}</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <p class="{{ $dl }}">{{ __('vendor.security_deposit') }}</p>
                        <p class="{{ $dv }}">₹{{ number_format((float) $item->security_deposit, 2) }}</p>
                    </div>
                    <div>
                        <p class="{{ $dl }}">{{ __('vendor.replacement_cost') }}</p>
                        <p class="{{ $dv }}">₹{{ number_format((float) $item->replacement_cost, 2) }}</p>
                    </div>
                    <div>
                        <p class="{{ $dl }}">{{ __('vendor.late_fee') }}</p>
                        <p class="{{ $dv }}">₹{{ number_format((float) $item->late_fee, 2) }}</p>
                    </div>
                </div>
            </section>

            <section class="{{ $card }} p-4 sm:p-5">
                <h2 class="{{ $sectionTitle }}">{{ __('vendor.item_rental_limits_section') }}</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="{{ $dl }}">{{ __('vendor.minimum_rental_duration') }}</p>
                        <p class="{{ $dv }}">{{ $item->min_rental_duration }}</p>
                    </div>
                    <div>
                        <p class="{{ $dl }}">{{ __('vendor.maximum_rental_duration') }}</p>
                        <p class="{{ $dv }}">{{ $item->max_rental_duration }}</p>
                    </div>
                </div>
            </section>

            <section class="{{ $card }} p-4 sm:p-5">
                <h2 class="{{ $sectionTitle }}">{{ __('vendor.item_inventory_buckets_section') }}</h2>
                <div class="mb-3">
                    <p class="{{ $dl }}">{{ __('vendor.track_stock_quantity') }}</p>
                    <p class="{{ $dv }}">{{ $item->manage_stock ? __('vendor.yes') : __('vendor.no') }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-lg border border-emerald-100 bg-emerald-50/50 px-3 py-3">
                        <p class="{{ $dl }}">{{ __('vendor.stock_quantity') }}</p>
                        <p class="mt-1 text-xl font-bold text-emerald-700">{{ $item->stock }}</p>
                    </div>
                    <div class="rounded-lg border border-orange-100 bg-orange-50/50 px-3 py-3">
                        <p class="{{ $dl }}">{{ __('vendor.damaged_stock') }}</p>
                        <p class="mt-1 text-xl font-bold text-orange-700">{{ $item->damaged_stock }}</p>
                    </div>
                    <div class="rounded-lg border border-blue-100 bg-blue-50/50 px-3 py-3">
                        <p class="{{ $dl }}">{{ __('vendor.maintenance_stock') }}</p>
                        <p class="mt-1 text-xl font-bold text-blue-700">{{ $item->maintenance_stock }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-3">
                        <p class="{{ $dl }}">{{ __('vendor.total') }}</p>
                        <p class="mt-1 text-xl font-bold text-gray-900">{{ $item->total_stock }}</p>
                    </div>
                </div>
            </section>

            <section class="{{ $card }} p-4 sm:p-5">
                <h2 class="{{ $sectionTitle }}">{{ __('vendor.item_physical_section') }}</h2>
                <div>
                    <p class="{{ $dl }}">{{ __('vendor.condition_status') }}</p>
                    <p class="{{ $dv }}">{{ $conditionLabel }}</p>
                </div>
            </section>

            <section class="{{ $card }} p-4 sm:p-5">
                <h2 class="{{ $sectionTitle }}">{{ __('vendor.item_form_section_visibility') }}</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="{{ $dl }}">{{ __('vendor.created') }}</p>
                        <p class="{{ $dv }}">{{ $item->created_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="{{ $dl }}">{{ __('vendor.last_updated') }}</p>
                        <p class="{{ $dv }}">{{ $item->updated_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') ?? '—' }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
