@php
    $livewireWizard = $livewireWizard ?? false;
    $btnPrimary = 'inline-flex h-10 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition [touch-action:manipulation] hover:bg-emerald-700 active:bg-emerald-800';
    $btnLight = 'inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 text-sm font-semibold text-emerald-800 transition [touch-action:manipulation] hover:bg-emerald-100 active:bg-emerald-200/60';
    $btnOutline = 'inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-4 text-sm font-semibold text-emerald-700 transition [touch-action:manipulation] hover:bg-emerald-50 active:bg-emerald-100';
    $btnOutlineNeutral = 'inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 transition [touch-action:manipulation] hover:bg-gray-50';
    $btnLightSm = 'inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3.5 text-xs font-semibold text-emerald-800 transition [touch-action:manipulation] hover:bg-emerald-100 active:bg-emerald-200/60 sm:px-4 sm:text-sm';
    $btnOutlineSm = 'inline-flex h-10 items-center justify-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-3.5 text-xs font-semibold text-emerald-700 transition [touch-action:manipulation] hover:bg-emerald-50 active:bg-emerald-100 sm:px-4 sm:text-sm';
    $btnPrimaryLg = 'inline-flex h-11 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition hover:bg-emerald-700 sm:w-auto';
    $btnOutlineNeutralLg = 'inline-flex h-11 w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 sm:w-auto';
@endphp

<div class="w-full">
    
@php
    $orderWizardItemsConfig = [
        'livewireWizard' => $livewireWizard,
        'items' => $catalogItemsForJs ?? [],
        'billingUnitsLabels' => $billingUnitsLabels ?? [],
        'rentalPeriods' => $rentalPeriods ?? [],
        'initialLineMeta' => $initialLineMeta ?? [],
        'bookingDefaultUnitsByPriceType' => $bookingDefaultUnitsByPriceType ?? [],
        'quickStoreUrl' => route('vendor.items.quick-store'),
        'categoryStoreUrl' => route('vendor.categories.store'),
        'categories' => isset($categories) ? $categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values() : [],
        'bookingWindow' => (! empty($wizard['start_time']) && ! empty($wizard['end_time']))
            ? ['start' => $wizard['start_time'], 'end' => $wizard['end_time']]
            : null,
        'stockBufferMinutes' => \App\Services\RentalStockAvailability::BUFFER_MINUTES,
        'i18n' => [
            'stock' => __('vendor.stock'),
            'available_stock' => __('vendor.available_stock'),
            'select_at_least_one_item' => __('vendor.order_wizard_select_at_least_one_item'),
            'billing_units' => __('vendor.billing_units'),
            'enter_quantity' => __('vendor.enter_quantity'),
            'quick_item_price_invalid' => __('vendor.order_wizard_quick_item_price_invalid'),
            'billing_units_required' => __('vendor.order_wizard_billing_units_required'),
            'variant_in_cart' => __('vendor.order_wizard_variant_in_cart'),
            'select_variant' => __('vendor.order_wizard_select_variant'),
            'variant_invalid' => __('vendor.order_wizard_variant_invalid'),
            'category_name_required' => __('vendor.order_wizard_category_name_required'),
            'category_create_failed' => __('vendor.order_wizard_category_create_failed'),
            'unit_minute' => __('vendor.order_wizard_summary_unit_minute'),
            'unit_hour' => __('vendor.order_wizard_summary_unit_hour'),
            'unit_day' => __('vendor.order_wizard_summary_unit_day'),
            'unit_week' => __('vendor.order_wizard_summary_unit_week'),
            'unit_month' => __('vendor.order_wizard_summary_unit_month'),
            'unit_year' => __('vendor.order_wizard_summary_unit_year'),
            'quick_item_required' => __('vendor.order_wizard_quick_item_required'),
            'item_create_failed' => __('vendor.order_wizard_item_create_failed'),
            'insufficient_stock' => __('vendor.order_wizard_insufficient_stock', ['name' => ':name']),
            'variant_insufficient_stock' => __('vendor.order_wizard_variant_insufficient_stock', ['label' => ':label']),
        ],
    ];
