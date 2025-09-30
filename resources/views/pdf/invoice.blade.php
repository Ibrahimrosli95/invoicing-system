@php
    $primaryBlue = !empty($palette['accent_color']) ? $palette['accent_color'] : '#0b57d0';
    $primaryContrast = !empty($palette['accent_text_color']) ? $palette['accent_text_color'] : '#ffffff';
    $textColor = !empty($palette['text_color']) ? $palette['text_color'] : '#000000';
    $mutedColor = !empty($palette['muted_text_color']) ? $palette['muted_text_color'] : '#4b5563';
    $headingColor = !empty($palette['heading_color']) ? $palette['heading_color'] : '#000000';
    $borderColor = !empty($palette['border_color']) ? $palette['border_color'] : '#d0d5dd';
    $tableHeaderBg = !empty($palette['table_header_background']) ? $palette['table_header_background'] : '#0b57d0';
    $tableHeaderText = !empty($palette['table_header_text']) ? $palette['table_header_text'] : '#ffffff';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: {{ $textColor }};
            background: #ffffff;
            line-height: 1.5;
        }

        .page {
            width: 174mm;
            margin: 0 auto;
            padding: 12mm 14mm;
        }

        h1, h2, h3 {
            margin: 0;
            font-weight: 600;
            color: {{ $headingColor }};
        }

        .title {
            text-align: center;
            font-size: 26px;
            letter-spacing: 2px;
            margin-bottom: 10mm;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .company-name {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
            color: {{ $primaryBlue }};
        }

        .company-line {
            color: {{ $mutedColor }};
            font-size: 12px;
        }

        .logo {
            max-width: 120px;
            max-height: 60px;
            object-fit: contain;
        }

        .separator {
            border: none;
            border-top: 2px solid {{ $primaryBlue }};
            margin: 10mm 0 8mm;
        }

        .section-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
            color: {{ $headingColor }};
        }

        .bill-table td {
            padding: 2px 0;
        }

        .meta-table {
            width: 100%;
            font-size: 12px;
        }

        .meta-table td {
            padding: 2px 4px;
        }

        .meta-table td:first-child {
            font-weight: 600;
            color: {{ $mutedColor }};
            padding-right: 10px;
            white-space: nowrap;
        }

        .items-table {
            margin-top: 8mm;
            font-size: 12px;
        }

        .items-table thead th {
            background: {{ $tableHeaderBg }};
            color: {{ $tableHeaderText }};
            padding: 8px 8px;
            font-weight: 600;
            text-align: left;
            border: 1px solid {{ $tableHeaderBg }};
        }

        .items-table thead th:nth-child(1) { width: 8%; text-align: center; }
        .items-table thead th:nth-child(3) { width: 10%; text-align: center; }
        .items-table thead th:nth-child(4),
        .items-table thead th:nth-child(5) { width: 18%; text-align: right; }

        .items-table tbody td {
            padding: 6px 8px;
            border: 1px solid {{ $borderColor }};
        }

        .items-table tbody td:nth-child(1) { text-align: center; }
        .items-table tbody td:nth-child(3) { text-align: center; }
        .items-table tbody td:nth-child(4),
        .items-table tbody td:nth-child(5) { text-align: right; }

        .summary-table {
            margin-top: 8mm;
        }

        .summary-table td {
            vertical-align: top;
            padding: 0;
        }

        .payment-block { padding-right: 12mm; }

        .payment-text {
            border: 1px solid {{ $borderColor }};
            padding: 8px 10px;
            border-radius: 4px;
            white-space: pre-line;
            color: {{ $textColor }};
        }

        .totals-table {
            width: 100%;
            font-size: 12px;
        }

        .totals-table td {
            padding: 2px 0;
        }

        .totals-table td:first-child {
            text-align: left;
            color: {{ $mutedColor }};
            padding-right: 12px;
        }

        .totals-table td:last-child {
            text-align: right;
            width: 110px;
        }

        .totals-table .total-row td {
            font-weight: 600;
            padding-top: 4px;
            border-top: 1px solid {{ $borderColor }};
            color: {{ $headingColor }};
        }

        .totals-table .balance-row td {
            font-weight: 700;
            color: #dc2626;
        }

        .signature-table {
            width: 100%;
            margin-top: 12mm;
            font-size: 12px;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            padding-top: 12mm;
        }

        .signature-line {
            border-top: 1px solid {{ $borderColor }};
            padding-top: 4px;
            width: 75%;
            margin: 0 auto;
        }

        .footer {
            margin-top: 10mm;
            text-align: center;
            font-size: 10px;
            color: {{ $mutedColor }};
        }
    </style>
