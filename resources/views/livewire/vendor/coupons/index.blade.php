<div class="mx-auto max-w-7xl space-y-3 pb-[max(1rem,env(safe-area-inset-bottom))] sm:space-y-4">
    <div class="space-y-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-3.5">
        <div class="grid w-full min-w-0 grid-cols-[minmax(0,1fr)_auto] items-center gap-x-2 gap-y-1 sm:gap-x-4">
            <h1 class="col-start-1 row-start-1 min-w-0 text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.coupons') }}</h1>
            <a href="{{ route('vendor.coupons.create') }}"
               wire:navigate
               class="col-start-2 row-span-2 row-start-1 inline-flex min-h-[44px] shrink-0 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-2.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 sm:min-h-[40px] sm:px-4 sm:text-sm">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                <span>{{ __('vendor.add_coupon') }}</span>
            </a>
            <p class="col-start-1 row-start-2 text-xs text-gray-600 sm:text-sm">
                {{ __('vendor.coupons_page_subtitle') }}
                <span class="mt-0.5 block font-medium text-gray-800 sm:mt-0 sm:inline sm:before:content-['·_']">
                    {{ __('vendor.total_coupons_count', ['count' => $stats['total']]) }}
                </span>
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-800 ring-1 ring-emerald-100">
                <i class="fas fa-check-circle text-[10px]" aria-hidden="true"></i>
                {{ __('vendor.coupons_stat_active') }}: {{ $stats['active'] }}
            </span>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="relative sm:col-span-2 lg:col-span-2">
                <input type="search"
                       wire:model.live.debounce.400ms="search"
                       placeholder="{{ __('vendor.search_coupon_placeholder') }}"
                       autocomplete="off"
                       class="min-h-[44px] w-full rounded-lg border border-gray-200 py-2.5 pl-10 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 sm:min-h-[40px]">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <i class="fas fa-search text-xs" aria-hidden="true"></i>
                </span>
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <i wire:loading wire:target="search" class="fas fa-spinner fa-spin text-xs" aria-hidden="true"></i>
                </span>
            </div>
            <div>
                <label class="sr-only">{{ __('vendor.coupons_filter_type') }}</label>
                <select wire:model.live="typeFilter"
                        class="min-h-[44px] w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 sm:min-h-[40px]">
                    <option value="">{{ __('vendor.all_types') }}</option>
                    <option value="fixed">{{ __('vendor.fixed_amount') }}</option>
                    <option value="percent">{{ __('vendor.percentage') }}</option>
                </select>
            </div>
            <div>
                <label class="sr-only">{{ __('vendor.coupons_filter_status') }}</label>
                <select wire:model.live="statusFilter"
                        class="min-h-[44px] w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 sm:min-h-[40px]">
                    <option value="">{{ __('vendor.all_status') }}</option>
                    <option value="active">{{ __('vendor.active') }}</option>
                    <option value="inactive">{{ __('vendor.inactive') }}</option>
                </select>
            </div>
        </div>

        @if($search !== '' || $typeFilter !== '' || $statusFilter !== '')
            <button type="button"
                    wire:click="clearFilters"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900">
                <i class="fas fa-times-circle mr-1" aria-hidden="true"></i>{{ __('vendor.clear_filters') }}
            </button>
        @endif
    </div>

    @if($flashMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-900"
             wire:key="coupons-flash">
            <i class="fas fa-check-circle mr-1.5 text-emerald-600" aria-hidden="true"></i>
            {{ $flashMessage }}
        </div>
    @endif

    <div wire:loading.class="opacity-60"
         wire:target="search,typeFilter,statusFilter,toggleStatus,gotoPage,previousPage,nextPage">
        @include('vendor.coupons.partials.coupons-list', [
            'coupons' => $coupons,
            'livewireList' => true,
            'search' => $search,
            'typeFilter' => $typeFilter,
            'statusFilter' => $statusFilter,
        ])
    </div>

</div>
