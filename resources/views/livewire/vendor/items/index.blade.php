@php
    $lbl = 'mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 sm:text-xs';
    $inp = 'block w-full min-h-[44px] rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm outline-none transition placeholder:text-gray-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 sm:min-h-[40px]';
@endphp

<div class="space-y-3 sm:space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.items') }}</h1>
            <p class="text-xs text-gray-600 sm:text-sm">{{ __('vendor.manage_rental_items') ?? __('vendor.items') }}</p>
        </div>
        <a wire:navigate href="{{ route('vendor.items.create') }}"
           class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
            <i class="fas fa-plus" aria-hidden="true"></i>
            {{ __('vendor.add_item') }}
        </a>
    </div>

    @if($flashMessage)
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ $flashMessage }}</div>
    @endif

    <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-4">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
            <div class="md:col-span-8">
                <label class="{{ $lbl }}">{{ __('vendor.search') }}</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-search text-xs" aria-hidden="true"></i></span>
                    <input type="search" wire:model.live.debounce.400ms="search" class="{{ $inp }} pl-10" placeholder="{{ __('vendor.items_search_placeholder') }}">
                </div>
            </div>
            <div class="md:col-span-4">
                <label class="{{ $lbl }}">{{ __('vendor.category') }}</label>
                <select wire:model.live="categoryId" class="{{ $inp }}">
                    <option value="">{{ __('vendor.all_categories') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3 border-t border-gray-100 pt-3">
            <button type="button" wire:click="clearFilters" class="text-xs font-medium text-gray-600 hover:text-gray-900">
                {{ __('vendor.clear_filters') }}
            </button>
        </div>
    </div>

    <div wire:loading.class="opacity-60" wire:target="search,categoryId,toggleStatus">
        @include('vendor.items.partials.items-list', [
            'items' => $items,
            'rentalPeriods' => $rentalPeriods,
            'livewireList' => true,
        ])
    </div>
</div>
