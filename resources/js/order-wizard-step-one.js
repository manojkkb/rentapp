import { createOrderBookingDatesController } from './order-booking-dates.js';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('orderWizardCustomerPicker', (config) => ({
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

    window.Alpine.data('orderWizardBookingDates', (config) => {
        let syncCombinedFn = () => {};

        return {
            init() {
                const ctrl = createOrderBookingDatesController({
                    ...config,
                    endDateInputId: 'end_date',
                    restrictPastDates: true,
                    setStartAt: (value) => this.$wire.set('startTime', value),
                    setEndAt: (value) => this.$wire.set('endTime', value),
                });
                Object.assign(this, ctrl);
                syncCombinedFn = ctrl.syncCombined.bind(this);
                ctrl.init.call(this);
            },

            syncToWire() {
                syncCombinedFn();
            },
        };
    });
});
