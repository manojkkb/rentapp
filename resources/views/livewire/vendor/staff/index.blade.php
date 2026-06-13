<div class="mx-auto max-w-7xl space-y-3 pb-[max(1rem,env(safe-area-inset-bottom))] sm:space-y-4">
    <div class="space-y-3 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm sm:p-4">
        <div class="grid w-full min-w-0 grid-cols-[minmax(0,1fr)_auto] items-center gap-x-2 gap-y-1 sm:gap-x-4">
            <h1 class="col-start-1 row-start-1 min-w-0 text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.staff_members') }}</h1>
            <a wire:navigate href="{{ route('vendor.staff.create') }}"
               class="col-start-2 row-span-2 row-start-1 inline-flex min-h-[44px] shrink-0 items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-2.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 sm:min-h-[40px] sm:px-4 sm:text-sm">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                <span>{{ __('vendor.add_staff') }}</span>
            </a>
            <p class="col-start-1 row-start-2 text-xs text-gray-600 sm:text-sm">
                {{ __('vendor.staff_page_subtitle') }}
                <span class="mt-0.5 block font-medium text-gray-800 sm:mt-0 sm:inline sm:before:content-['·_']">
                    {{ __('vendor.total_staff_count', ['count' => $staff->total()]) }}
                </span>
            </p>
        </div>

        <div class="relative w-full sm:max-w-md">
            <label for="staff-search" class="sr-only">{{ __('vendor.search') }}</label>
            <input type="search"
                   id="staff-search"
                   wire:model.live.debounce.400ms="search"
                   placeholder="{{ __('vendor.staff_search_placeholder') }}"
                   autocomplete="off"
                   class="min-h-[44px] w-full rounded-xl border border-gray-200 py-2.5 pl-10 pr-10 text-base sm:min-h-[40px] sm:text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <i class="fas fa-search text-xs" aria-hidden="true"></i>
            </span>
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <i wire:loading wire:target="search" class="fas fa-spinner fa-spin text-xs" aria-hidden="true"></i>
            </span>
        </div>
    </div>

    @if($flashMessage)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-900"
             wire:key="staff-flash">
            <i class="fas fa-check-circle mr-1.5 text-emerald-600" aria-hidden="true"></i>
            {{ $flashMessage }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
         wire:loading.class="opacity-60"
         wire:target="search,toggleStatus,deleteStaff,gotoPage,previousPage,nextPage">
        @include('vendor.staff.partials.staff-list', [
            'staff' => $staff,
            'livewireList' => true,
            'search' => $search,
        ])
    </div>
</div>
