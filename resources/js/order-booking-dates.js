/**
 * @param {object} config
 * @param {string} [config.startDateValue]
 * @param {string} [config.endDateValue]
 * @param {string} [config.prefillStartTime]
 * @param {string} [config.prefillEndTime]
 * @param {string} [config.endDateInputId]
 * @param {boolean} [config.restrictPastDates]
 * @param {(value: string) => void} [config.setStartAt]
 * @param {(value: string) => void} [config.setEndAt]
 */
export function createOrderBookingDatesController(config) {
    const restrictPastDates = config.restrictPastDates !== false;
    const endDateInputId = config.endDateInputId || 'end_date';

    return {
        prefillStartTime: config.prefillStartTime || '',
        prefillEndTime: config.prefillEndTime || '',
        startPicker: null,
        endPicker: null,
        hoverEndDate: null,

        init() {
            const OWD = window.OrderWizardDateTime;
            if (!OWD || typeof flatpickr === 'undefined') {
                return;
            }

            const startDateInput = this.$refs.startDate;
            const endDateInput = this.$refs.endDate;
            const startTimeSelect = this.$refs.startTimeSelect;
            const endTimeSelect = this.$refs.endTimeSelect;

            const rawStartDate = config.startDateValue || '';
            const rawEndDate = config.endDateValue || '';
            const safeStartDate = restrictPastDates ? OWD.sanitizeDateStr(rawStartDate) : rawStartDate;
            const safeEndDate = restrictPastDates ? OWD.sanitizeDateStr(rawEndDate) : rawEndDate;
            startDateInput.value = safeStartDate;
            endDateInput.value = safeEndDate;

            const self = this;

            const pushStartAt = (value) => {
                if (typeof config.setStartAt === 'function') {
                    config.setStartAt(value);
                }
            };

            const pushEndAt = (value) => {
                if (typeof config.setEndAt === 'function') {
                    config.setEndAt(value);
                }
            };

            const refreshStartTimes = () => {
                OWD.buildTimeOptions(startTimeSelect, startTimeSelect.value || self.prefillStartTime, {
                    dateStr: self.startPicker?.input?.value || safeStartDate,
                });
                pushStartAt(OWD.combineDateTime(self.startPicker?.input?.value || '', startTimeSelect.value || ''));
            };

            const refreshEndTimes = () => {
                const endDate = self.endPicker?.input?.value || '';
                const startDate = self.startPicker?.input?.value || '';
                const startTimeVal = startTimeSelect.value || '';
                let minDateTime = null;
                if (endDate && startDate && endDate === startDate && startTimeVal) {
                    minDateTime = OWD.parseDateTime(startDate, startTimeVal);
                }
                OWD.buildTimeOptions(endTimeSelect, endTimeSelect.value || self.prefillEndTime, {
                    dateStr: endDate,
                    minDateTime,
                    strictlyAfter: !!minDateTime,
                });
                pushEndAt(OWD.combineDateTime(endDate, endTimeSelect.value || ''));
            };

            const openTimeSelect = (selectEl) => {
                if (!selectEl) {
                    return;
                }
                requestAnimationFrame(() => {
                    selectEl.focus();
                    if (typeof selectEl.showPicker === 'function') {
                        try {
                            selectEl.showPicker();
                        } catch (e) {
                            /* unsupported */
                        }
                    }
                });
            };

            const openDatePicker = (picker) => {
                if (!picker || typeof picker.open !== 'function') {
                    return;
                }
                requestAnimationFrame(() => picker.open());
            };

            const dayStart = (date) => new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const isEndPicker = (fp) => fp?.element?.id === endDateInputId;

            const getBookingRange = () => {
                const startRaw = self.startPicker?.selectedDates?.[0];
                const endRaw = self.endPicker?.selectedDates?.[0];

                return {
                    start: startRaw ? dayStart(startRaw) : null,
                    end: endRaw ? dayStart(endRaw) : null,
                };
            };

            const decorateBookingDay = (dayElem, date, fp) => {
                if (!date || Number.isNaN(date.getTime())) {
                    return;
                }
                const { start, end } = getBookingRange();
                const day = dayStart(date);
                dayElem.classList.remove('booking-in-range', 'booking-range-start', 'booking-range-end', 'booking-range-preview-end');
                let rangeEnd = end;
                if (!rangeEnd && isEndPicker(fp) && self.hoverEndDate && start) {
                    rangeEnd = dayStart(self.hoverEndDate);
                    if (rangeEnd < start) {
                        rangeEnd = start;
                    }
                }
                if (start && rangeEnd) {
                    if (day >= start && day <= rangeEnd) {
                        dayElem.classList.add('booking-in-range');
                    }
                    if (day.getTime() === start.getTime()) {
                        dayElem.classList.add('booking-range-start');
                    }
                    if (end && day.getTime() === end.getTime()) {
                        dayElem.classList.add('booking-range-end');
                    } else if (!end && self.hoverEndDate && isEndPicker(fp) && day.getTime() === rangeEnd.getTime()) {
                        dayElem.classList.add('booking-range-preview-end');
                    }
                } else if (start && day.getTime() === start.getTime()) {
                    dayElem.classList.add('booking-range-start', 'booking-in-range');
                }
            };

            const refreshRangeHighlight = (fp) => {
                if (!fp?.calendarContainer) {
                    return;
                }
                fp.calendarContainer.querySelectorAll('.flatpickr-day').forEach((dayEl) => {
                    if (dayEl.dateObj) {
                        decorateBookingDay(dayEl, dayEl.dateObj, fp);
                    }
                });
            };

            const bindEndPickerHover = () => {
                if (!self.endPicker?.calendarContainer || self.endPicker.calendarContainer.dataset.rangeHoverBound) {
                    return;
                }
                self.endPicker.calendarContainer.dataset.rangeHoverBound = '1';
                self.endPicker.calendarContainer.addEventListener('mouseover', (e) => {
                    const dayEl = e.target.closest('.flatpickr-day:not(.flatpickr-disabled)');
                    if (!dayEl?.dateObj) {
                        return;
                    }
                    self.hoverEndDate = dayEl.dateObj;
                    refreshRangeHighlight(self.endPicker);
                });
                self.endPicker.calendarContainer.addEventListener('mouseleave', () => {
                    self.hoverEndDate = null;
                    refreshRangeHighlight(self.endPicker);
                });
            };

            const bookingRangeHooks = {
                onDayCreate(_dObj, _dStr, fp, dayElem) {
                    if (dayElem.dateObj) {
                        decorateBookingDay(dayElem, dayElem.dateObj, fp);
                    }
                },
                onOpen(_selectedDates, _dateStr, fp) {
                    if (isEndPicker(fp)) {
                        bindEndPickerHover();
                    }
                    refreshRangeHighlight(fp);
                },
                onMonthChange(_selectedDates, _dateStr, fp) {
                    refreshRangeHighlight(fp);
                },
                onYearChange(_selectedDates, _dateStr, fp) {
                    refreshRangeHighlight(fp);
                },
            };

            const afterBookingRangeChange = () => {
                if (self.startPicker) {
                    refreshRangeHighlight(self.startPicker);
                }
                if (self.endPicker) {
                    refreshRangeHighlight(self.endPicker);
                }
            };

            const fpDateConfig = OWD.flatpickrDateConfig(
                restrictPastDates ? {} : { minDate: null },
            );

            self.startPicker = flatpickr(startDateInput, {
                ...fpDateConfig,
                ...bookingRangeHooks,
                defaultDate: safeStartDate || null,
                onReady(_selectedDates, _dateStr, fp) {
                    OWD.bindDateWrapperOpen(startDateInput, fp);
                },
                onChange(selectedDates) {
                    if (selectedDates?.length > 0) {
                        refreshStartTimes();
                        if (self.endPicker) {
                            self.endPicker.set('minDate', selectedDates[0]);
                        }
                        openTimeSelect(startTimeSelect);
                    } else {
                        pushStartAt('');
                        if (self.endPicker) {
                            self.endPicker.set('minDate', restrictPastDates ? 'today' : null);
                        }
                    }
                    refreshEndTimes();
                    afterBookingRangeChange();
                },
            });

            self.endPicker = flatpickr(endDateInput, {
                ...fpDateConfig,
                ...bookingRangeHooks,
                defaultDate: safeEndDate || null,
                onReady(_selectedDates, _dateStr, fp) {
                    OWD.bindDateWrapperOpen(endDateInput, fp);
                    bindEndPickerHover();
                    afterBookingRangeChange();
                },
                onChange(selectedDates) {
                    self.hoverEndDate = null;
                    if (selectedDates?.length > 0) {
                        refreshEndTimes();
                        if (self.startPicker) {
                            self.startPicker.set('maxDate', selectedDates[0]);
                        }
                        openTimeSelect(endTimeSelect);
                    } else {
                        pushEndAt('');
                        if (self.startPicker) {
                            self.startPicker.set('maxDate', null);
                        }
                    }
                    afterBookingRangeChange();
                },
            });

            startTimeSelect.addEventListener('change', () => {
                pushStartAt(OWD.combineDateTime(self.startPicker.input.value, startTimeSelect.value));
                refreshEndTimes();
                if (startTimeSelect.value) {
                    openDatePicker(self.endPicker);
                }
            });

            endTimeSelect.addEventListener('change', () => {
                pushEndAt(OWD.combineDateTime(self.endPicker.input.value, endTimeSelect.value));
            });

            if (self.startPicker?.selectedDates?.length > 0) {
                self.endPicker?.set('minDate', self.startPicker.selectedDates[0]);
            } else if (restrictPastDates) {
                self.endPicker?.set('minDate', 'today');
            }
            if (self.endPicker?.selectedDates?.length > 0) {
                self.startPicker?.set('maxDate', self.endPicker.selectedDates[0]);
            }
            refreshStartTimes();
            refreshEndTimes();
            afterBookingRangeChange();
        },

        syncCombined() {
            const OWD = window.OrderWizardDateTime;
            if (!OWD || !this.startPicker) {
                return;
            }
            const startTimeSelect = this.$refs.startTimeSelect;
            const endTimeSelect = this.$refs.endTimeSelect;
            if (typeof config.setStartAt === 'function') {
                config.setStartAt(OWD.combineDateTime(this.startPicker.input.value, startTimeSelect?.value || ''));
            }
            if (typeof config.setEndAt === 'function') {
                config.setEndAt(OWD.combineDateTime(this.endPicker?.input?.value || '', endTimeSelect?.value || ''));
            }
        },
    };
}

function syncHiddenValue(id, value) {
    const el = document.getElementById(id);
    if (el) {
        el.value = value || '';
    }
}

function registerOrderBookingDates() {
    window.Alpine.data('orderBookingDates', (config) => createOrderBookingDatesController({
        ...config,
        setStartAt: (value) => syncHiddenValue(config.startAtHiddenId || 'edit_start_at', value),
        setEndAt: (value) => syncHiddenValue(config.endAtHiddenId || 'edit_end_at', value),
    }));
}

if (window.Alpine) {
    registerOrderBookingDates();
} else {
    document.addEventListener('alpine:init', registerOrderBookingDates);
}
