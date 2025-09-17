<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $options['title'] ?? 'Social Proof Portfolio' }}</title>
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
        
        /* Cover Page Styles */
        .cover-page {
            min-height: 267mm; /* Full page minus padding */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            margin: -15mm;
            padding: 15mm;
        }
        
        .cover-title {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .cover-subtitle {
            font-size: 24px;
            opacity: 0.9;
            margin-bottom: 40px;
        }
        
        .cover-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin: 60px 0;
            width: 100%;
            max-width: 600px;
        }
        
        .cover-stat {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px 20px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .cover-stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .cover-stat-label {
            font-size: 14px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .company-info-cover {
            margin-top: 60px;
            font-size: 16px;
            opacity: 0.9;
        }
        
        /* Regular Page Styles */
        .content-page {
            page-break-before: always;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            margin-bottom: 5px;
        }
        
        .company-tagline {
            font-size: 14px;
            color: #6b7280;
            font-style: italic;
        }
        
        .document-info {
            text-align: right;
            flex: 1;
        }
        
        .document-title {
            font-size: 20px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .document-date {
            font-size: 11px;
            color: #6b7280;
        }
        
        /* Section Headers */
        .section-header {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            font-size: 16px;
            margin: 30px 0 20px 0;
            border-radius: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .section-description {
            color: #6b7280;
            font-size: 11px;
            margin-bottom: 20px;
            line-height: 1.6;
            background: #f9fafb;
            padding: 10px 15px;
            border-radius: 6px;
            border-left: 4px solid #2563eb;
        }
        
        /* Proof Grid Styles */
        .proof-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .proof-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .proof-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--category-color, #2563eb);
        }
        
        .proof-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        
        .proof-badges {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .proof-badge {
            font-size: 8px;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-featured {
            background: #fbbf24;
            color: white;
        }
        
        .badge-category {
            background: var(--category-color, #2563eb);
            color: white;
        }
        
        .proof-title {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .proof-description {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .proof-assets {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(60px, 1fr));
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .asset-thumbnail {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            overflow: hidden;
            border: 2px solid #f3f4f6;
        }
        
        .asset-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .asset-placeholder {
            width: 60px;
            height: 60px;
            background: #f3f4f6;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 10px;
            text-align: center;
            font-weight: 500;
        }
        
        .proof-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #f3f4f6;
            padding-top: 12px;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .impact-badge {
            background: #10b981;
            color: white;
            padding: 2px 6px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        /* Category Sections */
        .category-visual { --category-color: #8b5cf6; }
        .category-social { --category-color: #06b6d4; }
        .category-professional { --category-color: #10b981; }
        .category-performance { --category-color: #f59e0b; }
        .category-trust { --category-color: #ef4444; }
        
        /* Summary Stats */
        .summary-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .summary-title {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-number {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
        
        /* Print optimizations */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
        
        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            font-weight: bold;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
            user-select: none;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <!-- Watermark if specified -->
    @if(!empty($options['watermark']))
        <div class="watermark">{{ $options['watermark'] }}</div>
    @endif

    <!-- Cover Page -->
    <div class="page cover-page">
        <div class="cover-title">{{ $options['title'] ?? 'Social Proof Portfolio' }}</div>
        <div class="cover-subtitle">{{ $company ? $company->name : 'Professional Credentials' }}</div>
        
        @php
            $analytics = app(\App\Services\PDFService::class)->getProofAnalytics($proofs);
        @endphp
        
        <div class="cover-stats">
            <div class="cover-stat">
                <div class="cover-stat-number">{{ $analytics['total_proofs'] }}</div>
                <div class="cover-stat-label">{{ Str::plural('Proof', $analytics['total_proofs']) }}</div>
            </div>
            <div class="cover-stat">
                <div class="cover-stat-number">{{ $analytics['featured_count'] }}</div>
                <div class="cover-stat-label">Featured</div>
            </div>
            <div class="cover-stat">
                <div class="cover-stat-number">{{ number_format($analytics['average_impact'], 1) }}%</div>
                <div class="cover-stat-label">Avg Impact</div>
            </div>
        </div>
        
        @if($company)
            <div class="company-info-cover">
                {{ $company->name }}<br>
                @if($company->tagline)
                    <em>{{ $company->tagline }}</em><br>
                @endif
                Generated on {{ now()->format('F j, Y') }}
            </div>
        @endif
    </div>

    <!-- Content Pages -->
    @foreach($groupedProofs as $category => $categoryProofs)
        <div class="page content-page">
            <!-- Header -->
            <div class="header">
                <div class="company-info">
                    <div class="company-name">{{ $company ? $company->name : 'Professional Portfolio' }}</div>
                    @if($company && $company->tagline)
                        <div class="company-tagline">{{ $company->tagline }}</div>
                    @endif
                </div>
                <div class="document-info">
                    <div class="document-title">{{ $options['title'] ?? 'Social Proof Portfolio' }}</div>
                    <div class="document-date">Generated {{ now()->format('M j, Y') }}</div>
                </div>
            </div>

            <!-- Category Section -->
            <div class="section-header category-{{ $category }}">
                {{ \App\Models\Proof::getTypeLabels()[$category] ?? Str::title($category) }}
                <span style="font-weight: normal; font-size: 12px; margin-left: 10px;">
                    ({{ $categoryProofs->count() }} {{ Str::plural('item', $categoryProofs->count()) }})
                </span>
            </div>

            <div class="section-description">
                @switch($category)
                    @case('testimonial')
                        Customer testimonials and feedback showcase our commitment to excellence and the positive impact of our services on our clients' success.
                        @break
                    @case('case_study')
                        Detailed case studies demonstrate our problem-solving capabilities and the tangible results we deliver for our clients.
                        @break
                    @case('certification')
                        Professional certifications and credentials that validate our expertise and commitment to industry standards.
                        @break
                    @case('award')
                        Recognition and awards that highlight our achievements and standing within the industry.
                        @break
                    @case('media_coverage')
                        Media coverage and press features that showcase our thought leadership and industry presence.
                        @break
                    @case('client_logo')
                        Trusted by leading organizations across various industries, demonstrating our broad expertise and reliability.
                        @break
                    @case('project_showcase')
                        Successful project completions that highlight our capabilities and the quality of our deliverables.
                        @break
                    @case('before_after')
                        Transformation results that demonstrate the tangible improvements and value we bring to our clients.
                        @break
                    @case('statistics')
                        Performance metrics and statistics that quantify our success and the impact of our services.
                        @break
                    @case('partnership')
                        Strategic partnerships that enhance our capabilities and extend our service offerings.
                        @break
                    @default
                        Professional credentials and evidence of our expertise in delivering exceptional results.
                @endswitch
            </div>

            <!-- Proof Grid -->
            <div class="proof-grid">
                @foreach($categoryProofs as $proof)
                    <div class="proof-card category-{{ $proof->type }}">
                        <!-- Proof Header -->
                        <div class="proof-header">
                            <div class="proof-badges">
                                @if($proof->is_featured)
                                    <span class="proof-badge badge-featured">★ Featured</span>
                                @endif
                                <span class="proof-badge badge-category">{{ $proof->type_label }}</span>
                            </div>
                        </div>

                        <!-- Proof Content -->
                        <div class="proof-title">{{ $proof->title }}</div>
                        
                        @if($proof->description)
                            <div class="proof-description">{{ $proof->description }}</div>
                        @endif

                        <!-- Proof Assets -->
                        @php
                            $displayAssets = app(\App\Services\PDFService::class)->filterAssetsForPDF($proof, 4);
                        @endphp
                        
                        @if($displayAssets->isNotEmpty())
                            <div class="proof-assets">
                                @foreach($displayAssets as $asset)
                                    @if($asset->isImage())
                                        <div class="asset-thumbnail">
                                            <img src="{{ asset('storage/' . ($asset->thumbnail_path ?: $asset->file_path)) }}" 
                                                 alt="{{ $asset->alt_text }}">
                                        </div>
                                    @else
                                        <div class="asset-placeholder">
                                            {{ strtoupper(pathinfo($asset->original_filename, PATHINFO_EXTENSION)) }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <!-- Proof Stats -->
                        <div class="proof-stats">
                            <div class="stat-item">
                                @if($proof->views_count > 0)
                                    <span>{{ number_format($proof->views_count) }} views</span>
                                @endif
                            </div>
                            <div class="stat-item">
                                @if($proof->conversion_impact)
                                    <span class="impact-badge">{{ $proof->conversion_impact }}% impact</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <!-- Summary Page -->
    @if($proofs->count() > 0)
        <div class="page content-page">
            <!-- Header -->
            <div class="header">
                <div class="company-info">
                    <div class="company-name">{{ $company ? $company->name : 'Professional Portfolio' }}</div>
                    @if($company && $company->tagline)
                        <div class="company-tagline">{{ $company->tagline }}</div>
                    @endif
                </div>
                <div class="document-info">
                    <div class="document-title">Portfolio Summary</div>
                    <div class="document-date">{{ now()->format('M j, Y') }}</div>
                </div>
            </div>

            <div class="summary-section">
                <div class="summary-title">Portfolio Overview</div>
                
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-number">{{ $analytics['total_proofs'] }}</div>
                        <div class="summary-label">Total Proofs</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-number">{{ number_format($analytics['total_views']) }}</div>
                        <div class="summary-label">Total Views</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-number">{{ $analytics['featured_count'] }}</div>
                        <div class="summary-label">Featured Items</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-number">{{ number_format($analytics['average_impact'], 1) }}%</div>
                        <div class="summary-label">Avg Impact</div>
                    </div>
                </div>
            </div>

            <!-- Category Breakdown -->
            <div class="section-header">Category Breakdown</div>
            <div class="proof-grid">
                @foreach($analytics['categories'] as $categoryName => $count)
                    <div class="proof-card category-{{ $categoryName }}">
                        <div class="proof-title">{{ \App\Models\Proof::getTypeLabels()[$categoryName] ?? Str::title($categoryName) }}</div>
                        <div class="proof-description">
                            {{ $count }} {{ Str::plural('item', $count) }} in this category
                        </div>
                        <div class="proof-stats">
                            <div class="stat-item">
                                <span>{{ number_format(($count / $analytics['total_proofs']) * 100, 1) }}% of portfolio</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($company)
                <div style="margin-top: 40px; text-align: center; padding: 20px; background: #f9fafb; border-radius: 10px;">
                    <div style="font-size: 14px; color: #6b7280; margin-bottom: 10px;">
                        This portfolio represents our commitment to excellence and the trust placed in us by our clients.
                    </div>
                    <div style="font-size: 16px; font-weight: 600; color: #1f2937;">
                        {{ $company->name }}
                    </div>
                    @if($company->website)
                        <div style="font-size: 12px; color: #2563eb; margin-top: 5px;">
                            {{ $company->website }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div>
            {{ $company ? $company->name : 'Social Proof Portfolio' }} • Generated {{ now()->format('M j, Y \a\t H:i') }}
        </div>
        <div>
            Page <span class="pageNumber"></span> of <span class="totalPages"></span>
        </div>
    </div>
</body>
</html>