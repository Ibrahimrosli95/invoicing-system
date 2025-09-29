<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        :root {
            --accent-color: {{ $palette['accent_color'] ?? '#0b57d0' }};
            --accent-text: {{ $palette['accent_text_color'] ?? '#ffffff' }};
            --heading-color: {{ $palette['heading_color'] ?? '#0b0b0b' }};
            --text-color: {{ $palette['text_color'] ?? '#1f1f1f' }};
            --muted-color: {{ $palette['muted_text_color'] ?? '#5f6368' }};
            --border-color: {{ $palette['border_color'] ?? '#d9d9d9' }};
            --table-header-bg: {{ $palette['table_header_background'] ?? '#0b57d0' }};
            --table-header-text: {{ $palette['table_header_text'] ?? '#ffffff' }};
            --table-row-even: {{ $palette['table_row_even'] ?? '#f3f6fb' }};
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
            padding: 16mm 18mm 14mm;
            margin: 0 auto;
            background: #ffffff;
            position: relative;
        }

        h1, h2, h3, h4 { color: var(--heading-color); font-weight: 600; }

        .title {
            text-align: center;
            font-size: 26px;
            letter-spacing: 3px;
            margin: 6mm 0 10mm;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12mm;
            margin-bottom: 6mm;
        }

        .company-details h2 {
            font-size: 16px;
            margin-bottom: 4px;
        }

        .company-details .line {
            font-size: 11.5px;
            color: var(--muted-color);
        }

        .logo {
            max-width: 150px;
            max-height: 70px;
        }

        .divider {
            border: none;
            border-top: 2px solid var(--accent-color);
            margin: 4mm 0 8mm;
        }

        .info-grid {
            display: flex;
            gap: 8mm;
            margin-bottom: 10mm;
        }

        .info-box {
            flex: 1;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 10px 12px;
            background: #fff;
        }

        .info-box h3 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .info-box .row {
            font-size: 12px;
        }

        .info-box .label {
            display: inline-block;
            min-width: 90px;
            color: var(--muted-color);
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10mm;
            font-size: 12px;
        }

        .items-table thead th {
            background: var(--table-header-bg);
            color: var(--table-header-text);
            padding: 8px 10px;
            text-align: left;
            font-weight: 600;
        }

        .items-table tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .items-table tbody tr:nth-child(even) {
            background: var(--table-row-even);
        }

        .items-table td.qty,
        .items-table td.rate,
        .items-table td.amount {
            text-align: right;
            white-space: nowrap;
        }

        .totals-wrapper {
            display: flex;
            justify-content: flex-end;
        }

        .totals {
            min-width: 240px;
        }

        .totals tr td {
            padding: 2px 0;
            font-size: 12px;
        }

        .totals tr td:first-child {
            color: var(--muted-color);
            padding-right: 10px;
        }

        .totals tr.total td {
            padding-top: 6px;
            border-top: 1px solid var(--border-color);
            font-weight: 600;
            color: var(--heading-color);
        }

        .section-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .payment-block {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 10px 12px;
            max-width: 65%;
            font-size: 12px;
        }

        .terms, .notes {
            font-size: 12px;
            max-width: 65%;
            margin-top: 6px;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            gap: 30mm;
            margin-top: 24mm;
        }

        .signature-block {
            flex: 1;
            text-align: center;
            font-size: 12px;
        }

        .signature-block .line {
            border-top: 1px solid var(--border-color);
            margin-top: 24mm;
            padding-top: 4px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: var(--muted-color);
            margin-top: 18mm;
        }

        .watermark {
            position: absolute;
            top: 45mm;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 60px;
            color: rgba(200, 206, 214, 0.15);
            font-weight: 700;
            letter-spacing: 12px;
            pointer-events: none;
        }
    </style>
</head>
<body>
@php
    $currency = $currency ?? ($invoice->company->invoice_settings['defaults']['currency'] ?? 'RM');
    $money = fn($value) => trim(($currency ? $currency . ' ' : '') . number_format((float) $value, 2));
    $subtotal = $invoice->subtotal ?? $invoice->items->sum(fn($item) => $item->total_price ?? ($item->quantity * $item->unit_price));
    $discount = $invoice->discount_amount ?? 0;
    $tax = $invoice->tax_amount ?? 0;
    $total = $invoice->total ?? ($subtotal - $discount + $tax);
    $paid = $invoice->amount_paid ?? 0;
    $balance = max(0, $total - $paid);
    $logoPath = null;
    if (!empty($invoice->company?->logo)) {
        $path = public_path('storage/' . ltrim($invoice->company->logo, '/'));
        if (file_exists($path)) {
            $logoPath = 'file://' . str_replace('\\\\', '/', $path);
        }
    }
    $billAddress = array_filter([
        $invoice->customer_address,
        trim(collect([$invoice->customer_postal_code, $invoice->customer_city])->filter()->implode(' ')),
        $invoice->customer_state,
    ]);
