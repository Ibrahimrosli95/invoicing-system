@php
    $palette = is_array($palette ?? []) ? $palette : [];
    $primaryBlue = is_string($palette['accent_color'] ?? null) ? $palette['accent_color'] : '#0b57d0';
    $primaryContrast = is_string($palette['accent_text_color'] ?? null) ? $palette['accent_text_color'] : '#ffffff';
    $textColor = is_string($palette['text_color'] ?? null) ? $palette['text_color'] : '#000000';
    $mutedColor = is_string($palette['muted_text_color'] ?? null) ? $palette['muted_text_color'] : '#4b5563';
    $headingColor = is_string($palette['heading_color'] ?? null) ? $palette['heading_color'] : '#000000';
    $borderColor = is_string($palette['border_color'] ?? null) ? $palette['border_color'] : '#d0d5dd';
    $tableHeaderBg = is_string($palette['table_header_background'] ?? null) ? $palette['table_header_background'] : '#0b57d0';
    $tableHeaderText = is_string($palette['table_header_text'] ?? null) ? $palette['table_header_text'] : '#ffffff';

    $settings = is_array($settings ?? []) ? $settings : [];

    $sectionDefaults = [
        'show_company_logo' => true,
        'show_payment_instructions' => true,
        'show_signatures' => true,
        'show_company_signature' => false,
        'show_customer_signature' => false,
    ];

    $sections = array_merge($sectionDefaults, is_array($sections ?? []) ? $sections : []);

    $currency = is_string($currency ?? 'RM') ? $currency : 'RM';

    $subtotal = $quotation->subtotal ?? $quotation->items->sum(fn($item) => $item->total_price ?? ($item->quantity * $item->unit_price));
    $discount = $quotation->discount_amount ?? 0;
    $tax = $quotation->tax_amount ?? 0;
    $total = $quotation->total ?? ($subtotal - $discount + $tax);

    $format = fn($value) => trim(($currency ? $currency . ' ' : '') . number_format((float) $value, 2));

    $paymentText = trim($quotation->payment_instructions ?? '');
    if ($paymentText === '') {
        $paymentText = "Please make payment to:\n\n" .
                       "Company: " . ($quotation->company->name ?? 'Company Name') . "\n" .
                       "Bank: (Bank details to be provided)\n" .
                       "Account: (Account number to be provided)\n\n" .
                       "Please include quotation number in payment reference.";
    }

    $logoPath = null;
    if (!empty($sections['show_company_logo'])) {
        $logoFile = null;

        if ($quotation->company && method_exists($quotation->company, 'defaultLogo')) {
            $defaultLogo = $quotation->company->defaultLogo();
            if ($defaultLogo && $defaultLogo->file_path) {
                $logoFile = $defaultLogo->file_path;
            }
        }

        if (!$logoFile) {
            $logoFile = $quotation->company->logo_path ?? $quotation->company->logo ?? ($settings['company_logo'] ?? null);
        }

        if ($logoFile) {
            $logoFile = ltrim($logoFile, '/');
            if (str_starts_with($logoFile, 'storage/')) {
                $logoFile = substr($logoFile, 8);
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
                    $logoPath = null;
                }
            }
        }
    }

    $billAddress = array_filter([
        $quotation->customer_address,
        trim(collect([$quotation->customer_postal_code, $quotation->customer_city])->filter()->implode(' ')),
        $quotation->customer_state,
    ]);

    $salesRep = $quotation->createdBy ?? $quotation->assignedTo;
    $salesRepName = $salesRep?->signature_name ?: ($salesRep?->name ?? 'Sales Representative');
    $salesRepTitle = $salesRep?->signature_title ?: 'Sales Representative';
    $salesRepSignature = null;

    if ($salesRep?->signature_path) {
        $signatureFile = ltrim($salesRep->signature_path, '/');
        if (str_starts_with($signatureFile, 'storage/')) {
            $signatureFile = substr($signatureFile, 8);
        }
        $sigPath = storage_path('app/public/' . $signatureFile);
        if (file_exists($sigPath)) {
            $extension = strtolower(pathinfo($sigPath, PATHINFO_EXTENSION));
            $mimeType = $extension === 'png' ? 'image/png' : 'image/jpeg';
            try {
                $signatureData = base64_encode(file_get_contents($sigPath));
                $salesRepSignature = 'data:' . $mimeType . ';base64,' . $signatureData;
            } catch (\Throwable $exception) {
                $salesRepSignature = null;
            }
        }
    }

    $companySignatureConfig = $settings['company_signature'] ?? [];
    $companySignatoryName = $companySignatureConfig['name'] ?? '';
    $companySignatoryTitle = $companySignatureConfig['title'] ?? '';
    $companySignature = null;

    if (!empty($companySignatureConfig['image_path'])) {
        $companySignatureFile = ltrim($companySignatureConfig['image_path'], '/');
        if (str_starts_with($companySignatureFile, 'storage/')) {
            $companySignatureFile = substr($companySignatureFile, 8);
        }
        $companySigPath = storage_path('app/public/' . $companySignatureFile);
        if (file_exists($companySigPath)) {
            $extension = strtolower(pathinfo($companySigPath, PATHINFO_EXTENSION));
            $mimeType = $extension === 'png' ? 'image/png' : 'image/jpeg';
            try {
                $companySignatureData = base64_encode(file_get_contents($companySigPath));
                $companySignature = 'data:' . $mimeType . ';base64,' . $companySignatureData;
            } catch (\Throwable $exception) {
                $companySignature = null;
            }
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation {{ $quotation->number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
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
            padding: 0;
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
            line-height: 1.4;
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

        .card {
            border: 1px solid {{ $borderColor }};
            border-radius: 4px;
            padding: 8px 10px;
            background: #fafafa;
        }

        .info-row td {
            vertical-align: top;
            padding: 0;
        }

        .bill-table td {
            padding: 2px 0;
            line-height: 1.4;
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
            width: 100%;
        }

        .items-table thead th {
            background: {{ $tableHeaderBg }};
            color: {{ $tableHeaderText }};
            padding: 8px;
            font-weight: 600;
            text-align: left;
            border: 1px solid {{ $tableHeaderBg }};
        }

        .items-table tbody td {
            padding: 6px 8px;
            border: 1px solid {{ $borderColor }};
        }

        .summary-table {
            margin-top: 8mm;
            width: 100%;
        }

        .summary-table td {
            vertical-align: top;
            padding: 0;
        }

        .payment-block {
            padding-right: 18mm;
        }

        .payment-text {
            border: 1px solid {{ $borderColor }};
            padding: 8px 10px;
            border-radius: 4px;
            white-space: normal;
            color: {{ $textColor }};
            background: #fafafa;
            font-size: 11px;
            line-height: 1.3;
            margin-right: 6mm;
        }

        .totals-table {
            width: 100%;
            font-size: 12px;
        }

        .totals-table td {
            padding: 3px 0;
        }

        .totals-table td:first-child {
            text-align: left;
            color: {{ $mutedColor }};
            padding-right: 12px;
        }

        .totals-table td:last-child {
            text-align: right;
            width: 110px;
            font-weight: 500;
        }

        .totals-table .total-row td {
            font-weight: 700;
            padding-top: 4px;
            border-top: 1px solid {{ $borderColor }};
            color: {{ $headingColor }};
        }

        .signature-table {
            width: 100%;
            margin-top: 12mm;
            font-size: 12px;
        }

        .signature-table td {
            width: 33.33%;
            text-align: center;
            padding-top: 12mm;
            vertical-align: top;
        }

        .signature-line {
            border-top: 1px solid {{ $borderColor }};
            padding-top: 4px;
            width: 75%;
            margin: 0 auto;
            font-weight: 600;
            color: {{ $mutedColor }};
        }

        .signature-name {
            margin-top: 2px;
            color: {{ $textColor }};
        }

        .footer {
            margin-top: 10mm;
            text-align: center;
            font-size: 10px;
            color: {{ $mutedColor }};
            border-top: 1px solid {{ $borderColor }};
            padding-top: 4mm;
        }

        .col-sl { width: 8%; text-align: center; }
        .col-description { width: auto; text-align: left; }
        .col-unit { width: 12%; text-align: center; }
        .col-quantity { width: 10%; text-align: center; }
        .col-rate { width: 16%; text-align: right; }
        .col-amount { width: 16%; text-align: right; }
    </style>
</head>
<body>
<div class="page">
    <h1 class="title">QUOTATION</h1>

    <table class="header-table">
        <tr>
            <td style="width:70%;">
                <div class="company-name">{{ $quotation->company->name ?? 'Company Name' }}</div>
                @foreach([
                    $quotation->company->address,
                    trim(collect([$quotation->company->postal_code, $quotation->company->city])->filter()->implode(' ')),
                    $quotation->company->state,
                    'Email: ' . ($quotation->company->email ?? '-'),
                    'Mobile: ' . ($quotation->company->phone ?? '-'),
                ] as $line)
                    @if(!empty(trim($line, ' -')))
                        <div class="company-line">{{ $line }}</div>
                    @endif
                @endforeach
            </td>
            <td style="width:30%; text-align: right;">
                @if(!empty($sections['show_company_logo']) && $logoPath)
                    <img src="{{ $logoPath }}" alt="Company Logo" class="logo">
                @endif
            </td>
        </tr>
    </table>

    <hr class="separator">

    <table class="info-row">
        <tr>
            <td style="width:50%; padding-right: 10mm;">
                <div class="section-label">Quote To</div>
                <div class="card">
                    <table class="bill-table">
                        <tr><td>{{ $quotation->customer_name ?? 'Customer Name' }}</td></tr>
                        @if($quotation->customer_company)
                            <tr><td>{{ $quotation->customer_company }}</td></tr>
                        @endif
                        @foreach($billAddress as $line)
                            <tr><td>{{ $line }}</td></tr>
                        @endforeach
                        @if($quotation->customer_email)
                            <tr><td>Email: {{ $quotation->customer_email }}</td></tr>
                        @endif
                        @if($quotation->customer_phone)
                            <tr><td>Phone: {{ $quotation->customer_phone }}</td></tr>
                        @endif
                    </table>
                </div>
            </td>
            <td style="width:50%;">
                <div class="section-label">Quotation Info</div>
                <div class="card">
                    <table class="meta-table">
                        <tr>
                            <td>Quotation No :</td>
                            <td>{{ $quotation->number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Quotation Date :</td>
                            <td>{{ optional($quotation->quotation_date)->format('d M, Y') ?? now()->format('d M, Y') }}</td>
                        </tr>
                        <tr>
                            <td>Valid Until :</td>
                            <td>{{ optional($quotation->valid_until)->format('d M, Y') ?? '-' }}</td>
                        </tr>
                        @if($quotation->reference_number)
                            <tr>
                                <td>Reference :</td>
                                <td>{{ $quotation->reference_number }}</td>
                            </tr>
                        @endif
                        @if($quotation->customerSegment)
                            <tr>
                                <td>Segment :</td>
                                <td>{{ $quotation->customerSegment->name }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td>Prepared By :</td>
                            <td>{{ $salesRepName }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    @if($quotation->title)
        <div style="margin-top: 6mm;">
            <div class="section-label">Subject</div>
            <div class="card" style="background:#fff; color: {{ $headingColor }}; font-weight:600;">
                {{ $quotation->title }}
            </div>
        </div>
    @endif

    @if($quotation->items->isNotEmpty())
        <div class="section-label" style="margin-top: 10mm;">Items &amp; Services</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-sl">No.</th>
                    <th class="col-description">Description</th>
                    <th class="col-unit">Unit</th>
                    <th class="col-quantity">Qty</th>
                    <th class="col-rate">Unit Price</th>
                    <th class="col-amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotation->items as $index => $item)
                    <tr>
                        <td class="col-sl">{{ $index + 1 }}</td>
                        <td class="col-description">
                            {{ $item->description ?? 'Item' }}
                            @if($item->specifications)
                                <div style="font-size: 10px; color: {{ $mutedColor }}; margin-top: 2px;">{{ $item->specifications }}</div>
                            @endif
                        </td>
                        <td class="col-unit">{{ $item->unit ?? 'Unit' }}</td>
                        <td class="col-quantity">{{ rtrim(rtrim(number_format((float) ($item->quantity ?? 0), 2), '0'), '.') }}</td>
                        <td class="col-rate">{{ $format($item->unit_price ?? 0) }}</td>
                        <td class="col-amount">{{ $format($item->total_price ?? (($item->quantity ?? 0) * ($item->unit_price ?? 0))) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <table class="summary-table">
        <tr>
            @if(!empty($sections['show_payment_instructions']))
                <td class="payment-block" style="width:50%;">
                    <div class="section-label">Payment Instructions</div>
                    <div class="payment-text">{!! nl2br(e($paymentText)) !!}</div>
                </td>
            @endif
            <td style="width:{{ !empty($sections['show_payment_instructions']) ? '50%' : '100%' }};">
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
                </table>
            </td>
        </tr>
    </table>

    @if($quotation->notes)
        <div style="margin-top: 6mm; page-break-inside: avoid;">
            <div class="section-label">Notes</div>
            <div style="font-size: 9pt; line-height: 1.4; color: {{ $textColor }};">
                {!! nl2br(e($quotation->notes)) !!}
            </div>
        </div>
    @endif

    @if($quotation->terms_conditions)
        <div style="margin-top: 6mm; page-break-inside: avoid;">
            <div class="section-label">Terms &amp; Conditions</div>
            <div style="font-size: 9pt; line-height: 1.4; color: {{ $textColor }};">
                {!! nl2br(e($quotation->terms_conditions)) !!}
            </div>
        </div>
    @endif

    @if(!empty($sections['show_signatures']))
        <table class="signature-table">
            <tr>
                <td>
                    @if($salesRepSignature)
                        <img src="{{ $salesRepSignature }}" alt="Sales Representative Signature" style="max-height:40px; margin-bottom:4mm;">
                    @endif
                    <div class="signature-line">{{ $salesRepTitle }}</div>
                    <div class="signature-name">{{ $salesRepName }}</div>
                </td>
                <td>
                    @if(!empty($sections['show_company_signature']))
                        @if($companySignature)
                            <img src="{{ $companySignature }}" alt="Company Signature" style="max-height:40px; margin-bottom:4mm;">
                        @endif
                        <div class="signature-line">{{ $companySignatoryTitle ?: 'Authorized Signatory' }}</div>
                        <div class="signature-name">{{ $companySignatoryName ?: ($quotation->company->name ?? 'Company Representative') }}</div>
                    @endif
                </td>
                <td>
                    @if(!empty($sections['show_customer_signature']))
                        <div class="signature-line">Customer Acceptance</div>
                        <div class="signature-name">{{ $quotation->customer_name ?? 'Customer' }}</div>
                    @endif
                </td>
            </tr>
        </table>
    @endif

    <div class="footer">
        {{ $quotation->company->name ?? 'Company' }} - Quotation {{ $quotation->number }} - Generated on {{ now()->format('d M Y, H:i') }}
    </div>
</div>
</body>
</html>
