@if($customers->count() > 0)
    <div class="hidden overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/90 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-4">{{ __('vendor.customer') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.mobile') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.address') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.registered') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.status') }}</th>
                        <th class="px-5 py-4"><span class="sr-only">{{ __('vendor.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($customers as $customer)
                        @php $initial = strtoupper(substr($customer->name, 0, 1)); @endphp
                        <tr class="hover:bg-emerald-50/35" @if($livewireList ?? false) wire:key="customer-{{ $customer->uuid }}" @endif>
                            <td class="px-5 py-4 align-top">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 text-sm font-bold text-white">
                                        {{ $initial }}
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('vendor.customers.show', $customer) }}"
                                           @if($livewireList ?? false) wire:navigate @endif
                                           class="block truncate font-medium text-emerald-700 transition hover:text-emerald-900 hover:underline">
                                            {{ $customer->name }}
                                        </a>
                                        <p class="mt-0.5 text-[11px] text-gray-500">{{ __('vendor.added_ago', ['time' => $customer->created_at->diffForHumans()]) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <a href="{{ route('vendor.customers.show', $customer) }}"
                                   @if($livewireList ?? false) wire:navigate @endif
                                   class="font-medium tabular-nums text-gray-800 transition hover:text-emerald-700 hover:underline">
                                    {{ $customer->mobile }}
                                </a>
                            </td>
                            <td class="max-w-[12rem] px-5 py-4 align-top">
                                <p class="truncate text-gray-700">{{ $customer->address ?: '—' }}</p>
                            </td>
                            <td class="px-5 py-4 align-top">
                                @if($customer->user_id)
                                    <span class="inline-flex rounded-full bg-teal-50 px-2 py-0.5 text-[11px] font-semibold text-teal-700 ring-1 ring-teal-100">{{ __('vendor.registered') }}</span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 align-top">
                                @if($livewireList ?? false)
                                    <div wire:key="customer-toggle-{{ $customer->id }}">
                                        <button type="button"
                                                wire:click="toggleStatus({{ $customer->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="toggleStatus({{ $customer->id }})"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 {{ $customer->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}"
                                                title="{{ $customer->is_active ? __('vendor.click_to_deactivate') : __('vendor.click_to_activate') }}">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $customer->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                        </button>
                                        <p class="mt-1 text-[11px] font-medium {{ $customer->is_active ? 'text-emerald-700' : 'text-gray-500' }}">
                                            {{ $customer->is_active ? __('vendor.active') : __('vendor.inactive') }}
                                        </p>
                                    </div>
                                @else
                                    <div x-data="{ isActive: {{ $customer->is_active ? 'true' : 'false' }} }">
                                        <form action="{{ route('vendor.customers.toggle', $customer) }}" method="POST" @submit.prevent="$el.submit(); isActive = !isActive">
                                            @csrf
                                            <button type="submit"
                                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                                    :class="isActive ? 'bg-emerald-500' : 'bg-gray-300'">
                                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                                      :class="isActive ? 'translate-x-6' : 'translate-x-1'"></span>
                                            </button>
                                        </form>
                                        <p class="mt-1 text-[11px] font-medium" :class="isActive ? 'text-emerald-700' : 'text-gray-500'" x-text="isActive ? @json(__('vendor.active')) : @json(__('vendor.inactive'))"></p>
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('vendor.customers.show', $customer) }}"
                                       @if($livewireList ?? false) wire:navigate @endif
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                        {{ __('vendor.view_customer') }}
                                    </a>
                                    <a href="{{ route('vendor.customers.edit', $customer) }}"
                                       @if($livewireList ?? false) wire:navigate @endif
                                       class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50"
                                       title="{{ __('vendor.edit') }}">
                                        <i class="fas fa-edit" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $customers->links() }}</div>
        @endif
    </div>

    <div class="space-y-3 md:hidden">
        @foreach($customers as $customer)
            @php $initial = strtoupper(substr($customer->name, 0, 1)); @endphp
            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"
                     @if($livewireList ?? false) wire:key="customer-m-{{ $customer->uuid }}" @endif>
                <div class="flex items-start gap-3 border-b border-gray-100 p-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-base font-bold text-white">
                        {{ $initial }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('vendor.customers.show', $customer) }}"
                           @if($livewireList ?? false) wire:navigate @endif
                           class="block truncate text-sm font-semibold text-emerald-700 hover:underline">
                            {{ $customer->name }}
                        </a>
                        <a href="{{ route('vendor.customers.show', $customer) }}"
                           @if($livewireList ?? false) wire:navigate @endif
                           class="mt-0.5 block text-xs tabular-nums text-gray-600 hover:text-emerald-700 hover:underline">
                            {{ $customer->mobile }}
                        </a>
                        @if($customer->address)
                            <p class="mt-1 line-clamp-2 text-xs text-gray-500">{{ $customer->address }}</p>
                        @endif
                    </div>
                    @if($livewireList ?? false)
                        <button type="button"
                                wire:click="toggleStatus({{ $customer->id }})"
                                wire:loading.attr="disabled"
                                wire:target="toggleStatus({{ $customer->id }})"
                                class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full {{ $customer->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $customer->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    @endif
                </div>
                <div class="flex items-center justify-between gap-2 px-4 py-3">
                    <div class="flex flex-wrap gap-1.5">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 {{ $customer->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-gray-50 text-gray-600 ring-gray-100' }}">
                            {{ $customer->is_active ? __('vendor.active') : __('vendor.inactive') }}
                        </span>
                        @if($customer->user_id)
                            <span class="inline-flex rounded-full bg-teal-50 px-2 py-0.5 text-[10px] font-semibold text-teal-700 ring-1 ring-teal-100">{{ __('vendor.registered') }}</span>
                        @endif
                    </div>
                    <a href="{{ route('vendor.customers.show', $customer) }}"
                       @if($livewireList ?? false) wire:navigate @endif
                       class="text-xs font-semibold text-emerald-700 hover:underline">
                        {{ __('vendor.view_customer') }} →
                    </a>
                </div>
            </article>
        @endforeach
        @if($customers->hasPages())
            <div class="pt-1">{{ $customers->links() }}</div>
        @endif
    </div>
@else
    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center shadow-sm">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
            <i class="fas fa-user-friends text-2xl" aria-hidden="true"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.no_customers_yet') }}</h3>
        <p class="mx-auto mt-2 max-w-md text-sm text-gray-600">
            @if(($search ?? '') !== '')
                {{ __('vendor.customers_empty_search') }}
            @else
                {{ __('vendor.customers_page_subtitle') }}
            @endif
        </p>
        <button type="button"
                @if($livewireList ?? false)
                    wire:click="openCreateModal"
                @else
                    @click="$dispatch('open-create-customer-modal')"
                @endif
                class="mt-6 inline-flex min-h-[44px] items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
            <i class="fas fa-plus text-xs" aria-hidden="true"></i>
            {{ __('vendor.add_customer') }}
        </button>
    </div>
@endif
