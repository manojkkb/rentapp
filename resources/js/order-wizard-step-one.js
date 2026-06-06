document.addEventListener('alpine:init', () => {
    Alpine.data('orderWizardCustomerPicker', (config) => ({
        customers: config.customers ?? [],
        dropdownOpen: false,
        searchLabel: '',
        searchQuery: '',

        init() {
            this.syncLabelFromWire();
            this.$wire.$watch('customerId', () => this.syncLabelFromWire());
        },

        syncLabelFromWire() {
            const id = this.$wire.customerId;
            if (! id) {
                if (! this.dropdownOpen) {
                    this.searchLabel = '';
                }
                return;
            }
            const c = this.customers.find((x) => Number(x.id) === Number(id));
            if (c) {
                this.searchLabel = c.name + ' — ' + c.mobile;
            }
        },

        isSelected(customerId) {
            return Number(this.$wire.customerId) === Number(customerId);
        },

        get filteredCustomers() {
            const q = this.searchQuery.toLowerCase().trim();
            if (! q) {
                return this.customers;
            }
            return this.customers.filter((c) =>
                c.name.toLowerCase().includes(q) || String(c.mobile).toLowerCase().includes(q)
            );
        },

        openDropdown() {
            this.dropdownOpen = true;
            this.searchQuery = this.$wire.customerId ? '' : this.searchLabel;
        },

        onSearchInput() {
            this.searchQuery = this.searchLabel;
            if (this.$wire.customerId) {
                this.$wire.set('customerId', null);
            }
            this.dropdownOpen = true;
        },

        selectCustomer(customer) {
            this.$wire.set('customerId', customer.id);
            this.searchLabel = customer.name + ' — ' + customer.mobile;
            this.searchQuery = '';
            this.dropdownOpen = false;
            this.$wire.set('showNewCustomerForm', false);
        },

        clearSelection() {
            this.$wire.set('customerId', null);
            this.searchLabel = '';
            this.searchQuery = '';
            this.dropdownOpen = true;
        },

        openNewCustomer() {
            this.dropdownOpen = false;
            this.$wire.openNewCustomerForm(this.searchQuery.trim() || this.searchLabel.trim());
        },

        onCustomerSaved(detail) {
            if (detail?.customers) {
                this.customers = detail.customers;
            }
            this.syncLabelFromWire();
            if (detail?.message && typeof showToast === 'function') {
                showToast(detail.message, 'success');
            }
        },
    }));

    Alpine.data('orderWizardBookingDates', (config) => ({
        prefillStartTime: config.prefillStartTime || '',
        prefillEndTime: config.prefillEndTime || '',
        startPicker: null,
        endPicker: null,
        hoverEndDate: null,

        init() {
            const OWD = window.OrderWizardDateTime;
            if (! OWD || typeof flatpickr === 'undefined') {
                return;
            }

            const startDateInput = this.$refs.startDate;
            const endDateInput = this.$refs.endDate;
            const startTimeSelect = this.$refs.startTimeSelect;
            const endTimeSelect = this.$refs.endTimeSelect;

            const safeStartDate = OWD.sanitizeDateStr(config.startDateValue || '');
            const safeEndDate = OWD.sanitizeDateStr(config.endDateValue || '');
            startDateInput.value = safeStartDate;
            endDateInput.value = safeEndDate;

            const self = this;

            const refreshStartTimes = () => {
                OWD.buildTimeOptions(startTimeSelect, startTimeSelect.value || self.prefillStartTime, {
                    dateStr: self.startPicker?.input?.value || safeStartDate,
                });
                self.$wire.set('startTime', OWD.combineDateTime(self.startPicker?.input?.value || '', startTimeSelect.value || ''));
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
                    strictlyAfter: !! minDateTime,
                });
                self.$wire.set('endTime', OWD.combineDateTime(endDate, endTimeSelect.value || ''));
            };

            const openTimeSelect = (selectEl) => {
                if (! selectEl) return;
                requestAnimationFrame(() => {
                    selectEl.focus();
                    if (typeof selectEl.showPicker === 'function') {
                        try { selectEl.showPicker(); } catch (e) { /* unsupported */ }
                    }
                });
            };

            const openDatePicker = (picker) => {
                if (! picker || typeof picker.open !== 'function') return;
                requestAnimationFrame(() => picker.open());
            };

            const dayStart = (date) => new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const isEndPicker = (fp) => fp?.element?.id === 'end_date';

            const getBookingRange = () => {
                const startRaw = self.startPicker?.selectedDates?.[0];
                const endRaw = self.endPicker?.selectedDates?.[0];
                return {
                    start: startRaw ? dayStart(startRaw) : null,
                    end: endRaw ? dayStart(endRaw) : null,
                };
            };

            const decorateBookingDay = (dayElem, date, fp) => {
                if (! date || Number.isNaN(date.getTime())) return;
                const { start, end } = getBookingRange();
                const day = dayStart(date);
                dayElem.classList.remove('booking-in-range', 'booking-range-start', 'booking-range-end', 'booking-range-preview-end');
                let rangeEnd = end;
                if (! rangeEnd && isEndPicker(fp) && self.hoverEndDate && start) {
                    rangeEnd = dayStart(self.hoverEndDate);
                    if (rangeEnd < start) rangeEnd = start;
                }
                if (start && rangeEnd) {
                    if (day >= start && day <= rangeEnd) dayElem.classList.add('booking-in-range');
                    if (day.getTime() === start.getTime()) dayElem.classList.add('booking-range-start');
                    if (end && day.getTime() === end.getTime()) {
                        dayElem.classList.add('booking-range-end');
                    } else if (! end && self.hoverEndDate && isEndPicker(fp) && day.getTime() === rangeEnd.getTime()) {
                        dayElem.classList.add('booking-range-preview-end');
                    }
                } else if (start && day.getTime() === start.getTime()) {
                    dayElem.classList.add('booking-range-start', 'booking-in-range');
                }
            };

            const refreshRangeHighlight = (fp) => {
                if (! fp?.calendarContainer) return;
                fp.calendarContainer.querySelectorAll('.flatpickr-day').forEach((dayEl) => {
                    if (dayEl.dateObj) decorateBookingDay(dayEl, dayEl.dateObj, fp);
                });
            };

            const bindEndPickerHover = () => {
                if (! self.endPicker?.calendarContainer || self.endPicker.calendarContainer.dataset.rangeHoverBound) return;
                self.endPicker.calendarContainer.dataset.rangeHoverBound = '1';
                self.endPicker.calendarContainer.addEventListener('mouseover', (e) => {
                    const dayEl = e.target.closest('.flatpickr-day:not(.flatpickr-disabled)');
                    if (! dayEl?.dateObj) return;
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
                    if (dayElem.dateObj) decorateBookingDay(dayElem, dayElem.dateObj, fp);
                },
                onOpen(_selectedDates, _dateStr, fp) {
                    if (isEndPicker(fp)) bindEndPickerHover();
                    refreshRangeHighlight(fp);
                },
                onMonthChange(_selectedDates, _dateStr, fp) { refreshRangeHighlight(fp); },
                onYearChange(_selectedDates, _dateStr, fp) { refreshRangeHighlight(fp); },
            };

            const afterBookingRangeChange = () => {
                if (self.startPicker) refreshRangeHighlight(self.startPicker);
                if (self.endPicker) refreshRangeHighlight(self.endPicker);
            };

            const fpDateConfig = OWD.flatpickrDateConfig();

            self.startPicker = flatpickr(startDateInput, {
                ...fpDateConfig,
                ...bookingRangeHooks,
                defaultDate: safeStartDate || null,
                onChange(selectedDates) {
                    if (selectedDates?.length > 0) {
                        refreshStartTimes();
                        if (self.endPicker) self.endPicker.set('minDate', selectedDates[0]);
                        openTimeSelect(startTimeSelect);
                    } else {
                        self.$wire.set('startTime', '');
                        if (self.endPicker) self.endPicker.set('minDate', 'today');
                    }
                    refreshEndTimes();
                    afterBookingRangeChange();
                },
            });

            self.endPicker = flatpickr(endDateInput, {
                ...fpDateConfig,
                ...bookingRangeHooks,
                defaultDate: safeEndDate || null,
                onReady() {
                    bindEndPickerHover();
                    afterBookingRangeChange();
                },
                onChange(selectedDates) {
                    self.hoverEndDate = null;
                    if (selectedDates?.length > 0) {
                        refreshEndTimes();
                        if (self.startPicker) self.startPicker.set('maxDate', selectedDates[0]);
                        openTimeSelect(endTimeSelect);
                    } else {
                        self.$wire.set('endTime', '');
                        if (self.startPicker) self.startPicker.set('maxDate', null);
                    }
                    afterBookingRangeChange();
                },
            });

            startTimeSelect.addEventListener('change', () => {
                self.$wire.set('startTime', OWD.combineDateTime(self.startPicker.input.value, startTimeSelect.value));
                refreshEndTimes();
                if (startTimeSelect.value) openDatePicker(self.endPicker);
            });

            endTimeSelect.addEventListener('change', () => {
                self.$wire.set('endTime', OWD.combineDateTime(self.endPicker.input.value, endTimeSelect.value));
            });

            if (self.startPicker?.selectedDates?.length > 0) {
                self.endPicker?.set('minDate', self.startPicker.selectedDates[0]);
            } else {
                self.endPicker?.set('minDate', 'today');
            }
            if (self.endPicker?.selectedDates?.length > 0) {
                self.startPicker?.set('maxDate', self.endPicker.selectedDates[0]);
            }
            refreshStartTimes();
            refreshEndTimes();
            afterBookingRangeChange();
        },

        syncToWire() {
            const OWD = window.OrderWizardDateTime;
            if (! OWD || ! this.startPicker) return;
            const startTimeSelect = this.$refs.startTimeSelect;
            const endTimeSelect = this.$refs.endTimeSelect;
            this.$wire.set('startTime', OWD.combineDateTime(this.startPicker.input.value, startTimeSelect?.value || ''));
            this.$wire.set('endTime', OWD.combineDateTime(this.endPicker?.input?.value || '', endTimeSelect?.value || ''));
        },
    }));
});
