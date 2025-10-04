@php
    // Extract palette colors with type safety
    $palette = is_array($palette ?? []) ? ($palette ?? []) : [];
    $primaryBlue = is_string($palette['accent_color'] ?? null) ? $palette['accent_color'] : '#0b57d0';
    $primaryContrast = is_string($palette['accent_text_color'] ?? null) ? $palette['accent_text_color'] : '#ffffff';
    $textColor = is_string($palette['text_color'] ?? null) ? $palette['text_color'] : '#000000';
    $mutedColor = is_string($palette['muted_text_color'] ?? null) ? $palette['muted_text_color'] : '#4b5563';
    $headingColor = is_string($palette['heading_color'] ?? null) ? $palette['heading_color'] : '#000000';
    $borderColor = is_string($palette['border_color'] ?? null) ? $palette['border_color'] : '#d0d5dd';
    $tableHeaderBg = is_string($palette['table_header_background'] ?? null) ? $palette['table_header_background'] : '#0b57d0';
    $tableHeaderText = is_string($palette['table_header_text'] ?? null) ? $palette['table_header_text'] : '#ffffff';

    // Ensure sections and currency are properly typed
    $sections = is_array($sections ?? []) ? ($sections ?? []) : [];
    $currency = is_string($currency ?? 'RM') ? ($currency ?? 'RM') : 'RM';

    // Calculate financial totals
    $subtotal = $quotation->subtotal ?? $quotation->items->sum(fn($item) => $item->total_price ?? ($item->quantity * $item->unit_price));
    $discount = $quotation->discount_amount ?? 0;
    $tax = $quotation->tax_amount ?? 0;
    $total = $quotation->total ?? ($subtotal - $discount + $tax);

    // Currency formatter
    $format = fn($value) => trim(($currency ? $currency . ' ' : '') . number_format((float) $value, 2));

    // Payment instructions - use quotation field directly (plain text)
    $paymentText = trim($quotation->payment_instructions ?? '');

    // Simple fallback if quotation doesn't have payment instructions
    if ($paymentText === '') {
        $paymentText = "Please make payment to:\n\n" .
                       "Company: " . ($quotation->company->name ?? 'Company Name') . "\n" .
                       "Bank: (Bank details to be provided)\n" .
                       "Account: (Account number to be provided)\n\n" .
                       "Please include quotation number in payment reference.";
    }

    // Logo path - check quotation-specific logo first, then company default logo
    $logoPath = null;
    if ($sections['show_company_logo'] ?? true) {
        // Priority 1: Quotation-specific logo from logo bank
        $logoFile = null;
        if ($quotation->companyLogo ?? null) {
            $logoFile = $quotation->companyLogo->file_path;
        }

        // Priority 2: Company default logo from logo bank
        if (!$logoFile && $quotation->company?->defaultLogo()) {
            $logoFile = $quotation->company->defaultLogo()->file_path;
        }

        // Priority 3: Legacy company logo_path
        if (!$logoFile) {
            $logoFile = $quotation->company?->logo_path ?? $quotation->company?->logo ?? null;
        }

        if (!empty($logoFile)) {
            // Use storage path directly (not public/storage symlink)
            $logoFile = ltrim($logoFile, '/');

            // Remove 'storage/' prefix if present
            if (str_starts_with($logoFile, 'storage/')) {
                $logoFile = substr($logoFile, 8); // Remove 'storage/' prefix
            }

            $path = storage_path('app/public/' . $logoFile);

            if (file_exists($path)) {
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'svg' => 'image/svg+xml',
                ];
                $mimeType = $mimeTypes[$extension] ?? 'image/png';

                try {
                    $logoData = base64_encode(file_get_contents($path));
                    $logoPath = 'data:' . $mimeType . ';base64,' . $logoData;
                } catch (\Throwable $exception) {
                    \Log::warning('Quotation PDF Logo Encoding Failed', [
                        'path' => $path,
                        'exception' => $exception->getMessage(),
                    ]);
                }
            }
        }
    }

    // Bill address
    $billAddress = array_filter([
        $quotation->customer_address,
        trim(collect([$quotation->customer_postal_code, $quotation->customer_city])->filter()->implode(' ')),
        $quotation->customer_state,
    ]);

    // Sales Rep Info
    $salesRep = $quotation->createdBy ?? $quotation->assignedTo;
    $salesRepName = $salesRep?->name ?? 'Sales Representative';
    $salesRepTitle = 'Sales Representative';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quotation {{ $quotation->number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: {{ $textColor }};
        }
        .page { width: 100%; padding: 20px; }

        /* Header */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid {{ $borderColor }};
            padding-bottom: 15px;
        }
        .header-left, .header-right {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        .header-right { text-align: right; }

        .logo { max-width: 180px; max-height: 80px; margin-bottom: 10px; }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: {{ $primaryBlue }};
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 9pt;
            color: {{ $mutedColor }};
            line-height: 1.6;
        }

        .doc-title {
            font-size: 24pt;
            font-weight: bold;
            color: {{ $headingColor }};
            margin-bottom: 8px;
        }
        .doc-number {
            font-size: 11pt;
            color: {{ $mutedColor }};
            margin-bottom: 3px;
        }
        .doc-date {
            font-size: 9pt;
            color: {{ $mutedColor }};
        }

        /* Customer Section */
        .customer-section {
            margin-bottom: 25px;
            background: #f9fafb;
            padding: 12px;
            border-radius: 4px;
        }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: {{ $headingColor }};
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .customer-info {
            font-size: 10pt;
            line-height: 1.6;
        }
        .customer-name { font-weight: bold; font-size: 11pt; }

        /* Items Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        thead th {
            background: {{ $tableHeaderBg }};
            color: {{ $tableHeaderText }};
            padding: 10px 8px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid {{ $borderColor }};
        }
        thead th.text-right { text-align: right; }
        thead th.text-center { text-align: center; }

        tbody td {
            padding: 8px;
            border: 1px solid {{ $borderColor }};
            font-size: 9pt;
        }
        tbody td.text-right { text-align: right; }
        tbody td.text-center { text-align: center; }
        tbody tr:nth-child(even) { background: #f9fafb; }

        /* Totals */
        .totals-table {
            margin-left: auto;
            width: 300px;
            margin-bottom: 25px;
        }
        .totals-table td {
            padding: 6px 10px;
            border: none;
            font-size: 10pt;
        }
        .totals-table tr.subtotal td { border-top: 1px solid {{ $borderColor }}; }
        .totals-table tr.total td {
            font-weight: bold;
            font-size: 12pt;
            background: {{ $tableHeaderBg }};
            color: {{ $tableHeaderText }};
            border-top: 2px solid {{ $borderColor }};
        }
        .totals-label { text-align: right; width: 60%; }
        .totals-amount { text-align: right; width: 40%; font-weight: 600; }

        /* Footer sections */
        .footer-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .footer-title {
            font-size: 10pt;
            font-weight: bold;
            color: {{ $headingColor }};
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid {{ $borderColor }};
        }
        .footer-content {
            font-size: 9pt;
            line-height: 1.6;
            white-space: pre-line;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100pt;
            font-weight: bold;
            color: rgba(239, 68, 68, 0.08);
            z-index: -1;
            pointer-events: none;
        }

        /* Signatures */
        .signatures {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            padding-top: 50px;
        }
        .signature-line {
            border-top: 1px solid {{ $borderColor }};
            margin: 0 auto;
            width: 80%;
            padding-top: 5px;
            font-size: 9pt;
            font-weight: 600;
        }
        .signature-title {
            font-size: 8pt;
            color: {{ $mutedColor }};
        }
    </style>