</head>
<body>
@php
    $currency = $currency ?? ($invoice->company->invoice_settings['defaults']['currency'] ?? 'RM');
    $format = fn($value) => trim(($currency ? $currency . ' ' : '') . number_format((float) $value, 2));
    $subtotal = $invoice->subtotal ?? $invoice->items->sum(fn($item) => $item->total_price ?? ($item->quantity * $item->unit_price));
    $discount = $invoice->discount_amount ?? 0;
    $tax = $invoice->tax_amount ?? 0;
    $total = $invoice->total ?? ($subtotal - $discount + $tax);
    $paid = $invoice->amount_paid ?? 0;
    $balance = max(0, $total - $paid);

    $paymentText = trim($invoice->payment_instructions);
    if ($paymentText === '') {
        $holder = $invoice->company->invoice_settings['content']['payment_instructions']['account_holder'] ?? ($invoice->company->name ?? '');
        $bank = $invoice->company->invoice_settings['content']['payment_instructions']['bank_name'] ?? '';
        $account = $invoice->company->invoice_settings['content']['payment_instructions']['account_number'] ?? '';
        $lines = [];
        if ($holder) {
            $lines[] = 'Pay Cheque to ' . $holder;
        }
        if ($bank && $account) {
            $lines[] = 'Send to bank (' . $bank . ') ' . $account;
        } elseif ($bank) {
            $lines[] = 'Bank: ' . $bank;
        }
        $lines[] = 'Please include invoice number in payment reference.';
        $paymentText = implode("\n", array_filter($lines));
    }

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

    <table class="header-table">
        <tr>
            <td class="company-block">
                <div class="company-name">{{ $invoice->company->name ?? 'Company Name' }}</div>
                @foreach([
                    $invoice->company->address,
                    trim(collect([$invoice->company->postal_code, $invoice->company->city])->filter()->implode(' ')),
                    $invoice->company->state,
                    'Email: ' . ($invoice->company->email ?? '—'),
                    'Mobile: ' . ($invoice->company->phone ?? '—'),
                ] as $line)
                    @if(!empty(trim($line, ' -')))
                        <div class="company-line">{{ $line }}</div>
                    @endif
                @endforeach
            </td>
            <td style="width:30%; text-align:right;">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Company Logo" class="logo">
                @endif
            </td>
        </tr>
    </table>

    <hr class="separator">

    <table class="info-row">
        <tr>
            <td style="width:50%; padding-right:6mm;">
                <div class="section-label">Bill To</div>
                <table class="bill-table">
                    <tr><td>{{ $invoice->customer_name ?? 'Customer Name' }}</td></tr>
                    @if($invoice->customer_company)
                        <tr><td>{{ $invoice->customer_company }}</td></tr>
                    @endif
                    @foreach($billAddress as $line)
                        <tr><td>{{ $line }}</td></tr>
                    @endforeach
                    @if($invoice->customer_email)
                        <tr><td>Email: {{ $invoice->customer_email }}</td></tr>
                    @endif
                    @if($invoice->customer_phone)
                        <tr><td>Phone: {{ $invoice->customer_phone }}</td></tr>
                    @endif
                </table>
            </td>
            <td style="width:50%;">
                <div class="section-label">Invoice Info</div>
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
                    <td>{{ $item->description }}</td>
                    <td>{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                    <td>{{ $format($item->unit_price) }}</td>
                    <td>{{ $format($item->total_price ?? ($item->quantity * $item->unit_price)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td class="payment-block">
                <div class="section-label">Payment Instructions</div>
                <div class="payment-text">{!! nl2br(e($paymentText)) !!}</div>
            </td>
            <td>
                <table class="totals-table">
                    <tr>
                        <td>Subtotal</td>
                        <td>{{ $format($subtotal) }}</td>
                    </tr>
                    @if($discount > 0)
                        <tr>
                            <td>Discount</td>
                            <td>-{{ $format($discount) }}</td>
                        </tr>
                    @endif
                    @if($tax > 0)
                        <tr>
                            <td>Tax</td>
                            <td>{{ $format($tax) }}</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td>Total</td>
                        <td>{{ $format($total) }}</td>
                    </tr>
                    <tr>
                        <td>Paid</td>
                        <td>{{ $format($paid) }}</td>
                    </tr>
                    <tr class="balance-row">
                        <td>Balance Due</td>
                        <td>{{ $format($balance) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="signature-table">
        <tr>
            @php
                $author = $invoice->createdBy;
                $authorName = $author?->name ?? 'Marketing Manager';
                $authorTitle = $invoice->company->invoice_settings['content']['signature_blocks']['company_signature_title'] ?? 'Marketing Manager';
                $authorSignature = $author?->signature_path ? public_path('storage/' . ltrim($author->signature_path, '/')) : null;
                if ($authorSignature && file_exists($authorSignature)) {
                    $authorSignature = 'file://' . str_replace('\\\\', '/', $authorSignature);
                } else {
                    $authorSignature = null;
                }
            @endphp
            <td>
                @if($authorSignature)
                    <img src="{{ $authorSignature }}" alt="Signature" style="max-height:40px; margin-bottom:4mm;">
                @endif
                <div class="signature-line">{{ $authorTitle }}</div>
                <div style="margin-top:2px;">{{ $authorName }}</div>
            </td>
            <td>
                <div class="signature-line">Customer Acceptance</div>
                <div style="margin-top:2px;">{{ $invoice->customer_name ?? 'Customer' }}</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        {{ $invoice->company->name ?? 'Company' }} • Invoice {{ $invoice->number }} • Generated on {{ now()->format('d M Y, H:i') }}
    </div>
</div>
</body>
</html>