@endphp

<div class="page">
    @if(in_array($invoice->status, ['DRAFT','OVERDUE']))
        <div class="watermark">{{ $invoice->status }}</div>
    @endif

    <div class="header">
        <div class="company-details">
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
            <img src="{{ $logoPath }}" alt="Company Logo" class="logo">
        @endif
    </div>

    <h1 class="title">INVOICE</h1>
    <hr class="divider">

    <div class="info-grid">
        <div class="info-box">
            <h3>Bill To</h3>
            <div class="row">{{ $invoice->customer_name ?? 'Customer Name' }}</div>
            @if($invoice->customer_company)
                <div class="row">{{ $invoice->customer_company }}</div>
            @endif
            @foreach($billAddress as $line)
                <div class="row">{{ $line }}</div>
            @endforeach
            @if($invoice->customer_email)
                <div class="row">Email: {{ $invoice->customer_email }}</div>
            @endif
            @if($invoice->customer_phone)
                <div class="row">Phone: {{ $invoice->customer_phone }}</div>
            @endif
        </div>
        <div class="info-box">
            <table class="meta-table" style="width:100%;">
                <tr>
                    <td class="label">Invoice No :</td>
                    <td>{{ $invoice->number }}</td>
                </tr>
                <tr>
                    <td class="label">Invoice Date :</td>
                    <td>{{ optional($invoice->issued_date)->format('d M, Y') ?? now()->format('d M, Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Due Date :</td>
                    <td>{{ optional($invoice->due_date)->format('d M, Y') ?? '—' }}</td>
                </tr>
                <tr>
                    <td class="label">Payment Terms :</td>
                    <td>{{ $invoice->payment_terms ? $invoice->payment_terms . ' days' : '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%; text-align:center;">Sl.</th>
                <th>Description</th>
                <th style="width: 12%; text-align:center;">Qty</th>
                <th style="width: 18%; text-align:right;">Rate</th>
                <th style="width: 18%; text-align:right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td style="text-align:center;">{{ $index + 1 }}</td>
                    <td>
                        {{ $item->description }}
                        @if($item->specifications)
                            <div style="color: var(--muted-color); font-size: 11px;">{{ $item->specifications }}</div>
                        @endif
                    </td>
                    <td class="qty">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                    <td class="rate">{{ $money($item->unit_price) }}</td>
                    <td class="amount">{{ $money($item->total_price ?? ($item->quantity * $item->unit_price)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-wrapper">
        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td>{{ $money($subtotal) }}</td>
            </tr>
            @if($discount > 0)
                <tr>
                    <td>Discount</td>
                    <td>-{{ $money($discount) }}</td>
                </tr>
            @endif
            @if($tax > 0)
                <tr>
                    <td>Tax</td>
                    <td>{{ $money($tax) }}</td>
                </tr>
            @endif
            <tr class="total">
                <td>Total</td>
                <td>{{ $money($total) }}</td>
            </tr>
            <tr>
                <td>Paid</td>
                <td>{{ $money($paid) }}</td>
            </tr>
            <tr>
                <td>Balance Due</td>
                <td>{{ $money($balance) }}</td>
            </tr>
        </table>
    </div>

    @if($sections['show_payment_instructions'] && $invoice->payment_instructions)
        <div class="section-title" style="margin-top: 8mm;">Payment Instructions</div>
        <div class="payment-block">{!! nl2br(e($invoice->payment_instructions)) !!}</div>
    @endif

    @if($sections['show_terms_conditions'] && $invoice->terms_conditions)
        <div class="section-title" style="margin-top: 8mm;">Terms &amp; Conditions</div>
        <div class="terms">{!! nl2br(e($invoice->terms_conditions)) !!}</div>
    @endif

    @if($sections['show_additional_notes'] && $invoice->notes)
        <div class="section-title" style="margin-top: 6mm;">Notes</div>
        <div class="notes">{!! nl2br(e($invoice->notes)) !!}</div>
    @endif

    @if($sections['show_signatures'])
        <div class="signatures">
            <div class="signature-block">
                <div class="line">Authorized Representative</div>
                <div>{{ $invoice->company->name }}</div>
            </div>
            <div class="signature-block">
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
