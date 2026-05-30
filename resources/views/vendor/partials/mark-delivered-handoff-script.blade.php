<script>
(function () {
    if (window.VendorMarkDelivered && window.VendorMarkDelivered._initialized) return;

    var labels = {
        noItems: @json(__('vendor.no_items_yet')),
        alreadyDone: @json(__('vendor.mark_delivered_already_done')),
        deliverRequired: @json(__('vendor.deliver_items_required')),
        confirmBase: @json(__('vendor.mark_delivered_confirm')),
        confirmCount: @json(__('vendor.mark_delivered_confirm_count')),
        deliveredUnitsTpl: @json(__('vendor.delivered_units_count', ['delivered' => ':delivered', 'total' => ':total'])),
        updated: @json(__('vendor.rental_status_updated')),
    };

    var state = {
        rentalUrl: null,
        triggerBtn: null,
        onSuccess: null,
    };

    function escapeHtml(text) {
        var d = document.createElement('div');
        d.textContent = text == null ? '' : String(text);
        return d.innerHTML;
    }

    function updateMarkDeliveredConfirmLabel() {
        var btn = document.getElementById('markDeliveredConfirmBtn');
        var label = document.getElementById('markDeliveredConfirmLabel');
        if (!btn || !label) return;
        var n = document.querySelectorAll('.mark-delivered-item-cb:checked:not(:disabled)').length;
        label.textContent = n > 0
            ? labels.confirmCount.replace(':count', String(n))
            : labels.confirmBase;
        btn.disabled = n === 0;
    }

    window.closeMarkDeliveredModal = function () {
        var modal = document.getElementById('markDeliveredModal');
        if (modal) modal.classList.add('hidden');
        document.body.style.overflow = '';
        state.triggerBtn = null;
        state.rentalUrl = null;
        state.onSuccess = null;
        var summary = document.getElementById('markDeliveredAlreadySummary');
        if (summary) {
            summary.textContent = '';
            summary.classList.add('hidden');
        }
        var orderRef = document.getElementById('markDeliveredOrderRef');
        if (orderRef) {
            orderRef.textContent = '';
            orderRef.classList.add('hidden');
        }
        var label = document.getElementById('markDeliveredConfirmLabel');
        var btn = document.getElementById('markDeliveredConfirmBtn');
        if (label) label.textContent = labels.confirmBase;
        if (btn) btn.disabled = false;
    };

    window.openMarkDeliveredModal = function (lines, rentalUrl, triggerBtn, options) {
        options = options || {};
        var list = document.getElementById('markDeliveredItemList');
        var modal = document.getElementById('markDeliveredModal');
        if (!list || !modal) return;

        if (!Array.isArray(lines) || lines.length === 0) {
            if (typeof options.toast === 'function') options.toast(labels.noItems, 'error');
            return;
        }

        var pending = lines.filter(function (l) { return !l.delivered; });
        if (pending.length === 0) {
            if (typeof options.toast === 'function') options.toast(labels.alreadyDone, 'info');
            return;
        }

        state.rentalUrl = rentalUrl;
        state.triggerBtn = triggerBtn || null;
        state.onSuccess = typeof options.onSuccess === 'function' ? options.onSuccess : null;

        var orderRef = document.getElementById('markDeliveredOrderRef');
        if (orderRef) {
            if (options.orderNumber) {
                orderRef.textContent = '#' + options.orderNumber;
                orderRef.classList.remove('hidden');
            } else {
                orderRef.textContent = '';
                orderRef.classList.add('hidden');
            }
        }

        var alreadyDeliveredUnits = lines.reduce(function (sum, l) {
            return sum + (l.delivered ? (l.quantity || 0) : 0);
        }, 0);
        var orderUnits = lines.reduce(function (sum, l) { return sum + (l.quantity || 0); }, 0);
        var deliverAlreadySummary = document.getElementById('markDeliveredAlreadySummary');
        if (deliverAlreadySummary) {
            if (alreadyDeliveredUnits > 0) {
                deliverAlreadySummary.textContent = labels.deliveredUnitsTpl
                    .replace(':delivered', String(alreadyDeliveredUnits))
                    .replace(':total', String(orderUnits));
                deliverAlreadySummary.classList.remove('hidden');
            } else {
                deliverAlreadySummary.textContent = '';
                deliverAlreadySummary.classList.add('hidden');
            }
        }

        var html = '';
        lines.forEach(function (line) {
            var checked = line.delivered ? '' : ' checked';
            var disabled = line.delivered ? ' disabled' : '';
            var badge = line.delivered
                ? '<span class="ml-1.5 inline-flex rounded bg-teal-100 px-1.5 py-0.5 text-[10px] font-semibold text-teal-800">' + escapeHtml(labels.alreadyDone) + '</span>'
                : '';
            html += '<label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm ring-1 ring-gray-100/80' + (line.delivered ? ' opacity-60' : ' hover:bg-teal-50/40') + '">';
            html += '<input type="checkbox" class="mark-delivered-item-cb mt-1 h-4 w-4 shrink-0 rounded border-gray-300 text-teal-600 focus:ring-teal-500" value="' + line.id + '"' + checked + disabled + ' onchange="updateMarkDeliveredConfirmLabel()">';
            html += '<span class="min-w-0 flex-1"><span class="text-sm font-semibold text-gray-900">' + escapeHtml(line.name) + badge + '</span>';
            html += '<span class="mt-0.5 block text-xs text-gray-500">× ' + line.quantity + '</span></span></label>';
        });
        list.innerHTML = html;
        updateMarkDeliveredConfirmLabel();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.updateMarkDeliveredConfirmLabel = updateMarkDeliveredConfirmLabel;

    window.confirmMarkDelivered = function () {
        var ids = [];
        document.querySelectorAll('.mark-delivered-item-cb:checked:not(:disabled)').forEach(function (cb) {
            var id = parseInt(cb.value, 10);
            if (id) ids.push(id);
        });
        if (ids.length === 0) {
            if (window.VendorMarkDelivered && typeof window.VendorMarkDelivered.toast === 'function') {
                window.VendorMarkDelivered.toast(labels.deliverRequired, 'error');
            }
            return;
        }
        if (!state.rentalUrl) return;

        var confirmBtn = document.getElementById('markDeliveredConfirmBtn');
        if (confirmBtn) confirmBtn.disabled = true;
        var triggerBtn = state.triggerBtn;
        if (triggerBtn) triggerBtn.disabled = true;

        var csrf = document.querySelector('meta[name="csrf-token"]');
        fetch(state.rentalUrl, {
            method: 'PATCH',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
            },
            body: JSON.stringify({ delivered: 'mark', order_item_ids: ids }),
        })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (res) {
                if (confirmBtn) confirmBtn.disabled = false;
                if (triggerBtn) triggerBtn.disabled = false;
                if (res.ok && res.data && res.data.success) {
                    closeMarkDeliveredModal();
                    if (typeof state.onSuccess === 'function') {
                        state.onSuccess(res.data);
                    }
                    if (window.VendorMarkDelivered && typeof window.VendorMarkDelivered.toast === 'function') {
                        window.VendorMarkDelivered.toast(res.data.message || labels.updated, 'success');
                    }
                    return;
                }
                var msg = (res.data && res.data.message) ? res.data.message : 'Could not update';
                if (res.data && res.data.errors) {
                    var first = Object.values(res.data.errors)[0];
                    if (first && first[0]) msg = first[0];
                }
                if (window.VendorMarkDelivered && typeof window.VendorMarkDelivered.toast === 'function') {
                    window.VendorMarkDelivered.toast(msg, 'error');
                }
            })
            .catch(function () {
                if (confirmBtn) confirmBtn.disabled = false;
                if (triggerBtn) triggerBtn.disabled = false;
                if (window.VendorMarkDelivered && typeof window.VendorMarkDelivered.toast === 'function') {
                    window.VendorMarkDelivered.toast('Network error', 'error');
                }
            });
    };

    window.VendorMarkDelivered = {
        _initialized: true,
        open: window.openMarkDeliveredModal,
        close: window.closeMarkDeliveredModal,
        toast: null,
        openFromButton: function (btn) {
            if (!btn) return;
            var b64 = btn.getAttribute('data-lines-b64');
            var url = btn.getAttribute('data-rental-url');
            if (!b64 || !url) return;
            var lines;
            try {
                lines = JSON.parse(atob(b64));
            } catch (e) {
                return;
            }
            window.openMarkDeliveredModal(lines, url, btn, {
                orderNumber: btn.getAttribute('data-order-number') || '',
                toast: window.VendorMarkDelivered.toast,
                onSuccess: typeof window.VendorMarkDelivered._defaultOnSuccess === 'function'
                    ? function (data) { window.VendorMarkDelivered._defaultOnSuccess(btn, data); }
                    : null,
            });
        },
    };
})();
</script>
