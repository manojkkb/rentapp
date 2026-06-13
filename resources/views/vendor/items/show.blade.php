@extends('vendor.layouts.app')

@section('title', __('vendor.item_details'))
@section('page-title', __('vendor.item_details'))

@section('content')
@php
    $card = 'overflow-hidden rounded-xl border border-gray-200/90 bg-white';
    $head = 'border-b border-gray-100 bg-gradient-to-r from-slate-50 to-emerald-50/20 px-3 py-2.5 sm:px-4 sm:py-3';
    $body = 'p-3 sm:p-4';
    $dl = 'text-[11px] font-semibold uppercase tracking-wide text-gray-500';
    $dv = 'mt-0.5 text-sm font-medium text-gray-900';
    $grid2 = 'grid grid-cols-2 gap-3 sm:gap-4';
    $grid3 = 'grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4';
    $rentalLabel = $rentalPeriods[$item->rental_period] ?? $item->rental_period;
    $conditionLabel = $conditionLabels[$item->condition_status] ?? $item->condition_status;
    $usesVariants = $item->usesVariants();
    $variantAttributes = $item->variantAttributes;
    $variants = $item->variants;
    $variantPrices = $usesVariants ? $variants->pluck('price')->map(fn ($p) => (float) $p) : collect();
    $minVariantPrice = $variantPrices->min();
    $maxVariantPrice = $variantPrices->max();
@endphp

