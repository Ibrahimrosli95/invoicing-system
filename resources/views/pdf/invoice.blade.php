@php
    // Extract palette colors with type safety
    $palette = is_array($palette) ? $palette : [];
    $primaryBlue = is_string($palette['accent_color'] ?? null) ? $palette['accent_color'] : '#0b57d0';
    $primaryContrast = is_string($palette['accent_text_color'] ?? null) ? $palette['accent_text_color'] : '#ffffff';
    $textColor = is_string($palette['text_color'] ?? null) ? $palette['text_color'] : '#000000';
    $mutedColor = is_string($palette['muted_text_color'] ?? null) ? $palette['muted_text_color'] : '#4b5563';
    $headingColor = is_string($palette['heading_color'] ?? null) ? $palette['heading_color'] : '#000000';
    $borderColor = is_string($palette['border_color'] ?? null) ? $palette['border_color'] : '#d0d5dd';
    $tableHeaderBg = is_string($palette['table_header_background'] ?? null) ? $palette['table_header_background'] : '#0b57d0';
    $tableHeaderText = is_string($palette['table_header_text'] ?? null) ? $palette['table_header_text'] : '#ffffff';

    // Ensure sections and currency are properly typed
    $sections = is_array($sections) ? $sections : [];
    $currency = is_string($currency) ? $currency : 'RM';

    // Calculate financial totals
    $subtotal = $invoice->subtotal ?? $invoice->items->sum(fn($item) => $item->total_price ?? ($item->quantity * $item->unit_price));
    $discount = $invoice->discount_amount ?? 0;
    $tax = $invoice->tax_amount ?? 0;
    $total = $invoice->total ?? ($subtotal - $discount + $tax);
    $paid = $invoice->amount_paid ?? 0;
    $balance = max(0, $total - $paid);

    // Currency formatter
    $format = fn($value) => trim(($currency ? $currency . ' ' : '') . number_format((float) $value, 2));

    // Payment instructions fallback
    $paymentText = trim($invoice->payment_instructions ?? '');
    if ($paymentText === '') {
        // Safely extract payment instructions, ensuring they're strings
        $paymentInstructions = $settings['payment_instructions'] ?? [];
        if (!is_array($paymentInstructions)) {
            $paymentInstructions = [];
        }

        $holder = $paymentInstructions['account_holder'] ?? $invoice->company->name ?? '';
        $holder = is_string($holder) ? $holder : '';

        $bank = $paymentInstructions['bank_name'] ?? '';
        $bank = is_string($bank) ? $bank : '';

        $account = $paymentInstructions['account_number'] ?? '';
        $account = is_string($account) ? $account : '';

        $additionalInfo = $paymentInstructions['additional_info'] ?? 'Please include invoice number in payment reference.';
        $additionalInfo = is_string($additionalInfo) ? $additionalInfo : 'Please include invoice number in payment reference.';

        $lines = [];
        if ($holder) {
            $lines[] = 'Pay Cheque to ' . $holder;
        }
        if ($bank && $account) {
            $lines[] = 'Send to bank (' . $bank . ') ' . $account;
        } elseif ($bank) {
            $lines[] = 'Bank: ' . $bank;
        }
        $lines[] = $additionalInfo;
        $paymentText = implode("\n", array_filter($lines));
    }

    // Logo path - check invoice-specific logo first, then company default logo, then settings
    $logoPath = null;
    if ($sections['show_company_logo']) {
        // Priority 1: Invoice-specific logo from logo bank
        $logoFile = null;
        if ($invoice->companyLogo) {
            $logoFile = $invoice->companyLogo->file_path;
        }

        // Priority 2: Company default logo from logo bank
        if (!$logoFile && $invoice->company?->defaultLogo()) {
            $logoFile = $invoice->company->defaultLogo()->file_path;
        }

        // Priority 3: Legacy company logo_path
        if (!$logoFile) {
            $logoFile = $invoice->company?->logo_path ?? $invoice->company?->logo ?? $settings['company_logo'] ?? null;
        }

        // Debug logging - always log for troubleshooting
        \Log::info('Invoice PDF Logo Debug', [
            'invoice_id' => $invoice->id,
            'invoice_company_logo_id' => $invoice->company_logo_id,
            'invoice_company_logo_path' => $invoice->companyLogo?->file_path,
            'company_default_logo_path' => $invoice->company?->defaultLogo()?->file_path,
            'company_logo_path' => $invoice->company?->logo_path,
            'company_logo' => $invoice->company?->logo,
            'settings_logo' => $settings['company_logo'] ?? null,
            'final_logo_file' => $logoFile,
            'logo_file_empty' => empty($logoFile),
            'show_company_logo' => $sections['show_company_logo'],
        ]);

        if (!empty($logoFile)) {
            // Use storage path directly (not public/storage symlink)
            $logoFile = ltrim($logoFile, '/');

            // Remove 'storage/' prefix if present
            if (str_starts_with($logoFile, 'storage/')) {
                $logoFile = substr($logoFile, 8); // Remove 'storage/' prefix
            }

            $path = storage_path('app/public/' . $logoFile);

            // Debug logging for file path
            \Log::info('Invoice PDF Logo Path Check', [
                'final_logo_file' => $logoFile,
                'full_path' => $path,
                'file_exists' => file_exists($path),
                'public_path' => public_path(),
            ]);

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
                    \Log::warning('Invoice PDF Logo Encoding Failed', [
                        'path' => $path,
                        'exception' => $exception->getMessage(),
                    ]);
                }
            } else {
                \Log::warning('Invoice PDF Logo File Not Found', [
                    'path' => $path,
                    'checked_paths' => [
                        public_path($logoFile),
                        public_path('storage/' . $logoFile),
                        storage_path('app/public/' . str_replace('storage/', '', $logoFile)),
                    ]
                ]);
            }
        } else {
            \Log::warning('Invoice PDF No Logo Configured', [
                'invoice_id' => $invoice->id,
                'company_id' => $invoice->company_id,
            ]);
        }
    }

    // Bill address
    $billAddress = array_filter([
        $invoice->customer_address,
        trim(collect([$invoice->customer_postal_code, $invoice->customer_city])->filter()->implode(' ')),
        $invoice->customer_state,
    ]);

    // Sales Rep Signature (from user profile)
    $salesRep = $invoice->createdBy;
    $salesRepName = $salesRep?->signature_name ?: ($salesRep?->name ?? 'Sales Representative');
    $salesRepTitle = $salesRep?->signature_title ?: 'Sales Representative';
    $salesRepSignature = null;

    if ($salesRep?->signature_path) {
        $salesRepSigPath = public_path('storage/' . ltrim($salesRep->signature_path, '/'));
        if (file_exists($salesRepSigPath)) {
            $salesRepSignature = 'file://' . str_replace('\\', '/', $salesRepSigPath);
        }
    }

    // Company Signature (from invoice settings - optional)
    $companySignatureConfig = $settings['company_signature'] ?? [];
    $companySignatoryName = $companySignatureConfig['name'] ?? '';
    $companySignatoryTitle = $companySignatureConfig['title'] ?? '';
    $companySignature = null;

    if (!empty($companySignatureConfig['image_path'])) {
        $companySigPath = public_path('storage/' . ltrim($companySignatureConfig['image_path'], '/'));
        if (file_exists($companySigPath)) {
            $companySignature = 'file://' . str_replace('\\', '/', $companySigPath);
        }
    }

    // Column labels from settings
    $columnLabels = [];
    foreach ($columns as $col) {
        $columnLabels[$col['key']] = $col['label'];
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->number }}</title>
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
            white-space: pre-line;
            color: {{ $textColor }};
            background: #fafafa;
            font-size: 11px;
            line-height: 1.5;
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

        /* Column-specific widths */
        .col-sl { width: 8%; text-align: center; }
        .col-description { width: auto; text-align: left; }
        .col-quantity { width: 10%; text-align: center; }
        .col-rate { width: 18%; text-align: right; }
        .col-amount { width: 18%; text-align: right; }
    </style>
