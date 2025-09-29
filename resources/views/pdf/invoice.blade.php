<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        :root {
            --accent-color: {{ $palette['accent_color'] ?? '#0b57d0' }};
            --accent-text: {{ $palette['accent_text_color'] ?? '#ffffff' }};
            --heading-color: {{ $palette['heading_color'] ?? '#111111' }};
            --text-color: {{ $palette['text_color'] ?? '#111111' }};
            --muted-color: {{ $palette['muted_text_color'] ?? '#555555' }};
            --border-color: {{ $palette['border_color'] ?? '#d1d5db' }};
            --table-header-bg: {{ $palette['table_header_background'] ?? '#0b57d0' }};
            --table-header-text: {{ $palette['table_header_text'] ?? '#ffffff' }};
            --table-row-even: {{ $palette['table_row_even'] ?? '#f6f6f6' }};
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: var(--text-color);
            font-size: 12px;
            line-height: 1.5;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 22mm 20mm;
            margin: 0 auto;
            background: #fff;
        }

        h1, h2, h3, h4 {
            font-weight: 600;
            color: var(--heading-color);
        }

        .center-title {
            text-align: center;
            letter-spacing: 2px;
            font-size: 24px;
            margin-bottom: 18px;
        }

        .flex-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 18px;
        }

        .company-block {
            flex: 1;
        }

        .company-block h2 {
            font-size: 16px;
            margin-bottom: 6px;
        }

        .company-block .line { color: var(--muted-color); font-size: 12px; }

        .logo {
            max-width: 140px;
            max-height: 70px;
        }

        .info-card {
            border: 1px solid var(--border-color);
            padding: 14px 16px;
            border-radius: 6px;
            flex: 1;
        }

        .info-card h3 {
            font-size: 13px;
            margin-bottom: 8px;
        }

        .info-card .label { display: inline-block; width: 90px; color: var(--muted-color); }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 3px 0;
            font-size: 12px;
        }

        .meta-table td:first-child {
            font-weight: 600;
            color: var(--heading-color);
            padding-right: 8px;
            white-space: nowrap;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }

        .items-table th {
            background: var(--table-header-bg);
            color: var(--table-header-text);
            font-size: 12px;
            padding: 8px;
            text-align: left;
        }

        .items-table td {
            padding: 8px;
            border-bottom: 1px solid var(--border-color);
        }

        .items-table tbody tr:nth-child(even) {
            background: var(--table-row-even);
        }

        .totals-row {
            display: flex;
            justify-content: flex-end;
            margin-top: 12px;
        }

        .totals-table {
            min-width: 220px;
            font-size: 12px;
        }

        .totals-table td { padding: 3px 0; }
        .totals-table td:first-child { color: var(--muted-color); padding-right: 12px; }
        .totals-table tr.total td { font-weight: 600; color: var(--heading-color); border-top: 1px solid var(--border-color); padding-top: 6px; }

        .section-title {
            font-size: 13px;
            font-weight: 600;
            margin-top: 20px;
            margin-bottom: 6px;
        }

        .payment-block {
            border: 1px solid var(--border-color);
            padding: 12px 14px;
            border-radius: 6px;
            width: 60%;
        }

        .payment-block p { margin: 4px 0; }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            gap: 40px;
        }

        .signature {
            flex: 1;
            text-align: center;
        }

        .signature .line {
            margin-top: 40px;
            border-top: 1px solid var(--border-color);
            padding-top: 6px;
        }

        .footer {
            margin-top: 60px;
            font-size: 10px;
            color: var(--muted-color);
            text-align: center;
        }

        .watermark {
            position: absolute;
            top: 40mm;
            left: 50%;
            transform: translateX(-50%);
            font-size: 60px;
            color: rgba(226, 232, 240, 0.3);
            font-weight: 700;
            letter-spacing: 8px;
        }
    </style>
</head>
<body>
@php
    $currency = $currency ?? ($invoice->company->invoice_settings['defaults']['currency'] ?? 'RM');
    $formatMoney = fn($value) => trim(($currency ? $currency . ' ' : '') . number_format((float) $value, 2));
    $subtotal = $invoice->subtotal ?? $invoice->items->sum(fn($item) => $item->total_price ?? ($item->quantity * $item->unit_price));
    $discount = $invoice->discount_amount ?? 0;
    $tax = $invoice->tax_amount ?? 0;
    $total = $invoice->total ?? ($subtotal - $discount + $tax);
    $paid = $invoice->amount_paid ?? 0;
    $balance = max(0, $total - $paid);
    $logoPath = null;
    if (!empty($invoice->company?->logo)) {
        $logoPath = public_path('storage/' . ltrim($invoice->company->logo, '/'));
    }
    if ($logoPath && file_exists($logoPath)) {
        $logoPath = 'file://' . str_replace('\\\\', '/', $logoPath);
    } else {
        $logoPath = null;
    }
@endphp

