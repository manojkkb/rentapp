@if($items->count() > 0)
    <div class="hidden overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm md:block">
        <table class="w-full text-left">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.item') }}</th>
                    <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.price') }}</th>
                    <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.stock') }}</th>
                    <th class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.status') }}</th>
                    <th class="px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($items as $item)
                    <tr class="hover:bg-emerald-50/30" wire:key="item-row-{{ $item->uuid }}">
                        <td class="px-4 py-3">
                            <div class="flex items-start gap-3">
                                @if($item->photo_url)
                                    <img src="{{ $item->photo_url }}" alt="" class="h-10 w-10 shrink-0 rounded-lg border border-gray-200 object-cover" loading="lazy">
                                @else
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100"><i class="fas fa-box text-sm text-emerald-600" aria-hidden="true"></i></div>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('vendor.items.show', $item) }}" @if($livewireList ?? false) wire:navigate @endif class="text-sm font-semibold text-gray-900 hover:text-emerald-700">{{ $item->name }}</a>
                                    @if($item->item_code)<p class="mt-0.5 font-mono text-[10px] text-gray-500">{{ $item->item_code }}</p>@endif
                                    @if($item->category)<span class="mt-1 inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700">{{ $item->category->name }}</span>@endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-sm font-semibold text-gray-900">₹{{ number_format($item->price, 2) }}</p>
                            <p class="text-xs text-gray-500">{{ $rentalPeriods[$item->rental_period] ?? $item->rental_period }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ ($item->stock ?? 0) > 10 ? 'bg-emerald-100 text-emerald-700' : (($item->stock ?? 0) > 0 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700') }}">
                                {{ $item->stock ?? 0 }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($livewireList ?? false)
                                <button type="button" wire:click="toggleStatus(@js($item->uuid))" class="text-xs font-semibold {{ $item->is_active ? 'text-emerald-700' : 'text-gray-500' }}">
                                    {{ $item->is_active ? __('vendor.active') : __('vendor.inactive') }}
                                </button>
                            @else
                                <span class="text-xs font-semibold {{ $item->is_active ? 'text-emerald-700' : 'text-gray-500' }}">{{ $item->is_active ? __('vendor.active') : __('vendor.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('vendor.items.edit', $item) }}" @if($livewireList ?? false) wire:navigate @endif class="inline-flex items-center rounded-lg border border-emerald-200 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                {{ __('vendor.edit') }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="space-y-2 p-2 md:hidden">
        @foreach($items as $item)
            <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm" wire:key="item-card-{{ $item->uuid }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('vendor.items.show', $item) }}" @if($livewireList ?? false) wire:navigate @endif class="text-sm font-semibold text-gray-900">{{ $item->name }}</a>
                        <p class="mt-1 text-lg font-bold text-gray-900">₹{{ number_format($item->price, 2) }}</p>
                    </div>
                    <a href="{{ route('vendor.items.edit', $item) }}" @if($livewireList ?? false) wire:navigate @endif class="rounded-lg p-2 text-emerald-600 hover:bg-emerald-50"><i class="fas fa-edit" aria-hidden="true"></i></a>
                </div>
            </div>
        @endforeach
    </div>

    @if($items->hasPages())
        <div class="border-t border-gray-200 px-4 py-3">{{ $items->links() }}</div>
    @endif
@else
    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/80 p-10 text-center">
        <h3 class="text-base font-semibold text-gray-900">{{ __('vendor.no_items_found') ?? 'No items found' }}</h3>
        <a wire:navigate href="{{ route('vendor.items.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            <i class="fas fa-plus" aria-hidden="true"></i>{{ __('vendor.add_item') }}
        </a>
    </div>
@endif
