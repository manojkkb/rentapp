@php
    $orderReadOnlyList = $orderReadOnly ?? $order->isLockedForEditing();
@endphp
@if($order->items->count() > 0)
    <ul class="divide-y divide-gray-100 text-[13px] text-gray-800 sm:text-sm">
        @foreach($order->items as $cartItem)
            @php
                $linePt = $cartItem->rental_period ?? ($cartItem->item?->rental_period ?? 'per_day');
                $usesBilling = \App\Models\Items::rentalPeriodUsesBillingUnits($linePt);
                $bu = $cartItem->billing_units;
                $buFmt = ($usesBilling && $bu !== null && $bu !== '')
                    ? (rtrim(rtrim(number_format((float) $bu, 2, '.', ''), '0'), '.') ?: '0')
                    : null;
                $unitShort = match ($linePt) {
                    'per_minute' => __('vendor.order_wizard_summary_unit_minute'),
                    'per_hour' => __('vendor.order_wizard_summary_unit_hour'),
                    'per_day' => __('vendor.order_wizard_summary_unit_day'),
                    'per_week' => __('vendor.order_wizard_summary_unit_week'),
                    'per_month' => __('vendor.order_wizard_summary_unit_month'),
                    'per_year' => __('vendor.order_wizard_summary_unit_year'),
                    default => '',
                };
                $unitPrice = (float) $cartItem->price;
                $lineTotal = $cartItem->lineSubtotal();
                $photoUrl = $cartItem->item?->photo_url;
                $lineName = $cartItem->item?->name ?? $cartItem->item_name;
                $hasVariants = (bool) ($cartItem->item?->usesVariants());
                if ($cartItem->variant_label && ! str_contains($lineName, $cartItem->variant_label)) {
                    $lineName .= ' — '.$cartItem->variant_label;
                }
                $lineEditPayload = base64_encode(json_encode([
                    'item_id' => (int) $cartItem->item_id,
                    'name' => $lineName,
                    'quantity' => (int) $cartItem->quantity,
                    'billing_units' => $cartItem->billing_units,
                    'uses_billing' => $usesBilling,
                    'unit_price' => $unitPrice,
                    'rental_period' => $linePt,
                ]));
                $lineVariantChangePayload = base64_encode(json_encode([
                    'order_item_id' => (int) $cartItem->id,
                    'item_id' => (int) $cartItem->item_id,
                    'item_variant_id' => $cartItem->item_variant_id ? (int) $cartItem->item_variant_id : null,
                    'name' => $lineName,
                    'quantity' => (int) $cartItem->quantity,
                ]));
                $lineMenuItemCount = $hasVariants ? 3 : 2;
                $lineQty = max(1, (int) $cartItem->quantity);
                $lineDeliveredQty = $cartItem->delivered_at ? $lineQty : 0;
                $lineReturnedQty = min($lineQty, max(0, (int) ($cartItem->returned_qty ?? 0)));
                $showLineRentalStatus = in_array($order->status, ['pending', 'confirmed', 'completed'], true);
            @endphp
            <li data-cart-line="1"
                class="flex items-center gap-2 py-2.5 first:pt-0 sm:gap-3"
                data-order-item-id="{{ $cartItem->id }}"
                data-line-delivered="{{ $cartItem->delivered_at ? '1' : '0' }}"
                data-line-returned="{{ ($cartItem->returned_at || (int) ($cartItem->returned_qty ?? 0) >= (int) $cartItem->quantity) ? '1' : '0' }}"
                data-line-returned-qty="{{ (int) ($cartItem->returned_qty ?? 0) }}"
                data-line-rental-period="{{ $linePt }}"
                data-line-qty="{{ $cartItem->quantity }}"
                data-line-billing="{{ (float) ($cartItem->billing_units ?? 1) }}">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-slate-100 to-emerald-50 ring-1 ring-gray-200/80 sm:h-12 sm:w-12">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}"
                             alt=""
                             class="h-full w-full object-cover"
                             loading="lazy"
                             decoding="async">
                    @else
                        <i class="fas fa-box-open text-lg text-emerald-600/90" aria-hidden="true"></i>
                    @endif
                </div>
                <div class="min-w-0 flex-1 pr-1">
                    <p class="truncate font-medium leading-snug text-gray-900" data-line-name>{{ $lineName }}</p>
                    <p class="mt-0.5 break-words text-[11px] leading-snug text-gray-600 tabular-nums sm:text-xs">
                        ₹{{ number_format((int) round($unitPrice), 0, '.', ',') }} × {{ $cartItem->quantity }}
                        @if($usesBilling && $buFmt !== null)
                            × {{ $buFmt }}
                        @endif
                        <span class="font-semibold text-gray-800" data-line-total="{{ $cartItem->item_id }}"> = ₹{{ number_format((int) round($lineTotal), 0, '.', ',') }}</span>
                    </p>
                    @if($showLineRentalStatus)
                        <p class="mt-0.5 flex flex-wrap items-center gap-x-2.5 gap-y-0.5 text-[10px] leading-snug sm:text-[11px]"
                           data-line-rental-status
                           @if($lineDeliveredQty === 0 && $lineReturnedQty === 0) hidden @endif>
                            @if($lineDeliveredQty > 0)
                                <span class="inline-flex items-center gap-1 font-medium text-teal-800" data-line-delivered-label>
                                    <i class="fas fa-truck text-[9px] opacity-80" aria-hidden="true"></i>
                                    {{ trans_choice('vendor.line_delivered_items', $lineDeliveredQty, ['count' => $lineDeliveredQty]) }}
                                </span>
                            @endif
                            @if($lineReturnedQty > 0)
                                <span class="inline-flex items-center gap-1 font-medium text-teal-800" data-line-returned-label>
                                    <i class="fas fa-rotate-left text-[9px] opacity-80" aria-hidden="true"></i>
                                    {{ trans_choice('vendor.line_returned_items', $lineReturnedQty, ['count' => $lineReturnedQty]) }}
                                </span>
                            @endif
                        </p>
                    @endif
                </div>
                <div class="flex shrink-0 items-stretch gap-2 text-right sm:gap-3" data-line-qty-stepper="{{ $cartItem->item_id }}">
                    <div class="flex min-w-[2.25rem] flex-col items-end justify-center gap-0.5">
                        <span class="text-base font-bold tabular-nums leading-none text-gray-900 sm:text-lg" data-qty-display="{{ $cartItem->item_id }}">{{ $cartItem->quantity }}</span>
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.order_wizard_qty') }}</span>
                    </div>
                    @if($buFmt !== null)
                        <div class="flex min-w-[2.25rem] flex-col items-end justify-center gap-0.5 border-l border-gray-200 pl-2 sm:pl-3">
                            <span class="text-base font-bold tabular-nums leading-none text-gray-900 sm:text-lg" data-billing-display="{{ $cartItem->item_id }}">{{ $buFmt }}</span>
                            <span class="max-w-[4.5rem] truncate text-[10px] font-semibold capitalize leading-tight text-gray-500 sm:max-w-none">{{ $unitShort }}</span>
                        </div>
                    @endif
                    @if(! $orderReadOnlyList)
                        <div class="relative z-0 flex shrink-0 items-center border-l border-gray-200 pl-1 sm:pl-2"
                             x-data="{
                                menu: false,
                                menuTop: 0,
                                menuLeft: 0,
                                openMenu(event) {
                                    if (this.menu) {
                                        this.menu = false;
                                        return;
                                    }
                                    const rect = event.currentTarget.getBoundingClientRect();
                                    const width = 144;
                                    const itemCount = {{ $lineMenuItemCount }};
                                    let left = rect.right - width;
                                    left = Math.max(8, Math.min(left, window.innerWidth - width - 8));
                                    let top = rect.bottom + 6;
                                    const menuHeight = itemCount * 44 + 8;
                                    if (top + menuHeight > window.innerHeight - 8) {
                                        top = Math.max(8, rect.top - menuHeight - 6);
                                    }
                                    this.menuLeft = left;
                                    this.menuTop = top;
                                    this.menu = true;
                                },
                                closeMenu() { this.menu = false; },
                             }"
                             :class="menu && 'z-[70]'">
                            <button type="button"
                                    data-cart-remove="1"
                                    class="sr-only"
                                    tabindex="-1"
                                    aria-hidden="true"></button>
                            <button type="button"
                                    class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 [touch-action:manipulation]"
                                    @click.stop="openMenu($event)"
                                    :aria-expanded="menu"
                                    aria-haspopup="true"
                                    aria-label="{{ __('vendor.order_wizard_summary_more_actions') }}">
                                <i class="fas fa-ellipsis-v text-sm" aria-hidden="true"></i>
                            </button>
                            <template x-teleport="body">
                                <div x-show="menu"
                                     x-cloak
                                     class="fixed inset-0 z-[75]"
                                     @keydown.escape.window="closeMenu()">
                                    <div class="absolute inset-0 bg-transparent"
                                         aria-hidden="true"
                                         @click="closeMenu()"></div>
                                    <div x-show="menu"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="fixed w-36 overflow-hidden rounded-lg border border-gray-200 bg-white py-1 shadow-xl ring-1 ring-black/10"
                                         :style="'top:' + menuTop + 'px;left:' + menuLeft + 'px'"
                                         role="menu"
                                         @click.stop>
                                        <button type="button"
                                                role="menuitem"
                                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium text-gray-800 hover:bg-gray-50"
                                                data-edit-b64="{{ $lineEditPayload }}"
                                                @click="closeMenu(); $dispatch('order-line-edit', JSON.parse(atob($event.currentTarget.dataset.editB64)))">
                                            <i class="fas fa-pen w-4 text-center text-xs text-gray-400" aria-hidden="true"></i>
                                            {{ __('vendor.order_wizard_summary_edit_line') }}
                                        </button>
                                        @if($hasVariants)
                                        <button type="button"
                                                role="menuitem"
                                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium text-gray-800 hover:bg-gray-50"
                                                data-variant-b64="{{ $lineVariantChangePayload }}"
                                                @click="closeMenu(); $dispatch('order-line-change-variant', JSON.parse(atob($event.currentTarget.dataset.variantB64)))">
                                            <i class="fas fa-exchange-alt w-4 text-center text-xs text-gray-400" aria-hidden="true"></i>
                                            {{ __('vendor.order_wizard_edit_variant') }}
                                        </button>
                                        @endif
                                        <button type="button"
                                                role="menuitem"
                                                class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm font-medium text-red-700 hover:bg-red-50"
                                                @click="closeMenu(); removeCartItem(@js($order->uuid), {{ $cartItem->item_id }}, $event.currentTarget)">
                                            <i class="fas fa-trash-alt w-4 text-center text-xs" aria-hidden="true"></i>
                                            {{ __('vendor.remove') }}
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
@else
    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-gray-50/80 px-6 py-14 text-center">
        <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
            <i class="fas fa-shopping-cart text-3xl text-gray-400" aria-hidden="true"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.no_items_yet') }}</h3>
        <p class="mt-2 max-w-sm text-sm leading-relaxed text-gray-600">{{ __('vendor.add_to_cart') }}</p>
        @if(! $orderReadOnlyList)
            <button type="button" @click="showAddItem = true"
                    class="mt-6 inline-flex min-h-[48px] items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3.5 text-base font-semibold text-white shadow-md transition hover:bg-emerald-700 active:scale-[0.99]">
                <i class="fas fa-plus" aria-hidden="true"></i>{{ __('vendor.add_item') }}
            </button>
        @endif
    </div>
@endif
