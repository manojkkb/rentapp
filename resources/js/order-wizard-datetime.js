import './lib/flatpickr';

/**
 * Shared date/time helpers for the vendor order-create wizard.
 * Dates cannot be before today; times on today cannot be in the past.
 */
function pad2(n) {
    return String(n).padStart(2, '0');
}

export function todayYmd() {
    const d = new Date();

    return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
}

export function formatYmd(date) {
    return date.getFullYear() + '-' + pad2(date.getMonth() + 1) + '-' + pad2(date.getDate());
}

export function formatHi(date) {
    return pad2(date.getHours()) + ':' + pad2(date.getMinutes());
}

export function nextAvailableTimeSlot(fromDate = new Date()) {
    const slot = new Date(fromDate);
    slot.setSeconds(0, 0);
    slot.setMilliseconds(0);

    const minute = slot.getMinutes();
    const remainder = minute % 30;

    if (remainder === 0) {
        if (slot <= fromDate) {
            slot.setMinutes(minute + 30);
        }
    } else {
        slot.setMinutes(minute + (30 - remainder));
    }

    return slot;
}

export function defaultBookingDateParts() {
    const startSlot = nextAvailableTimeSlot();
    const endSlot = new Date(startSlot);
    endSlot.setDate(endSlot.getDate() + 1);

    return {
        startDateValue: formatYmd(startSlot),
        endDateValue: formatYmd(endSlot),
        prefillStartTime: formatHi(startSlot),
        prefillEndTime: formatHi(startSlot),
    };
}

export function defaultFulfillmentDateParts() {
    const slot = nextAvailableTimeSlot();

    return {
        dateValue: formatYmd(slot),
        prefillTime: formatHi(slot),
    };
}

export function formatTimeLabel(hour, minute) {
    const period = hour >= 12 ? 'PM' : 'AM';
    const h12 = hour % 12 === 0 ? 12 : hour % 12;
    const m = pad2(minute);

    return h12 + ':' + m + ' ' + period;
}

export function parseDateTime(dateStr, timeStr) {
    if (!dateStr || !timeStr) {
        return null;
    }
    const parts = dateStr.split('-').map(Number);
    const times = timeStr.split(':').map(Number);
    if (parts.length !== 3 || times.length < 2) {
        return null;
    }

    return new Date(parts[0], parts[1] - 1, parts[2], times[0], times[1], 0, 0);
}

export function combineDateTime(dateStr, timeStr) {
    if (!dateStr || !timeStr) {
        return '';
    }

    return dateStr + ' ' + timeStr;
}

export function sanitizeDateStr(dateStr) {
    if (!dateStr || dateStr < todayYmd()) {
        return '';
    }

    return dateStr;
}

/**
 * @param {{ dateStr?: string, minDateTime?: Date|null, strictlyAfter?: boolean }} constraints
 */
export function isTimeSlotAllowed(timeValue, constraints = {}) {
    const dateStr = constraints.dateStr || '';
    if (!dateStr) {
        return true;
    }

    const slot = parseDateTime(dateStr, timeValue);
    if (!slot) {
        return false;
    }

    const now = new Date();
    if (dateStr === todayYmd() && slot <= now) {
        return false;
    }

    const min = constraints.minDateTime;
    if (min instanceof Date && !Number.isNaN(min.getTime())) {
        if (constraints.strictlyAfter) {
            return slot > min;
        }

        return slot >= min;
    }

    return true;
}

/**
 * @param {{ dateStr?: string, minDateTime?: Date|null, strictlyAfter?: boolean }} constraints
 */
export function buildTimeOptions(selectEl, selectedValue, constraints = {}) {
    if (!selectEl) {
        return;
    }

    const placeholder = selectEl.dataset.placeholder || '';
    const previous = selectedValue || selectEl.value || '';
    selectEl.innerHTML = '';

    const empty = document.createElement('option');
    empty.value = '';
    empty.textContent = placeholder;
    selectEl.appendChild(empty);

    let nextSelected = '';

    for (let hour = 0; hour < 24; hour += 1) {
        for (const minute of [0, 30]) {
            const value = pad2(hour) + ':' + pad2(minute);
            if (!isTimeSlotAllowed(value, constraints)) {
                continue;
            }

            const option = document.createElement('option');
            option.value = value;
            option.textContent = formatTimeLabel(hour, minute);
            if (previous === value) {
                option.selected = true;
                nextSelected = value;
            }
            selectEl.appendChild(option);
        }
    }

    if (previous && !nextSelected) {
        selectEl.value = '';
    }

    syncTimeSelectPlaceholderStyle(selectEl);
}

export function syncTimeSelectPlaceholderStyle(selectEl) {
    if (!selectEl) {
        return;
    }

    const apply = function () {
        if (selectEl.dataset.placeholderOpen === '1') {
            return;
        }
        selectEl.classList.toggle('is-placeholder', selectEl.value === '');
    };

    apply();

    if (!selectEl.dataset.placeholderStyleBound) {
        selectEl.dataset.placeholderStyleBound = '1';
        selectEl.addEventListener('change', function () {
            selectEl.dataset.placeholderOpen = '0';
            apply();
        });
        selectEl.addEventListener('focus', function () {
            selectEl.dataset.placeholderOpen = '1';
            selectEl.classList.remove('is-placeholder');
        });
        selectEl.addEventListener('blur', function () {
            selectEl.dataset.placeholderOpen = '0';
            apply();
        });
    }
}

export function flatpickrDateConfig(overrides = {}) {
    return {
        enableTime: false,
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'j F Y',
        allowInput: false,
        // Keep Flatpickr UI on phones so the whole field opens the calendar (not a tiny native control).
        disableMobile: true,
        clickOpens: true,
        monthSelectorType: 'dropdown',
        animate: true,
        minDate: 'today',
        ...overrides,
    };
}

/** Open calendar when tapping anywhere on the date field wrapper (fixes mobile dead zones). */
export function bindDateWrapperOpen(inputEl, picker) {
    if (! inputEl || ! picker || typeof picker.open !== 'function') {
        return;
    }

    const wrapper = inputEl.closest('.date-input-wrapper');
    if (! wrapper || wrapper.dataset.dateTapBound === '1') {
        return;
    }

    wrapper.dataset.dateTapBound = '1';
    wrapper.classList.add('cursor-pointer');

    wrapper.addEventListener('click', (e) => {
        if (e.target.closest('.flatpickr-calendar')) {
            return;
        }
        picker.open();
    });
}

window.OrderWizardDateTime = {
    todayYmd,
    formatYmd,
    formatHi,
    nextAvailableTimeSlot,
    defaultBookingDateParts,
    defaultFulfillmentDateParts,
    formatTimeLabel,
    parseDateTime,
    combineDateTime,
    sanitizeDateStr,
    isTimeSlotAllowed,
    buildTimeOptions,
    syncTimeSelectPlaceholderStyle,
    flatpickrDateConfig,
    bindDateWrapperOpen,
};