</head>
<body>
<div class="page">
    {{-- Centered Title --}}
    <h1 class="title">INVOICE</h1>

    {{-- Company Block + Logo --}}
    <table class="header-table">
        <tr>
            <td style="width:70%;">
                <div class="company-name">{{ $invoice->company->name ?? 'Company Name' }}</div>
                @foreach([
                    $invoice->company->address,
                    trim(collect([$invoice->company->postal_code, $invoice->company->city])->filter()->implode(' ')),
                    $invoice->company->state,
                    'Email: ' . ($invoice->company->email ?? '—'),
                    'Mobile: ' . ($invoice->company->phone ?? '—'),
                ] as $line)
                    @if(!empty(trim($line, ' -—')))
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

    {{-- Bill To + Invoice Info Cards --}}
    <table class="info-row">
        <tr>
            <td style="width:50%; padding-right:6mm;">
                <div class="section-label">Bill To</div>
                <div class="card">
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
                </div>
            </td>
            <td style="width:50%;">
                <div class="section-label">Invoice Info</div>
                <div class="card">
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
            </td>
        </tr>
    </table>

    {{-- Items Table with Blue Header --}}
    <table class="items-table">
        <thead>
            <tr>
                @foreach($columns as $col)
                    <th class="col-{{ $col['key'] }}">{{ $col['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    @foreach($columns as $col)
                        @if($col['key'] === 'sl')
                            <td class="col-sl">{{ $index + 1 }}</td>
                        @elseif($col['key'] === 'description')
                            <td class="col-description">{{ $item->description }}</td>
                        @elseif($col['key'] === 'quantity')
                            <td class="col-quantity">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                        @elseif($col['key'] === 'rate')
                            <td class="col-rate">{{ $format($item->unit_price) }}</td>
                        @elseif($col['key'] === 'amount')
                            <td class="col-amount">{{ $format($item->total_price ?? ($item->quantity * $item->unit_price)) }}</td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Payment Instructions + Totals --}}
    <table class="summary-table">
        <tr>
            @if($sections['show_payment_instructions'])
                <td class="payment-block" style="width:50%;">
                    <div class="section-label">Payment Instructions</div>
                    <div class="payment-text">{!! nl2br(e($paymentText)) !!}</div>
                </td>
            @endif
            <td style="width:{{ $sections['show_payment_instructions'] ? '50%' : '100%' }};">
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

    {{-- Notes --}}
    @if(!empty($invoice->notes) || !empty($settings['default_notes']))
        <div style="margin-top: 6mm; page-break-inside: avoid;">
            <div class="section-label">Notes</div>
            <div style="font-size: 9pt; line-height: 1.4; color: {{ $textColor }};">
                {!! nl2br(e($invoice->notes ?? $settings['default_notes'] ?? '')) !!}
            </div>
        </div>
    @endif

    {{-- Terms & Conditions --}}
    @if(!empty($invoice->terms) || !empty($settings['terms_and_conditions']))
        <div style="margin-top: 6mm; page-break-inside: avoid;">
            <div class="section-label">Terms & Conditions</div>
            <div style="font-size: 9pt; line-height: 1.4; color: {{ $textColor }};">
                {!! nl2br(e($invoice->terms ?? $settings['terms_and_conditions'] ?? '')) !!}
            </div>
        </div>
    @endif

    {{-- Signature Lines (1-3 columns: Sales Rep + Optional Company + Optional Customer) --}}
    @if($sections['show_signatures'])
        @php
            $showCompanySig = $sections['show_company_signature'] ?? false;
            $showCustomerSig = $sections['show_customer_signature'] ?? false;
            $columnCount = 1 + ($showCompanySig ? 1 : 0) + ($showCustomerSig ? 1 : 0);
        @endphp

        <table class="signature-table">
            <tr>
                {{-- Sales Rep Signature (Always shown when signatures enabled) --}}
                <td style="width: {{ 100 / $columnCount }}%; vertical-align: top;">
                    @if($salesRepSignature)
                        <img src="{{ $salesRepSignature }}" alt="Signature" style="max-height:40px; margin-bottom:4mm;">
                    @endif
                    <div class="signature-line">{{ $salesRepTitle }}</div>
                    <div class="signature-name">{{ $salesRepName }}</div>
                </td>

                {{-- Company Signature (Optional) --}}
                @if($showCompanySig)
                    <td style="width: {{ 100 / $columnCount }}%; vertical-align: top;">
                        @if($companySignature)
                            <img src="{{ $companySignature }}" alt="Company Signature" style="max-height:40px; margin-bottom:4mm;">
                        @endif
                        <div class="signature-line">{{ $companySignatoryTitle ?: 'Authorized Signatory' }}</div>
                        <div class="signature-name">{{ $companySignatoryName ?: 'Company Representative' }}</div>
                    </td>
                @endif

                {{-- Customer Signature (Optional) --}}
                @if($showCustomerSig)
                    <td style="width: {{ 100 / $columnCount }}%; vertical-align: top;">
                        <div class="signature-line">Customer Acceptance</div>
                        <div class="signature-name">{{ $invoice->customer_name ?? 'Customer' }}</div>
                    </td>
                @endif
            </tr>
        </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        {{ $invoice->company->name ?? 'Company' }} • Invoice {{ $invoice->number }} • Generated on {{ now()->format('d M Y, H:i') }}
    </div>
</div>
</body>
</html>
