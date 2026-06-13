document.addEventListener('alpine:init', () => {
    window.Alpine.data('orderWizardSummary', (config) => {
        const livewireWizard = Boolean(config.livewireWizard);

        return {
            rentalPeriods: config.rentalPeriods || {},
            billingUnitsLabels: config.billingUnitsLabels || {},
            bookingDefaultUnitsByPriceType: config.bookingDefaultUnitsByPriceType || {},
            billingUnitsFallback: config.i18n?.billing_units || 'Billing units',
            editOpen: false,
            editLineKey: null,
            editItemId: null,
            editName: '',
            editQty: 1,
            editPrice: '',
            editRentalPeriod: 'per_day',
            editBilling: '',
            editUsesBilling: false,
            removeOpen: false,
            removeLineKey: null,
            removeItemId: null,
            removeItemName: '',
            rentalPeriodUsesBilling(period) {
                return String(period || '') !== 'fixed';
            },
            formatRupeeInt(amount) {
                const n = parseInt(String(Math.round(Number(amount) || 0)), 10) || 0;

                return n.toLocaleString('en-IN');
            },
            formatBillingUnitsDisplay(val) {
                const n = parseFloat(String(val));
                if (!Number.isFinite(n)) {
                    return '0';
                }

                return n.toFixed(2).replace(/\.?0+$/, '') || '0';
            },
            defaultBillingUnitsForType(period) {
                const map = this.bookingDefaultUnitsByPriceType || {};
                const raw = map[period];
                const v = raw !== undefined && raw !== null ? parseFloat(String(raw)) : NaN;

                return Number.isFinite(v) ? v : 1;
            },
            openEdit(d) {
                this.editLineKey = d.line_key || String(d.item_id);
                this.editItemId = d.item_id;
                this.editName = d.name ?? '';
                this.editQty = parseInt(String(d.quantity), 10) || 1;
                this.editRentalPeriod = d.rental_period || 'per_day';
                this.editUsesBilling = this.rentalPeriodUsesBilling(this.editRentalPeriod);
                const price = parseFloat(String(d.unit_price ?? d.price ?? ''));
                this.editPrice = Number.isFinite(price) ? String(price) : '';
                if (this.editUsesBilling) {
                    if (d.billing_units !== null && d.billing_units !== undefined && d.billing_units !== '') {
                        const n = parseFloat(String(d.billing_units));
                        this.editBilling = Number.isFinite(n) ? String(n) : String(this.defaultBillingUnitsForType(this.editRentalPeriod));
                    } else {
                        this.editBilling = String(this.defaultBillingUnitsForType(this.editRentalPeriod));
                    }
                } else {
                    this.editBilling = '';
                }
                this.editOpen = true;
                document.documentElement.classList.add('overflow-hidden');
            },
            closeEdit() {
                this.editOpen = false;
                if (!this.removeOpen) {
                    document.documentElement.classList.remove('overflow-hidden');
                }
            },
            onEditRentalChange() {
                this.editUsesBilling = this.rentalPeriodUsesBilling(this.editRentalPeriod);
                if (!this.editUsesBilling) {
                    this.editBilling = '';
                    return;
                }
                if (!this.editBilling || this.editBilling === '') {
                    this.editBilling = String(this.defaultBillingUnitsForType(this.editRentalPeriod));
                }
            },
            editBillingLabel() {
                return this.billingUnitsLabels[this.editRentalPeriod] || this.billingUnitsFallback;
            },
            editRentalPeriodLabel() {
                return this.rentalPeriods[this.editRentalPeriod] || this.editRentalPeriod || '';
            },
            editPreviewTotal() {
                const price = parseFloat(String(this.editPrice));
                const qty = parseInt(String(this.editQty), 10);
                if (!Number.isFinite(price) || !Number.isFinite(qty) || qty < 1) {
                    return 0;
                }
                let total = price * qty;
                if (this.rentalPeriodUsesBilling(this.editRentalPeriod)) {
                    const bu = parseFloat(String(this.editBilling));
                    if (Number.isFinite(bu) && bu >= 0.01) {
                        total *= bu;
                    }
                }

                return Math.round(total);
            },
            openRemove(d) {
                this.removeLineKey = d.line_key || String(d.item_id);
                this.removeItemId = d.item_id;
                this.removeItemName = d.name ?? '';
                this.removeOpen = true;
                document.documentElement.classList.add('overflow-hidden');
            },
            closeRemove() {
                this.removeOpen = false;
                this.removeItemId = null;
                this.removeItemName = '';
                if (!this.editOpen) {
                    document.documentElement.classList.remove('overflow-hidden');
                }
            },
            onEscape() {
                if (this.removeOpen) {
                    this.closeRemove();
                } else if (this.editOpen) {
                    this.closeEdit();
                }
            },
            submitEditLine(ev) {
                if (!livewireWizard) {
                    ev.target.submit();
                    return;
                }
                ev.preventDefault();
                this.$wire.updateSummaryLine({
                    line_key: this.editLineKey,
                    item_id: this.editItemId,
                    quantity: this.editQty,
                    price: this.editPrice,
                    rental_period: this.editRentalPeriod,
                    billing_units: this.editUsesBilling ? this.editBilling : null,
                }).then(() => this.closeEdit());
            },
            submitRemoveLine(ev) {
                if (!livewireWizard) {
                    ev.target.submit();
                    return;
                }
                ev.preventDefault();
                this.$wire.removeSummaryLine({
                    line_key: this.removeLineKey,
                    item_id: this.removeItemId,
                }).then(() => this.closeRemove());
            },
        };
    });
});
