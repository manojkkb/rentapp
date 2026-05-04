@extends('vendor.layouts.app')

@section('title', __('vendor.order_details'))
@section('page-title', __('vendor.order_details'))

@section('main_bottom_class')
    pb-[max(4.25rem,env(safe-area-inset-bottom))] md:pb-6
@endsection

@section('content')
{{-- Alpine state must not live inside x-data="..." — JSON quotes break the HTML attribute and leak JS as visible text. --}}
<script>
function isoToDatetimeLocalValue(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}
function orderPageData() {
    const p = {
        addedItems: @json($order->items->pluck('quantity', 'item_id')),
        addedItemBillingUnits: @json($order->items->mapWithKeys(fn ($row) => [$row->item_id => (float) ($row->billing_units ?? 1)])->all()),
        billingUnitsLabels: @json($cartBillingUnitsLabels),
        items: @json($availableItems),
        fulfillmentType: @json($order->fulfillment_type ?: 'pickup'),
        deliveryAddress: @json($order->delivery_address ?? ''),
        pickupAt: @json($order->pickup_at ? $order->pickup_at->copy()->timezone(config('app.timezone'))->format('Y-m-d\TH:i') : ''),
        deliveryCharge: @json(round((float) ($order->delivery_charge ?? 0), 2)),
        saveFulfillmentUrl: @json(route('vendor.orders.fulfillment', $order)),
    };
    return {
        showAddItem: false,
        addedItems: p.addedItems,
        addedItemBillingUnits: p.addedItemBillingUnits,
        billingUnitsLabels: p.billingUnitsLabels,
        addingItem: null,
        updatingItem: null,
        searchQuery: '',
        selectedCategory: '',
        items: p.items,
        fulfillmentType: p.fulfillmentType,
        deliveryAddress: p.deliveryAddress,
        pickupAt: p.pickupAt,
        deliveryCharge: p.deliveryCharge,
        savingFulfillment: false,
        fulfillmentFieldError: '',
        saveFulfillmentUrl: p.saveFulfillmentUrl,
        async saveFulfillment() {
            this.fulfillmentFieldError = '';
            this.savingFulfillment = true;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const res = await fetch(this.saveFulfillmentUrl, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        fulfillment_type: this.fulfillmentType,
                        delivery_address: (this.deliveryAddress || '').trim(),
                        pickup_at: this.fulfillmentType === 'pickup' && this.pickupAt ? this.pickupAt : null,
                        delivery_charge: this.fulfillmentType === 'delivery' ? (parseFloat(this.deliveryCharge) || 0) : 0,
                    }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    if (this.fulfillmentType === 'delivery') {
                        this.pickupAt = '';
                    }
                    if (data.delivery_address !== undefined) {
                        this.deliveryAddress = data.delivery_address ?? '';
                    }
                    if (data.pickup_at) {
                        this.pickupAt = isoToDatetimeLocalValue(data.pickup_at);
                    } else if (this.fulfillmentType === 'pickup') {
                        this.pickupAt = '';
                    }
                    if (data.delivery_charge !== undefined && data.delivery_charge !== null) {
                        this.deliveryCharge = parseFloat(data.delivery_charge) || 0;
                    }
                    if (data.order && typeof updateSummary === 'function') {
                        updateSummary(data.order);
                    }
                    showToast(data.message || 'Saved', 'success');
                } else if (res.status === 422 && data.errors) {
                    const msgs = Object.values(data.errors).flat();
                    this.fulfillmentFieldError = msgs[0] || data.message || '';
                    showToast(this.fulfillmentFieldError, 'error');
                } else {
                    showToast(data.message || 'Could not save', 'error');
                }
            } catch (e) {
                showToast('Error saving', 'error');
            } finally {
                this.savingFulfillment = false;
            }
        },
        isAdded(itemId) {
            return this.addedItems[itemId] !== undefined;
        },
        getAddedQty(itemId) {
            return this.addedItems[itemId] || 1;
        },
        lineUsesBillingUnits(item) {
            return item.price_type !== 'fixed';
        },
        billingUnitsLabelForLine(item) {
            const t = item.price_type;
            return this.billingUnitsLabels[t] || '';
        },
        getLineBillingUnits(itemId) {
            const v = this.addedItemBillingUnits[itemId];
            if (v !== undefined && v !== null && !Number.isNaN(parseFloat(v))) {
                return parseFloat(v);
            }
            return 1;
        },
        onBillingUnitsBlur(itemId, item, ev) {
            let v = parseFloat(ev.target.value);
            if (!Number.isFinite(v) || v < 0.01) {
                v = 1;
                ev.target.value = '1';
            }
            this.addedItemBillingUnits = { ...this.addedItemBillingUnits, [itemId]: v };
            if (this.isAdded(itemId)) {
                this.updatingItem = itemId;
                updateModalItemQty(itemId, this.getAddedQty(itemId), this);
            }
        },
        incrementBillingUnits(itemId, item) {
            if (this.updatingItem === itemId) return;
            const cur = parseFloat(this.getLineBillingUnits(itemId));
            const v = Math.round((Number.isFinite(cur) ? cur : 1) * 100 + 100) / 100;
            this.addedItemBillingUnits = { ...this.addedItemBillingUnits, [itemId]: v };
            if (this.isAdded(itemId)) {
                this.updatingItem = itemId;
                updateModalItemQty(itemId, this.getAddedQty(itemId), this);
            }
        },
        decrementBillingUnits(itemId, item) {
            if (this.updatingItem === itemId) return;
            const cur = parseFloat(this.getLineBillingUnits(itemId));
            if (!Number.isFinite(cur) || cur <= 0.011) return;
            let v = Math.round((Number.isFinite(cur) ? cur : 1) * 100 - 100) / 100;
            if (!Number.isFinite(v) || v < 0.01) v = 0.01;
            this.addedItemBillingUnits = { ...this.addedItemBillingUnits, [itemId]: v };
            if (this.isAdded(itemId)) {
                this.updatingItem = itemId;
                updateModalItemQty(itemId, this.getAddedQty(itemId), this);
            }
        },
        addItemToCart(itemId) {
            if (this.addingItem === itemId) return;
            this.addingItem = itemId;
            addItemToCartAjax(itemId, 1, this);
        },
        incrementCartQty(itemId) {
            if (this.updatingItem === itemId) return;
            this.updatingItem = itemId;
            const newQty = this.getAddedQty(itemId) + 1;
            this.addedItems[itemId] = newQty;
            updateModalItemQty(itemId, newQty, this);
        },
        decrementCartQty(itemId) {
            if (this.updatingItem === itemId) return;
            this.updatingItem = itemId;
            const currentQty = this.getAddedQty(itemId);
            if (currentQty > 1) {
                const newQty = currentQty - 1;
                this.addedItems[itemId] = newQty;
                updateModalItemQty(itemId, newQty, this);
            } else {
                removeModalItem(itemId, this);
            }
        },
        syncItemRemoved(itemId) {
            const updated = { ...this.addedItems };
            delete updated[itemId];
            this.addedItems = updated;
            const bu = { ...this.addedItemBillingUnits };
            delete bu[itemId];
            this.addedItemBillingUnits = bu;
        },
        syncItemUpdated(itemId, qty, billingUnits) {
            this.addedItems = { ...this.addedItems, [itemId]: qty };
            if (billingUnits !== undefined && billingUnits !== null) {
                this.addedItemBillingUnits = { ...this.addedItemBillingUnits, [itemId]: parseFloat(billingUnits) };
            }
        },
        syncAllRemoved() {
            this.addedItems = {};
            this.addedItemBillingUnits = {};
        },
        get filteredItems() {
            return this.items.filter(item => {
                const matchesSearch = !this.searchQuery ||
                    item.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                    (item.category && item.category.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
                const matchesCategory = !this.selectedCategory ||
                    (item.category_id == this.selectedCategory);
                return matchesSearch && matchesCategory;
            });
        },
    };
}
</script>
<div id="orderApp" x-data="orderPageData()"
@order-item-removed.window="syncItemRemoved($event.detail.itemId)"
@order-item-updated.window="syncItemUpdated($event.detail.itemId, $event.detail.quantity, $event.detail.billing_units)"
@order-emptied.window="syncAllRemoved()"
>
    
    <div class="mb-3 flex w-full min-w-0 flex-col gap-3 sm:mb-4 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between md:mb-6">
        <a href="{{ route('vendor.orders.index') }}"
           class="inline-flex min-h-[44px] w-full shrink-0 items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/80 hover:text-blue-800 active:scale-[0.98] sm:w-auto sm:justify-start">
            <i class="fas fa-arrow-left text-blue-600"></i>
            {{ __('vendor.back') }}
        </a>
        @php
            $orderStatusBadgeClasses = [
                'pending' => 'border-amber-200 bg-amber-50 text-amber-900 ring-amber-100',
                'confirmed' => 'border-blue-200 bg-blue-50 text-blue-900 ring-blue-100',
                'ongoing' => 'border-purple-200 bg-purple-50 text-purple-900 ring-purple-100',
                'completed' => 'border-emerald-200 bg-emerald-50 text-emerald-900 ring-emerald-100',
                'cancelled' => 'border-red-200 bg-red-50 text-red-900 ring-red-100',
            ];
            $st = $order->status;
            $orderStatusBadgeClass = $orderStatusBadgeClasses[$st] ?? 'border-gray-200 bg-gray-50 text-gray-900 ring-gray-100';
            $orderNextStatuses = $order->allowedNextStatuses();
        @endphp
        <div class="flex w-full min-w-0 flex-wrap items-stretch gap-2 sm:w-auto sm:items-center sm:justify-end sm:gap-3">
            <span class="inline-flex min-h-[44px] min-w-0 flex-1 items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2.5 text-sm font-bold text-emerald-800 sm:flex-initial sm:justify-start sm:px-4">
                <span class="truncate">{{ $order->order_number }}</span>
            </span>
            <div class="flex w-full min-w-0 flex-1 flex-col gap-2 sm:w-auto sm:max-w-md sm:flex-initial sm:flex-row sm:items-center sm:justify-end">
                <span class="inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-sm font-semibold ring-1 sm:justify-start sm:px-4 {{ $orderStatusBadgeClass }}">
                    {{ __('vendor.'.$st) }}
                </span>
                @if(count($orderNextStatuses) > 0)
                    <form method="POST"
                          action="{{ route('vendor.orders.update-status', $order) }}"
                          class="flex w-full min-w-0 flex-col gap-2 sm:w-auto sm:flex-row sm:items-center sm:justify-end sm:gap-2">
                        @csrf
                        @method('PUT')
                        <label for="order-status-next" class="sr-only">{{ __('vendor.order_status') }}</label>
                        <select id="order-status-next"
                                name="status"
                                required
                                class="min-h-[44px] w-full min-w-0 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-900 shadow-sm ring-1 ring-gray-100 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/25 sm:w-auto sm:min-w-[11rem]">
                            <option value="" disabled selected>{{ __('vendor.order_status_next_placeholder') }}</option>
                            @foreach($orderNextStatuses as $next)
                                <option value="{{ $next }}">{{ __('vendor.'.$next) }}</option>
                            @endforeach
                        </select>
                        <button type="submit"
                                class="inline-flex min-h-[44px] w-full shrink-0 items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 active:scale-[0.99] sm:w-auto">
                            {{ __('vendor.update_status') }}
                        </button>
                    </form>
                @endif
                @error('status')
                    <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 flex items-start gap-3 rounded-2xl border border-green-200 bg-green-50/90 p-4 shadow-sm ring-1 ring-green-100">
            <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
            <div class="flex-1">
                <p class="text-sm font-medium text-green-900">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50/90 p-4 shadow-sm ring-1 ring-red-100">
            <div class="flex items-start space-x-3">
                <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-900 mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-sm text-red-800 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-3 lg:gap-8 lg:pb-0">

        <div class="order-2 space-y-4 sm:space-y-5 lg:order-1 lg:col-span-2 lg:space-y-6">

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 bg-gradient-to-r from-blue-50 via-white to-indigo-50/80 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 shadow-md ring-2 ring-white/50">
                                <i class="fas fa-shopping-cart text-lg text-white"></i>
                            </div>
                            <div class="min-w-0">
                                <h2 class="truncate text-lg font-bold tracking-tight text-gray-900 sm:text-xl" data-order-title>{{ $order->order_number }}</h2>
                                <p class="mt-0.5 text-sm text-gray-600">{{ __('vendor.created') }} {{ $order->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <button type="button" onclick="openEditCartModal()"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-blue-200 bg-white px-3 py-2.5 text-sm font-semibold text-blue-700 shadow-sm transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:px-4">
                            <i class="fas fa-edit"></i>
                            <span class="hidden sm:inline">{{ __('vendor.edit') }}</span>
                        </button>
                    </div>
                </div>

                <div class="space-y-4 p-4 sm:p-6">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                        <div class="flex gap-3 rounded-xl border border-gray-100 bg-gray-50/80 p-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 ring-1 ring-emerald-200/60">
                                <i class="fas fa-user text-emerald-700"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.customer') }}</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900">{{ $order->customer->name }}</p>
                                <p class="mt-0.5 text-sm text-gray-600">{{ $order->customer->mobile }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3 rounded-xl border border-gray-100 bg-gray-50/80 p-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-100 ring-1 ring-violet-200/60">
                                <i class="fas fa-calendar text-violet-700"></i>
                            </div>
                            <div class="min-w-0" data-booking-dates>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.booking_period') }}</p>
                                @if($order->start_at && $order->end_at)
                                    <p class="mt-1 text-sm font-semibold leading-snug text-gray-900">{{ $order->start_at->format('M d, Y h:i A') }}</p>
                                    <p class="mt-0.5 text-xs text-gray-600">{{ __('vendor.to') }} {{ $order->end_at->format('M d, Y h:i A') }}</p>
                                @else
                                    <p class="mt-1 text-sm italic text-gray-500">{{ __('vendor.not_specified') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 bg-gradient-to-r from-amber-50 via-white to-orange-50/90 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-amber-600 shadow-md ring-2 ring-white/40">
                            <i class="fas fa-truck text-lg text-white"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold tracking-tight text-gray-900">{{ __('vendor.fulfillment_method') }}</h3>
                            <p class="mt-0.5 text-sm leading-relaxed text-gray-600">{{ __('vendor.fulfillment_method_help') }}</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-5 p-4 sm:p-6">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                        <label class="relative flex min-h-[52px] cursor-pointer rounded-2xl border-2 p-4 transition-all"
                               :class="fulfillmentType === 'pickup' ? 'border-emerald-500 bg-emerald-50/50 ring-1 ring-emerald-500/30' : 'border-gray-200 hover:border-gray-300 bg-white'">
                            <input type="radio" name="fulfillment_type" value="pickup" x-model="fulfillmentType" @change="fulfillmentFieldError = ''" class="sr-only">
                            <div class="flex items-start gap-3 w-full">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                                      :class="fulfillmentType === 'pickup' ? 'border-emerald-600 bg-emerald-600' : 'border-gray-300 bg-white'">
                                    <span class="h-2 w-2 rounded-full bg-white" x-show="fulfillmentType === 'pickup'"></span>
                                </span>
                                <div>
                                    <span class="block text-sm font-bold text-gray-900">{{ __('vendor.pickup') }}</span>
                                    <span class="block text-xs text-gray-600 mt-0.5">{{ __('vendor.pickup_short_help') }}</span>
                                </div>
                            </div>
                        </label>
                        <label class="relative flex min-h-[52px] cursor-pointer rounded-2xl border-2 p-4 transition-all"
                               :class="fulfillmentType === 'delivery' ? 'border-emerald-500 bg-emerald-50/50 ring-1 ring-emerald-500/30' : 'border-gray-200 hover:border-gray-300 bg-white'">
                            <input type="radio" name="fulfillment_type" value="delivery" x-model="fulfillmentType" @change="fulfillmentFieldError = ''" class="sr-only">
                            <div class="flex items-start gap-3 w-full">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                                      :class="fulfillmentType === 'delivery' ? 'border-emerald-600 bg-emerald-600' : 'border-gray-300 bg-white'">
                                    <span class="h-2 w-2 rounded-full bg-white" x-show="fulfillmentType === 'delivery'"></span>
                                </span>
                                <div>
                                    <span class="block text-sm font-bold text-gray-900">{{ __('vendor.delivery') }}</span>
                                    <span class="block text-xs text-gray-600 mt-0.5">{{ __('vendor.delivery_short_help') }}</span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div x-show="fulfillmentType === 'pickup'" class="space-y-4" x-cloak>
                        <div class="space-y-2">
                            <label for="pickup_at_input" class="block text-sm font-semibold text-gray-800">
                                {{ __('vendor.pickup_datetime') }}
                            </label>
                            <input type="datetime-local"
                                   id="pickup_at_input"
                                   x-model="pickupAt"
                                   class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-base text-gray-900 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 sm:text-sm"
                            />
                            <p class="text-xs text-gray-500">{{ __('vendor.pickup_datetime_help') }}</p>
                        </div>
                        <div class="space-y-2">
                            <label for="delivery_address_pickup_input" class="block text-sm font-semibold text-gray-800">
                                {{ __('vendor.delivery_address') }}
                            </label>
                            <textarea id="delivery_address_pickup_input"
                                      x-model="deliveryAddress"
                                      rows="4"
                                      class="w-full resize-y rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-base text-gray-900 shadow-inner placeholder:text-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 sm:text-sm"
                                      :class="fulfillmentFieldError ? 'border-red-400' : ''"
                                      placeholder="{{ __('vendor.delivery_address_help') }}"></textarea>
                            <p class="text-xs text-gray-500">{{ __('vendor.delivery_address_optional_pickup') }}</p>
                        </div>
                    </div>

                    <div x-show="fulfillmentType === 'delivery'" class="space-y-2" x-cloak>
                        <div>
                            <label for="delivery_charge_input" class="block text-sm font-semibold text-gray-800">
                                {{ __('vendor.delivery_charge') }}
                            </label>
                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-500">₹</span>
                                <input type="number"
                                       id="delivery_charge_input"
                                       x-model.number="deliveryCharge"
                                       min="0"
                                       step="0.01"
                                       class="w-full rounded-xl border border-gray-200 bg-white py-3 pl-9 pr-4 text-base text-gray-900 shadow-inner focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/30 sm:text-sm"
                                       placeholder="0.00"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ __('vendor.delivery_charge_help') }}</p>
                        </div>
                        <label for="delivery_address_input" class="block text-sm font-semibold text-gray-800">
                            {{ __('vendor.delivery_address') }}
                        </label>
                        <textarea id="delivery_address_input"
                                  x-model="deliveryAddress"
                                  rows="4"
                                  class="w-full resize-y rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-base text-gray-900 shadow-inner placeholder:text-gray-400 focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/30 sm:text-sm"
                                  :class="fulfillmentFieldError ? 'border-red-400' : ''"
                                  placeholder="{{ __('vendor.delivery_address_help') }}"></textarea>
                        <p class="text-xs text-gray-500">{{ __('vendor.delivery_address_help') }}</p>
                        <p x-show="fulfillmentFieldError" class="text-sm text-red-600" x-text="fulfillmentFieldError"></p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        <button type="button"
                                @click="saveFulfillment()"
                                :disabled="savingFulfillment"
                                class="inline-flex min-h-[48px] items-center justify-center gap-2 rounded-xl bg-orange-600 px-5 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-50 active:scale-[0.99]">
                            <i class="fas fa-spinner fa-spin" x-show="savingFulfillment"></i>
                            <i class="fas fa-save" x-show="!savingFulfillment"></i>
                            {{ __('vendor.save') }}
                        </button>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100">
                <div class="border-b border-gray-100 bg-gradient-to-r from-slate-50 via-white to-blue-50/60 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold tracking-tight text-gray-900">{{ __('vendor.cart_items') }}</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                <span data-items-count class="inline-flex items-center gap-2 font-medium text-gray-800">
                                    <span class="inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-full bg-blue-100 px-2 text-xs font-bold text-blue-800 tabular-nums">{{ $order->items->count() }}</span>
                                    <span>{{ __('vendor.items') }}</span>
                                </span>
                            </p>
                        </div>
                        <div class="flex w-full flex-shrink-0 flex-col gap-2 sm:w-auto sm:flex-row sm:justify-end">
                            <button type="button" @click="showAddItem = true"
                                    class="inline-flex min-h-[48px] flex-1 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-blue-700 active:scale-[0.99] sm:flex-none sm:min-h-[44px] sm:py-2.5">
                                <i class="fas fa-plus"></i>
                                {{ __('vendor.add_item') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div data-items-list class="p-3 sm:p-4 md:p-5">
                    @if($order->items->count() > 0)
                        <style>
                            @media (max-width: 767px) {
                                .cart-line-table thead { display: none !important; }
                                .cart-line-table { display: block; width: 100%; border: 0; }
                                .cart-line-table tbody { display: block; }
                                .cart-line-table tbody tr[data-cart-line] {
                                    display: flex !important;
                                    flex-direction: column;
                                    gap: 0.625rem;
                                    padding: 0.875rem;
                                    margin-bottom: 0.75rem;
                                    border: 1px solid #e5e7eb !important;
                                    border-radius: 0.75rem;
                                    background: #fff;
                                    box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
                                }
                                .cart-line-table tbody tr[data-cart-line]:last-child { margin-bottom: 0; }
                                .cart-line-table tbody td {
                                    display: grid !important;
                                    grid-template-columns: minmax(5.25rem, 32%) 1fr;
                                    gap: 0.5rem 0.75rem;
                                    align-items: center;
                                    padding: 0 !important;
                                    border: none !important;
                                    width: 100% !important;
                                    text-align: left !important;
                                }
                                .cart-line-table tbody td:first-child,
                                .cart-line-table tbody td:nth-child(2),
                                .cart-line-table tbody td:nth-child(3) { align-items: start; }
                                .cart-line-table tbody td::before {
                                    content: attr(data-col);
                                    font-size: 0.65rem;
                                    font-weight: 600;
                                    line-height: 1.25;
                                    text-transform: uppercase;
                                    letter-spacing: 0.06em;
                                    color: #6b7280;
                                    padding-top: 0.15rem;
                                }
                                .cart-line-table tbody td:last-child { justify-items: start; }
                            }
                        </style>
                        <div class="-mx-3 max-md:overflow-visible md:-mx-0 md:overflow-x-auto md:rounded-xl md:border md:border-gray-200">
                            <table class="cart-line-table w-full border-collapse text-left text-sm md:min-w-[36rem]">
                                <thead>
                                    <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        <th class="py-3 pl-3 pr-2 sm:pl-4">{{ __('vendor.item') }}</th>
                                        <th class="w-[1%] whitespace-nowrap px-2 py-3 text-center">{{ __('vendor.quantity') }}</th>
                                        <th class="w-[1%] whitespace-nowrap px-2 py-3 text-center">{{ __('vendor.cart_duration') }}</th>
                                        <th class="w-[1%] whitespace-nowrap px-2 py-3 text-right">{{ __('vendor.total') }}</th>
                                        <th class="w-12 px-2 py-3 pr-3 text-center sm:pr-4"><span class="sr-only">{{ __('vendor.remove_from_cart') }}</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                            @foreach($order->items as $cartItem)
                                @php
                                    $linePt = $cartItem->price_type ?? ($cartItem->item?->price_type ?? 'per_day');
                                @endphp
                                <tr data-cart-line="1"
                                    class="border-b border-gray-100 bg-white transition hover:bg-slate-50/80 max-md:border-0 max-md:hover:bg-white md:border-b"
                                    data-line-price-type="{{ $linePt }}"
                                    data-line-qty="{{ $cartItem->quantity }}"
                                    data-line-billing="{{ (float) ($cartItem->billing_units ?? 1) }}">
                                    <td class="align-middle py-3 pl-3 pr-2 sm:pl-4" data-col="{{ __('vendor.item') }}">
                                        <div class="flex min-w-0 max-w-md gap-3">
                                            <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-slate-100 to-blue-50 ring-1 ring-gray-200/80 sm:h-16 sm:w-16" aria-hidden="true">
                                                <i class="fas fa-box-open text-xl text-blue-600/90"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-bold leading-snug text-gray-900">{{ $cartItem->item?->name ?? $cartItem->item_name }}</div>
                                                <div class="mt-0.5 text-base font-bold tabular-nums text-blue-700">₹{{ number_format((float) ($cartItem->item?->price ?? $cartItem->price), 2) }}</div>
                                                <div class="text-xs text-gray-500">{{ $cartItem->item?->category->name ?? __('vendor.no_category') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle px-2 py-3 text-center max-md:text-left" data-col="{{ __('vendor.quantity') }}">
                                        <div class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-gray-50/80 px-1 py-0.5" data-line-qty-stepper="{{ $cartItem->item_id }}">
                                            <button type="button"
                                                onclick="nudgeLineQty({{ $order->id }}, {{ $cartItem->item_id }}, -1, this)"
                                                class="flex h-9 w-9 items-center justify-center rounded-md bg-white text-gray-700 shadow-sm transition hover:bg-gray-100 active:scale-95"
                                                title="{{ __('vendor.quantity') }} −1">
                                                <i class="fas fa-minus text-xs"></i>
                                            </button>
                                            <span class="min-w-[2rem] text-center text-sm font-bold tabular-nums text-gray-900" data-qty-display="{{ $cartItem->item_id }}">{{ $cartItem->quantity }}</span>
                                            <button type="button"
                                                onclick="nudgeLineQty({{ $order->id }}, {{ $cartItem->item_id }}, 1, this)"
                                                class="flex h-9 w-9 items-center justify-center rounded-md bg-white text-gray-700 shadow-sm transition hover:bg-gray-100 active:scale-95"
                                                title="{{ __('vendor.quantity') }} +1">
                                                <i class="fas fa-plus text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="align-middle px-2 py-3 text-center max-md:text-left" data-col="{{ __('vendor.cart_duration') }}">
                                        @if(\App\Models\Items::priceTypeUsesBillingUnits($linePt))
                                            <div class="inline-flex flex-col items-center gap-1">
                                                <span class="text-[0.65rem] font-semibold uppercase tracking-wide text-gray-500">{{ \App\Models\Items::billingUnitsFieldLabel($linePt) }}</span>
                                                <div class="inline-flex items-center gap-1">
                                                    <button type="button"
                                                        onclick="nudgeLineBilling({{ $order->id }}, {{ $cartItem->item_id }}, -1)"
                                                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50 active:scale-95">
                                                        <i class="fas fa-minus text-xs"></i>
                                                    </button>
                                                    <input id="line-billing-{{ $cartItem->item_id }}" type="number" step="0.01" min="0.01" lang="en"
                                                        class="h-9 w-14 rounded-lg border border-gray-200 bg-white text-center text-sm font-bold text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"
                                                        value="{{ (float) ($cartItem->billing_units ?? 1) }}"
                                                        onblur="updateCartBillingUnits({{ $order->id }}, {{ $cartItem->item_id }}, this)">
                                                    <button type="button"
                                                        onclick="nudgeLineBilling({{ $order->id }}, {{ $cartItem->item_id }}, 1)"
                                                        class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50 active:scale-95">
                                                        <i class="fas fa-plus text-xs"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="align-middle px-2 py-3 text-right max-md:text-left" data-col="{{ __('vendor.total') }}">
                                        <span class="text-base font-bold tabular-nums text-gray-900" data-line-total="{{ $cartItem->item_id }}">₹{{ number_format($cartItem->lineSubtotal(), 2) }}</span>
                                    </td>
                                    <td class="align-middle px-2 py-3 pr-3 text-center sm:pr-4 max-md:text-left" data-col="{{ __('vendor.remove_from_cart') }}">
                                        <button type="button"
                                                data-cart-remove="1"
                                                onclick="removeCartItem({{ $order->id }}, {{ $cartItem->item_id }}, this)"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:bg-red-100 hover:text-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                                title="{{ __('vendor.remove_from_cart') }}">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-gray-50/80 px-6 py-14 text-center">
                            <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-gray-100">
                                <i class="fas fa-shopping-cart text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.no_items_yet') }}</h3>
                            <p class="mt-2 max-w-sm text-sm leading-relaxed text-gray-600">{{ __('vendor.add_to_cart') }}</p>
                            <button type="button" @click="showAddItem = true"
                                    class="mt-6 inline-flex min-h-[48px] items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 text-base font-semibold text-white shadow-md transition hover:bg-blue-700 active:scale-[0.99]">
                                <i class="fas fa-plus"></i>{{ __('vendor.add_item') }}
                            </button>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="order-1 lg:order-2 lg:col-span-1">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100 lg:sticky lg:top-6 lg:z-[5]">
                <div class="border-b border-gray-100 bg-gradient-to-r from-emerald-50 via-white to-teal-50/80 px-4 py-4 sm:px-5 sm:py-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-600 to-teal-600 text-white shadow-md ring-2 ring-white/60">
                            <i class="fas fa-receipt text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-lg font-bold tracking-tight text-gray-900">{{ __('vendor.summary') }}</h3>
                            <p class="mt-0.5 text-xs leading-relaxed text-gray-600">{{ __('vendor.summary_help') }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-4 sm:p-5">
                    {{-- 1. Rental charges --}}
                    <div>
                        <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-500">{{ __('vendor.summary_section_charges') }}</p>
                        <div class="space-y-0 rounded-xl border border-gray-100 bg-gray-50/70 p-1">
                            <div class="flex items-center justify-between gap-3 rounded-lg px-3 py-2.5">
                                <span class="text-sm text-gray-600">{{ __('vendor.sub_total') }}</span>
                                <span data-sub-total class="text-sm font-semibold tabular-nums text-gray-900">₹{{ number_format($order->sub_total, 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-lg border-t border-gray-100/80 bg-white/60 px-3 py-2.5">
                                <span class="text-sm text-gray-600">{{ __('vendor.tax') }} <span class="text-gray-400">(10%)</span></span>
                                <span data-tax-total class="text-sm font-semibold tabular-nums text-gray-900">₹{{ number_format($order->tax_total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Discounts & coupons --}}
                    <div>
                        <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-500">{{ __('vendor.summary_section_savings') }}</p>
                        <div class="space-y-2 rounded-xl border border-dashed border-gray-200 bg-white p-3">
                            <div id="discount-add" class="{{ $order->discount_amount > 0 ? 'hidden' : '' }}">
                                <button type="button" onclick="openDiscountModal()"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-gray-50/80 py-2.5 text-sm font-semibold text-blue-700 transition hover:border-blue-200 hover:bg-blue-50/80">
                                    <i class="fas fa-plus-circle text-xs"></i>
                                    <span>{{ __('vendor.add_discount') }}</span>
                                </button>
                            </div>
                            <div id="discount-applied" class="{{ $order->discount_amount > 0 ? '' : 'hidden' }}">
                                <div class="flex items-center justify-between gap-2 rounded-lg bg-blue-50/50 px-3 py-2 ring-1 ring-blue-100/80">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <i class="fas fa-tag shrink-0 text-blue-600 text-xs"></i>
                                        <span class="truncate text-sm font-medium text-gray-800" id="discount-label">
                                            @if($order->discount_type === 'percent')
                                                {{ __('vendor.discount') }} {{ rtrim(rtrim(number_format($order->discount_value, 2), '0'), '.') }}%
                                            @elseif($order->discount_type === 'fixed')
                                                {{ __('vendor.discount') }} ₹{{ number_format($order->discount_value, 2) }}
                                            @else
                                                {{ __('vendor.discount') }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <span data-discount-amount class="text-sm font-semibold tabular-nums text-red-600">-₹{{ number_format($order->discount_amount, 2) }}</span>
                                        <button type="button" onclick="removeDiscount()"
                                                class="rounded p-1 text-red-500 transition hover:bg-red-100 hover:text-red-700"
                                                title="{{ __('vendor.remove') }} {{ __('vendor.discount') }}">
                                            <i class="fas fa-times-circle text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="coupon-add" class="{{ $order->coupon_code ? 'hidden' : '' }}">
                                <button type="button" onclick="openCouponModal()"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-gray-50/80 py-2.5 text-sm font-semibold text-emerald-800 transition hover:border-emerald-200 hover:bg-emerald-50/80">
                                    <i class="fas fa-ticket-alt text-xs"></i>
                                    <span>{{ __('vendor.add_coupon') }}</span>
                                </button>
                            </div>
                            <div id="coupon-applied" class="{{ $order->coupon_code ? '' : 'hidden' }}">
                                <div class="flex items-center justify-between gap-2 rounded-lg bg-emerald-50/50 px-3 py-2 ring-1 ring-emerald-100/80">
                                    <div class="flex min-w-0 items-center gap-2">
                                        <i class="fas fa-ticket-alt shrink-0 text-emerald-600 text-xs"></i>
                                        <span class="truncate text-sm font-semibold text-emerald-900" data-coupon-code>{{ $order->coupon_code }}</span>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <span data-coupon-discount class="text-sm font-semibold tabular-nums text-red-600">-₹{{ number_format($order->coupon_discount, 2) }}</span>
                                        <button type="button" onclick="removeCoupon()"
                                                class="rounded p-1 text-red-500 transition hover:bg-red-100 hover:text-red-700"
                                                title="{{ __('vendor.remove') }} {{ __('vendor.coupon_code') }}">
                                            <i class="fas fa-times-circle text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 border-t border-gray-100 pt-2.5">
                                <span class="text-sm font-semibold text-gray-700">{{ __('vendor.total_savings') }}</span>
                                <span data-discount-total class="text-sm font-bold tabular-nums text-red-600">-₹{{ number_format($order->discount_total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    @php
                        $showDeliveryLine = (($order->fulfillment_type ?? 'pickup') === 'delivery' && (float) ($order->delivery_charge ?? 0) > 0);
                    @endphp
                    <div id="summary-delivery-charge-row"
                         class="flex items-center justify-between gap-3 rounded-xl border border-orange-100 bg-orange-50/40 px-3.5 py-2.5 {{ $showDeliveryLine ? '' : 'hidden' }}">
                        <span class="text-sm font-medium text-gray-800">{{ __('vendor.delivery_charge') }}</span>
                        <span data-delivery-charge-line class="text-sm font-bold tabular-nums text-gray-900">₹{{ number_format((float) ($order->delivery_charge ?? 0), 2) }}</span>
                    </div>

                    {{-- 3. Order total (before deposit) --}}
                    <div class="flex items-start justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50/90 px-3.5 py-3">
                        <div class="min-w-0">
                            <span class="text-sm font-bold text-slate-800">{{ __('vendor.summary_order_total') }}</span>
                            <span class="mt-0.5 block text-[11px] leading-snug text-slate-500">{{ __('vendor.summary_order_total_hint') }}</span>
                        </div>
                        <span data-order-total class="shrink-0 text-base font-bold tabular-nums text-slate-900">₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>

                    {{-- 4. Security deposit --}}
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-white px-3 py-2.5 shadow-sm">
                        <button type="button"
                                onclick="openSecurityDepositModal()"
                                class="inline-flex min-w-0 flex-1 items-center gap-2 text-left text-sm font-semibold text-blue-700 transition hover:text-blue-900">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600 ring-1 ring-blue-100">
                                <i class="fas fa-shield-alt text-xs"></i>
                            </span>
                            <span id="securityDepositLabel" class="truncate">{{ __('vendor.quote_security_deposit') }}</span>
                        </button>
                        <span data-security-deposit-total class="shrink-0 text-sm font-bold tabular-nums text-gray-900">₹{{ number_format($order->security_deposit ?? 0, 2) }}</span>
                    </div>

                    {{-- 5. Total due (order + deposit) --}}
                    <div class="rounded-xl border-2 border-emerald-200/90 bg-gradient-to-br from-emerald-50 via-white to-teal-50/70 p-4 shadow-sm ring-1 ring-emerald-100/60">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="text-sm font-bold text-gray-900">{{ __('vendor.summary_total_due') }}</span>
                                <span class="mt-0.5 block text-[11px] font-medium leading-snug text-emerald-800/80">{{ __('vendor.summary_total_due_hint') }}</span>
                            </div>
                            <span data-grand-total class="shrink-0 text-xl font-bold tabular-nums tracking-tight text-emerald-700">₹{{ number_format((float) $order->grand_total + (float) ($order->security_deposit ?? 0), 2) }}</span>
                        </div>
                    </div>

                    {{-- 6. Payment status --}}
                    <div class="space-y-2 border-t border-gray-200 pt-4">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">{{ __('vendor.summary_section_payment') }}</p>
                        <div class="flex items-center justify-between rounded-xl bg-gray-50/80 px-3.5 py-2.5 ring-1 ring-gray-100">
                            <span class="text-sm text-gray-600">{{ __('vendor.paid_amount') }}</span>
                            <span data-paid-amount class="text-sm font-semibold tabular-nums text-emerald-600">₹{{ number_format($order->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl border border-amber-200/80 bg-amber-50/70 px-3.5 py-3">
                            <span class="text-sm font-bold text-gray-900">{{ __('vendor.balance_due') }}</span>
                            <span data-balance-due class="text-base font-bold tabular-nums text-red-600">₹{{ number_format((float) $order->grand_total + (float) ($order->security_deposit ?? 0) - (float) $order->paid_amount, 2) }}</span>
                        </div>
                        <button type="button" onclick="openAddPaymentModal()"
                                class="mt-1 inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-blue-700 active:scale-[0.99]">
                            <i class="fas fa-wallet"></i>{{ __('vendor.new_payment') }}
                        </button>

                        @php
                            $paymentRows = is_array($order->payment_detail) ? $order->payment_detail : [];
                        @endphp
                        <div id="payment-history-section" class="mt-4 space-y-2">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">{{ __('vendor.payment_history_title') }}</p>
                            <div id="payment-history-empty"
                                 class="{{ count($paymentRows) ? 'hidden' : '' }} rounded-xl border border-dashed border-gray-200 bg-gray-50/90 px-3 py-3 text-center text-xs leading-relaxed text-gray-500">
                                {{ __('vendor.payment_history_empty') }}
                            </div>
                            <ul id="payment-history-list" class="space-y-2 {{ count($paymentRows) ? '' : 'hidden' }}">
                                @foreach($paymentRows as $idx => $p)
                                    <li class="flex items-start gap-2 rounded-xl border border-gray-100 bg-white px-3 py-2.5 shadow-sm ring-1 ring-gray-100/80">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                                <span class="text-sm font-bold tabular-nums text-gray-900">₹{{ number_format((float) ($p['amount'] ?? 0), 2) }}</span>
                                                @if(($p['payment_for'] ?? '') === 'security_deposit')
                                                    <span class="inline-flex rounded-md bg-violet-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-violet-700 ring-1 ring-violet-100">{{ __('vendor.payment_for_deposit_short') }}</span>
                                                @else
                                                    <span class="inline-flex rounded-md bg-blue-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-blue-700 ring-1 ring-blue-100">{{ __('vendor.payment_for_order_short') }}</span>
                                                @endif
                                            </div>
                                            <p class="mt-0.5 text-[11px] text-gray-500">
                                                @php
                                                    $m = $p['method'] ?? '';
                                                    $methodLabels = ['card' => 'Card', 'cash' => 'Cash', 'upi' => 'UPI', 'bank_transfer' => 'Bank transfer', 'wallet' => 'Wallet', 'other' => 'Other'];
                                                    $mLabel = $methodLabels[$m] ?? ucfirst(str_replace('_', ' ', (string) $m));
                                                    $paidOn = ! empty($p['paid_on']) ? \Carbon\Carbon::parse($p['paid_on'])->format('M j, Y') : null;
                                                @endphp
                                                <span class="font-medium text-gray-600">{{ $mLabel }}</span>
                                                @if($paidOn)<span class="text-gray-400"> · </span><span>{{ $paidOn }}</span>@endif
                                            </p>
                                        </div>
                                        <button type="button"
                                                onclick="removeCartPayment({{ (int) $idx }})"
                                                class="shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600"
                                                title="{{ __('vendor.payment_remove') }}">
                                            <i class="fas fa-times text-sm"></i>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- New Payment Modal (step 1: select payment for) -->
                    <div id="addPaymentModal" class="fixed inset-0 z-[80] hidden">
                        <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeAddPaymentModal()"></div>
                        <div class="fixed inset-0 flex items-center justify-center p-4">
                            <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all" onclick="event.stopPropagation()">
                                <!-- Header -->
                                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 rounded-t-xl">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 flex items-center justify-center bg-blue-600 rounded-lg">
                                                <i class="fas fa-wallet text-white"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-bold text-gray-900">New Payment</h3>
                                                <p class="text-xs text-gray-600">Select payment for</p>
                                            </div>
                                        </div>
                                        <button type="button" onclick="closeAddPaymentModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg transition-colors">
                                            <i class="fas fa-times text-xl"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Body -->
                                <div class="p-6 space-y-5">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-3">Payment for</label>
                                        <div class="grid grid-cols-1 gap-3">
                                            <button type="button" onclick="openNewPaymentDueModal('order_amount')"
                                                    class="w-full rounded-lg border-2 border-gray-200 px-4 py-3 text-left transition-all hover:border-blue-400 hover:bg-blue-50/60">
                                                <div class="flex items-start gap-3">
                                                    <i class="fas fa-file-invoice-dollar mt-0.5 text-blue-600"></i>
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-800">Order amount</p>
                                                        <p class="text-xs text-gray-500">Collect payment against order total.</p>
                                                    </div>
                                                </div>
                                            </button>

                                            <button type="button" onclick="openNewPaymentDueModal('security_deposit')"
                                                    class="w-full rounded-lg border-2 border-gray-200 px-4 py-3 text-left transition-all hover:border-blue-400 hover:bg-blue-50/60">
                                                <div class="flex items-start gap-3">
                                                    <i class="fas fa-shield-alt mt-0.5 text-blue-600"></i>
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-800">Security deposit</p>
                                                        <p class="text-xs text-gray-500">Collect payment for refundable security deposit.</p>
                                                    </div>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                                        <button type="button" onclick="closeAddPaymentModal()"
                                                class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Payment: amount due (step 2) -->
                    <div id="newPaymentDueModal" class="fixed inset-0 z-[86] hidden">
                        <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="backNewPaymentDueModal()"></div>
                        <div class="fixed inset-0 flex items-center justify-center p-4">
                            <div class="relative max-h-[92vh] w-full max-w-md overflow-y-auto rounded-xl bg-white shadow-2xl" onclick="event.stopPropagation()">
                                <div class="rounded-t-xl border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-blue-50 px-6 py-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <h3 id="npDueTitle" class="text-lg font-bold text-gray-900"></h3>
                                            <p id="npDueSubtitle" class="text-xs text-gray-600"></p>
                                        </div>
                                        <button type="button" onclick="backNewPaymentDueModal()" class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-white hover:text-gray-600">
                                            <i class="fas fa-times text-xl"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="space-y-4 p-6">
                                    <div id="npSectionOrder" class="hidden rounded-xl border border-gray-100 bg-gray-50/90 p-4">
                                        <p class="text-center text-xs font-semibold uppercase tracking-wide text-blue-700">Order amount</p>
                                        <p class="text-center text-sm font-semibold text-gray-800">Rental balance</p>
                                        <dl class="mt-4 space-y-3 text-sm">
                                            <div class="flex items-center justify-between gap-3 border-b border-gray-200/80 pb-2">
                                                <dt class="text-gray-600">Due amount</dt>
                                                <dd id="npOrderDueAmount" class="font-bold tabular-nums text-gray-900">₹0.00</dd>
                                            </div>
                                            <div class="flex items-center justify-between gap-3 border-b border-gray-200/80 pb-2">
                                                <dt class="text-gray-600">Paid amount</dt>
                                                <dd id="npOrderPaidAmount" class="font-semibold tabular-nums text-emerald-700">₹0.00</dd>
                                            </div>
                                            <div class="flex items-center justify-between gap-2 text-xs text-gray-500">
                                                <span>Order total</span>
                                                <span id="npOrderTotal" class="tabular-nums">₹0.00</span>
                                            </div>
                                        </dl>
                                        <div class="mt-4">
                                            <label for="npOrderPaymentInput" class="mb-1.5 block text-left text-sm font-semibold text-gray-700">Payment amount</label>
                                            <div class="relative">
                                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₹</span>
                                                <input type="number" id="npOrderPaymentInput" step="0.01" min="0"
                                                       oninput="npSyncPayButtonState()"
                                                       class="w-full rounded-lg border border-gray-300 py-2.5 pl-8 pr-3 text-sm font-semibold tabular-nums focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                                       placeholder="0.00">
                                            </div>
                                            <p class="mt-1 text-left text-xs text-gray-500">Change the amount to collect for this order (max due).</p>
                                        </div>
                                        <div class="mt-4">
                                            <label for="npOrderPaymentDate" class="mb-1.5 block text-left text-sm font-semibold text-gray-700">Payment date</label>
                                            <input type="date" id="npOrderPaymentDate"
                                                   onchange="npSyncPayButtonState()"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>
                                    <div id="npSectionSecurity" class="hidden rounded-xl border border-gray-100 bg-gray-50/90 p-4">
                                        <p class="text-center text-xs font-semibold uppercase tracking-wide text-blue-700">Security deposit</p>
                                        <p class="text-center text-sm font-semibold text-gray-800">Refundable hold</p>
                                        <dl class="mt-4 space-y-3 text-sm">
                                            <div class="flex items-center justify-between gap-3 border-b border-gray-200/80 pb-2">
                                                <dt class="text-gray-600">Due amount</dt>
                                                <dd id="npSdDueAmount" class="font-bold tabular-nums text-gray-900">₹0.00</dd>
                                            </div>
                                            <div class="flex items-center justify-between gap-3 border-b border-gray-200/80 pb-2">
                                                <dt class="text-gray-600">Paid amount</dt>
                                                <dd id="npSdPaidAmount" class="font-semibold tabular-nums text-emerald-700">₹0.00</dd>
                                            </div>
                                            <div class="flex items-center justify-between gap-2 text-xs text-gray-500">
                                                <span>Deposit required</span>
                                                <span id="npSdTotal" class="tabular-nums">₹0.00</span>
                                            </div>
                                        </dl>
                                        <div class="mt-4">
                                            <label for="npSdPaymentInput" class="mb-1.5 block text-left text-sm font-semibold text-gray-700">Payment amount</label>
                                            <div class="relative">
                                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₹</span>
                                                <input type="number" id="npSdPaymentInput" step="0.01" min="0"
                                                       oninput="npSyncPayButtonState()"
                                                       class="w-full rounded-lg border border-gray-300 py-2.5 pl-8 pr-3 text-sm font-semibold tabular-nums focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                                       placeholder="0.00">
                                            </div>
                                            <p class="mt-1 text-left text-xs text-gray-500">Change the amount to collect for security deposit (max due).</p>
                                        </div>
                                        <div class="mt-4">
                                            <label for="npSdPaymentDate" class="mb-1.5 block text-left text-sm font-semibold text-gray-700">Payment date</label>
                                            <input type="date" id="npSdPaymentDate"
                                                   onchange="npSyncPayButtonState()"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-blue-500">
                                        </div>
                                    </div>

                                    <div id="npPaymentMethodSection" class="mt-8 border-t border-gray-100 pt-6">
                                        <p class="mb-4 text-sm font-semibold text-gray-800">Payment method</p>
                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="np_payment_method" value="card" class="peer sr-only" onchange="npOnPaymentMethodPick()">
                                                <div class="flex min-h-[48px] items-center justify-center rounded-lg border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-semibold text-gray-900 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                                    Card
                                                </div>
                                            </label>
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="np_payment_method" value="cash" class="peer sr-only" onchange="npOnPaymentMethodPick()">
                                                <div class="flex min-h-[48px] items-center justify-center rounded-lg border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-semibold text-gray-900 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                                    Cash
                                                </div>
                                            </label>
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="np_payment_method" value="upi" class="peer sr-only" onchange="npOnPaymentMethodPick()">
                                                <div class="flex min-h-[48px] items-center justify-center rounded-lg border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-semibold text-gray-900 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                                    UPI
                                                </div>
                                            </label>
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="np_payment_method" value="bank_transfer" class="peer sr-only" onchange="npOnPaymentMethodPick()">
                                                <div class="flex min-h-[48px] items-center justify-center rounded-lg border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-semibold text-gray-900 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                                    Bank Transfer
                                                </div>
                                            </label>
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="np_payment_method" value="wallet" class="peer sr-only" onchange="npOnPaymentMethodPick()">
                                                <div class="flex min-h-[48px] items-center justify-center rounded-lg border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-semibold text-gray-900 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                                    Wallet
                                                </div>
                                            </label>
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="np_payment_method" value="other" class="peer sr-only" onchange="npOnPaymentMethodPick()">
                                                <div class="flex min-h-[48px] items-center justify-center rounded-lg border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-semibold text-gray-900 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                                    Other
                                                </div>
                                            </label>
                                        </div>
                                        <div id="npPayWithAmountWrap" class="mt-4 hidden">
                                            <button type="button" id="npPayWithAmountBtn" onclick="npSubmitPaymentIntent()"
                                                    disabled
                                                    class="flex min-h-[52px] w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-base font-bold text-white shadow-md transition hover:bg-emerald-700 enabled:active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">
                                                <i class="fas fa-lock-open"></i>
                                                <span id="npPayWithAmountLabel">Pay</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-end border-t border-gray-200 px-6 py-4">
                                    <button type="button" onclick="backNewPaymentDueModal()"
                                            class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                        Back
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                    function npFormatInr(n) {
                        const x = parseFloat(n) || 0;
                        return '₹' + x.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                    function npParseMoney(el) {
                        if (!el) return 0;
                        return parseFloat(String(el.textContent).replace(/[₹,]/g, '')) || 0;
                    }
                    function npTodayLocal() {
                        const d = new Date();
                        const y = d.getFullYear();
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        return y + '-' + m + '-' + day;
                    }
                    function npSetPaymentDatesToday() {
                        const t = npTodayLocal();
                        const o = document.getElementById('npOrderPaymentDate');
                        const s = document.getElementById('npSdPaymentDate');
                        if (o) o.value = t;
                        if (s) s.value = t;
                    }
                    const npRecordPaymentUrl = @json(route('vendor.orders.payment', $order));
                    const npMethodLabels = { card: 'Card', cash: 'Cash', upi: 'UPI', bank_transfer: 'Bank transfer', wallet: 'Wallet', other: 'Other' };
                    function npGetActivePaymentAmount() {
                        const k = document.getElementById('newPaymentDueModal')?.dataset?.npKind;
                        const inp = k === 'security_deposit'
                            ? document.getElementById('npSdPaymentInput')
                            : document.getElementById('npOrderPaymentInput');
                        return parseFloat(inp?.value || 0) || 0;
                    }
                    function npSyncPayButtonState() {
                        const btn = document.getElementById('npPayWithAmountBtn');
                        const label = document.getElementById('npPayWithAmountLabel');
                        const wrap = document.getElementById('npPayWithAmountWrap');
                        if (!label || !btn) return;
                        const amt = npGetActivePaymentAmount();
                        const method = document.querySelector('#newPaymentDueModal input[name=np_payment_method]:checked');
                        const k = document.getElementById('newPaymentDueModal')?.dataset?.npKind;
                        const dateInp = k === 'security_deposit'
                            ? document.getElementById('npSdPaymentDate')
                            : document.getElementById('npOrderPaymentDate');
                        const dateOk = !!(dateInp && dateInp.value);
                        const canPay = !!(method && amt > 0 && dateOk);
                        const mKey = method ? method.value : '';
                        const mLab = mKey ? (npMethodLabels[mKey] || mKey.replace(/_/g, ' ')) : '';
                        label.textContent = mLab ? ('Pay ' + npFormatInr(amt) + ' · ' + mLab) : ('Pay ' + npFormatInr(amt));
                        if (wrap && !wrap.classList.contains('hidden')) {
                            btn.disabled = !canPay;
                        }
                    }
                    function npOnPaymentMethodPick() {
                        const wrap = document.getElementById('npPayWithAmountWrap');
                        if (wrap) wrap.classList.remove('hidden');
                        npSyncPayButtonState();
                    }
                    function npSubmitPaymentIntent() {
                        const method = document.querySelector('#newPaymentDueModal input[name=np_payment_method]:checked');
                        const amt = npGetActivePaymentAmount();
                        const k = document.getElementById('newPaymentDueModal')?.dataset?.npKind;
                        const dateInp = k === 'security_deposit'
                            ? document.getElementById('npSdPaymentDate')
                            : document.getElementById('npOrderPaymentDate');
                        if (!method) {
                            if (typeof showToast === 'function') showToast('Select a payment method', 'error');
                            return;
                        }
                        if (!amt || amt <= 0) {
                            if (typeof showToast === 'function') showToast('Enter a valid payment amount', 'error');
                            return;
                        }
                        if (dateInp && !dateInp.value) {
                            if (typeof showToast === 'function') showToast('Select payment date', 'error');
                            return;
                        }
                        const btn = document.getElementById('npPayWithAmountBtn');
                        const payLabel = document.getElementById('npPayWithAmountLabel');
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        let prevLabel = '';
                        if (btn) btn.disabled = true;
                        if (payLabel) {
                            prevLabel = payLabel.textContent;
                            payLabel.textContent = 'Processing…';
                        }
                        fetch(npRecordPaymentUrl, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf || ''
                            },
                            body: JSON.stringify({
                                amount: amt,
                                payment_for: k === 'security_deposit' ? 'security_deposit' : 'order_amount',
                                method: method.value,
                                paid_on: dateInp ? dateInp.value : null
                            })
                        })
                        .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, status: r.status, data: data }; }); })
                        .then(function (res) {
                            if (payLabel && prevLabel) payLabel.textContent = prevLabel;
                            if (btn) btn.disabled = false;
                            if (res.ok && res.data && res.data.success && res.data.order) {
                                if (typeof updateSummary === 'function') {
                                    updateSummary(res.data.order);
                                }
                                npResetPaymentMethodUi();
                                refreshNewPaymentDueModalTotals(k);
                                npSyncPayButtonState();
                                closeAddPaymentModal();
                                if (typeof showToast === 'function') showToast(res.data.message || 'Payment saved', 'success');
                            } else {
                                let msg = (res.data && res.data.message) ? res.data.message : 'Payment failed';
                                if (res.data && res.data.errors) {
                                    const first = Object.values(res.data.errors)[0];
                                    if (first && first[0]) msg = first[0];
                                }
                                if (typeof showToast === 'function') showToast(msg, 'error');
                                npSyncPayButtonState();
                            }
                        })
                        .catch(function () {
                            if (payLabel && prevLabel) payLabel.textContent = prevLabel;
                            if (btn) btn.disabled = false;
                            if (typeof showToast === 'function') showToast('Network error', 'error');
                            npSyncPayButtonState();
                        });
                    }
                    function npResetPaymentMethodUi() {
                        document.querySelectorAll('#newPaymentDueModal input[name=np_payment_method]').forEach(function (r) {
                            r.checked = false;
                        });
                        const wrap = document.getElementById('npPayWithAmountWrap');
                        if (wrap) wrap.classList.add('hidden');
                    }
                    function refreshNewPaymentDueModalTotals(kind) {
                        const modal = document.getElementById('newPaymentDueModal');
                        const k = kind || modal?.dataset?.npKind || 'order_amount';

                        const grand = npParseMoney(document.querySelector('[data-grand-total]'));
                        const depTotal = npParseMoney(document.querySelector('[data-security-deposit-total]'));
                        const orderTotal = Math.max(0, grand - depTotal);
                        const paid = npParseMoney(document.querySelector('[data-paid-amount]'));

                        const toOrder = Math.min(paid, orderTotal);
                        const orderRemaining = Math.max(0, orderTotal - toOrder);
                        const afterOrder = Math.max(0, paid - orderTotal);
                        const toDeposit = Math.min(afterOrder, depTotal);
                        const depRemaining = Math.max(0, depTotal - toDeposit);

                        const od = document.getElementById('npOrderDueAmount');
                        const op = document.getElementById('npOrderPaidAmount');
                        const ot = document.getElementById('npOrderTotal');
                        const sd = document.getElementById('npSdDueAmount');
                        const sp = document.getElementById('npSdPaidAmount');
                        const st = document.getElementById('npSdTotal');
                        if (od) od.textContent = npFormatInr(orderRemaining);
                        if (op) op.textContent = npFormatInr(toOrder);
                        if (ot) ot.textContent = npFormatInr(orderTotal);
                        if (sd) sd.textContent = npFormatInr(depRemaining);
                        if (sp) sp.textContent = npFormatInr(toDeposit);
                        if (st) st.textContent = npFormatInr(depTotal);

                        const inpOrder = document.getElementById('npOrderPaymentInput');
                        const inpSd = document.getElementById('npSdPaymentInput');
                        if (k === 'security_deposit') {
                            if (inpSd) {
                                const maxSd = Math.max(0, depRemaining);
                                inpSd.max = maxSd;
                                inpSd.value = maxSd > 0 ? maxSd.toFixed(2) : '';
                            }
                        } else {
                            if (inpOrder) {
                                const maxOr = Math.max(0, orderRemaining);
                                inpOrder.max = maxOr;
                                inpOrder.value = maxOr > 0 ? maxOr.toFixed(2) : '';
                            }
                        }
                        npSetPaymentDatesToday();
                        npSyncPayButtonState();
                    }
                    function openAddPaymentModal() {
                        document.getElementById('newPaymentDueModal').classList.add('hidden');
                        document.getElementById('addPaymentModal').classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                    function closeAddPaymentModal() {
                        document.getElementById('addPaymentModal').classList.add('hidden');
                        document.getElementById('newPaymentDueModal').classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                    const npPaymentCartId = @json($order->id);
                    function applyNewPaymentDueLayout(kind) {
                        const modal = document.getElementById('newPaymentDueModal');
                        const title = document.getElementById('npDueTitle');
                        const sub = document.getElementById('npDueSubtitle');
                        const secOrder = document.getElementById('npSectionOrder');
                        const secSd = document.getElementById('npSectionSecurity');
                        if (modal) modal.dataset.npKind = kind || '';

                        if (title) {
                            if (kind === 'security_deposit') {
                                title.textContent = 'Payment for security deposit #' + npPaymentCartId;
                            } else {
                                title.textContent = 'Payment for order #' + npPaymentCartId;
                            }
                        }

                        if (kind === 'security_deposit') {
                            if (sub) sub.textContent = 'You chose security deposit — details below are for the deposit only.';
                            if (secOrder) {
                                secOrder.classList.add('hidden');
                                secOrder.classList.remove('ring-2', 'ring-blue-100');
                            }
                            if (secSd) {
                                secSd.classList.remove('hidden');
                                secSd.classList.add('ring-2', 'ring-blue-100');
                            }
                        } else {
                            if (sub) sub.textContent = 'You chose order amount — details below are for the rental / order balance only.';
                            if (secSd) {
                                secSd.classList.add('hidden');
                                secSd.classList.remove('ring-2', 'ring-blue-100');
                            }
                            if (secOrder) {
                                secOrder.classList.remove('hidden');
                                secOrder.classList.add('ring-2', 'ring-blue-100');
                            }
                        }
                    }
                    function openNewPaymentDueModal(kind) {
                        document.getElementById('addPaymentModal').classList.add('hidden');
                        document.getElementById('newPaymentDueModal').classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                        npResetPaymentMethodUi();
                        applyNewPaymentDueLayout(kind);
                        refreshNewPaymentDueModalTotals(kind);
                    }
                    function backNewPaymentDueModal() {
                        document.getElementById('newPaymentDueModal').classList.add('hidden');
                        document.getElementById('addPaymentModal').classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }
                    @php
                        $_destroyPaymentSample = route('vendor.orders.payments.destroy', [$order, 0]);
                        $npDestroyPaymentPrefix = substr($_destroyPaymentSample, 0, strrpos($_destroyPaymentSample, '/') + 1);
                    @endphp
                    const npPaymentRemovePrefix = @json($npDestroyPaymentPrefix);
                    const npPayTagOrder = @json(__('vendor.payment_for_order_short'));
                    const npPayTagDeposit = @json(__('vendor.payment_for_deposit_short'));
                    const npPaymentRemoveConfirm = @json(__('vendor.payment_remove_confirm'));
                    function npPaymentRemoveUrl(index) {
                        return npPaymentRemovePrefix + String(parseInt(index, 10));
                    }
                    function npFormatPaymentListDate(isoOrYmd) {
                        if (!isoOrYmd) return '';
                        const d = new Date(isoOrYmd);
                        if (isNaN(d.getTime())) return '';
                        return d.toLocaleDateString('en-IN', { month: 'short', day: 'numeric', year: 'numeric' });
                    }
                    function refreshPaymentListFromOrder(cart) {
                        const list = document.getElementById('payment-history-list');
                        const empty = document.getElementById('payment-history-empty');
                        if (!list) return;
                        const rows = Array.isArray(cart.payment_detail) ? cart.payment_detail : [];
                        if (!rows.length) {
                            list.innerHTML = '';
                            list.classList.add('hidden');
                            if (empty) empty.classList.remove('hidden');
                            return;
                        }
                        if (empty) empty.classList.add('hidden');
                        list.classList.remove('hidden');
                        list.innerHTML = rows.map(function (p, idx) {
                            const amt = parseFloat(p.amount || 0).toFixed(2);
                            const isSd = p.payment_for === 'security_deposit';
                            const badge = isSd
                                ? '<span class="inline-flex rounded-md bg-violet-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-violet-700 ring-1 ring-violet-100">' + npPayTagDeposit + '</span>'
                                : '<span class="inline-flex rounded-md bg-blue-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-blue-700 ring-1 ring-blue-100">' + npPayTagOrder + '</span>';
                            const m = p.method || '';
                            const mLabel = npMethodLabels[m] || (m ? m.charAt(0).toUpperCase() + m.slice(1).replace(/_/g, ' ') : '—');
                            const datePart = p.paid_on
                                ? ('<span class="text-gray-400"> · </span><span>' + npFormatPaymentListDate(p.paid_on) + '</span>')
                                : '';
                            return '<li class="flex items-start gap-2 rounded-xl border border-gray-100 bg-white px-3 py-2.5 shadow-sm ring-1 ring-gray-100/80">'
                                + '<div class="min-w-0 flex-1">'
                                + '<div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">'
                                + '<span class="text-sm font-bold tabular-nums text-gray-900">₹' + amt + '</span>' + badge
                                + '</div>'
                                + '<p class="mt-0.5 text-[11px] text-gray-500"><span class="font-medium text-gray-600">' + mLabel + '</span>' + datePart + '</p>'
                                + '</div>'
                                + '<button type="button" onclick="removeCartPayment(' + idx + ')" class="shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600" title="@json(__('vendor.payment_remove'))"><i class="fas fa-times text-sm"></i></button>'
                                + '</li>';
                        }).join('');
                    }
                    function removeCartPayment(index) {
                        if (!confirm(npPaymentRemoveConfirm)) return;
                        const csrf = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        fetch(npPaymentRemoveUrl(index), {
                            method: 'DELETE',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf || ''
                            }
                        })
                            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                            .then(function (res) {
                                if (res.ok && res.data.success && res.data.order) {
                                    if (typeof updateSummary === 'function') updateSummary(res.data.order);
                                    if (typeof showToast === 'function') showToast(res.data.message || 'Removed', 'success');
                                } else if (typeof showToast === 'function') {
                                    showToast((res.data && res.data.message) ? res.data.message : 'Could not remove', 'error');
                                }
                            })
                            .catch(function () {
                                if (typeof showToast === 'function') showToast('Network error', 'error');
                            });
                    }
                    </script>
                    
                </div>

                <div class="space-y-3 border-t border-gray-100 bg-gray-50/50 p-4 sm:p-5">
                   
                   
                    <div class="space-y-2.5 pt-1">
                        <a href="{{ route('vendor.orders.print', ['order' => $order, 'autoprint' => 1]) }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-800 shadow-sm transition hover:bg-gray-50">
                            <i class="fas fa-file-invoice text-gray-600"></i>{{ __('vendor.print_quote') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div x-show="showAddItem"
         x-cloak
         class="fixed inset-0 z-50 flex flex-col md:items-center md:justify-center md:p-4">
        <div @click="showAddItem = false; searchQuery = ''; selectedCategory = ''"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/60 backdrop-blur-[2px] transition-opacity"></div>

        <div x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-6 md:translate-y-0 md:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 md:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 md:scale-100"
             x-transition:leave-end="opacity-0 translate-y-6 md:scale-95"
             class="relative z-10 flex h-[100dvh] max-h-[100dvh] w-full flex-col overflow-hidden bg-white shadow-2xl ring-1 ring-gray-200/80
                    md:mt-0 md:h-auto md:max-h-[min(92vh,880px)] md:min-h-[360px] md:max-w-5xl md:rounded-2xl
                    rounded-t-3xl md:rounded-2xl mt-auto md:mx-auto">

            <header class="flex-shrink-0 border-b border-gray-200 bg-gradient-to-r from-blue-50 via-white to-indigo-50/80 px-4 pb-4 pt-[max(1rem,env(safe-area-inset-top))] sm:px-6 sm:py-4 md:rounded-t-2xl md:pt-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 pr-2">
                        <h3 id="add-items-modal-title" class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">{{ __('vendor.modal_add_items_title') }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-gray-600">{{ __('vendor.modal_add_items_subtitle') }}</p>
                    </div>
                    <button type="button"
                            @click="showAddItem = false; searchQuery = ''; selectedCategory = ''"
                            class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl text-gray-500 transition-colors hover:bg-white hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                            aria-label="{{ __('vendor.modal_close_aria') }}">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </header>

            <div class="flex min-h-0 flex-1 flex-col">

                <div class="flex-shrink-0 space-y-3 border-b border-gray-100 bg-gray-50/80 px-4 py-4 sm:px-6">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="add-items-search" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.search_items') }}</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input id="add-items-search"
                                       type="search"
                                       autocomplete="off"
                                       x-model="searchQuery"
                                       placeholder="{{ __('vendor.search_placeholder') }}"
                                       class="w-full rounded-xl border border-gray-200 bg-white py-3.5 pl-11 pr-11 text-base text-gray-900 shadow-sm transition-shadow placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 md:py-2.5 md:text-sm">
                                <button type="button"
                                        x-show="searchQuery"
                                        x-cloak
                                        @click="searchQuery = ''"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-700">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg hover:bg-gray-100"><i class="fas fa-times"></i></span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label for="add-items-category" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.filter_by_category') }}</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                                    <i class="fas fa-layer-group text-sm"></i>
                                </span>
                                <select id="add-items-category"
                                        x-model="selectedCategory"
                                        class="w-full appearance-none rounded-xl border border-gray-200 bg-white py-3.5 pl-11 pr-10 text-base text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 md:py-2.5 md:text-sm">
                                    <option value="">{{ __('vendor.all_categories') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <p class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                        <span class="inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-full bg-blue-100 px-2 text-xs font-bold text-blue-800 tabular-nums" x-text="filteredItems.length"></span>
                        <span>{{ __('vendor.items') }}</span>
                    </p>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain bg-white">
                    <div x-show="items.length === 0" class="flex min-h-[12rem] flex-col items-center justify-center px-6 py-12 text-center">
                        <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-amber-50 ring-1 ring-amber-100">
                            <i class="fas fa-box-open text-3xl text-amber-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.modal_no_inventory_title') }}</h3>
                        <p class="mt-2 max-w-sm text-sm leading-relaxed text-gray-600">{{ __('vendor.modal_no_inventory_body') }}</p>
                        <a href="{{ route('vendor.items.create') }}"
                           class="mt-6 inline-flex min-h-[48px] items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fas fa-plus mr-2"></i>{{ __('vendor.modal_create_item_cta') }}
                        </a>
                    </div>

                    <div x-show="items.length > 0 && filteredItems.length === 0" class="flex min-h-[12rem] flex-col items-center justify-center px-6 py-12 text-center">
                        <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-gray-100">
                            <i class="fas fa-search text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.no_items_found') }}</h3>
                        <p class="mt-2 max-w-sm text-sm text-gray-600">{{ __('vendor.adjust_search') }}</p>
                        <button type="button"
                                @click="searchQuery = ''; selectedCategory = ''"
                                class="mt-5 inline-flex min-h-[48px] items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-blue-700 shadow-sm transition hover:bg-gray-50">
                            <i class="fas fa-redo mr-2"></i>{{ __('vendor.clear_filters') }}
                        </button>
                    </div>

                    <div class="hidden md:block" x-show="filteredItems.length > 0">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[640px] text-left">
                                <thead class="sticky top-0 z-10 border-b border-gray-200 bg-gray-50/95 backdrop-blur supports-[backdrop-filter]:bg-gray-50/80">
                                    <tr>
                                        <th class="px-5 py-3.5 text-xs font-bold uppercase tracking-wide text-gray-600">{{ __('vendor.item') }}</th>
                                        <th class="px-5 py-3.5 text-xs font-bold uppercase tracking-wide text-gray-600">{{ __('vendor.price') }}</th>
                                        <th class="px-5 py-3.5 text-center text-xs font-bold uppercase tracking-wide text-gray-600">{{ __('vendor.quantity') }}</th>
                                        <th class="min-w-[10rem] px-5 py-3.5 text-xs font-bold uppercase tracking-wide text-gray-600">{{ __('vendor.billing_units') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <template x-for="item in filteredItems" :key="item.id">
                                        <tr class="transition-colors hover:bg-blue-50/40">
                                            <td class="px-5 py-4 align-middle">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 ring-1 ring-blue-100">
                                                        <i class="fas fa-box text-blue-600"></i>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="truncate font-semibold text-gray-900" x-text="item.name"></p>
                                                        <p class="truncate text-xs text-gray-500" x-text="item.category ? item.category.name : '{{ __('vendor.no_category') }}'"></p>
                                                        <p class="text-xs text-gray-500" x-show="item.manage_stock">
                                                            {{ __('vendor.stock') }}: <span x-text="item.stock"></span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 align-middle">
                                                <span class="inline-flex rounded-lg bg-blue-50 px-2.5 py-1 text-sm font-bold text-blue-800 tabular-nums">₹<span x-text="parseFloat(item.price).toFixed(2)"></span></span>
                                            </td>
                                            <td class="px-5 py-4 align-middle">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <button type="button" @click="addItemToCart(item.id)"
                                                            x-show="!isAdded(item.id)"
                                                            :disabled="addingItem === item.id"
                                                            class="inline-flex min-h-[40px] min-w-[5.5rem] items-center justify-center rounded-xl bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50">
                                                        <span x-show="addingItem !== item.id" class="flex items-center gap-1.5"><i class="fas fa-plus"></i>{{ __('vendor.add') }}</span>
                                                        <span x-show="addingItem === item.id"><i class="fas fa-spinner fa-spin"></i></span>
                                                    </button>
                                                    <div x-show="isAdded(item.id)" class="flex items-center justify-center gap-1.5" x-cloak>
                                                        <button type="button" @click="decrementCartQty(item.id)"
                                                                :disabled="updatingItem === item.id"
                                                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50">
                                                            <i class="fas fa-minus text-sm"></i>
                                                        </button>
                                                        <input type="number" min="1"
                                                               class="h-10 w-16 rounded-xl border border-gray-200 text-center text-sm font-bold text-gray-900 shadow-inner focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                                               :value="getAddedQty(item.id)"
                                                               :disabled="updatingItem === item.id"
                                                               @input="let v = parseInt($event.target.value) || 1; if (v < 1) v = 1; addedItems[item.id] = v;"
                                                               @blur="let v = parseInt($event.target.value) || 1; if (v < 1) v = 1; updatingItem = item.id; updateModalItemQty(item.id, v, $root);"
                                                               @keydown.enter.prevent="let v = parseInt($event.target.value) || 1; if (v < 1) v = 1; updatingItem = item.id; updateModalItemQty(item.id, v, $root); $event.target.blur();">
                                                        <button type="button" @click="incrementCartQty(item.id)"
                                                                :disabled="updatingItem === item.id"
                                                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50">
                                                            <i class="fas fa-plus text-sm"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-5 py-4 align-middle">
                                                <div x-show="!isAdded(item.id)" class="text-center text-sm text-gray-400">—</div>
                                                <div x-show="isAdded(item.id) && !lineUsesBillingUnits(item)" class="text-center text-sm text-gray-400">—</div>
                                                <div x-show="isAdded(item.id) && lineUsesBillingUnits(item)" class="mx-auto max-w-[12rem]">
                                                    <p class="mb-1.5 text-center text-[11px] font-semibold uppercase tracking-wide text-gray-500" x-text="billingUnitsLabelForLine(item)"></p>
                                                    <div class="flex items-center justify-center gap-1.5">
                                                        <button type="button" @click="decrementBillingUnits(item.id, item)"
                                                                :disabled="updatingItem === item.id || getLineBillingUnits(item.id) <= 0.01"
                                                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                                                            <i class="fas fa-minus text-sm"></i>
                                                        </button>
                                                        <input type="number" step="0.01" min="0.01" lang="en"
                                                               class="h-10 w-16 rounded-xl border border-gray-200 text-center text-sm font-bold text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                                               :value="getLineBillingUnits(item.id)"
                                                               :disabled="updatingItem === item.id"
                                                               @input="addedItemBillingUnits = { ...addedItemBillingUnits, [item.id]: parseFloat($event.target.value) || 1 }"
                                                               @blur="onBillingUnitsBlur(item.id, item, $event)">
                                                        <button type="button" @click="incrementBillingUnits(item.id, item)"
                                                                :disabled="updatingItem === item.id"
                                                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                                                            <i class="fas fa-plus text-sm"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="md:hidden" x-show="filteredItems.length > 0">
                        <div class="space-y-3 px-3 py-3 pb-[max(1rem,env(safe-area-inset-bottom))]">
                            <template x-for="item in filteredItems" :key="item.id">
                                <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100">
                                    <div class="p-4">
                                        <div class="flex gap-3">
                                            <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 ring-1 ring-blue-100">
                                                <i class="fas fa-box text-lg text-blue-600"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-2">
                                                    <h4 class="text-base font-bold leading-snug text-gray-900" x-text="item.name"></h4>
                                                    <span class="flex-shrink-0 rounded-lg bg-blue-50 px-2.5 py-1 text-sm font-bold tabular-nums text-blue-800">₹<span x-text="parseFloat(item.price).toFixed(2)"></span></span>
                                                </div>
                                                <p class="mt-1 text-sm text-gray-500" x-text="item.category ? item.category.name : '{{ __('vendor.no_category') }}'"></p>
                                                <p class="mt-1 text-xs text-gray-500" x-show="item.manage_stock">
                                                    {{ __('vendor.stock') }}: <span x-text="item.stock"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border-t border-gray-100 bg-gray-50/90 px-4 py-4">
                                        <button type="button" @click="addItemToCart(item.id)"
                                                x-show="!isAdded(item.id)"
                                                :disabled="addingItem === item.id"
                                                class="flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-3.5 text-base font-semibold text-white shadow-md transition hover:bg-blue-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">
                                            <span x-show="addingItem !== item.id"><i class="fas fa-plus"></i>{{ __('vendor.add_to_cart') }}</span>
                                            <span x-show="addingItem === item.id"><i class="fas fa-spinner fa-spin"></i></span>
                                        </button>
                                        <div x-show="isAdded(item.id)" class="space-y-4" x-cloak>
                                            <div>
                                                <p class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-500">{{ __('vendor.quantity') }}</p>
                                                <div class="flex items-center justify-center gap-3">
                                                    <button type="button" @click="decrementCartQty(item.id)"
                                                            :disabled="updatingItem === item.id"
                                                            class="flex h-12 w-12 items-center justify-center rounded-xl border-2 border-gray-200 bg-white text-lg text-gray-800 shadow-sm active:scale-95 disabled:opacity-50">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" min="1"
                                                           class="h-12 w-20 rounded-xl border-2 border-gray-200 bg-white text-center text-lg font-bold text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"
                                                           :value="getAddedQty(item.id)"
                                                           :disabled="updatingItem === item.id"
                                                           @input="let v = parseInt($event.target.value) || 1; if (v < 1) v = 1; addedItems[item.id] = v;"
                                                           @blur="let v = parseInt($event.target.value) || 1; if (v < 1) v = 1; updatingItem = item.id; updateModalItemQty(item.id, v, $root);"
                                                           @keydown.enter.prevent="let v = parseInt($event.target.value) || 1; if (v < 1) v = 1; updatingItem = item.id; updateModalItemQty(item.id, v, $root); $event.target.blur();">
                                                    <button type="button" @click="incrementCartQty(item.id)"
                                                            :disabled="updatingItem === item.id"
                                                            class="flex h-12 w-12 items-center justify-center rounded-xl border-2 border-gray-200 bg-white text-lg text-gray-800 shadow-sm active:scale-95 disabled:opacity-50">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div x-show="lineUsesBillingUnits(item)">
                                                <p class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-500" x-text="billingUnitsLabelForLine(item)"></p>
                                                <div class="flex items-center justify-center gap-3">
                                                    <button type="button" @click="decrementBillingUnits(item.id, item)"
                                                            :disabled="updatingItem === item.id || getLineBillingUnits(item.id) <= 0.01"
                                                            class="flex h-12 w-12 items-center justify-center rounded-xl border-2 border-gray-200 bg-white text-lg text-gray-800 shadow-sm active:scale-95 disabled:opacity-50">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" step="0.01" min="0.01" lang="en"
                                                           class="h-12 w-20 rounded-xl border-2 border-gray-200 bg-white text-center text-lg font-bold text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/25"
                                                           :value="getLineBillingUnits(item.id)"
                                                           :disabled="updatingItem === item.id"
                                                           @input="addedItemBillingUnits = { ...addedItemBillingUnits, [item.id]: parseFloat($event.target.value) || 1 }"
                                                           @blur="onBillingUnitsBlur(item.id, item, $event)">
                                                    <button type="button" @click="incrementBillingUnits(item.id, item)"
                                                            :disabled="updatingItem === item.id"
                                                            class="flex h-12 w-12 items-center justify-center rounded-xl border-2 border-gray-200 bg-white text-lg text-gray-800 shadow-sm active:scale-95 disabled:opacity-50">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            </template>
                        </div>
                    </div>
                </div>

                @if($errors->any())
                    <div class="flex-shrink-0 border-t border-red-100 bg-red-50 px-4 py-3 sm:px-6">
                        <p class="text-sm text-red-800">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $errors->first() }}
                        </p>
                    </div>
                @endif

                <footer class="flex flex-shrink-0 flex-col gap-2 border-t border-gray-200 bg-white/95 px-4 py-3 backdrop-blur supports-[backdrop-filter]:bg-white/80 sm:flex-row sm:items-center sm:justify-end sm:px-6 sm:py-4"
                        style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
                    <button type="button"
                            @click="showAddItem = false; searchQuery = ''; selectedCategory = ''"
                            class="inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl border border-gray-200 bg-gray-50 py-3.5 text-base font-semibold text-gray-800 shadow-sm transition hover:bg-gray-100 sm:w-auto sm:px-8 sm:py-3 sm:text-sm">
                        <i class="fas fa-check text-blue-600"></i>{{ __('vendor.modal_done') }}
                    </button>
                </footer>
            </div>
        </div>
    </div>
</div>

<!-- Discount Modal -->
<div id="discountModal" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeDiscountModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center bg-blue-600 rounded-lg">
                            <i class="fas fa-percent text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.add') }} {{ __('vendor.discount') }}</h3>
                            <p class="text-xs text-gray-600">Apply discount to this cart</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeDiscountModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <form id="discountForm" onsubmit="submitDiscount(event)" class="p-6 space-y-5">
                <!-- Discount Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Discount Type</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative cursor-pointer">
                            <input type="radio" name="discount_type" value="fixed" checked class="peer sr-only" id="discount_type_fixed">
                            <div class="flex items-center justify-center px-4 py-3 border-2 border-gray-200 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all">
                                <div class="text-center">
                                    <i class="fas fa-rupee-sign text-lg text-gray-500 peer-checked:text-blue-600 mb-1"></i>
                                    <p class="text-sm font-semibold text-gray-700">Fixed Amount</p>
                                    <p class="text-xs text-gray-500">₹ value</p>
                                </div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="discount_type" value="percent" class="peer sr-only" id="discount_type_percent">
                            <div class="flex items-center justify-center px-4 py-3 border-2 border-gray-200 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all">
                                <div class="text-center">
                                    <i class="fas fa-percent text-lg text-gray-500 peer-checked:text-blue-600 mb-1"></i>
                                    <p class="text-sm font-semibold text-gray-700">Percentage</p>
                                    <p class="text-xs text-gray-500">% of subtotal</p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Discount Value -->
                <div>
                    <label for="discount_value" class="block text-sm font-semibold text-gray-700 mb-2">
                        Discount Value <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span id="discountSymbol" class="text-gray-500 font-medium">₹</span>
                        </div>
                        <input type="number" name="discount_value" id="discount_value"
                               step="0.01" min="0" required
                               class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter discount value">
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">
                        Subtotal: <span class="font-semibold">₹{{ number_format($order->sub_total, 2) }}</span>
                    </p>
                    <p id="discountError" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeDiscountModal()"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit" id="discountSubmitBtn"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 shadow-sm">
                        <i class="fas fa-check mr-2"></i>Apply Discount
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Security Deposit Modal -->
<div id="securityDepositModal" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeSecurityDepositModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white shadow-2xl" onclick="event.stopPropagation()">
            <div class="rounded-t-xl border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600">
                            <i class="fas fa-shield-alt text-white"></i>
                        </div>
                        <div>
                            <h3 id="securityDepositModalTitle" class="text-lg font-bold text-gray-900">Security Deposit</h3>
                            <p id="securityDepositModalSubtitle" class="text-xs text-gray-600">Choose how to charge security deposit</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeSecurityDepositModal()" class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-white hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <form id="securityDepositForm" onsubmit="submitSecurityDeposit(event)" class="space-y-5 p-6">
                <div class="space-y-3">
                    <label class="block text-sm font-semibold text-gray-700">Deposit Rule</label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50/50">
                        <input type="radio" name="security_deposit_type" value="none" class="mt-1 h-4 w-4 text-blue-600" @checked(($order->security_deposit_type ?? 'none') === 'none')>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">None</p>
                            <p class="text-xs text-gray-600">Do not charge a security deposit by default.</p>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50/50">
                        <input type="radio" name="security_deposit_type" value="order_amount" class="mt-1 h-4 w-4 text-blue-600" @checked(($order->security_deposit_type ?? 'none') === 'order_amount')>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Order amount</p>
                            <p class="text-xs text-gray-600">Add a percentage of the total order amount.</p>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50/50">
                        <input type="radio" name="security_deposit_type" value="product_security_deposit" class="mt-1 h-4 w-4 text-blue-600" @checked(($order->security_deposit_type ?? 'none') === 'product_security_deposit')>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Product security deposit value</p>
                            <p class="text-xs text-gray-600">Charge a percentage of the security deposit value of all products on an order.</p>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50/50">
                        <input type="radio" name="security_deposit_type" value="fixed_amount" class="mt-1 h-4 w-4 text-blue-600" @checked(($order->security_deposit_type ?? 'none') === 'fixed_amount')>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Fixed amount</p>
                            <p class="text-xs text-gray-600">Charge a fixed amount, regardless of the products on an order.</p>
                        </div>
                    </label>
                </div>

                <div id="securityDepositValueWrap" class="hidden">
                    <label for="security_deposit_value" id="securityDepositValueLabel" class="mb-2 block text-sm font-semibold text-gray-700">Value</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <span id="securityDepositValueSymbol" class="font-medium text-gray-500">%</span>
                        </div>
                        <input type="number"
                               id="security_deposit_value"
                               step="0.01"
                               min="0"
                               class="w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-4 text-sm focus:border-transparent focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter value">
                    </div>
                    <p id="securityDepositValueHelp" class="mt-1.5 text-xs text-gray-500"></p>
                </div>

                <p id="securityDepositError" class="hidden text-sm text-red-600"></p>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                    <button type="button" onclick="closeSecurityDepositModal()" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 active:scale-95">
                        <i class="fas fa-check mr-2"></i>Apply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Coupon Modal -->
<div id="couponModal" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeCouponModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all max-h-[85vh] flex flex-col" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="flex-shrink-0 px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-green-100 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center bg-emerald-600 rounded-lg">
                            <i class="fas fa-ticket-alt text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Apply Coupon</h3>
                            <p class="text-xs text-gray-600">Enter code or select from list</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeCouponModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Coupon Code Input -->
            <div class="flex-shrink-0 px-6 pt-5 pb-3">
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-tag text-gray-400"></i>
                        </div>
                        <input type="text" id="coupon_code_input"
                               placeholder="Enter coupon code"
                               class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent uppercase"
                               maxlength="50">
                    </div>
                    <button type="button" onclick="applyCouponFromModal()" id="applyCouponBtn"
                            class="px-4 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-95 whitespace-nowrap">
                        Apply
                    </button>
                </div>
                <p id="couponError" class="mt-1.5 text-xs text-red-600 hidden"></p>
            </div>

            <!-- Divider -->
            <div class="flex-shrink-0 px-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center text-xs">
                        <span class="px-3 bg-white text-gray-500">or select a coupon</span>
                    </div>
                </div>
            </div>

            <!-- Coupon List -->
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-3" id="couponList">
                <div class="flex items-center justify-center py-8">
                    <i class="fas fa-spinner fa-spin text-gray-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Cart Modal -->
<div id="editCartModal" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeEditCartModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-lg w-full transform transition-all" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center bg-blue-600 rounded-lg">
                            <i class="fas fa-shopping-cart text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.edit') }} {{ __('vendor.booking_dates') }}</h3>
                            <p class="text-xs text-gray-600">{{ __('vendor.order_booking_modal_help') }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeEditCartModal()" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-white rounded-lg transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form id="editCartForm" onsubmit="submitEditCart(event)" class="p-6 space-y-5">
                <!-- Customer (Read-only) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('vendor.customer') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" 
                               value="{{ $order->customer->name }} - {{ $order->customer->mobile }}"
                               class="w-full pl-11 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                               disabled readonly>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500"><i class="fas fa-info-circle mr-1"></i>{{ __('vendor.customer_cannot_change') }}</p>
                </div>

                <!-- Booking Dates -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar text-emerald-600 mr-1"></i>
                        {{ __('vendor.booking_dates') }} <span class="text-gray-500 text-xs">({{ __('vendor.optional') }})</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="edit_start_at" class="block text-sm font-semibold text-gray-700 mb-1">
                                <i class="far fa-calendar-alt text-emerald-600 mr-1"></i>{{ __('vendor.start_date_time') }}
                            </label>
                            <div class="date-input-wrapper">
                                <input type="text" name="start_at" id="edit_start_at"
                                       value="{{ $order->start_at ? $order->start_at->format('Y-m-d H:i') : '' }}"
                                       readonly
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white cursor-pointer"
                                       placeholder="Select start date">
                                <span class="date-clear-btn" onclick="clearEditDate('start')" title="Clear">
                                    <i class="fas fa-times"></i>
                                </span>
                                <span class="date-icon"><i class="fas fa-chevron-down"></i></span>
                            </div>
                            <p id="editStartTimeError" class="mt-1 text-sm text-red-600 hidden"></p>
                        </div>
                        <div>
                            <label for="edit_end_at" class="block text-sm font-semibold text-gray-700 mb-1">
                                <i class="far fa-calendar-alt text-emerald-600 mr-1"></i>{{ __('vendor.end_date_time') }}
                            </label>
                            <div class="date-input-wrapper">
                                <input type="text" name="end_at" id="edit_end_at"
                                       value="{{ $order->end_at ? $order->end_at->format('Y-m-d H:i') : '' }}"
                                       readonly
                                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white cursor-pointer"
                                       placeholder="Select end date">
                                <span class="date-clear-btn" onclick="clearEditDate('end')" title="Clear">
                                    <i class="fas fa-times"></i>
                                </span>
                                <span class="date-icon"><i class="fas fa-chevron-down"></i></span>
                            </div>
                            <p id="editEndTimeError" class="mt-1 text-sm text-red-600 hidden"></p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeEditCartModal()"
                            class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit" id="editCartSubmitBtn"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-all active:scale-95 shadow-sm">
                        <i class="fas fa-save mr-2"></i>{{ __('vendor.update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" id="deleteModalOverlay"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all" id="deleteModalPanel">
            <div class="p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center bg-red-100 rounded-full">
                    <i class="fas fa-trash-alt text-2xl text-red-600"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('vendor.confirm_delete') }}</h3>
                <p class="text-sm text-gray-600 mb-1">Are you sure you want to remove this item from the cart?</p>
                <p class="text-xs text-gray-500">This action cannot be undone.</p>
            </div>
            <div class="flex items-center justify-center gap-3 px-6 pb-6">
                <button type="button" id="deleteModalCancel"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-times mr-2"></i>{{ __('vendor.cancel') }}
                </button>
                <button type="button" id="deleteModalConfirm"
                        class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                    <i class="fas fa-trash mr-2"></i>{{ __('vendor.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
<style>
    [x-cloak] { display: none !important; }
    .flatpickr-calendar { border-radius: 12px !important; box-shadow: 0 10px 40px rgba(0,0,0,.15) !important; border: 1px solid #e5e7eb !important; font-family: 'Inter', sans-serif !important; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #059669 !important; border-color: #059669 !important; }
    .flatpickr-day.today { border-color: #059669 !important; }
    .flatpickr-day:hover { background: #d1fae5 !important; }
    .flatpickr-months .flatpickr-month { height: 40px !important; }
    .flatpickr-current-month { font-size: 1rem !important; font-weight: 600 !important; }
    .flatpickr-time input { font-size: 1rem !important; }
    .date-input-wrapper { position: relative; }
    .date-input-wrapper .date-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 14px; }
    .date-input-wrapper input { padding-right: 32px; }
    .date-clear-btn { position: absolute; right: 28px; top: 50%; transform: translateY(-50%); color: #9ca3af; cursor: pointer; font-size: 12px; padding: 2px 4px; display: none; }
    .date-clear-btn:hover { color: #ef4444; }
    .date-input-wrapper input:not([value=""]) ~ .date-clear-btn,
    .date-input-wrapper input.has-value ~ .date-clear-btn { display: block; }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
let pendingDelete = null;

// --- Add Item to Cart (AJAX) ---
function addItemToCartAjax(itemId, qty, component) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const item = component.items.find(i => i.id === itemId);
    const priceType = item?.price_type ?? 'per_day';
    const billingUnits = priceType === 'fixed' ? 1 : Math.max(parseFloat(component.addedItemBillingUnits[itemId]) || 1, 0.01);

    fetch('{{ route("vendor.orders.items.add", $order->id) }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            items: [{ item_id: itemId, quantity: qty, billing_units: billingUnits }]
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            component.addedItems = { ...component.addedItems, [itemId]: qty };
            component.addedItemBillingUnits = { ...component.addedItemBillingUnits, [itemId]: billingUnits };
            updateSummary(data.order);
            refreshOrderItems();
            showToast(data.message || 'Item added to cart', 'success');
        } else {
            showToast(data.message || 'Error adding item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error adding item', 'error');
    })
    .finally(() => {
        component.addingItem = null;
    });
}

// --- Update item qty from modal ---
function updateModalItemQty(itemId, newQty, component) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const item = component.items.find(i => i.id === itemId);
    const priceType = item?.price_type ?? 'per_day';
    let billingUnits = 1;
    if (priceType !== 'fixed') {
        billingUnits = Math.max(parseFloat(component.addedItemBillingUnits[itemId]) || 1, 0.01);
    }

    fetch(`{{ url('vendor/orders') }}/{{ $order->id }}/items/${itemId}`, {
        method: 'PUT',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ quantity: newQty, billing_units: billingUnits })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.item && data.item.billing_units != null) {
                component.addedItemBillingUnits = { ...component.addedItemBillingUnits, [itemId]: parseFloat(data.item.billing_units) };
            }
            updateSummary(data.order);
            refreshOrderItems();
        } else {
            showToast(data.message || 'Error updating quantity', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating quantity', 'error');
    })
    .finally(() => {
        component.updatingItem = null;
    });
}

// --- Remove item from modal (when qty goes to 0) ---
function removeModalItem(itemId, component) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`{{ url('vendor/orders') }}/{{ $order->id }}/items/${itemId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.dispatchEvent(new CustomEvent('order-item-removed', { detail: { itemId } }));
            updateSummary(data.order);
            refreshOrderItems();
            showToast(data.message || 'Item removed', 'success');
        } else {
            component.addedItems = { ...component.addedItems, [itemId]: 1 };
            showToast(data.message || 'Error removing item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        component.addedItems = { ...component.addedItems, [itemId]: 1 };
        showToast('Error removing item', 'error');
    })
    .finally(() => {
        component.updatingItem = null;
    });
}

// --- Refresh cart items list via page reload ---
function refreshOrderItems() {
    fetch(window.location.href, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newList = doc.querySelector('[data-items-list]');
        const currentList = document.querySelector('[data-items-list]');
        if (newList && currentList) currentList.innerHTML = newList.innerHTML;
        const newCount = doc.querySelector('[data-items-count]');
        const currentCount = document.querySelector('[data-items-count]');
        if (newCount && currentCount) currentCount.innerHTML = newCount.innerHTML;
    })
    .catch(() => {});
}

// --- Flatpickr for Edit Cart Modal ---
let editStartPicker, editEndPicker;

function initEditDatePickers() {
    function toggleClearBtn(instance) {
        const wrapper = instance.element.closest('.date-input-wrapper');
        if (!wrapper) return;
        const clearBtn = wrapper.querySelector('.date-clear-btn');
        if (clearBtn) {
            clearBtn.style.display = instance.selectedDates.length > 0 ? 'block' : 'none';
        }
    }

    const fpConfig = {
        enableTime: true,
        dateFormat: 'Y-m-d H:i',
        altInput: true,
        altFormat: 'M j, Y h:i K',
        time_24hr: false,
        allowInput: false,
        disableMobile: false,
        monthSelectorType: 'dropdown',
        animate: true,
        onReady: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
        },
        onChange: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
        }
    };

    if (editStartPicker) editStartPicker.destroy();
    if (editEndPicker) editEndPicker.destroy();

    editStartPicker = flatpickr('#edit_start_at', {
        ...fpConfig,
        onChange: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
            if (selectedDates.length > 0) {
                editEndPicker.set('minDate', selectedDates[0]);
            } else {
                editEndPicker.set('minDate', null);
            }
        }
    });

    editEndPicker = flatpickr('#edit_end_at', {
        ...fpConfig,
        onChange: function(selectedDates, dateStr, instance) {
            toggleClearBtn(instance);
            if (selectedDates.length > 0) {
                editStartPicker.set('maxDate', selectedDates[0]);
            } else {
                editStartPicker.set('maxDate', null);
            }
        }
    });

    // Set initial constraints
    if (editStartPicker.selectedDates.length > 0) {
        editEndPicker.set('minDate', editStartPicker.selectedDates[0]);
    }
    if (editEndPicker.selectedDates.length > 0) {
        editStartPicker.set('maxDate', editEndPicker.selectedDates[0]);
    }
}

window.clearEditDate = function(which) {
    if (which === 'start') {
        editStartPicker.clear();
        editEndPicker.set('minDate', null);
    } else {
        editEndPicker.clear();
        editStartPicker.set('maxDate', null);
    }
};

document.addEventListener('DOMContentLoaded', function() {
    initEditDatePickers();
});

// --- Discount Modal ---
function openDiscountModal() {
    document.getElementById('discountModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('discountError').classList.add('hidden');
    document.getElementById('discount_value').value = '';
    document.getElementById('discount_type_fixed').checked = true;
    updateDiscountSymbol();
}

function closeDiscountModal() {
    document.getElementById('discountModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function updateDiscountSymbol() {
    const type = document.querySelector('input[name="discount_type"]:checked').value;
    document.getElementById('discountSymbol').textContent = type === 'percent' ? '%' : '₹';
}

// Listen for discount type changes
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="discount_type"]').forEach(radio => {
        radio.addEventListener('change', updateDiscountSymbol);
    });
});

function submitDiscount(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('discountSubmitBtn');
    const errorEl = document.getElementById('discountError');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    errorEl.classList.add('hidden');

    const discountType = document.querySelector('input[name="discount_type"]:checked').value;
    const discountValue = parseFloat(document.getElementById('discount_value').value);

    if (!discountValue || discountValue <= 0) {
        errorEl.textContent = 'Please enter a valid discount value';
        errorEl.classList.remove('hidden');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Applying...';

    fetch(`{{ route('vendor.orders.discount', $order->id) }}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            discount_type: discountType,
            discount_value: discountValue
        })
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
        if (status === 422) {
            if (data.errors) {
                const firstError = Object.values(data.errors)[0][0];
                errorEl.textContent = firstError;
            } else {
                errorEl.textContent = data.message || 'Validation error';
            }
            errorEl.classList.remove('hidden');
            return;
        }

        if (data.success) {
            // Update discount label
            const label = discountType === 'percent'
                ? 'Discount ' + parseFloat(discountValue) + '%'
                : 'Discount ₹' + parseFloat(discountValue).toFixed(2);
            document.getElementById('discount-label').textContent = label;

            // Show applied, hide add
            document.getElementById('discount-applied').classList.remove('hidden');
            document.getElementById('discount-add').classList.add('hidden');

            updateSummary(data.order);
            closeDiscountModal();
            showToast(data.message, 'success');
        } else {
            errorEl.textContent = data.message || 'Error applying discount';
            errorEl.classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorEl.textContent = 'Error applying discount';
        errorEl.classList.remove('hidden');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Apply Discount';
    });
}

// --- Remove Discount ---
function removeDiscount() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`{{ route('vendor.orders.discount.remove', $order->id) }}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show add, hide applied
            document.getElementById('discount-add').classList.remove('hidden');
            document.getElementById('discount-applied').classList.add('hidden');

            updateSummary(data.order);
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error removing discount', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error removing discount', 'error');
    });
}

// --- Security Deposit Modal ---
const securityDepositState = {
    type: @json($order->security_deposit_type ?? 'none'),
    value: parseFloat(@json($order->security_deposit_value ?? 0)) || 0,
    amount: parseFloat(@json((float) ($order->security_deposit ?? 0))),
    /** Order total from server (sub + tax − discounts), excludes security deposit */
    orderGrandTotal: parseFloat(@json((float) $order->grand_total)),
    subTotal: parseFloat(@json((float) $order->sub_total)),
};

function openSecurityDepositModal() {
    document.getElementById('securityDepositModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('securityDepositError').classList.add('hidden');

    const radio = document.querySelector(`input[name="security_deposit_type"][value="${securityDepositState.type}"]`)
        || document.querySelector('input[name="security_deposit_type"][value="none"]');
    if (radio) radio.checked = true;
    document.getElementById('security_deposit_value').value = securityDepositState.value > 0 ? securityDepositState.value : '';
    updateSecurityDepositValueInput();
    updateSecurityDepositModalCopy();
}

function updateSecurityDepositModalCopy() {
    const subtitle = document.getElementById('securityDepositModalSubtitle');
    const title = document.getElementById('securityDepositModalTitle');
    if (!subtitle || !title) return;

    const t = document.querySelector('input[name="security_deposit_type"]:checked')?.value
        || securityDepositState.type
        || 'none';

    const copy = {
        none: ['Security Deposit', 'Choose how to charge security deposit — currently no deposit.'],
        order_amount: ['Security deposit (order %)', 'Charge a percentage of the order total (after tax and discounts).'],
        product_security_deposit: ['Security deposit (product %)', 'Charge a percentage of the order subtotal (product value).'],
        fixed_amount: ['Security deposit (fixed)', 'Charge a fixed amount regardless of order size.'],
    };
    const pair = copy[t] || copy.none;
    title.textContent = pair[0];
    subtitle.textContent = pair[1];
}

function closeSecurityDepositModal() {
    document.getElementById('securityDepositModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function updateSecurityDepositValueInput() {
    const selectedType = document.querySelector('input[name="security_deposit_type"]:checked')?.value || 'none';
    const wrap = document.getElementById('securityDepositValueWrap');
    const symbol = document.getElementById('securityDepositValueSymbol');
    const label = document.getElementById('securityDepositValueLabel');
    const help = document.getElementById('securityDepositValueHelp');
    const input = document.getElementById('security_deposit_value');

    if (selectedType === 'none') {
        wrap.classList.add('hidden');
        input.required = false;
        return;
    }

    wrap.classList.remove('hidden');
    input.required = true;

    if (selectedType === 'fixed_amount') {
        symbol.textContent = '₹';
        label.textContent = 'Fixed Amount';
        help.textContent = 'Enter exact amount to charge as security deposit.';
        input.placeholder = 'Enter fixed amount';
    } else if (selectedType === 'order_amount') {
        symbol.textContent = '%';
        label.textContent = 'Order Amount Percentage';
        help.textContent = 'Applied on the current order total.';
        input.placeholder = 'Enter percentage';
    } else {
        symbol.textContent = '%';
        label.textContent = 'Product Deposit Percentage';
        help.textContent = 'Applied on the total product value of this order.';
        input.placeholder = 'Enter percentage';
    }
}

function calculateSecurityDepositAmount(cartTotals) {
    const type = securityDepositState.type;
    const value = parseFloat(securityDepositState.value || 0);
    if (type === 'none' || value <= 0) return 0;

    if (type === 'fixed_amount') {
        return value;
    }

    if (type === 'order_amount') {
        const base = parseFloat(cartTotals.grand_total || 0);
        return (base * value) / 100;
    }

    const base = parseFloat(cartTotals.sub_total || 0);
    return (base * value) / 100;
}

function applySecurityDepositDisplay(cartTotals) {
    const securityDepositEl = document.querySelector('[data-security-deposit-total]');
    const securityDepositLabel = document.getElementById('securityDepositLabel');
    const grandTotalEl = document.querySelector('[data-grand-total]');
    const orderTotalEl = document.querySelector('[data-order-total]');
    const balanceDueEl = document.querySelector('[data-balance-due]');
    if (!securityDepositEl || !securityDepositLabel) return;

    const orderTotal = parseFloat(cartTotals.grand_total ?? securityDepositState.orderGrandTotal ?? 0);
    if (orderTotalEl) {
        orderTotalEl.textContent = '₹' + orderTotal.toFixed(2);
    }
    const amount = (cartTotals.security_deposit !== undefined && cartTotals.security_deposit !== null)
        ? parseFloat(cartTotals.security_deposit)
        : calculateSecurityDepositAmount(cartTotals);
    securityDepositState.amount = amount;
    securityDepositEl.textContent = '₹' + amount.toFixed(2);

    const totalWithDeposit = orderTotal + amount;
    if (grandTotalEl) {
        grandTotalEl.textContent = '₹' + totalWithDeposit.toFixed(2);
    }
    if (balanceDueEl) {
        const paidAmount = parseFloat(document.querySelector('[data-paid-amount]')?.textContent?.replace(/[₹,]/g, '') || 0);
        balanceDueEl.textContent = '₹' + (totalWithDeposit - paidAmount).toFixed(2);
    }

    const typeLabelMap = {
        none: 'Security Deposit',
        order_amount: 'Security Deposit (Order %)',
        product_security_deposit: 'Security Deposit (Product %)',
        fixed_amount: 'Security Deposit (Fixed)',
    };
    securityDepositLabel.textContent = typeLabelMap[securityDepositState.type] || 'Security Deposit';
}

function submitSecurityDeposit(e) {
    e.preventDefault();

    const errorEl = document.getElementById('securityDepositError');
    errorEl.classList.add('hidden');

    const selectedType = document.querySelector('input[name="security_deposit_type"]:checked')?.value || 'none';
    const valueInput = document.getElementById('security_deposit_value').value;
    const numericValue = parseFloat(valueInput || 0);

    if (selectedType !== 'none') {
        if (!numericValue || numericValue <= 0) {
            errorEl.textContent = 'Please enter a valid value.';
            errorEl.classList.remove('hidden');
            return;
        }

        if ((selectedType === 'order_amount' || selectedType === 'product_security_deposit') && numericValue > 100) {
            errorEl.textContent = 'Percentage cannot be more than 100.';
            errorEl.classList.remove('hidden');
            return;
        }
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Apply';
    }

    fetch(`{{ route('vendor.orders.security-deposit', $order->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            security_deposit_type: selectedType,
            security_deposit_value: selectedType === 'none' ? null : numericValue,
        }),
    })
        .then(async (r) => {
            const data = await r.json().catch(() => ({}));
            return { ok: r.ok, data };
        })
        .then(({ ok, data }) => {
            if (ok && data.success && data.order) {
                securityDepositState.type = data.order.security_deposit_type || selectedType;
                securityDepositState.value = parseFloat(data.order.security_deposit_value ?? 0) || 0;
                updateSummary(data.order);
                closeSecurityDepositModal();
                showToast(data.message || 'Security deposit updated', 'success');
            } else {
                errorEl.textContent = data.message || 'Could not update security deposit.';
                errorEl.classList.remove('hidden');
            }
        })
        .catch(() => {
            errorEl.textContent = 'Could not update security deposit.';
            errorEl.classList.remove('hidden');
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Apply';
            }
        });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="security_deposit_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            updateSecurityDepositValueInput();
            updateSecurityDepositModalCopy();
        });
    });
    applySecurityDepositDisplay({
        sub_total: securityDepositState.subTotal,
        grand_total: securityDepositState.orderGrandTotal,
    });
});

// --- Coupon Modal ---
function openCouponModal() {
    document.getElementById('couponModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('couponError').classList.add('hidden');
    document.getElementById('coupon_code_input').value = '';
    loadCouponList();
}

function closeCouponModal() {
    document.getElementById('couponModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function loadCouponList() {
    const listEl = document.getElementById('couponList');
    listEl.innerHTML = '<div class="flex items-center justify-center py-8"><i class="fas fa-spinner fa-spin text-gray-400 text-xl"></i></div>';

    fetch(`{{ route('vendor.orders.coupons.list', $order->id) }}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success || !data.coupons.length) {
            listEl.innerHTML = '<div class="text-center py-8 text-sm text-gray-500"><i class="fas fa-ticket-alt text-2xl mb-2 block text-gray-300"></i>No coupons available</div>';
            return;
        }

        listEl.innerHTML = data.coupons.map(coupon => {
            const disabled = !coupon.is_applicable;
            const typeLabel = coupon.type === 'percent'
                ? parseFloat(coupon.value) + '% OFF'
                : '₹' + parseFloat(coupon.value).toFixed(0) + ' OFF';
            const minOrder = coupon.min_order_amount > 0 ? 'Min order ₹' + parseFloat(coupon.min_order_amount).toFixed(0) : '';
            const maxDiscount = coupon.max_discount_amount > 0 ? 'Max ₹' + parseFloat(coupon.max_discount_amount).toFixed(0) : '';
            const expiry = coupon.end_date ? 'Expires ' + coupon.end_date : '';
            const details = [minOrder, maxDiscount, expiry].filter(Boolean).join(' · ');

            return `
                <div class="border ${disabled ? 'border-gray-200 opacity-60' : 'border-emerald-200 hover:border-emerald-400 cursor-pointer'} rounded-lg p-3 transition-all ${disabled ? '' : 'hover:shadow-sm'}"
                     ${disabled ? '' : `onclick="selectCoupon('${coupon.code}')"`}>
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold ${disabled ? 'bg-gray-100 text-gray-500' : 'bg-emerald-100 text-emerald-800'} border ${disabled ? 'border-gray-200' : 'border-emerald-200'} tracking-wider">${coupon.code}</span>
                                <span class="text-xs font-semibold ${disabled ? 'text-gray-400' : 'text-blue-600'}">${typeLabel}</span>
                            </div>
                            <p class="text-sm text-gray-700 mt-1 truncate">${coupon.name}</p>
                            ${details ? `<p class="text-xs text-gray-400 mt-0.5">${details}</p>` : ''}
                        </div>
                        ${disabled
                            ? '<span class="text-xs text-gray-400 flex-shrink-0 ml-2">Not applicable</span>'
                            : '<button class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex-shrink-0 ml-2">Apply</button>'}
                    </div>
                </div>
            `;
        }).join('');
    })
    .catch(error => {
        console.error('Error:', error);
        listEl.innerHTML = '<div class="text-center py-8 text-sm text-red-500">Error loading coupons</div>';
    });
}

function selectCoupon(code) {
    document.getElementById('coupon_code_input').value = code;
    applyCouponFromModal();
}

function applyCouponFromModal() {
    const codeInput = document.getElementById('coupon_code_input');
    const code = codeInput.value.trim();
    const errorEl = document.getElementById('couponError');
    const btn = document.getElementById('applyCouponBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    errorEl.classList.add('hidden');

    if (!code) {
        errorEl.textContent = 'Please enter a coupon code';
        errorEl.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`{{ route('vendor.orders.coupon.apply', $order->id) }}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ coupon_code: code })
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
        if (!data.success) {
            errorEl.textContent = data.message || 'Invalid coupon';
            errorEl.classList.remove('hidden');
            return;
        }

        // Show applied coupon in summary
        document.querySelector('[data-coupon-code]').textContent = data.coupon.code;
        document.getElementById('coupon-applied').classList.remove('hidden');
        document.getElementById('coupon-add').classList.add('hidden');

        updateSummary(data.order);
        closeCouponModal();
        showToast(data.message, 'success');
    })
    .catch(error => {
        console.error('Error:', error);
        errorEl.textContent = 'Error applying coupon';
        errorEl.classList.remove('hidden');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'Apply';
    });
}

function removeCoupon() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`{{ route('vendor.orders.coupon.remove', $order->id) }}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show add, hide applied
            document.getElementById('coupon-add').classList.remove('hidden');
            document.getElementById('coupon-applied').classList.add('hidden');

            updateSummary(data.order);
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error removing coupon', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error removing coupon', 'error');
    });
}

// --- Edit Cart Modal ---
function openEditCartModal() {
    document.getElementById('editCartModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    document.getElementById('editStartTimeError').classList.add('hidden');
    document.getElementById('editEndTimeError').classList.add('hidden');
}

function closeEditCartModal() {
    document.getElementById('editCartModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function submitEditCart(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('editCartSubmitBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.getElementById('editStartTimeError').classList.add('hidden');
    document.getElementById('editEndTimeError').classList.add('hidden');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>{{ __("vendor.updating") }}...';

    const payload = {
        start_at: document.getElementById('edit_start_at').value || null,
        end_at: document.getElementById('edit_end_at').value || null,
    };

    fetch(`{{ route('vendor.orders.booking', $order) }}`, {
        method: 'PATCH',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json().then(data => ({ status: response.status, data })))
    .then(({ status, data }) => {
        if (status === 422 && data.errors) {
            if (data.errors.start_at) {
                const el = document.getElementById('editStartTimeError');
                el.textContent = data.errors.start_at[0];
                el.classList.remove('hidden');
            }
            if (data.errors.end_at) {
                const el = document.getElementById('editEndTimeError');
                el.textContent = data.errors.end_at[0];
                el.classList.remove('hidden');
            }
            return;
        }

        if (data.success) {
            if (typeof updateSummary === 'function' && data.order) updateSummary(data.order);
            if (typeof refreshOrderItems === 'function') refreshOrderItems();
            closeEditCartModal();
            showToast(data.message || 'Saved', 'success');
            setTimeout(() => window.location.reload(), 400);
        } else {
            showToast(data.message || 'Error updating booking', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating booking', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>{{ __("vendor.update") }}';
    });
}

function nudgeLineQty(cartId, itemId, delta, buttonEl) {
    const row = buttonEl.closest('[data-line-price-type]');
    let current = row ? parseInt(row.getAttribute('data-line-qty'), 10) : NaN;
    if (!Number.isFinite(current)) {
        const qtyEl = document.querySelector(`[data-qty-display="${itemId}"]`);
        current = qtyEl ? parseInt(qtyEl.textContent, 10) : 1;
    }
    if (!Number.isFinite(current) || current < 1) current = 1;
    const next = current + delta;
    if (delta < 0 && current <= 1) {
        const removeBtn = row ? row.querySelector('[data-cart-remove]') : null;
        removeCartItem(cartId, itemId, removeBtn || buttonEl);
        return;
    }
    if (next < 1) return;
    updateCartItemQty(cartId, itemId, next, buttonEl);
}

function updateCartItemQty(cartId, itemId, quantity, el) {
    quantity = parseInt(quantity);
    if (!quantity || quantity < 1) {
        showToast('Quantity must be at least 1', 'error');
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const row = el && el.closest ? el.closest('[data-line-price-type]') : null;
    const priceType = row ? row.getAttribute('data-line-price-type') : null;
    let billingUnits = 1;
    if (priceType && priceType !== 'fixed' && row) {
        const b = parseFloat(row.getAttribute('data-line-billing'));
        billingUnits = Number.isFinite(b) && b >= 0.01 ? b : 1;
    }

    fetch(`{{ url('vendor/orders') }}/${cartId}/items/${itemId}`, {
        method: 'PUT',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ quantity: quantity, billing_units: billingUnits })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Sync Alpine state via event
            window.dispatchEvent(new CustomEvent('order-item-updated', {
                detail: {
                    itemId: itemId,
                    quantity: data.item.quantity,
                    billing_units: data.item.billing_units,
                },
            }));

            // Update quantity display
            const qtyDisplay = document.querySelector(`[data-qty-display="${itemId}"]`);
            if (qtyDisplay) qtyDisplay.textContent = String(data.item.quantity);

            // Update line total
            const lineTotalEl = document.querySelector(`[data-line-total="${itemId}"]`);
            if (lineTotalEl) lineTotalEl.textContent = '₹' + parseFloat(data.item.line_total).toFixed(2);

            if (row) {
                if (data.item.price_type) {
                    row.setAttribute('data-line-price-type', data.item.price_type);
                }
                row.setAttribute('data-line-qty', data.item.quantity);
                if (data.item.billing_units != null) {
                    row.setAttribute('data-line-billing', data.item.billing_units);
                    const bin = row.querySelector('input[id^="line-billing-"]');
                    if (bin) bin.value = parseFloat(data.item.billing_units);
                }
            }

            // Update summary totals
            updateSummary(data.order);

            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error updating quantity', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating quantity', 'error');
    });
}

function nudgeLineBilling(cartId, itemId, delta) {
    const input = document.getElementById('line-billing-' + itemId);
    if (!input) return;
    let v = parseFloat(input.value);
    if (!Number.isFinite(v)) v = 1;
    if (delta < 0 && v <= 0.011) return;
    v = Math.round((v + delta) * 100) / 100;
    if (v < 0.01) v = 0.01;
    input.value = v;
    updateCartBillingUnits(cartId, itemId, input);
}

function updateCartBillingUnits(cartId, itemId, inputEl) {
    const row = inputEl.closest('[data-line-price-type]');
    if (!row) return;
    let billing = parseFloat(inputEl.value);
    if (!Number.isFinite(billing) || billing < 0.01) {
        billing = 1;
        inputEl.value = '1';
    }
    const qty = parseInt(row.getAttribute('data-line-qty'), 10) || 1;
    const priceType = row.getAttribute('data-line-price-type');
    const billingToSend = priceType === 'fixed' ? 1 : billing;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch(`{{ url('vendor/orders') }}/${cartId}/items/${itemId}`, {
        method: 'PUT',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ quantity: qty, billing_units: billingToSend })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            row.setAttribute('data-line-qty', data.item.quantity);
            if (data.item.billing_units != null) {
                row.setAttribute('data-line-billing', data.item.billing_units);
                inputEl.value = parseFloat(data.item.billing_units);
            }
            window.dispatchEvent(new CustomEvent('order-item-updated', {
                detail: {
                    itemId: itemId,
                    quantity: data.item.quantity,
                    billing_units: data.item.billing_units,
                },
            }));
            const lineTotalEl = document.querySelector(`[data-line-total="${itemId}"]`);
            if (lineTotalEl) lineTotalEl.textContent = '₹' + parseFloat(data.item.line_total).toFixed(2);
            updateSummary(data.order);
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Could not update line', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating line', 'error');
    });
}

function removeCartItem(cartId, itemId, button) {
    // Show confirmation modal
    pendingDelete = { cartId, itemId, button };
    const modal = document.getElementById('deleteConfirmModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteConfirmModal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    pendingDelete = null;
}

function confirmDelete() {
    if (!pendingDelete) return;

    const { cartId, itemId, button } = pendingDelete;
    closeDeleteModal();

    const row = button.closest('[data-cart-line]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Disable button while processing
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch(`{{ url('vendor/orders') }}/${cartId}/items/${itemId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Sync Alpine state via event
            window.dispatchEvent(new CustomEvent('order-item-removed', { detail: { itemId: itemId } }));

            // Animate and remove the row
            row.style.transition = 'opacity 0.3s, max-height 0.3s';
            row.style.opacity = '0';
            row.style.overflow = 'hidden';
            setTimeout(() => {
                row.remove();

                // Update summary totals
                updateSummary(data.order);

                const itemsCountEl = document.querySelector('[data-items-count]');
                if (itemsCountEl) {
                    const c = data.order.items_count;
                    itemsCountEl.innerHTML = '<span class="inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-full bg-blue-100 px-2 text-xs font-bold text-blue-800 tabular-nums">' + c + '</span> <span class="font-medium text-gray-800">{{ __("vendor.items") }}</span>';
                }

                if (data.order.items_count === 0) {
                    refreshOrderItems();
                }
            }, 300);

            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Error removing item', 'error');
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-trash"></i>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error removing item', 'error');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-trash"></i>';
    });
}

// Modal event listeners
document.getElementById('deleteModalCancel').addEventListener('click', closeDeleteModal);
document.getElementById('deleteModalOverlay').addEventListener('click', closeDeleteModal);
document.getElementById('deleteModalConfirm').addEventListener('click', confirmDelete);

function updateSummary(cart) {
    const subTotalEl = document.querySelector('[data-sub-total]');
    const taxTotalEl = document.querySelector('[data-tax-total]');
    const discountAmountEl = document.querySelector('[data-discount-amount]');
    const couponDiscountEl = document.querySelector('[data-coupon-discount]');
    const discountTotalEl = document.querySelector('[data-discount-total]');
    const orderTotalEl = document.querySelector('[data-order-total]');

    if (subTotalEl) subTotalEl.textContent = '₹' + parseFloat(cart.sub_total).toFixed(2);
    if (taxTotalEl) taxTotalEl.textContent = '₹' + parseFloat(cart.tax_total).toFixed(2);
    if (discountAmountEl) discountAmountEl.textContent = '-₹' + parseFloat(cart.discount_amount).toFixed(2);
    if (couponDiscountEl) couponDiscountEl.textContent = '-₹' + parseFloat(cart.coupon_discount).toFixed(2);
    if (discountTotalEl) discountTotalEl.textContent = '-₹' + parseFloat(cart.discount_total).toFixed(2);
    if (orderTotalEl) orderTotalEl.textContent = '₹' + parseFloat(cart.grand_total).toFixed(2);

    const delRow = document.getElementById('summary-delivery-charge-row');
    const delLine = document.querySelector('[data-delivery-charge-line]');
    if (delRow && delLine && cart.fulfillment_type !== undefined) {
        const ch = parseFloat(cart.delivery_charge ?? 0) || 0;
        if (cart.fulfillment_type === 'delivery' && ch > 0) {
            delRow.classList.remove('hidden');
            delLine.textContent = '₹' + ch.toFixed(2);
        } else {
            delRow.classList.add('hidden');
        }
    }

    securityDepositState.orderGrandTotal = parseFloat(cart.grand_total);
    securityDepositState.subTotal = parseFloat(cart.sub_total);
    if (cart.security_deposit_type !== undefined) {
        securityDepositState.type = cart.security_deposit_type || 'none';
    }
    if (cart.security_deposit_value !== undefined && cart.security_deposit_value !== null && cart.security_deposit_value !== '') {
        securityDepositState.value = parseFloat(cart.security_deposit_value);
    } else if (cart.security_deposit_type === 'none' || !cart.security_deposit_type) {
        securityDepositState.value = 0;
    }
    if (cart.paid_amount !== undefined && cart.paid_amount !== null) {
        const paidEl = document.querySelector('[data-paid-amount]');
        if (paidEl) paidEl.textContent = '₹' + parseFloat(cart.paid_amount).toFixed(2);
    }
    applySecurityDepositDisplay(cart);
    if (typeof refreshPaymentListFromOrder === 'function') {
        refreshPaymentListFromOrder(cart);
    }
}

function showToast(message, type = 'success') {
    const bgColor = type === 'success' ? 'bg-emerald-500' : 'bg-red-500';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 z-[60]`;
    toast.innerHTML = `
        <i class="fas ${icon} text-2xl"></i>
        <div><p class="font-medium">${message}</p></div>
        <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-100">
            <i class="fas fa-times"></i>
        </button>
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

</script>
@endsection