@endphp
<div x-data="orderWizardItems(@js($orderWizardItemsConfig))" x-init="init()" @keydown.escape.window="closeLineActionMenu()" data-wizard-alpine-root>
            <form @if(! $livewireWizard) action="{{ route('vendor.orders.create.step2') }}" method="POST" @endif
                  class="rounded-xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-100"
                  id="order-items-step-form"
                  @submit.prevent="submitItemsStep($event)">
                @if(! $livewireWizard) @csrf @endif

                <div class="flex-shrink-0 border-b border-gray-100 bg-gradient-to-b from-slate-50 to-white px-3 py-3 sm:px-4 sm:py-4">
                    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h2 class="text-sm font-bold text-gray-900 sm:text-base">{{ __('vendor.select_items_quantity') }}</h2>
                            <p class="mt-0.5 text-xs leading-relaxed text-gray-600 sm:text-sm">{{ __('vendor.order_wizard_items_intro') }}</p>
                        </div>
                        <button type="button"
                                x-show="items.length > 0"
                                x-cloak
                                @click="openAddItemModal()"
                                class="{{ $btnLight }} shrink-0 sm:min-w-[9rem]">
                            <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                            {{ __('vendor.add_new_item') }}
                        </button>
                    </div>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 sm:gap-3" x-show="items.length > 0" x-cloak>
                        <div>
                            <label for="order-wizard-items-search" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 sm:text-xs">{{ __('vendor.search_items') }}</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <i class="fas fa-search text-sm"></i>
                                </span>
                                <input id="order-wizard-items-search"
                                       type="search"
                                       autocomplete="off"
                                       x-model="searchQuery"
                                       placeholder="{{ __('vendor.search_placeholder') }}"
                                       class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-10 pr-10 text-sm text-gray-900 placeholder:text-gray-400 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <button type="button"
                                        x-show="searchQuery"
                                        x-cloak
                                        @click="searchQuery = ''"
                                        class="absolute inset-y-0 right-0 flex items-center pr-2 text-gray-400 hover:text-gray-700">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-md hover:bg-gray-100"><i class="fas fa-times text-sm"></i></span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="order-wizard-items-category" class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 sm:text-xs">{{ __('vendor.filter_by_category') }}</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                    <i class="fas fa-layer-group text-xs"></i>
                                </span>
                                <select id="order-wizard-items-category"
                                        x-model="selectedCategory"
                                        class="h-10 w-full appearance-none rounded-lg border border-gray-200 bg-white pl-10 pr-9 text-sm text-gray-900 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="">{{ __('vendor.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="items.length === 0" x-cloak
                     class="flex min-h-[12rem] flex-col items-center justify-center border-b border-gray-100 px-4 py-10 text-center sm:min-h-[14rem]">
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-50 ring-1 ring-emerald-100">
                        <i class="fas fa-box-open text-2xl text-emerald-600" aria-hidden="true"></i>
                    </div>
                    <p class="max-w-sm text-sm font-medium text-gray-800 sm:text-base">{{ __('vendor.order_wizard_no_catalog_items') }}</p>
                    <button type="button"
                            @click="openAddItemModal()"
                            class="mt-4 {{ $btnPrimary }} min-w-[9rem]">
                        {{ __('vendor.add_new_item') }}
                    </button>
                </div>

                <div x-show="items.length > 0 && filteredItems.length === 0" x-cloak
                     class="flex min-h-[9rem] flex-col items-center justify-center border-b border-gray-100 px-4 py-8 text-center sm:min-h-[10rem] sm:py-10">
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-gray-100">
                        <i class="fas fa-search text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 sm:text-lg">{{ __('vendor.no_items_found') }}</h3>
                    <p class="mt-1 max-w-sm text-xs text-gray-600 sm:text-sm">{{ __('vendor.adjust_search') }}</p>
                    <button type="button"
                            @click="searchQuery = ''; selectedCategory = ''"
                            class="mt-4 {{ $btnOutline }}">
                        <i class="fas fa-redo mr-1.5 text-xs"></i>{{ __('vendor.clear_filters') }}
                    </button>
                </div>

                <div x-show="items.length > 0" x-cloak class="max-h-[min(78vh,38rem)] overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch] md:max-h-[min(82vh,42rem)]" @scroll.passive="closeLineActionMenu()">
                    <template x-for="item in items" :key="item.id">
                        <div x-show="matchesFilter(item)"
                             x-cloak
                             data-order-wizard-line
                             class="border-b border-gray-100 px-3 py-3 transition-colors last:border-b-0 sm:px-4 sm:py-3.5"
                             :class="{
                                 'bg-gradient-to-r from-emerald-50/90 via-emerald-50/30 to-white': !itemHasVariants(item) && isSimpleSelected(item.id),
                                 'bg-gradient-to-r from-emerald-50/80 via-emerald-50/25 to-white': itemHasVariants(item) && hasVariantLines(item.id),
                                 'hover:bg-gray-50/70': !isItemInOrder(item),
                             }">

                            {{-- Main row --}}
                            <div class="flex items-center gap-2.5 sm:gap-3">
                                <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl ring-1 sm:h-[3.25rem] sm:w-[3.25rem]"
                                     :class="itemHasVariants(item) ? 'bg-gradient-to-br from-emerald-100 to-emerald-50 ring-emerald-200/80' : 'bg-gradient-to-br from-slate-100 to-emerald-50 ring-gray-200/80'">
                                    <img x-show="item.photo_url" :src="item.photo_url" alt="" class="h-full w-full object-cover" loading="lazy" decoding="async">
                                    <i x-show="!item.photo_url"
                                       class="text-lg"
                                       :class="itemHasVariants(item) ? 'fas fa-layer-group text-emerald-600' : 'fas fa-box-open text-emerald-600/90'"
                                       aria-hidden="true"></i>
                                    <span x-show="isItemInOrder(item)"
                                          x-cloak
                                          class="absolute -bottom-0.5 -right-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-[10px] text-white shadow ring-2 ring-white">
                                        <i class="fas fa-check" aria-hidden="true"></i>
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1 pr-1">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <p class="truncate text-sm font-semibold leading-snug text-gray-900 sm:text-[15px]" x-text="item.name"></p>
                                        <span x-show="itemHasVariants(item)" x-cloak class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 ring-1 ring-emerald-200/60">{{ __('vendor.item_has_variants_badge') }}</span>
                                    </div>
                                    <p class="mt-0.5 text-[11px] font-medium text-blue-600 tabular-nums sm:text-xs" x-show="item.manage_stock && (!itemHasVariants(item) || !hasVariantLines(item.id))" x-text="itemStockLabel(item)"></p>
                                    <p class="mt-0.5 text-[11px] text-gray-500 sm:text-xs" x-show="itemHasVariants(item) && !hasVariantLines(item.id)">
                                        <span x-text="(item.variants || []).length"></span> {{ __('vendor.order_wizard_variants_available') }}
                                    </p>
                                    <p x-show="!itemHasVariants(item) && !isSimpleSelected(item.id)" class="mt-1 text-xs font-semibold text-emerald-700 tabular-nums sm:text-sm">
                                        ₹<span x-text="formatRupeeInt(item.price)"></span>
                                    </p>
                                    <p x-show="itemHasVariants(item) && !hasVariantLines(item.id)" x-cloak class="mt-1 text-xs font-semibold text-emerald-700 tabular-nums sm:text-sm">
                                        ₹<span x-text="itemPriceLabel(item)"></span>
                                    </p>
                                    {{-- Selected: price breakdown --}}
                                    <p x-show="!itemHasVariants(item) && isSimpleSelected(item.id)" x-cloak
                                       class="mt-1 inline-flex flex-wrap items-center gap-1 rounded-lg bg-white/80 px-2 py-1 text-[11px] leading-snug text-gray-600 ring-1 ring-gray-200/70 tabular-nums sm:text-xs">
                                        <span class="font-medium text-gray-800">₹<span x-text="formatRupeeInt(linePriceForKey(lineKey(item.id), item))"></span></span>
                                        <span class="text-gray-400">×</span>
                                        <span x-text="getQty(item.id)"></span>
                                        <template x-if="lineUsesBillingForKey(lineKey(item.id), item)">
                                            <span class="inline-flex items-center gap-1">
                                                <span class="text-gray-400">×</span>
                                                <span x-text="formatBillingUnitsDisplay(getLineBillingUnits(item))"></span>
                                                <span class="text-[10px] font-medium uppercase text-gray-500" x-text="billingUnitShortForKey(lineKey(item.id), item)"></span>
                                            </span>
                                        </template>
                                        <span class="text-gray-400">=</span>
                                        <span class="font-bold text-emerald-700">₹<span x-text="formatRupeeInt(lineTotalForKey(lineKey(item.id), item))"></span></span>
                                    </p>
                                </div>

                                {{-- Qty + duration + menu (simple selected) --}}
                                <div x-show="!itemHasVariants(item) && isSimpleSelected(item.id)" x-cloak class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                                    <div class="flex items-stretch gap-1 rounded-xl bg-white/90 p-1 ring-1 ring-gray-200/80 shadow-sm sm:gap-1.5 sm:p-1.5">
                                        <div class="flex min-w-[2.5rem] flex-col items-center justify-center rounded-lg px-1.5 py-0.5 sm:min-w-[2.75rem] sm:px-2">
                                            <span class="text-base font-bold tabular-nums leading-none text-gray-900 sm:text-lg" x-text="getQty(item.id)"></span>
                                            <span class="mt-0.5 text-[9px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.order_wizard_qty') }}</span>
                                        </div>
                                        <div x-show="lineUsesBillingForKey(lineKey(item.id), item)" x-cloak class="flex min-w-[2.5rem] flex-col items-center justify-center rounded-lg border-l border-gray-200 px-1.5 py-0.5 sm:min-w-[2.75rem] sm:px-2">
                                            <span class="text-base font-bold tabular-nums leading-none text-gray-900 sm:text-lg" x-text="formatBillingUnitsDisplay(getLineBillingUnits(item))"></span>
                                            <span class="mt-0.5 max-w-[3.5rem] truncate text-[9px] font-semibold capitalize leading-tight text-gray-500 sm:max-w-none" x-text="billingUnitShortForKey(lineKey(item.id), item)"></span>
                                        </div>
                                    </div>
                                    <div class="flex shrink-0 items-center">
                                        <button type="button"
                                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-gray-500 ring-1 ring-gray-200/80 shadow-sm transition hover:bg-gray-50 hover:text-gray-800 [touch-action:manipulation]"
                                                @click.stop="openLineActionMenu($event, { id: 'simple-'+item.id, type: 'simple', itemId: item.id, itemCount: 2, width: 168 })"
                                                :aria-expanded="isLineActionMenuOpen('simple-'+item.id)"
                                                aria-haspopup="true"
                                                aria-label="{{ __('vendor.order_wizard_summary_more_actions') }}">
                                            <i class="fas fa-ellipsis-v text-sm" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- Add / choose variant --}}
                                <div class="flex shrink-0 items-center self-center">
                                    <button type="button"
                                            x-show="!itemHasVariants(item) && !isSimpleSelected(item.id)"
                                            @click="addLine(item)"
                                            class="{{ $btnLightSm }}">
                                        <i class="fas fa-plus text-xs"></i>{{ __('vendor.add') }}
                                    </button>
                                    <button type="button"
                                            x-show="itemHasVariants(item) && !hasVariantLines(item.id)"
                                            @click="openVariantModal(item)"
                                            class="{{ $btnLightSm }}">
                                        <i class="fas fa-layer-group text-xs"></i>{{ __('vendor.order_wizard_choose_variant_btn') }}
                                    </button>
                                    <button type="button"
                                            x-show="itemHasVariants(item) && hasVariantLines(item.id)"
                                            x-cloak
                                            @click="openVariantModal(item, null, 'modify')"
                                            class="{{ $btnOutlineSm }}">
                                        <i class="fas fa-layer-group text-xs" aria-hidden="true"></i>
                                        {{ __('vendor.order_wizard_modify_variant_btn') }}
                                    </button>
                                </div>
                            </div>

                            {{-- Variant lines --}}
                            <template x-if="itemHasVariants(item) && hasVariantLines(item.id)">
                                <div class="mt-3 space-y-2 sm:ml-[3.75rem]">
                                    <template x-for="lineKey in selectedVariantLineKeys(item.id)" :key="'vline-'+lineKey">
                                        <div class="flex items-center gap-2 rounded-xl border border-emerald-100/90 bg-white/70 p-2.5 shadow-sm ring-1 ring-emerald-100/50 sm:gap-3 sm:p-3">
                                            <div class="min-w-0 flex-1 pr-1">
                                                <p class="truncate text-xs font-semibold leading-snug text-emerald-900 sm:text-sm" x-text="lineMeta[lineKey]?.variant_label || ''"></p>
                                                <p class="mt-0.5 text-[10px] font-medium text-blue-600 tabular-nums sm:text-[11px]" x-show="variantLineStockLabel(item, lineKey)" x-text="variantLineStockLabel(item, lineKey)"></p>
                                                <p class="mt-1 inline-flex flex-wrap items-center gap-1 text-[11px] leading-snug text-gray-600 tabular-nums sm:text-xs">
                                                    <span class="font-medium text-gray-800">₹<span x-text="formatRupeeInt(linePriceForKey(lineKey, item))"></span></span>
                                                    <span class="text-gray-400">×</span>
                                                    <span x-text="getQtyForKey(lineKey)"></span>
                                                    <template x-if="lineUsesBillingForKey(lineKey, item)">
                                                        <span class="inline-flex items-center gap-1">
                                                            <span class="text-gray-400">×</span>
                                                            <span x-text="formatBillingUnitsDisplay(getLineBillingUnitsForKey(lineKey, item))"></span>
                                                            <span class="text-[10px] font-medium uppercase text-gray-500" x-text="billingUnitShortForKey(lineKey, item)"></span>
                                                        </span>
                                                    </template>
                                                    <span class="text-gray-400">=</span>
                                                    <span class="font-bold text-emerald-700">₹<span x-text="formatRupeeInt(lineTotalForKey(lineKey, item))"></span></span>
                                                </p>
                                            </div>
                                            <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                                                <div class="flex items-stretch gap-1 rounded-xl bg-emerald-50/80 p-1 ring-1 ring-emerald-100 sm:gap-1.5 sm:p-1.5">
                                                    <div class="flex min-w-[2.25rem] flex-col items-center justify-center rounded-lg px-1.5 py-0.5 sm:min-w-[2.5rem]">
                                                        <span class="text-base font-bold tabular-nums leading-none text-gray-900 sm:text-lg" x-text="getQtyForKey(lineKey)"></span>
                                                        <span class="mt-0.5 text-[9px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.order_wizard_qty') }}</span>
                                                    </div>
                                                    <div x-show="lineUsesBillingForKey(lineKey, item)" x-cloak class="flex min-w-[2.25rem] flex-col items-center justify-center rounded-lg border-l border-emerald-200/80 px-1.5 py-0.5 sm:min-w-[2.5rem]">
                                                        <span class="text-base font-bold tabular-nums leading-none text-gray-900 sm:text-lg" x-text="formatBillingUnitsDisplay(getLineBillingUnitsForKey(lineKey, item))"></span>
                                                        <span class="mt-0.5 max-w-[3.5rem] truncate text-[9px] font-semibold capitalize leading-tight text-gray-500 sm:max-w-none" x-text="billingUnitShortForKey(lineKey, item)"></span>
                                                    </div>
                                                </div>
                                                <div class="flex shrink-0 items-center">
                                                    <button type="button"
                                                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-white text-gray-500 ring-1 ring-emerald-200/70 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-800 [touch-action:manipulation]"
                                                            @click.stop="openLineActionMenu($event, { id: 'vline-'+lineKey, type: 'variant-line', itemId: item.id, lineKey: lineKey, itemCount: 3, width: 168 })"
                                                            :aria-expanded="isLineActionMenuOpen('vline-'+lineKey)"
                                                            aria-haspopup="true"
                                                            aria-label="{{ __('vendor.order_wizard_summary_more_actions') }}">
                                                        <i class="fas fa-ellipsis-v text-sm" aria-hidden="true"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="hidden">
                    <template x-for="(meta, lineKey) in lineMeta" :key="'line-form-'+lineKey">
                        <div x-show="getQtyForKey(lineKey) >= 1">
                            <input type="hidden" :name="'lines['+lineKey+'][item_id]'" :value="meta.item_id">
                            <input type="hidden" :name="'lines['+lineKey+'][quantity]'" :value="lineQty[lineKey]">
                            <input type="hidden" :name="'lines['+lineKey+'][rental_period]'" :value="meta.rental_period || findItem(meta.item_id)?.rental_period || 'per_day'">
                            <input type="hidden" :name="'lines['+lineKey+'][price]'" :value="meta.price ?? findItem(meta.item_id)?.price ?? 0">
                            <template x-if="meta.item_variant_id">
                                <input type="hidden" :name="'lines['+lineKey+'][item_variant_id]'" :value="meta.item_variant_id">
                            </template>
                            <template x-if="lineUsesBillingForKey(lineKey, findItem(meta.item_id)) && lineUnits[lineKey] != null && lineUnits[lineKey] !== ''">
                                <input type="hidden" :name="'lines['+lineKey+'][billing_units]'" :value="lineUnits[lineKey]">
                            </template>
                        </div>
                    </template>
                </div>

                <x-order-wizard-actions class="border-t border-gray-200 p-3 sm:p-4">
                    @if($livewireWizard)
                        <button type="button" wire:click="goToStep(1)"
                                class="{{ $btnOutlineNeutral }} sm:mr-auto">
                            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                            {{ __('vendor.back') }}
                        </button>
                    @else
                        <a href="{{ route('vendor.orders.create') }}"
                           class="{{ $btnOutlineNeutral }} sm:mr-auto">
                            <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                            {{ __('vendor.back') }}
                        </a>
                    @endif
                    <div class="flex w-full flex-col items-stretch gap-2 sm:w-auto sm:items-end">
                        <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="saveItemsStep"
                                :aria-disabled="!hasSelectedItems"
                                :class="hasSelectedItems
                                    ? '{{ $btnPrimary }} w-full sm:w-auto sm:min-w-[8rem] disabled:opacity-70'
                                    : 'inline-flex h-10 w-full cursor-not-allowed items-center justify-center gap-1.5 rounded-lg bg-gray-300 px-4 text-sm font-semibold text-gray-500 shadow-none transition [touch-action:manipulation] sm:w-auto sm:min-w-[8rem]'">
                            <span wire:loading.remove wire:target="saveItemsStep">
                                <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                                {{ __('vendor.order_wizard_continue_summary') }}
                            </span>
                            <span wire:loading wire:target="saveItemsStep">
                                <i class="fas fa-spinner fa-spin text-xs" aria-hidden="true"></i>
                            </span>
                        </button>
                    </div>
                </x-order-wizard-actions>
            </form>

        @include('vendor.orders.partials.quick-item-modal')
        @include('vendor.orders.partials.variant-picker-modal')

        {{-- Fixed 3-dot action menu (avoids scroll/overflow clipping) --}}
        <template x-teleport="body">
            <div x-show="lineActionMenu"
                 x-cloak
                 class="fixed inset-0 z-[75]"
                 @keydown.escape.window="closeLineActionMenu()">
                <div class="absolute inset-0 bg-transparent"
                     aria-hidden="true"
                     @click="closeLineActionMenu()"></div>
                <div x-show="lineActionMenu"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="fixed overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl ring-1 ring-black/10"
                     :style="lineActionMenu ? ('top:' + lineActionMenu.top + 'px;left:' + lineActionMenu.left + 'px;width:' + lineActionMenu.width + 'px') : ''"
                     role="menu"
                     @click.stop>
                    <template x-if="lineActionMenu?.type === 'simple'">
                        <div>
                            <button type="button"
                                    role="menuitem"
                                    class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left text-sm font-medium text-gray-800 hover:bg-gray-50"
                                    @click="runLineActionMenu('edit')">
                                <i class="fas fa-pen w-4 text-center text-xs text-emerald-500" aria-hidden="true"></i>
                                {{ __('vendor.edit') }}
                            </button>
                            <button type="button"
                                    role="menuitem"
                                    class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left text-sm font-medium text-red-700 hover:bg-red-50"
                                    @click="runLineActionMenu('remove')">
                                <i class="fas fa-trash-alt w-4 text-center text-xs" aria-hidden="true"></i>
                                {{ __('vendor.remove') }}
                            </button>
                        </div>
                    </template>
                    <template x-if="lineActionMenu?.type === 'variant-line'">
                        <div>
                            <button type="button"
                                    role="menuitem"
                                    class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left text-sm font-medium text-gray-800 hover:bg-gray-50"
                                    @click="runLineActionMenu('edit')">
                                <i class="fas fa-pen w-4 text-center text-xs text-emerald-500" aria-hidden="true"></i>
                                {{ __('vendor.edit') }}
                            </button>
                            <button type="button"
                                    role="menuitem"
                                    class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left text-sm font-medium text-gray-800 hover:bg-gray-50"
                                    @click="runLineActionMenu('change')">
                                <i class="fas fa-exchange-alt w-4 text-center text-xs text-emerald-500" aria-hidden="true"></i>
                                {{ __('vendor.order_wizard_edit_variant') }}
                            </button>
                            <button type="button"
                                    role="menuitem"
                                    class="flex w-full items-center gap-2.5 px-3 py-2.5 text-left text-sm font-medium text-red-700 hover:bg-red-50"
                                    @click="runLineActionMenu('remove')">
                                <i class="fas fa-trash-alt w-4 text-center text-xs" aria-hidden="true"></i>
                                {{ __('vendor.remove') }}
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <div x-show="showLineEditModal"
             x-cloak
             class="fixed inset-0 z-[80] flex items-end justify-center p-2 sm:items-center sm:p-4"
             role="dialog"
             aria-modal="true"
             @keydown.escape.window="closeLineEditModal()">
            <div x-show="showLineEditModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 class="absolute inset-0 bg-gray-900/55 backdrop-blur-[1px]"
                 @click="closeLineEditModal()"></div>
            <div x-show="showLineEditModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 class="relative z-10 w-full max-w-md overflow-hidden rounded-t-2xl bg-white shadow-2xl ring-1 ring-gray-900/5 sm:rounded-2xl"
                 @click.stop>
                <div class="relative overflow-hidden border-b border-emerald-100 bg-gradient-to-br from-emerald-600 via-emerald-600 to-teal-600 px-4 py-4 text-white sm:px-5">
                    <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10"></div>
                    <div class="absolute -bottom-8 right-8 h-20 w-20 rounded-full bg-white/5"></div>
                    <div class="relative flex items-start gap-3 pr-8">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                            <i class="fas fa-sliders-h text-sm" aria-hidden="true"></i>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-base font-bold leading-tight">{{ __('vendor.order_wizard_summary_edit_line') }}</h4>
                            <p class="mt-1 line-clamp-2 text-sm text-emerald-100" x-text="lineEditName"></p>
                        </div>
                    </div>
                    <button type="button"
                            @click="closeLineEditModal()"
                            class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-white/80 transition hover:bg-white/15 hover:text-white"
                            aria-label="{{ __('vendor.cancel') }}">
                        <i class="fas fa-times text-sm" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="space-y-4 px-4 py-4 sm:px-5 sm:py-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2 sm:col-span-1">
                            <label for="items_edit_price" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                                <i class="fas fa-rupee-sign text-[10px] text-emerald-600" aria-hidden="true"></i>
                                {{ __('vendor.price') }}
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm font-medium text-gray-500">₹</span>
                                <input id="items_edit_price"
                                       type="number"
                                       min="0"
                                       step="0.01"
                                       inputmode="decimal"
                                       required
                                       x-model="lineEditPrice"
                                       class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50/50 pl-8 pr-3 text-sm font-medium text-gray-900 transition focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="items_edit_qty" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                                <i class="fas fa-cubes text-[10px] text-emerald-600" aria-hidden="true"></i>
                                {{ __('vendor.quantity') }}
                            </label>
                            <input id="items_edit_qty"
                                   type="number"
                                   min="1"
                                   step="1"
                                   required
                                   x-model.number="lineEditQty"
                                   class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 text-sm font-medium text-gray-900 transition focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                    <div>
                        <label for="items_edit_rental" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                            <i class="fas fa-clock text-[10px] text-emerald-600" aria-hidden="true"></i>
                            {{ __('vendor.rental_period') }}
                        </label>
                        <div class="relative">
                            <select id="items_edit_rental"
                                    x-model="lineEditRentalPeriod"
                                    @change="onLineEditRentalChange()"
                                    class="h-11 w-full appearance-none rounded-xl border border-gray-200 bg-gray-50/50 px-3 pr-9 text-sm font-medium text-gray-900 transition focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <template x-for="(label, key) in rentalPeriods" :key="'edit-rp-'+key">
                                    <option :value="key" x-text="label"></option>
                                </template>
                            </select>
                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                <i class="fas fa-chevron-down text-xs" aria-hidden="true"></i>
                            </span>
                        </div>
                    </div>
                    <div x-show="lineEditUsesBilling" x-cloak x-transition>
                        <label for="items_edit_billing" class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold text-gray-700">
                            <i class="fas fa-hourglass-half text-[10px] text-emerald-600" aria-hidden="true"></i>
                            <span x-text="lineEditBillingLabel()"></span>
                        </label>
                        <input id="items_edit_billing"
                               type="number"
                               step="0.01"
                               min="0.01"
                               lang="en"
                               x-model="lineEditBilling"
                               class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50/50 px-3 text-sm font-medium text-gray-900 transition focus:border-transparent focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50/50 px-4 py-3 ring-1 ring-emerald-100/80">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700/80">{{ __('vendor.order_wizard_summary_total_amount_label') }}</p>
                                <p class="mt-0.5 truncate text-xs text-gray-600">
                                    <span x-text="lineEditRentalPeriodLabel()"></span>
                                    <template x-if="lineEditUsesBilling && lineEditBilling">
                                        <span> · <span x-text="formatBillingUnitsDisplay(lineEditBilling)"></span></span>
                                    </template>
                                </p>
                            </div>
                            <p class="shrink-0 text-xl font-bold tabular-nums text-emerald-700 sm:text-2xl">
                                ₹<span x-text="formatRupeeInt(lineEditPreviewTotal())"></span>
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end sm:gap-3">
                        <button type="button"
                                @click="closeLineEditModal()"
                                class="{{ $btnOutlineNeutralLg }}">
                            {{ __('vendor.cancel') }}
                        </button>
                        <button type="button"
                                @click="saveLineEditModal()"
                                class="{{ $btnPrimaryLg }}">
                            <i class="fas fa-check text-xs" aria-hidden="true"></i>
                            {{ __('vendor.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
