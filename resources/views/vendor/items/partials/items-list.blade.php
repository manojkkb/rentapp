@if($items->count() > 0)
    <div class="hidden overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm md:block">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/90 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="px-5 py-4">{{ __('vendor.item') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.price') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.stock') }}</th>
                        <th class="px-5 py-4">{{ __('vendor.status') }}</th>
                        <th class="px-5 py-4"><span class="sr-only">{{ __('vendor.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($items as $item)
                        <tr class="hover:bg-emerald-50/35" wire:key="item-row-{{ $item->uuid }}">
                            <td class="px-5 py-4 align-top">
                                <div class="flex items-start gap-3">
                                    @if($item->photo_url)
                                        <img src="{{ $item->photo_url }}" alt="" class="h-11 w-11 shrink-0 rounded-xl border border-gray-200 object-cover" loading="lazy">
                                    @else
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-sm">
                                            <i class="fas fa-box text-sm" aria-hidden="true"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ route('vendor.items.show', $item) }}"
                                           @if($livewireList ?? false) wire:navigate @endif
                                           class="block truncate font-medium text-emerald-700 transition hover:text-emerald-900 hover:underline">
                                            {{ $item->name }}
                                        </a>
                                        @if($item->item_code)
                                            <p class="mt-0.5 font-mono text-[11px] text-gray-500">{{ $item->item_code }}</p>
                                        @endif
                                        @if($item->category)
                                            <span class="mt-1.5 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700 ring-1 ring-gray-200/80">
                                                {{ $item->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 align-top">
                                <p class="font-semibold tabular-nums text-gray-900">₹{{ number_format($item->price, 2) }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">{{ $rentalPeriods[$item->rental_period] ?? $item->rental_period }}</p>
                            </td>
                            <td class="px-5 py-4 align-top">
                                @include('vendor.items.partials.stock-breakdown', ['item' => $item, 'layout' => 'list'])
                            </td>
                            <td class="px-5 py-4 align-top">
                                @if($livewireList ?? false)
                                    <div wire:key="item-toggle-{{ $item->uuid }}">
                                        <button type="button"
                                                wire:click="toggleStatus(@js($item->uuid))"
                                                wire:loading.attr="disabled"
                                                wire:target="toggleStatus(@js($item->uuid))"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 {{ $item->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}"
                                                title="{{ $item->is_active ? __('vendor.click_to_deactivate') : __('vendor.click_to_activate') }}">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $item->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                        </button>
                                        <p class="mt-1 text-[11px] font-medium {{ $item->is_active ? 'text-emerald-700' : 'text-gray-500' }}">
                                            {{ $item->is_active ? __('vendor.active') : __('vendor.inactive') }}
                                        </p>
                                    </div>
                                @else
                                    <span class="text-xs font-semibold {{ $item->is_active ? 'text-emerald-700' : 'text-gray-500' }}">
                                        {{ $item->is_active ? __('vendor.active') : __('vendor.inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 align-top">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('vendor.items.show', $item) }}"
                                       @if($livewireList ?? false) wire:navigate @endif
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                        {{ __('vendor.view') }}
                                    </a>
                                    <a href="{{ route('vendor.items.edit', $item) }}"
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
        @if($items->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $items->links() }}</div>
        @endif
    </div>

    <div class="space-y-3 md:hidden">
        @foreach($items as $item)
            <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" wire:key="item-card-{{ $item->uuid }}">
                <div class="flex items-start gap-3 border-b border-gray-100 p-4">
                    @if($item->photo_url)
                        <img src="{{ $item->photo_url }}" alt="" class="h-14 w-14 shrink-0 rounded-xl border border-gray-200 object-cover" loading="lazy">
                    @else
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-sm">
                            <i class="fas fa-box text-lg" aria-hidden="true"></i>
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('vendor.items.show', $item) }}"
                           @if($livewireList ?? false) wire:navigate @endif
                           class="block truncate text-sm font-semibold text-emerald-700 hover:underline">
                            {{ $item->name }}
                        </a>
                        @if($item->item_code)
                            <p class="mt-0.5 font-mono text-[11px] text-gray-500">{{ $item->item_code }}</p>
                        @endif
                        <p class="mt-1 text-lg font-bold tabular-nums text-gray-900">₹{{ number_format($item->price, 2) }}</p>
                        <p class="text-[11px] text-gray-500">{{ $rentalPeriods[$item->rental_period] ?? $item->rental_period }}</p>
                    </div>
                    @if($livewireList ?? false)
                        <button type="button"
                                wire:click="toggleStatus(@js($item->uuid))"
                                wire:loading.attr="disabled"
                                wire:target="toggleStatus(@js($item->uuid))"
                                class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full {{ $item->is_active ? 'bg-emerald-500' : 'bg-gray-300' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $item->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    @endif
                </div>
                <div class="flex items-center justify-between gap-2 px-4 py-3">
                    <div class="flex flex-wrap gap-1.5">
                        @if($item->category)
                            <span class="inline-flex rounded-full bg-gray-50 px-2 py-0.5 text-[10px] font-semibold text-gray-700 ring-1 ring-gray-100">
                                {{ $item->category->name }}
                            </span>
                        @endif
                        <div class="w-full basis-full">
                            @include('vendor.items.partials.stock-breakdown', ['item' => $item, 'layout' => 'compact'])
                        </div>
                        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 {{ $item->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-gray-50 text-gray-600 ring-gray-100' }}">
                            {{ $item->is_active ? __('vendor.active') : __('vendor.inactive') }}
                        </span>
                        @if(!$item->is_available)
                            <span class="inline-flex rounded-full bg-orange-50 px-2 py-0.5 text-[10px] font-semibold text-orange-700 ring-1 ring-orange-100">
                                {{ __('vendor.unavailable') }}
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('vendor.items.edit', $item) }}"
                       @if($livewireList ?? false) wire:navigate @endif
                       class="inline-flex min-h-[36px] items-center gap-1 rounded-lg border border-emerald-200 px-2.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                        <i class="fas fa-edit text-[10px]" aria-hidden="true"></i>
                        {{ __('vendor.edit') }}
                    </a>
                </div>
            </article>
        @endforeach
        @if($items->hasPages())
            <div class="pt-1">{{ $items->links() }}</div>
        @endif
    </div>
@else
    <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center shadow-sm">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
            <i class="fas fa-box-open text-2xl" aria-hidden="true"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.no_items_found') }}</h3>
        <p class="mx-auto mt-2 max-w-md text-sm text-gray-600">
            @if(($search ?? '') !== '' || ($categoryId ?? '') !== '')
                {{ __('vendor.items_empty_search') }}
            @else
                {{ __('vendor.items_page_subtitle') }}
            @endif
        </p>
        <a wire:navigate href="{{ route('vendor.items.create') }}"
           class="mt-6 inline-flex min-h-[44px] items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
            <i class="fas fa-plus text-xs" aria-hidden="true"></i>
            {{ __('vendor.add_item') }}
        </a>
    </div>
@endif
