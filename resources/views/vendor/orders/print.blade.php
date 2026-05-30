@php
    use App\Support\InvoiceDocument;

    $forPdf = (bool) ($forPdf ?? false);
    $isInvoice = (bool) ($isInvoice ?? false);
    $pdfFontFamily = $pdfFontFamily ?? null;

    $paymentRows = is_array($order->payment_detail) ? $order->payment_detail : [];
    $methodLabels = InvoiceDocument::paymentMethodLabels();
    $bookingDurationHuman = InvoiceDocument::rentalDurationHuman($order);
    $vendorAddress = InvoiceDocument::vendorAddress($order->vendor);
    $statusLabel = InvoiceDocument::orderStatusLabel($order->status);

    $docTitle = $isInvoice ? __('vendor.invoice_document_title') : __('vendor.quote_document_title');
    $preparedForLabel = $isInvoice ? __('vendor.invoice_prepared_for') : __('vendor.quotation_for');
    $fromLabel = $isInvoice ? __('vendor.invoice_from_vendor') : __('vendor.quotation_from');
    $footerNote = $isInvoice ? __('vendor.invoice_footer_note') : __('vendor.quote_footer_note');
    $refLabel = $isInvoice ? __('vendor.invoice_ref') : __('vendor.quotation_ref');
    $issuedLabel = $isInvoice ? __('vendor.invoice_issued_on') : __('vendor.created');
    $sectionCustomerTitle = __('vendor.invoice_section_customer_booking');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if($forPdf) class="pdf-invoice"@endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $docTitle }} — {{ $order->order_number }}</title>
    @unless($forPdf)
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@@400;500;600;700&display=swap" rel="stylesheet">
    @endunless
    <style>
        @if($forPdf)
        * { box-sizing: border-box; }
        html.pdf-invoice body {
            margin: 0;
            padding: 0;
            font-family: {!! $pdfFontFamily ?? "'dejavu sans', sans-serif" !!};
            font-size: 11px;
            line-height: 1.45;
            color: #0f172a;
            background: #fff;
        }
        html.pdf-invoice table { border-collapse: collapse; }
        html.pdf-invoice .inv-shell { width: 100%; }
        html.pdf-invoice .inv-header {
            width: 100%;
            margin-bottom: 14px;
            padding-bottom: 12px;
            border-bottom: 2px solid #0f766e;
        }
        html.pdf-invoice .inv-header td { vertical-align: top; padding: 0; }
        html.pdf-invoice .inv-eyebrow {
            font-size: 10px;
            font-weight: 700;
            color: #0f766e;
            margin: 0 0 4px;
        }
        html.pdf-invoice .inv-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px;
            line-height: 1.2;
        }
        html.pdf-invoice .inv-subtitle { margin: 0; font-size: 11px; color: #64748b; }
        html.pdf-invoice .inv-vendor { margin: 6px 0 0; font-size: 10px; color: #64748b; }
        html.pdf-invoice .inv-badge {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }
        html.pdf-invoice .inv-badge-num {
            font-size: 14px;
            font-weight: 700;
            color: #0f766e;
            margin: 2px 0;
        }
        html.pdf-invoice .inv-section {
            margin-top: 16px;
            page-break-inside: avoid;
        }
        html.pdf-invoice .inv-section-title {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            margin: 0 0 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
        }
        html.pdf-invoice .inv-panel {
            width: 100%;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            table-layout: fixed;
        }
        html.pdf-invoice .inv-panel td {
            padding: 0;
            vertical-align: top;
            border-bottom: 1px solid #e2e8f0;
        }
        html.pdf-invoice .inv-panel tr:last-child td { border-bottom: none; }
        html.pdf-invoice .inv-field {
            padding: 8px 12px;
        }
        html.pdf-invoice .inv-field-label {
            font-family: inherit;
            font-size: 9px;
            font-weight: 700;
            color: #334155;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        html.pdf-invoice .inv-field-value {
            font-family: inherit;
            font-size: 10px;
            color: #0f172a;
            line-height: 1.45;
            margin: 4px 0 0;
            padding: 0;
        }
        html.pdf-invoice .inv-items {
            width: 100%;
            border: 1px solid #e2e8f0;
        }
        html.pdf-invoice .inv-items th {
            background: #115e59;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            padding: 8px 10px;
            text-align: left;
        }
        html.pdf-invoice .inv-items th.num,
        html.pdf-invoice .inv-items td.num { text-align: right; }
        html.pdf-invoice .inv-items td {
            padding: 8px 10px;
            font-size: 10px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        html.pdf-invoice .inv-items tr:nth-child(even) td { background: #fafbfc; }
        html.pdf-invoice .inv-totals {
            width: 100%;
            margin-top: 4px;
        }
        html.pdf-invoice .inv-totals-box {
            width: 300px;
            margin-left: auto;
            border: 1px solid #e2e8f0;
        }
        html.pdf-invoice .inv-totals-box td {
            padding: 7px 10px;
            font-size: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        html.pdf-invoice .inv-totals-box tr:last-child td { border-bottom: none; }
        html.pdf-invoice .inv-totals-box td.num { text-align: right; font-weight: 600; }
        html.pdf-invoice .inv-totals-grand td {
            background: #d1fae5;
            font-weight: 700;
            font-size: 11px;
            color: #0f766e;
        }
        html.pdf-invoice .inv-paycard {
            width: 100%;
            border: 1px solid #fde68a;
            background: #fffbeb;
        }
        html.pdf-invoice .inv-paycard td {
            padding: 8px 10px;
            font-size: 11px;
        }
        html.pdf-invoice .inv-paycard td.num { text-align: right; font-weight: 700; }
        html.pdf-invoice .inv-footer {
            margin-top: 18px;
            padding-top: 12px;
            border-top: 1px dashed #e2e8f0;
            font-size: 9px;
            color: #64748b;
            line-height: 1.5;
        }
        html.pdf-invoice .muted { color: #64748b; font-style: italic; }
        @else
        :root {
            --q-ink: #0f172a;
            --q-muted: #64748b;
            --q-soft: #f1f5f9;
            --q-line: #e2e8f0;
            --q-accent: #0d9488;
            --q-accent-dark: #0f766e;
            --q-warn-bg: #fffbeb;
            --q-warn-border: #fde68a;
            --q-radius: 12px;
            --q-radius-sm: 8px;
            --q-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif;
            font-size: 14px;
            line-height: 1.55;
            color: var(--q-ink);
            background: #eef2f6;
        }
        .no-print {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.92);
            border-bottom: 1px solid var(--q-line);
        }
        .no-print button {
            cursor: pointer;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 20px;
            border: none;
        }
        .no-print .btn-print {
            background: linear-gradient(165deg, var(--q-accent) 0%, var(--q-accent-dark) 100%);
            color: #fff;
        }
        .no-print .btn-close {
            background: #fff;
            color: var(--q-ink);
            border: 1px solid var(--q-line);
        }
        .quote-shell { padding: 24px 16px 40px; max-width: 840px; margin: 0 auto; }
        .quote-doc {
            background: #fff;
            border-radius: var(--q-radius);
            box-shadow: var(--q-shadow);
            overflow: hidden;
            border: 1px solid var(--q-line);
        }
        .quote-doc__hero {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            padding: 28px 32px 24px;
            background: linear-gradient(135deg, #f0fdfa 0%, #fff 45%, #f8fafc 100%);
            border-bottom: 3px solid var(--q-accent);
        }
        .quote-doc__brand { min-width: 0; flex: 1; }
        .quote-doc__eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--q-accent-dark);
            margin: 0 0 6px;
        }
        .quote-doc__title {
            font-size: 1.65rem;
            font-weight: 700;
            margin: 0 0 6px;
            line-height: 1.2;
        }
        .quote-doc__subtitle { margin: 0; font-size: 14px; color: var(--q-muted); }
        .quote-doc__vendor { margin: 10px 0 0; font-size: 13px; color: var(--q-muted); }
        .quote-doc__badge { text-align: right; flex-shrink: 0; }
        .quote-doc__badge-inner {
            display: inline-block;
            padding: 12px 18px;
            border-radius: var(--q-radius-sm);
            background: #fff;
            border: 1px solid var(--q-line);
        }
        .quote-doc__badge-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--q-muted);
            margin: 0 0 4px;
        }
        .quote-doc__badge-num { font-size: 1.25rem; font-weight: 700; color: var(--q-accent-dark); margin: 0; }
        .quote-doc__badge-date { font-size: 12px; color: var(--q-muted); margin: 6px 0 0; }
        .quote-doc__body { padding: 28px 32px 32px; }
        .quote-section { margin-top: 28px; }
        .quote-section:first-of-type { margin-top: 0; }
        .quote-section__title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--q-muted);
            margin: 0 0 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--q-soft);
        }
        .quote-panel {
            background: var(--q-soft);
            border: 1px solid var(--q-line);
            border-radius: var(--q-radius-sm);
            padding: 18px 20px;
        }
        .quote-panel__row { margin-top: 10px; font-size: 13px; }
        .quote-panel__row:first-child { margin-top: 0; }
        .quote-panel__label {
            font-weight: 600;
            color: var(--q-muted);
            font-size: 11px;
            text-transform: uppercase;
            display: block;
            margin-bottom: 4px;
        }
        .quote-table-wrap {
            border-radius: var(--q-radius-sm);
            overflow: hidden;
            border: 1px solid var(--q-line);
        }
        table.quote-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .quote-table thead th {
            text-align: left;
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            background: var(--q-accent-dark);
        }
        .quote-table thead th.num { text-align: right; }
        .quote-table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--q-line);
            vertical-align: top;
        }
        .quote-table tbody tr:nth-child(even) td { background: #fafbfc; }
        .quote-table td.num { text-align: right; font-weight: 600; }
        .quote-totals { display: flex; justify-content: flex-end; margin-top: 20px; }
        .quote-totals__box {
            min-width: 300px;
            border: 1px solid var(--q-line);
            border-radius: var(--q-radius-sm);
            overflow: hidden;
            background: #fff;
        }
        .quote-totals__row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 10px 18px;
            font-size: 13px;
            border-bottom: 1px solid var(--q-line);
        }
        .quote-totals__row:last-child { border-bottom: none; }
        .quote-totals__row--grand {
            background: #d1fae5;
            font-size: 15px;
            font-weight: 700;
            padding: 14px 18px;
            color: var(--q-accent-dark);
        }
        .quote-paycard {
            border-radius: var(--q-radius-sm);
            border: 1px solid var(--q-warn-border);
            background: var(--q-warn-bg);
            padding: 16px 18px;
        }
        .quote-paycard__row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 6px 0;
        }
        .quote-doc__footer {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px dashed var(--q-line);
            font-size: 12px;
            color: var(--q-muted);
        }
        .muted { color: var(--q-muted); font-style: italic; }
        @@media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .quote-shell { padding: 0; max-width: none; }
            .quote-doc { box-shadow: none; border: none; border-radius: 0; }
            @@page { margin: 14mm; size: A4 portrait; }
        }
        @endif
    </style>
