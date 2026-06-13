import './bootstrap';
import Alpine from 'alpinejs';
import './lib/flatpickr';
import './order-wizard-datetime.js';
import { createOrderBookingDatesController } from './order-booking-dates.js';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function updateCartBadges(count) {
    document.querySelectorAll('[data-cart-badge]').forEach((el) => {
        const n = Number(count) || 0;
        if (n > 0) {
            el.textContent = String(n);
            el.classList.remove('hidden');
        } else {
            el.textContent = '';
            el.classList.add('hidden');
        }
    });
}

function showToast(message, type = 'success') {
    let host = document.getElementById('store-toast');
    if (!host) {
        host = document.createElement('div');
        host.id = 'store-toast';
        host.className = 'pointer-events-none fixed bottom-20 right-4 z-[90] flex flex-col gap-2 sm:bottom-4';
        document.body.appendChild(host);
    }

    const toast = document.createElement('div');
    toast.className = type === 'error'
        ? 'pointer-events-auto rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-900 shadow-lg'
        : 'pointer-events-auto rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-900 shadow-lg';
    toast.textContent = message;
    host.appendChild(toast);
    setTimeout(() => toast.remove(), 3200);
}

function readBookingTimesFromDom() {
    const OWD = window.OrderWizardDateTime;
    if (!OWD) {
        return { start: '', end: '' };
    }

    const startDateEl = document.getElementById('store_start_date');
    const endDateEl = document.getElementById('store_end_date');
    const startDate = startDateEl?.value || startDateEl?._flatpickr?.input?.value || '';
    const endDate = endDateEl?.value || endDateEl?._flatpickr?.input?.value || '';
    const startTime = document.getElementById('store_start_time')?.value || '';
    const endTime = document.getElementById('store_end_time')?.value || '';

    return {
        start: OWD.combineDateTime(startDate, startTime),
        end: OWD.combineDateTime(endDate, endTime),
    };
}

Alpine.data('storefrontBookingDates', (config = {}) => ({
    pickersReady: false,

    init() {
        window.addEventListener('storefront-booking-modal-open', () => this.setupPickers());
        if (this.$root?.showBookingModal) {
            this.$nextTick(() => this.setupPickers());
        }
    },

    setupPickers() {
        if (this.pickersReady) {
            return;
        }

        if (typeof flatpickr === 'undefined' || !window.OrderWizardDateTime) {
            setTimeout(() => this.setupPickers(), 50);
            return;
        }

        const self = this;
        const ctrl = createOrderBookingDatesController({
            ...config,
            endDateInputId: 'store_end_date',
            restrictPastDates: true,
            setStartAt: (value) => { self.$root.startTime = value; },
            setEndAt: (value) => { self.$root.endTime = value; },
        });

        Object.assign(this, ctrl);
        ctrl.init.call(this);
        this.pickersReady = true;
    },

    syncCombined() {
        const OWD = window.OrderWizardDateTime;
        if (!OWD) {
            return;
        }

        if (this.startPicker) {
            const startTimeSelect = this.$refs.startTimeSelect;
            const endTimeSelect = this.$refs.endTimeSelect;
            this.$root.startTime = OWD.combineDateTime(
                this.startPicker.input?.value || '',
                startTimeSelect?.value || '',
            );
            this.$root.endTime = OWD.combineDateTime(
                this.endPicker?.input?.value || '',
                endTimeSelect?.value || '',
            );
            return;
        }

        const fromDom = readBookingTimesFromDom();
        if (fromDom.start) {
            this.$root.startTime = fromDom.start;
        }
        if (fromDom.end) {
            this.$root.endTime = fromDom.end;
        }
    },
}));

