<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #374151;
            background: white;
        }
        
        .header {
            border-bottom: 3px solid #2563EB;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 5px;
        }
        
        .company-details {
            color: #6B7280;
            font-size: 10px;
        }
        
        .report-info {
            text-align: right;
            color: #6B7280;
            font-size: 10px;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: 600;
            color: #2563EB;
            margin-bottom: 10px;
        }
        
        .report-meta {
            display: flex;
            justify-content: space-between;
            background: #F9FAFB;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #E5E7EB;
        }
        
        .meta-item {
            text-align: center;
        }
        
        .meta-label {
            font-size: 9px;
            color: #6B7280;
            text-transform: uppercase;
            font-weight: 500;
            margin-bottom: 3px;
        }
        
        .meta-value {
            font-size: 12px;
            font-weight: 600;
            color: #1F2937;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .data-table th {
            background: #2563EB;
            color: white;
            padding: 10px 8px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            text-align: left;
            border: 1px solid #1D4ED8;
        }
        
        .data-table td {
            padding: 8px;
            border: 1px solid #E5E7EB;
            font-size: 10px;
            vertical-align: top;
        }
        
        .data-table tr:nth-child(even) {
            background: #F9FAFB;
        }
        
        .data-table tr:hover {
            background: #EFF6FF;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-new { background: #DBEAFE; color: #1D4ED8; }
        .status-contacted { background: #FEF3C7; color: #D97706; }
        .status-quoted { background: #EDE9FE; color: #7C3AED; }
        .status-won { background: #D1FAE5; color: #065F46; }
        .status-lost { background: #FEE2E2; color: #DC2626; }
        .status-draft { background: #F3F4F6; color: #4B5563; }
        .status-sent { background: #DBEAFE; color: #1D4ED8; }
        .status-accepted { background: #D1FAE5; color: #065F46; }
        .status-rejected { background: #FEE2E2; color: #DC2626; }
        .status-paid { background: #D1FAE5; color: #065F46; }
        .status-overdue { background: #FEE2E2; color: #DC2626; }
        
        .currency {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #6B7280;
            font-size: 9px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6B7280;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            .page-break {
                page-break-before: always;
            }
        }
        
        /* Responsive table for many columns */
        @media (max-width: 1200px) {
            .data-table {
                font-size: 8px;
            }
            .data-table th {
                padding: 6px 4px;
            }
            .data-table td {
                padding: 6px 4px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <div>
                <div class="company-name">{{ $company->name ?? 'Bina Group' }}</div>
                <div class="company-details">
                    {{ $company->address ?? 'Company Address' }}<br>
                    Phone: {{ $company->phone ?? '+60 3-1234 5678' }} • Email: {{ $company->email ?? 'info@binagroup.com' }}
                </div>
            </div>
            <div class="report-info">
                <strong>Report Generated:</strong><br>
                {{ $generated_at->format('F d, Y') }}<br>
                {{ $generated_at->format('h:i A') }}<br><br>
                <strong>Generated By:</strong><br>
                {{ $generated_by }}
            </div>
        </div>
        
        <div class="report-title">{{ $title }}</div>
    </div>

    <!-- Report Metadata -->
    <div class="report-meta">
        <div class="meta-item">
            <div class="meta-label">Report Type</div>
            <div class="meta-value">{{ ucfirst(str_replace('_', ' ', $report_type)) }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Total Records</div>
            <div class="meta-value">{{ number_format($total_records) }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Fields Selected</div>
            <div class="meta-value">{{ count($fields) }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Generated</div>
            <div class="meta-value">{{ $generated_at->format('M d, Y') }}</div>
        </div>
    </div>

    <!-- Data Table -->
    @if(count($data) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    @foreach($fields as $fieldKey => $fieldValue)
                        @php
                            $fieldName = is_numeric($fieldKey) ? $fieldValue : $fieldKey;
                            $fieldLabel = $field_labels[$fieldName] ?? ucfirst(str_replace('_', ' ', $fieldName));
                        @endphp
                        <th>{{ $fieldLabel }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $record)
                    <tr>
                        @foreach($fields as $fieldKey => $fieldValue)
                            @php
                                $fieldName = is_numeric($fieldKey) ? $fieldValue : $fieldKey;
                                $value = $record->{$fieldName} ?? '';
                                
                                // Format specific field types
                                if (str_contains($fieldName, '_at') && $value) {
                                    $value = \Carbon\Carbon::parse($value)->format('M d, Y H:i');
                                } elseif (in_array($fieldName, ['total', 'subtotal', 'amount', 'estimated_value', 'paid_amount', 'balance']) && is_numeric($value)) {
                                    $value = 'RM ' . number_format($value, 2);
                                    $isAmount = true;
                                } elseif ($fieldName === 'conversion_rate' && is_numeric($value)) {
                                    $value = number_format($value, 1) . '%';
                                }
                                
                                $statusClass = '';
                                if ($fieldName === 'status') {
                                    $statusClass = 'status-' . strtolower($value);
                                }
                            @endphp
                            <td class="{{ isset($isAmount) && $isAmount ? 'currency' : '' }}">
                                @if($fieldName === 'status' && $statusClass)
                                    <span class="status-badge {{ $statusClass }}">{{ $value }}</span>
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                            @php unset($isAmount); @endphp
                        @endforeach
                    </tr>
                    
                    {{-- Add page breaks every 25 rows for better PDF pagination --}}
                    @if(($index + 1) % 25 === 0 && $index !== count($data) - 1)
                        </tbody>
        </table>
        <div class="page-break"></div>
        <table class="data-table">
            <thead>
                <tr>
                    @foreach($fields as $fieldKey => $fieldValue)
                        @php
                            $fieldName = is_numeric($fieldKey) ? $fieldValue : $fieldKey;
                            $fieldLabel = $field_labels[$fieldName] ?? ucfirst(str_replace('_', ' ', $fieldName));
                        @endphp
                        <th>{{ $fieldLabel }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                    @endif
                @endforeach
            </tbody>
        </table>
        
        @if($total_records > count($data))
            <div style="background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 8px; padding: 15px; margin-top: 20px;">
                <div style="color: #D97706; font-weight: 600; margin-bottom: 5px;">
                    <strong>Note:</strong> This PDF shows the first {{ count($data) }} records out of {{ number_format($total_records) }} total records.
                </div>
                <div style="color: #92400E; font-size: 10px;">
                    For complete data, please use the Excel export option or generate a more specific report with filters.
                </div>
            </div>
        @endif
        
    @else
        <div class="no-data">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" style="margin: 0 auto 15px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 style="font-size: 14px; font-weight: 600; color: #4B5563; margin-bottom: 8px;">No Data Available</h3>
            <p style="color: #6B7280;">No records match the specified criteria for this report.</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>{{ $company->name ?? 'Bina Group' }}</strong> • 
            Report generated on {{ $generated_at->format('F d, Y \a\t h:i A') }} • 
            Page <span class="pageNumber"></span> of <span class="totalPages"></span>
        </p>
        <p style="margin-top: 5px;">
            This report contains confidential business information. Distribution should be restricted to authorized personnel only.
        </p>
    </div>

    <script>
        // Add page numbers (will be processed by Browsershot)
        function addPageNumbers() {
            const pageNumbers = document.querySelectorAll('.pageNumber');
            const totalPages = document.querySelectorAll('.totalPages');
            
            pageNumbers.forEach(el => el.textContent = '1');
            totalPages.forEach(el => el.textContent = '1');
        }
        
        // Run when DOM is loaded
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', addPageNumbers);
        } else {
            addPageNumbers();
        }
    </script>
</body>
</html>