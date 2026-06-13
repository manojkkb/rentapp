document.addEventListener('alpine:init', () => {
    window.Alpine.data('orderWizardPayment', (preview) => {
        const ot = preview.old_type;
        const ov = preview.old_value;
        const validTypes = ['order_amount', 'product_security_deposit', 'fixed_amount', 'none'];
        const initialType = validTypes.includes(ot) ? ot : 'none';
        const initialValue = (ov !== null && ov !== undefined && String(ov) !== '') ? String(ov) : '';

        return {
            depositModalOpen: false,
            depositType: initialType,
            depositValue: initialValue,
            modalDepositType: initialType,
            modalDepositValue: initialValue,
            sdLabels: preview.sd_labels || {},
            depositNames: preview.deposit_names || {},
            subTotal: Number(preview.sub_total) || 0,
            grandTotal: Number(preview.grand_total) || 0,
            fmt(n) {
                const x = Number(n);
                if (!Number.isFinite(x)) {
                    return '0.00';
                }

                return (Math.round(x * 100) / 100).toFixed(2);
            },
            depositAmount() {
                const v = parseFloat(String(this.depositValue).replace(',', '.')) || 0;
                const t = this.depositType;
                if (t === 'none' || v <= 0) {
                    return 0;
                }
                if (t === 'fixed_amount') {
                    return Math.round(v * 100) / 100;
                }
                if (t === 'order_amount') {
                    return Math.round(this.grandTotal * v / 100 * 100) / 100;
                }
                if (t === 'product_security_deposit') {
                    return Math.round(this.subTotal * v / 100 * 100) / 100;
                }

                return 0;
            },
            totalPayable() {
                return Math.round((this.grandTotal + this.depositAmount()) * 100) / 100;
            },
            depositRuleSummary() {
                const n = this.depositNames[this.depositType] || '';
                if (this.depositType === 'none') {
                    return n;
                }
                const raw = String(this.depositValue || '').trim();
                if (!raw) {
                    return n;
                }
                const v = parseFloat(raw.replace(',', '.'));
                if (!Number.isFinite(v) || v <= 0) {
                    return n;
                }
                if (this.depositType === 'fixed_amount') {
                    return n + ' · ₹' + this.fmt(v);
                }

                return n + ' · ' + this.fmt(v) + '%';
            },
            openDepositModal() {
                this.modalDepositType = this.depositType;
                this.modalDepositValue = this.depositType === 'none' ? '' : this.depositValue;
                this.depositModalOpen = true;
            },
            closeDepositModal() {
                this.depositModalOpen = false;
            },
            applyDepositModal() {
                this.depositType = this.modalDepositType;
                this.depositValue = this.modalDepositType === 'none' ? '' : String(this.modalDepositValue ?? '');
                this.depositModalOpen = false;
            },
        };
    });
});
