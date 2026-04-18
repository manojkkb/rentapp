<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('vendor.print_quote') }} — {{ $cart->cart_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --q-ink: #0f172a;
            --q-muted: #64748b;
            --q-soft: #f1f5f9;
            --q-line: #e2e8f0;
            --q-accent: #0d9488;
            --q-accent-dark: #0f766e;
            --q-accent-soft: #ccfbf1;
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
            -webkit-font-smoothing: antialiased;
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
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--q-line);
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8) inset;
        }
        .no-print button {
            cursor: pointer;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 20px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.12s ease;
        }
        .no-print button:active { transform: scale(0.98); }
        .no-print .btn-print {
            background: linear-gradient(165deg, var(--q-accent) 0%, var(--q-accent-dark) 100%);
            color: #fff;
            box-shadow: 0 2px 12px rgba(13, 148, 136, 0.35);
        }
        .no-print .btn-print:hover { box-shadow: 0 4px 16px rgba(13, 148, 136, 0.45); }
        .no-print .btn-close {
            background: #fff;
            color: var(--q-ink);
            border: 1px solid var(--q-line);
        }
        .no-print .btn-close:hover { background: var(--q-soft); }
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
            letter-spacing: -0.02em;
            margin: 0 0 6px;
            color: var(--q-ink);
            line-height: 1.2;
        }
        .quote-doc__subtitle { margin: 0; font-size: 14px; color: var(--q-muted); font-weight: 500; }
        .quote-doc__vendor { margin: 10px 0 0; font-size: 13px; color: var(--q-muted); }
        .quote-doc__badge {
            text-align: right;
            flex-shrink: 0;
        }
        .quote-doc__badge-inner {
            display: inline-block;
            padding: 12px 18px;
            border-radius: var(--q-radius-sm);
            background: #fff;
            border: 1px solid var(--q-line);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }
        .quote-doc__badge-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--q-muted);
            margin: 0 0 4px;
        }
        .quote-doc__badge-num { font-size: 1.25rem; font-weight: 700; color: var(--q-accent-dark); margin: 0; font-variant-numeric: tabular-nums; }
        .quote-doc__badge-date { font-size: 12px; color: var(--q-muted); margin: 6px 0 0; }
        .quote-doc__body { padding: 28px 32px 32px; }
        .quote-section { margin-top: 28px; }
        .quote-section:first-of-type { margin-top: 0; }
        .quote-section__title {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
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
        .quote-panel__row { margin-top: 10px; font-size: 13px; color: var(--q-ink); }
        .quote-panel__row:first-child { margin-top: 0; }
        .quote-panel__label { font-weight: 600; color: var(--q-muted); font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; display: block; margin-bottom: 4px; }
        .quote-table-wrap {
            border-radius: var(--q-radius-sm);
            overflow: hidden;
            border: 1px solid var(--q-line);
            margin-top: 4px;
        }
        table.quote-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .quote-table thead th {
            text-align: left;
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #fff;
            background: linear-gradient(180deg, var(--q-accent-dark) 0%, #115e59 100%);
        }
        .quote-table thead th.num { text-align: right; }
        .quote-table tbody td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--q-line);
            vertical-align: top;
        }
        .quote-table tbody tr:nth-child(even) td { background: #fafbfc; }
        .quote-table tbody tr:last-child td { border-bottom: none; }
        .quote-table td.num { text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; }
        .quote-table .muted { color: var(--q-muted); font-style: italic; }
        .quote-totals { display: flex; justify-content: flex-end; margin-top: 20px; }
        .quote-totals__box {
            min-width: 300px;
            max-width: 100%;
            border-radius: var(--q-radius-sm);
            border: 1px solid var(--q-line);
            overflow: hidden;
            background: #fff;
        }
        .quote-totals__row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 10px 18px;
            font-size: 13px;
            border-bottom: 1px solid var(--q-line);
        }
        .quote-totals__row:last-child { border-bottom: none; }
        .quote-totals__row--grand {
            background: linear-gradient(180deg, #ecfdf5 0%, #d1fae5 100%);
            font-size: 15px;
            font-weight: 700;
            padding: 14px 18px;
            color: var(--q-accent-dark);
        }
        .quote-totals__row--grand span:last-child { font-variant-numeric: tabular-nums; font-size: 1.15rem; }
        .quote-paycard {
            border-radius: var(--q-radius-sm);
            border: 1px solid var(--q-warn-border);
            background: var(--q-warn-bg);
            padding: 16px 18px;
            margin-top: 12px;
        }
        .quote-paycard__row { display: flex; justify-content: space-between; font-size: 14px; padding: 6px 0; font-variant-numeric: tabular-nums; }
        .quote-paycard__row strong { font-weight: 700; }
        .muted { color: var(--q-muted); font-style: italic; }
        .quote-doc__footer {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px dashed var(--q-line);
            font-size: 12px;
            color: var(--q-muted);
            line-height: 1.6;
        }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .quote-shell { padding: 0; max-width: none; }
            .quote-doc { box-shadow: none; border: none; border-radius: 0; }
            .quote-doc__hero { break-inside: avoid; }
            .quote-table-wrap, .quote-panel, .quote-totals__box { break-inside: avoid; }
            @page { margin: 14mm; size: A4 portrait; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn-print" onclick="window.print()">{{ __('vendor.print_quote') }}</button>
        <button type="button" class="btn-close" onclick="window.close()">{{ __('vendor.cancel') }}</button>
    </div>

    @php
        $orderGrand = (float) $cart->grand_total;
        $deposit = (float) ($cart->security_deposit ?? 0);
        $withDeposit = $orderGrand + $deposit;
        $paid = (float) ($cart->paid_amount ?? 0);
        $balance = max(0, $withDeposit - $paid);
        $paymentRows = is_array($cart->payment_detail) ? $cart->payment_detail : [];
        $methodLabels = ['card' => 'Card', 'cash' => 'Cash', 'upi' => 'UPI', 'bank_transfer' => 'Bank transfer', 'wallet' => 'Wallet', 'other' => 'Other'];
    @endphp

    <div class="quote-shell">
        <article class="quote-doc">
            <header class="quote-doc__hero">
                <div class="quote-doc__brand">
                    <p class="quote-doc__eyebrow">{{ __('vendor.quote_document_title') }}</p>
                    <h1 class="quote-doc__title">{{ $cart->cart_name }}</h1>
                    <p class="quote-doc__subtitle">{{ __('vendor.quotation_for') }} {{ $cart->customer?->name ?? '—' }}</p>
                    @if($cart->vendor)
                        <p class="quote-doc__vendor">{{ __('vendor.quotation_from') }} <strong>{{ $cart->vendor->name }}</strong></p>
                    @endif
                </div>
                <div class="quote-doc__badge">
                    <div class="quote-doc__badge-inner">
                        <p class="quote-doc__badge-label">{{ __('vendor.quotation_ref') }}</p>
                        <p class="quote-doc__badge-num">#{{ $cart->id }}</p>
                        <p class="quote-doc__badge-date">{{ __('vendor.created') }} {{ $cart->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </header>

            <div class="quote-doc__body">
                <section class="quote-section">
                    <h2 class="quote-section__title">{{ __('vendor.customer') }} &amp; {{ __('vendor.booking_period') }}</h2>
                    <div class="quote-panel">
                        <div class="quote-panel__row">
                            <span class="quote-panel__label">{{ __('vendor.customer') }}</span>
                            {{ $cart->customer?->name ?? '—' }}@if($cart->customer?->mobile) · {{ $cart->customer->mobile }}@endif
                        </div>
                        <div class="quote-panel__row">
                            <span class="quote-panel__label">{{ __('vendor.booking_period') }}</span>
                            @if($cart->start_time && $cart->end_time)
                                {{ $cart->start_time->format('M d, Y h:i A') }} — {{ $cart->end_time->format('M d, Y h:i A') }}
                            @else
                                <span class="muted">{{ __('vendor.not_specified') }}</span>
                            @endif
                        </div>
                        @include('vendor.carts.partials.quote-booking-duration', ['cart' => $cart])
                        <div class="quote-panel__row">
                            <span class="quote-panel__label">{{ __('vendor.fulfillment_method') }}</span>
                            {{ $cart->fulfillment_type === 'delivery' ? __('vendor.delivery') : __('vendor.pickup') }}
                            @if($cart->fulfillment_type === 'delivery' && $cart->delivery_address)
                                <br><span style="font-weight:500;color:var(--q-ink);margin-top:6px;display:inline-block;">{{ $cart->delivery_address }}</span>
                            @endif
                        </div>
                        @if(($cart->fulfillment_type ?? 'pickup') === 'pickup' && $cart->pickup_at)
                            <div class="quote-panel__row">
                                <span class="quote-panel__label">{{ __('vendor.pickup_datetime') }}</span>
                                {{ $cart->pickup_at->timezone(config('app.timezone'))->format('M d, Y h:i A') }}
                            </div>
                        @endif
                        @if(($cart->fulfillment_type ?? 'pickup') === 'delivery' && (float) ($cart->delivery_charge ?? 0) > 0)
                            <div class="quote-panel__row">
                                <span class="quote-panel__label">{{ __('vendor.delivery_charge') }}</span>
                                ₹{{ number_format((float) $cart->delivery_charge, 2) }}
                            </div>
                        @endif
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
                                @forelse($cart->items as $line)
                                    <tr>
                                        <td>{{ $line->item?->name ?? '—' }}</td>
                                        <td class="num">{{ $line->quantity }}</td>
                                        <td class="num">₹{{ number_format($line->lineSubtotal(), 2) }}</td>
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
                            <div class="quote-totals__row"><span>{{ __('vendor.subtotal') }}</span><span>₹{{ number_format($cart->sub_total, 2) }}</span></div>
                            <div class="quote-totals__row"><span>{{ __('vendor.tax') }}</span><span>₹{{ number_format($cart->tax_total, 2) }}</span></div>
                            <div class="quote-totals__row"><span>{{ __('vendor.discount') }}</span><span>−₹{{ number_format($cart->discount_total, 2) }}</span></div>
                            @if(($cart->fulfillment_type ?? 'pickup') === 'delivery' && (float) ($cart->delivery_charge ?? 0) > 0)
                                <div class="quote-totals__row"><span>{{ __('vendor.delivery_charge') }}</span><span>₹{{ number_format((float) $cart->delivery_charge, 2) }}</span></div>
                            @endif
                            <div class="quote-totals__row"><span>{{ __('vendor.quote_order_total') }}</span><span><strong>₹{{ number_format($orderGrand, 2) }}</strong></span></div>
                            <div class="quote-totals__row"><span>{{ __('vendor.quote_security_deposit') }}</span><span>₹{{ number_format($deposit, 2) }}</span></div>
                            <div class="quote-totals__row quote-totals__row--grand"><span>{{ __('vendor.quote_total_with_deposit') }}</span><span>₹{{ number_format($withDeposit, 2) }}</span></div>
                        </div>
                    </div>
                </section>

                <section class="quote-section">
                    <h2 class="quote-section__title">{{ __('vendor.print_paid_balance') }}</h2>
                    <div class="quote-paycard">
                        <div class="quote-paycard__row"><span>{{ __('vendor.paid_amount') }}</span><strong>₹{{ number_format($paid, 2) }}</strong></div>
                        <div class="quote-paycard__row"><span>{{ __('vendor.balance_due') }}</span><strong>₹{{ number_format($balance, 2) }}</strong></div>
                    </div>
                </section>

                @if(count($paymentRows))
                    <section class="quote-section">
                        <h2 class="quote-section__title">{{ __('vendor.print_payments') }}</h2>
                        <div class="quote-table-wrap">
                            <table class="quote-table">
                                <thead>
                                    <tr>
                                        <th class="num">{{ __('vendor.print_col_amount') }}</th>
                                        <th>{{ __('vendor.print_col_for') }}</th>
                                        <th>{{ __('vendor.print_col_method') }}</th>
                                        <th>{{ __('vendor.print_col_date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentRows as $p)
                                        @php
                                            $m = $p['method'] ?? '';
                                            $mLabel = $methodLabels[$m] ?? ucfirst(str_replace('_', ' ', (string) $m));
                                            $forLabel = ($p['payment_for'] ?? '') === 'security_deposit' ? __('vendor.payment_for_deposit_short') : __('vendor.payment_for_order_short');
                                            $paidOn = ! empty($p['paid_on']) ? \Carbon\Carbon::parse($p['paid_on'])->format('M j, Y') : '—';
                                        @endphp
                                        <tr>
                                            <td class="num">₹{{ number_format((float) ($p['amount'] ?? 0), 2) }}</td>
                                            <td>{{ $forLabel }}</td>
                                            <td>{{ $mLabel }}</td>
                                            <td>{{ $paidOn }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                <footer class="quote-doc__footer">{{ __('vendor.quote_footer_note') }}</footer>
            </div>
        </article>
    </div>

    @if(!empty($autoprint))
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 400);
            });
        </script>
    @endif
</body>
</html>
