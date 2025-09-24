<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #1f2937;
            background: #ffffff;
        }

        /* Layout */
        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm;
            margin: 0 auto;
            position: relative;
        }

        /* Watermark for draft invoices */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            font-weight: bold;
            color: rgba(239, 68, 68, 0.1);
            z-index: -1;
            user-select: none;
            pointer-events: none;
        }

        /* Overdue watermark for overdue invoices */
        .overdue-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.15);
            z-index: -1;
            user-select: none;
            pointer-events: none;
        }

        /* Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .company-info {
            flex: 1;
        }

        /* Logo positioning */
        .company-logo {
            margin-bottom: 15px;
        }

        .logo-left { text-align: left; }
        .logo-center { text-align: center; }
        .logo-right { text-align: right; }

        .logo-small img { height: 30px; }
        .logo-medium img { height: 50px; }
        .logo-large img { height: 70px; }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .company-address {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.5;
        }

        .invoice-title {
            text-align: right;
            flex: 0 0 auto;
        }

        .invoice-title h1 {
            font-size: 36px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 15px;
        }

        .invoice-details-info {
            text-align: right;
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
        }

        .invoice-details-info .label {
            display: inline-block;
            width: 80px;
            text-align: left;
        }

        .invoice-details-info .value {
            font-family: monospace;
            color: #1f2937;
        }

        /* Customer Information */
        .customer-info {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .customer-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .customer-grid.with-shipping {
            grid-template-columns: 1fr 1fr;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .customer-details {
            font-size: 11px;
            line-height: 1.6;
            color: #4b5563;
        }

        .customer-name {
            font-weight: bold;
            color: #1f2937;
            font-size: 13px;
            margin-bottom: 5px;
        }

        /* Items table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 11px;
        }

        .items-table th {
            background: #f8fafc;
            color: #374151;
            font-weight: bold;
            text-align: left;
            padding: 12px 8px;
            border-bottom: 2px solid #e2e8f0;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        .items-table th.text-right {
            text-align: right;
        }

        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        .items-table td.text-right {
            text-align: right;
        }

        .item-description {
            font-weight: 500;
            color: #1f2937;
        }

        .item-amount {
            font-weight: 600;
            color: #1f2937;
        }

        /* Totals section */
        .totals-section {
            background: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .totals-grid {
            display: flex;
            justify-content: flex-end;
        }

        .totals-table {
            width: 300px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 11px;
        }

        .totals-row.subtotal {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 8px;
        }

        .totals-row.total {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            border-top: 2px solid #e5e7eb;
            padding-top: 12px;
            margin-top: 8px;
        }

        .totals-label {
            color: #6b7280;
        }

        .totals-amount {
            font-weight: 600;
            color: #1f2937;
        }

        .total .totals-amount {
            color: #2563eb;
        }

        /* Payment Instructions */
        .payment-instructions {
            background: #fefce8;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }

        .payment-instructions h3 {
            color: #92400e;
            margin-bottom: 15px;
            font-size: 13px;
            font-weight: bold;
        }

        .payment-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            font-size: 11px;
        }

        .payment-detail {
            margin-bottom: 8px;
        }

        .payment-detail strong {
            color: #1f2937;
            display: inline-block;
            min-width: 100px;
        }

        .payment-additional {
            grid-column: 1 / -1;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #fbbf24;
            color: #92400e;
            font-style: italic;
        }

        /* Notes and Terms */
        .notes-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 30px 0;
        }

        .notes-block h3 {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .notes-content {
            font-size: 11px;
            line-height: 1.6;
            color: #6b7280;
        }

        /* Signatures */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
        }

        .signature-block {
            text-align: center;
            padding-top: 40px;
            border-top: 1px solid #374151;
            margin-top: 20px;
            font-size: 11px;
            color: #6b7280;
        }

        .signature-title {
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }

        .signature-company {
            color: #6b7280;
        }

        .signature-date {
            color: #9ca3af;
            margin-top: 5px;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
        }

        /* Print styles */
        @media print {
            .page-break {
                page-break-before: always;
            }

            .no-break {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Watermarks for different statuses -->
        @if($invoice->status === 'DRAFT')
            <div class="watermark">DRAFT</div>
        @elseif($invoice->status === 'OVERDUE')
            <div class="overdue-watermark">OVERDUE</div>
        @endif

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="company-info">
                <!-- Company Logo (if enabled) -->
                @if($invoice->shouldShowSection('company_logo') && $invoice->company->logo)
                    <div class="company-logo logo-{{ $invoice->getLogoPosition() }} logo-{{ $invoice->getLogoSize() }}">
                        <img src="{{ $invoice->company->logo }}" alt="{{ $invoice->company->name }} Logo">
                    </div>
                @endif

                <div class="company-name">{{ $invoice->company->name }}</div>
                <div class="company-address">
                    {{ $invoice->company->address }}<br>
                    {{ $invoice->company->city }}, {{ $invoice->company->state }} {{ $invoice->company->postal_code }}<br>
                    {{ $invoice->company->phone }} • {{ $invoice->company->email }}
                </div>
            </div>

            <div class="invoice-title">
                <h1>INVOICE</h1>
                <div class="invoice-details-info">
                    <div><span class="label">Invoice #:</span> <span class="value">{{ $invoice->number }}</span></div>
                    <div><span class="label">Date:</span> <span class="value">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : 'N/A' }}</span></div>
                    <div><span class="label">Due Date:</span> <span class="value">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A' }}</span></div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-info">
            <div class="customer-grid{{ $invoice->shouldShowSection('shipping') ? ' with-shipping' : '' }}">
                <!-- Bill To -->
                <div class="bill-to">
                    <div class="section-title">Bill To</div>
                    <div class="customer-details">
                        <div class="customer-name">{{ $invoice->getCustomerDisplayName() }}</div>
                        @if($invoice->customer_company)
                            {{ $invoice->customer_company }}<br>
                        @endif
                        @if($invoice->customer_address)
                            {{ $invoice->customer_address }}<br>
                        @endif
                        @if($invoice->customer_city || $invoice->customer_state || $invoice->customer_postal_code)
                            {{ trim(implode(', ', array_filter([$invoice->customer_city, $invoice->customer_state]))) }}
                            {{ $invoice->customer_postal_code }}<br>
                        @endif
                        @if($invoice->customer_phone || $invoice->customer_email)
                            @if($invoice->customer_phone){{ $invoice->customer_phone }}@endif
                            @if($invoice->customer_phone && $invoice->customer_email) • @endif
                            @if($invoice->customer_email){{ $invoice->customer_email }}@endif
                        @endif
                    </div>
                </div>

                <!-- Ship To (if enabled) -->
                @if($invoice->shouldShowSection('shipping'))
                    <div class="ship-to">
                        <div class="section-title">Ship To</div>
                        <div class="customer-details">
                            @if($invoice->shipping_info)
                                @php $shipping = is_array($invoice->shipping_info) ? $invoice->shipping_info : json_decode($invoice->shipping_info, true); @endphp
                                @if(isset($shipping['name']) && $shipping['name'])
                                    <div class="customer-name">{{ $shipping['name'] }}</div>
                                @endif
                                @if(isset($shipping['address']) && $shipping['address'])
                                    {{ $shipping['address'] }}<br>
                                @endif
                                @if((isset($shipping['city']) && $shipping['city']) || (isset($shipping['state']) && $shipping['state']) || (isset($shipping['postal_code']) && $shipping['postal_code']))
                                    {{ trim(implode(', ', array_filter([
                                        $shipping['city'] ?? null,
                                        $shipping['state'] ?? null
                                    ]))) }}
                                    {{ $shipping['postal_code'] ?? null }}<br>
                                @endif
                            @else
                                <span style="color: #9ca3af; font-style: italic;">Same as billing address</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Line Items Table -->
        <table class="items-table no-break">
            <thead>
                <tr>
                    <th style="width: 50%">Description</th>
                    <th class="text-right" style="width: 15%">Quantity</th>
                    <th class="text-right" style="width: 15%">Rate</th>
                    <th class="text-right" style="width: 20%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            <div class="item-description">{{ $item->description }}</div>
                        </td>
                        <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-right">RM {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">
                            <div class="item-amount">RM {{ number_format($item->quantity * $item->unit_price, 2) }}</div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-section no-break">
            <div class="totals-grid">
                <div class="totals-table">
                    <div class="totals-row subtotal">
                        <span class="totals-label">Subtotal:</span>
                        <span class="totals-amount">RM {{ number_format($invoice->subtotal, 2) }}</span>
                    </div>

                    @if($invoice->discount_amount > 0)
                        <div class="totals-row">
                            <span class="totals-label">Discount ({{ $invoice->discount_percentage }}%):</span>
                            <span class="totals-amount">-RM {{ number_format($invoice->discount_amount, 2) }}</span>
                        </div>
                    @endif

                    @if($invoice->tax_amount > 0)
                        <div class="totals-row">
                            <span class="totals-label">Tax ({{ $invoice->tax_percentage }}%):</span>
                            <span class="totals-amount">RM {{ number_format($invoice->tax_amount, 2) }}</span>
                        </div>
                    @endif

                    <div class="totals-row total">
                        <span class="totals-label">Total:</span>
                        <span class="totals-amount">RM {{ number_format($invoice->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Instructions (if enabled) -->
        @if($invoice->shouldShowSection('payment_instructions'))
            <div class="payment-instructions no-break">
                <h3>Payment Instructions</h3>
                <div class="payment-details">
                    @php
                        $paymentInstructions = $invoice->getPaymentInstructions();
                    @endphp

                    <div>
                        @if($paymentInstructions['bank_name'])
                            <div class="payment-detail">
                                <strong>Bank:</strong> {{ $paymentInstructions['bank_name'] }}
                            </div>
                        @endif
                        @if($paymentInstructions['account_number'])
                            <div class="payment-detail">
                                <strong>Account:</strong> {{ $paymentInstructions['account_number'] }}
                            </div>
                        @endif
                        @if($paymentInstructions['account_holder'])
                            <div class="payment-detail">
                                <strong>Account Name:</strong> {{ $paymentInstructions['account_holder'] }}
                            </div>
                        @endif
                    </div>

                    <div>
                        @if($paymentInstructions['swift_code'])
                            <div class="payment-detail">
                                <strong>SWIFT Code:</strong> {{ $paymentInstructions['swift_code'] }}
                            </div>
                        @endif
                    </div>

                    @if($paymentInstructions['additional_info'])
                        <div class="payment-additional">
                            {{ $paymentInstructions['additional_info'] }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Notes and Terms -->
        <div class="notes-section">
            @if($invoice->notes)
                <div class="notes-block">
                    <h3>Notes</h3>
                    <div class="notes-content">
                        {!! nl2br(e($invoice->notes)) !!}
                    </div>
                </div>
            @endif

            @if($invoice->terms_conditions)
                <div class="notes-block">
                    <h3>Terms & Conditions</h3>
                    <div class="notes-content">
                        {!! nl2br(e($invoice->terms_conditions)) !!}
                    </div>
                </div>
            @endif
        </div>

        <!-- Signature Blocks (if enabled) -->
        @if($invoice->shouldShowSection('signatures'))
            <div class="signatures no-break">
                <div>
                    <div class="signature-block">
                        <div class="signature-title">Authorized Representative</div>
                        <div class="signature-company">{{ $invoice->company->name }}</div>
                    </div>
                </div>
                <div>
                    <div class="signature-block">
                        <div class="signature-title">Customer Acceptance</div>
                        <div class="signature-date">Date: _______________</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="invoice-footer">
            Generated on {{ now()->format('d/m/Y H:i') }} by {{ config('app.name') }}
        </div>
    </div>
</body>
</html>