Alpine.data('storefrontShop', (config = {}) => ({
    menuOpen: false,
    hasBookingDates: !!config.hasBookingDates,
    booking: config.booking || { is_set: false },
    bookingSaveUrl: config.bookingSaveUrl || '',
    cartAddUrl: config.cartAddUrl || '',
    catalogById: Object.fromEntries((config.catalogItems || []).map((i) => [String(i.id), i])),
    bookingDefaultsByPriceType: config.bookingDefaultsByPriceType || {},
    i18n: config.i18n || {},

    showBookingModal: false,
    bookingSaving: false,
    bookingError: '',
    startTime: config.booking?.start_at || '',
    endTime: config.booking?.end_at || '',

    showVariantModal: false,
    variantModalItem: null,
    variantModalPick: '',
    variantModalError: '',
    pendingItemId: null,

    searchQuery: '',
    filteredItemCount: 0,
    detailVariantError: '',

    init() {
        if (!this.hasBookingDates) {
            this.$nextTick(() => this.openBookingModal());
        }
        this.$nextTick(() => this.updateFilteredCount());
        this.$watch('searchQuery', () => this.updateFilteredCount());
    },

    matchesItem(name, category, code) {
        const q = this.searchQuery.trim().toLowerCase();
        if (!q) {
            return true;
        }

        const haystack = [name, category, code].filter(Boolean).join(' ').toLowerCase();

        return haystack.includes(q);
    },

    updateFilteredCount() {
        this.$nextTick(() => {
            const cards = document.querySelectorAll('[data-store-item-card]');
            let visible = 0;
            cards.forEach((card) => {
                if (card.offsetParent !== null) {
                    visible += 1;
                }
            });
            this.filteredItemCount = visible;
        });
    },

    openBookingModal() {
        this.bookingError = '';
        this.showBookingModal = true;
        this.$nextTick(() => {
            window.dispatchEvent(new CustomEvent('storefront-booking-modal-open'));
        });
    },

    closeBookingModal() {
        if (!this.hasBookingDates) {
            return;
        }
        this.showBookingModal = false;
    },

    syncBookingTimes() {
        document.dispatchEvent(new CustomEvent('sync-storefront-booking'));
        const fromDom = readBookingTimesFromDom();
        if (fromDom.start) {
            this.startTime = fromDom.start;
        }
        if (fromDom.end) {
            this.endTime = fromDom.end;
        }
    },

    async saveBooking() {
        this.syncBookingTimes();
        await this.$nextTick();

        if (!this.startTime || !this.endTime) {
            this.bookingError = this.i18n.booking_required || 'Select rental dates';
            return;
        }

        this.bookingSaving = true;
        this.bookingError = '';

        try {
            const response = await fetch(this.bookingSaveUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ start_time: this.startTime, end_time: this.endTime }),
                credentials: 'same-origin',
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || !data.success) {
                this.bookingError = data.message || data.errors?.start_time?.[0] || data.errors?.end_time?.[0] || 'Could not save dates';
                return;
            }

            this.hasBookingDates = true;
            this.booking = data.booking || this.booking;
            this.bookingDefaultsByPriceType = data.billing_defaults || {};
            this.showBookingModal = false;
            showToast(data.message || this.i18n.booking_saved);
            window.location.reload();
        } catch {
            this.bookingError = 'Could not save dates';
        } finally {
            this.bookingSaving = false;
        }
    },

    findItem(itemId) {
        return this.catalogById[String(itemId)] || null;
    },

    variantSelectable(variant) {
        if (!variant?.is_available) {
            return false;
        }
        if (variant.manage_stock && (parseInt(variant.stock, 10) || 0) < 1) {
            return false;
        }
        return true;
    },

    addItem(itemId) {
        this.detailVariantError = '';
        if (!this.hasBookingDates) {
            this.pendingItemId = itemId;
            this.openBookingModal();
            return;
        }

        const item = this.findItem(itemId);
        if (!item) {
            return;
        }

        if (item.has_variants) {
            this.variantModalItem = item;
            this.variantModalPick = '';
            this.variantModalError = '';
            this.pendingItemId = itemId;
            this.showVariantModal = true;
            return;
        }

        this.postAddToCart(itemId, null);
    },

    addItemFromDetail(itemId, variantId) {
        this.detailVariantError = '';
        if (!this.hasBookingDates) {
            this.pendingItemId = itemId;
            this.openBookingModal();
            return;
        }

        if (!variantId) {
            this.detailVariantError = this.i18n.select_variant || 'Select a variant';
            return;
        }

        const item = this.findItem(itemId);
        const variant = (item?.variants || []).find((v) => String(v.id) === String(variantId));
        if (!variant || !this.variantSelectable(variant)) {
            this.detailVariantError = this.i18n.select_variant || 'Select a variant';
            return;
        }

        this.postAddToCart(itemId, parseInt(variantId, 10));
    },

    closeVariantModal() {
        this.showVariantModal = false;
        this.variantModalItem = null;
        this.variantModalPick = '';
        this.pendingItemId = null;
    },

    confirmVariantAdd() {
        if (!this.variantModalPick) {
            this.variantModalError = this.i18n.select_variant || 'Select a variant';
            return;
        }
        const variant = (this.variantModalItem?.variants || []).find((v) => String(v.id) === String(this.variantModalPick));
        if (!variant || !this.variantSelectable(variant)) {
            this.variantModalError = this.i18n.select_variant || 'Select a variant';
            return;
        }
        this.postAddToCart(this.pendingItemId, parseInt(this.variantModalPick, 10));
        this.closeVariantModal();
    },

    async postAddToCart(itemId, variantId) {
        const body = new FormData();
        body.append('item_id', String(itemId));
        body.append('quantity', '1');
        if (variantId) {
            body.append('item_variant_id', String(variantId));
        }

        try {
            const response = await fetch(this.cartAddUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body,
                credentials: 'same-origin',
            });
            const data = await response.json().catch(() => ({}));

            if (data.needs_booking) {
                this.openBookingModal();
                showToast(data.message, 'error');
                return;
            }
            if (data.needs_variant) {
                this.addItem(itemId);
                return;
            }
            if (!response.ok || !data.success) {
                showToast(data.message || 'Could not add to cart', 'error');
                return;
            }

            if (typeof data.cart_count === 'number') {
                updateCartBadges(data.cart_count);
            }
            showToast(data.message || this.i18n.added);
        } catch {
            showToast('Could not add to cart', 'error');
        }
    },
}));

Alpine.data('storefrontCheckout', (config = {}) => ({
    fulfillmentType: config.defaultFulfillment || 'pickup',
    cartSubtotal: Number(config.cartSubtotal) || 0,
    deliveryCharge: Number(config.deliveryCharge) || 0,
    freeDeliveryMin: config.freeDeliveryMin ? Number(config.freeDeliveryMin) : null,
    otpSent: false,
    otpSending: false,
    otpMessage: '',
    otpError: '',

    async sendOtp() {
        const mobile = this.$refs.mobile?.value?.trim();
        if (!mobile || mobile.length !== 10) {
            this.otpError = config.mobileInvalid || 'Enter a valid 10-digit mobile number';
            return;
        }

        this.otpSending = true;
        this.otpError = '';
        this.otpMessage = '';

        try {
            const response = await fetch(config.otpUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ mobile }),
                credentials: 'same-origin',
            });

            const data = await response.json();
            if (!response.ok || !data.success) {
                this.otpError = data.message || 'Could not send OTP';
                return;
            }

            this.otpSent = true;
            this.otpMessage = data.message || 'OTP sent';
            if (data.otp) {
                this.otpMessage += ` (${data.otp})`;
            }
        } catch {
            this.otpError = 'Could not send OTP';
        } finally {
            this.otpSending = false;
        }
    },
}));

window.Alpine = Alpine;
Alpine.start();
