<script>
(function () {
    if (window.VendorMarkReturned && window.VendorMarkReturned._initialized) return;

    var labels = {
        noItems: @json(__('vendor.no_items_yet')),
        alreadyDone: @json(__('vendor.mark_returned_already_done')),
        returnRequired: @json(__('vendor.return_items_required')),
        confirmBase: @json(__('vendor.mark_returned_confirm')),
        confirmCount: @json(__('vendor.mark_returned_confirm_count')),
        returnedUnitsTpl: @json(__('vendor.returned_units_count', ['returned' => ':returned', 'total' => ':total'])),
        returnQtyLabel: @json(__('vendor.return_qty_label')),
        qtyLabel: @json(__('vendor.order_wizard_qty')),
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

    function updateMarkReturnedConfirmLabel() {
        var btn = document.getElementById('markReturnedConfirmBtn');
        var label = document.getElementById('markReturnedConfirmLabel');
        if (!btn || !label) return;
        var n = document.querySelectorAll('.mark-returned-item-cb:checked:not(:disabled)').length;
        label.textContent = n > 0
            ? labels.confirmCount.replace(':count', String(n))
            : labels.confirmBase;
        btn.disabled = n === 0;
    }

    window.toggleMarkReturnedQtyInput = function (cb) {
        var wrap = cb.closest('.rounded-lg');
        if (!wrap) return;
        var qtyInput = wrap.querySelector('.mark-returned-item-qty');
        if (!qtyInput) return;
        qtyInput.disabled = !cb.checked || cb.disabled;
    };

    window.closeMarkReturnedModal = function () {
        var modal = document.getElementById('markReturnedModal');
        if (modal) modal.classList.add('hidden');
        document.body.style.overflow = '';
        state.triggerBtn = null;
        state.rentalUrl = null;
        state.onSuccess = null;
        var summary = document.getElementById('markReturnedAlreadySummary');
        if (summary) {
            summary.textContent = '';
            summary.classList.add('hidden');
        }
        var orderRef = document.getElementById('markReturnedOrderRef');
        if (orderRef) {
            orderRef.textContent = '';
            orderRef.classList.add('hidden');
        }
        var label = document.getElementById('markReturnedConfirmLabel');
        var btn = document.getElementById('markReturnedConfirmBtn');
        if (label) label.textContent = labels.confirmBase;
        if (btn) btn.disabled = false;
    };

    window.openMarkReturnedModal = function (lines, rentalUrl, triggerBtn, options) {
        options = options || {};
        var list = document.getElementById('markReturnedItemList');
        var modal = document.getElementById('markReturnedModal');
        if (!list || !modal) return;

        if (!Array.isArray(lines) || lines.length === 0) {
            if (typeof options.toast === 'function') options.toast(labels.noItems, 'error');
            return;
        }

        var pending = lines.filter(function (l) {
            return l.delivered && !l.fully_returned && l.returnable_qty > 0;
        });
        if (pending.length === 0) {
            if (typeof options.toast === 'function') options.toast(labels.alreadyDone, 'info');
            return;
        }

        state.rentalUrl = rentalUrl;
        state.triggerBtn = triggerBtn || null;
        state.onSuccess = typeof options.onSuccess === 'function' ? options.onSuccess : null;

        var orderRef = document.getElementById('markReturnedOrderRef');
        if (orderRef) {
            if (options.orderNumber) {
                orderRef.textContent = '#' + options.orderNumber;
                orderRef.classList.remove('hidden');
            } else {
                orderRef.textContent = '';
                orderRef.classList.add('hidden');
            }
        }

        var alreadyReturnedUnits = lines.reduce(function (sum, l) { return sum + (l.returned_qty || 0); }, 0);
        var orderUnits = lines.reduce(function (sum, l) { return sum + (l.order_qty || 0); }, 0);
        var alreadySummary = document.getElementById('markReturnedAlreadySummary');
        if (alreadySummary) {
            if (alreadyReturnedUnits > 0) {
                alreadySummary.textContent = labels.returnedUnitsTpl
                    .replace(':returned', String(alreadyReturnedUnits))
                    .replace(':total', String(orderUnits));
                alreadySummary.classList.remove('hidden');
            } else {
                alreadySummary.textContent = '';
                alreadySummary.classList.add('hidden');
            }
        }

        var html = '';
        lines.forEach(function (line) {
            if (!line.delivered || line.fully_returned) return;
            var minQty = Math.max(1, (line.returned_qty || 0) + 1);
            if (minQty > line.order_qty) return;
            var defaultQty = line.order_qty;
            html += '<div class="rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm ring-1 ring-gray-100/80">';
            html += '<label class="flex cursor-pointer items-start gap-3">';
            html += '<input type="checkbox" class="mark-returned-item-cb mt-1 h-4 w-4 shrink-0 rounded border-gray-300 text-teal-600 focus:ring-teal-500" value="' + line.id + '" data-max-qty="' + line.order_qty + '" data-min-qty="' + minQty + '" checked onchange="toggleMarkReturnedQtyInput(this); updateMarkReturnedConfirmLabel()">';
            html += '<span class="min-w-0 flex-1"><span class="text-sm font-semibold text-gray-900">' + escapeHtml(line.name) + '</span>';
            html += '<span class="mt-0.5 block text-xs text-gray-500">' + escapeHtml(labels.returnQtyLabel) + ': ';
            html += '<span class="font-medium text-gray-700">' + line.returned_qty + '</span> / ' + line.order_qty + ' ' + escapeHtml(labels.qtyLabel) + '</span></span></label>';
            html += '<div class="mt-2 flex items-center gap-2 pl-7">';
            html += '<input type="number" min="' + minQty + '" max="' + line.order_qty + '" step="1" class="mark-returned-item-qty w-20 rounded-lg border border-gray-200 px-2 py-1.5 text-sm font-semibold tabular-nums text-gray-900 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/25" value="' + defaultQty + '" data-order-item-id="' + line.id + '">';
            html += '<span class="text-xs text-gray-500">/ ' + line.order_qty + '</span></div></div>';
        });
        list.innerHTML = html;
        updateMarkReturnedConfirmLabel();
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    window.updateMarkReturnedConfirmLabel = updateMarkReturnedConfirmLabel;

    window.confirmMarkReturned = function () {
        var returnLines = [];
        document.querySelectorAll('.mark-returned-item-cb:checked:not(:disabled)').forEach(function (cb) {
            var id = parseInt(cb.value, 10);
            if (!id) return;
            var wrap = cb.closest('.rounded-lg');
            var qtyInput = wrap ? wrap.querySelector('.mark-returned-item-qty') : null;
            var qty = qtyInput ? parseInt(qtyInput.value, 10) : parseInt(cb.getAttribute('data-max-qty'), 10);
            var maxQty = parseInt(cb.getAttribute('data-max-qty'), 10) || qty;
            var minQty = parseInt(cb.getAttribute('data-min-qty'), 10) || 1;
            if (!Number.isFinite(qty) || qty < minQty) qty = minQty;
            if (qty > maxQty) qty = maxQty;
            returnLines.push({ order_item_id: id, quantity: qty });
        });
        if (returnLines.length === 0) {
            if (window.VendorMarkReturned && typeof window.VendorMarkReturned.toast === 'function') {
                window.VendorMarkReturned.toast(labels.returnRequired, 'error');
            }
            return;
        }
        if (!state.rentalUrl) return;

        var confirmBtn = document.getElementById('markReturnedConfirmBtn');
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
            body: JSON.stringify({ returned: 'mark', return_lines: returnLines }),
        })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (res) {
                if (confirmBtn) confirmBtn.disabled = false;
                if (triggerBtn) triggerBtn.disabled = false;
                if (res.ok && res.data && res.data.success) {
                    closeMarkReturnedModal();
                    if (typeof state.onSuccess === 'function') {
                        state.onSuccess(res.data);
                    }
                    if (window.VendorMarkReturned && typeof window.VendorMarkReturned.toast === 'function') {
                        window.VendorMarkReturned.toast(res.data.message || labels.updated, 'success');
                    }
                    return;
                }
                var msg = (res.data && res.data.message) ? res.data.message : 'Could not update';
                if (res.data && res.data.errors) {
                    var first = Object.values(res.data.errors)[0];
                    if (first && first[0]) msg = first[0];
                }
                if (window.VendorMarkReturned && typeof window.VendorMarkReturned.toast === 'function') {
                    window.VendorMarkReturned.toast(msg, 'error');
                }
            })
            .catch(function () {
                if (confirmBtn) confirmBtn.disabled = false;
                if (triggerBtn) triggerBtn.disabled = false;
                if (window.VendorMarkReturned && typeof window.VendorMarkReturned.toast === 'function') {
                    window.VendorMarkReturned.toast('Network error', 'error');
                }
            });
    };

    window.VendorMarkReturned = {
        _initialized: true,
        open: window.openMarkReturnedModal,
        close: window.closeMarkReturnedModal,
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
            window.openMarkReturnedModal(lines, url, btn, {
                orderNumber: btn.getAttribute('data-order-number') || '',
                toast: window.VendorMarkReturned.toast,
                onSuccess: typeof window.VendorMarkReturned._defaultOnSuccess === 'function'
                    ? function (data) { window.VendorMarkReturned._defaultOnSuccess(btn, data); }
                    : null,
            });
        },
    };
})();
</script>
