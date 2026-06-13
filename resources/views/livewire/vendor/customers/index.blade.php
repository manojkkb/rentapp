<div class="mx-auto max-w-7xl space-y-3 pb-[max(1rem,env(safe-area-inset-bottom))] sm:space-y-4">
    <div class="space-y-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm sm:p-3.5">
        <div class="grid w-full min-w-0 grid-cols-[minmax(0,1fr)_auto] items-center gap-x-2 gap-y-1 sm:gap-x-4">
            <h1 class="col-start-1 row-start-1 min-w-0 text-lg font-bold text-gray-900 sm:text-xl">{{ __('vendor.customers') }}</h1>
            <button type="button"
                    wire:click="openCreateModal"
                    class="col-start-2 row-span-2 row-start-1 inline-flex min-h-[44px] shrink-0 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-2.5 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 sm:min-h-[40px] sm:px-4 sm:text-sm">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                <span>{{ __('vendor.add_customer') }}</span>
            </button>
            <p class="col-start-1 row-start-2 text-xs text-gray-600 sm:text-sm">
                {{ __('vendor.customers_page_subtitle') }}
                <span class="mt-0.5 block font-medium text-gray-800 sm:mt-0 sm:inline sm:before:content-['·_']">
                    {{ __('vendor.total_customers_count', ['count' => $customers->total()]) }}
                </span>
            </p>
        </div>
        <div class="relative w-full sm:max-w-md">
            <input type="search"
                   wire:model.live.debounce.400ms="search"
                   placeholder="{{ __('vendor.search_customers') }}"
                   autocomplete="off"
                   class="min-h-[44px] w-full rounded-lg border border-gray-200 py-2.5 pl-10 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 sm:min-h-[40px]">
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
             wire:key="customers-flash">
            <i class="fas fa-check-circle mr-1.5 text-emerald-600" aria-hidden="true"></i>
            {{ $flashMessage }}
        </div>
    @endif

    <div wire:loading.class="opacity-60" wire:target="search,toggleStatus,saveCustomer,gotoPage,previousPage,nextPage">
        @include('vendor.customers.partials.customers-list', [
            'customers' => $customers,
            'livewireList' => true,
            'search' => $search,
        ])
    </div>

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-end justify-center sm:items-center sm:p-4"
             wire:key="create-customer-modal"
             x-data
             x-trap.noscroll="true"
             @keydown.escape.window="$wire.closeCreateModal()">
            <div class="fixed inset-0 bg-black/50"
                 wire:click="closeCreateModal"
                 aria-hidden="true"></div>

            <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-t-2xl border border-gray-200 bg-white shadow-2xl sm:rounded-2xl"
                 @click.stop>
                <form wire:submit="saveCustomer" class="p-4 sm:p-5">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <h3 class="text-base font-bold text-gray-900 sm:text-lg">{{ __('vendor.add_customer') }}</h3>
                        <button type="button"
                                wire:click="closeCreateModal"
                                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                                aria-label="{{ __('vendor.cancel') }}">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.customer_name') }}</label>
                            <input type="text"
                                   wire:model="newCustomerName"
                                   class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            @error('newCustomerName') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.mobile') }}</label>
                            <input type="tel"
                                   wire:model="newCustomerMobile"
                                   maxlength="10"
                                   inputmode="numeric"
                                   class="block w-full min-h-[40px] rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                            @error('newCustomerMobile') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-0.5 block text-sm font-medium text-gray-800">{{ __('vendor.address') }}</label>
                            <textarea wire:model="newCustomerAddress"
                                      rows="2"
                                      class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20"></textarea>
                            @error('newCustomerAddress') <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button type="button"
                                wire:click="closeCreateModal"
                                class="inline-flex min-h-[44px] items-center justify-center rounded-lg border border-gray-200 px-4 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:min-h-[40px]">
                            {{ __('vendor.cancel') }}
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveCustomer"
                                class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-70 sm:min-h-[40px]">
                            <span wire:loading.remove wire:target="saveCustomer">
                                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                                {{ __('vendor.add_customer') }}
                            </span>
                            <span wire:loading wire:target="saveCustomer">
                                <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
