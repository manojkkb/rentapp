@php
    $showPrice = $store->show_prices_online;
    $periodLabel = $item->rental_period
        ? (\App\Models\Items::rentalPeriodSelectOptions()[$item->rental_period] ?? $item->rental_period)
        : '';
    $itemUrl = route('storefront.items.show', [$vendor->slug, $item]);
    $searchName = $item->name;
    $searchCategory = $item->category?->name ?? '';
    $searchCode = $item->item_code ?? '';
@endphp
<article x-show="matchesItem(@js($searchName), @js($searchCategory), @js($searchCode))"
         data-store-item-card
         class="flex flex-wrap items-center gap-4 px-1 py-5 sm:flex-nowrap sm:px-0">
    <a href="{{ $itemUrl }}" class="h-20 w-20 shrink-0 overflow-hidden bg-gray-100 sm:h-24 sm:w-24">
        @if($item->photo_url)
            <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover" loading="lazy">
        @else
            <div class="flex h-full w-full items-center justify-center store-accent-bg-soft">
                <i class="fas fa-image text-gray-300" aria-hidden="true"></i>
            </div>
        @endif
    </a>
    <div class="min-w-0 flex-1">
        <h3 class="font-bold uppercase tracking-wide">
            <a href="{{ $itemUrl }}" class="transition hover:store-accent-text">{{ $item->name }}</a>
        </h3>
        @if($item->category)
            <p class="mt-0.5 text-xs uppercase tracking-widest text-gray-400">{{ $item->category->name }}</p>
        @endif
        @if($showPrice)
            <p class="mt-1 text-sm font-semibold store-accent-text-dark">
                ₹{{ number_format((float) $item->price, 0) }}
                @if($periodLabel)<span class="font-normal text-gray-500">/ {{ $periodLabel }}</span>@endif
            </p>
        @endif
    </div>
    <button type="button" @click.prevent="addItem({{ $item->id }})"
            class="{{ $theme['classes']['btn'] }} store-btn-primary h-11 shrink-0 px-5 text-xs font-bold sm:text-sm">
        {{ $item->usesVariants() ? __('vendor.store_choose_variant') : __('vendor.store_add_to_cart') }}
    </button>
</article>
