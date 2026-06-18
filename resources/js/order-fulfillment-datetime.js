/**
 * Flatpickr date + 30-min time select for order pickup / delivery fields.
 */

/**
 * @param {object} config
 * @param {HTMLElement} config.dateInput
 * @param {HTMLSelectElement} config.timeSelect
 * @param {HTMLInputElement} config.hiddenInput
 * @param {string} [config.prefillTime]
 * @param {boolean} [config.restrictPastDates]
 * @param {(value: string) => void} [config.onValueChange]
 */
export function initFulfillmentDatetimeField(config) {
    const OWD = window.OrderWizardDateTime;
    if (!OWD || typeof flatpickr === 'undefined') {
        return null;
    }

    const { dateInput, timeSelect, hiddenInput } = config;
    if (!dateInput || !timeSelect || !hiddenInput) {
        return null;
    }

    if (dateInput._flatpickr) {
        dateInput._flatpickr.destroy();
    }

    const restrictPastDates = config.restrictPastDates !== false;
    let rawDate = dateInput.value || '';
    let prefillTime = config.prefillTime || '';

    if (!rawDate && !prefillTime) {
        const defaults = OWD.defaultFulfillmentDateParts();
        rawDate = defaults.dateValue;
        prefillTime = defaults.prefillTime;
    }

    const safeDate = restrictPastDates ? OWD.sanitizeDateStr(rawDate) : rawDate;
    dateInput.value = safeDate;

    function syncHidden(dateStr, timeStr) {
        const value = OWD.combineDateTime(dateStr, timeStr);
        hiddenInput.value = value;
        if (typeof config.onValueChange === 'function') {
            config.onValueChange(value);
        }
    }

    function refreshTimes(dateStr) {
        OWD.buildTimeOptions(timeSelect, timeSelect.value || prefillTime || '', {
            dateStr: dateStr || '',
        });
        syncHidden(dateStr || '', timeSelect.value || '');
    }

    function openTimeSelect() {
        requestAnimationFrame(() => {
            timeSelect.focus();
            if (typeof timeSelect.showPicker === 'function') {
                try {
                    timeSelect.showPicker();
                } catch (e) {
                    /* unsupported */
                }
            }
        });
    }

    const picker = flatpickr(dateInput, OWD.flatpickrDateConfig({
        minDate: restrictPastDates ? undefined : null,
        defaultDate: safeDate || null,
        onReady(_selectedDates, _dateStr, fp) {
            OWD.bindDateWrapperOpen(dateInput, fp);
        },
        onChange(selectedDates, dateStr) {
            if (selectedDates?.length > 0) {
                refreshTimes(dateStr);
                openTimeSelect();
            } else {
                refreshTimes('');
            }
        },
    }));

    timeSelect.addEventListener('change', () => {
        syncHidden(picker.input.value, timeSelect.value);
    });

    refreshTimes(picker.input.value || safeDate);

    return {
        sync() {
            refreshTimes(picker.input.value);
        },
        destroy() {
            picker?.destroy();
        },
    };
}

function isFulfillmentFieldVisible(el) {
    if (!el) {
        return false;
    }
    if (el.offsetParent !== null) {
        return true;
    }

    return window.getComputedStyle(el).display !== 'none';
}

function registerOrderFulfillmentDatetime() {
    window.Alpine.data('orderFulfillmentDatetime', (config) => ({
        _field: null,

        init() {
            this.$nextTick(() => {
                this.mountIfVisible();
            });
        },

        isVisible() {
            return isFulfillmentFieldVisible(this.$el);
        },

        mountIfVisible() {
            if (!this.isVisible()) {
                return;
            }
            this.mountField();
        },

        mountField() {
            if (this._field?.destroy) {
                this._field.destroy();
                this._field = null;
            }

            const dateInput = this.$refs.dateInput;
            const timeSelect = this.$refs.timeSelect;
            const hiddenInput = document.getElementById(config.hiddenId);

            if (!dateInput || !timeSelect || !hiddenInput) {
                return;
            }

            this._field = initFulfillmentDatetimeField({
                dateInput,
                timeSelect,
                hiddenInput,
                prefillTime: config.prefillTime || '',
                restrictPastDates: config.restrictPastDates !== false,
                onValueChange: (value) => {
                    if (config.changeEvent) {
                        document.dispatchEvent(new CustomEvent(config.changeEvent, { detail: value }));
                    }
                },
            });
        },

        sync() {
            if (!this._field) {
                this.mountIfVisible();
            }
            this._field?.sync();
        },
    }));
}

if (window.Alpine) {
    registerOrderFulfillmentDatetime();
} else {
    document.addEventListener('alpine:init', registerOrderFulfillmentDatetime);
}
