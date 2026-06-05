@extends('vendor.layouts.app')

@section('title', __('vendor.create_order'))
@section('page-title', __('vendor.create_order'))

@section('content')

<div class="mx-auto max-w-5xl pb-[max(4.25rem,env(safe-area-inset-bottom))] max-md:pb-[max(11rem,env(safe-area-inset-bottom))] md:pb-0">
    @include('vendor.orders.partials.wizard-steps', ['current' => 2, 'compact' => true])

    <script>
        function orderWizardItemsPage() {
            const p = {
                items: @json($catalogItemsForJs),
                billingUnitsLabels: @json($billingUnitsLabels),
                initialQty: @json($initialQty),
                initialUnits: @json($initialUnits),
                bookingDefaultUnitsByPriceType: @json($bookingDefaultUnitsByPriceType ?? []),
                quickStoreUrl: @json(route('vendor.items.quick-store')),
                categoryStoreUrl: @json(route('vendor.categories.store')),
                categories: @json($categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values()),
            };
            return {
                items: p.items,
                billingUnitsLabels: p.billingUnitsLabels,
                bookingDefaultUnitsByPriceType: p.bookingDefaultUnitsByPriceType,
                quickStoreUrl: p.quickStoreUrl,
                categoryStoreUrl: p.categoryStoreUrl,
                categories: p.categories,
                searchQuery: '',
                selectedCategory: '',
                showAddCategoryInline: false,
                newCategoryName: '',
                categoryInlineError: '',
                categoryCreateSaving: false,
                lineQty: {},
                lineUnits: {},
                showAddItemModal: false,
                quickItemSaving: false,
                quickItemError: '',
                quickItem: { name: '', category_id: '', price: '', rental_period: 'per_day' },
                itemsStepError: '',
                get hasSelectedItems() {
                    return Object.keys(this.lineQty).some((id) => (parseInt(String(this.lineQty[id]), 10) || 0) >= 1);
                },
                submitItemsStep(ev) {
                    if (!this.hasSelectedItems) {
                        this.itemsStepError = @json(__('vendor.order_wizard_select_at_least_one_item'));
                        if (typeof showToast === 'function') {
                            showToast(this.itemsStepError, 'error');
                        }
                        return;
                    }
                    this.itemsStepError = '';
                    ev.target.submit();
                },
                init() {
                        const qty = {};
                        const units = {};
                        p.items.forEach((item) => {
                            const id = item.id;
                            const iq = p.initialQty[id];
                            qty[id] = iq !== undefined && iq !== null ? parseInt(String(iq), 10) || 0 : 0;
                            const iu = p.initialUnits[id];
                            units[id] = iu !== undefined && iu !== null && iu !== '' ? parseFloat(String(iu)) : null;
                        });
                        this.lineQty = qty;
                        this.lineUnits = units;
                    },
                    matchesFilter(item) {
                        const q = (this.searchQuery || '').toLowerCase().trim();
                        const cat = this.selectedCategory || '';
                        const matchesSearch = !q ||
                            item.name.toLowerCase().includes(q) ||
                            (item.category && item.category.name.toLowerCase().includes(q));
                        const matchesCategory = !cat || String(item.category_id ?? '') === String(cat);
                        return matchesSearch && matchesCategory;
                    },
                    get filteredItems() {
                        return this.items.filter((item) => this.matchesFilter(item));
                    },
                    openAddCategoryInline() {
                        this.showAddCategoryInline = true;
                        this.categoryInlineError = '';
                        this.$nextTick(() => document.getElementById('orderWizardNewCategoryName')?.focus());
                    },
                    closeAddCategoryInline() {
                        this.showAddCategoryInline = false;
                        this.categoryInlineError = '';
                    },
                    async saveQuickCategory() {
                        const name = (this.newCategoryName || '').trim();
                        if (!name) {
                            this.categoryInlineError = @json(__('vendor.order_wizard_category_name_required'));
                            return;
                        }
                        if (this.categoryCreateSaving) return;
                        this.categoryInlineError = '';
                        this.categoryCreateSaving = true;
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        try {
                            const res = await fetch(this.categoryStoreUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({ name, is_active: 1 }),
                            });
                            const data = await res.json().catch(() => ({}));
                            if (!res.ok || !data.success || !data.category) {
                                const errs = data.errors ? Object.values(data.errors).flat() : [];
                                this.categoryInlineError = data.message || errs[0] || @json(__('vendor.order_wizard_category_create_failed'));
                                return;
                            }
                            const cat = { id: data.category.id, name: data.category.name };
                            const rest = this.categories.filter((c) => String(c.id) !== String(cat.id));
                            this.categories = [cat, ...rest];
                            this.quickItem.category_id = String(cat.id);
                            this.showAddCategoryInline = false;
                            this.newCategoryName = '';
                        } catch (e) {
                            this.categoryInlineError = @json(__('vendor.order_wizard_category_create_failed'));
                        } finally {
                            this.categoryCreateSaving = false;
                        }
                    },
                    billingUnitsLabelForLine(item) {
                        const t = item.rental_period;
                        return this.billingUnitsLabels[t] || '';
                    },
                    isSelected(itemId) {
                        return (parseInt(String(this.lineQty[itemId]), 10) || 0) >= 1;
                    },
                    getQty(itemId) {
                        return parseInt(String(this.lineQty[itemId]), 10) || 0;
                    },
                    defaultBillingUnitsFor(item) {
                        const map = this.bookingDefaultUnitsByPriceType || {};
                        const t = item.rental_period;
                        const raw = map[t];
                        const v = raw !== undefined && raw !== null ? parseFloat(String(raw)) : NaN;
                        return Number.isFinite(v) ? v : 1;
                    },
                    getLineBillingUnits(item) {
                        const id = item.id;
                        const v = this.lineUnits[id];
                        if (v !== undefined && v !== null && v !== '' && !Number.isNaN(parseFloat(String(v)))) {
                            return parseFloat(String(v));
                        }
                        return this.defaultBillingUnitsFor(item);
                    },
                    addLine(item) {
                        this.itemsStepError = '';
                        this.lineQty = { ...this.lineQty, [item.id]: 1 };
                        if (item.uses_billing_units) {
                            const cur = this.lineUnits[item.id];
                            if (cur === undefined || cur === null || cur === '' || !Number.isFinite(parseFloat(String(cur)))) {
                                this.lineUnits = { ...this.lineUnits, [item.id]: this.defaultBillingUnitsFor(item) };
                            }
                        }
                    },
                    incrementQty(item) {
                        const id = item.id;
                        const next = this.getQty(id) + 1;
                        if (next >= 1) this.itemsStepError = '';
                        this.lineQty = { ...this.lineQty, [id]: next };
                        if (item.uses_billing_units) {
                            const cur = this.lineUnits[id];
                            if (cur === undefined || cur === null || !Number.isFinite(parseFloat(String(cur)))) {
                                this.lineUnits = { ...this.lineUnits, [id]: this.defaultBillingUnitsFor(item) };
                            }
                        }
                    },
                    decrementQty(item) {
                        const id = item.id;
                        const current = this.getQty(id);
                        if (current > 1) {
                            this.lineQty = { ...this.lineQty, [id]: current - 1 };
                        } else if (current === 1) {
                            this.lineQty = { ...this.lineQty, [id]: 0 };
                            this.lineUnits = { ...this.lineUnits, [id]: null };
                        }
                    },
                    incrementBillingUnits(item) {
                        const cur = this.getLineBillingUnits(item);
                        const v = Math.round((Number.isFinite(cur) ? cur : 1) * 100 + 100) / 100;
                        this.lineUnits = { ...this.lineUnits, [item.id]: v };
                    },
                    decrementBillingUnits(item) {
                        const cur = this.getLineBillingUnits(item);
                        if (!Number.isFinite(cur) || cur <= 0.011) return;
                        let v = Math.round((Number.isFinite(cur) ? cur : 1) * 100 - 100) / 100;
                        if (!Number.isFinite(v) || v < 0.01) v = 0.01;
                        this.lineUnits = { ...this.lineUnits, [item.id]: v };
                    },
                    onBillingUnitsBlur(item, ev) {
                        let v = parseFloat(ev.target.value);
                        if (!Number.isFinite(v) || v < 0.01) {
                            v = this.defaultBillingUnitsFor(item);
                            ev.target.value = String(v);
                        }
                        this.lineUnits = { ...this.lineUnits, [item.id]: v };
                    },
                    openAddItemModal() {
                        this.quickItemError = '';
                        this.showAddCategoryInline = false;
                        this.newCategoryName = '';
                        this.categoryInlineError = '';
                        this.quickItem = { name: '', category_id: '', price: '', rental_period: 'per_day' };
                        this.showAddItemModal = true;
                        document.documentElement.classList.add('overflow-hidden');
                        this.$nextTick(() => document.getElementById('orderWizardQuickItemName')?.focus());
                    },
                    closeAddItemModal() {
                        if (this.quickItemSaving) return;
                        this.showAddItemModal = false;
                        document.documentElement.classList.remove('overflow-hidden');
                    },
                    async saveQuickItem() {
                        if (this.quickItemSaving) return;
                        this.quickItemError = '';
                        const name = (this.quickItem.name || '').trim();
                        const categoryId = this.quickItem.category_id;
                        const price = parseFloat(String(this.quickItem.price));
                        if (!name || !categoryId) {
                            this.quickItemError = @json(__('vendor.order_wizard_quick_item_required'));
                            return;
                        }
                        if (!Number.isFinite(price) || price < 0) {
                            this.quickItemError = @json(__('vendor.order_wizard_quick_item_price_invalid'));
                            return;
                        }
                        this.quickItemSaving = true;
                        try {
                            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                            const res = await fetch(this.quickStoreUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({
                                    name,
                                    category_id: categoryId,
                                    price,
                                    rental_period: this.quickItem.rental_period || 'per_day',
                                }),
                            });
                            const data = await res.json().catch(() => ({}));
                            if (!res.ok || !data.success) {
                                const errs = data.errors ? Object.values(data.errors).flat() : [];
                                this.quickItemError = data.message || errs[0] || @json(__('vendor.order_wizard_item_create_failed'));
                                return;
                            }
                            const item = data.item;
                            if (!this.items.some((i) => i.id === item.id)) {
                                this.items = [...this.items, item];
                                this.lineQty = { ...this.lineQty, [item.id]: 0 };
                                this.lineUnits = { ...this.lineUnits, [item.id]: null };
                            }
                            this.addLine(item);
                            this.searchQuery = '';
                            this.selectedCategory = '';
                            this.showAddItemModal = false;
                            document.documentElement.classList.remove('overflow-hidden');
                        } catch (e) {
                            this.quickItemError = @json(__('vendor.order_wizard_item_create_failed'));
                        } finally {
                            this.quickItemSaving = false;
                        }
                    },
                };
        }
    </script>

    <div x-data="orderWizardItemsPage()" x-init="init()">
            <form action="{{ route('vendor.orders.create.step2') }}" method="POST"
                  class="overflow-hidden rounded-xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-100"
                  id="order-items-step-form"
                  @submit.prevent="submitItemsStep($event)">
                @csrf

                <div class="flex-shrink-0 space-y-2 border-b border-gray-100 bg-gray-50/80 px-3 py-2.5 sm:space-y-2 sm:px-4 sm:py-3">
                    <div class="flex justify-end" x-show="items.length > 0" x-cloak>
                        <button type="button"
                                @click="openAddItemModal()"
                                class="inline-flex h-10 w-full items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition [touch-action:manipulation] hover:bg-emerald-700 active:bg-emerald-800 sm:w-auto sm:min-w-[9rem]">
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
                                       class="h-10 w-full rounded-lg border border-gray-200 bg-white pl-10 pr-10 text-sm text-gray-900 shadow-sm transition-shadow placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
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
                                        class="h-10 w-full appearance-none rounded-lg border border-gray-200 bg-white pl-10 pr-9 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
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
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-xl bg-amber-50 ring-1 ring-amber-100">
                        <i class="fas fa-box-open text-2xl text-amber-600" aria-hidden="true"></i>
                    </div>
                    <p class="max-w-sm text-sm font-medium text-gray-800 sm:text-base">{{ __('vendor.order_wizard_no_catalog_items') }}</p>
                    <button type="button"
                            @click="openAddItemModal()"
                            class="mt-4 inline-flex h-10 min-w-[9rem] items-center justify-center rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition [touch-action:manipulation] hover:bg-emerald-700 active:bg-emerald-800">
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
                            class="mt-4 inline-flex h-10 items-center justify-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-semibold text-blue-700 shadow-sm transition hover:bg-gray-50">
                        <i class="fas fa-redo mr-1.5 text-xs"></i>{{ __('vendor.clear_filters') }}
                    </button>
                </div>

                <div x-show="items.length > 0" x-cloak class="max-h-[min(78vh,38rem)] overflow-y-auto overscroll-y-contain [-webkit-overflow-scrolling:touch] md:max-h-[min(82vh,42rem)]">
                    <style>
                        @media (max-width: 767px) {
                            .order-wizard-items-table thead { display: none !important; }
                            .order-wizard-items-table { display: block; width: 100%; border: 0; }
                            .order-wizard-items-table tbody { display: block; }
                            .order-wizard-items-table tbody tr[data-order-wizard-line] {
                                display: grid !important;
                                grid-template-columns: minmax(0, 1fr) minmax(6.75rem, auto);
                                grid-template-rows: auto auto;
                                column-gap: 0.625rem;
                                row-gap: 0.375rem;
                                align-items: start;
                                padding: 0.625rem;
                                margin-bottom: 0.5rem;
                                margin-left: 0.5rem;
                                margin-right: 0.5rem;
                                border: 1px solid #e5e7eb !important;
                                border-radius: 0.625rem;
                                background: #fff;
                                box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
                            }
                            .order-wizard-items-table tbody tr[data-order-wizard-line]:last-child { margin-bottom: 0.5rem; }
                            .order-wizard-items-table tbody tr[data-order-wizard-line] > td:nth-child(1) {
                                grid-column: 1;
                                grid-row: 1 / span 2;
                                align-self: stretch;
                                display: block !important;
                                padding: 0 !important;
                                border: none !important;
                                width: auto !important;
                                min-width: 0;
                                text-align: left !important;
                            }
                            .order-wizard-items-table tbody tr[data-order-wizard-line] > td:nth-child(3) {
                                grid-column: 2;
                                grid-row: 1;
                                display: flex !important;
                                justify-content: flex-end;
                                align-items: flex-start;
                                padding: 0 !important;
                                border: none !important;
                                width: auto !important;
                                text-align: right !important;
                            }
                            .order-wizard-items-table tbody tr[data-order-wizard-line] > td:nth-child(4) {
                                grid-column: 2;
                                grid-row: 2;
                                display: flex !important;
                                justify-content: flex-end;
                                align-items: flex-start;
                                padding: 0 !important;
                                border: none !important;
                                width: auto !important;
                                text-align: right !important;
                            }
                        }
                    </style>
                    <div class="-mx-0 max-md:overflow-visible md:overflow-x-auto md:border-b md:border-gray-100">
                        <table class="order-wizard-items-table w-full border-collapse text-left text-sm md:min-w-[40rem]">
                            <thead class="sticky top-0 z-10 border-b border-gray-200 bg-gray-50/95 backdrop-blur supports-[backdrop-filter]:bg-gray-50/80">
                                <tr>
                                    <th class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-wide text-gray-600 sm:px-4">{{ __('vendor.item') }}</th>
                                    <th class="px-3 py-2.5 text-[11px] font-bold uppercase tracking-wide text-gray-600 sm:px-4">{{ __('vendor.price') }}</th>
                                    <th class="px-3 py-2.5 text-center text-[11px] font-bold uppercase tracking-wide text-gray-600 sm:px-4">{{ __('vendor.quantity') }}</th>
                                    <th class="min-w-[9rem] px-3 py-2.5 text-[11px] font-bold uppercase tracking-wide text-gray-600 sm:px-4">{{ __('vendor.billing_units') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 md:divide-y">
                                <template x-for="item in items" :key="item.id">
                                    <tr x-show="matchesFilter(item)"
                                        x-cloak
                                        data-order-wizard-line
                                        class="border-b border-gray-100 bg-white transition hover:bg-blue-50/40 max-md:border-0 max-md:bg-transparent max-md:hover:bg-transparent md:border-b">
                                        <td class="px-3 py-2.5 align-middle sm:px-4 sm:py-3">
                                            <input type="hidden" :name="'lines[' + item.id + '][item_id]'" :value="item.id">
                                            <div class="flex items-start gap-2 sm:items-center sm:gap-2.5">
                                                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-blue-50 to-indigo-50 ring-1 ring-blue-100 sm:h-10 sm:w-10">
                                                    <img x-show="item.photo_url"
                                                         :src="item.photo_url"
                                                         alt=""
                                                         class="h-full w-full object-cover"
                                                         loading="lazy"
                                                         decoding="async">
                                                    <i x-show="!item.photo_url"
                                                       class="fas fa-box text-sm text-blue-600 sm:text-base"
                                                       aria-hidden="true"></i>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-sm font-semibold text-gray-900" x-text="item.name"></p>
                                                    <p class="text-[11px] text-gray-500 sm:text-xs" x-show="item.manage_stock">
                                                        {{ __('vendor.stock') }}: <span x-text="item.stock"></span>
                                                    </p>
                                                    <div class="mt-1 md:hidden">
                                                        <span class="inline-flex rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-800 tabular-nums">₹<span x-text="parseFloat(item.price).toFixed(2)"></span></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="hidden px-3 py-2.5 align-middle sm:px-4 sm:py-3 md:table-cell">
                                            <span class="inline-flex rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-800 tabular-nums sm:text-sm">₹<span x-text="parseFloat(item.price).toFixed(2)"></span></span>
                                        </td>
                                        <td class="px-3 py-2.5 align-middle sm:px-4 sm:py-3">
                                            <template x-if="!isSelected(item.id)">
                                                <div class="flex flex-col items-center justify-center gap-1.5 max-md:w-full max-md:items-end">
                                                    <input type="hidden" :name="'lines[' + item.id + '][quantity]'" value="0">
                                                    <button type="button"
                                                            @click="addLine(item)"
                                                            class="inline-flex h-9 min-w-[5rem] max-md:min-w-0 max-md:w-full items-center justify-center rounded-lg bg-blue-600 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700 active:scale-[0.98] sm:text-sm">
                                                        <span class="flex items-center gap-1"><i class="fas fa-plus text-xs"></i>{{ __('vendor.add') }}</span>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="isSelected(item.id)">
                                                <div class="flex items-center justify-center gap-1 max-md:justify-end">
                                                    <button type="button"
                                                            @click="decrementQty(item)"
                                                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50 active:scale-95">
                                                        <i class="fas fa-minus text-xs"></i>
                                                    </button>
                                                    <input type="number"
                                                           min="1"
                                                           step="1"
                                                           class="h-9 w-14 rounded-lg border border-gray-200 text-center text-sm font-bold text-gray-900 shadow-inner focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                                           :name="'lines[' + item.id + '][quantity]'"
                                                           x-model.number="lineQty[item.id]"
                                                           @input="if ((parseInt($event.target.value, 10) || 0) < 1) { lineQty[item.id] = 0; lineUnits[item.id] = null; }">
                                                    <button type="button"
                                                            @click="incrementQty(item)"
                                                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50 active:scale-95">
                                                        <i class="fas fa-plus text-xs"></i>
                                                    </button>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="px-3 py-2.5 align-middle sm:px-4 sm:py-3">
                                            <div x-show="!isSelected(item.id)" class="text-center text-xs text-gray-400 max-md:text-right">—</div>
                                            <div x-show="isSelected(item.id) && !item.uses_billing_units" class="text-center text-xs text-gray-400 max-md:text-right" x-cloak>—</div>
                                            <template x-if="isSelected(item.id) && item.uses_billing_units">
                                                <div class="mx-auto max-w-[11rem] max-md:ml-0 max-md:mr-0 max-md:max-w-none max-md:w-full">
                                                    <p class="mb-1 hidden text-center text-[10px] font-semibold uppercase tracking-wide text-gray-500 md:block md:text-[11px]" x-text="billingUnitsLabelForLine(item)"></p>
                                                    <div class="flex items-center justify-center gap-1 max-md:justify-end">
                                                        <button type="button"
                                                                @click="decrementBillingUnits(item)"
                                                                :disabled="getLineBillingUnits(item) <= 0.01"
                                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50">
                                                            <i class="fas fa-minus text-xs"></i>
                                                        </button>
                                                        <input type="number"
                                                               step="0.01"
                                                               min="0.01"
                                                               lang="en"
                                                               class="h-9 w-14 rounded-lg border border-gray-200 text-center text-sm font-bold text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30"
                                                               :name="'lines[' + item.id + '][billing_units]'"
                                                               :value="getLineBillingUnits(item)"
                                                               @input="lineUnits[item.id] = parseFloat($event.target.value) || defaultBillingUnitsFor(item)"
                                                               @blur="onBillingUnitsBlur(item, $event)">
                                                        <button type="button"
                                                                @click="incrementBillingUnits(item)"
                                                                class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50">
                                                            <i class="fas fa-plus text-xs"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            <template x-if="!isSelected(item.id) || !item.uses_billing_units">
                                                <input type="hidden" :name="'lines[' + item.id + '][billing_units]'" value="">
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                @error('lines')
                    <p class="border-t border-red-100 bg-red-50/80 px-3 py-2 text-xs text-red-700 sm:px-4 sm:text-sm">{{ $message }}</p>
                @enderror
                @if($errors->has('items'))
                    <p class="border-t border-red-100 bg-red-50/80 px-3 py-2 text-xs text-red-700 sm:px-4 sm:text-sm">{{ $errors->first('items') }}</p>
                @endif

                <x-order-wizard-actions class="border-t border-gray-200 p-3 sm:p-4">
                    <a href="{{ route('vendor.orders.create') }}"
                       class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-blue-700 [touch-action:manipulation] sm:mr-auto">
                        <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                        {{ __('vendor.back') }}
                    </a>
                    <div class="flex w-full flex-col items-stretch gap-2 sm:w-auto sm:items-end">
                        <p x-show="itemsStepError"
                           x-text="itemsStepError"
                           x-cloak
                           class="text-center text-xs font-medium text-red-600 sm:text-right sm:text-sm"
                           role="alert"></p>
                        <button type="submit"
                                :aria-disabled="!hasSelectedItems"
                                :class="hasSelectedItems
                                    ? 'inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm shadow-emerald-600/15 transition [touch-action:manipulation] hover:bg-emerald-700 active:bg-emerald-800 sm:w-auto sm:min-w-[8rem]'
                                    : 'inline-flex h-10 w-full cursor-not-allowed items-center justify-center gap-1.5 rounded-lg bg-gray-300 px-4 text-sm font-semibold text-gray-500 shadow-none transition [touch-action:manipulation] sm:w-auto sm:min-w-[8rem]'">
                            <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                            {{ __('vendor.order_wizard_continue_summary') }}
                        </button>
                    </div>
                </x-order-wizard-actions>
            </form>

        @include('vendor.orders.partials.quick-item-modal')
    </div>
</div>
@endsection
