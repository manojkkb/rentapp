@php
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

@if($coupons->count() > 0)
    <div class="hidden overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm lg:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/90 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-4">{{ __('vendor.coupon_code') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.coupon_form_discount') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.coupon_form_rules') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.coupon_form_validity') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.status') }}</th>
                        <th class="px-5 py-4"><span class="sr-only">{{ __('vendor.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($coupons as $coupon)
                        @php $life = $coupon->lifecycleStatus(); @endphp
                        <tr class="hover:bg-emerald-50/30" @if($livewireList ?? false) wire:key="coupon-{{ $coupon->uuid }}" @endif>
                            <td class="px-5 py-4 align-top">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-sm">
                                        <i class="fas fa-ticket text-sm" aria-hidden="true"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('vendor.coupons.show', $coupon) }}"
                                           wire:navigate
                                           class="font-mono text-sm font-bold tracking-wide text-gray-900 hover:text-emerald-700">{{ $coupon->code }}</a>
                                        @if($coupon->name)
                                            <p class="mt-0.5 truncate text-xs text-gray-600">{{ $coupon->name }}</p>
                                        @endif
                                        <p class="mt-1 text-[11px] text-gray-400">{{ __('vendor.added_ago', ['time' => $coupon->created_at->diffForHumans()]) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <span class="inline-flex rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-bold text-violet-800 ring-1 ring-violet-100">
                                    {{ $coupon->discountLabel() }}
                                </span>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $coupon->type === 'percent' ? __('vendor.percentage') : __('vendor.fixed_amount') }}
                                </p>
                            </td>
                            <td class="px-5 py-4 align-top text-xs text-gray-600">
                                <ul class="space-y-1">
                                    @if($coupon->min_order_amount > 0)
                                        <li><i class="fas fa-shopping-bag mr-1.5 w-3 text-center text-gray-400" aria-hidden="true"></i>{{ __('vendor.min') }} ₹{{ number_format($coupon->min_order_amount, 0) }}</li>
                                    @endif
                                    @if($coupon->max_discount_amount)
                                        <li><i class="fas fa-arrow-down mr-1.5 w-3 text-center text-gray-400" aria-hidden="true"></i>{{ __('vendor.max') }} ₹{{ number_format($coupon->max_discount_amount, 0) }}</li>
                                    @endif
                                    <li>
                                        <i class="fas fa-sync-alt mr-1.5 w-3 text-center text-gray-400" aria-hidden="true"></i>
                                        @if($coupon->usage_limit)
                                            {{ __('vendor.coupon_usage_progress', ['used' => $coupon->used_count, 'limit' => $coupon->usage_limit]) }}
                                        @else
                                            {{ __('vendor.unlimited') }}
                                        @endif
                                    </li>
                                </ul>
                            </td>
                            <td class="px-5 py-4 align-top text-xs text-gray-600">
                                @if($coupon->start_date || $coupon->end_date)
                                    <p>
                                        <i class="fas fa-calendar-days mr-1.5 text-gray-400" aria-hidden="true"></i>
                                        @if($coupon->start_date && $coupon->end_date)
                                            {{ $coupon->start_date->format('d M Y') }} – {{ $coupon->end_date->format('d M Y') }}
                                        @elseif($coupon->end_date)
                                            {{ __('vendor.until') }} {{ $coupon->end_date->format('d M Y') }}
                                        @else
                                            {{ __('vendor.from') }} {{ $coupon->start_date->format('d M Y') }}
                                        @endif
                                    </p>
                                @else
                                    <span class="text-gray-400">{{ __('vendor.coupon_no_expiry') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 align-top">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 {{ $statusTone[$life] ?? $statusTone['inactive'] }}">
                                    {{ $statusLabel[$life] ?? $life }}
                                </span>
                                @if($livewireList ?? false)
                                    <div class="mt-2" wire:key="coupon-toggle-{{ $coupon->uuid }}">
                                        <button type="button"
                                                wire:click="toggleStatus(@js($coupon->uuid))"
                                                wire:loading.attr="disabled"
                                                wire:target="toggleStatus(@js($coupon->uuid))"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 {{ $coupon->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}"
                                                title="{{ $coupon->is_active ? __('vendor.deactivate') : __('vendor.activate') }}">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $coupon->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('vendor.coupons.show', $coupon) }}"
                                       wire:navigate
                                       class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                       title="{{ __('vendor.view') }}">
                                        <i class="fas fa-eye text-[11px]" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('vendor.coupons.edit', $coupon) }}"
                                       wire:navigate
                                       class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                       title="{{ __('vendor.edit') }}">
                                        <i class="fas fa-pen text-[11px]" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $coupons->links() }}</div>
        @endif
    </div>

    <div class="space-y-3 lg:hidden">
        @foreach($coupons as $coupon)
            @php $life = $coupon->lifecycleStatus(); @endphp
            <article class="overflow-hidden rounded-2xl border border-dashed border-violet-200/80 bg-gradient-to-br from-white to-violet-50/40 shadow-sm"
                     @if($livewireList ?? false) wire:key="coupon-m-{{ $coupon->uuid }}" @endif>
                <div class="border-b border-dashed border-violet-100 px-4 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('vendor.coupons.show', $coupon) }}"
                                   wire:navigate
                                   class="font-mono text-base font-bold tracking-wider text-gray-900 hover:text-emerald-700">{{ $coupon->code }}</a>
                                <span class="inline-flex rounded-md bg-violet-100 px-2 py-0.5 text-[11px] font-bold text-violet-800">
                                    {{ $coupon->discountLabel() }}
                                </span>
                            </div>
                            @if($coupon->name)
                                <p class="mt-1 text-sm text-gray-700">{{ $coupon->name }}</p>
                            @endif
                        </div>
                        @if($livewireList ?? false)
                            <button type="button"
                                    wire:click="toggleStatus(@js($coupon->uuid))"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleStatus(@js($coupon->uuid))"
                                    class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full {{ $coupon->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $coupon->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        @endif
                    </div>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 {{ $statusTone[$life] ?? $statusTone['inactive'] }}">
                            {{ $statusLabel[$life] ?? $life }}
                        </span>
                        @if($coupon->min_order_amount > 0)
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600">{{ __('vendor.min') }} ₹{{ number_format($coupon->min_order_amount, 0) }}</span>
                        @endif
                        @if($coupon->usage_limit)
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600">{{ $coupon->used_count }}/{{ $coupon->usage_limit }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-between gap-2 px-4 py-3">
                    <p class="text-[11px] text-gray-500">
                        @if($coupon->end_date)
                            {{ __('vendor.until') }} {{ $coupon->end_date->format('d M Y') }}
                        @else
                            {{ __('vendor.coupon_no_expiry') }}
                        @endif
                    </p>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('vendor.coupons.show', $coupon) }}"
                           wire:navigate
                           class="text-xs font-semibold text-gray-700 hover:underline">
                            {{ __('vendor.view') }}
                        </a>
                        <a href="{{ route('vendor.coupons.edit', $coupon) }}"
                           wire:navigate
                           class="text-xs font-semibold text-emerald-700 hover:underline">
                            {{ __('vendor.edit') }}
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
        @if($coupons->hasPages())
            <div class="pt-1">{{ $coupons->links() }}</div>
        @endif
    </div>
@else
    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center shadow-sm">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
            <i class="fas fa-ticket-alt text-2xl" aria-hidden="true"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.no_coupons_found') }}</h3>
        <p class="mx-auto mt-2 max-w-md text-sm text-gray-600">
            @if(($search ?? '') !== '' || ($typeFilter ?? '') !== '' || ($statusFilter ?? '') !== '')
                {{ __('vendor.coupons_empty_search') }}
            @else
                {{ __('vendor.create_first_coupon') }}
            @endif
        </p>
        <a href="{{ route('vendor.coupons.create') }}"
           wire:navigate
           class="mt-6 inline-flex min-h-[44px] items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
            <i class="fas fa-plus text-xs" aria-hidden="true"></i>
            {{ __('vendor.add_coupon') }}
        </a>
    </div>
@endif
