@extends('vendor.layouts.app')

@section('title', __('vendor.create_order'))
@section('page-title', __('vendor.create_order'))

@section('styles')
@include('vendor.orders.partials.order-wizard-flatpickr-assets')
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
    .date-input-wrapper { position: relative; cursor: pointer; -webkit-tap-highlight-color: transparent; }
    .date-input-wrapper .date-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 14px; z-index: 1; }
    .date-input-wrapper input,
    .date-input-wrapper .flatpickr-input { display: block; width: 100%; box-sizing: border-box; padding-right: 32px; min-height: 40px; font-size: 0.875rem; cursor: pointer; }
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
<script>
function remountFulfillmentPickers() {
    window.dispatchEvent(new CustomEvent('remount-fulfillment-datetimes'));
}

function refreshWizardAlpine(root) {
    if (!root || typeof Alpine === 'undefined') {
        return;
    }
    root.querySelectorAll('[data-wizard-alpine-root]').forEach((node) => {
        if (node._x_dataStack?.length) {
            return;
        }
        Alpine.initTree(node);
    });
    remountFulfillmentPickers();
}

document.addEventListener('livewire:initialized', () => {
    function runWizardStepScripts(root) {
        if (!root || typeof root.querySelectorAll !== 'function') {
            return;
        }
        root.querySelectorAll('script[type="text/wizard-script"]').forEach((script) => {
            const run = document.createElement('script');
            run.textContent = script.textContent || '';
            document.body.appendChild(run);
            run.remove();
        });
    }

    function refreshWizardStepUi(root) {
        runWizardStepScripts(root);
        requestAnimationFrame(() => refreshWizardAlpine(root));
    }

    requestAnimationFrame(() => {
        remountFulfillmentPickers();
        document.querySelectorAll('[wire\\:id]').forEach((root) => {
            if (root.querySelector('[data-wizard-alpine-root]')) {
                refreshWizardStepUi(root);
            }
        });
    });

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
