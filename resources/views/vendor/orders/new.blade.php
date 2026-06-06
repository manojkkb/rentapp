@extends('vendor.layouts.app')

@section('title', __('vendor.create_order'))
@section('page-title', __('vendor.create_order'))

@section('styles')
@push('vite-before-app')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endpush
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/airbnb.css">
<style>
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
    .flatpickr-day.booking-range-end.booking-in-range {
        border-radius: 0 !important;
    }
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
    .flatpickr-time input { font-size: 1rem !important; }
    .date-input-wrapper { position: relative; }
    .date-input-wrapper .date-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 14px; }
    .date-input-wrapper input { padding-right: 32px; min-height: 40px; font-size: 0.875rem; }
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
    .booking-time-select option { color: #111827; }
    .booking-time-select option[value=""] { color: #9ca3af; }
    @media (min-width: 640px) {
        .date-input-wrapper input { min-height: 2.5rem; font-size: 0.875rem; }
        .booking-time-select { min-height: 2.5rem; }
    }
    .order-wizard-customer-dropdown { z-index: 40; }
</style>
@endsection

@section('content')
    <livewire:vendor.orders.create-order-wizard />
@endsection

@section('scripts')
@vite('resources/js/order-wizard-datetime.js')
<script>
function orderWizardPaymentPage(preview) {
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
            if (!Number.isFinite(x)) return '0.00';
            return (Math.round(x * 100) / 100).toFixed(2);
        },
        depositAmount() {
            const v = parseFloat(String(this.depositValue).replace(',', '.')) || 0;
            const t = this.depositType;
            if (t === 'none' || v <= 0) return 0;
            if (t === 'fixed_amount') return Math.round(v * 100) / 100;
            if (t === 'order_amount') return Math.round(this.grandTotal * v / 100 * 100) / 100;
            if (t === 'product_security_deposit') return Math.round(this.subTotal * v / 100 * 100) / 100;
            return 0;
        },
        totalPayable() {
            return Math.round((this.grandTotal + this.depositAmount()) * 100) / 100;
        },
        depositRuleSummary() {
            const n = this.depositNames[this.depositType] || '';
            if (this.depositType === 'none') return n;
            const raw = String(this.depositValue || '').trim();
            if (!raw) return n;
            const v = parseFloat(raw.replace(',', '.'));
            if (!Number.isFinite(v) || v <= 0) return n;
            if (this.depositType === 'fixed_amount') return n + ' · ₹' + this.fmt(v);
            return n + ' · ' + this.fmt(v) + '%';
        },
        openDepositModal() {
            this.modalDepositType = this.depositType;
            this.modalDepositValue = this.depositType === 'none' ? '' : this.depositValue;
            this.depositModalOpen = true;
        },
        closeDepositModal() { this.depositModalOpen = false; },
        applyDepositModal() {
            this.depositType = this.modalDepositType;
            this.depositValue = this.modalDepositType === 'none' ? '' : String(this.modalDepositValue ?? '');
            this.depositModalOpen = false;
        },
    };
}

function initOrderWizardFulfillmentDateTime() {
    const form = document.getElementById('orderWizard_fulfillment_form');
    if (!form || typeof window.OrderWizardDateTime === 'undefined' || typeof flatpickr === 'undefined') return;

    const OWD = window.OrderWizardDateTime;
    const pickupDate = document.getElementById('pickup_date');
    if (!pickupDate || pickupDate._flatpickr) return;

    function openTimeSelect(selectEl) {
        if (!selectEl) return;
        requestAnimationFrame(function () {
            selectEl.focus();
            if (typeof selectEl.showPicker === 'function') {
                try { selectEl.showPicker(); } catch (e) {}
            }
        });
    }

    function initDateTimeField(config) {
        const dateInput = document.getElementById(config.dateId);
        const timeSelect = document.getElementById(config.timeSelectId);
        const hiddenInput = document.getElementById(config.hiddenId);
        if (!dateInput || !timeSelect || !hiddenInput) return null;

        const safeDate = OWD.sanitizeDateStr(dateInput.value || '');
        dateInput.value = safeDate;

        function syncHidden(dateStr, timeStr) {
            const value = OWD.combineDateTime(dateStr, timeStr);
            hiddenInput.value = value;
            if (typeof config.onValueChange === 'function') config.onValueChange(value);
        }

        function refreshTimes(dateStr) {
            OWD.buildTimeOptions(timeSelect, timeSelect.value || config.prefillTime || '', { dateStr: dateStr || '' });
            syncHidden(dateStr || '', timeSelect.value || '');
        }

        const picker = flatpickr('#' + config.dateId, OWD.flatpickrDateConfig({
            defaultDate: safeDate || null,
            onChange: function (selectedDates, dateStr) {
                if (selectedDates?.length > 0) {
                    refreshTimes(dateStr);
                    openTimeSelect(timeSelect);
                } else {
                    refreshTimes('');
                }
            },
        }));

        timeSelect.addEventListener('change', function () {
            syncHidden(picker.input.value, timeSelect.value);
        });

        refreshTimes(picker.input.value || safeDate);

        return { sync: function () { refreshTimes(picker.input.value); } };
    }

    window.__orderWizardPickupField = initDateTimeField({
        dateId: 'pickup_date',
        timeSelectId: 'pickup_time_select',
        hiddenId: 'pickup_at',
        prefillTime: pickupDate.dataset.prefillTime || '',
        onValueChange: function (value) {
            if (typeof Alpine === 'undefined') return;
            const data = Alpine.$data(form);
            if (data) { data.pickupAt = value; data.fulfillmentStepError = ''; }
        },
    });

    window.__orderWizardDeliveryField = initDateTimeField({
        dateId: 'delivery_date',
        timeSelectId: 'delivery_time_select',
        hiddenId: 'delivery_at',
        prefillTime: document.getElementById('delivery_date')?.dataset.prefillTime || '',
    });
}

document.addEventListener('livewire:initialized', () => {
    function runWizardStepScripts(root) {
        if (! root || typeof root.querySelectorAll !== 'function') return;
        root.querySelectorAll('script[type="text/wizard-script"]').forEach((script) => {
            const run = document.createElement('script');
            run.textContent = script.textContent || '';
            document.body.appendChild(run);
            run.remove();
        });
        if (typeof Alpine !== 'undefined') {
            root.querySelectorAll('[data-wizard-alpine-root]').forEach((node) => {
                Alpine.initTree(node);
            });
        }
    }

    function refreshWizardStepUi(root) {
        runWizardStepScripts(root);
        setTimeout(initOrderWizardFulfillmentDateTime, 50);
    }

    Livewire.hook('morph.updated', ({ el }) => {
        if (! el?.querySelector?.('script[type="text/wizard-script"], [data-wizard-alpine-root]')) {
            return;
        }
        refreshWizardStepUi(el);
    });

    Livewire.on('wizard-step-changed', () => {
        requestAnimationFrame(() => {
            document.querySelectorAll('[wire\\:id]').forEach((root) => {
                if (root.querySelector('script[type="text/wizard-script"], [data-wizard-alpine-root]')) {
                    refreshWizardStepUi(root);
                }
            });
        });
    });
});
</script>
@endsection