<div class="page">
    @if($invoice->status === 'DRAFT')
        <div class="watermark">DRAFT</div>
    @elseif($invoice->status === 'OVERDUE')
        <div class="watermark">OVERDUE</div>
    @endif

    <h1 class="center-title">INVOICE</h1>

    <div class="flex-row" style="margin-bottom: 24px;">
        <div class="company-block">
            <h2>{{ $invoice->company->name ?? 'Company Name' }}</h2>
            @foreach([
                $invoice->company->address,
                trim(collect([$invoice->company->postal_code, $invoice->company->city])->filter()->implode(' ')),
                $invoice->company->state,
                'Tel: ' . ($invoice->company->phone ?? '—'),
                'Email: ' . ($invoice->company->email ?? '—'),
            ] as $line)
                @if(!empty(trim($line, ' -')))
                    <div class="line">{{ $line }}</div>
                @endif
            @endforeach
        </div>
        @if(($sections['show_company_logo'] ?? true) && $logoPath)
            <div>
                <img src="{{ $logoPath }}" alt="Company Logo" class="logo">
            </div>
        @endif
    </div>

    <div class="flex-row">
        <div class="info-card">
            <h3>Bill To</h3>
            <div>{{ $invoice->customer_name ?? 'Customer Name' }}</div>
            @if($invoice->customer_company)
                <div>{{ $invoice->customer_company }}</div>
            @endif
            <div style="margin: 6px 0;">
                @php
                    $addressLines = array_filter([
                        $invoice->customer_address,
                        trim(collect([$invoice->customer_postal_code, $invoice->customer_city])->filter()->implode(' ')),
                        $invoice->customer_state,
                    ]);
                @endphp
                @foreach($addressLines as $line)
                    <div>{{ $line }}</div>
                @endforeach
            </div>
            @if($invoice->customer_email)
                <div>Email: {{ $invoice->customer_email }}</div>
            @endif
            @if($invoice->customer_phone)
                <div>Phone: {{ $invoice->customer_phone }}</div>
            @endif
        </div>
        <div class="info-card">
            <table class="meta-table">
                <tr>
                    <td>Invoice No :</td>
                    <td>{{ $invoice->number }}</td>
                </tr>
                <tr>
                    <td>Invoice Date :</td>
                    <td>{{ optional($invoice->issued_date)->format('d M, Y') ?? now()->format('d M, Y') }}</td>
                </tr>
                <tr>
                    <td>Due Date :</td>
                    <td>{{ optional($invoice->due_date)->format('d M, Y') ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Payment Terms :</td>
                    <td>{{ $invoice->payment_terms ? $invoice->payment_terms . ' days' : '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%;">Sl.</th>
                <th>Description</th>
                <th style="width: 12%; text-align: center;">Qty</th>
                <th style="width: 18%; text-align: right;">Rate</th>
                <th style="width: 18%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>
                        {{ $item->description }}
                        @if($item->specifications)
                            <div style="color: var(--muted-color); font-size: 11px;">{{ $item->specifications }}</div>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                    <td style="text-align: right;">{{ $formatMoney($item->unit_price) }}</td>
                    <td style="text-align: right;">{{ $formatMoney($item->total_price ?? ($item->quantity * $item->unit_price)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-row">
        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td>{{ $formatMoney($subtotal) }}</td>
            </tr>
            @if($discount > 0)
                <tr>
                    <td>Discount</td>
                    <td>-{{ $formatMoney($discount) }}</td>
                </tr>
            @endif
            @if($tax > 0)
                <tr>
                    <td>Tax</td>
                    <td>{{ $formatMoney($tax) }}</td>
                </tr>
            @endif
            <tr class="total">
                <td>Total</td>
                <td>{{ $formatMoney($total) }}</td>
            </tr>
            <tr>
                <td>Paid</td>
                <td>{{ $formatMoney($paid) }}</td>
            </tr>
            <tr>
                <td>Balance Due</td>
                <td>{{ $formatMoney($balance) }}</td>
            </tr>
        </table>
    </div>

    @if($sections['show_payment_instructions'] && !empty($invoice->payment_instructions))
        <div class="section-title">Payment Instructions</div>
        <div class="payment-block">
            {!! nl2br(e($invoice->payment_instructions)) !!}
        </div>
    @endif

    @if($sections['show_terms_conditions'] && $invoice->terms_conditions)
        <div class="section-title">Terms &amp; Conditions</div>
        <div style="max-width: 70%;">{!! nl2br(e($invoice->terms_conditions)) !!}</div>
    @endif

    @if($sections['show_additional_notes'] && $invoice->notes)
        <div class="section-title">Notes</div>
        <div style="max-width: 70%;">{!! nl2br(e($invoice->notes)) !!}</div>
    @endif

    @if($sections['show_signatures'])
        <div class="signatures">
            <div class="signature">
                <div class="line">Authorized Representative</div>
                <div>{{ $invoice->company->name }}</div>
            </div>
            <div class="signature">
                <div class="line">Customer Acceptance</div>
                <div>{{ $invoice->customer_name }}</div>
            </div>
        </div>
    @endif

    <div class="footer">
        {{ $invoice->company->name ?? 'Company' }} — Invoice {{ $invoice->number }} — Generated on {{ now()->format('d M Y, H:i') }}
    </div>
</div>
</body>
</html>




