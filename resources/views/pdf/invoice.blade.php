<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        :root {
            --primary-blue: {{ $palette['accent_color'] ?? '#1e40af' }};
            --white: {{ $palette['accent_text_color'] ?? '#ffffff' }};
            --text-black: {{ $palette['text_color'] ?? '#000000' }};
            --text-grey: {{ $palette['muted_text_color'] ?? '#6b7280' }};
            --heading-color: {{ $palette['heading_color'] ?? '#000000' }};
            --border-color: {{ $palette['border_color'] ?? '#d1d5db' }};
            --table-header-bg: {{ $palette['table_header_background'] ?? '#1e40af' }};
            --table-header-text: {{ $palette['table_header_text'] ?? '#ffffff' }};
            --balance-due-color: #dc2626;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: var(--text-black);
            line-height: 1.55;
            background: #ffffff;
        }

        .page {
            width: 180mm;
            min-height: 297mm;
            margin: 0;
            padding: 10mm;
            position: relative;
            box-sizing: border-box;
        }

        h1, h2, h3 {
            margin: 0;
            font-weight: 600;
            color: var(--heading-color);
        }

        .title {
            text-align: center;
            letter-spacing: 3px;
            font-size: 26px;
            margin-bottom: 12mm;
        }

        .header {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            vertical-align: top;
            padding: 0;
        }

        .company-block {
            width: 70%;
        }

        .company-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 4px;
        }

        .company-line {
            font-size: 12px;
            color: var(--text-grey);
        }

        .logo {
            max-width: 120px;
            max-height: 60px;
            object-fit: contain;
        }

        .top-divider {
            border: none;
            border-top: 2px solid var(--primary-blue);
            margin: 12mm 0 10mm;
        }

        .info-row {
            width: 100%;
            border-collapse: collapse;
        }

        .info-row td {
            vertical-align: top;
            padding: 0 2mm 0 0;
        }

        .info-row td:last-child {
            padding-right: 0;
        }

        .card {
            width: 100%;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 12px 14px;
            background: #fff;
            box-sizing: border-box;
        }

        .card-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .meta-table td {
            padding: 3px 0;
        }

        .meta-label {
            color: var(--text-grey);
            padding-right: 12px;
            white-space: nowrap;
        }

        .bill-line {
            font-size: 12px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12mm;
        }

        .items-table th {
            background: var(--table-header-bg);
            color: var(--table-header-text);
            padding: 8px 10px;
            font-size: 12px;
            text-align: left;
        }

        .items-table th:nth-child(1),
        .items-table td:nth-child(1) {
            width: 8%;
            text-align: center;
        }

        .items-table th:nth-child(3),
        .items-table td:nth-child(3) {
            width: 12%;
            text-align: center;
        }

        .items-table th:nth-child(4),
        .items-table td:nth-child(4),
        .items-table th:nth-child(5),
        .items-table td:nth-child(5) {
            width: 18%;
            text-align: right;
        }

        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border-color);
            font-size: 12px;
        }

        .items-table tbody tr:nth-child(even) {
            background: var(--table-row-even);
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12mm;
        }

        .summary-grid td {
            vertical-align: top;
            padding: 0 2mm 0 0;
        }

        .summary-grid td:last-child {
            padding-right: 0;
        }

        .payment-instructions {
            width: 60%;
        }

        .payment-text {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 12px;
            color: var(--text-black);
            white-space: pre-line;
        }

        .totals {
            width: 40%;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .totals-table td {
            padding: 3px 0;
        }

        .totals-table td:first-child {
            color: var(--text-grey);
            padding-right: 12px;
        }

        .totals-table tr.total td {
            font-weight: 600;
            border-top: 1px solid var(--border-color);
            padding-top: 6px;
            color: var(--heading-color);
        }

        .totals-table tr.balance td {
            font-weight: 600;
            color: #b91c1c;
        }

        .footer-signature {
            margin-top: 18mm;
            text-align: right;
            font-size: 12px;
        }

        .signature-line {
            display: inline-block;
            border-top: 1px solid var(--border-color);
            padding-top: 6px;
            width: 170px;
            text-align: center;
        }

        .footer {
            margin-top: 20mm;
            text-align: center;
            font-size: 10px;
            color: var(--text-grey);
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

    $paymentInstructions = $invoice->payment_instructions
        ?: ($invoice->company->invoice_settings['content']['payment_instructions']['additional_info'] ?? '');

    $logoPath = null;
    if (($sections['show_company_logo'] ?? true) && !empty($invoice->company?->logo)) {
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
    <h1 class="title">INVOICE</h1>

    <table class="header">
        <tr>
            <td class="company-block">
                <div class="company-name">{{ $invoice->company->name ?? 'Company Name' }}</div>
                @foreach([
                    $invoice->company->address,
                    trim(collect([$invoice->company->postal_code, $invoice->company->city])->filter()->implode(' ')),
                    $invoice->company->state,
                    'Email: ' . ($invoice->company->email ?? '�'),
                    'Tel: ' . ($invoice->company->phone ?? '�'),
                ] as $line)
                    @if(!empty(trim($line, ' -')))
                        <div class="company-line">{{ $line }}</div>
                    @endif
                @endforeach
            </td>
            <td style="width: 30%; text-align: right;">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Company Logo" class="logo">
                @endif
            </td>
        </tr>
    </table>

    <hr class="top-divider">

    <table class="info-row">
        <tr>
            <td style="width: 50%;">
                <div class="card">
                    <div class="card-title">Bill To</div>
                    <div class="bill-line">{{ $invoice->customer_name ?? 'Customer Name' }}</div>
                    @if($invoice->customer_company)
                        <div class="bill-line">{{ $invoice->customer_company }}</div>
                    @endif
                    @foreach($billAddress as $line)
                        <div class="bill-line">{{ $line }}</div>
                    @endforeach
                    @if($invoice->customer_email)
                        <div class="bill-line">Email: {{ $invoice->customer_email }}</div>
                    @endif
                    @if($invoice->customer_phone)
                        <div class="bill-line">Phone: {{ $invoice->customer_phone }}</div>
                    @endif
                </div>
            </td>
            <td style="width: 50%;">
                <div class="card">
                    <div class="card-title">Invoice Info</div>
                    <table class="meta-table">
                        <tr>
                            <td class="meta-label">Invoice No :</td>
                            <td>{{ $invoice->number }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Invoice Date :</td>
                            <td>{{ optional($invoice->issued_date)->format('d M, Y') ?? now()->format('d M, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Due Date :</td>
                            <td>{{ optional($invoice->due_date)->format('d M, Y') ?? '�' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Payment Terms :</td>
                            <td>{{ $invoice->payment_terms ? $invoice->payment_terms . ' days' : '�' }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Sl.</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $item->description }}
                        @if($item->specifications)
                            <div style="font-size: 11px; color: var(--muted-color);">{{ $item->specifications }}</div>
                        @endif
                    </td>
                    <td>{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                    <td>{{ $money($item->unit_price) }}</td>
                    <td>{{ $money($item->total_price ?? ($item->quantity * $item->unit_price)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-grid">
        <tr>
            <td class="payment-instructions">
                <div class="card-title" style="margin-bottom: 6px;">Payment Instructions</div>
                <div class="payment-text">{{ $paymentInstructions ? strip_tags($paymentInstructions) : 'Please include the invoice number as your payment reference.' }}</div>
            </td>
            <td class="totals">
                <table class="totals-table">
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
                    <tr class="balance">
                        <td>Balance Due</td>
                        <td>{{ $money($balance) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer-signature">
        <div class="signature-line">
            {{ $invoice->company->invoice_settings['content']['signature_blocks']['company_signature_title'] ?? 'Marketing Manager' }}
        </div>
    </div>

    <div class="footer">
        {{ $invoice->company->name ?? 'Company' }} � Invoice {{ $invoice->number }} � Generated on {{ now()->format('d M Y, H:i') }}
    </div>
</div>
</body>
</html>