<div class="mx-auto w-full max-w-4xl space-y-3 sm:space-y-4">
    <header class="flex flex-wrap items-start justify-between gap-2">
        <div class="min-w-0">
            <a href="{{ route('vendor.items.index') }}"
               class="mb-1.5 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-emerald-600">
                <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                {{ __('vendor.back_to_items') }}
            </a>
            <h1 class="truncate text-lg font-bold text-gray-900 sm:text-xl">{{ $item->name }}</h1>
            <p class="mt-0.5 font-mono text-xs text-gray-500">{{ $item->item_code }}</p>
        </div>
        @vendorCan('items.edit')
        <a href="{{ route('vendor.items.edit', $item) }}"
           class="inline-flex min-h-[40px] shrink-0 items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            <i class="fas fa-edit text-xs" aria-hidden="true"></i>
            {{ __('vendor.edit_item') }}
        </a>
        @endvendorCan
    </header>

    {{-- Hero: photo + key facts --}}
    <section class="{{ $card }}">
        <div class="grid grid-cols-1 gap-0 sm:grid-cols-[11rem_minmax(0,1fr)]">
            <div class="flex items-center justify-center border-b border-gray-100 bg-gradient-to-br from-emerald-50/50 to-slate-50 p-4 sm:border-b-0 sm:border-r">
                @if($item->photo_url)
                    <img src="{{ $item->photo_url }}" alt=""
                         class="h-36 w-36 rounded-xl border border-gray-200 object-cover sm:h-32 sm:w-32">
                @else
                    <div class="flex h-36 w-36 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 sm:h-32 sm:w-32">
                        <i class="fas fa-box text-4xl text-emerald-500" aria-hidden="true"></i>
                    </div>
                @endif
            </div>
            <div class="{{ $body }} space-y-3">
                <div class="flex flex-wrap gap-1.5">
                    @if($usesVariants)
                        <span class="inline-flex items-center rounded-full bg-teal-50 px-2 py-0.5 text-[11px] font-semibold text-teal-700 ring-1 ring-teal-100">
                            <i class="fas fa-layer-group mr-1 text-[9px]" aria-hidden="true"></i>{{ __('vendor.item_has_variants_badge') }}
                        </span>
                    @endif
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-gray-50 text-gray-600 ring-gray-100' }}">
                        {{ $item->is_active ? __('vendor.active') : __('vendor.inactive') }}
                    </span>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 {{ $item->is_available ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-orange-50 text-orange-700 ring-orange-100' }}">
                        {{ $item->is_available ? __('vendor.available_for_rent') : __('vendor.not_available') }}
                    </span>
                </div>

                <div class="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5">
                    @if($usesVariants && $variants->isNotEmpty())
                        @if($minVariantPrice === $maxVariantPrice)
                            <p class="text-xl font-bold text-gray-900">₹{{ number_format((float) $minVariantPrice, 2) }}</p>
                        @elseif($minVariantPrice !== null && $maxVariantPrice !== null)
                            <p class="text-xl font-bold text-gray-900">
                                {{ __('vendor.item_price_range', [
                                    'min' => number_format((float) $minVariantPrice, 2),
                                    'max' => number_format((float) $maxVariantPrice, 2),
                                ]) }}
                            </p>
                        @else
                            <p class="text-xl font-bold text-gray-900">—</p>
                        @endif
                        <p class="text-xs text-gray-500">{{ $rentalLabel }} · {{ __('vendor.item_variants_count', ['count' => $variants->count()]) }}</p>
                    @else
                        <p class="text-xl font-bold text-gray-900">₹{{ number_format((float) $item->price, 2) }}</p>
                        <p class="text-xs text-gray-500">{{ $rentalLabel }}</p>
                    @endif
                </div>

                <div class="{{ $grid2 }}">
                    <div>
                        <p class="{{ $dl }}">{{ __('vendor.category') }}</p>
                        <p class="{{ $dv }}">{{ $item->category?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="{{ $dl }}">{{ __('vendor.condition_status') }}</p>
                        <p class="{{ $dv }}">{{ $conditionLabel }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="{{ $dl }}">{{ __('vendor.current_url') }}</p>
                        <p class="{{ $dv }}"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs">{{ $item->slug }}</code></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($item->images->isNotEmpty())
    <section class="{{ $card }}">
        <div class="{{ $head }}">
            <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.item_form_gallery_images') }}</h2>
        </div>
        <div class="{{ $body }}">
            <div class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-6">
                @foreach($item->images as $image)
                    <a href="{{ $image->url }}" target="_blank" rel="noopener"
                       class="aspect-square overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                        <img src="{{ $image->url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if(filled($item->description))
    <section class="{{ $card }}">
        <div class="{{ $head }}">
            <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.description') }}</h2>
        </div>
        <div class="{{ $body }}">
            <p class="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{{ $item->description }}</p>
        </div>
    </section>
    @endif

    {{-- Pricing, fees & rental --}}
    <section class="{{ $card }}">
        <div class="{{ $head }}">
            <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.item_form_section_combined_pricing') }}</h2>
        </div>
        <div class="{{ $body }} space-y-4">
            <div class="{{ $grid2 }}">
                <div>
                    <p class="{{ $dl }}">{{ __('vendor.minimum_rental_duration') }}</p>
                    <p class="{{ $dv }}">{{ $item->min_rental_duration }}</p>
                </div>
                <div>
                    <p class="{{ $dl }}">{{ __('vendor.maximum_rental_duration') }}</p>
                    <p class="{{ $dv }}">{{ $item->max_rental_duration }}</p>
                </div>
            </div>
            <div class="{{ $grid3 }}">
                <div>
                    <p class="{{ $dl }}">{{ __('vendor.security_deposit') }}</p>
                    <p class="{{ $dv }}">₹{{ number_format((float) $item->security_deposit, 2) }}</p>
                </div>
                <div>
                    <p class="{{ $dl }}">{{ __('vendor.replacement_cost') }}</p>
                    <p class="{{ $dv }}">₹{{ number_format((float) $item->replacement_cost, 2) }}</p>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <p class="{{ $dl }}">{{ __('vendor.late_fee') }}</p>
                    <p class="{{ $dv }}">₹{{ number_format((float) $item->late_fee, 2) }}</p>
                </div>
            </div>
        </div>
    </section>

    @if($usesVariants && $variants->isNotEmpty())
    <section class="{{ $card }}">
        <div class="{{ $head }}">
            <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.item_show_variants_section') }}</h2>
        </div>
        <div class="{{ $body }}">
            <div class="mb-3">
                @include('vendor.items.partials.stock-breakdown', [
                    'item' => $item,
                    'layout' => 'cards',
                    'dl' => $dl,
                    'showUnavailable' => false,
                ])
            </div>
            <div class="mb-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                <div class="rounded-lg border border-emerald-100 bg-emerald-50/50 px-3 py-2.5">
                    <p class="{{ $dl }}">{{ __('vendor.stock_quantity') }}</p>
                    <p class="mt-0.5 text-lg font-bold text-emerald-700">{{ $variants->sum('stock') }}</p>
                </div>
                <div class="rounded-lg border border-orange-100 bg-orange-50/50 px-3 py-2.5">
                    <p class="{{ $dl }}">{{ __('vendor.damaged_stock') }}</p>
                    <p class="mt-0.5 text-lg font-bold text-orange-700">{{ $variants->sum('damaged_stock') }}</p>
                </div>
                <div class="rounded-lg border border-emerald-100 bg-emerald-50/50 px-3 py-2.5">
                    <p class="{{ $dl }}">{{ __('vendor.maintenance_stock') }}</p>
                    <p class="mt-0.5 text-lg font-bold text-emerald-700">{{ $variants->sum('maintenance_stock') }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
                    <p class="{{ $dl }}">{{ __('vendor.total') }}</p>
                    <p class="mt-0.5 text-lg font-bold text-gray-900">{{ $variants->sum(fn ($v) => $v->total_stock) }}</p>
                </div>
            </div>

            <div class="overflow-x-auto -mx-1 px-1">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-[10px] uppercase tracking-wide text-gray-500">
                            @foreach($variantAttributes as $attribute)
                                <th class="px-2 py-2 font-semibold whitespace-nowrap">{{ $attribute->name }}</th>
                            @endforeach
                            <th class="px-2 py-2 font-semibold whitespace-nowrap">{{ __('vendor.item_variant_code') }}</th>
                            <th class="px-2 py-2 font-semibold whitespace-nowrap">{{ __('vendor.price') }}</th>
                            <th class="px-2 py-2 font-semibold whitespace-nowrap">{{ __('vendor.stock') }}</th>
                            <th class="px-2 py-2 font-semibold whitespace-nowrap">{{ __('vendor.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($variants as $variant)
                            @php
                                $attributeValues = is_array($variant->getAttribute('attributes')) ? $variant->getAttribute('attributes') : [];
                                $variantCondition = $conditionLabels[$variant->condition_status] ?? $variant->condition_status;
                            @endphp
                            <tr>
                                @foreach($variantAttributes as $attribute)
                                    <td class="px-2 py-2 text-gray-900 whitespace-nowrap">
                                        {{ filled($attributeValues[$attribute->slug] ?? null) ? $attributeValues[$attribute->slug] : '—' }}
                                    </td>
                                @endforeach
                                <td class="px-2 py-2 font-mono text-xs text-gray-600 whitespace-nowrap">{{ $variant->variant_code }}</td>
                                <td class="px-2 py-2 font-semibold whitespace-nowrap">₹{{ number_format((float) $variant->price, 2) }}</td>
                                <td class="px-2 py-2 whitespace-nowrap">{{ $variant->rentableAvailableStock() }}</td>
                                <td class="px-2 py-2 whitespace-nowrap">
                                    @if(!$variant->manage_stock)
                                        <span class="inline-flex rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-semibold text-sky-800">{{ __('vendor.stock_not_tracked') }}</span>
                                    @elseif($variant->is_active && $variant->is_available && $variant->rentableAvailableStock() > 0)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">{{ __('vendor.available') }}</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">{{ __('vendor.unavailable') }}</span>
                                    @endif
                                    <p class="mt-0.5 text-[10px] text-gray-500">{{ $variantCondition }}</p>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    @else
    <section class="{{ $card }}">
        <div class="{{ $head }}">
            <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.item_form_section_inventory') }}</h2>
        </div>
        <div class="{{ $body }}">
            <div class="mb-3 {{ $grid2 }}">
                <div>
                    <p class="{{ $dl }}">{{ __('vendor.track_stock_quantity') }}</p>
                    <p class="{{ $dv }}">{{ $item->manage_stock ? __('vendor.yes') : __('vendor.no') }}</p>
                </div>
            </div>
            <div class="mb-3">
                @include('vendor.items.partials.stock-breakdown', [
                    'item' => $item,
                    'layout' => 'cards',
                    'dl' => $dl,
                    'showUnavailable' => false,
                ])
            </div>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                <div class="rounded-lg border border-emerald-100 bg-emerald-50/50 px-3 py-2.5">
                    <p class="{{ $dl }}">{{ __('vendor.stock_quantity') }}</p>
                    <p class="mt-0.5 text-lg font-bold text-emerald-700">{{ $item->stock }}</p>
                </div>
                <div class="rounded-lg border border-orange-100 bg-orange-50/50 px-3 py-2.5">
                    <p class="{{ $dl }}">{{ __('vendor.damaged_stock') }}</p>
                    <p class="mt-0.5 text-lg font-bold text-orange-700">{{ $item->damaged_stock }}</p>
                </div>
                <div class="rounded-lg border border-emerald-100 bg-emerald-50/50 px-3 py-2.5">
                    <p class="{{ $dl }}">{{ __('vendor.maintenance_stock') }}</p>
                    <p class="mt-0.5 text-lg font-bold text-emerald-700">{{ $item->maintenance_stock }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5">
                    <p class="{{ $dl }}">{{ __('vendor.total') }}</p>
                    <p class="mt-0.5 text-lg font-bold text-gray-900">{{ $item->total_stock }}</p>
                </div>
            </div>
        </div>
    </section>
    @endif

    <section class="{{ $card }}">
        <div class="{{ $head }}">
            <h2 class="text-sm font-bold text-gray-900">{{ __('vendor.item_form_section_visibility') }}</h2>
        </div>
        <div class="{{ $body }}">
            <div class="{{ $grid2 }}">
                <div>
                    <p class="{{ $dl }}">{{ __('vendor.created') }}</p>
                    <p class="{{ $dv }}">{{ $item->created_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') ?? '—' }}</p>
                </div>
                <div>
                    <p class="{{ $dl }}">{{ __('vendor.last_updated') }}</p>
                    <p class="{{ $dv }}">{{ $item->updated_at?->timezone(config('app.timezone'))->format('d M Y, h:i A') ?? '—' }}</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
