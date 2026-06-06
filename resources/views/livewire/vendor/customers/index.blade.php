<div>
    <div class="mb-6 flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('vendor.customers') }}</h2>
            <p class="text-sm text-gray-600">
                <i class="fas fa-users mr-1 text-emerald-600" aria-hidden="true"></i>
                <span class="font-medium">{{ __('vendor.total_customers_count', ['count' => $customers->total()]) }}</span>
            </p>
        </div>
        <button type="button"
                wire:click="openCreateModal"
                class="inline-flex shrink-0 items-center whitespace-nowrap rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 active:scale-95 active:bg-emerald-800">
            <i class="fas fa-plus mr-2" aria-hidden="true"></i>
            {{ __('vendor.add_customer') }}
        </button>
    </div>

    @if($flashMessage)
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800"
             wire:key="customers-flash">
            <i class="fas fa-check-circle mr-1.5" aria-hidden="true"></i>
            {{ $flashMessage }}
        </div>
    @endif

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas fa-search text-gray-400" aria-hidden="true"></i>
            </div>
            <input type="search"
                   wire:model.live.debounce.400ms="search"
                   class="block w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-10 focus:border-emerald-500 focus:ring-emerald-500"
                   placeholder="{{ __('vendor.search') }} {{ __('vendor.customers') }}..."
                   autocomplete="off">
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <i wire:loading wire:target="search" class="fas fa-spinner fa-spin text-gray-400" aria-hidden="true"></i>
            </div>
        </div>
    </div>

    <div class="relative rounded-xl border border-gray-200 bg-white shadow-sm" wire:loading.class="opacity-60" wire:target="search,toggleStatus,saveCustomer,gotoPage,previousPage,nextPage">
        @include('vendor.customers.partials.customers-list', [
            'customers' => $customers,
            'livewireList' => true,
        ])
    </div>

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto"
             wire:key="create-customer-modal"
             x-data
             x-trap.noscroll="true"
             @keydown.escape.window="$wire.closeCreateModal()">
            <div class="flex min-h-screen items-center justify-center px-4 pb-20 pt-4 text-center">
                <div class="fixed inset-0 bg-black/50 transition-opacity"
                     wire:click="closeCreateModal"
                     aria-hidden="true"></div>

                <div class="relative z-10 inline-block w-full max-w-lg overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl sm:my-8 sm:align-middle"
                     @click.stop>
                    <form wire:submit="saveCustomer" class="p-5 sm:p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.add_customer') }}</h3>
                            <button type="button"
                                    wire:click="closeCreateModal"
                                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                                    aria-label="{{ __('vendor.cancel') }}">
                                <i class="fas fa-times" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.customer_name') }}</label>
                                <input type="text"
                                       wire:model="newCustomerName"
                                       class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                @error('newCustomerName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.mobile') }}</label>
                                <input type="tel"
                                       wire:model="newCustomerMobile"
                                       maxlength="10"
                                       inputmode="numeric"
                                       class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                @error('newCustomerMobile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.address') }}</label>
                                <textarea wire:model="newCustomerAddress"
                                          rows="2"
                                          class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                                @error('newCustomerAddress') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button type="button"
                                    wire:click="closeCreateModal"
                                    class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('vendor.cancel') }}
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="saveCustomer"
                                    class="inline-flex h-10 items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-70">
                                <span wire:loading.remove wire:target="saveCustomer">
                                    <i class="fas fa-save mr-1.5" aria-hidden="true"></i>
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
        </div>
    @endif
</div>
