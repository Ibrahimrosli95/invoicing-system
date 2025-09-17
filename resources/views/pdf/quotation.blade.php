<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $quotation->number }}</title>
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
        
        /* Watermark for draft quotations */
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
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        
        .company-address {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
        }
        
        .quotation-info {
            text-align: right;
            flex: 1;
        }
        
        .quotation-title {
            font-size: 28px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .quotation-details {
            font-size: 11px;
            color: #6b7280;
        }
        
        .quotation-details div {
            margin-bottom: 5px;
        }
        
        /* Customer Information */
        .customer-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .customer-info {
            background: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #2563eb;
        }
        
        .customer-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .customer-details {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6;
        }
        
        /* Items Table */
        .items-section {
            margin-bottom: 30px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th {
            background: #2563eb;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .items-table th:first-child {
            border-radius: 4px 0 0 0;
        }
        
        .items-table th:last-child {
            border-radius: 0 4px 0 0;
            text-align: right;
        }
        
        .items-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        
        .items-table tr:nth-child(even) {
            background: #f9fafb;
        }
        
        .items-table tr:hover {
            background: #f3f4f6;
        }
        
        .item-description {
            font-weight: 500;
            margin-bottom: 3px;
        }
        
        .item-specifications {
            font-size: 10px;
            color: #6b7280;
            font-style: italic;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* Sections (for service quotations) */
        .section-header {
            background: #1f2937;
            color: white;
            padding: 10px;
            font-weight: bold;
            font-size: 12px;
            margin-top: 20px;
            border-radius: 4px 4px 0 0;
        }
        
        .section-items {
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 4px 4px;
        }
        
        .section-total {
            background: #f3f4f6;
            padding: 8px 10px;
            font-weight: 600;
            text-align: right;
            border-top: 2px solid #d1d5db;
        }
        
        /* Financial Summary */
        .financial-summary {
            margin-top: 30px;
            margin-left: auto;
            width: 300px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .summary-table .label {
            font-weight: 500;
            color: #4b5563;
        }
        
        .summary-table .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }
        
        .summary-table .total-row {
            background: #2563eb;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        
        .summary-table .total-row td {
            border-bottom: none;
            padding: 12px 10px;
        }
        
        /* Terms and Notes */
        .terms-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .terms-content {
            background: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
            line-height: 1.6;
        }
        
        .notes-section {
            margin-top: 20px;
        }
        
        .notes-content {
            padding: 15px;
            border-left: 4px solid #10b981;
            background: #ecfdf5;
            font-size: 11px;
            line-height: 1.6;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 15mm;
            left: 15mm;
            right: 15mm;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            font-size: 10px;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
        }
        
        .footer-left {
            flex: 1;
        }
        
        .footer-right {
            text-align: right;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-sent {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .status-viewed {
            background: #e0e7ff;
            color: #3730a3;
        }
        
        .status-accepted {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Utilities */
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .mb-4 { margin-bottom: 16px; }
        .font-bold { font-weight: bold; }
        .text-sm { font-size: 11px; }
        .text-xs { font-size: 10px; }
        
        /* Print specific styles */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .page {
                margin: 0;
                box-shadow: none;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <!-- Watermark for draft quotations -->
    @if($quotation->status === 'DRAFT')
        <div class="watermark">DRAFT</div>
    @endif

    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <div class="company-name">{{ $quotation->company->name ?? 'Bina Group' }}</div>
                <div class="company-address">
                    @if($quotation->company->address)
                        {!! nl2br(e($quotation->company->address)) !!}<br>
                    @endif
                    @if($quotation->company->phone)
                        Phone: {{ $quotation->company->phone }}<br>
                    @endif
                    @if($quotation->company->email)
                        Email: {{ $quotation->company->email }}
                    @endif
                </div>
            </div>
            <div class="quotation-info">
                <div class="quotation-title">QUOTATION</div>
                <div class="quotation-details">
                    <div><strong>Number:</strong> {{ $quotation->number }}</div>
                    <div><strong>Date:</strong> {{ $quotation->created_at->format('d M Y') }}</div>
                    @if($quotation->valid_until)
                        <div><strong>Valid Until:</strong> {{ $quotation->valid_until->format('d M Y') }}</div>
                    @endif
                    <div>
                        <span class="status-badge status-{{ strtolower($quotation->status) }}">
                            {{ $quotation->status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-section">
            <div class="section-title">Quote To</div>
            <div class="customer-info">
                <div class="customer-name">
                    {{ $quotation->customer_name }}
                    @if($quotation->customerSegment)
                        <span style="font-size: 10px; background: {{ $quotation->customerSegment->color ?? '#6B7280' }}; color: white; padding: 2px 6px; border-radius: 3px; margin-left: 8px;">
                            {{ $quotation->customerSegment->name }}
                        </span>
                    @endif
                </div>
                <div class="customer-details">
                    @if($quotation->customer_email)
                        Email: {{ $quotation->customer_email }}<br>
                    @endif
                    @if($quotation->customer_phone)
                        Phone: {{ $quotation->customer_phone }}<br>
                    @endif
                    @if($quotation->customer_address)
                        Address: {!! nl2br(e($quotation->customer_address)) !!}
                    @endif
                    @if($quotation->customerSegment && $quotation->customerSegment->default_discount_percentage > 0)
                        <br><strong>Customer Segment:</strong> {{ $quotation->customerSegment->name }} 
                        ({{ $quotation->customerSegment->default_discount_percentage }}% discount applied)
                    @endif
                </div>
            </div>
        </div>

        <!-- Items/Sections -->
        <div class="items-section">
            <div class="section-title">Items & Services</div>

            @if($quotation->sections->isNotEmpty())
                <!-- Service Quotation with Sections -->
                @foreach($quotation->sections as $section)
                    <div class="section-header">
                        {{ $section->name }}
                        @if($section->description)
                            <span class="text-sm"> - {{ $section->description }}</span>
                        @endif
                    </div>
                    <div class="section-items">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Description</th>
                                    <th style="width: 10%;">Unit</th>
                                    <th style="width: 15%;" class="text-center">Quantity</th>
                                    <th style="width: 15%;" class="text-right">Unit Price</th>
                                    <th style="width: 15%;" class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($section->items as $item)
                                    <tr>
                                        <td>
                                            <div class="item-description">{{ $item->description }}</div>
                                            @if($item->specifications)
                                                <div class="item-specifications">{{ $item->specifications }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $item->unit ?? 'Nos' }}</td>
                                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-right">RM {{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-right">RM {{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="section-total">
                            Section Total: RM {{ number_format($section->total, 2) }}
                        </div>
                    </div>
                @endforeach
            @else
                <!-- Simple Product Quotation -->
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 50%;">Description</th>
                            <th style="width: 10%;">Unit</th>
                            <th style="width: 15%;" class="text-center">Quantity</th>
                            <th style="width: 15%;" class="text-right">Unit Price</th>
                            <th style="width: 15%;" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($quotation->items as $item)
                            <tr>
                                <td>
                                    <div class="item-description">{{ $item->description }}</div>
                                    @if($item->specifications)
                                        <div class="item-specifications">{{ $item->specifications }}</div>
                                    @endif
                                </td>
                                <td>{{ $item->unit ?? 'Nos' }}</td>
                                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-right">RM {{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-right">RM {{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Financial Summary -->
        <div class="financial-summary">
            <table class="summary-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="amount">RM {{ number_format($quotation->subtotal, 2) }}</td>
                </tr>
                @if($quotation->discount_percentage > 0)
                    <tr>
                        <td class="label">Discount ({{ $quotation->discount_percentage }}%):</td>
                        <td class="amount">- RM {{ number_format($quotation->discount_amount, 2) }}</td>
                    </tr>
                @endif
                @if($quotation->tax_percentage > 0)
                    <tr>
                        <td class="label">Tax ({{ $quotation->tax_percentage }}%):</td>
                        <td class="amount">RM {{ number_format($quotation->tax_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL:</td>
                    <td class="amount">RM {{ number_format($quotation->total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Terms and Conditions -->
        @if($quotation->terms)
            <div class="terms-section">
                <div class="section-title">Terms & Conditions</div>
                <div class="terms-content">
                    {!! nl2br(e($quotation->terms)) !!}
                </div>
            </div>
        @endif

        <!-- Notes -->
        @if($quotation->notes)
            <div class="notes-section">
                <div class="section-title">Notes</div>
                <div class="notes-content">
                    {!! nl2br(e($quotation->notes)) !!}
                </div>
            </div>
        @endif

        <!-- Pricing Information -->
        @if($quotation->customerSegment)
            <div style="margin-top: 20px; padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                <div style="font-size: 11px; color: #64748b; margin-bottom: 5px;">
                    <strong>Pricing Information:</strong>
                </div>
                <div style="font-size: 10px; color: #475569;">
                    • Customer Segment: <strong>{{ $quotation->customerSegment->name }}</strong>
                    @if($quotation->customerSegment->default_discount_percentage > 0)
                        ({{ $quotation->customerSegment->default_discount_percentage }}% segment discount)
                    @endif
                </div>
                <div style="font-size: 10px; color: #475569; margin-top: 3px;">
                    • Prices may include quantity-based tier pricing where applicable
                </div>
                <div style="font-size: 10px; color: #475569; margin-top: 3px;">
                    • All pricing is subject to the terms and conditions stated above
                </div>
            </div>
        @endif

        <!-- Social Proof Section -->
        @php
            $pdfService = app(\App\Services\PDFService::class);
            $proofs = $pdfService->getProofsForPDF($quotation, 'quotation');
        @endphp

        @if($proofs->isNotEmpty())
            <div style="margin-top: 30px; page-break-inside: avoid;">
                <div class="section-title">{{ $pdfService->getProofSectionTitle('quotation') }}</div>
                
                <div style="background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <!-- Proof Grid -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        @foreach($proofs->take(4) as $proof)
                            <div style="background: white; padding: 12px; border-radius: 6px; border: 1px solid #e5e7eb;">
                                <!-- Proof Header -->
                                <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                    @if($proof->is_featured)
                                        <span style="background: #fbbf24; color: white; font-size: 8px; padding: 2px 6px; border-radius: 10px; margin-right: 6px; font-weight: 600;">★</span>
                                    @endif
                                    <span style="background: {{ $proof->getCategoryColor() }}; color: white; font-size: 8px; padding: 2px 6px; border-radius: 10px; font-weight: 600; text-transform: uppercase;">
                                        {{ $proof->type_label }}
                                    </span>
                                </div>

                                <!-- Proof Title -->
                                <div style="font-size: 11px; font-weight: 600; color: #1f2937; margin-bottom: 4px; line-height: 1.2;">
                                    {{ Str::limit($proof->title, 45) }}
                                </div>

                                <!-- Proof Description -->
                                @if($proof->description)
                                    <div style="font-size: 9px; color: #6b7280; margin-bottom: 8px; line-height: 1.3;">
                                        {{ Str::limit($proof->description, 80) }}
                                    </div>
                                @endif

                                <!-- Proof Assets -->
                                @php
                                    $displayAssets = $pdfService->filterAssetsForPDF($proof, 2);
                                @endphp
                                
                                @if($displayAssets->isNotEmpty())
                                    <div style="display: flex; gap: 4px; margin-bottom: 6px;">
                                        @foreach($displayAssets as $asset)
                                            @if($asset->isImage())
                                                <div style="width: 24px; height: 24px; border-radius: 3px; overflow: hidden; border: 1px solid #e5e7eb;">
                                                    <img src="{{ asset('storage/' . ($asset->thumbnail_path ?: $asset->file_path)) }}" 
                                                         alt="{{ $asset->alt_text }}"
                                                         style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                            @endif
                                        @endforeach
                                        @if($proof->assets->count() > 2)
                                            <div style="width: 24px; height: 24px; border-radius: 3px; background: #f3f4f6; border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 8px; color: #6b7280; font-weight: 600;">
                                                +{{ $proof->assets->count() - 2 }}
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- Proof Stats -->
                                <div style="display: flex; justify-content: between; align-items: center; font-size: 8px; color: #6b7280;">
                                    @if($proof->views_count > 0)
                                        <span>{{ number_format($proof->views_count) }} views</span>
                                    @endif
                                    @if($proof->conversion_impact)
                                        <span style="margin-left: auto; background: #10b981; color: white; padding: 1px 4px; border-radius: 8px; font-weight: 600;">
                                            {{ $proof->conversion_impact }}% impact
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Additional Proofs Summary -->
                    @if($proofs->count() > 4)
                        <div style="text-align: center; padding: 8px; background: white; border-radius: 4px; border: 1px solid #e5e7eb;">
                            <div style="font-size: 10px; color: #6b7280;">
                                + {{ $proofs->count() - 4 }} more {{ Str::plural('credential', $proofs->count() - 4) }} available
                            </div>
                        </div>
                    @endif

                    <!-- Proof Summary Statistics -->
                    @php
                        $proofAnalytics = $pdfService->getProofAnalytics($proofs);
                    @endphp
                    
                    <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #e5e7eb;">
                        <div style="display: flex; justify-content: space-between; font-size: 9px; color: #6b7280;">
                            <span><strong>{{ $proofAnalytics['total_proofs'] }}</strong> {{ Str::plural('proof', $proofAnalytics['total_proofs']) }}</span>
                            @if($proofAnalytics['total_views'] > 0)
                                <span><strong>{{ number_format($proofAnalytics['total_views']) }}</strong> total views</span>
                            @endif
                            @if($proofAnalytics['featured_count'] > 0)
                                <span><strong>{{ $proofAnalytics['featured_count'] }}</strong> featured</span>
                            @endif
                            @if($proofAnalytics['average_impact'])
                                <span><strong>{{ number_format($proofAnalytics['average_impact'], 1) }}%</strong> avg. impact</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-left">
            <div>{{ $quotation->company->name ?? 'Bina Group' }}</div>
            <div>Generated on {{ now()->format('d M Y \a\t H:i') }}</div>
        </div>
        <div class="footer-right">
            <div>Quotation {{ $quotation->number }}</div>
            <div>Page 1 of 1</div>
        </div>
    </div>
</body>
</html>