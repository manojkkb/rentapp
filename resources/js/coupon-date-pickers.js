import './lib/flatpickr';
import { bindDateWrapperOpen, flatpickrDateConfig } from './order-wizard-datetime.js';

function normalizeDateStr(dateStr) {
    if (!dateStr || typeof dateStr !== 'string') {
        return '';
    }

    return /^\d{4}-\d{2}-\d{2}$/.test(dateStr.trim()) ? dateStr.trim() : '';
}

function syncHiddenDate(id, value) {
    const el = document.getElementById(id);
    if (el) {
        el.value = value || '';
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }
}

function syncParentAlpineDates(startHiddenId, endHiddenId) {
    const startEl = document.getElementById(startHiddenId);
    if (!startEl) {
        return;
    }

    const form = startEl.closest('form');
    const root = form?._x_dataStack?.[0];
    if (!root) {
        return;
    }

    root.startDate = startEl.value || '';
    root.endDate = document.getElementById(endHiddenId)?.value || '';
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('couponValidityDates', (config) => ({
        startPicker: null,
        endPicker: null,

        init() {
            if (typeof flatpickr === 'undefined') {
                return;
            }

            const startInput = this.$refs.couponStartDate;
            const endInput = this.$refs.couponEndDate;
            if (!startInput || !endInput) {
                return;
            }

            const startHiddenId = config.startHiddenId || 'coupon_start_date';
            const endHiddenId = config.endHiddenId || 'coupon_end_date';
            const startValue = normalizeDateStr(config.startDate || '');
            const endValue = normalizeDateStr(config.endDate || '');
            startInput.value = startValue;
            endInput.value = endValue;
            syncHiddenDate(startHiddenId, startValue);
            syncHiddenDate(endHiddenId, endValue);

            const self = this;

            this.startPicker = flatpickr(startInput, flatpickrDateConfig({
                minDate: null,
                defaultDate: startValue || undefined,
                onChange(_selectedDates, dateStr) {
                    syncHiddenDate(startHiddenId, dateStr || '');
                    syncParentAlpineDates(startHiddenId, endHiddenId);
                    if (self.endPicker) {
                        self.endPicker.set('minDate', dateStr || null);
                        const endVal = self.endPicker.input?.value || '';
                        if (dateStr && endVal && endVal < dateStr) {
                            self.endPicker.clear();
                            syncHiddenDate(endHiddenId, '');
                            syncParentAlpineDates(startHiddenId, endHiddenId);
                        }
                    }
                },
            }));
            bindDateWrapperOpen(startInput, this.startPicker);

            this.endPicker = flatpickr(endInput, flatpickrDateConfig({
                minDate: startValue || null,
                defaultDate: endValue || undefined,
                onChange(_selectedDates, dateStr) {
                    syncHiddenDate(endHiddenId, dateStr || '');
                    syncParentAlpineDates(startHiddenId, endHiddenId);
                },
            }));
            bindDateWrapperOpen(endInput, this.endPicker);
        },

        destroy() {
            this.startPicker?.destroy();
            this.endPicker?.destroy();
        },
    }));
});
