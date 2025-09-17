<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Report - {{ $assessment->assessment_code }}</title>
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.4;
            color: #1f2937;
            font-size: 11px;
            background: white;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        /* Header Styles */
        .header {
            padding: 15px 0;
            border-bottom: 2px solid #2563eb;
            margin-bottom: 20px;
            position: relative;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 60%;
        }
        
        .header-right {
            display: table-cell;
            vertical-align: top;
            width: 40%;
            text-align: right;
        }
        
        .company-logo {
            max-height: 50px;
            max-width: 150px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 3px;
        }
        
        .company-details {
            font-size: 9px;
            color: #6b7280;
            line-height: 1.3;
        }
        
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .assessment-code {
            font-size: 12px;
            color: #6b7280;
            font-family: 'Courier New', monospace;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-scheduled { background: #dbeafe; color: #1d4ed8; }
        .status-in-progress { background: #fef3c7; color: #d97706; }
        .status-completed { background: #d1fae5; color: #059669; }
        .status-cancelled { background: #fee2e2; color: #dc2626; }
        
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
            pointer-events: none;
        }
        
        /* Content Sections */
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
            padding: 8px 12px;
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            margin-bottom: 10px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: 600;
            color: #374151;
            padding: 4px 8px 4px 0;
            width: 30%;
            vertical-align: top;
        }
        
        .info-value {
            display: table-cell;
            color: #1f2937;
            padding: 4px 0;
            vertical-align: top;
        }
        
        /* Assessment Analytics */
        .analytics-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .analytics-item {
            display: table-cell;
            text-align: center;
            width: 25%;
            padding: 10px 5px;
            border: 1px solid #e5e7eb;
        }
        
        .analytics-value {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
        }
        
        .analytics-label {
            font-size: 9px;
            color: #6b7280;
            margin-top: 2px;
        }
        
        /* Risk Indicators */
        .risk-indicator {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .risk-low { background: #d1fae5; color: #059669; }
        .risk-medium { background: #fef3c7; color: #d97706; }
        .risk-high { background: #fee2e2; color: #dc2626; }
        .risk-critical { background: #f3e8ff; color: #7c3aed; }
        
        /* Assessment Sections */
        .assessment-sections {
            margin-top: 20px;
        }
        
        .assessment-section {
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .section-header {
            background: #f8fafc;
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
            display: table;
            width: 100%;
        }
        
        .section-name {
            display: table-cell;
            font-weight: 600;
            color: #1f2937;
            width: 60%;
        }
        
        .section-score {
            display: table-cell;
            text-align: right;
            width: 40%;
            font-size: 10px;
        }
        
        .section-content {
            padding: 10px 12px;
        }
        
        .section-items {
            margin-top: 8px;
        }
        
        .section-item {
            margin-bottom: 6px;
            font-size: 10px;
        }
        
        .item-name {
            font-weight: 500;
            color: #374151;
        }
        
        .item-details {
            color: #6b7280;
            margin-left: 10px;
        }
        
        /* Photos Grid */
        .photos-section {
            margin-top: 20px;
        }
        
        .photos-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
        }
        
        .photos-row {
            display: table-row;
        }
        
        .photo-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            vertical-align: top;
        }
        
        .photo-container {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            background: #f9fafb;
        }
        
        .photo-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        
        .photo-caption {
            padding: 6px;
            font-size: 8px;
            color: #6b7280;
            background: white;
        }
        
        /* Recommendations */
        .recommendations-list {
            list-style: none;
            padding: 0;
        }
        
        .recommendation-item {
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .recommendation-item:last-child {
            border-bottom: none;
        }
        
        .recommendation-priority {
            font-weight: 600;
            color: #dc2626;
        }
        
        .recommendation-text {
            margin-top: 3px;
            color: #374151;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #6b7280;
            text-align: center;
        }
        
        /* Social Proof Section */
        .proof-section {
            margin-top: 25px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .proof-title {
            font-size: 12px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .proof-grid {
            display: table;
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
        }
        
        .proof-row {
            display: table-row;
        }
        
        .proof-cell {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .proof-item {
            background: white;
            border-radius: 6px;
            padding: 8px;
            border: 1px solid #e5e7eb;
            margin-bottom: 8px;
        }
        
        .proof-type {
            font-size: 8px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .proof-title-text {
            font-size: 10px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 2px;
        }
        
        .proof-description {
            font-size: 9px;
            color: #374151;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    <!-- Watermark for draft assessments -->
    @if($assessment->status === 'draft')
        <div class="watermark">DRAFT</div>
    @endif
    
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                @if($assessment->company->logo_path)
                    <img src="{{ asset('storage/' . $assessment->company->logo_path) }}" alt="{{ $assessment->company->name }}" class="company-logo">
                @else
                    <div class="company-name">{{ $assessment->company->name }}</div>
                @endif
                <div class="company-details">
                    @if($assessment->company->address)
                        {{ $assessment->company->address }}<br>
                    @endif
                    @if($assessment->company->phone)
                        Tel: {{ $assessment->company->phone }}
                    @endif
                    @if($assessment->company->email)
                        | Email: {{ $assessment->company->email }}
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="report-title">Assessment Report</div>
                <div class="assessment-code">{{ $assessment->assessment_code }}</div>
                <div style="margin-top: 8px;">
                    <span class="status-badge status-{{ strtolower(str_replace('_', '-', $assessment->status)) }}">
                        {{ str_replace('_', ' ', $assessment->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment Overview -->
    <div class="section">
        <div class="section-title">Assessment Overview</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Service Type:</div>
                <div class="info-value">{{ ucwords(str_replace('_', ' ', $assessment->service_type)) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Property Type:</div>
                <div class="info-value">{{ ucwords($assessment->property_type) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Assessment Date:</div>
                <div class="info-value">{{ $assessment->assessment_date?->format('d M Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Estimated Duration:</div>
                <div class="info-value">{{ $assessment->estimated_duration }} minutes</div>
            </div>
            @if($assessment->total_area)
            <div class="info-row">
                <div class="info-label">Total Area:</div>
                <div class="info-value">{{ number_format($assessment->total_area, 2) }} {{ $assessment->area_unit }}</div>
            </div>
            @endif
            @if($assessment->overall_risk_score)
            <div class="info-row">
                <div class="info-label">Risk Level:</div>
                <div class="info-value">
                    <span class="risk-indicator risk-{{ $assessment->overall_risk_score >= 7 ? 'high' : ($assessment->overall_risk_score >= 4 ? 'medium' : 'low') }}">
                        {{ $assessment->overall_risk_score >= 7 ? 'High' : ($assessment->overall_risk_score >= 4 ? 'Medium' : 'Low') }}
                        ({{ $assessment->overall_risk_score }}/10)
                    </span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Client Information -->
    <div class="section">
        <div class="section-title">Client Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Client Name:</div>
                <div class="info-value">{{ $assessment->client_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Phone:</div>
                <div class="info-value">{{ $assessment->client_phone }}</div>
            </div>
            @if($assessment->client_email)
            <div class="info-row">
                <div class="info-label">Email:</div>
                <div class="info-value">{{ $assessment->client_email }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Location:</div>
                <div class="info-value">
                    {{ $assessment->location_address }}<br>
                    {{ $assessment->location_city }}, {{ $assessment->location_state }} {{ $assessment->location_postal_code }}
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment Analytics -->
    @php
        $analytics = app(\App\Services\PDFService::class)->getAssessmentAnalyticsForPDF($assessment);
    @endphp
    
    <div class="section">
        <div class="section-title">Assessment Summary</div>
        <div class="analytics-grid">
            <div class="analytics-item">
                <div class="analytics-value">{{ $analytics['completion_rate'] }}%</div>
                <div class="analytics-label">Completion Rate</div>
            </div>
            <div class="analytics-item">
                <div class="analytics-value">{{ $analytics['overall_score'] }}%</div>
                <div class="analytics-label">Overall Score</div>
            </div>
            <div class="analytics-item">
                <div class="analytics-value">{{ $analytics['completed_sections'] }}/{{ $analytics['total_sections'] }}</div>
                <div class="analytics-label">Sections Complete</div>
            </div>
            <div class="analytics-item">
                <div class="analytics-value">{{ $analytics['photos_count'] }}</div>
                <div class="analytics-label">Photos Captured</div>
            </div>
        </div>
    </div>

    <!-- Assessment Sections -->
    @if($assessment->sections->isNotEmpty())
    <div class="section">
        <div class="section-title">Assessment Details</div>
        <div class="assessment-sections">
            @foreach($assessment->sections->sortBy('sort_order') as $section)
            <div class="assessment-section">
                <div class="section-header">
                    <div class="section-name">{{ $section->name }}</div>
                    <div class="section-score">
                        @if($section->current_score && $section->max_score)
                            {{ $section->current_score }}/{{ $section->max_score }}
                            ({{ round(($section->current_score / $section->max_score) * 100, 1) }}%)
                        @endif
                        @if($section->status)
                            <span class="status-badge status-{{ $section->status }}">{{ $section->status }}</span>
                        @endif
                    </div>
                </div>
                <div class="section-content">
                    @if($section->description)
                        <div style="margin-bottom: 8px; color: #6b7280; font-size: 10px;">
                            {{ $section->description }}
                        </div>
                    @endif
                    
                    @if($section->quality_rating || $section->risk_level)
                        <div style="margin-bottom: 8px;">
                            @if($section->quality_rating)
                                <span style="font-size: 9px; color: #374151;"><strong>Quality:</strong> {{ ucfirst($section->quality_rating) }}</span>
                            @endif
                            @if($section->risk_level)
                                <span style="font-size: 9px; color: #374151; margin-left: 10px;">
                                    <strong>Risk:</strong> 
                                    <span class="risk-indicator risk-{{ $section->risk_level }}">{{ ucfirst($section->risk_level) }}</span>
                                </span>
                            @endif
                        </div>
                    @endif
                    
                    @if($section->items->isNotEmpty())
                        <div class="section-items">
                            @foreach($section->items->sortBy('sort_order') as $item)
                            <div class="section-item">
                                <div class="item-name">{{ $item->name }}</div>
                                @if($item->description)
                                    <div class="item-details">{{ $item->description }}</div>
                                @endif
                                @if($item->current_score && $item->max_score)
                                    <div class="item-details">
                                        Score: {{ $item->current_score }}/{{ $item->max_score }}
                                        ({{ round(($item->current_score / $item->max_score) * 100, 1) }}%)
                                    </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endif
                    
                    @if($section->notes)
                        <div style="margin-top: 8px; padding: 6px; background: #f9fafb; border-radius: 4px; font-size: 9px;">
                            <strong>Notes:</strong> {{ $section->notes }}
                        </div>
                    @endif
                    
                    @if($section->recommendations)
                        <div style="margin-top: 8px; padding: 6px; background: #fef3c7; border-radius: 4px; font-size: 9px;">
                            <strong>Recommendations:</strong> {{ $section->recommendations }}
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Photos Section -->
    @php
        $photosByType = app(\App\Services\PDFService::class)->getAssessmentPhotosForPDF($assessment, 3);
    @endphp
    
    @if(!empty($photosByType))
    <div class="page-break"></div>
    <div class="section photos-section">
        <div class="section-title">Assessment Photos</div>
        @foreach($photosByType as $photoType => $photos)
            @if($photos->isNotEmpty())
            <div style="margin-bottom: 15px;">
                <h4 style="font-size: 11px; color: #374151; margin-bottom: 8px;">{{ ucwords(str_replace('_', ' ', $photoType)) }}</h4>
                <div class="photos-grid">
                    @foreach($photos->chunk(3) as $photoRow)
                    <div class="photos-row">
                        @foreach($photoRow as $photo)
                        <div class="photo-cell">
                            <div class="photo-container">
                                <img src="{{ asset('storage/' . $photo->file_path) }}" alt="{{ $photo->description }}" class="photo-image">
                                @if($photo->description)
                                <div class="photo-caption">{{ $photo->description }}</div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        @for($i = count($photoRow); $i < 3; $i++)
                        <div class="photo-cell"></div>
                        @endfor
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @endif

    <!-- Overall Recommendations -->
    @if($assessment->recommendations || $assessment->sections->whereNotNull('recommendations')->isNotEmpty())
    <div class="section">
        <div class="section-title">Recommendations & Next Steps</div>
        @if($assessment->recommendations)
            <div style="margin-bottom: 12px; padding: 10px; background: #fef3c7; border-radius: 6px;">
                <strong style="color: #92400e;">Overall Recommendations:</strong>
                <div style="margin-top: 4px; color: #1f2937;">{{ $assessment->recommendations }}</div>
            </div>
        @endif
        
        @php
            $sectionRecommendations = $assessment->sections->whereNotNull('recommendations');
        @endphp
        
        @if($sectionRecommendations->isNotEmpty())
            <ul class="recommendations-list">
                @foreach($sectionRecommendations as $section)
                <li class="recommendation-item">
                    <div class="recommendation-priority">{{ $section->name }}:</div>
                    <div class="recommendation-text">{{ $section->recommendations }}</div>
                </li>
                @endforeach
            </ul>
        @endif
    </div>
    @endif

    <!-- Social Proof Section -->
    @php
        $proofs = app(\App\Services\PDFService::class)->getProofsForPDF($assessment, 'assessment');
    @endphp
    
    @if($proofs->isNotEmpty())
    <div class="proof-section">
        <div class="proof-title">{{ app(\App\Services\PDFService::class)->getProofSectionTitle('assessment') }}</div>
        <div class="proof-grid">
            @foreach($proofs->chunk(2) as $proofRow)
            <div class="proof-row">
                @foreach($proofRow as $proof)
                <div class="proof-cell">
                    <div class="proof-item">
                        <div class="proof-type">{{ str_replace('_', ' ', $proof->type) }}</div>
                        <div class="proof-title-text">{{ $proof->title }}</div>
                        @if($proof->description)
                        <div class="proof-description">{{ Str::limit($proof->description, 100) }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
                @for($i = count($proofRow); $i < 2; $i++)
                <div class="proof-cell"></div>
                @endfor
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div>Assessment Report generated on {{ now()->format('d M Y \a\t H:i') }}</div>
        <div style="margin-top: 3px;">
            @if($assessment->assignedTo)
                Assessed by: {{ $assessment->assignedTo->name }}
            @endif
            @if($assessment->serviceTemplate)
                | Template: {{ $assessment->serviceTemplate->name }}
            @endif
        </div>
        <div style="margin-top: 8px; font-size: 8px; color: #9ca3af;">
            This report is confidential and intended solely for {{ $assessment->client_name }}.
            Generated by {{ $assessment->company->name }} Assessment System.
        </div>
    </div>
</body>
</html>