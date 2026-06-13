@php
    $modernCard = $modernCard ?? false;
    $boutiqueCard = $boutiqueCard ?? false;
    $oceanCard = $oceanCard ?? false;
    $sunsetFeatured = $sunsetFeatured ?? false;
    $showPrice = $store->show_prices_online;
    $periodLabel = $item->rental_period
        ? (\App\Models\Items::rentalPeriodSelectOptions()[$item->rental_period] ?? $item->rental_period)
        : '';
    $usesBilling = \App\Models\Items::rentalPeriodUsesBillingUnits($item->rental_period ?? 'per_day');
    $billingUnits = ($hasBookingDates ?? false) && $usesBilling
        ? ($bookingDefaultsByPriceType[$item->rental_period ?? 'per_day'] ?? null)
        : null;
    $unitPrice = (float) $item->price;
    if ($item->usesVariants() && $item->variants->isNotEmpty()) {
        $unitPrice = (float) $item->variants->where('is_active', true)->where('is_available', true)->min('price');
    }
    $linePreview = ($hasBookingDates ?? false)
        ? \App\Support\StorefrontRentalPricing::lineSubtotal($unitPrice, 1, $item->rental_period ?? 'per_day', $billingUnits)
        : $unitPrice;
    $itemUrl = route('storefront.items.show', [$vendor->slug, $item]);
    $searchName = $item->name;
    $searchCategory = $item->category?->name ?? '';
    $searchCode = $item->item_code ?? '';
    $itemWaUrl = ($whatsappEnabled ?? false)
        ? \App\Support\StorefrontWhatsApp::url(
            $store,
            \App\Support\StorefrontWhatsApp::itemMessage(
                $vendor,
                $item,
                $itemUrl,
                $whatsappRentalHint ?? null,
            ),
        )
        : null;
@endphp
<article x-show="matchesItem(@js($searchName), @js($searchCategory), @js($searchCode))"
         data-store-item-card
         class="{{ $theme['classes']['card'] }} group flex flex-col overflow-hidden store-surface-bg transition duration-300 {{ $sunsetFeatured ? 'hover:shadow-xl' : 'hover:-translate-y-0.5 hover:shadow-lg' }} {{ $oceanCard ? 'border-t-4 border-t-[var(--store-accent)]' : '' }}">
    <a href="{{ $itemUrl }}" class="{{ $theme['classes']['card_image'] }} relative block overflow-hidden bg-gray-100{{ $sunsetFeatured ? ' !aspect-[4/3] sm:!aspect-square' : '' }}">
        @if($item->photo_url)
            <img src="{{ $item->photo_url }}" alt="{{ $item->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        @else
            <div class="flex h-full w-full items-center justify-center store-accent-bg-soft">
                <i class="fas fa-image text-3xl text-gray-300" aria-hidden="true"></i>
            </div>
        @endif
        @if($item->category)
            <span class="absolute left-2.5 top-2.5 max-w-[85%] truncate rounded-full bg-black/60 px-2.5 py-1 text-[10px] font-semibold text-white backdrop-blur-sm">{{ $item->category->name }}</span>
        @endif
        @if($item->usesVariants())
            <span class="absolute bottom-2.5 right-2.5 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-semibold text-gray-700">{{ __('vendor.item_has_variants') }}</span>
        @endif
    </a>
    <div class="flex flex-1 flex-col {{ $sunsetFeatured ? 'p-5 sm:p-6' : ($boutiqueCard ? 'p-5' : 'p-4') }}">
        <h3 class="line-clamp-2 leading-snug {{ $sunsetFeatured ? 'text-lg font-bold sm:text-xl' : ($boutiqueCard ? 'text-base font-medium sm:text-lg' : 'text-sm font-semibold sm:text-base') }}">
            <a href="{{ $itemUrl }}" class="transition hover:store-accent-text-dark">{{ $item->name }}</a>
        </h3>
        <div class="mt-auto flex flex-col gap-3 pt-4">
            @if($showPrice)
                <div>
                    <p class="text-lg font-bold store-accent-text-dark">₹{{ number_format($linePreview, 0) }}</p>
                    @if($periodLabel)
                        <p class="text-xs text-gray-500">
                            @if($billingUnits)
                                ₹{{ number_format($unitPrice, 0) }} / {{ $periodLabel }} × {{ rtrim(rtrim(number_format($billingUnits, 2), '0'), '.') }}
                            @else
                                / {{ $periodLabel }}
                            @endif
                        </p>
                    @endif
                </div>
            @else
                <p class="text-xs font-medium text-gray-500">{{ __('vendor.store_public_price_on_request') }}</p>
            @endif
            <button type="button" @click.prevent="addItem({{ $item->id }})"
                    class="{{ $theme['classes']['btn'] }} store-btn-primary flex h-11 w-full items-center justify-center gap-2 text-sm font-semibold transition">
                <i class="fas fa-cart-plus text-xs" aria-hidden="true"></i>
                {{ $item->usesVariants() ? __('vendor.store_choose_variant') : __('vendor.store_add_to_cart') }}
            </button>
            @if($itemWaUrl)
                <a href="{{ $itemWaUrl }}" target="_blank" rel="noopener noreferrer" @click.stop
                   class="{{ $theme['classes']['btn'] }} flex h-10 w-full items-center justify-center gap-2 border border-[#25D366]/40 bg-[#25D366]/5 text-xs font-semibold text-[#128C7E] hover:bg-[#25D366]/10">
                    <i class="fab fa-whatsapp" aria-hidden="true"></i>
                    {{ __('vendor.store_ask_on_whatsapp') }}
                </a>
            @endif
        </div>
    </div>
</article>
