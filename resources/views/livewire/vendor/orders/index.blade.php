<div class="mx-auto max-w-7xl space-y-3 pb-[max(1rem,env(safe-area-inset-bottom))] sm:space-y-4">
    <div class="space-y-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-3.5">
        <div class="grid w-full min-w-0 grid-cols-[minmax(0,1fr)_auto] items-center gap-x-2 gap-y-1 sm:gap-x-4">
            <h1 class="col-start-1 row-start-1 min-w-0 text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.orders') }}</h1>
            <a wire:navigate href="{{ route('vendor.orders.new') }}"
               class="col-start-2 row-span-2 row-start-1 inline-flex min-h-[44px] shrink-0 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-2.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 sm:min-h-[40px] sm:px-4 sm:text-sm">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                <span>{{ __('vendor.create_order') }}</span>
            </a>
            <p class="col-start-1 row-start-2 text-xs text-gray-600 sm:text-sm">{{ __('vendor.orders_page_subtitle') }}</p>
        </div>
        <div class="relative w-full sm:max-w-md">
            <input type="search"
                   wire:model.live.debounce.400ms="search"
                   placeholder="{{ __('vendor.search_by_order') }}"
                   class="min-h-[44px] w-full rounded-lg border border-gray-200 py-2.5 pl-10 pr-4 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 sm:min-h-[40px]">
            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="fas fa-search text-xs" aria-hidden="true"></i></span>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-2 shadow-sm sm:p-2.5">
        <div class="flex gap-1.5 overflow-x-auto pb-0.5 sm:flex-wrap sm:overflow-visible">
            <button type="button"
                    wire:click="setStatus('')"
                    class="flex shrink-0 items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-semibold transition {{ $status === '' ? 'border-emerald-500/80 bg-emerald-600 text-white' : 'border-gray-200 bg-white text-gray-700 hover:bg-emerald-50/60' }}">
                {{ __('vendor.all_orders') }}
                <span class="rounded-full px-2 py-0.5 text-xs font-bold tabular-nums {{ $status === '' ? 'bg-white/20' : 'bg-gray-100 text-gray-600' }}">{{ $statusCounts['all'] }}</span>
            </button>
            @foreach($statusMeta as $key => $meta)
                <button type="button"
                        wire:click="setStatus('{{ $key }}')"
                        class="flex shrink-0 items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-semibold transition {{ $status === $key ? $meta['tab'] : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50' }}">
                    <i class="fas {{ $meta['icon'] }} text-xs" aria-hidden="true"></i>
                    {{ $meta['label'] }}
                    <span class="rounded-full px-2 py-0.5 text-xs font-bold tabular-nums bg-gray-100 text-gray-600">{{ $statusCounts[$key] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <div wire:loading.class="opacity-60" wire:target="search,status,setStatus">
        @include('vendor.orders.partials.orders-index-list', [
            'orders' => $orders,
            'statusMeta' => $statusMeta,
            'search' => $search,
            'status' => $status,
        ])
    </div>
</div>
