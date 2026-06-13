@extends('vendor.layouts.app')

@section('title', __('vendor.order_details'))
@section('page-title', __('vendor.order_details'))

@section('main_bottom_class')
    pb-[max(4.25rem,env(safe-area-inset-bottom))] md:pb-6
@endsection

@section('content')
@php
    $orderReadOnly = $order->isLockedForEditing();
    $billingUnitShorts = [
        'per_minute' => __('vendor.order_wizard_summary_unit_minute'),
        'per_hour' => __('vendor.order_wizard_summary_unit_hour'),
        'per_day' => __('vendor.order_wizard_summary_unit_day'),
        'per_week' => __('vendor.order_wizard_summary_unit_week'),
        'per_month' => __('vendor.order_wizard_summary_unit_month'),
        'per_year' => __('vendor.order_wizard_summary_unit_year'),
    ];
@endphp
{{-- Alpine state must not live inside x-data="..." — JSON quotes break the HTML attribute and leak JS as visible text. --}}
<script>
function isoToDatetimeLocalValue(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}
function isoToFulfillmentValue(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
}
function formatFulfillmentDisplay(val) {
    if (!val) return '';
    let d;
    if (String(val).includes('T')) {
        d = new Date(val);
    } else {
        const m = String(val).match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/);
        d = m ? new Date(+m[1], +m[2] - 1, +m[3], +m[4], +m[5]) : new Date(val);
    }
    if (isNaN(d.getTime())) return val;
    return d.toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
}
function orderPageData() {
    const p = {
        addedItems: @json($order->items->pluck('quantity', 'item_id')),
        addedItemBillingUnits: @json($order->items->mapWithKeys(fn ($row) => [$row->item_id => (float) ($row->billing_units ?? 1)])->all()),
        billingUnitsLabels: @json($cartBillingUnitsLabels),
        rentalPeriods: @json(\App\Models\Items::rentalPeriodSelectOptions()),
        billingUnitShorts: @json($billingUnitShorts),
        items: @json($availableItemsJson ?? $availableItems),
        orderLineVariantQty: @json($order->items->mapWithKeys(fn ($row) => [
            (int) $row->item_id.'_v'.(int) ($row->item_variant_id ?? 0) => (int) $row->quantity,
        ])->all()),
        fulfillmentType: @json($order->fulfillment_type ?: 'pickup'),
        deliveryAddress: @json($order->delivery_address ?? ''),
        pickupAt: @json($order->pickup_at ? $order->pickup_at->copy()->timezone(config('app.timezone'))->format('Y-m-d H:i') : ''),
        deliveryAt: @json($order->delivery_at ? $order->delivery_at->copy()->timezone(config('app.timezone'))->format('Y-m-d H:i') : ''),
        deliveryCharge: @json(round((float) ($order->delivery_charge ?? 0), 2)),
        fulfillmentPickupHiddenId: 'fulfillment_modal_pickup_at',
        fulfillmentDeliveryHiddenId: 'fulfillment_modal_delivery_at',
        saveFulfillmentUrl: @json(route('vendor.orders.fulfillment', $order)),
        readOnly: @json($orderReadOnly),
    };
    return {
        showAddItem: false,
        addedItems: p.addedItems,
        addedItemBillingUnits: p.addedItemBillingUnits,
        billingUnitsLabels: p.billingUnitsLabels,
        rentalPeriods: p.rentalPeriods,
        billingUnitShorts: p.billingUnitShorts,
        addingItem: null,
        updatingItem: null,
        searchQuery: '',
        selectedCategory: '',
        items: p.items,
        orderLineVariantQty: p.orderLineVariantQty,
        fulfillmentType: p.fulfillmentType,
        deliveryAddress: p.deliveryAddress,
        pickupAt: p.pickupAt,
        deliveryAt: p.deliveryAt,
        deliveryCharge: p.deliveryCharge,
        fulfillmentPickupHiddenId: p.fulfillmentPickupHiddenId,
        fulfillmentDeliveryHiddenId: p.fulfillmentDeliveryHiddenId,
        savingFulfillment: false,
        fulfillmentFieldError: '',
        saveFulfillmentUrl: p.saveFulfillmentUrl,
        readOnly: p.readOnly,
        headerMoreOpen: false,
        showFulfillmentModal: false,
        _fulfillmentSnapshot: null,
        pickupLabel: @json(__('vendor.pickup')),
        deliveryLabel: @json(__('vendor.delivery')),
        notSpecifiedLabel: @json(__('vendor.not_specified')),
        openFulfillmentModal() {
            if (this.readOnly) return;
            this._fulfillmentSnapshot = {
                fulfillmentType: this.fulfillmentType,
                deliveryAddress: this.deliveryAddress,
                pickupAt: this.pickupAt,
                deliveryAt: this.deliveryAt,
                deliveryCharge: this.deliveryCharge,
            };
            this.fulfillmentFieldError = '';
            this.showFulfillmentModal = true;
        },
        cancelFulfillmentModal() {
            if (this.savingFulfillment) return;
            if (this._fulfillmentSnapshot) {
                const s = this._fulfillmentSnapshot;
                this.fulfillmentType = s.fulfillmentType;
                this.deliveryAddress = s.deliveryAddress;
                this.pickupAt = s.pickupAt;
                this.deliveryAt = s.deliveryAt;
                this.deliveryCharge = s.deliveryCharge;
            }
            this.fulfillmentFieldError = '';
            this.showFulfillmentModal = false;
            this._fulfillmentSnapshot = null;
        },
        fulfillmentSummaryPrimary() {
            return this.fulfillmentType === 'pickup' ? this.pickupLabel : this.deliveryLabel;
        },
        fulfillmentSummarySecondary() {
            if (this.fulfillmentType === 'pickup') {
                if (!this.pickupAt) return this.notSpecifiedLabel;
                return formatFulfillmentDisplay(this.pickupAt);
            }
            const addr = (this.deliveryAddress || '').trim();
            const ch = parseFloat(this.deliveryCharge) || 0;
            const bits = [];
            if (this.deliveryAt) bits.push(formatFulfillmentDisplay(this.deliveryAt));
            if (ch > 0) bits.push('₹' + ch.toFixed(2));
            if (addr) bits.push(addr.length > 72 ? addr.slice(0, 69) + '…' : addr);
            return bits.length ? bits.join(' · ') : this.notSpecifiedLabel;
        },
        async saveFulfillment() {
            if (this.readOnly) {
                return;
            }
            this.fulfillmentFieldError = '';
            document.dispatchEvent(new CustomEvent('sync-fulfillment-datetimes'));
            const pickupVal = document.getElementById(this.fulfillmentPickupHiddenId)?.value?.trim() || '';
            const deliveryVal = document.getElementById(this.fulfillmentDeliveryHiddenId)?.value?.trim() || '';
            if (this.fulfillmentType === 'pickup' && !pickupVal) {
                this.fulfillmentFieldError = @json(__('vendor.pickup_datetime_required'));
                showToast(this.fulfillmentFieldError, 'error');
                return;
            }
            if (this.fulfillmentType === 'delivery' && !(this.deliveryAddress || '').trim()) {
                this.fulfillmentFieldError = @json(__('vendor.delivery_address_required'));
                showToast(this.fulfillmentFieldError, 'error');
                return;
            }
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
                        pickup_at: this.fulfillmentType === 'pickup' ? pickupVal : null,
                        delivery_at: this.fulfillmentType === 'delivery' ? (deliveryVal || null) : null,
                        delivery_charge: this.fulfillmentType === 'delivery' ? (parseFloat(this.deliveryCharge) || 0) : 0,
                    }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    if (data.delivery_address !== undefined) {
                        this.deliveryAddress = data.delivery_address ?? '';
                    }
                    this.pickupAt = data.pickup_at ? isoToFulfillmentValue(data.pickup_at) : '';
                    this.deliveryAt = data.delivery_at ? isoToFulfillmentValue(data.delivery_at) : '';
                    if (data.delivery_charge !== undefined && data.delivery_charge !== null) {
                        this.deliveryCharge = parseFloat(data.delivery_charge) || 0;
                    }
                    if (data.order && typeof updateSummary === 'function') {
                        updateSummary(data.order);
                    }
                    showToast(data.message || 'Saved', 'success');
                    this.showFulfillmentModal = false;
                    this._fulfillmentSnapshot = null;
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
            return item.rental_period !== 'fixed';
        },
        billingUnitsLabelForLine(item) {
            const t = item.rental_period;
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
        orderId: @json($order->uuid),
        orderUuid: @json($order->uuid),
        lineEditOpen: false,
        lineEditItemId: null,
        lineEditName: '',
        lineEditQty: 1,
        lineEditBilling: '',
        lineEditUsesBilling: false,
        lineEditPrice: '',
        lineEditRentalPeriod: 'per_day',
        lineEditError: '',
        lineEditSaving: false,
        showVariantModal: false,
        variantModalItem: null,
        variantModalPick: '',
        variantModalSelections: [],
        variantModalError: '',
        variantModalMode: 'change',
        variantModalOrderItemId: null,
        variantModalOriginalVariantId: null,
        variantModalLineQty: 1,
        variantModalSaving: false,
        formatRupeeInt(n) {
            const x = parseFloat(n);
            if (!Number.isFinite(x)) return '0';
            return Math.round(x).toLocaleString('en-IN');
        },
        formatBillingUnitsDisplay(val) {
            const n = parseFloat(val);
            if (!Number.isFinite(n)) return '0';
            const s = (Math.round(n * 100) / 100).toString();
            return s.replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '') || '0';
        },
        lineEditBillingLabel() {
            return this.billingUnitsLabels[this.lineEditRentalPeriod] || @json(__('vendor.billing_units'));
        },
        lineEditRentalPeriodLabel() {
            return this.rentalPeriods[this.lineEditRentalPeriod] || this.lineEditRentalPeriod || '';
        },
        lineEditBillingUnitShort() {
            return this.billingUnitShorts[this.lineEditRentalPeriod] || '';
        },
        rentalPeriodUsesBilling(period) {
            return String(period || '') !== 'fixed';
        },
        onLineEditRentalChange() {
            this.lineEditUsesBilling = this.rentalPeriodUsesBilling(this.lineEditRentalPeriod);
            if (!this.lineEditUsesBilling) {
                this.lineEditBilling = '';
                return;
            }
            if (!this.lineEditBilling || this.lineEditBilling === '') {
                this.lineEditBilling = '1';
            }
        },
        lineEditPreviewTotal() {
            const price = parseFloat(String(this.lineEditPrice));
            const qty = parseInt(String(this.lineEditQty), 10);
            if (!Number.isFinite(price) || !qty || qty < 1) return 0;
            let total = price * qty;
            if (this.rentalPeriodUsesBilling(this.lineEditRentalPeriod)) {
                const bu = parseFloat(String(this.lineEditBilling));
                if (Number.isFinite(bu) && bu > 0) total *= bu;
            }
            return total;
        },
        incrementLineEditQty() {
            this.lineEditQty = Math.max(1, (parseInt(String(this.lineEditQty), 10) || 1) + 1);
            this.lineEditError = '';
        },
        decrementLineEditQty() {
            const q = parseInt(String(this.lineEditQty), 10) || 1;
            if (q <= 1) return;
            this.lineEditQty = q - 1;
            this.lineEditError = '';
        },
        incrementLineEditBilling() {
            const cur = parseFloat(String(this.lineEditBilling));
            const v = Math.round((Number.isFinite(cur) ? cur : 1) * 100 + 100) / 100;
            this.lineEditBilling = String(v);
            this.lineEditError = '';
        },
        decrementLineEditBilling() {
            const cur = parseFloat(String(this.lineEditBilling));
            if (!Number.isFinite(cur) || cur <= 0.011) return;
            let v = Math.round((cur) * 100 - 100) / 100;
            if (!Number.isFinite(v) || v < 0.01) v = 0.01;
            this.lineEditBilling = String(v);
            this.lineEditError = '';
        },
        openLineEdit(d) {
            this.lineEditItemId = d.item_id;
            this.lineEditName = d.name ?? '';
            this.lineEditQty = parseInt(String(d.quantity), 10) || 1;
            this.lineEditRentalPeriod = d.rental_period || 'per_day';
            this.lineEditUsesBilling = this.rentalPeriodUsesBilling(this.lineEditRentalPeriod);
            const unitPrice = parseFloat(d.unit_price);
            this.lineEditPrice = Number.isFinite(unitPrice) ? String(unitPrice) : '';
            this.lineEditError = '';
            if (d.billing_units !== null && d.billing_units !== undefined && d.billing_units !== '') {
                const n = parseFloat(String(d.billing_units));
                this.lineEditBilling = Number.isFinite(n) ? String(n) : '1';
            } else {
                this.lineEditBilling = this.lineEditUsesBilling ? '1' : '';
            }
            this.lineEditOpen = true;
        },
        closeLineEdit() {
            if (this.lineEditSaving) return;
            this.lineEditOpen = false;
            this.lineEditError = '';
        },
        async saveLineEdit() {
            if (this.lineEditSaving || this.lineEditItemId == null) return;
            this.lineEditError = '';
            const qty = parseInt(String(this.lineEditQty), 10);
            if (!qty || qty < 1) {
                this.lineEditError = @json(__('vendor.enter_quantity'));
                if (typeof showToast === 'function') showToast(this.lineEditError, 'error');
                return;
            }
            const price = parseFloat(String(this.lineEditPrice));
            if (!Number.isFinite(price) || price < 0) {
                this.lineEditError = @json(__('vendor.order_wizard_quick_item_price_invalid'));
                if (typeof showToast === 'function') showToast(this.lineEditError, 'error');
                return;
            }
            const rentalPeriod = String(this.lineEditRentalPeriod || 'per_day');
            let billing = parseFloat(String(this.lineEditBilling));
            if (this.rentalPeriodUsesBilling(rentalPeriod)) {
                if (!Number.isFinite(billing) || billing < 0.01) {
                    this.lineEditError = @json(__('vendor.order_wizard_billing_units_required'));
                    if (typeof showToast === 'function') showToast(this.lineEditError, 'error');
                    return;
                }
            } else {
                billing = 1;
            }
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            this.lineEditSaving = true;
            try {
                const res = await fetch(`{{ url('vendor/orders') }}/${this.orderId}/items/${this.lineEditItemId}`, {
                    method: 'PUT',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf || '',
                    },
                    body: JSON.stringify({
                        quantity: qty,
                        price: price,
                        rental_period: rentalPeriod,
                        billing_units: this.rentalPeriodUsesBilling(rentalPeriod) ? billing : null,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    window.dispatchEvent(new CustomEvent('order-item-updated', {
                        detail: {
                            itemId: this.lineEditItemId,
                            quantity: data.item.quantity,
                            billing_units: data.item.billing_units,
                        },
                    }));
                    if (typeof refreshOrderItems === 'function') refreshOrderItems();
                    if (typeof updateSummary === 'function' && data.order) updateSummary(data.order);
                    if (typeof showToast === 'function') showToast(data.message, 'success');
                    this.lineEditOpen = false;
                } else if (typeof showToast === 'function') {
                    showToast(data.message || 'Could not update line', 'error');
                }
            } catch (e) {
                if (typeof showToast === 'function') showToast('Network error', 'error');
            } finally {
                this.lineEditSaving = false;
            }
        },
        findCatalogItem(itemId) {
            return this.items.find((i) => String(i.id) === String(itemId)) || null;
        },
        findVariant(item, variantId) {
            if (!item || !Array.isArray(item.variants)) return null;
            return item.variants.find((v) => String(v.id) === String(variantId)) || null;
        },
        variantQtyOnOrder(itemId, variantId) {
            const key = String(itemId) + '_v' + String(variantId ?? 0);
            return parseInt(String(this.orderLineVariantQty[key] || 0), 10) || 0;
        },
        variantAvailableStock(item, variant) {
            if (!variant?.manage_stock) {
                return parseInt(String(variant?.stock), 10) || 0;
            }
            const base = parseInt(String(variant.stock), 10) || 0;
            let onOrder = this.variantQtyOnOrder(item?.id, variant.id);
            if (String(this.variantModalOriginalVariantId) === String(variant.id)) {
                onOrder -= parseInt(String(this.variantModalLineQty), 10) || 0;
            }
            return Math.max(0, base - Math.max(0, onOrder));
        },
        availableStockLabel(count) {
            const n = parseInt(String(count), 10) || 0;
            return n + ' ' + @json(__('vendor.available_stock'));
        },
        variantQtyInOrder() {
            return 0;
        },
        variantInCartLabel() {
            return '';
        },
        variantModalSelectionCount() {
            return this.variantModalSelections.length;
        },
        variantSelectable(variant) {
            if (!variant?.is_available) return false;
            const item = this.variantModalItem;
            const qty = parseInt(String(this.variantModalLineQty), 10) || 1;
            if (variant.manage_stock && this.variantAvailableStock(item, variant) < qty) return false;
            return true;
        },
        isVariantModalRowSelected(variant) {
            return String(this.variantModalPick) === String(variant.id);
        },
        handleVariantRowClick(variant) {
            if (!this.variantSelectable(variant)) return;
            this.variantModalPick = String(variant.id);
            this.variantModalError = '';
        },
        openLineVariantChange(d) {
            if (this.readOnly) return;
            const item = this.findCatalogItem(d.item_id);
            if (!item?.has_variants) return;
            this.variantModalError = '';
            this.variantModalItem = item;
            this.variantModalOrderItemId = d.order_item_id;
            this.variantModalOriginalVariantId = d.item_variant_id ?? null;
            this.variantModalLineQty = parseInt(String(d.quantity), 10) || 1;
            this.variantModalMode = 'change';
            this.variantModalPick = d.item_variant_id ? String(d.item_variant_id) : '';
            this.variantModalSelections = [];
            this.showVariantModal = true;
            document.documentElement.classList.add('overflow-hidden');
        },
        closeVariantModal() {
            if (this.variantModalSaving) return;
            this.showVariantModal = false;
            this.variantModalItem = null;
            this.variantModalPick = '';
            this.variantModalSelections = [];
            this.variantModalOrderItemId = null;
            this.variantModalOriginalVariantId = null;
            this.variantModalLineQty = 1;
            this.variantModalError = '';
            document.documentElement.classList.remove('overflow-hidden');
        },
        async confirmVariantModal() {
            if (this.variantModalSaving || this.variantModalMode !== 'change') return;
            const item = this.variantModalItem;
            const orderItemId = this.variantModalOrderItemId;
            if (!item || orderItemId == null) return;
            const variantId = parseInt(String(this.variantModalPick || ''), 10);
            if (!variantId) {
                this.variantModalError = @json(__('vendor.order_wizard_select_variant'));
                return;
            }
            const variant = this.findVariant(item, variantId);
            if (!variant || !this.variantSelectable(variant)) {
                this.variantModalError = @json(__('vendor.order_wizard_variant_invalid'));
                return;
            }
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            this.variantModalSaving = true;
            try {
                const res = await fetch(`{{ url('vendor/orders') }}/${this.orderId}/lines/${orderItemId}/variant`, {
                    method: 'PATCH',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf || '',
                    },
                    body: JSON.stringify({ item_variant_id: variantId }),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    if (typeof refreshOrderItems === 'function') refreshOrderItems();
                    if (typeof updateSummary === 'function' && data.order) updateSummary(data.order);
                    if (typeof showToast === 'function') showToast(data.message, 'success');
                    this.variantModalSaving = false;
                    this.showVariantModal = false;
                    this.variantModalItem = null;
                    this.variantModalPick = '';
                    this.variantModalSelections = [];
                    this.variantModalOrderItemId = null;
                    this.variantModalOriginalVariantId = null;
                    this.variantModalLineQty = 1;
                    this.variantModalError = '';
                    document.documentElement.classList.remove('overflow-hidden');
                } else if (data.errors?.item_variant_id?.[0]) {
                    this.variantModalError = data.errors.item_variant_id[0];
                } else if (typeof showToast === 'function') {
                    showToast(data.message || 'Could not change variant', 'error');
                }
            } catch (e) {
                if (typeof showToast === 'function') showToast('Network error', 'error');
            } finally {
                this.variantModalSaving = false;
            }
        },
    };
}
</script>
<div id="orderApp"
     x-data="orderPageData()"
     @fulfillment-pickup-at-changed.window="pickupAt = $event.detail || ''"
     @fulfillment-delivery-at-changed.window="deliveryAt = $event.detail || ''"
@order-item-removed.window="syncItemRemoved($event.detail.itemId)"
@order-item-updated.window="syncItemUpdated($event.detail.itemId, $event.detail.quantity, $event.detail.billing_units)"
@order-emptied.window="syncAllRemoved()"
@order-line-edit.window="openLineEdit($event.detail)"
@order-line-change-variant.window="openLineVariantChange($event.detail)"
@keydown.escape.window="if (lineEditOpen) closeLineEdit()"
>
    
    <header class="mb-3 sm:mb-4 md:mb-5">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
            <div class="flex min-w-0 flex-1 items-center gap-3">
                <a href="{{ route('vendor.orders.index') }}"
                   class="inline-flex shrink-0 items-center gap-1.5 text-sm font-medium text-gray-600 transition hover:text-emerald-700 [touch-action:manipulation]">
                    <i class="fas fa-arrow-left text-xs" aria-hidden="true"></i>
                    <span class="hidden sm:inline">{{ __('vendor.back') }}</span>
                </a>
                <div class="min-w-0 flex-1 border-l border-gray-200 pl-3 sm:pl-4">
                    <p class="truncate font-mono text-sm font-semibold leading-tight text-gray-700">
                        {{ $order->order_number ?: '—' }}
                    </p>
                    <p class="truncate text-sm font-bold leading-tight text-gray-900 sm:text-base">
                        {{ $order->event_name ?: '—' }}
                    </p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-1.5 overflow-x-auto pb-0.5 [-ms-overflow-style:none] [scrollbar-width:none] sm:flex-wrap sm:justify-end sm:overflow-visible sm:pb-0 [&::-webkit-scrollbar]:hidden">
                @if(! $orderReadOnly && in_array($order->status, ['pending', 'confirmed'], true))
                    <span id="rs-header-delivered-wrap" class="inline-flex shrink-0">
                        @if($order->delivered_at)
                            <button type="button"
                                    class="rs-btn-deliver-clear inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-lg border border-teal-200 bg-white px-3 py-2 text-xs font-semibold text-teal-900 transition hover:bg-teal-50 sm:min-h-[44px] sm:gap-2 sm:rounded-xl sm:px-4 sm:py-2.5 sm:text-sm"
                                    onclick="openRentalClearConfirm('delivered', this)">
                                <i class="fas fa-undo text-xs" aria-hidden="true"></i><span class="hidden sm:inline">{{ __('vendor.clear_delivered') }}</span>
                            </button>
                        @else
                            <button type="button"
                                    class="rs-btn-deliver-mark inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-teal-700 sm:min-h-[44px] sm:gap-2 sm:rounded-xl sm:px-4 sm:py-2.5 sm:text-sm"
                                    onclick="openMarkDeliveredModal(this)">
                                <i class="fas fa-truck text-xs" aria-hidden="true"></i>{{ __('vendor.mark_delivered') }}
                            </button>
                        @endif
                    </span>
                    <span id="rs-header-returned-wrap" class="inline-flex shrink-0">
                        @if($order->returned_at)
                            <button type="button"
                                    class="rs-btn-return-clear inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-lg border border-teal-200 bg-white px-3 py-2 text-xs font-semibold text-teal-900 transition hover:bg-teal-50 sm:min-h-[44px] sm:gap-2 sm:rounded-xl sm:px-4 sm:py-2.5 sm:text-sm"
                                    onclick="openRentalClearConfirm('returned', this)">
                                <i class="fas fa-undo text-xs" aria-hidden="true"></i><span class="hidden sm:inline">{{ __('vendor.clear_returned') }}</span><span class="sm:hidden">{{ __('vendor.returned_status') }}</span>
                            </button>
                        @else
                            <button type="button"
                                    class="rs-btn-return-mark inline-flex min-h-[40px] items-center justify-center gap-1.5 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-teal-700 sm:min-h-[44px] sm:gap-2 sm:rounded-xl sm:px-4 sm:py-2.5 sm:text-sm"
                                    onclick="openMarkReturnedModal(this)">
                                <i class="fas fa-rotate-left text-xs" aria-hidden="true"></i>{{ __('vendor.mark_returned') }}
                            </button>
                        @endif
                    </span>
                @endif
                @if(! $orderReadOnly)
                    <button type="button" onclick="openAddPaymentModal()"
                            class="inline-flex min-h-[40px] shrink-0 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 sm:min-h-[44px] sm:rounded-xl sm:px-4 sm:py-2.5 sm:text-sm">
                        <i class="fas fa-wallet text-xs" aria-hidden="true"></i>{{ __('vendor.order_show_action_pay') }}
                    </button>
                    <button type="button" @click="showAddItem = true"
                            class="inline-flex min-h-[40px] shrink-0 items-center justify-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-100 sm:min-h-[44px] sm:rounded-xl sm:px-4 sm:py-2.5 sm:text-sm">
                        <i class="fas fa-plus text-xs" aria-hidden="true"></i>{{ __('vendor.add_item') }}
                    </button>
                @endif
                <a href="{{ route('vendor.orders.invoice.download', $order) }}"
                   class="inline-flex min-h-[40px] shrink-0 items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-800 transition hover:bg-gray-50 sm:min-h-[44px] sm:rounded-xl sm:px-4 sm:py-2.5 sm:text-sm">
                    <i class="fas fa-download text-xs text-emerald-700" aria-hidden="true"></i>{{ __('vendor.download_invoice') }}
                </a>
                <div class="relative shrink-0">
                    <button type="button"
                            @click="headerMoreOpen = !headerMoreOpen"
                            class="inline-flex min-h-[40px] min-w-[40px] items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 transition hover:bg-gray-50 sm:min-h-[44px] sm:min-w-[44px] sm:rounded-xl"
                            :aria-expanded="headerMoreOpen"
                            aria-label="{{ __('vendor.order_show_more_actions') }}">
                        <i class="fas fa-ellipsis-vertical text-sm" aria-hidden="true"></i>
                    </button>
                    <div x-show="headerMoreOpen"
                         x-cloak
                         @click.outside="headerMoreOpen = false"
                         x-transition
                         class="absolute right-0 top-full z-40 mt-1.5 w-52 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-lg ring-1 ring-black/5">
                        <a href="{{ route('vendor.orders.print', ['order' => $order, 'autoprint' => 1]) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="flex w-full items-center gap-2.5 px-3.5 py-2.5 text-left text-sm font-medium text-gray-800 transition hover:bg-gray-50">
                            <i class="fas fa-file-invoice w-4 text-center text-gray-500" aria-hidden="true"></i>{{ __('vendor.print_quote') }}
                        </a>
                        @if(! $orderReadOnly)
                            <button type="button" @click="headerMoreOpen = false; openExtraChargeModal()"
                                    class="flex w-full items-center gap-2.5 px-3.5 py-2.5 text-left text-sm font-medium text-gray-800 transition hover:bg-gray-50">
                                <i class="fas fa-plus-circle w-4 text-center text-amber-600" aria-hidden="true"></i>{{ __('vendor.add_extra_charge') }}
                            </button>
                            <button type="button" @click="headerMoreOpen = false; openDiscountModal()"
                                    class="flex w-full items-center gap-2.5 px-3.5 py-2.5 text-left text-sm font-medium text-gray-800 transition hover:bg-gray-50">
                                <i class="fas fa-tag w-4 text-center text-emerald-600" aria-hidden="true"></i>{{ __('vendor.add_discount') }}
                            </button>
                            <button type="button" @click="headerMoreOpen = false; openCouponModal()"
                                    class="flex w-full items-center gap-2.5 px-3.5 py-2.5 text-left text-sm font-medium text-gray-800 transition hover:bg-gray-50">
                                <i class="fas fa-ticket-alt w-4 text-center text-emerald-600" aria-hidden="true"></i>{{ __('vendor.add_coupon') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>

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

    @if($orderReadOnly)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 shadow-sm ring-1 ring-amber-100">
            {{ __('vendor.order_readonly_notice') }}
        </div>
    @endif

    @php
        $stPanel = $order->status;
        $orderNextStatusesPanel = $order->allowedNextStatuses();
        $tzRental = config('app.timezone');
        $fmtRental = fn ($dt) => $dt ? $dt->copy()->timezone($tzRental)->format('M j, Y g:i A') : null;
        $showRentalHandoff = in_array($stPanel, ['pending', 'confirmed', 'completed'], true);
        $deliveredUnitsTotal = 0;
        $returnedUnitsTotal = 0;
        $orderUnitsTotal = 0;
        foreach ($order->items as $rentalLine) {
            $lineQty = max(1, (int) $rentalLine->quantity);
            $orderUnitsTotal += $lineQty;
            if ($rentalLine->delivered_at) {
                $deliveredUnitsTotal += $lineQty;
            }
            $returnedUnitsTotal += min($lineQty, max(0, (int) ($rentalLine->returned_qty ?? 0)));
        }
        $deliveredUnitsCountLabel = $deliveredUnitsTotal > 0
            ? __('vendor.delivered_units_count', ['delivered' => $deliveredUnitsTotal, 'total' => $orderUnitsTotal])
            : null;
        $returnedUnitsCountLabel = $returnedUnitsTotal > 0
            ? __('vendor.returned_units_count', ['returned' => $returnedUnitsTotal, 'total' => $orderUnitsTotal])
            : null;
        $statusStepIcons = [
            'pending' => 'fa-clock',
            'confirmed' => 'fa-check-circle',
            'completed' => 'fa-circle-check',
            'cancelled' => 'fa-ban',
        ];
        $statusAccentBorder = [
            'pending' => 'border-l-amber-400',
            'confirmed' => 'border-l-teal-500',
            'completed' => 'border-l-emerald-500',
            'cancelled' => 'border-l-red-500',
        ];
        $statusIconBg = [
            'pending' => 'bg-amber-100 text-amber-700',
            'confirmed' => 'bg-teal-100 text-teal-700',
            'completed' => 'bg-emerald-100 text-emerald-700',
            'cancelled' => 'bg-red-100 text-red-700',
        ];
        $statusHints = [
            'pending' => __('vendor.order_status_pending_hint'),
            'confirmed' => __('vendor.order_status_confirmed_hint'),
            'completed' => __('vendor.order_status_completed_hint'),
            'cancelled' => __('vendor.order_status_cancelled_hint'),
        ];
        $statusFlow = ['pending', 'confirmed', 'completed'];
        $currentFlowIndex = in_array($stPanel, $statusFlow, true)
            ? array_search($stPanel, $statusFlow, true)
            : false;
        $completionChecklist = $order->completionChecklist();
    @endphp

    <div class="mx-auto max-w-6xl">
    <div class="grid grid-cols-1 gap-3 sm:gap-4 lg:grid-cols-3 lg:gap-6 lg:pb-0">

        <div class="order-1 space-y-3 sm:space-y-4 lg:col-span-2 lg:space-y-4">
            @if($orderReadOnly)
                <div inert class="select-none space-y-3 opacity-[0.94] sm:space-y-4 lg:space-y-4">
            @endif

            {{-- 1. Order & rental status --}}
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100" aria-labelledby="order-status-section-title">
                <div class="space-y-3 p-3 sm:space-y-4 sm:p-4" id="order-rental-status-panel">
                    @if($stPanel !== 'cancelled')
                        <div class="rounded-lg border border-gray-100 bg-gray-50/60 px-2 py-3 sm:px-3">
                            <div class="flex items-start">
                                @foreach($statusFlow as $stepIndex => $stepKey)
                                    @php
                                        $isDone = $currentFlowIndex !== false && $stepIndex < $currentFlowIndex;
                                        $isCurrent = $stepKey === $stPanel;
                                        $stepDotClass = $isCurrent
                                            ? 'bg-slate-800 text-white ring-4 ring-slate-200'
                                            : ($isDone ? 'bg-emerald-500 text-white' : 'bg-white text-gray-400 ring-2 ring-gray-200');
                                    @endphp
                                    <div class="flex min-w-0 flex-1 flex-col items-center gap-1.5">
                                        <div class="flex w-full items-center">
                                            @if($stepIndex > 0)
                                                <div class="h-0.5 flex-1 rounded-full {{ $isDone || $isCurrent ? 'bg-emerald-400' : 'bg-gray-200' }}"></div>
                                            @endif
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs {{ $stepDotClass }}">
                                                @if($isDone)
                                                    <i class="fas fa-check" aria-hidden="true"></i>
                                                @else
                                                    <i class="fas {{ $statusStepIcons[$stepKey] ?? 'fa-circle' }} text-[11px]" aria-hidden="true"></i>
                                                @endif
                                            </div>
                                            @if($stepIndex < count($statusFlow) - 1)
                                                <div class="h-0.5 flex-1 rounded-full {{ $isDone ? 'bg-emerald-400' : 'bg-gray-200' }}"></div>
                                            @endif
                                        </div>
                                        <span class="max-w-[4.5rem] text-center text-[10px] font-semibold leading-tight sm:max-w-none sm:text-[11px] {{ $isCurrent ? 'text-slate-900' : ($isDone ? 'text-emerald-700' : 'text-gray-400') }}">
                                            {{ __('vendor.'.$stepKey) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm border-l-4 {{ $statusAccentBorder[$stPanel] ?? 'border-l-gray-300' }} sm:p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('vendor.order_status_current') }}</p>
                        <div class="mt-2 flex items-start gap-3">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $statusIconBg[$stPanel] ?? 'bg-gray-100 text-gray-600' }}">
                                <i class="fas {{ $statusStepIcons[$stPanel] ?? 'fa-circle-info' }} text-lg" aria-hidden="true"></i>
                            </div>
                            <div class="min-w-0 flex-1 pt-0.5">
                                <p id="order-status-section-title" class="text-lg font-bold leading-tight text-gray-900">{{ __('vendor.'.$stPanel) }}</p>
                                <p class="mt-1 text-xs leading-relaxed text-gray-500">{{ $statusHints[$stPanel] ?? '' }}</p>
                            </div>
                        </div>
                    </div>

                    @if(count($orderNextStatusesPanel) > 0 && ! $orderReadOnly)
                        @php
                            $primaryStatusAction = match ($stPanel) {
                                'pending' => 'confirmed',
                                'confirmed' => 'completed',
                                default => null,
                            };
                        @endphp
                        <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/50 p-3.5 sm:p-4">
                            <p class="mb-3 text-xs font-semibold text-gray-700">
                                <i class="fas fa-hand-pointer mr-1.5 text-emerald-600" aria-hidden="true"></i>
                                {{ __('vendor.order_status_next_steps') }}
                            </p>
                            <div class="flex flex-col gap-2 sm:flex-row">
                                @if($primaryStatusAction && in_array($primaryStatusAction, $orderNextStatusesPanel, true))
                                    @if($primaryStatusAction === 'completed')
                                        <button type="button"
                                                onclick="openCompleteOrderModal()"
                                                class="flex w-full min-h-[44px] items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-emerald-700 active:scale-[0.99] sm:flex-1">
                                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                                            {{ __('vendor.order_status_complete_order') }}
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('vendor.orders.update-status', $order) }}" class="w-full sm:flex-1">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="{{ $primaryStatusAction }}">
                                            <button type="submit"
                                                    class="flex w-full min-h-[44px] items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-emerald-700 active:scale-[0.99]">
                                                <i class="fas fa-check-circle" aria-hidden="true"></i>
                                                {{ __('vendor.order_status_confirm_order') }}
                                            </button>
                                        </form>
                                    @endif
                                @endif
                                @if(in_array('cancelled', $orderNextStatusesPanel, true))
                                    <form method="POST"
                                          action="{{ route('vendor.orders.update-status', $order) }}"
                                          class="w-full sm:flex-1"
                                          onsubmit="return confirm(@json(__('vendor.order_status_cancel_confirm')));">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit"
                                                class="flex w-full min-h-[44px] items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-3 text-sm font-medium text-red-600 transition hover:bg-red-50 active:scale-[0.99]">
                                            <i class="fas fa-times-circle" aria-hidden="true"></i>
                                            {{ __('vendor.order_status_cancel_order') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @elseif($orderReadOnly)
                        <p class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2.5 text-xs leading-relaxed text-gray-500">
                            <i class="fas fa-lock mr-1.5 text-gray-400" aria-hidden="true"></i>
                            {{ __('vendor.order_edit_not_allowed_locked') }}
                        </p>
                    @endif
                    @error('status')
                        <p class="text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                    @if($showRentalHandoff)
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border border-teal-100/90 bg-teal-50/40 p-3 ring-1 ring-teal-100/60">
                            <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-teal-900/85">{{ __('vendor.delivered_status') }}</p>
                            <p id="rs-delivered-summary" class="text-xs font-semibold leading-snug text-gray-900">
                                @if($order->delivered_at)
                                    {{ $fmtRental($order->delivered_at) }}
                                @elseif($deliveredUnitsCountLabel)
                                    {{ $deliveredUnitsCountLabel }}
                                @else
                                    {{ __('vendor.not_delivered_yet') }}
                                @endif
                            </p>
                            <p id="rs-delivered-units" class="mt-0.5 text-[11px] font-medium leading-snug text-teal-900/80 {{ ($order->delivered_at && $deliveredUnitsCountLabel) ? '' : 'hidden' }}">{{ ($order->delivered_at && $deliveredUnitsCountLabel) ? $deliveredUnitsCountLabel : '' }}</p>
                            <div class="mt-2 hidden flex-wrap gap-2" id="rs-delivered-actions" aria-hidden="true"></div>
                        </div>
                        <div class="rounded-lg border border-teal-100/90 bg-teal-50/40 p-3 ring-1 ring-teal-100/60">
                            <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-teal-900/85">{{ __('vendor.returned_status') }}</p>
                            <p id="rs-returned-summary" class="text-xs font-semibold leading-snug text-gray-900">
                                @if($order->returned_at)
                                    {{ $fmtRental($order->returned_at) }}
                                @elseif($returnedUnitsCountLabel)
                                    {{ $returnedUnitsCountLabel }}
                                @else
                                    {{ __('vendor.not_returned_yet') }}
                                @endif
                            </p>
                            <p id="rs-returned-units" class="mt-0.5 text-[11px] font-medium leading-snug text-teal-900/80 {{ ($order->returned_at && $returnedUnitsCountLabel) ? '' : 'hidden' }}">{{ ($order->returned_at && $returnedUnitsCountLabel) ? $returnedUnitsCountLabel : '' }}</p>
                            <div class="mt-2 hidden flex-wrap gap-2" id="rs-returned-actions" aria-hidden="true"></div>
                        </div>
                    </div>
                    @endif
                </div>
            </section>

            {{-- Customer, booking & fulfillment --}}
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100">
                <div class="p-3 sm:p-4">
                    <div class="flex min-w-0 gap-3 rounded-lg border border-gray-100 bg-gray-50/90 p-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200/50">
                            <i class="fas fa-user text-sm" aria-hidden="true"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold leading-tight text-gray-900">{{ $order->customer?->name ?? __('vendor.order_customer_unavailable') }}</p>
                            <p class="truncate text-xs text-gray-600">{{ $order->customer?->mobile ?? '—' }}</p>
                            @if($order->customer?->email)
                                <p class="truncate text-xs text-gray-500">{{ $order->customer->email }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-3 border-t border-gray-100 p-3 sm:items-center sm:p-4">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-100 text-teal-700 ring-1 ring-teal-200/50">
                        <i class="fas fa-calendar text-sm" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0 flex-1" data-booking-dates>
                        @if($order->start_at && $order->end_at)
                            <p class="text-sm font-semibold leading-snug text-gray-900">
                                {{ $order->start_at->format('M j, g:i A') }}
                                <span class="mx-0.5 font-normal text-gray-400">→</span>
                                {{ $order->end_at->format('M j, Y g:i A') }}
                            </p>
                        @else
                            <p class="text-sm italic text-gray-500">{{ __('vendor.not_specified') }}</p>
                        @endif
                    </div>
                    @if(!$orderReadOnly)
                        <button type="button"
                                onclick="openEditCartModal()"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            <i class="fas fa-edit text-xs" aria-hidden="true"></i>{{ __('vendor.edit') }}
                        </button>
                    @endif
                </div>

                <div class="flex items-start gap-3 border-t border-gray-100 p-3 sm:items-center sm:gap-4 sm:p-4">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-orange-700 ring-1 ring-orange-200/60">
                        <i class="fas fa-truck text-sm" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-gray-900" x-text="fulfillmentSummaryPrimary()"></p>
                        <p class="mt-0.5 line-clamp-2 text-xs leading-snug text-gray-600" x-text="fulfillmentSummarySecondary()"></p>
                    </div>
                    <button type="button"
                            x-show="!readOnly"
                            x-cloak
                            @click="openFulfillmentModal()"
                            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-orange-200 bg-white px-3 py-2 text-xs font-semibold text-orange-800 transition hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-orange-500/30 sm:text-sm">
                        <i class="fas fa-pen" aria-hidden="true"></i>
                        {{ __('vendor.edit') }}
                    </button>
                </div>
            </section>

            {{-- Fulfillment edit modal --}}
            <div x-show="showFulfillmentModal"
                 x-cloak
                 class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center"
                 role="dialog"
                 aria-modal="true"
                 @keydown.escape.window="showFulfillmentModal && !savingFulfillment && cancelFulfillmentModal()">
                <div class="absolute inset-0 bg-black/40" @click="!savingFulfillment && cancelFulfillmentModal()" aria-hidden="true"></div>
                <div class="relative z-10 flex max-h-[min(92dvh,40rem)] w-full max-w-2xl flex-col rounded-t-2xl border border-gray-200 bg-white shadow-2xl sm:rounded-2xl"
                     @click.stop>
                    <div class="shrink-0 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-amber-50 px-4 py-4 sm:px-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-600 text-white shadow-sm">
                                    <i class="fas fa-truck" aria-hidden="true"></i>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-base font-bold text-gray-900 sm:text-lg">{{ __('vendor.fulfillment_method') }}</h3>
                                    <p class="mt-0.5 text-xs text-gray-600 sm:text-sm">{{ __('vendor.fulfillment_method_help') }}</p>
                                </div>
                            </div>
                            <button type="button"
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-white hover:text-gray-800"
                                    @click="!savingFulfillment && cancelFulfillmentModal()"
                                    aria-label="{{ __('vendor.modal_close_aria') }}">
                                <i class="fas fa-times" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-4 sm:px-5 sm:py-5">
                        @include('vendor.orders.partials.order-fulfillment-form-body', [
                            'order' => $order,
                            'prefix' => 'fulfillment_modal',
                        ])
                    </div>
                    <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-gray-100 bg-gray-50/80 px-4 py-3 sm:flex-row sm:justify-end sm:px-5">
                        <button type="button"
                                @click="cancelFulfillmentModal()"
                                :disabled="savingFulfillment"
                                class="inline-flex min-h-[44px] w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                            {{ __('vendor.cancel') }}
                        </button>
                        <button type="button"
                                @click="saveFulfillment()"
                                :disabled="savingFulfillment"
                                class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                            <i class="fas fa-spinner fa-spin" x-show="savingFulfillment"></i>
                            <i class="fas fa-save" x-show="!savingFulfillment"></i>
                            {{ __('vendor.save') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- 5. Items --}}
            <section class="overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100">
                <div data-items-list class="p-3 sm:p-4">
                    <p class="mb-3 text-xs font-medium text-gray-600"
                       data-items-count>{{ __('vendor.order_items_heading', ['count' => $order->items->count()]) }}</p>
                    @include('vendor.orders.partials.order-items-list', ['order' => $order, 'orderReadOnly' => $orderReadOnly])
                </div>
            </section>

            {{-- 6. Order activity --}}
            @php
                $orderActivities = \App\Support\OrderActivityTimeline::for($order);
            @endphp
            <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100" aria-labelledby="order-activity-title">
                <div class="border-b border-gray-100 bg-gradient-to-r from-slate-50 to-emerald-50/30 px-3 py-3 sm:px-4">
                    <h2 id="order-activity-title" class="text-sm font-bold text-gray-900 sm:text-base">{{ __('vendor.order_activity_title') }}</h2>
                    <p class="mt-0.5 text-xs text-gray-500">{{ __('vendor.order_activity_subtitle') }}</p>
                </div>
                <div class="p-3 sm:p-4">
                    @include('vendor.orders.partials.order-activity', ['activities' => $orderActivities])
                </div>
            </section>
            @if($orderReadOnly)
                </div>
            @endif
        </div>

        <div class="order-2 min-w-0 lg:col-span-1">
            <section class="overflow-visible rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-100 lg:sticky lg:top-4 lg:z-[5] lg:self-start">

            {{-- Price summary --}}
                <div class="space-y-3 p-3 sm:p-4">
                    @if($orderReadOnly)
                        <div inert class="select-none space-y-3 opacity-[0.94]">
                    @endif
                    {{-- 1. Rental charges --}}
                    <div>
                        <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-gray-500">{{ __('vendor.summary_section_charges') }}</p>
                        <div class="space-y-0 rounded-lg border border-gray-100 bg-gray-50/70 p-0.5">
                            <div class="flex items-center justify-between gap-2 rounded-md px-2.5 py-2">
                                <span class="text-xs text-gray-600">{{ __('vendor.sub_total') }}</span>
                                <span data-sub-total class="shrink-0 whitespace-nowrap text-xs font-semibold tabular-nums text-gray-900">₹{{ number_format($order->sub_total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Discounts & coupons --}}
                    <div>
                        <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wider text-gray-500">{{ __('vendor.summary_section_savings') }}</p>
                        <div class="space-y-1.5 rounded-lg border border-dashed border-gray-200 bg-white p-2">
                            <div id="discount-add" class="{{ $order->discount_amount > 0 ? 'hidden' : '' }}">
                                <button type="button" onclick="openDiscountModal()"
                                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border border-gray-200 bg-gray-50/80 py-2 text-xs font-semibold text-emerald-700 transition hover:border-emerald-200 hover:bg-emerald-50/80">
                                    <i class="fas fa-plus-circle text-xs"></i>
                                    <span>{{ __('vendor.add_discount') }}</span>
                                </button>
                            </div>
                            <div id="discount-applied" class="{{ $order->discount_amount > 0 ? '' : 'hidden' }}">
                                <div class="flex items-center justify-between gap-2 rounded-md bg-emerald-50/50 px-2.5 py-1.5 ring-1 ring-emerald-100/80">
                                    <div class="flex min-w-0 items-center gap-1.5">
                                        <i class="fas fa-tag shrink-0 text-emerald-600 text-[10px]"></i>
                                        <span class="truncate text-xs font-medium text-gray-800" id="discount-label">
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
                                        <span data-discount-amount class="text-xs font-semibold tabular-nums text-red-600">-₹{{ number_format($order->discount_amount, 2) }}</span>
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
                                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border border-gray-200 bg-gray-50/80 py-2 text-xs font-semibold text-emerald-800 transition hover:border-emerald-200 hover:bg-emerald-50/80">
                                    <i class="fas fa-ticket-alt text-xs"></i>
                                    <span>{{ __('vendor.add_coupon') }}</span>
                                </button>
                            </div>
                            <div id="coupon-applied" class="{{ $order->coupon_code ? '' : 'hidden' }}">
                                <div class="flex items-center justify-between gap-2 rounded-md bg-emerald-50/50 px-2.5 py-1.5 ring-1 ring-emerald-100/80">
                                    <div class="flex min-w-0 items-center gap-1.5">
                                        <i class="fas fa-ticket-alt shrink-0 text-emerald-600 text-[10px]"></i>
                                        <span class="truncate text-xs font-semibold text-emerald-900" data-coupon-code>{{ $order->coupon_code }}</span>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <span data-coupon-discount class="text-xs font-semibold tabular-nums text-red-600">-₹{{ number_format($order->coupon_discount, 2) }}</span>
                                        <button type="button" onclick="removeCoupon()"
                                                class="rounded p-1 text-red-500 transition hover:bg-red-100 hover:text-red-700"
                                                title="{{ __('vendor.remove') }} {{ __('vendor.coupon_code') }}">
                                            <i class="fas fa-times-circle text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-2 border-t border-gray-100 pt-2">
                                <span class="text-xs font-semibold text-gray-700">{{ __('vendor.total_savings') }}</span>
                                <span data-discount-total class="text-xs font-bold tabular-nums text-red-600">-₹{{ number_format($order->discount_total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    @php
                        $showDeliveryLine = (($order->fulfillment_type ?? 'pickup') === 'delivery' && (float) ($order->delivery_charge ?? 0) > 0);
                    @endphp
                    <div id="summary-delivery-charge-row"
                         class="flex items-center justify-between gap-2 rounded-lg border border-orange-100 bg-orange-50/40 px-3 py-2 text-xs {{ $showDeliveryLine ? '' : 'hidden' }}">
                        <span class="font-medium text-gray-800">{{ __('vendor.delivery_charge') }}</span>
                        <span data-delivery-charge-line class="font-bold tabular-nums text-gray-900">₹{{ number_format((float) ($order->delivery_charge ?? 0), 2) }}</span>
                    </div>

                    @php
                        $extraLines = is_array($order->extra_charges_lines) ? $order->extra_charges_lines : [];
                        $extraTotal = (float) ($order->extra_charges_total ?? 0);
                    @endphp
                    <div id="summary-extra-charges-block" class="space-y-1.5 rounded-lg border border-amber-100/90 bg-amber-50/30 px-3 py-2 text-xs {{ $extraTotal > 0 ? '' : 'hidden' }}">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-900/80">{{ __('vendor.extra_charges_label') }}</p>
                        <p class="hidden text-[10px] leading-snug text-amber-900/70 sm:block">{{ __('vendor.extra_charges_hint') }}</p>
                        <ul id="extra-charges-lines-ul" class="space-y-1 text-xs text-gray-800">
                            @foreach($extraLines as $line)
                                @if(is_array($line))
                                    <li class="flex items-start justify-between gap-2 border-b border-amber-100/80 pb-1 text-xs last:border-0 last:pb-0">
                                        <span class="min-w-0 flex-1 leading-snug">{{ $line['label'] ?? '—' }}</span>
                                        <div class="flex shrink-0 items-start gap-1.5">
                                            <span class="font-semibold tabular-nums text-gray-900">₹{{ number_format((float) ($line['amount'] ?? 0), 2) }}</span>
                                            @if(!$orderReadOnly)
                                                <button type="button"
                                                        onclick="removeExtraChargeLine({{ $loop->index }}, this)"
                                                        class="rounded p-0.5 text-red-500 transition hover:bg-red-100 hover:text-red-700"
                                                        title="{{ __('vendor.remove_extra_charge') }}">
                                                    <i class="fas fa-times-circle text-sm" aria-hidden="true"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                        <div class="flex items-center justify-between gap-2 border-t border-amber-100/90 pt-1.5 text-xs">
                            <span class="font-semibold text-amber-950">{{ __('vendor.extra_charges_label') }}</span>
                            <span data-extra-charges-total class="font-bold tabular-nums text-amber-950">₹{{ number_format($extraTotal, 2) }}</span>
                        </div>
                    </div>

                    {{-- Order total (before deposit) --}}
                    <div class="flex items-start justify-between gap-2 rounded-lg border border-slate-200 bg-slate-50/90 px-3 py-2.5">
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-800">{{ __('vendor.summary_order_total') }}</span>
                            <span class="mt-0.5 block text-[10px] leading-snug text-slate-500">{{ __('vendor.summary_order_total_hint') }}</span>
                        </div>
                        <span data-order-total class="shrink-0 whitespace-nowrap text-sm font-bold tabular-nums text-slate-900">₹{{ number_format($order->grand_total, 2) }}</span>
                    </div>
                    @if($orderReadOnly)
                        </div>
                    @endif
                </div>

            {{-- Deposit & total due --}}
                <div class="space-y-3 border-t border-gray-100 p-3 sm:p-4">
                    @if($orderReadOnly)
                        <div inert class="select-none space-y-3 opacity-[0.94]">
                    @endif
                    <div class="flex items-center justify-between gap-2 rounded-lg border border-gray-100 bg-white px-2.5 py-2.5">
                        <button type="button"
                                onclick="openSecurityDepositModal()"
                                class="inline-flex min-w-0 flex-1 items-center gap-2 text-left text-xs font-semibold text-emerald-700 transition hover:text-emerald-900">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                                <i class="fas fa-shield-alt text-[10px]"></i>
                            </span>
                            <span id="securityDepositLabel" class="truncate">{{ __('vendor.quote_security_deposit') }}</span>
                        </button>
                        <span data-security-deposit-total class="shrink-0 whitespace-nowrap text-xs font-bold tabular-nums text-gray-900">₹{{ number_format($order->security_deposit ?? 0, 2) }}</span>
                    </div>

                    <div class="rounded-lg border border-emerald-200/90 bg-gradient-to-br from-emerald-50/90 via-white to-teal-50/60 p-3 ring-1 ring-emerald-100/50">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-gray-900">{{ __('vendor.summary_total_due') }}</span>
                                <span class="mt-0.5 block text-[10px] font-medium leading-snug text-emerald-800/80">{{ __('vendor.summary_total_due_hint') }}</span>
                            </div>
                            <span data-grand-total class="shrink-0 whitespace-nowrap text-lg font-bold tabular-nums tracking-tight text-emerald-700">₹{{ number_format((float) $order->grand_total + (float) ($order->security_deposit ?? 0), 2) }}</span>
                        </div>
                    </div>
                    @if($orderReadOnly)
                        </div>
                    @endif
                </div>

            {{-- Payments --}}
                @php
                    $paymentSummary = $order->paymentSummary();
                    $showPaymentRefunds = $order->status === 'completed';
                @endphp
                <div class="space-y-3 border-t border-gray-100 p-3 sm:p-4">
                    <div class="space-y-2">
                        <div class="rounded-lg border border-emerald-100 bg-emerald-50/50 px-3 py-2.5 ring-1 ring-emerald-100/80">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-800">{{ __('vendor.order_payment_dues') }}</p>
                            <div class="mt-1.5 space-y-1 text-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-gray-600">{{ __('vendor.summary_order_total') }}</span>
                                    <span data-order-payment-total class="shrink-0 font-semibold tabular-nums text-gray-900">₹{{ number_format($order->grand_total, 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-gray-600">{{ __('vendor.paid') }}</span>
                                    <span data-order-payment-paid class="shrink-0 font-semibold tabular-nums text-emerald-700">₹{{ number_format($paymentSummary['order_paid'], 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2 border-t border-emerald-100/80 pt-1">
                                    <span class="font-bold text-gray-900">{{ __('vendor.balance_due') }}</span>
                                    <span data-order-payment-due class="shrink-0 text-sm font-bold tabular-nums {{ $paymentSummary['order_due'] > 0 ? 'text-red-600' : 'text-emerald-700' }}">₹{{ number_format($paymentSummary['order_due'], 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-teal-100 bg-teal-50/50 px-3 py-2.5 ring-1 ring-teal-100/80">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-teal-800">{{ __('vendor.security_deposit_dues') }}</p>
                            <div class="mt-1.5 space-y-1 text-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-gray-600">{{ __('vendor.quote_security_deposit') }}</span>
                                    <span data-deposit-payment-total class="shrink-0 font-semibold tabular-nums text-gray-900">₹{{ number_format($order->security_deposit ?? 0, 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-gray-600">{{ __('vendor.paid') }}</span>
                                    <span data-deposit-payment-paid class="shrink-0 font-semibold tabular-nums text-emerald-700">₹{{ number_format($paymentSummary['deposit_paid'], 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-2 border-t border-teal-100/80 pt-1">
                                    <span class="font-bold text-gray-900">{{ __('vendor.balance_due') }}</span>
                                    <span data-deposit-payment-due class="shrink-0 text-sm font-bold tabular-nums {{ $paymentSummary['deposit_due'] > 0 ? 'text-red-600' : 'text-emerald-700' }}">₹{{ number_format($paymentSummary['deposit_due'], 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50/90 px-3 py-2 text-xs ring-1 ring-slate-100">
                            <span class="font-bold text-gray-900">{{ __('vendor.summary_total_due') }}</span>
                            <span data-balance-due class="shrink-0 whitespace-nowrap text-sm font-bold tabular-nums {{ $paymentSummary['total_due'] > 0 ? 'text-red-600' : 'text-emerald-700' }}">₹{{ number_format($paymentSummary['total_due'], 2) }}</span>
                        </div>
                        <div class="hidden">
                            <span data-paid-amount>₹{{ number_format($order->paid_amount, 2) }}</span>
                        </div>

                        @if($showPaymentRefunds)
                            <div class="rounded-lg border border-rose-100 bg-rose-50/40 px-3 py-2.5 ring-1 ring-rose-100/80" data-refund-summary-section>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-rose-800">{{ __('vendor.order_refunds_title') }}</p>
                                <div class="mt-1.5 space-y-1 text-xs">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-gray-600">{{ __('vendor.refund_order_amount_label') }}</span>
                                        <span data-refund-order-total class="shrink-0 font-semibold tabular-nums text-rose-700">₹{{ number_format($paymentSummary['refund_order'], 2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-gray-600">{{ __('vendor.refund_deposit_amount_label') }}</span>
                                        <span data-refund-deposit-total class="shrink-0 font-semibold tabular-nums text-rose-700">₹{{ number_format($paymentSummary['refund_deposit'], 2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-2 border-t border-rose-100/80 pt-1">
                                        <span class="font-bold text-gray-900">{{ __('vendor.refund_total_label') }}</span>
                                        <span data-refund-total class="shrink-0 text-sm font-bold tabular-nums text-rose-700">₹{{ number_format($paymentSummary['refund_total'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if(! $orderReadOnly)
                        <div class="mt-0.5">
                            <button type="button" onclick="openAddPaymentModal()"
                                    class="inline-flex min-h-[44px] w-full items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2.5 text-xs font-semibold text-white transition hover:bg-emerald-700 active:scale-[0.99]">
                                <i class="fas fa-wallet"></i>{{ __('vendor.new_payment') }}
                            </button>
                        </div>
                        @endif

                        @php
                            $paymentRows = is_array($order->payment_detail) ? $order->payment_detail : [];
                        @endphp
                        <div id="payment-history-section" class="mt-2 space-y-1.5">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-500">{{ __('vendor.payment_history_title') }}</p>
                            <div id="payment-history-empty"
                                 class="{{ count($paymentRows) ? 'hidden' : '' }} rounded-lg border border-dashed border-gray-200 bg-gray-50/90 px-2.5 py-2 text-center text-[11px] leading-snug text-gray-500">
                                {{ __('vendor.payment_history_empty') }}
                            </div>
                            <ul id="payment-history-list" class="space-y-1.5 {{ count($paymentRows) ? '' : 'hidden' }}">
                                @foreach($paymentRows as $idx => $p)
                                    @php
                                        $pIsRefund = (($p['entry_kind'] ?? 'payment') === 'refund');
                                    @endphp
                                    <li class="flex items-start gap-2 rounded-lg border border-gray-100 bg-white px-2.5 py-2 text-xs shadow-sm ring-1 ring-gray-100/80">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                                @if($pIsRefund)
                                                    <span class="text-xs font-bold tabular-nums text-rose-700">−₹{{ number_format((float) ($p['amount'] ?? 0), 2) }}</span>
                                                @else
                                                    <span class="text-xs font-bold tabular-nums text-gray-900">₹{{ number_format((float) ($p['amount'] ?? 0), 2) }}</span>
                                                @endif
                                                @if(($p['payment_for'] ?? '') === 'security_deposit')
                                                    <span class="inline-flex rounded-md bg-teal-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-700 ring-1 ring-teal-100">{{ __('vendor.payment_for_deposit_short') }}</span>
                                                @else
                                                    <span class="inline-flex rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-100">{{ __('vendor.payment_for_order_short') }}</span>
                                                @endif
                                                @if($pIsRefund)
                                                    <span class="inline-flex rounded-md bg-rose-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-rose-700 ring-1 ring-rose-100">{{ __('vendor.label_refund') }}</span>
                                                @endif
                                            </div>
                                            <p class="mt-0.5 text-[11px] text-gray-500">
                                                @php
                                                    $m = $p['method'] ?? '';
                                                    $methodLabels = ['card' => 'Card', 'cash' => 'Cash', 'upi' => 'UPI', 'bank_transfer' => 'Bank transfer', 'wallet' => 'Wallet', 'other' => 'Other', 'settlement' => __('vendor.payment_method_settlement')];
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
                </div>
            </section>
        </div>
    </div>
    </div>

    <!-- New Payment Modal (step 1: select payment for) -->
                    <div id="addPaymentModal" class="fixed inset-0 z-[80] hidden">
                        <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeAddPaymentModal()"></div>
                        <div class="fixed inset-0 flex items-center justify-center p-4">
                            <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all" onclick="event.stopPropagation()">
                                <!-- Header -->
                                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-t-xl">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 flex items-center justify-center bg-emerald-600 rounded-lg">
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
                                                    class="w-full rounded-lg border-2 border-gray-200 px-4 py-3 text-left transition-all hover:border-emerald-400 hover:bg-emerald-50/60">
                                                <div class="flex items-start gap-3">
                                                    <i class="fas fa-file-invoice-dollar mt-0.5 text-emerald-600"></i>
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-800">Order amount</p>
                                                        <p class="text-xs text-gray-500">Collect payment against order total.</p>
                                                    </div>
                                                </div>
                                            </button>

                                            <button type="button" onclick="openNewPaymentDueModal('security_deposit')"
                                                    class="w-full rounded-lg border-2 border-gray-200 px-4 py-3 text-left transition-all hover:border-emerald-400 hover:bg-emerald-50/60">
                                                <div class="flex items-start gap-3">
                                                    <i class="fas fa-shield-alt mt-0.5 text-emerald-600"></i>
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-800">Security deposit</p>
                                                        <p class="text-xs text-gray-500">Collect payment for refundable security deposit.</p>
                                                    </div>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-3">{{ __('vendor.payment_step_refunds') }}</label>
                                        <div class="grid grid-cols-1 gap-3">
                                            <button type="button" onclick="openNewPaymentDueModal('refund_order_amount')"
                                                    class="w-full rounded-lg border-2 border-rose-100 bg-rose-50/40 px-4 py-3 text-left transition-all hover:border-rose-300 hover:bg-rose-50/80">
                                                <div class="flex items-start gap-3">
                                                    <i class="fas fa-undo-alt mt-0.5 text-rose-600"></i>
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-800">{{ __('vendor.label_refund') }} · {{ __('vendor.payment_for_order_short') }}</p>
                                                        <p class="text-xs text-gray-600">{{ __('vendor.refund_order_blurb') }}</p>
                                                    </div>
                                                </div>
                                            </button>
                                            <button type="button" onclick="openNewPaymentDueModal('refund_security_deposit')"
                                                    class="w-full rounded-lg border-2 border-rose-100 bg-rose-50/40 px-4 py-3 text-left transition-all hover:border-rose-300 hover:bg-rose-50/80">
                                                <div class="flex items-start gap-3">
                                                    <i class="fas fa-shield-alt mt-0.5 text-rose-600"></i>
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-800">{{ __('vendor.label_refund') }} · {{ __('vendor.payment_for_deposit_short') }}</p>
                                                        <p class="text-xs text-gray-600">{{ __('vendor.refund_deposit_blurb') }}</p>
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
                                <div class="rounded-t-xl border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-emerald-50 px-6 py-4">
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
                                        <p class="text-center text-xs font-semibold uppercase tracking-wide text-emerald-700">Order amount</p>
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
                                                       class="w-full rounded-lg border border-gray-300 py-2.5 pl-8 pr-3 text-sm font-semibold tabular-nums focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                                                       placeholder="0.00">
                                            </div>
                                            <p class="mt-1 text-left text-xs text-gray-500">Change the amount to collect for this order (max due).</p>
                                        </div>
                                        <div class="mt-4">
                                            <label for="npOrderPaymentDate" class="mb-1.5 block text-left text-sm font-semibold text-gray-700">Payment date</label>
                                            <input type="date" id="npOrderPaymentDate"
                                                   onchange="npSyncPayButtonState()"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-emerald-500">
                                        </div>
                                    </div>
                                    <div id="npSectionSecurity" class="hidden rounded-xl border border-gray-100 bg-gray-50/90 p-4">
                                        <p class="text-center text-xs font-semibold uppercase tracking-wide text-emerald-700">Security deposit</p>
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
                                                       class="w-full rounded-lg border border-gray-300 py-2.5 pl-8 pr-3 text-sm font-semibold tabular-nums focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                                                       placeholder="0.00">
                                            </div>
                                            <p class="mt-1 text-left text-xs text-gray-500">Change the amount to collect for security deposit (max due).</p>
                                        </div>
                                        <div class="mt-4">
                                            <label for="npSdPaymentDate" class="mb-1.5 block text-left text-sm font-semibold text-gray-700">Payment date</label>
                                            <input type="date" id="npSdPaymentDate"
                                                   onchange="npSyncPayButtonState()"
                                                   class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-emerald-500">
                                        </div>
                                    </div>

                                    <div id="npPaymentMethodSection" class="mt-8 border-t border-gray-100 pt-6">
                                        <p class="mb-4 text-sm font-semibold text-gray-800">Payment method</p>
                                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="np_payment_method" value="card" class="peer sr-only" onchange="npOnPaymentMethodPick()">
                                                <div class="flex min-h-[48px] items-center justify-center rounded-lg border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-semibold text-gray-900 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50">
                                                    Card
                                                </div>
                                            </label>
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="np_payment_method" value="cash" class="peer sr-only" onchange="npOnPaymentMethodPick()">
                                                <div class="flex min-h-[48px] items-center justify-center rounded-lg border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-semibold text-gray-900 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50">
                                                    Cash
                                                </div>
                                            </label>
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="np_payment_method" value="upi" class="peer sr-only" onchange="npOnPaymentMethodPick()">
                                                <div class="flex min-h-[48px] items-center justify-center rounded-lg border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-semibold text-gray-900 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50">
                                                    UPI
                                                </div>
                                            </label>
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="np_payment_method" value="bank_transfer" class="peer sr-only" onchange="npOnPaymentMethodPick()">
                                                <div class="flex min-h-[48px] items-center justify-center rounded-lg border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-semibold text-gray-900 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50">
                                                    Bank Transfer
                                                </div>
                                            </label>
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="np_payment_method" value="wallet" class="peer sr-only" onchange="npOnPaymentMethodPick()">
                                                <div class="flex min-h-[48px] items-center justify-center rounded-lg border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-semibold text-gray-900 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50">
                                                    Wallet
                                                </div>
                                            </label>
                                            <label class="relative cursor-pointer">
                                                <input type="radio" name="np_payment_method" value="other" class="peer sr-only" onchange="npOnPaymentMethodPick()">
                                                <div class="flex min-h-[48px] items-center justify-center rounded-lg border-2 border-gray-200 px-3 py-2.5 text-center text-sm font-semibold text-gray-900 transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50">
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

                    <div id="extraChargeModal" class="fixed inset-0 z-[88] hidden">
                        <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeExtraChargeModal()"></div>
                        <div class="fixed inset-0 flex items-center justify-center p-4">
                            <div class="relative w-full max-w-md rounded-xl bg-white shadow-2xl" onclick="event.stopPropagation()">
                                <div class="rounded-t-xl border-b border-amber-100 bg-gradient-to-r from-amber-50 to-orange-50 px-6 py-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.extra_charge_modal_title') }}</h3>
                                        <button type="button" onclick="closeExtraChargeModal()" class="rounded-lg p-2 text-gray-400 transition hover:bg-white hover:text-gray-600">
                                            <i class="fas fa-times text-xl"></i>
                                        </button>
                                    </div>
                                </div>
                                <form id="extraChargeForm" class="space-y-4 p-6" onsubmit="submitExtraCharge(event)">
                                    <div>
                                        <label for="extraChargeLabel" class="mb-1.5 block text-sm font-semibold text-gray-700">{{ __('vendor.extra_charge_label_field') }}</label>
                                        <input type="text" id="extraChargeLabel" name="label" required maxlength="200"
                                               class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm text-gray-900 focus:border-transparent focus:ring-2 focus:ring-amber-500"
                                               placeholder="{{ __('vendor.description') }}">
                                    </div>
                                    <div>
                                        <label for="extraChargeAmount" class="mb-1.5 block text-sm font-semibold text-gray-700">{{ __('vendor.extra_charge_amount_field') }}</label>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">₹</span>
                                            <input type="number" id="extraChargeAmount" name="amount" required step="0.01" min="0.01" max="999999"
                                                   class="w-full rounded-lg border border-gray-300 py-2.5 pl-8 pr-3 text-sm font-semibold tabular-nums focus:border-transparent focus:ring-2 focus:ring-amber-500"
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                                        <button type="button" onclick="closeExtraChargeModal()" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                            {{ __('vendor.cancel') }}
                                        </button>
                                        <button type="submit" id="extraChargeSubmitBtn" class="rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-amber-700">
                                            {{ __('vendor.add_extra_charge') }}
                                        </button>
                                    </div>
                                </form>
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
                    var extraChargePostUrl = @json(route('vendor.orders.extra-charges', $order));
                    @php
                        $__ecdDestroy0 = route('vendor.orders.extra-charges.destroy', ['order' => $order, 'lineIndex' => 0]);
                        $__ecdDestroyPrefix = preg_replace('#/0$#', '', $__ecdDestroy0);
                    @endphp
                    var extraChargeDeleteUrlPrefix = @json($__ecdDestroyPrefix);
                    var orderShowReadOnly = @json($orderReadOnly);
                    var removeExtraChargeTitle = @json(__('vendor.remove_extra_charge'));
                    var rentalStatusUrl = @json(route('vendor.orders.rental-status', $order));
                    var rentalStatusLabels = {
                        notDelivered: @json(__('vendor.not_delivered_yet')),
                        notReturned: @json(__('vendor.not_returned_yet')),
                    };
                    var deliveredUnitsCountTpl = @json(__('vendor.delivered_units_count', ['delivered' => ':delivered', 'total' => ':total']));
                    var returnedUnitsCountTpl = @json(__('vendor.returned_units_count', ['returned' => ':returned', 'total' => ':total']));
                    var markDeliveredAlreadyLabel = @json(__('vendor.mark_delivered_already_done'));
                    var markReturnedAlreadyLabel = @json(__('vendor.mark_returned_already_done'));
                    var returnQtyLabel = @json(__('vendor.return_qty_label'));
                    var markReturnedConfirmBase = @json(__('vendor.mark_returned_confirm'));
                    var markReturnedConfirmCountTpl = @json(__('vendor.mark_returned_confirm_count'));
                    var markDeliveredConfirmBase = @json(__('vendor.mark_delivered_confirm'));
                    var markDeliveredConfirmCountTpl = @json(__('vendor.mark_delivered_confirm_count'));
                    function applyRentalStatusToUi(rs) {
                        if (!rs) return;
                        var dText = document.getElementById('rs-delivered-summary');
                        var dUnits = document.getElementById('rs-delivered-units');
                        var rText = document.getElementById('rs-returned-summary');
                        var rUnits = document.getElementById('rs-returned-units');
                        var deliveredUnits = parseInt(rs.delivered_units, 10) || 0;
                        var totalUnits = parseInt(rs.total_units, 10) || 0;
                        var deliveredCountText = deliveredUnits > 0
                            ? deliveredUnitsCountTpl.replace(':delivered', String(deliveredUnits)).replace(':total', String(totalUnits))
                            : '';
                        if (dText) {
                            if (rs.delivered_at_display) {
                                dText.textContent = rs.delivered_at_display;
                            } else if (deliveredCountText) {
                                dText.textContent = deliveredCountText;
                            } else {
                                dText.textContent = rentalStatusLabels.notDelivered;
                            }
                        }
                        if (dUnits) {
                            if (rs.delivered_at_display && deliveredCountText) {
                                dUnits.textContent = deliveredCountText;
                                dUnits.classList.remove('hidden');
                            } else {
                                dUnits.textContent = '';
                                dUnits.classList.add('hidden');
                            }
                        }
                        var returnedUnits = parseInt(rs.returned_units, 10) || 0;
                        var returnedCountText = returnedUnits > 0
                            ? returnedUnitsCountTpl.replace(':returned', String(returnedUnits)).replace(':total', String(totalUnits))
                            : '';
                        if (rText) {
                            if (rs.returned_at_display) {
                                rText.textContent = rs.returned_at_display;
                            } else if (returnedCountText) {
                                rText.textContent = returnedCountText;
                            } else {
                                rText.textContent = rentalStatusLabels.notReturned;
                            }
                        }
                        if (rUnits) {
                            if (rs.returned_at_display && returnedCountText) {
                                rUnits.textContent = returnedCountText;
                                rUnits.classList.remove('hidden');
                            } else {
                                rUnits.textContent = '';
                                rUnits.classList.add('hidden');
                            }
                        }
                        var hasD = !!(rs.delivered_at);
                        var hasR = !!(rs.returned_at);
                        function deliveredActionButtonHtml(delivered, compact) {
                            if (delivered) {
                                return compact
                                    ? '<button type="button" class="rs-btn-deliver-clear inline-flex items-center justify-center rounded-lg border border-teal-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-teal-900 shadow-sm transition hover:bg-teal-50" onclick="openRentalClearConfirm(\'delivered\', this)">' + @json(__('vendor.clear_delivered')) + '</button>'
                                    : '<button type="button" class="rs-btn-deliver-clear inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl border border-teal-200 bg-white px-4 py-2.5 text-sm font-semibold text-teal-900 shadow-sm transition hover:bg-teal-50 sm:w-auto" onclick="openRentalClearConfirm(\'delivered\', this)"><i class="fas fa-undo" aria-hidden="true"></i>' + @json(__('vendor.clear_delivered')) + '</button>';
                            }
                            return compact
                                ? '<button type="button" class="rs-btn-deliver-mark inline-flex items-center justify-center rounded-lg bg-teal-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm transition hover:bg-teal-700" onclick="openMarkDeliveredModal(this)">' + @json(__('vendor.mark_delivered')) + '</button>'
                                : '<button type="button" class="rs-btn-deliver-mark inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700 sm:w-auto" onclick="openMarkDeliveredModal(this)"><i class="fas fa-truck" aria-hidden="true"></i>' + @json(__('vendor.mark_delivered')) + '</button>';
                        }
                        var panelWrap = document.getElementById('rs-delivered-actions');
                        var headerWrap = document.getElementById('rs-header-delivered-wrap');
                        if (panelWrap) panelWrap.innerHTML = deliveredActionButtonHtml(hasD, true);
                        if (headerWrap) headerWrap.innerHTML = deliveredActionButtonHtml(hasD, false);
                        function returnedActionButtonHtml(returned, compact) {
                            if (returned) {
                                return compact
                                    ? '<button type="button" class="rs-btn-return-clear inline-flex items-center justify-center rounded-lg border border-teal-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-teal-900 shadow-sm transition hover:bg-teal-50" onclick="openRentalClearConfirm(\'returned\', this)">' + @json(__('vendor.clear_returned')) + '</button>'
                                    : '<button type="button" class="rs-btn-return-clear inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl border border-teal-200 bg-white px-4 py-2.5 text-sm font-semibold text-teal-900 shadow-sm transition hover:bg-teal-50 sm:w-auto" onclick="openRentalClearConfirm(\'returned\', this)"><i class="fas fa-undo" aria-hidden="true"></i>' + @json(__('vendor.clear_returned')) + '</button>';
                            }
                            return compact
                                ? '<button type="button" class="rs-btn-return-mark inline-flex items-center justify-center rounded-lg bg-teal-600 px-3 py-1.5 text-[11px] font-semibold text-white shadow-sm transition hover:bg-teal-700" onclick="openMarkReturnedModal(this)">' + @json(__('vendor.mark_returned')) + '</button>'
                                : '<button type="button" class="rs-btn-return-mark inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-700 sm:w-auto" onclick="openMarkReturnedModal(this)"><i class="fas fa-rotate-left" aria-hidden="true"></i>' + @json(__('vendor.mark_returned')) + '</button>';
                        }
                        var returnPanelWrap = document.getElementById('rs-returned-actions');
                        var returnHeaderWrap = document.getElementById('rs-header-returned-wrap');
                        if (returnPanelWrap) returnPanelWrap.innerHTML = returnedActionButtonHtml(hasR, true);
                        if (returnHeaderWrap) returnHeaderWrap.innerHTML = returnedActionButtonHtml(hasR, false);
                        if (!hasD) {
                            document.querySelectorAll('[data-cart-line][data-order-item-id]').forEach(function (row) {
                                row.setAttribute('data-line-delivered', '0');
                            });
                        }
                    }
                    function escapeDeliveredModalHtml(text) {
                        var d = document.createElement('div');
                        d.textContent = text == null ? '' : String(text);
                        return d.innerHTML;
                    }
                    function collectOrderDeliveryLines() {
                        var lines = [];
                        document.querySelectorAll('[data-cart-line][data-order-item-id]').forEach(function (row) {
                            var id = parseInt(row.getAttribute('data-order-item-id'), 10);
                            if (!id) return;
                            var nameEl = row.querySelector('[data-line-name]');
                            var name = nameEl ? nameEl.textContent.trim() : ('Item #' + id);
                            var qtyEl = row.querySelector('[data-qty-display]');
                            var qty = qtyEl ? (parseInt(qtyEl.textContent, 10) || 1) : (parseInt(row.getAttribute('data-line-qty'), 10) || 1);
                            lines.push({
                                id: id,
                                name: name,
                                quantity: qty,
                                delivered: row.getAttribute('data-line-delivered') === '1',
                            });
                        });
                        return lines;
                    }
                    function openMarkDeliveredModal(triggerBtn) {
                        if (typeof orderShowReadOnly !== 'undefined' && orderShowReadOnly) return;
                        var lines = collectOrderDeliveryLines();
                        var list = document.getElementById('markDeliveredItemList');
                        var modal = document.getElementById('markDeliveredModal');
                        if (!list || !modal) return;
                        if (lines.length === 0) {
                            if (typeof showToast === 'function') showToast(@json(__('vendor.no_items_yet')), 'error');
                            return;
                        }
                        var pending = lines.filter(function (l) { return !l.delivered; });
                        if (pending.length === 0) {
                            if (typeof showToast === 'function') showToast(@json(__('vendor.mark_delivered_already_done')), 'info');
                            return;
                        }
                        var alreadyDeliveredUnits = lines.reduce(function (sum, l) {
                            return sum + (l.delivered ? (l.quantity || 0) : 0);
                        }, 0);
                        var orderUnits = lines.reduce(function (sum, l) { return sum + (l.quantity || 0); }, 0);
                        var deliverAlreadySummary = document.getElementById('markDeliveredAlreadySummary');
                        if (deliverAlreadySummary) {
                            if (alreadyDeliveredUnits > 0) {
                                deliverAlreadySummary.textContent = deliveredUnitsCountTpl
                                    .replace(':delivered', String(alreadyDeliveredUnits))
                                    .replace(':total', String(orderUnits));
                                deliverAlreadySummary.classList.remove('hidden');
                            } else {
                                deliverAlreadySummary.textContent = '';
                                deliverAlreadySummary.classList.add('hidden');
                            }
                        }
                        var html = '';
                        lines.forEach(function (line) {
                            var checked = ' checked';
                            var disabled = line.delivered ? ' disabled' : '';
                            var badge = line.delivered
                                ? '<span class="ml-1.5 inline-flex rounded bg-teal-100 px-1.5 py-0.5 text-[10px] font-semibold text-teal-800">' + escapeDeliveredModalHtml(markDeliveredAlreadyLabel) + '</span>'
                                : '';
                            html += '<label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm ring-1 ring-gray-100/80' + (line.delivered ? ' opacity-60' : ' hover:bg-teal-50/40') + '">';
                            html += '<input type="checkbox" class="mark-delivered-item-cb mt-1 h-4 w-4 shrink-0 rounded border-gray-300 text-teal-600 focus:ring-teal-500" value="' + line.id + '"' + checked + disabled + ' onchange="updateMarkDeliveredConfirmLabel()">';
                            html += '<span class="min-w-0 flex-1"><span class="text-sm font-semibold text-gray-900">' + escapeDeliveredModalHtml(line.name) + badge + '</span>';
                            html += '<span class="mt-0.5 block text-xs text-gray-500">× ' + line.quantity + '</span></span></label>';
                        });
                        list.innerHTML = html;
                        updateMarkDeliveredConfirmLabel();
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                        window.markDeliveredTriggerBtn = triggerBtn || null;
                    }
                    function updateMarkDeliveredConfirmLabel() {
                        var btn = document.getElementById('markDeliveredConfirmBtn');
                        var label = document.getElementById('markDeliveredConfirmLabel');
                        if (!btn || !label) return;
                        var n = document.querySelectorAll('.mark-delivered-item-cb:checked:not(:disabled)').length;
                        label.textContent = n > 0
                            ? markDeliveredConfirmCountTpl.replace(':count', String(n))
                            : markDeliveredConfirmBase;
                        btn.disabled = n === 0;
                    }
                    function closeMarkDeliveredModal() {
                        var modal = document.getElementById('markDeliveredModal');
                        if (modal) modal.classList.add('hidden');
                        document.body.style.overflow = '';
                        window.markDeliveredTriggerBtn = null;
                        var deliverAlreadySummary = document.getElementById('markDeliveredAlreadySummary');
                        if (deliverAlreadySummary) {
                            deliverAlreadySummary.textContent = '';
                            deliverAlreadySummary.classList.add('hidden');
                        }
                        var label = document.getElementById('markDeliveredConfirmLabel');
                        var btn = document.getElementById('markDeliveredConfirmBtn');
                        if (label) label.textContent = markDeliveredConfirmBase;
                        if (btn) btn.disabled = false;
                    }
                    function confirmMarkDelivered() {
                        var ids = [];
                        document.querySelectorAll('.mark-delivered-item-cb:checked:not(:disabled)').forEach(function (cb) {
                            var id = parseInt(cb.value, 10);
                            if (id) ids.push(id);
                        });
                        if (ids.length === 0) {
                            if (typeof showToast === 'function') showToast(@json(__('vendor.deliver_items_required')), 'error');
                            return;
                        }
                        var btn = document.getElementById('markDeliveredConfirmBtn');
                        if (btn) btn.disabled = true;
                        patchRentalStatus({ delivered: 'mark', order_item_ids: ids }, btn || window.markDeliveredTriggerBtn);
                    }
                    function collectOrderReturnLines() {
                        var lines = [];
                        document.querySelectorAll('[data-cart-line][data-order-item-id]').forEach(function (row) {
                            var id = parseInt(row.getAttribute('data-order-item-id'), 10);
                            if (!id) return;
                            var nameEl = row.querySelector('[data-line-name]');
                            var name = nameEl ? nameEl.textContent.trim() : ('Item #' + id);
                            var orderQty = parseInt(row.getAttribute('data-line-qty'), 10) || 1;
                            var returnedQty = parseInt(row.getAttribute('data-line-returned-qty'), 10) || 0;
                            var delivered = row.getAttribute('data-line-delivered') === '1';
                            var fullyReturned = row.getAttribute('data-line-returned') === '1';
                            var returnableQty = Math.max(0, orderQty - returnedQty);
                            lines.push({
                                id: id,
                                name: name,
                                orderQty: orderQty,
                                returnedQty: returnedQty,
                                returnableQty: returnableQty,
                                delivered: delivered,
                                fullyReturned: fullyReturned,
                            });
                        });
                        return lines;
                    }
                    function openMarkReturnedModal(triggerBtn) {
                        if (typeof orderShowReadOnly !== 'undefined' && orderShowReadOnly) return;
                        var lines = collectOrderReturnLines();
                        var list = document.getElementById('markReturnedItemList');
                        var modal = document.getElementById('markReturnedModal');
                        if (!list || !modal) return;
                        if (lines.length === 0) {
                            if (typeof showToast === 'function') showToast(@json(__('vendor.no_items_yet')), 'error');
                            return;
                        }
                        var pending = lines.filter(function (l) { return l.delivered && !l.fullyReturned && l.returnableQty > 0; });
                        if (pending.length === 0) {
                            if (typeof showToast === 'function') showToast(markReturnedAlreadyLabel, 'info');
                            return;
                        }
                        var alreadyReturnedUnits = lines.reduce(function (sum, l) { return sum + (l.returnedQty || 0); }, 0);
                        var orderUnits = lines.reduce(function (sum, l) { return sum + (l.orderQty || 0); }, 0);
                        var alreadySummary = document.getElementById('markReturnedAlreadySummary');
                        if (alreadySummary) {
                            if (alreadyReturnedUnits > 0) {
                                alreadySummary.textContent = returnedUnitsCountTpl
                                    .replace(':returned', String(alreadyReturnedUnits))
                                    .replace(':total', String(orderUnits));
                                alreadySummary.classList.remove('hidden');
                            } else {
                                alreadySummary.textContent = '';
                                alreadySummary.classList.add('hidden');
                            }
                        }
                        var html = '';
                        lines.forEach(function (line) {
                            if (!line.delivered || line.fullyReturned) return;
                            var minQty = Math.max(1, line.returnedQty + 1);
                            if (minQty > line.orderQty) return;
                            var defaultQty = line.orderQty;
                            html += '<div class="rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm ring-1 ring-gray-100/80">';
                            html += '<label class="flex cursor-pointer items-start gap-3">';
                            html += '<input type="checkbox" class="mark-returned-item-cb mt-1 h-4 w-4 shrink-0 rounded border-gray-300 text-teal-600 focus:ring-teal-500" value="' + line.id + '" data-max-qty="' + line.orderQty + '" data-min-qty="' + minQty + '" checked onchange="toggleMarkReturnedQtyInput(this); updateMarkReturnedConfirmLabel()">';
                            html += '<span class="min-w-0 flex-1"><span class="text-sm font-semibold text-gray-900">' + escapeDeliveredModalHtml(line.name) + '</span>';
                            html += '<span class="mt-0.5 block text-xs text-gray-500">' + escapeDeliveredModalHtml(returnQtyLabel) + ': ';
                            html += '<span class="font-medium text-gray-700">' + line.returnedQty + '</span> / ' + line.orderQty + ' ' + escapeDeliveredModalHtml(@json(__('vendor.order_wizard_qty'))) + '</span></span></label>';
                            html += '<div class="mt-2 flex items-center gap-2 pl-7">';
                            html += '<input type="number" min="' + minQty + '" max="' + line.orderQty + '" step="1" class="mark-returned-item-qty w-20 rounded-lg border border-gray-200 px-2 py-1.5 text-sm font-semibold tabular-nums text-gray-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/25" value="' + defaultQty + '" data-order-item-id="' + line.id + '">';
                            html += '<span class="text-xs text-gray-500">/ ' + line.orderQty + '</span></div></div>';
                        });
                        list.innerHTML = html;
                        updateMarkReturnedConfirmLabel();
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                        window.markReturnedTriggerBtn = triggerBtn || null;
                    }
                    function updateMarkReturnedConfirmLabel() {
                        var btn = document.getElementById('markReturnedConfirmBtn');
                        var label = document.getElementById('markReturnedConfirmLabel');
                        if (!btn || !label) return;
                        var n = document.querySelectorAll('.mark-returned-item-cb:checked:not(:disabled)').length;
                        label.textContent = n > 0
                            ? markReturnedConfirmCountTpl.replace(':count', String(n))
                            : markReturnedConfirmBase;
                        btn.disabled = n === 0;
                    }
                    function toggleMarkReturnedQtyInput(cb) {
                        var wrap = cb.closest('.rounded-lg');
                        if (!wrap) return;
                        var qtyInput = wrap.querySelector('.mark-returned-item-qty');
                        if (!qtyInput) return;
                        qtyInput.disabled = !cb.checked || cb.disabled;
                    }
                    function closeMarkReturnedModal() {
                        var modal = document.getElementById('markReturnedModal');
                        if (modal) modal.classList.add('hidden');
                        document.body.style.overflow = '';
                        window.markReturnedTriggerBtn = null;
                        var alreadySummary = document.getElementById('markReturnedAlreadySummary');
                        if (alreadySummary) {
                            alreadySummary.textContent = '';
                            alreadySummary.classList.add('hidden');
                        }
                        var label = document.getElementById('markReturnedConfirmLabel');
                        var btn = document.getElementById('markReturnedConfirmBtn');
                        if (label) label.textContent = markReturnedConfirmBase;
                        if (btn) btn.disabled = false;
                    }
                    function confirmMarkReturned() {
                        var returnLines = [];
                        document.querySelectorAll('.mark-returned-item-cb:checked:not(:disabled)').forEach(function (cb) {
                            var id = parseInt(cb.value, 10);
                            if (!id) return;
                            var wrap = cb.closest('.rounded-lg');
                            var qtyInput = wrap ? wrap.querySelector('.mark-returned-item-qty') : null;
                            var qty = qtyInput ? parseInt(qtyInput.value, 10) : parseInt(cb.getAttribute('data-max-qty'), 10);
                            var maxQty = parseInt(cb.getAttribute('data-max-qty'), 10) || qty;
                            var minQty = parseInt(cb.getAttribute('data-min-qty'), 10) || 1;
                            if (!Number.isFinite(qty) || qty < minQty) qty = minQty;
                            if (qty > maxQty) qty = maxQty;
                            returnLines.push({ order_item_id: id, quantity: qty });
                        });
                        if (returnLines.length === 0) {
                            if (typeof showToast === 'function') showToast(@json(__('vendor.return_items_required')), 'error');
                            return;
                        }
                        var btn = document.getElementById('markReturnedConfirmBtn');
                        if (btn) btn.disabled = true;
                        patchRentalStatus({ returned: 'mark', return_lines: returnLines }, btn || window.markReturnedTriggerBtn);
                    }
                    function patchRentalStatus(payload, triggerBtn) {
                        if (typeof orderShowReadOnly !== 'undefined' && orderShowReadOnly) return;
                        var csrf = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        if (triggerBtn) triggerBtn.disabled = true;
                        fetch(rentalStatusUrl, {
                            method: 'PATCH',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf || '',
                            },
                            body: JSON.stringify(payload),
                        })
                            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
                            .then(function (res) {
                                if (triggerBtn) triggerBtn.disabled = false;
                                if (res.ok && res.data && res.data.success && res.data.rental_status) {
                                    closeMarkDeliveredModal();
                                    closeMarkReturnedModal();
                                    var confirmBtn = document.getElementById('markDeliveredConfirmBtn');
                                    if (confirmBtn) confirmBtn.disabled = false;
                                    var returnConfirmBtn = document.getElementById('markReturnedConfirmBtn');
                                    if (returnConfirmBtn) returnConfirmBtn.disabled = false;
                                    if (payload && payload.delivered === 'mark' && payload.order_item_ids) {
                                        document.querySelectorAll('[data-cart-line][data-order-item-id]').forEach(function (row) {
                                            var rid = parseInt(row.getAttribute('data-order-item-id'), 10);
                                            if (payload.order_item_ids.indexOf(rid) !== -1) {
                                                row.setAttribute('data-line-delivered', '1');
                                            }
                                        });
                                        if (typeof refreshOrderItems === 'function') {
                                            refreshOrderItems();
                                        }
                                    }
                                    if (payload && payload.delivered === 'clear') {
                                        document.querySelectorAll('[data-cart-line][data-order-item-id]').forEach(function (row) {
                                            row.setAttribute('data-line-delivered', '0');
                                            row.setAttribute('data-line-returned', '0');
                                            row.setAttribute('data-line-returned-qty', '0');
                                        });
                                        if (typeof refreshOrderItems === 'function') {
                                            refreshOrderItems();
                                        }
                                    }
                                    if (payload && (payload.returned === 'mark' || payload.returned === 'clear') && typeof refreshOrderItems === 'function') {
                                        refreshOrderItems();
                                    }
                                    applyRentalStatusToUi(res.data.rental_status);
                                    if (typeof showToast === 'function') showToast(res.data.message || 'OK', 'success');
                                } else {
                                    var msg = res.data && res.data.message ? res.data.message : 'Could not update';
                                    if (res.data && res.data.errors) {
                                        var first = Object.values(res.data.errors)[0];
                                        if (first && first[0]) msg = first[0];
                                    }
                                    if (typeof showToast === 'function') showToast(msg, 'error');
                                    var confirmBtnErr = document.getElementById('markDeliveredConfirmBtn');
                                    if (confirmBtnErr) confirmBtnErr.disabled = false;
                                    var returnConfirmBtnErr = document.getElementById('markReturnedConfirmBtn');
                                    if (returnConfirmBtnErr) {
                                        returnConfirmBtnErr.disabled = false;
                                        updateMarkReturnedConfirmLabel();
                                    }
                                }
                            })
                            .catch(function () {
                                if (triggerBtn) triggerBtn.disabled = false;
                                var confirmBtn = document.getElementById('markDeliveredConfirmBtn');
                                if (confirmBtn) confirmBtn.disabled = false;
                                var returnConfirmBtn = document.getElementById('markReturnedConfirmBtn');
                                if (returnConfirmBtn) returnConfirmBtn.disabled = false;
                                if (typeof showToast === 'function') showToast('Network error', 'error');
                            });
                    }
                    var rentalClearPending = null;
                    var rentalClearMessages = {
                        delivered: @json(__('vendor.clear_delivered_confirm_body')),
                        returned: @json(__('vendor.clear_returned_confirm_body')),
                    };
                    function openRentalClearConfirm(kind, btn) {
                        if (typeof orderShowReadOnly !== 'undefined' && orderShowReadOnly) return;
                        if (kind !== 'delivered' && kind !== 'returned') return;
                        rentalClearPending = { kind: kind, btn: btn };
                        var m = document.getElementById('rentalClearConfirmModal');
                        var body = document.getElementById('rentalClearConfirmBody');
                        if (body) body.textContent = rentalClearMessages[kind] || '';
                        if (m) {
                            m.classList.remove('hidden');
                            document.body.style.overflow = 'hidden';
                        }
                    }
                    function closeRentalClearConfirmModal() {
                        rentalClearPending = null;
                        var m = document.getElementById('rentalClearConfirmModal');
                        if (m) m.classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                    function confirmRentalClear() {
                        var p = rentalClearPending;
                        if (!p || !p.btn) {
                            closeRentalClearConfirmModal();
                            return;
                        }
                        var btn = p.btn;
                        var kind = p.kind;
                        rentalClearPending = null;
                        var m = document.getElementById('rentalClearConfirmModal');
                        if (m) m.classList.add('hidden');
                        document.body.style.overflow = '';
                        var payload = kind === 'delivered' ? { delivered: 'clear' } : { returned: 'clear' };
                        patchRentalStatus(payload, btn);
                    }
                    var lastOrderCartState = @json($orderCartJson);
                    const npMethodLabels = { card: 'Card', cash: 'Cash', upi: 'UPI', bank_transfer: 'Bank transfer', wallet: 'Wallet', other: 'Other', settlement: @json(__('vendor.payment_method_settlement')) };
                    const npPayTagRefund = @json(__('vendor.label_refund'));
                    function npIsDepositKind(kind) {
                        return kind === 'security_deposit' || kind === 'refund_security_deposit';
                    }
                    function npRefundBucketsFromRows(rows) {
                        let orderNet = 0;
                        let depNet = 0;
                        (rows || []).forEach(function (p) {
                            const amt = parseFloat(p.amount || 0) || 0;
                            const entryKind = p.entry_kind || 'payment';
                            const sign = entryKind === 'refund' ? -1 : 1;
                            if (p.payment_for === 'security_deposit') {
                                depNet += sign * amt;
                            } else {
                                orderNet += sign * amt;
                            }
                        });
                        return { order: Math.max(0, orderNet), deposit: Math.max(0, depNet) };
                    }
                    function npPaymentPayloadFromKind(kind) {
                        const isRf = kind === 'refund_order_amount' || kind === 'refund_security_deposit';
                        return {
                            entry_kind: isRf ? 'refund' : 'payment',
                            payment_for: npIsDepositKind(kind) ? 'security_deposit' : 'order_amount',
                        };
                    }
                    function npGetActivePaymentAmount() {
                        const k = document.getElementById('newPaymentDueModal')?.dataset?.npKind;
                        const inp = npIsDepositKind(k)
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
                        const dateInp = npIsDepositKind(k)
                            ? document.getElementById('npSdPaymentDate')
                            : document.getElementById('npOrderPaymentDate');
                        const dateOk = !!(dateInp && dateInp.value);
                        const canPay = !!(method && amt > 0 && dateOk);
                        const mKey = method ? method.value : '';
                        const mLab = mKey ? (npMethodLabels[mKey] || mKey.replace(/_/g, ' ')) : '';
                        const isRf = k === 'refund_order_amount' || k === 'refund_security_deposit';
                        const verb = isRf ? npPayTagRefund : 'Pay';
                        label.textContent = mLab ? (verb + ' ' + npFormatInr(amt) + ' · ' + mLab) : (verb + ' ' + npFormatInr(amt));
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
                        const dateInp = npIsDepositKind(k)
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
                        const pp = npPaymentPayloadFromKind(k || '');
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
                                payment_for: pp.payment_for,
                                entry_kind: pp.entry_kind,
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
                        const fb = npRefundBucketsFromRows(lastOrderCartState?.payment_detail || []);
                        if (k === 'security_deposit') {
                            if (inpSd) {
                                const maxSd = Math.max(0, depRemaining);
                                inpSd.max = maxSd;
                                inpSd.value = maxSd > 0 ? maxSd.toFixed(2) : '';
                            }
                        } else if (k === 'refund_order_amount') {
                            if (inpOrder) {
                                const maxRf = fb.order;
                                inpOrder.max = maxRf;
                                inpOrder.value = maxRf > 0 ? maxRf.toFixed(2) : '';
                            }
                        } else if (k === 'refund_security_deposit') {
                            if (inpSd) {
                                const maxRf = fb.deposit;
                                inpSd.max = maxRf;
                                inpSd.value = maxRf > 0 ? maxRf.toFixed(2) : '';
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
                    const npPaymentCartId = @json($order->uuid);
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
                            } else if (kind === 'refund_security_deposit') {
                                title.textContent = 'Refund security deposit · #' + npPaymentCartId;
                            } else if (kind === 'refund_order_amount') {
                                title.textContent = 'Refund order amount · #' + npPaymentCartId;
                            } else {
                                title.textContent = 'Payment for order #' + npPaymentCartId;
                            }
                        }

                        if (kind === 'security_deposit') {
                            if (sub) sub.textContent = 'You chose security deposit — details below are for the deposit only.';
                            if (secOrder) {
                                secOrder.classList.add('hidden');
                                secOrder.classList.remove('ring-2', 'ring-emerald-100', 'ring-rose-100');
                            }
                            if (secSd) {
                                secSd.classList.remove('hidden');
                                secSd.classList.remove('ring-rose-100');
                                secSd.classList.add('ring-2', 'ring-emerald-100');
                            }
                        } else if (kind === 'refund_security_deposit') {
                            if (sub) sub.textContent = 'Refund against amounts collected for the security deposit.';
                            if (secOrder) {
                                secOrder.classList.add('hidden');
                                secOrder.classList.remove('ring-2', 'ring-emerald-100');
                            }
                            if (secSd) {
                                secSd.classList.remove('hidden');
                                secSd.classList.add('ring-2', 'ring-rose-100');
                            }
                        } else if (kind === 'refund_order_amount') {
                            if (sub) sub.textContent = 'Refund against amounts collected for the rental / order balance.';
                            if (secSd) {
                                secSd.classList.add('hidden');
                                secSd.classList.remove('ring-2', 'ring-emerald-100', 'ring-rose-100');
                            }
                            if (secOrder) {
                                secOrder.classList.remove('hidden');
                                secOrder.classList.add('ring-2', 'ring-rose-100');
                            }
                        } else {
                            if (sub) sub.textContent = 'You chose order amount — details below are for the rental / order balance only.';
                            if (secSd) {
                                secSd.classList.add('hidden');
                                secSd.classList.remove('ring-2', 'ring-emerald-100', 'ring-rose-100');
                            }
                            if (secOrder) {
                                secOrder.classList.remove('hidden');
                                secOrder.classList.add('ring-2', 'ring-emerald-100');
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
                            const entryKind = p.entry_kind || 'payment';
                            const amtNum = parseFloat(p.amount || 0);
                            const amt = amtNum.toFixed(2);
                            const isSd = p.payment_for === 'security_deposit';
                            const badge = isSd
                                ? '<span class="inline-flex rounded-md bg-teal-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-teal-700 ring-1 ring-teal-100">' + npPayTagDeposit + '</span>'
                                : '<span class="inline-flex rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-100">' + npPayTagOrder + '</span>';
                            const refundBadge = entryKind === 'refund'
                                ? '<span class="inline-flex rounded-md bg-rose-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-rose-700 ring-1 ring-rose-100">' + npPayTagRefund + '</span>'
                                : '';
                            const amtDisp = entryKind === 'refund'
                                ? ('<span class="text-xs font-bold tabular-nums text-rose-700">−₹' + amt + '</span>')
                                : ('<span class="text-xs font-bold tabular-nums text-gray-900">₹' + amt + '</span>');
                            const m = p.method || '';
                            const mLabel = npMethodLabels[m] || (m ? m.charAt(0).toUpperCase() + m.slice(1).replace(/_/g, ' ') : '—');
                            const datePart = p.paid_on
                                ? ('<span class="text-gray-400"> · </span><span>' + npFormatPaymentListDate(p.paid_on) + '</span>')
                                : '';
                            return '<li class="flex items-start gap-2 rounded-lg border border-gray-100 bg-white px-2.5 py-2 text-xs shadow-sm ring-1 ring-gray-100/80">'
                                + '<div class="min-w-0 flex-1">'
                                + '<div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">'
                                + amtDisp + badge + refundBadge
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

            <header class="flex-shrink-0 border-b border-gray-200 bg-gradient-to-r from-emerald-50 via-white to-teal-50/80 px-4 pb-4 pt-[max(1rem,env(safe-area-inset-top))] sm:px-6 sm:py-4 md:rounded-t-2xl md:pt-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 pr-2">
                        <h3 id="add-items-modal-title" class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl">{{ __('vendor.modal_add_items_title') }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-gray-600">{{ __('vendor.modal_add_items_subtitle') }}</p>
                    </div>
                    <button type="button"
                            @click="showAddItem = false; searchQuery = ''; selectedCategory = ''"
                            class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl text-gray-500 transition-colors hover:bg-white hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
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
                                       class="w-full rounded-xl border border-gray-200 bg-white py-3.5 pl-11 pr-11 text-base text-gray-900 shadow-sm transition-shadow placeholder:text-gray-400 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 md:py-2.5 md:text-sm">
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
                                        class="w-full appearance-none rounded-xl border border-gray-200 bg-white py-3.5 pl-11 pr-10 text-base text-gray-900 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30 md:py-2.5 md:text-sm">
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
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain bg-white">
                    <div x-show="items.length === 0" class="flex min-h-[12rem] flex-col items-center justify-center px-6 py-12 text-center">
                        <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-amber-50 ring-1 ring-amber-100">
                            <i class="fas fa-box-open text-3xl text-amber-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.modal_no_inventory_title') }}</h3>
                        <p class="mt-2 max-w-sm text-sm leading-relaxed text-gray-600">{{ __('vendor.modal_no_inventory_body') }}</p>
                        <a href="{{ route('vendor.items.create') }}"
                           class="mt-6 inline-flex min-h-[48px] items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
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
                                class="mt-5 inline-flex min-h-[48px] items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-gray-50">
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
                                        <tr class="transition-colors hover:bg-emerald-50/40">
                                            <td class="px-5 py-4 align-middle">
                                                <div class="flex items-center gap-3">
                                                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 ring-1 ring-emerald-100">
                                                        <i class="fas fa-box text-emerald-600"></i>
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
                                                <span class="inline-flex rounded-lg bg-emerald-50 px-2.5 py-1 text-sm font-bold text-emerald-800 tabular-nums">₹<span x-text="parseFloat(item.price).toFixed(2)"></span></span>
                                            </td>
                                            <td class="px-5 py-4 align-middle">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <button type="button" @click="addItemToCart(item.id)"
                                                            x-show="!isAdded(item.id)"
                                                            :disabled="addingItem === item.id"
                                                            class="inline-flex min-h-[40px] min-w-[5.5rem] items-center justify-center rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50">
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
                                                               class="h-10 w-16 rounded-xl border border-gray-200 text-center text-sm font-bold text-gray-900 shadow-inner focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
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
                                                               class="h-10 w-16 rounded-xl border border-gray-200 text-center text-sm font-bold text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
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
                                            <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 ring-1 ring-emerald-100">
                                                <i class="fas fa-box text-lg text-emerald-600"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-2">
                                                    <h4 class="text-base font-bold leading-snug text-gray-900" x-text="item.name"></h4>
                                                    <span class="flex-shrink-0 rounded-lg bg-emerald-50 px-2.5 py-1 text-sm font-bold tabular-nums text-emerald-800">₹<span x-text="parseFloat(item.price).toFixed(2)"></span></span>
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
                                                class="flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-3.5 text-base font-semibold text-white shadow-md transition hover:bg-emerald-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">
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
                                                           class="h-12 w-20 rounded-xl border-2 border-gray-200 bg-white text-center text-lg font-bold text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/25"
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
                                                           class="h-12 w-20 rounded-xl border-2 border-gray-200 bg-white text-center text-lg font-bold text-gray-900 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/25"
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
                        <i class="fas fa-check text-emerald-600"></i>{{ __('vendor.modal_done') }}
                    </button>
                </footer>
            </div>
        </div>
    </div>

    @include('vendor.orders.partials.order-line-edit-modal')
    @include('vendor.orders.partials.variant-picker-modal')
</div>

<!-- Discount Modal -->
<div id="discountModal" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeDiscountModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-t-xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 flex items-center justify-center bg-emerald-600 rounded-lg">
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
                            <div class="flex items-center justify-center px-4 py-3 border-2 border-gray-200 rounded-lg peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all">
                                <div class="text-center">
                                    <i class="fas fa-rupee-sign text-lg text-gray-500 peer-checked:text-emerald-600 mb-1"></i>
                                    <p class="text-sm font-semibold text-gray-700">Fixed Amount</p>
                                    <p class="text-xs text-gray-500">₹ value</p>
                                </div>
                            </div>
                        </label>
                        <label class="relative cursor-pointer">
                            <input type="radio" name="discount_type" value="percent" class="peer sr-only" id="discount_type_percent">
                            <div class="flex items-center justify-center px-4 py-3 border-2 border-gray-200 rounded-lg peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition-all">
                                <div class="text-center">
                                    <i class="fas fa-percent text-lg text-gray-500 peer-checked:text-emerald-600 mb-1"></i>
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
                               class="w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
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
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-all active:scale-95 shadow-sm">
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
            <div class="rounded-t-xl border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-emerald-100 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600">
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

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 px-4 py-3 transition hover:border-emerald-300 hover:bg-emerald-50/50">
                        <input type="radio" name="security_deposit_type" value="none" class="mt-1 h-4 w-4 text-emerald-600" @checked(($order->security_deposit_type ?? 'none') === 'none')>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">None</p>
                            <p class="text-xs text-gray-600">Do not charge a security deposit by default.</p>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 px-4 py-3 transition hover:border-emerald-300 hover:bg-emerald-50/50">
                        <input type="radio" name="security_deposit_type" value="order_amount" class="mt-1 h-4 w-4 text-emerald-600" @checked(($order->security_deposit_type ?? 'none') === 'order_amount')>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Order amount</p>
                            <p class="text-xs text-gray-600">Add a percentage of the total order amount.</p>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 px-4 py-3 transition hover:border-emerald-300 hover:bg-emerald-50/50">
                        <input type="radio" name="security_deposit_type" value="product_security_deposit" class="mt-1 h-4 w-4 text-emerald-600" @checked(($order->security_deposit_type ?? 'none') === 'product_security_deposit')>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Product security deposit value</p>
                            <p class="text-xs text-gray-600">Charge a percentage of the security deposit value of all products on an order.</p>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 px-4 py-3 transition hover:border-emerald-300 hover:bg-emerald-50/50">
                        <input type="radio" name="security_deposit_type" value="fixed_amount" class="mt-1 h-4 w-4 text-emerald-600" @checked(($order->security_deposit_type ?? 'none') === 'fixed_amount')>
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
                               class="w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-4 text-sm focus:border-transparent focus:ring-2 focus:ring-emerald-500"
                               placeholder="Enter value">
                    </div>
                    <p id="securityDepositValueHelp" class="mt-1.5 text-xs text-gray-500"></p>
                </div>

                <p id="securityDepositError" class="hidden text-sm text-red-600"></p>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-4">
                    <button type="button" onclick="closeSecurityDepositModal()" class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-emerald-700 active:scale-95">
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

@php
    $bookingTz = config('app.timezone');
    $parseOrderBooking = function ($dt) use ($bookingTz) {
        if (! $dt) {
            return ['date' => '', 'time' => ''];
        }
        $local = $dt->copy()->timezone($bookingTz);

        return ['date' => $local->format('Y-m-d'), 'time' => $local->format('H:i')];
    };
    $bookingStartParts = $parseOrderBooking($order->start_at);
    $bookingEndParts = $parseOrderBooking($order->end_at);
    $bookingStartAtValue = $order->start_at
        ? $order->start_at->copy()->timezone($bookingTz)->format('Y-m-d H:i')
        : '';
    $bookingEndAtValue = $order->end_at
        ? $order->end_at->copy()->timezone($bookingTz)->format('Y-m-d H:i')
        : '';
@endphp

<!-- Edit Booking Dates Modal -->
<div id="editCartModal" class="fixed inset-0 z-[70] hidden">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeEditCartModal()"></div>
    <div class="fixed inset-0 flex items-end justify-center p-0 sm:items-center sm:p-4">
        <div class="relative flex max-h-[min(92dvh,40rem)] w-full max-w-2xl flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl" onclick="event.stopPropagation()">
            <div class="shrink-0 border-b border-gray-200 bg-gradient-to-r from-emerald-50 to-teal-50 px-4 py-4 sm:px-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold text-gray-900">{{ __('vendor.edit') }} {{ __('vendor.booking_dates') }}</h3>
                            <p class="mt-0.5 text-xs text-gray-600 sm:text-sm">{{ __('vendor.order_booking_modal_help') }}</p>
                        </div>
                    </div>
                    <button type="button"
                            onclick="closeEditCartModal()"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-white hover:text-gray-800"
                            aria-label="{{ __('vendor.modal_close_aria') }}">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <form id="editCartForm" onsubmit="submitEditCart(event)" class="flex min-h-0 flex-1 flex-col">
                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6 sm:py-5">
                    <div class="rounded-lg border border-gray-100 bg-gray-50/80 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-500">{{ __('vendor.customer') }}</p>
                        <p class="mt-0.5 truncate text-sm font-medium text-gray-900">
                            {{ $order->customer ? $order->customer->name . ' — ' . $order->customer->mobile : __('vendor.order_customer_unavailable') }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-gray-800">
                            <i class="fas fa-calendar mr-1 text-emerald-600" aria-hidden="true"></i>
                            {{ __('vendor.booking_dates') }}
                            <span class="text-xs font-normal text-gray-500">({{ __('vendor.optional') }})</span>
                        </label>
                        @include('vendor.orders.partials.order-booking-dates-fields', [
                            'prefix' => 'edit',
                            'startParts' => $bookingStartParts,
                            'endParts' => $bookingEndParts,
                            'startAtValue' => $bookingStartAtValue,
                            'endAtValue' => $bookingEndAtValue,
                            'restrictPastDates' => false,
                        ])
                    </div>
                </div>

                <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-gray-200 bg-white px-4 py-3 sm:flex-row sm:justify-end sm:px-6 sm:py-4">
                    <button type="button"
                            onclick="closeEditCartModal()"
                            class="inline-flex min-h-[44px] items-center justify-center rounded-lg border border-gray-300 bg-white px-5 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:min-h-[40px]">
                        {{ __('vendor.cancel') }}
                    </button>
                    <button type="submit"
                            id="editCartSubmitBtn"
                            class="inline-flex min-h-[44px] items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 active:scale-[0.98] sm:min-h-[40px]">
                        <i class="fas fa-save text-xs" aria-hidden="true"></i>
                        {{ __('vendor.update') }}
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

<!-- Mark delivered: item checklist -->
<div id="markDeliveredModal" class="fixed inset-0 z-[73] hidden" role="dialog" aria-modal="true" aria-labelledby="markDeliveredModalTitle">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeMarkDeliveredModal()"></div>
    <div class="fixed inset-0 flex items-end justify-center p-0 sm:items-center sm:p-4">
        <div class="relative flex max-h-[min(92dvh,640px)] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl ring-1 ring-gray-200 sm:rounded-2xl" onclick="event.stopPropagation()">
            <div class="shrink-0 border-b border-gray-100 bg-gradient-to-r from-teal-50 to-emerald-50 px-4 py-3.5 sm:px-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 id="markDeliveredModalTitle" class="text-base font-bold text-gray-900 sm:text-lg">{{ __('vendor.mark_delivered_modal_title') }}</h3>
                        <p class="mt-1 text-xs leading-snug text-gray-600 sm:text-sm">{{ __('vendor.mark_delivered_modal_hint') }}</p>
                        <p id="markDeliveredAlreadySummary" class="mt-2 hidden rounded-lg border border-teal-200/80 bg-white/80 px-2.5 py-1.5 text-xs font-semibold text-teal-900"></p>
                    </div>
                    <button type="button" onclick="closeMarkDeliveredModal()" class="shrink-0 rounded-lg p-2 text-gray-500 transition hover:bg-white/80 hover:text-gray-800" aria-label="{{ __('vendor.cancel') }}">
                        <i class="fas fa-times text-lg" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div id="markDeliveredItemList" class="min-h-0 flex-1 space-y-2 overflow-y-auto overscroll-y-contain px-4 py-3 sm:px-5 [-webkit-overflow-scrolling:touch]"></div>
            <div class="shrink-0 border-t border-gray-200 bg-white px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-5">
                <button type="button" id="markDeliveredConfirmBtn" onclick="confirmMarkDelivered()"
                        class="flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-3 text-sm font-bold text-white shadow-md transition hover:bg-teal-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="fas fa-check" aria-hidden="true"></i><span id="markDeliveredConfirmLabel">{{ __('vendor.mark_delivered_confirm') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Mark returned: item checklist + qty -->
<div id="markReturnedModal" class="fixed inset-0 z-[73] hidden" role="dialog" aria-modal="true" aria-labelledby="markReturnedModalTitle">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeMarkReturnedModal()"></div>
    <div class="fixed inset-0 flex items-end justify-center p-0 sm:items-center sm:p-4">
        <div class="relative flex max-h-[min(92dvh,640px)] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl ring-1 ring-gray-200 sm:rounded-2xl" onclick="event.stopPropagation()">
            <div class="shrink-0 border-b border-gray-100 bg-gradient-to-r from-teal-50 to-teal-50 px-4 py-3.5 sm:px-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 id="markReturnedModalTitle" class="text-base font-bold text-gray-900 sm:text-lg">{{ __('vendor.mark_returned_modal_title') }}</h3>
                        <p class="mt-1 text-xs leading-snug text-gray-600 sm:text-sm">{{ __('vendor.mark_returned_modal_hint') }}</p>
                        <p id="markReturnedAlreadySummary" class="mt-2 hidden rounded-lg border border-teal-200/80 bg-white/80 px-2.5 py-1.5 text-xs font-semibold text-teal-900"></p>
                    </div>
                    <button type="button" onclick="closeMarkReturnedModal()" class="shrink-0 rounded-lg p-2 text-gray-500 transition hover:bg-white/80 hover:text-gray-800" aria-label="{{ __('vendor.cancel') }}">
                        <i class="fas fa-times text-lg" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div id="markReturnedItemList" class="min-h-0 flex-1 space-y-2 overflow-y-auto overscroll-y-contain px-4 py-3 sm:px-5 [-webkit-overflow-scrolling:touch]"></div>
            <div class="shrink-0 border-t border-gray-200 bg-white px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-5">
                <button type="button" id="markReturnedConfirmBtn" onclick="confirmMarkReturned()"
                        class="flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-3 text-sm font-bold text-white shadow-md transition hover:bg-teal-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="fas fa-check" aria-hidden="true"></i><span id="markReturnedConfirmLabel">{{ __('vendor.mark_returned_confirm') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Complete order checklist -->
<div id="completeOrderModal" class="fixed inset-0 z-[74] hidden" role="dialog" aria-modal="true" aria-labelledby="completeOrderModalTitle">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeCompleteOrderModal()"></div>
    <div class="fixed inset-0 flex items-end justify-center p-0 sm:items-center sm:p-4">
        <div class="relative flex max-h-[92dvh] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl ring-1 ring-gray-200 sm:max-h-[90vh] sm:rounded-2xl" onclick="event.stopPropagation()">
            <div class="border-b border-gray-100 bg-gradient-to-b from-emerald-50/70 to-white px-4 py-4 sm:px-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 id="completeOrderModalTitle" class="text-lg font-bold text-gray-900">{{ __('vendor.order_complete_modal_title') }}</h3>
                        <p class="mt-1 text-xs leading-relaxed text-gray-600">{{ __('vendor.order_complete_modal_subtitle') }}</p>
                    </div>
                    <button type="button" onclick="closeCompleteOrderModal()" class="shrink-0 rounded-lg p-2 text-gray-500 hover:bg-gray-100" aria-label="{{ __('vendor.cancel') }}">
                        <i class="fas fa-times" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-y-contain px-4 py-4 sm:px-5">
                @if($completionChecklist['has_pending'])
                    @if(count($completionChecklist['undelivered']) > 0)
                        <section class="rounded-xl border border-amber-200 bg-amber-50/60 p-3 ring-1 ring-amber-100">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-900">{{ __('vendor.order_complete_undelivered_title') }}</p>
                            <ul class="mt-2 space-y-1.5">
                                @foreach($completionChecklist['undelivered'] as $row)
                                    <li class="flex items-start justify-between gap-2 text-xs">
                                        <span class="min-w-0 font-medium text-gray-900">{{ $row['name'] }}</span>
                                        <span class="shrink-0 tabular-nums text-amber-900">{{ __('vendor.order_complete_qty_units', ['count' => $row['quantity']]) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if(count($completionChecklist['not_returned']) > 0)
                        <section class="rounded-xl border border-orange-200 bg-orange-50/50 p-3 ring-1 ring-orange-100">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-orange-900">{{ __('vendor.order_complete_not_returned_title') }}</p>
                            <ul class="mt-2 space-y-1.5">
                                @foreach($completionChecklist['not_returned'] as $row)
                                    <li class="flex items-start justify-between gap-2 text-xs">
                                        <span class="min-w-0 font-medium text-gray-900">{{ $row['name'] }}</span>
                                        <span class="shrink-0 text-right tabular-nums text-orange-900">
                                            {{ __('vendor.order_complete_return_pending', ['returned' => $row['returned'], 'total' => $row['quantity']]) }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                    @if($completionChecklist['order_due'] > 0.009 || $completionChecklist['deposit_due'] > 0.009)
                        <section class="rounded-xl border border-red-200 bg-red-50/40 p-3 ring-1 ring-red-100">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-red-900">{{ __('vendor.order_complete_dues_title') }}</p>
                            <div class="mt-2 space-y-1 text-xs">
                                @if($completionChecklist['order_due'] > 0.009)
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-700">{{ __('vendor.order_payment_dues') }}</span>
                                        <span class="font-bold tabular-nums text-red-700">₹{{ number_format($completionChecklist['order_due'], 2) }}</span>
                                    </div>
                                @endif
                                @if($completionChecklist['deposit_due'] > 0.009)
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-700">{{ __('vendor.security_deposit_dues') }}</span>
                                        <span class="font-bold tabular-nums text-red-700">₹{{ number_format($completionChecklist['deposit_due'], 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between gap-2 border-t border-red-100 pt-1 font-semibold">
                                    <span class="text-gray-900">{{ __('vendor.summary_total_due') }}</span>
                                    <span class="tabular-nums text-red-700">₹{{ number_format($completionChecklist['total_due'], 2) }}</span>
                                </div>
                            </div>
                        </section>
                    @endif

                    @if($completionChecklist['refund_pending_total'] > 0.009)
                        <section class="rounded-xl border border-violet-200 bg-violet-50/40 p-3 ring-1 ring-violet-100">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-violet-900">{{ __('vendor.order_complete_refunds_title') }}</p>
                            <div class="mt-2 space-y-1 text-xs">
                                @if($completionChecklist['order_refund_pending'] > 0.009)
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-700">{{ __('vendor.refund_order_amount_label') }}</span>
                                        <span class="font-bold tabular-nums text-violet-800">₹{{ number_format($completionChecklist['order_refund_pending'], 2) }}</span>
                                    </div>
                                @endif
                                @if($completionChecklist['deposit_refund_pending'] > 0.009)
                                    <div class="flex justify-between gap-2">
                                        <span class="text-gray-700">{{ __('vendor.refund_deposit_amount_label') }}</span>
                                        <span class="font-bold tabular-nums text-violet-800">₹{{ number_format($completionChecklist['deposit_refund_pending'], 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between gap-2 border-t border-violet-100 pt-1 font-semibold">
                                    <span class="text-gray-900">{{ __('vendor.order_complete_refund_pending_total') }}</span>
                                    <span class="tabular-nums text-violet-800">₹{{ number_format($completionChecklist['refund_pending_total'], 2) }}</span>
                                </div>
                            </div>
                        </section>
                    @endif
                @else
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 px-4 py-6 text-center ring-1 ring-emerald-100">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <i class="fas fa-circle-check text-xl" aria-hidden="true"></i>
                        </div>
                        <p class="text-sm font-semibold text-emerald-900">{{ __('vendor.order_complete_all_clear') }}</p>
                        <p class="mt-1 text-xs text-emerald-800/80">{{ __('vendor.order_complete_all_clear_hint') }}</p>
                    </div>
                @endif

                @if($completionChecklist['has_pending'])
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-gray-50/80 px-3 py-3 text-sm ring-1 ring-gray-100">
                        <input type="checkbox"
                               id="completeOrderSettlementAck"
                               class="mt-0.5 h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                               onchange="syncCompleteOrderSubmitState()">
                        <span class="text-xs leading-relaxed text-gray-800">{{ __('vendor.order_complete_settlement_checkbox') }}</span>
                    </label>
                @endif
            </div>

            <div class="shrink-0 border-t border-gray-200 bg-white px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-5">
                <form id="completeOrderForm" method="POST" action="{{ route('vendor.orders.update-status', $order) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="completed">
                    @if($completionChecklist['has_pending'])
                        <input type="hidden" name="settlement_acknowledged" id="completeOrderSettlementField" value="0">
                    @endif
                    <div class="flex flex-col gap-2 sm:flex-row-reverse">
                        <button type="submit"
                                id="completeOrderSubmitBtn"
                                @if($completionChecklist['has_pending']) disabled @endif
                                class="flex min-h-[48px] flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-md transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                            {{ __('vendor.order_status_complete_order') }}
                        </button>
                        <button type="button"
                                onclick="closeCompleteOrderModal()"
                                class="flex min-h-[48px] flex-1 items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            {{ __('vendor.cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Clear delivered / returned confirmation -->
<div id="rentalClearConfirmModal" class="fixed inset-0 z-[72] hidden" role="dialog" aria-modal="true" aria-labelledby="rentalClearConfirmTitle">
    <div class="fixed inset-0 bg-gray-900/50 transition-opacity" onclick="closeRentalClearConfirmModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="relative w-full max-w-md rounded-xl bg-white shadow-2xl ring-1 ring-gray-200" onclick="event.stopPropagation()">
            <div class="p-6 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                    <i class="fas fa-undo-alt text-2xl text-amber-700" aria-hidden="true"></i>
                </div>
                <h3 id="rentalClearConfirmTitle" class="mb-2 text-lg font-bold text-gray-900">{{ __('vendor.rental_clear_confirm_title') }}</h3>
                <p id="rentalClearConfirmBody" class="text-sm leading-relaxed text-gray-600"></p>
            </div>
            <div class="flex items-center justify-center gap-3 border-t border-gray-100 px-6 pb-6 pt-2">
                <button type="button" onclick="closeRentalClearConfirmModal()"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition-colors hover:bg-gray-50">
                    <i class="fas fa-times mr-2"></i>{{ __('vendor.cancel') }}
                </button>
                <button type="button" onclick="confirmRentalClear()"
                        class="flex-1 rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-amber-700">
                    <i class="fas fa-check mr-2"></i>{{ __('vendor.rental_clear_confirm_cta') }}
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .flatpickr-calendar { border-radius: 12px !important; box-shadow: 0 10px 40px rgba(0,0,0,.15) !important; border: 1px solid #e5e7eb !important; font-family: 'Inter', sans-serif !important; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #059669 !important; border-color: #059669 !important; }
    .flatpickr-day.booking-in-range {
        background: #d1fae5 !important;
        border-color: #a7f3d0 !important;
        color: #065f46 !important;
        box-shadow: none !important;
    }
    .flatpickr-day.booking-range-start,
    .flatpickr-day.booking-range-end,
    .flatpickr-day.booking-range-preview-end {
        background: #059669 !important;
        border-color: #059669 !important;
        color: #fff !important;
    }
    .flatpickr-day.booking-range-start.booking-in-range,
    .flatpickr-day.booking-range-end.booking-in-range { border-radius: 0 !important; }
    .flatpickr-day.booking-range-start { border-radius: 50% 0 0 50% !important; }
    .flatpickr-day.booking-range-end,
    .flatpickr-day.booking-range-preview-end { border-radius: 0 50% 50% 0 !important; }
    .flatpickr-day.booking-range-start.booking-range-end { border-radius: 50% !important; }
    .flatpickr-day.today { border-color: #059669 !important; }
    .flatpickr-day:hover { background: #d1fae5 !important; }
    .flatpickr-day.booking-range-start:hover,
    .flatpickr-day.booking-range-end:hover,
    .flatpickr-day.booking-range-preview-end:hover { background: #047857 !important; }
    .flatpickr-months .flatpickr-month { height: 40px !important; }
    .flatpickr-current-month { font-size: 1rem !important; font-weight: 600 !important; }
    .date-input-wrapper { position: relative; cursor: pointer; -webkit-tap-highlight-color: transparent; }
    .date-input-wrapper .date-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 14px; z-index: 1; }
    .date-input-wrapper input,
    .date-input-wrapper .flatpickr-input { display: block; width: 100%; box-sizing: border-box; padding-right: 32px; min-height: 40px; font-size: 0.875rem; cursor: pointer; }
    .time-input-wrapper { position: relative; }
    .time-input-wrapper .time-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 14px; }
    .booking-time-select {
        min-height: 40px;
        font-size: 0.875rem;
        padding-right: 32px;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: none;
        color: #111827;
    }
    .booking-time-select.is-placeholder { color: #9ca3af; }
</style>
@endsection

@section('scripts')
<script>
let pendingDelete = null;

// --- Add Item to Cart (AJAX) ---
function addItemToCartAjax(itemId, qty, component) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const item = component.items.find(i => i.id === itemId);
    const priceType = item?.rental_period ?? 'per_day';
    const billingUnits = priceType === 'fixed' ? 1 : Math.max(parseFloat(component.addedItemBillingUnits[itemId]) || 1, 0.01);

    fetch('{{ route("vendor.orders.items.add", $order) }}', {
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
    const priceType = item?.rental_period ?? 'per_day';
    let billingUnits = 1;
    if (priceType !== 'fixed') {
        billingUnits = Math.max(parseFloat(component.addedItemBillingUnits[itemId]) || 1, 0.01);
    }

    fetch(`{{ url('vendor/orders') }}/{{ $order->uuid }}/items/${itemId}`, {
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

    fetch(`{{ url('vendor/orders') }}/{{ $order->uuid }}/items/${itemId}`, {
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

    fetch(`{{ route('vendor.orders.discount', $order) }}`, {
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

    fetch(`{{ route('vendor.orders.discount.remove', $order) }}`, {
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
    /** Order total from server (sub − discounts + delivery + extras), excludes security deposit */
    orderGrandTotal: parseFloat(@json((float) $order->grand_total)),
    subTotal: parseFloat(@json((float) $order->sub_total)),
};

function openCompleteOrderModal() {
    const modal = document.getElementById('completeOrderModal');
    if (!modal) return;
    const ack = document.getElementById('completeOrderSettlementAck');
    if (ack) ack.checked = false;
    syncCompleteOrderSubmitState();
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCompleteOrderModal() {
    const modal = document.getElementById('completeOrderModal');
    if (!modal) return;
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function syncCompleteOrderSubmitState() {
    const ack = document.getElementById('completeOrderSettlementAck');
    const field = document.getElementById('completeOrderSettlementField');
    const btn = document.getElementById('completeOrderSubmitBtn');
    if (ack && field) {
        field.value = ack.checked ? '1' : '0';
    }
    if (btn && ack) {
        btn.disabled = !ack.checked;
    }
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('completeOrderModal');
        if (modal && !modal.classList.contains('hidden')) {
            closeCompleteOrderModal();
        }
    }
});

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
        order_amount: ['Security deposit (order %)', 'Charge a percentage of the order total (after discounts).'],
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

function applyPaymentSummaryDisplay(cartTotals) {
    const summary = cartTotals.payment_summary;
    if (!summary) {
        return;
    }

    const orderTotal = parseFloat(cartTotals.grand_total ?? securityDepositState.orderGrandTotal ?? 0);
    const depositTotal = parseFloat(cartTotals.security_deposit ?? securityDepositState.amount ?? 0);
    const orderPaid = parseFloat(summary.order_paid ?? 0);
    const depositPaid = parseFloat(summary.deposit_paid ?? 0);
    const orderDue = Math.max(0, orderTotal - orderPaid);
    const depositDue = Math.max(0, depositTotal - depositPaid);
    const totalDue = orderDue + depositDue;

    const setText = (sel, value, prefix) => {
        const el = document.querySelector(sel);
        if (el) {
            el.textContent = prefix + value.toFixed(2);
        }
    };
    const setDueClass = (sel, due) => {
        const el = document.querySelector(sel);
        if (!el) return;
        el.classList.remove('text-red-600', 'text-emerald-700');
        el.classList.add(due > 0.009 ? 'text-red-600' : 'text-emerald-700');
    };

    setText('[data-order-payment-total]', orderTotal, '₹');
    setText('[data-order-payment-paid]', orderPaid, '₹');
    setText('[data-order-payment-due]', orderDue, '₹');
    setDueClass('[data-order-payment-due]', orderDue);

    setText('[data-deposit-payment-total]', depositTotal, '₹');
    setText('[data-deposit-payment-paid]', depositPaid, '₹');
    setText('[data-deposit-payment-due]', depositDue, '₹');
    setDueClass('[data-deposit-payment-due]', depositDue);

    setText('[data-balance-due]', totalDue, '₹');
    setDueClass('[data-balance-due]', totalDue);

    const paidEl = document.querySelector('[data-paid-amount]');
    if (paidEl && cartTotals.paid_amount !== undefined && cartTotals.paid_amount !== null) {
        paidEl.textContent = '₹' + parseFloat(cartTotals.paid_amount).toFixed(2);
    }

    setText('[data-refund-order-total]', parseFloat(summary.refund_order ?? 0), '₹');
    setText('[data-refund-deposit-total]', parseFloat(summary.refund_deposit ?? 0), '₹');
    setText('[data-refund-total]', parseFloat(summary.refund_total ?? 0), '₹');
}

function applySecurityDepositDisplay(cartTotals) {
    const securityDepositEl = document.querySelector('[data-security-deposit-total]');
    const securityDepositLabel = document.getElementById('securityDepositLabel');
    const grandTotalEl = document.querySelector('[data-grand-total]');
    const orderTotalEl = document.querySelector('[data-order-total]');
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
    applyPaymentSummaryDisplay(cartTotals);

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

    fetch(`{{ route('vendor.orders.security-deposit', $order) }}`, {
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

    fetch(`{{ route('vendor.orders.coupons.list', $order) }}`, {
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
                                <span class="text-xs font-semibold ${disabled ? 'text-gray-400' : 'text-emerald-600'}">${typeLabel}</span>
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

    fetch(`{{ route('vendor.orders.coupon.apply', $order) }}`, {
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

    fetch(`{{ route('vendor.orders.coupon.remove', $order) }}`, {
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

    document.dispatchEvent(new CustomEvent('sync-booking-dates'));

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

function formatBillingUnitsDisplay(n) {
    const v = parseFloat(n);
    if (!Number.isFinite(v)) return '0';
    const s = v.toFixed(2).replace(/\.?0+$/, '');
    return s || '0';
}

function nudgeLineQty(cartId, itemId, delta, buttonEl) {
    const row = buttonEl.closest('[data-line-rental-period]');
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
    const row = el && el.closest ? el.closest('[data-line-rental-period]') : null;
    const priceType = row ? row.getAttribute('data-line-rental-period') : null;
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

            const billingDisplay = document.querySelector(`[data-billing-display="${itemId}"]`);
            if (billingDisplay && data.item.billing_units != null) {
                billingDisplay.textContent = formatBillingUnitsDisplay(data.item.billing_units);
            }

            const lineTotalEl = document.querySelector(`[data-line-total="${itemId}"]`);
            if (lineTotalEl) lineTotalEl.textContent = ' = ₹' + Math.round(parseFloat(data.item.line_total) || 0).toLocaleString('en-IN');

            if (row) {
                if (data.item.rental_period) {
                    row.setAttribute('data-line-rental-period', data.item.rental_period);
                }
                row.setAttribute('data-line-qty', data.item.quantity);
                if (data.item.billing_units != null) {
                    row.setAttribute('data-line-billing', data.item.billing_units);
                }
            }

            updateSummary(data.order);
            if (typeof refreshOrderItems === 'function') refreshOrderItems();

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
    const row = inputEl.closest('[data-line-rental-period]');
    if (!row) return;
    let billing = parseFloat(inputEl.value);
    if (!Number.isFinite(billing) || billing < 0.01) {
        billing = 1;
        inputEl.value = '1';
    }
    const qty = parseInt(row.getAttribute('data-line-qty'), 10) || 1;
    const priceType = row.getAttribute('data-line-rental-period');
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
            }
            window.dispatchEvent(new CustomEvent('order-item-updated', {
                detail: {
                    itemId: itemId,
                    quantity: data.item.quantity,
                    billing_units: data.item.billing_units,
                },
            }));
            const qtyDisplay = document.querySelector(`[data-qty-display="${itemId}"]`);
            if (qtyDisplay) qtyDisplay.textContent = String(data.item.quantity);
            const billingDisplay = document.querySelector(`[data-billing-display="${itemId}"]`);
            if (billingDisplay && data.item.billing_units != null) {
                billingDisplay.textContent = formatBillingUnitsDisplay(data.item.billing_units);
            }
            const lineTotalEl = document.querySelector(`[data-line-total="${itemId}"]`);
            if (lineTotalEl) lineTotalEl.textContent = ' = ₹' + Math.round(parseFloat(data.item.line_total) || 0).toLocaleString('en-IN');
            updateSummary(data.order);
            if (typeof refreshOrderItems === 'function') refreshOrderItems();
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
                    itemsCountEl.textContent = @json(__('vendor.order_items_heading', ['count' => ':count'])).replace(':count', String(c));
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

function removeExtraChargeLine(lineIndex, button) {
    if (typeof orderShowReadOnly !== 'undefined' && orderShowReadOnly) {
        return;
    }
    const url = (typeof extraChargeDeleteUrlPrefix === 'string' ? extraChargeDeleteUrlPrefix : '') + '/' + encodeURIComponent(lineIndex);
    const csrf = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    if (button) {
        button.disabled = true;
    }
    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf || '',
        },
    })
        .then(function (r) {
            return r.json().then(function (data) {
                return { ok: r.ok, data: data };
            });
        })
        .then(function (res) {
            if (button) {
                button.disabled = false;
            }
            if (res.ok && res.data && res.data.success && res.data.order) {
                if (typeof updateSummary === 'function') {
                    updateSummary(res.data.order);
                }
                if (typeof showToast === 'function') {
                    showToast(res.data.message || 'Removed', 'success');
                }
            } else {
                const msg = res.data && res.data.message ? res.data.message : 'Could not remove';
                if (typeof showToast === 'function') {
                    showToast(msg, 'error');
                }
            }
        })
        .catch(function () {
            if (button) {
                button.disabled = false;
            }
            if (typeof showToast === 'function') {
                showToast('Network error', 'error');
            }
        });
}

function refreshExtraChargesFromCart(cart) {
    const block = document.getElementById('summary-extra-charges-block');
    const ul = document.getElementById('extra-charges-lines-ul');
    const tot = document.querySelector('[data-extra-charges-total]');
    const total = parseFloat(cart.extra_charges_total ?? 0) || 0;
    const lines = Array.isArray(cart.extra_charges_lines) ? cart.extra_charges_lines : [];
    if (block) {
        block.classList.toggle('hidden', total <= 0);
    }
    if (ul) {
        const ro = typeof orderShowReadOnly !== 'undefined' && orderShowReadOnly;
        const titleAttr = (typeof removeExtraChargeTitle === 'string' ? removeExtraChargeTitle : 'Remove')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/"/g, '&quot;');
        ul.innerHTML = lines
            .map(function (line, idx) {
                if (!line || typeof line !== 'object') {
                    return '';
                }
                const lbl = String(line.label || '—')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
                const a = (parseFloat(line.amount) || 0).toFixed(2);
                const removeBtn = ro
                    ? ''
                    : '<button type="button" onclick="removeExtraChargeLine(' + idx + ', this)" class="rounded p-0.5 text-red-500 transition hover:bg-red-100 hover:text-red-700" title="' + titleAttr + '">'
                        + '<i class="fas fa-times-circle text-sm" aria-hidden="true"></i></button>';
                return '<li class="flex items-start justify-between gap-2 border-b border-amber-100/80 pb-1 text-xs last:border-0 last:pb-0">'
                    + '<span class="min-w-0 flex-1 leading-snug">' + lbl + '</span>'
                    + '<div class="flex shrink-0 items-start gap-1.5">'
                    + '<span class="font-semibold tabular-nums text-gray-900">₹' + a + '</span>'
                    + removeBtn
                    + '</div></li>';
            })
            .join('');
    }
    if (tot) {
        tot.textContent = '₹' + total.toFixed(2);
    }
}

function openExtraChargeModal() {
    const m = document.getElementById('extraChargeModal');
    const f = document.getElementById('extraChargeForm');
    if (f) {
        f.reset();
    }
    if (m) {
        m.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeExtraChargeModal() {
    const m = document.getElementById('extraChargeModal');
    if (m) {
        m.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function submitExtraCharge(e) {
    e.preventDefault();
    const labelInp = document.getElementById('extraChargeLabel');
    const amtInp = document.getElementById('extraChargeAmount');
    const btn = document.getElementById('extraChargeSubmitBtn');
    const csrf = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const label = labelInp && labelInp.value ? String(labelInp.value).trim() : '';
    const amount = amtInp ? parseFloat(amtInp.value) : 0;
    if (!label || !amount || amount <= 0) {
        if (typeof showToast === 'function') {
            showToast('Enter label and amount', 'error');
        }
        return;
    }
    if (btn) {
        btn.disabled = true;
    }
    fetch(extraChargePostUrl, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf || '',
        },
        body: JSON.stringify({ label: label, amount: amount }),
    })
        .then(function (r) {
            return r.json().then(function (data) {
                return { ok: r.ok, data: data };
            });
        })
        .then(function (res) {
            if (btn) {
                btn.disabled = false;
            }
            if (res.ok && res.data && res.data.success && res.data.order) {
                if (typeof updateSummary === 'function') {
                    updateSummary(res.data.order);
                }
                closeExtraChargeModal();
                if (typeof showToast === 'function') {
                    showToast(res.data.message || 'Saved', 'success');
                }
            } else {
                let msg = res.data && res.data.message ? res.data.message : 'Could not save';
                if (res.data && res.data.errors) {
                    const first = Object.values(res.data.errors)[0];
                    if (first && first[0]) {
                        msg = first[0];
                    }
                }
                if (typeof showToast === 'function') {
                    showToast(msg, 'error');
                }
            }
        })
        .catch(function () {
            if (btn) {
                btn.disabled = false;
            }
            if (typeof showToast === 'function') {
                showToast('Network error', 'error');
            }
        });
}

function updateSummary(cart) {
    lastOrderCartState = cart;
    refreshExtraChargesFromCart(cart);

    const subTotalEl = document.querySelector('[data-sub-total]');
    const discountAmountEl = document.querySelector('[data-discount-amount]');
    const couponDiscountEl = document.querySelector('[data-coupon-discount]');
    const discountTotalEl = document.querySelector('[data-discount-total]');
    const orderTotalEl = document.querySelector('[data-order-total]');

    if (subTotalEl) subTotalEl.textContent = '₹' + parseFloat(cart.sub_total).toFixed(2);
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
    applySecurityDepositDisplay(cart);
    if (typeof refreshPaymentListFromOrder === 'function') {
        refreshPaymentListFromOrder(cart);
    }
    if (cart.delivered_at_display !== undefined && typeof applyRentalStatusToUi === 'function') {
        applyRentalStatusToUi({
            delivered_at: cart.delivered_at,
            delivered_at_display: cart.delivered_at_display,
            delivered_units: cart.delivered_units,
            returned_at: cart.returned_at,
            returned_at_display: cart.returned_at_display,
            returned_units: cart.returned_units,
            total_units: cart.total_units,
        });
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
