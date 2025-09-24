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
            color: rgba(220, 38, 38, 0.2);
            z-index: -1;
            user-select: none;
            pointer-events: none;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-logo {
            width: 120px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 8px;
        }
        
        .company-details {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
        }
        
        .invoice-info {
            text-align: right;
            min-width: 200px;
        }
        
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        
        .invoice-number {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .invoice-date {
            font-size: 11px;
            color: #6b7280;
        }
        
        .invoice-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
        }
        
        .status-draft { background: #f3f4f6; color: #374151; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-partial { background: #fef3c7; color: #d97706; }
        .status-paid { background: #d1fae5; color: #059669; }
        .status-overdue { background: #fee2e2; color: #dc2626; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        
        /* Main content */
        .main-content {
            margin-bottom: 30px;
        }
        
        .billing-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .bill-to, .invoice-details {
            flex: 1;
            margin-right: 30px;
        }
        
        .invoice-details {
            margin-right: 0;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .bill-to-info, .invoice-details-info {
            font-size: 12px;
            line-height: 1.6;
            color: #4b5563;
        }
        
        .customer-name {
            font-weight: bold;
            color: #1f2937;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        /* Payment status highlight */
        .payment-status {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }
        
        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            text-align: center;
        }
        
        .payment-item {
            border-right: 1px solid #e2e8f0;
        }
        
        .payment-item:last-child {
            border-right: none;
        }
        
        .payment-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 5px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .payment-amount {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
        }
        
        .amount-total { color: #2563eb; }
        .amount-paid { color: #059669; }
        .amount-due { color: #dc2626; }
        
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
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        
        .items-table tbody tr:hover {
            background: #fafbfc;
        }
        
        .item-description {
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 3px;
        }
        
        .item-code {
            font-size: 9px;
            color: #6b7280;
            font-family: monospace;
        }
        
        .item-specs {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
            font-style: italic;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .font-semibold {
            font-weight: 600;
        }
        
        /* Totals section */
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        
        .totals-table {
            width: 300px;
            font-size: 12px;
        }
        
        .totals-table tr {
            border-bottom: 1px solid #f1f5f9;
        }
        
        .totals-table tr:last-child {
            border-bottom: 2px solid #2563eb;
            font-weight: bold;
            font-size: 14px;
        }
        
        .totals-table td {
            padding: 8px 12px;
        }
        
        .totals-table .label {
            color: #6b7280;
            text-align: right;
        }
        
        .totals-table .amount {
            text-align: right;
            font-weight: 600;
            color: #1f2937;
            min-width: 120px;
        }
        
        /* Payment history */
        .payment-history {
            margin-bottom: 30px;
        }
        
        .payment-record {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 0 4px 4px 0;
        }
        
        .payment-record-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .payment-amount-record {
            font-weight: bold;
            color: #15803d;
        }
        
        .payment-method {
            font-size: 10px;
            color: #6b7280;
        }
        
        .payment-reference {
            font-size: 10px;
            color: #6b7280;
            font-family: monospace;
        }
        
        /* Terms and notes */
        .terms-notes {
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            margin-top: 20px;
        }
        
        .terms-section, .notes-section {
            margin-bottom: 15px;
        }
        
        .terms-title, .notes-title {
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .terms-content, .notes-content {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.5;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 15mm;
            left: 15mm;
            right: 15mm;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
        
        .footer-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Page break */
        .page-break {
            page-break-before: always;
        }
        
        /* Print styles */
        @media print {
            .page {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <!-- Watermark for draft invoices -->
    @if($invoice->status === 'DRAFT')
        <div class="watermark">DRAFT</div>
    @endif
    
    <!-- Watermark for overdue invoices -->
    @if($invoice->status === 'OVERDUE')
        <div class="overdue-watermark">OVERDUE</div>
    @endif

    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                @if($invoice->company->logo)
                    <img src="{{ asset('storage/' . $invoice->company->logo) }}" alt="{{ $invoice->company->name }}" class="company-logo">
                @endif
                <div class="company-name">{{ $invoice->company->name ?? 'Bina Group' }}</div>
                <div class="company-details">
                    @if($invoice->company->address)
                        {{ $invoice->company->address }}<br>
                    @endif
                    @if($invoice->company->phone)
                        Phone: {{ $invoice->company->phone }}<br>
                    @endif
                    @if($invoice->company->email)
                        Email: {{ $invoice->company->email }}<br>
                    @endif
                    @if($invoice->company->website)
                        {{ $invoice->company->website }}
                    @endif
                </div>
            </div>
            
            <div class="invoice-info">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">{{ $invoice->number }}</div>
                <div class="invoice-date">
                    Issue Date: @displayDate($invoice->created_at)<br>
                    Due Date: @displayDate($invoice->due_date)
                    @if($invoice->status === 'OVERDUE' && $invoice->overdue_days > 0)
                        <br><strong style="color: #dc2626;">{{ $invoice->overdue_days }} days overdue</strong>
                    @endif
                </div>
                <div class="invoice-status status-{{ strtolower($invoice->status) }}">
                    {{ $invoice->status }}
                </div>
            </div>
        </div>

        <!-- Payment Status Summary -->
        <div class="payment-status">
            <div class="payment-grid">
                <div class="payment-item">
                    <div class="payment-label">Total Amount</div>
                    <div class="payment-amount amount-total">RM {{ number_format($invoice->total_amount, 2) }}</div>
                </div>
                <div class="payment-item">
                    <div class="payment-label">Amount Paid</div>
                    <div class="payment-amount amount-paid">RM {{ number_format($invoice->amount_paid, 2) }}</div>
                </div>
                <div class="payment-item">
                    <div class="payment-label">Amount Due</div>
                    <div class="payment-amount amount-due">RM {{ number_format($invoice->amount_due, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Billing Information -->
        <div class="billing-info">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <div class="bill-to-info">
                    <div class="customer-name">{{ $invoice->customer_name }}</div>
                    @if($invoice->customer_phone)
                        Phone: {{ $invoice->customer_phone }}<br>
                    @endif
                    @if($invoice->customer_email)
                        Email: {{ $invoice->customer_email }}<br>
                    @endif
                    @if($invoice->customer_address)
                        {{ $invoice->customer_address }}<br>
                        @if($invoice->customer_city){{ $invoice->customer_city }}, @endif
                        @if($invoice->customer_state){{ $invoice->customer_state }} @endif
                        @if($invoice->customer_postal_code){{ $invoice->customer_postal_code }}@endif
                    @endif
                </div>
            </div>
            
            <div class="invoice-details">
                <div class="section-title">Invoice Details</div>
                <div class="invoice-details-info">
                    <strong>Invoice Number:</strong> {{ $invoice->number }}<br>
                    <strong>Issue Date:</strong> @displayDate($invoice->created_at)<br>
                    <strong>Due Date:</strong> @displayDate($invoice->due_date)<br>
                    <strong>Payment Terms:</strong> {{ $invoice->payment_terms_days }} days<br>
                    @if($invoice->quotation)
                        <strong>From Quotation:</strong> {{ $invoice->quotation->number }}<br>
                    @endif
                    @if($invoice->team)
                        <strong>Team:</strong> {{ $invoice->team->name }}<br>
                    @endif
                    @if($invoice->assignedTo)
                        <strong>Contact:</strong> {{ $invoice->assignedTo->name }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%">Description</th>
                    <th style="width: 10%">Unit</th>
                    <th style="width: 12%" class="text-center">Quantity</th>
                    <th style="width: 14%" class="text-right">Unit Price</th>
                    <th style="width: 14%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                    <tr>
                        <td>
                            <div class="item-description">{{ $item->description }}</div>
                            @if($item->item_code)
                                <div class="item-code">Code: {{ $item->item_code }}</div>
                            @endif
                            @if($item->specifications)
                                <div class="item-specs">{{ $item->specifications }}</div>
                            @endif
                        </td>
                        <td>{{ $item->unit }}</td>
                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                        <td class="text-right">RM {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right font-semibold">RM {{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="amount">RM {{ number_format($invoice->subtotal_amount, 2) }}</td>
                </tr>
                @if($invoice->discount_percentage > 0)
                <tr>
                    <td class="label">Discount ({{ $invoice->discount_percentage }}%):</td>
                    <td class="amount">-RM {{ number_format($invoice->discount_amount, 2) }}</td>
                </tr>
                @endif
                @if($invoice->tax_percentage > 0)
                <tr>
                    <td class="label">Tax ({{ $invoice->tax_percentage }}%):</td>
                    <td class="amount">RM {{ number_format($invoice->tax_amount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label">Total Amount:</td>
                    <td class="amount">RM {{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                @if($invoice->amount_paid > 0)
                <tr style="color: #059669;">
                    <td class="label">Amount Paid:</td>
                    <td class="amount">-RM {{ number_format($invoice->amount_paid, 2) }}</td>
                </tr>
                @endif
                @if($invoice->amount_due > 0)
                <tr style="color: #dc2626; font-size: 16px;">
                    <td class="label">Amount Due:</td>
                    <td class="amount">RM {{ number_format($invoice->amount_due, 2) }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Payment History -->
        @if($invoice->paymentRecords->count() > 0)
        <div class="payment-history">
            <div class="section-title">Payment History</div>
            @foreach($invoice->paymentRecords as $payment)
                <div class="payment-record">
                    <div class="payment-record-header">
                        <span class="payment-amount-record">RM {{ number_format($payment->amount, 2) }}</span>
                        <span class="payment-method">{{ $payment->payment_method }} • @displayDate($payment->payment_date)</span>
                    </div>
                    @if($payment->reference_number || $payment->receipt_number)
                        <div class="payment-reference">
                            @if($payment->reference_number)Ref: {{ $payment->reference_number }} • @endif
                            @if($payment->receipt_number)Receipt: {{ $payment->receipt_number }}@endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @endif

        <!-- Social Proof Section -->
        @php
            $pdfService = app(\App\Services\PDFService::class);
            $proofs = $pdfService->getProofsForPDF($invoice, 'invoice');
        @endphp

        @if($proofs->isNotEmpty())
            <div style="margin-top: 30px; page-break-inside: avoid;">
                <div class="section-title">{{ $pdfService->getProofSectionTitle('invoice') }}</div>
                
                <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <!-- Proof Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 15px;">
                        @foreach($proofs->take(6) as $proof)
                            <div style="background: white; padding: 10px; border-radius: 6px; border: 1px solid #e5e7eb;">
                                <!-- Proof Header -->
                                <div style="display: flex; align-items: center; margin-bottom: 6px;">
                                    @if($proof->is_featured)
                                        <span style="background: #fbbf24; color: white; font-size: 8px; padding: 2px 6px; border-radius: 10px; margin-right: 6px; font-weight: 600;">★</span>
                                    @endif
                                    <span style="background: {{ $proof->getCategoryColor() }}; color: white; font-size: 8px; padding: 2px 6px; border-radius: 10px; font-weight: 600; text-transform: uppercase;">
                                        {{ $proof->type_label }}
                                    </span>
                                </div>

                                <!-- Proof Title -->
                                <div style="font-size: 10px; font-weight: 600; color: #1f2937; margin-bottom: 4px; line-height: 1.2;">
                                    {{ Str::limit($proof->title, 35) }}
                                </div>

                                <!-- Proof Description -->
                                @if($proof->description)
                                    <div style="font-size: 8px; color: #6b7280; margin-bottom: 6px; line-height: 1.3;">
                                        {{ Str::limit($proof->description, 60) }}
                                    </div>
                                @endif

                                <!-- Proof Assets (Smaller for invoice) -->
                                @php
                                    $displayAssets = $pdfService->filterAssetsForPDF($proof, 1);
                                @endphp
                                
                                @if($displayAssets->isNotEmpty())
                                    <div style="margin-bottom: 4px;">
                                        @foreach($displayAssets as $asset)
                                            @if($asset->isImage())
                                                <div style="width: 18px; height: 18px; border-radius: 2px; overflow: hidden; border: 1px solid #e5e7eb; display: inline-block;">
                                                    <img src="{{ asset('storage/' . ($asset->thumbnail_path ?: $asset->file_path)) }}" 
                                                         alt="{{ $asset->alt_text }}"
                                                         style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                            @endif
                                        @endforeach
                                        @if($proof->assets->count() > 1)
                                            <span style="font-size: 7px; color: #6b7280; margin-left: 4px;">
                                                +{{ $proof->assets->count() - 1 }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <!-- Proof Stats (Compact) -->
                                @if($proof->views_count > 0 || $proof->conversion_impact)
                                    <div style="font-size: 7px; color: #6b7280; display: flex; justify-content: space-between;">
                                        @if($proof->views_count > 0)
                                            <span>{{ number_format($proof->views_count) }} views</span>
                                        @endif
                                        @if($proof->conversion_impact)
                                            <span style="background: #10b981; color: white; padding: 1px 3px; border-radius: 6px; font-weight: 600;">
                                                {{ $proof->conversion_impact }}%
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Additional Proofs Summary -->
                    @if($proofs->count() > 6)
                        <div style="text-align: center; padding: 6px; background: white; border-radius: 4px; border: 1px solid #e5e7eb; margin-bottom: 10px;">
                            <div style="font-size: 9px; color: #6b7280;">
                                + {{ $proofs->count() - 6 }} more {{ Str::plural('credential', $proofs->count() - 6) }} available
                            </div>
                        </div>
                    @endif

                    <!-- Compact Proof Summary -->
                    @php
                        $proofAnalytics = $pdfService->getProofAnalytics($proofs);
                    @endphp
                    
                    <div style="padding-top: 8px; border-top: 1px solid #e5e7eb; text-align: center;">
                        <div style="font-size: 8px; color: #6b7280;">
                            <strong>{{ $proofAnalytics['total_proofs'] }}</strong> credentials • 
                            @if($proofAnalytics['featured_count'] > 0)
                                <strong>{{ $proofAnalytics['featured_count'] }}</strong> featured • 
                            @endif
                            @if($proofAnalytics['average_impact'])
                                <strong>{{ number_format($proofAnalytics['average_impact'], 1) }}%</strong> avg. impact
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Terms and Notes -->
        <div class="terms-notes">
            @if($invoice->terms_conditions)
            <div class="terms-section">
                <div class="terms-title">Terms & Conditions</div>
                <div class="terms-content">
                    {!! nl2br(e($invoice->terms_conditions)) !!}
                </div>
            </div>
            @endif

            @if($invoice->notes)
            <div class="notes-section">
                <div class="notes-title">Notes</div>
                <div class="notes-content">
                    {!! nl2br(e($invoice->notes)) !!}
                </div>
            </div>
            @endif

            @if($invoice->description)
            <div class="notes-section">
                <div class="notes-title">Description</div>
                <div class="notes-content">
                    {!! nl2br(e($invoice->description)) !!}
                </div>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-info">
                <span>{{ $invoice->company->name ?? 'Bina Group' }} - Invoice {{ $invoice->number }}</span>
                <span>Generated on @displayDateTime(now())</span>
            </div>
        </div>
    </div>
</body>
</html>