</head>
<body>
    @unless($forPdf)
    <div class="no-print">
        <button type="button" class="btn-print" onclick="window.print()">{{ __('vendor.print_quote') }}</button>
        <button type="button" class="btn-close" onclick="window.close()">{{ __('vendor.cancel') }}</button>
    </div>
    @endunless

    @if($forPdf)
    <div class="inv-shell">
        <table class="inv-header" cellpadding="0" cellspacing="0">
            <tr>
                <td width="60%">
                    <p class="inv-eyebrow">{{ $docTitle }}</p>
                    <h1 class="inv-title">{{ $order->order_number }}</h1>
                    <p class="inv-subtitle">{{ $preparedForLabel }}: {{ $order->customer?->name ?? '—' }}</p>
                    @if($order->vendor)
                        <p class="inv-vendor">{{ $fromLabel }}: <strong>{{ $order->vendor->name }}</strong></p>
                        @if($vendorAddress)
                            <p class="inv-vendor">{{ $vendorAddress }}</p>
                        @endif
                        @if(filled($order->vendor->gst_number))
                            <p class="inv-vendor">{{ __('vendor.gst_number') }}: {{ $order->vendor->gst_number }}</p>
                        @endif
                    @endif
                </td>
                <td width="40%" class="inv-badge">
                    <div>{{ $refLabel }}</div>
                    <div class="inv-badge-num">{{ $order->order_number }}</div>
                    <div>{{ $issuedLabel }}: {{ InvoiceDocument::formatDate($order->created_at, false) }}</div>
                    <div>{{ __('vendor.order_status') }}: {{ $statusLabel }}</div>
                </td>
            </tr>
        </table>

        <div class="inv-section">
            <h2 class="inv-section-title">{{ $sectionCustomerTitle }}</h2>
            <table class="inv-panel" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td>
                        <div class="inv-field">
                            <div class="inv-field-label">{{ __('vendor.customer') }}</div>
                            <div class="inv-field-value">
                                {{ $order->customer?->name ?? '—' }}
                                @if($order->customer?->mobile)
                                    · {{ $order->customer->mobile }}
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @if(filled($order->event_name))
                <tr>
                    <td>
                        <div class="inv-field">
                            <div class="inv-field-label">{{ __('vendor.event_name') }}</div>
                            <div class="inv-field-value">{{ $order->event_name }}</div>
                        </div>
                    </td>
                </tr>
                @endif
                <tr>
                    <td>
                        <div class="inv-field">
                            <div class="inv-field-label">{{ __('vendor.booking_period') }}</div>
                            <div class="inv-field-value">
                                @if($order->start_at && $order->end_at)
                                    {{ InvoiceDocument::formatDate($order->start_at) }}
                                    —
                                    {{ InvoiceDocument::formatDate($order->end_at) }}
                                @else
                                    <span class="muted">{{ __('vendor.not_specified') }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @if($bookingDurationHuman)
                <tr>
                    <td>
                        <div class="inv-field">
                            <div class="inv-field-label">{{ __('vendor.quote_duration') }}</div>
                            <div class="inv-field-value">{{ $bookingDurationHuman }}</div>
                        </div>
                    </td>
                </tr>
                @endif
                <tr>
                    <td>
                        <div class="inv-field">
                            <div class="inv-field-label">{{ __('vendor.fulfillment_method') }}</div>
                            <div class="inv-field-value">
                                {{ $order->fulfillment_type === 'delivery' ? __('vendor.delivery') : __('vendor.pickup') }}
                                @if(filled($order->delivery_address))
                                    <br>{{ $order->delivery_address }}
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @if(($order->fulfillment_type ?? 'pickup') === 'pickup' && $order->pickup_at)
                <tr>
                    <td>
                        <div class="inv-field">
                            <div class="inv-field-label">{{ __('vendor.pickup_datetime') }}</div>
                            <div class="inv-field-value">{{ InvoiceDocument::formatDate($order->pickup_at) }}</div>
                        </div>
                    </td>
                </tr>
                @endif
            </table>
        </div>

        <div class="inv-section">
            <h2 class="inv-section-title">{{ __('vendor.quotation_line_items') }}</h2>
            <table class="inv-items" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th>{{ __('vendor.item_name') }}</th>
                        <th class="num" width="70">{{ __('vendor.quantity') }}</th>
                        <th class="num" width="100">{{ __('vendor.total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $line)
                    <tr>
                        <td>{{ $line->item?->name ?? $line->item_name ?? '—' }}</td>
                        <td class="num">{{ $line->quantity }}</td>
                        <td class="num">{{ InvoiceDocument::money($line->lineSubtotal()) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="muted">{{ __('vendor.no_items_yet') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="inv-section">
            <h2 class="inv-section-title">{{ __('vendor.quotation_amounts') }}</h2>
            <table class="inv-totals" cellpadding="0" cellspacing="0">
                <tr>
                    <td align="right">
                        <table class="inv-totals-box" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>{{ __('vendor.subtotal') }}</td>
                                <td class="num" width="110">{{ InvoiceDocument::money($order->sub_total) }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('vendor.discount') }}</td>
                                <td class="num">−{{ InvoiceDocument::money($order->discount_total) }}</td>
                            </tr>
                            @if(filled($order->coupon_code) && (float) ($order->coupon_discount ?? 0) > 0)
                            <tr>
                                <td>{{ __('vendor.coupon_code') }} ({{ $order->coupon_code }})</td>
                                <td class="num">−{{ InvoiceDocument::money($order->coupon_discount) }}</td>
                            </tr>
                            @endif
                            @if(($order->fulfillment_type ?? 'pickup') === 'delivery' && (float) ($order->delivery_charge ?? 0) > 0)
                            <tr>
                                <td>{{ __('vendor.delivery_charge') }}</td>
                                <td class="num">{{ InvoiceDocument::money($order->delivery_charge) }}</td>
                            </tr>
                            @endif
                            @if((float) ($order->extra_charges_total ?? 0) > 0)
                            <tr>
                                <td>{{ __('vendor.extra_charges_label') }}</td>
                                <td class="num">{{ InvoiceDocument::money($order->extra_charges_total) }}</td>
                            </tr>
                            @endif
                            @if((float) ($order->tax_total ?? 0) > 0)
                            <tr>
                                <td>{{ __('vendor.tax') }}</td>
                                <td class="num">{{ InvoiceDocument::money($order->tax_total) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td>{{ __('vendor.quote_order_total') }}</td>
                                <td class="num"><strong>{{ InvoiceDocument::money($order->grand_total) }}</strong></td>
                            </tr>
                            <tr>
                                <td>{{ __('vendor.quote_security_deposit') }}</td>
                                <td class="num">{{ InvoiceDocument::money($order->security_deposit) }}</td>
                            </tr>
                            <tr class="inv-totals-grand">
                                <td>{{ __('vendor.quote_total_with_deposit') }}</td>
                                <td class="num">{{ InvoiceDocument::money((float) ($order->grand_total ?? 0) + (float) ($order->security_deposit ?? 0)) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="inv-section">
            <h2 class="inv-section-title">{{ __('vendor.print_paid_balance') }}</h2>
            <table class="inv-paycard" cellpadding="0" cellspacing="0">
                <tr>
                    <td>{{ __('vendor.paid_amount') }}</td>
                    <td class="num">{{ InvoiceDocument::money($order->paid_amount) }}</td>
                </tr>
                <tr>
                    <td>{{ __('vendor.balance_due') }}</td>
                    <td class="num">{{ InvoiceDocument::money(max(0, ((float) ($order->grand_total ?? 0) + (float) ($order->security_deposit ?? 0)) - (float) ($order->paid_amount ?? 0))) }}</td>
                </tr>
            </table>
        </div>

        @if(!empty($paymentRows))
        <div class="inv-section">
            <h2 class="inv-section-title">{{ __('vendor.print_payments') }}</h2>
            <table class="inv-items" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th class="num" width="100">{{ __('vendor.print_col_amount') }}</th>
                        <th>{{ __('vendor.print_col_for') }}</th>
                        <th>{{ __('vendor.print_col_method') }}</th>
                        <th width="90">{{ __('vendor.print_col_date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach((array) $paymentRows as $p)
                    @php
                        $mLabel = InvoiceDocument::paymentMethodLabel($p['method'] ?? '');
                        $forLabel = ($p['payment_for'] ?? '') === 'security_deposit'
                            ? __('vendor.payment_for_deposit_short')
                            : __('vendor.payment_for_order_short');
                        if (($p['entry_kind'] ?? 'payment') === 'refund') {
                            $forLabel = $forLabel.' · '.__('vendor.label_refund');
                        }
                        $paidOn = ! empty($p['paid_on'])
                            ? InvoiceDocument::formatDate(\Carbon\Carbon::parse($p['paid_on']), false)
                            : '—';
                        $pIsRefund = (($p['entry_kind'] ?? 'payment') === 'refund');
                    @endphp
                    <tr>
                        <td class="num">@if($pIsRefund)−@endif{{ InvoiceDocument::money($p['amount'] ?? 0) }}</td>
                        <td>{{ $forLabel }}</td>
                        <td>{{ $mLabel }}</td>
                        <td>{{ $paidOn }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <footer class="inv-footer">{{ $footerNote }}</footer>
    </div>
    @else
    <div class="quote-shell">
        <article class="quote-doc">
            <header class="quote-doc__hero">
                <div class="quote-doc__brand">
                    <p class="quote-doc__eyebrow">{{ $docTitle }}</p>
                    <h1 class="quote-doc__title">{{ $order->order_number }}</h1>
                    <p class="quote-doc__subtitle">{{ $preparedForLabel }} {{ $order->customer?->name ?? '—' }}</p>
                    @if($order->vendor)
                        <p class="quote-doc__vendor">{{ $fromLabel }} <strong>{{ $order->vendor->name }}</strong></p>
                        @if($vendorAddress)
                            <p class="quote-doc__vendor">{{ $vendorAddress }}</p>
                        @endif
                    @endif
                </div>
                <div class="quote-doc__badge">
                    <div class="quote-doc__badge-inner">
                        <p class="quote-doc__badge-label">{{ $refLabel }}</p>
                        <p class="quote-doc__badge-num">{{ $order->order_number }}</p>
                        <p class="quote-doc__badge-date">{{ $issuedLabel }} {{ InvoiceDocument::formatDate($order->created_at, false) }}</p>
                    </div>
                </div>
            </header>

            <div class="quote-doc__body">
                <section class="quote-section">
                    <h2 class="quote-section__title">{{ $sectionCustomerTitle }}</h2>
                    <div class="quote-panel">
                        <div class="quote-panel__row">
                            <span class="quote-panel__label">{{ __('vendor.customer') }}</span>
                            {{ $order->customer?->name ?? '—' }}@if($order->customer?->mobile) · {{ $order->customer->mobile }}@endif
                        </div>
                        @if(filled($order->event_name))
                        <div class="quote-panel__row">
                            <span class="quote-panel__label">{{ __('vendor.event_name') }}</span>
                            {{ $order->event_name }}
                        </div>
                        @endif
                        <div class="quote-panel__row">
                            <span class="quote-panel__label">{{ __('vendor.booking_period') }}</span>
                            @if($order->start_at && $order->end_at)
                                {{ InvoiceDocument::formatDate($order->start_at) }} — {{ InvoiceDocument::formatDate($order->end_at) }}
                            @else
                                <span class="muted">{{ __('vendor.not_specified') }}</span>
                            @endif
                        </div>
                        @if($bookingDurationHuman)
                        <div class="quote-panel__row">
                            <span class="quote-panel__label">{{ __('vendor.quote_duration') }}</span>
                            {{ $bookingDurationHuman }}
                        </div>
                        @endif
                        <div class="quote-panel__row">
                            <span class="quote-panel__label">{{ __('vendor.fulfillment_method') }}</span>
                            {{ $order->fulfillment_type === 'delivery' ? __('vendor.delivery') : __('vendor.pickup') }}
                            @if(filled($order->delivery_address))
                                <br><span style="font-weight:500;margin-top:6px;display:inline-block;">{{ $order->delivery_address }}</span>
                            @endif
                        </div>
                    </div>
                </section>

                <section class="quote-section">
                    <h2 class="quote-section__title">{{ __('vendor.quotation_line_items') }}</h2>
                    <div class="quote-table-wrap">
                        <table class="quote-table">
                            <thead>
                                <tr>
                                    <th>{{ __('vendor.item_name') }}</th>
                                    <th class="num">{{ __('vendor.quantity') }}</th>
                                    <th class="num">{{ __('vendor.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->items as $line)
                                <tr>
                                    <td>{{ $line->item?->name ?? $line->item_name ?? '—' }}</td>
                                    <td class="num">{{ $line->quantity }}</td>
                                    <td class="num">{{ InvoiceDocument::money($line->lineSubtotal()) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="muted">{{ __('vendor.no_items_yet') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="quote-section">
                    <h2 class="quote-section__title">{{ __('vendor.quotation_amounts') }}</h2>
                    <div class="quote-totals">
                        <div class="quote-totals__box">
                            <div class="quote-totals__row"><span>{{ __('vendor.subtotal') }}</span><span>{{ InvoiceDocument::money($order->sub_total) }}</span></div>
                            <div class="quote-totals__row"><span>{{ __('vendor.discount') }}</span><span>−{{ InvoiceDocument::money($order->discount_total) }}</span></div>
                            <div class="quote-totals__row"><span>{{ __('vendor.quote_order_total') }}</span><span><strong>{{ InvoiceDocument::money($order->grand_total) }}</strong></span></div>
                            <div class="quote-totals__row"><span>{{ __('vendor.quote_security_deposit') }}</span><span>{{ InvoiceDocument::money($order->security_deposit) }}</span></div>
                            <div class="quote-totals__row quote-totals__row--grand"><span>{{ __('vendor.quote_total_with_deposit') }}</span><span>{{ InvoiceDocument::money((float) ($order->grand_total ?? 0) + (float) ($order->security_deposit ?? 0)) }}</span></div>
                        </div>
                    </div>
                </section>

                <section class="quote-section">
                    <h2 class="quote-section__title">{{ __('vendor.print_paid_balance') }}</h2>
                    <div class="quote-paycard">
                        <div class="quote-paycard__row"><span>{{ __('vendor.paid_amount') }}</span><strong>{{ InvoiceDocument::money($order->paid_amount) }}</strong></div>
                        <div class="quote-paycard__row"><span>{{ __('vendor.balance_due') }}</span><strong>{{ InvoiceDocument::money(max(0, ((float) ($order->grand_total ?? 0) + (float) ($order->security_deposit ?? 0)) - (float) ($order->paid_amount ?? 0))) }}</strong></div>
                    </div>
                </section>

                <footer class="quote-doc__footer">{{ $footerNote }}</footer>
            </div>
        </article>
    </div>
    @endif

    @if(!empty($autoprint))
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 400);
        });
    </script>
    @endif
</body>
</html>
