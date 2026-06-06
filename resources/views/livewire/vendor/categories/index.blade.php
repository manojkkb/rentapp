<div>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('vendor.categories') }}</h2>
            <p class="text-sm text-gray-600">{{ __('vendor.manage_categories') ?? __('vendor.categories') }}</p>
        </div>
        <a wire:navigate href="{{ route('vendor.categories.create') }}"
           class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white hover:bg-emerald-700">
            <i class="fas fa-plus mr-2" aria-hidden="true"></i>
            {{ __('vendor.add_category') }}
        </a>
    </div>

    @if($flashMessage)
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ $flashMessage }}</div>
    @endif

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="relative">
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-search" aria-hidden="true"></i></span>
            <input type="search"
                   wire:model.live.debounce.400ms="search"
                   class="w-full rounded-lg border border-gray-300 py-2.5 pl-10 focus:border-emerald-500 focus:ring-emerald-500"
                   placeholder="{{ __('vendor.search') }} {{ __('vendor.categories') }}...">
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm" wire:loading.class="opacity-60" wire:target="search,toggleStatus">
        @include('vendor.categories.partials.categories-list', [
            'categories' => $categories,
            'livewireList' => true,
        ])
    </div>
</div>