</head>
<body>
    <div class="page">
        @if($quotation->status === 'DRAFT')
            <div class="watermark">DRAFT</div>
        @endif

        <!-- Header -->
        <div class="header">
            <div class="header-left">
                @if($logoPath)
                    <img src="{{ $logoPath }}" class="logo" alt="Company Logo">
                @endif
                <div class="company-name">{{ $quotation->company->name ?? 'Company Name' }}</div>
                <div class="company-details">
                    @if($quotation->company->address ?? null)
                        {{ $quotation->company->address }}<br>
                    @endif
                    @if($quotation->company->phone ?? null)
                        Tel: {{ $quotation->company->phone }}<br>
                    @endif
                    @if($quotation->company->email ?? null)
                        Email: {{ $quotation->company->email }}
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="doc-title">QUOTATION</div>
                <div class="doc-number">{{ $quotation->number }}</div>
                <div class="doc-date">
                    Date: {{ $quotation->quotation_date?->format('d M Y') ?? date('d M Y') }}<br>
                    Valid Until: {{ $quotation->valid_until?->format('d M Y') ?? 'N/A' }}
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-section">
            <div class="section-title">Quotation For:</div>
            <div class="customer-info">
                <div class="customer-name">{{ $quotation->customer_name }}</div>
                @if($quotation->customer_company)
                    <div>{{ $quotation->customer_company }}</div>
                @endif
                @foreach($billAddress as $line)
                    <div>{{ $line }}</div>
                @endforeach
                @if($quotation->customer_phone)
                    <div>Tel: {{ $quotation->customer_phone }}</div>
                @endif
                @if($quotation->customer_email)
                    <div>Email: {{ $quotation->customer_email }}</div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">No.</th>
                    <th style="width: 45%;">Description</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 10%;" class="text-center">Unit</th>
                    <th style="width: 15%;" class="text-right">Unit Price</th>
                    <th style="width: 15%;" class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->description }}</strong>
                        @if($item->item_code)
                            <br><small style="color: {{ $mutedColor }};">Code: {{ $item->item_code }}</small>
                        @endif
                        @if($item->specifications)
                            <br><small style="color: {{ $mutedColor }};">{{ $item->specifications }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-center">{{ $item->unit ?? 'Nos' }}</td>
                    <td class="text-right">{{ $format($item->unit_price) }}</td>
                    <td class="text-right">{{ $format($item->total_price ?? ($item->quantity * $item->unit_price)) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <table class="totals-table">
            <tr class="subtotal">
                <td class="totals-label">Subtotal:</td>
                <td class="totals-amount">{{ $format($subtotal) }}</td>
            </tr>
            @if($discount > 0)
            <tr>
                <td class="totals-label">Discount {{ $quotation->discount_percentage > 0 ? '(' . number_format($quotation->discount_percentage, 2) . '%)' : '' }}:</td>
                <td class="totals-amount">-{{ $format($discount) }}</td>
            </tr>
            @endif
            @if($tax > 0)
            <tr>
                <td class="totals-label">Tax {{ $quotation->tax_percentage > 0 ? '(' . number_format($quotation->tax_percentage, 2) . '%)' : '' }}:</td>
                <td class="totals-amount">{{ $format($tax) }}</td>
            </tr>
            @endif
            <tr class="total">
                <td class="totals-label">TOTAL:</td>
                <td class="totals-amount">{{ $format($total) }}</td>
            </tr>
        </table>

        <!-- Notes -->
        @if($quotation->notes)
        <div class="footer-section">
            <div class="footer-title">Notes</div>
            <div class="footer-content">{{ $quotation->notes }}</div>
        </div>
        @endif

        <!-- Terms & Conditions -->
        @if($quotation->terms_conditions)
        <div class="footer-section">
            <div class="footer-title">Terms & Conditions</div>
            <div class="footer-content">{{ $quotation->terms_conditions }}</div>
        </div>
        @endif

        <!-- Payment Instructions -->
        @if($sections['show_payment_instructions'] ?? true)
        <div class="footer-section">
            <div class="footer-title">Payment Instructions</div>
            <div class="footer-content">{{ $paymentText }}</div>
        </div>
        @endif

        <!-- Signatures -->
        @if($sections['show_signatures'] ?? true)
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">{{ $salesRepName }}</div>
                <div class="signature-title">{{ $salesRepTitle }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">{{ $quotation->company->name ?? 'Company' }}</div>
                <div class="signature-title">Authorized Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">{{ $quotation->customer_name }}</div>
                <div class="signature-title">Customer Acceptance</div>
            </div>
        </div>
        @endif
    </div>
</body>
</html>
