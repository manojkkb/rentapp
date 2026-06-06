<div>
    <div class="mb-6 flex items-start justify-between gap-3">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('vendor.coupons') }}</h2>
            <p class="text-sm text-gray-600">
                <i class="fas fa-tag mr-1 text-emerald-600" aria-hidden="true"></i>
                <span class="font-medium">{{ $coupons->total() }}</span> {{ __('vendor.total_coupons') }}
            </p>
        </div>
        <button type="button"
                wire:click="openCreateModal"
                class="inline-flex shrink-0 items-center whitespace-nowrap rounded-lg bg-emerald-600 px-4 py-2 font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 active:scale-95">
            <i class="fas fa-plus mr-2" aria-hidden="true"></i>
            {{ __('vendor.add_coupon') }}
        </button>
    </div>

    @if($flashMessage)
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ $flashMessage }}</div>
    @endif

    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label class="mb-2 block text-xs font-medium text-gray-700">{{ __('vendor.search') }}</label>
                <input type="search"
                       wire:model.live.debounce.400ms="search"
                       placeholder="{{ __('vendor.search_coupon_placeholder') }}"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="mb-2 block text-xs font-medium text-gray-700">{{ __('vendor.type') }}</label>
                <select wire:model.live="typeFilter" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">{{ __('vendor.all_types') }}</option>
                    <option value="fixed">{{ __('vendor.fixed_amount') }}</option>
                    <option value="percent">{{ __('vendor.percentage') }}</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-medium text-gray-700">{{ __('vendor.status') }}</label>
                <select wire:model.live="statusFilter" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">{{ __('vendor.all_status') }}</option>
                    <option value="active">{{ __('vendor.active') }}</option>
                    <option value="inactive">{{ __('vendor.inactive') }}</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="button" wire:click="clearFilters" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                <i class="fas fa-times-circle mr-1" aria-hidden="true"></i>{{ __('vendor.clear_filters') }}
            </button>
        </div>
    </div>

    <div wire:loading.class="opacity-60" wire:target="search,typeFilter,statusFilter,toggleStatus,deleteCoupon,saveCoupon">
        @include('vendor.coupons._list', ['coupons' => $coupons, 'livewireList' => true])
    </div>

    @if($showModal)
        <div class="fixed inset-0 z-[70] overflow-y-auto" @keydown.escape.window="$wire.closeModal()">
            <div class="fixed inset-0 bg-gray-900/50" wire:click="closeModal" aria-hidden="true"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white shadow-2xl" @click.stop>
                    <form wire:submit="saveCoupon" class="p-5 sm:p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-gray-900">
                                {{ $editingUuid ? __('vendor.edit_coupon') : __('vendor.add_coupon') }}
                            </h3>
                            <button type="button" wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.coupon_code') ?? 'Code' }} *</label>
                                <input type="text" wire:model="code" class="w-full rounded-lg border-gray-300 uppercase focus:border-emerald-500 focus:ring-emerald-500">
                                @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.name') }}</label>
                                <input type="text" wire:model="name" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.type') }} *</label>
                                    <select wire:model.live="type" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                        <option value="percent">{{ __('vendor.percentage') }}</option>
                                        <option value="fixed">{{ __('vendor.fixed_amount') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.value') ?? 'Value' }} *</label>
                                    <input type="number" step="0.01" wire:model="value" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    @error('value') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.min_order_amount') ?? 'Min order' }}</label>
                                    <input type="number" step="0.01" wire:model="minOrderAmount" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.max_discount') ?? 'Max discount' }}</label>
                                    <input type="number" step="0.01" wire:model="maxDiscountAmount" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.usage_limit') ?? 'Usage limit' }}</label>
                                <input type="number" wire:model="usageLimit" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.start_date') ?? 'Start date' }}</label>
                                    <input type="date" wire:model="startDate" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('vendor.end_date') ?? 'End date' }}</label>
                                    <input type="date" wire:model="endDate" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                                    @error('endDate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model="isActive" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="text-sm font-medium text-gray-700">{{ __('vendor.active') }}</span>
                            </label>
                        </div>
                        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button type="button" wire:click="closeModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                {{ __('vendor.cancel') }}
                            </button>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="saveCoupon"
                                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-70">
                                <span wire:loading.remove wire:target="saveCoupon">{{ __('vendor.save') }}</span>
                                <span wire:loading wire:target="saveCoupon"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
