<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        /* CSS Variables for Color Palette */
        :root {
            --background-color: {{ $palette['background_color'] ?? '#ffffff' }};
            --border-color: {{ $palette['border_color'] ?? '#e5e7eb' }};
            --heading-color: {{ $palette['heading_color'] ?? '#111827' }};
            --subheading-color: {{ $palette['subheading_color'] ?? '#1f2937' }};
            --text-color: {{ $palette['text_color'] ?? '#111827' }};
            --muted-text-color: {{ $palette['muted_text_color'] ?? '#6b7280' }};
            --accent-color: {{ $palette['accent_color'] ?? '#1d4ed8' }};
            --accent-text-color: {{ $palette['accent_text_color'] ?? '#ffffff' }};
            --table-header-background: {{ $palette['table_header_background'] ?? '#1d4ed8' }};
            --table-header-text: {{ $palette['table_header_text'] ?? '#ffffff' }};
            --table-row-even: {{ $palette['table_row_even'] ?? '#f8fafc' }};
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: var(--text-color);
            background-color: var(--background-color);
            font-size: 14px;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 0 auto;
            position: relative;
            background-color: var(--background-color);
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid var(--accent-color);
            padding-bottom: 20px;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: var(--accent-color);
            margin-bottom: 10px;
            letter-spacing: 1px;
        }

        .invoice-number {
            font-size: 16px;
            color: var(--muted-text-color);
            margin-bottom: 5px;
        }

        .invoice-date {
            font-size: 14px;
            color: var(--muted-text-color);
        }

        /* Company and Customer Info Cards */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            gap: 30px;
        }

        .info-card {
            flex: 1;
            padding: 20px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: var(--background-color);
        }

        .info-card h3 {
            font-size: 16px;
            font-weight: bold;
            color: var(--heading-color);
            margin-bottom: 10px;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 5px;
        }

        .company-logo {
            float: right;
            max-height: 60px;
            max-width: 120px;
            margin-left: 15px;
        }

        .info-line {
            margin-bottom: 6px;
            font-size: 13px;
        }

        .info-line strong {
            color: var(--subheading-color);
        }

        /* Line Items Table */
        .line-items {
            margin: 30px 0;
        }

        .line-items h3 {
            font-size: 18px;
            color: var(--heading-color);
            margin-bottom: 15px;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 8px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }

        .items-table th {
            background-color: var(--table-header-background);
            color: var(--table-header-text);
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table th:first-child {
            width: 8%;
            text-align: center;
        }

        .items-table th:nth-child(2) {
            width: 50%;
        }

        .items-table th:nth-child(3) {
            width: 12%;
            text-align: center;
        }

        .items-table th:nth-child(4) {
            width: 15%;
            text-align: right;
        }

        .items-table th:nth-child(5) {
            width: 15%;
            text-align: right;
        }

        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid var(--border-color);
            font-size: 13px;
            vertical-align: top;
        }

        .items-table tr:nth-child(even) {
            background-color: var(--table-row-even);
        }

        .items-table .item-no {
            text-align: center;
            font-weight: bold;
            color: var(--muted-text-color);
        }

        .items-table .qty {
            text-align: center;
        }

        .items-table .rate,
        .items-table .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        /* Totals Card */
        .totals-section {
            float: right;
            width: 300px;
            margin: 20px 0;
        }

        .totals-card {
            border: 2px solid var(--accent-color);
            border-radius: 8px;
            overflow: hidden;
        }

        .totals-header {
            background-color: var(--accent-color);
            color: var(--accent-text-color);
            padding: 12px 15px;
            font-weight: bold;
            text-align: center;
            font-size: 16px;
        }

        .totals-body {
            padding: 15px;
            background-color: var(--background-color);
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .total-line.final {
            border-top: 2px solid var(--accent-color);
            padding-top: 8px;
            margin-top: 10px;
            font-weight: bold;
            font-size: 16px;
            color: var(--accent-color);
        }

        .total-amount {
            font-family: 'Courier New', monospace;
        }

        /* Payment Instructions */
        .payment-instructions {
            clear: both;
            margin: 30px 0;
            padding: 20px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: #fafafa;
        }

        .payment-instructions h3 {
            color: var(--heading-color);
            margin-bottom: 12px;
            font-size: 16px;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 5px;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }

        .payment-item {
            font-size: 13px;
        }

        .payment-item strong {
            color: var(--subheading-color);
        }

        /* Terms and Notes */
        .terms-notes {
            margin: 30px 0;
        }

        .terms-notes h4 {
            font-size: 14px;
            color: var(--heading-color);
            margin-bottom: 8px;
            font-weight: bold;
        }

        .terms-notes p {
            font-size: 12px;
            line-height: 1.5;
            color: var(--muted-text-color);
            margin-bottom: 15px;
        }

        /* Signature Section */
        .signature-section {
            margin: 40px 0 20px 0;
            display: flex;
            justify-content: space-between;
            gap: 40px;
        }

        .signature-block {
            flex: 1;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid var(--border-color);
            height: 40px;
            margin-bottom: 8px;
        }

        .signature-label {
            font-size: 12px;
            color: var(--muted-text-color);
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 15mm;
            left: 20mm;
            right: 20mm;
            border-top: 1px solid var(--border-color);
            padding-top: 10px;
            font-size: 11px;
            color: var(--muted-text-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Status Watermarks */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            font-weight: bold;
            z-index: -1;
            user-select: none;
            pointer-events: none;
        }

        .watermark.draft {
            color: rgba(239, 68, 68, 0.1);
        }

        .watermark.overdue {
            color: rgba(220, 38, 38, 0.15);
            font-size: 100px;
        }

        /* Utility Classes */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        @page {
            margin: 0;
            size: A4 portrait;
        }

        @media print {
            .page {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    @php
        $totals = app(\App\Services\InvoicePdfRenderer::class)->calculateTotals($invoice);
    @endphp

    <!-- Status Watermarks -->
    @if($invoice->status === 'DRAFT')
        <div class="watermark draft">DRAFT</div>
    @elseif($invoice->status === 'OVERDUE')
        <div class="watermark overdue">OVERDUE</div>
    @endif

    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-number">{{ $invoice->number }}</div>
            <div class="invoice-date">{{ $dateHelper($invoice->created_at) }}</div>
        </div>

        <!-- Company and Customer Info -->
        <div class="info-section">
            <!-- Company Block -->
            <div class="info-card">
                <h3>From</h3>
                @if($sections['show_company_logo'] && $invoice->company->logo_path)
                    <img src="{{ public_path('storage/' . $invoice->company->logo_path) }}"
                         alt="{{ $invoice->company->name }}" class="company-logo">
                @endif
                <div class="info-line"><strong>{{ $invoice->company->name }}</strong></div>
                @if($invoice->company->registration_number)
                    <div class="info-line">Reg: {{ $invoice->company->registration_number }}</div>
                @endif
                @if($invoice->company->address)
                    <div class="info-line">{{ $invoice->company->address }}</div>
                @endif
                @if($invoice->company->city || $invoice->company->state || $invoice->company->postal_code)
                    <div class="info-line">
                        {{ collect([$invoice->company->city, $invoice->company->state, $invoice->company->postal_code])->filter()->join(', ') }}
                    </div>
                @endif
                @if($invoice->company->phone)
                    <div class="info-line">Tel: {{ $invoice->company->phone }}</div>
                @endif
                @if($invoice->company->email)
                    <div class="info-line">Email: {{ $invoice->company->email }}</div>
                @endif
            </div>

            <!-- Customer Block -->
            <div class="info-card">
                <h3>Bill To</h3>
                <div class="info-line"><strong>{{ $invoice->customer_name }}</strong></div>
                @if($invoice->customer_email)
                    <div class="info-line">{{ $invoice->customer_email }}</div>
                @endif
                @if($invoice->customer_phone)
                    <div class="info-line">{{ $invoice->customer_phone }}</div>
                @endif
                @if($invoice->customer_address)
                    <div class="info-line">{{ $invoice->customer_address }}</div>
                @endif
                <div style="margin-top: 15px;">
                    <div class="info-line"><strong>Invoice Date:</strong> {{ $dateHelper($invoice->created_at) }}</div>
                    @if($invoice->due_date)
                        <div class="info-line"><strong>Due Date:</strong> {{ $dateHelper($invoice->due_date) }}</div>
                    @endif
                    @if($invoice->payment_terms)
                        <div class="info-line"><strong>Payment Terms:</strong> {{ $invoice->payment_terms }} days</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <div class="line-items">
            <h3>Items & Services</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            <td class="item-no">{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $item->description }}</strong>
                                @if($item->notes)
                                    <br><small style="color: var(--muted-text-color);">{{ $item->notes }}</small>
                                @endif
                            </td>
                            <td class="qty">{{ number_format($item->quantity, 0) }}</td>
                            <td class="rate">{{ $currencyHelper($item->unit_price) }}</td>
                            <td class="amount">{{ $currencyHelper($item->quantity * $item->unit_price) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Card -->
        <div class="totals-section">
            <div class="totals-card">
                <div class="totals-header">Invoice Total</div>
                <div class="totals-body">
                    <div class="total-line">
                        <span>Subtotal:</span>
                        <span class="total-amount">{{ $currencyHelper($totals['subtotal']) }}</span>
                    </div>
                    @if($totals['discount'] > 0)
                        <div class="total-line">
                            <span>Discount:</span>
                            <span class="total-amount">-{{ $currencyHelper($totals['discount']) }}</span>
                        </div>
                    @endif
                    @if($invoice->tax_rate > 0)
                        <div class="total-line">
                            <span>Tax ({{ $invoice->tax_rate }}%):</span>
                            <span class="total-amount">{{ $currencyHelper($totals['tax']) }}</span>
                        </div>
                    @endif
                    <div class="total-line final">
                        <span>Total:</span>
                        <span class="total-amount">{{ $currencyHelper($totals['total']) }}</span>
                    </div>
                    @if($totals['total_paid'] > 0)
                        <div class="total-line" style="color: #059669;">
                            <span>Amount Paid:</span>
                            <span class="total-amount">{{ $currencyHelper($totals['total_paid']) }}</span>
                        </div>
                        <div class="total-line final" style="color: {{ $totals['balance'] > 0 ? 'var(--accent-color)' : '#059669' }};">
                            <span>{{ $totals['balance'] > 0 ? 'Balance Due:' : 'Fully Paid' }}</span>
                            <span class="total-amount">{{ $totals['balance'] > 0 ? $currencyHelper($totals['balance']) : '✓' }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

        <!-- Payment Instructions -->
        @if($sections['show_payment_instructions'])
            <div class="payment-instructions">
                <h3>Payment Instructions</h3>
                <p>Please make payment using any of the following methods:</p>
                <div class="payment-grid">
                    <div class="payment-item">
                        <strong>Bank Name:</strong><br>
                        {{ $invoice->company->bank_name ?? 'Please contact us for bank details' }}
                    </div>
                    <div class="payment-item">
                        <strong>Account Number:</strong><br>
                        {{ $invoice->company->bank_account ?? 'Available upon request' }}
                    </div>
                    <div class="payment-item">
                        <strong>Account Holder:</strong><br>
                        {{ $invoice->company->bank_account_holder ?? $invoice->company->name }}
                    </div>
                    <div class="payment-item">
                        <strong>Reference:</strong><br>
                        {{ $invoice->number }}
                    </div>
                </div>
                <p style="margin-top: 15px; font-style: italic;">
                    Please include the invoice number as your payment reference for faster processing.
                </p>
            </div>
        @endif

        <!-- Terms and Notes -->
        @if($sections['show_terms_conditions'] && ($invoice->terms_conditions || $invoice->notes))
            <div class="terms-notes">
                @if($invoice->terms_conditions)
                    <h4>Terms & Conditions</h4>
                    <p>{{ $invoice->terms_conditions }}</p>
                @endif
                @if($invoice->notes)
                    <h4>Notes</h4>
                    <p>{{ $invoice->notes }}</p>
                @endif
            </div>
        @endif

        <!-- Signature Section -->
        @if($sections['show_signatures'])
            <div class="signature-section">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Authorized Representative</div>
                    <div class="signature-label">{{ $invoice->company->name }}</div>
                </div>
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <div class="signature-label">Customer Acceptance</div>
                    <div class="signature-label">{{ $invoice->customer_name }}</div>
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <span>{{ $invoice->company->name }} - Invoice {{ $invoice->number }}</span>
            <span>Generated on {{ now()->format('d M, Y H:i') }}</span>
        </div>
    </div>
</body>
</html>