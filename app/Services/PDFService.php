<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\Proof;
use App\Models\Assessment;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Dompdf\Dompdf;
use Dompdf\Options;

class PDFService
{
    /**
     * Generate PDF for a quotation using Dompdf
     */
    public function generateQuotationPDF(Quotation $quotation): string
    {
        // Load quotation with all necessary relationships including proofs
        $quotation->load(['items', 'sections.items', 'lead', 'company', 'team', 'proofs.assets', 'customerSegment']);

        $paletteDefaults = [
            'accent_color' => '#0b57d0',
            'accent_text_color' => '#ffffff',
            'text_color' => '#000000',
            'muted_text_color' => '#4b5563',
            'heading_color' => '#000000',
            'border_color' => '#d0d5dd',
            'table_header_background' => '#0b57d0',
            'table_header_text' => '#ffffff',
        ];

        $sectionDefaults = [
            'show_company_logo' => true,
            'show_payment_instructions' => true,
            'show_signatures' => true,
            'show_company_signature' => false,
            'show_customer_signature' => false,
        ];

        $currency = $quotation->currency ?? 'RM';
        $viewName = 'pdf.quotation';
        $viewData = [];

        if ($quotation->type === Quotation::TYPE_PRODUCT) {
            /** @var \App\Services\ProductQuotationSettingsService $settingsService */
            $settingsService = app(\App\Services\ProductQuotationSettingsService::class);
            $mergedSettings = $settingsService->getMergedSettingsForPDF($quotation, $quotation->company_id);

            $appearance = array_merge($paletteDefaults, $mergedSettings['appearance'] ?? []);
            $sections = array_merge($sectionDefaults, $mergedSettings['sections'] ?? []);
            $currency = $mergedSettings['defaults']['currency'] ?? $currency;

            $viewName = 'pdf.product-quotation';
            $viewData = [
                'quotation' => $quotation,
                'palette' => $appearance,
                'sections' => $sections,
                'currency' => $currency,
                'settings' => $mergedSettings,
            ];
        } else {
            $viewData = [
                'quotation' => $quotation,
                'palette' => $paletteDefaults,
                'sections' => [
                    'show_company_logo' => true,
                    'show_payment_instructions' => true,
                    'show_signatures' => true,
                ],
                'currency' => $currency,
            ];
        }

        // Render the HTML view for the PDF
        $html = view($viewName, $viewData)->render();

        // Configure Dompdf options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        // Generate PDF using Dompdf
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdf = $dompdf->output();

        // Store the PDF
        $filename = $this->generatePDFFilename($quotation);
        $path = "pdfs/quotations/{$quotation->company_id}/{$filename}";

        Storage::put($path, $pdf);

        // Update quotation with PDF path
        $quotation->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * Configure Browsershot with Chrome path if available
     */
    protected function configureBrowsershot($browsershot)
    {
        $chromePath = $this->getChromePath();
        if ($chromePath !== null) {
            $browsershot->setChromePath($chromePath);
        } else {
            // If no Chrome found, try to use Node.js with Puppeteer
            $browsershot->setNodeBinary('node')
                      ->setNpmBinary('npm');
        }

        // Add additional debugging and error handling
        $browsershot->setOption('args', [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-gpu',
            '--disable-dev-shm-usage',
            '--disable-web-security',
            '--font-render-hinting=none'
        ]);

        return $browsershot;
    }

    /**
     * Get the Chrome/Chromium path for different environments
     */
    protected function getChromePath(): ?string
    {
        // Check common Chrome/Chromium paths
        $paths = [
            '/usr/bin/google-chrome',
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
            '/snap/bin/chromium',
            './node_modules/.bin/puppeteer'
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // For development with Node.js Puppeteer
        $puppeteerPath = base_path('node_modules/puppeteer/.local-chromium');
        if (is_dir($puppeteerPath)) {
            // Find the Chromium executable in Puppeteer's directory
            $chromiumDirs = glob($puppeteerPath . '/linux-*');
            if (!empty($chromiumDirs)) {
                $chromiumPath = $chromiumDirs[0] . '/chrome-linux/chrome';
                if (file_exists($chromiumPath)) {
                    return $chromiumPath;
                }
            }
        }
        
        // Let Browsershot try to find Chrome automatically
        return null;
    }

    /**
     * Generate a unique filename for the PDF
     */
    protected function generatePDFFilename(Quotation $quotation): string
    {
        $number = Str::slug($quotation->number);
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "{$number}_{$timestamp}.pdf";
    }

    /**
     * Get the full path to a stored PDF
     */
    public function getPDFPath(string $path): string
    {
        return Storage::path($path);
    }

    /**
     * Check if PDF exists for a quotation
     */
    public function pdfExists(Quotation $quotation): bool
    {
        return $quotation->pdf_path && Storage::exists($quotation->pdf_path);
    }

    /**
     * Delete PDF file for a quotation
     */
    public function deletePDF(Quotation $quotation): bool
    {
        if ($quotation->pdf_path && Storage::exists($quotation->pdf_path)) {
            Storage::delete($quotation->pdf_path);
            $quotation->update(['pdf_path' => null]);
            return true;
        }
        
        return false;
    }

    /**
     * Generate PDF for an invoice
     */
    public function generateInvoicePDF(Invoice $invoice): string
    {
        // Use the new InvoicePdfRenderer
        $renderer = app(InvoicePdfRenderer::class);

        try {
            $pdf = $renderer->generate($invoice);

            // Store the PDF
            $filename = $this->generateInvoicePDFFilename($invoice);
            $path = "pdfs/invoices/{$invoice->company_id}/{$filename}";

            Storage::put($path, $pdf);

            // Update invoice with PDF path and generation timestamp
            $invoice->update([
                'pdf_path' => $path,
                'pdf_generated_at' => now()
            ]);

            return $path;
        } catch (\Exception $e) {
            \Log::error('Invoice PDF generation failed', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->number,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate a unique filename for the invoice PDF
     */
    protected function generateInvoicePDFFilename(Invoice $invoice): string
    {
        $number = Str::slug($invoice->number);
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "{$number}_{$timestamp}.pdf";
    }

    /**
     * Check if PDF exists for an invoice
     */
    public function invoicePdfExists(Invoice $invoice): bool
    {
        return $invoice->pdf_path && Storage::exists($invoice->pdf_path);
    }

    /**
     * Delete PDF file for an invoice
     */
    public function deleteInvoicePDF(Invoice $invoice): bool
    {
        if ($invoice->pdf_path && Storage::exists($invoice->pdf_path)) {
            Storage::delete($invoice->pdf_path);
            $invoice->update(['pdf_path' => null]);
            return true;
        }
        
        return false;
    }

    /**
     * Generic method to download PDF for quotation, invoice, or assessment
     */
    public function downloadPDF($model, string $type = null): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if ($model instanceof Assessment || $type === 'assessment') {
            // Generate PDF if it doesn't exist
            if (!$this->assessmentPdfExists($model)) {
                $this->generateAssessmentPDF($model);
            }

            $filename = "{$model->assessment_code}.pdf";
            return response()->download(Storage::path($model->pdf_path), $filename);

        } elseif ($model instanceof Quotation || $type === 'quotation') {
            // Generate PDF if it doesn't exist
            if (!$this->pdfExists($model)) {
                $this->generateQuotationPDF($model);
            }

            $filename = "{$model->number}.pdf";
            return response()->download(Storage::path($model->pdf_path), $filename);

        } elseif ($model instanceof Invoice || $type === 'invoice') {
            // Generate PDF if it doesn't exist
            if (!$this->invoicePdfExists($model)) {
                $this->generateInvoicePDF($model);
            }

            $filename = "{$model->number}.pdf";
            return response()->download(Storage::path($model->pdf_path), $filename);
        }

        throw new \InvalidArgumentException('Invalid model type for PDF generation');
    }

    /**
     * Generic method to stream PDF for preview (quotation, invoice, or assessment)
     */
    public function streamPDF($model, string $type = null): \Symfony\Component\HttpFoundation\Response
    {
        if ($model instanceof Assessment || $type === 'assessment') {
            // Generate PDF if it doesn't exist
            if (!$this->assessmentPdfExists($model)) {
                $this->generateAssessmentPDF($model);
            }

            return response()->file(Storage::path($model->pdf_path), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $model->assessment_code . '.pdf"'
            ]);

        } elseif ($model instanceof Quotation || $type === 'quotation') {
            // Generate PDF if it doesn't exist
            if (!$this->pdfExists($model)) {
                $this->generateQuotationPDF($model);
            }

            return response()->file(Storage::path($model->pdf_path), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $model->number . '.pdf"'
            ]);

        } elseif ($model instanceof Invoice || $type === 'invoice') {
            // Generate PDF if it doesn't exist
            if (!$this->invoicePdfExists($model)) {
                $this->generateInvoicePDF($model);
            }

            return response()->file(Storage::path($model->pdf_path), [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $model->number . '.pdf"'
            ]);
        }

        throw new \InvalidArgumentException('Invalid model type for PDF generation');
    }

    /**
     * Get proofs for PDF display based on model and settings (quotation, invoice, or assessment)
     */
    public function getProofsForPDF($model, string $type = 'quotation'): \Illuminate\Database\Eloquent\Collection
    {
        $query = Proof::query()
            ->with(['assets' => function ($query) {
                $query->where('processing_status', 'completed')
                      ->where('show_in_gallery', true)
                      ->orderBy('is_primary', 'desc')
                      ->orderBy('sort_order');
            }])
            ->where('status', 'active')
            ->where('company_id', $model->company_id)
            ->whereDate('expires_at', '>=', now())
            ->orWhereNull('expires_at');

        if ($type === 'quotation') {
            $query->where('show_in_quotation', true);
        } elseif ($type === 'invoice') {
            $query->where('show_in_invoice', true);
        } elseif ($type === 'assessment') {
            // For assessments, show credibility and expertise proofs
            $query->where('show_in_pdf', true)
                  ->whereIn('type', ['testimonials', 'case_studies', 'certifications', 'awards']);
        }

        $query->where('show_in_pdf', true);

        // Filter by scope if proof is linked to specific content
        $query->where(function ($q) use ($model) {
            $q->whereNull('scope_type')
              ->orWhere(function ($sq) use ($model) {
                  $sq->where('scope_type', get_class($model))
                     ->where('scope_id', $model->id);
              });
        });

        return $query->orderBy('is_featured', 'desc')
                    ->orderBy('sort_order')
                    ->limit($type === 'assessment' ? 6 : 8) // Fewer proofs for assessments
                    ->get();
    }

    /**
     * Generate standalone proof pack PDF
     */
    public function generateProofPackPDF(\Illuminate\Database\Eloquent\Collection $proofs, array $options = []): string
    {
        // Default options
        $options = array_merge([
            'title' => 'Social Proof Portfolio',
            'company_id' => null,
            'orientation' => 'portrait',
            'show_analytics' => false,
            'watermark' => null
        ], $options);

        // Ensure we have company context
        $company = null;
        if ($options['company_id']) {
            $company = \App\Models\Company::find($options['company_id']);
        } elseif ($proofs->isNotEmpty()) {
            $company = $proofs->first()->company;
        }

        // Group proofs by category for better organization
        $groupedProofs = $proofs->groupBy('type');

        // Render the HTML view for the proof pack PDF
        $html = view('pdf.proof-pack', compact('proofs', 'groupedProofs', 'company', 'options'))->render();

        // Generate PDF using Browsershot
        $browsershot = Browsershot::html($html)
            ->format('A4')
            ->orientation($options['orientation'])
            ->margins(15, 15, 15, 15)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->timeout(90); // Longer timeout for image-heavy proofs

        $pdf = $this->configureBrowsershot($browsershot)->pdf();

        // Store the PDF
        $filename = $this->generateProofPackFilename($options['title']);
        $path = "pdfs/proof-packs/{$options['company_id']}/{$filename}";
        
        Storage::put($path, $pdf);

        return $path;
    }

    /**
     * Generate filename for proof pack PDF
     */
    protected function generateProofPackFilename(string $title): string
    {
        $slug = Str::slug($title);
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "proof-pack_{$slug}_{$timestamp}.pdf";
    }

    /**
     * Get proof statistics for PDF analytics
     */
    public function getProofAnalytics(\Illuminate\Database\Eloquent\Collection $proofs): array
    {
        return [
            'total_proofs' => $proofs->count(),
            'total_views' => $proofs->sum('views_count'),
            'average_impact' => $proofs->avg('conversion_impact'),
            'featured_count' => $proofs->where('is_featured', true)->count(),
            'categories' => $proofs->groupBy('type')->map->count(),
            'total_assets' => $proofs->sum(function ($proof) {
                return $proof->assets->count();
            })
        ];
    }

    /**
     * Filter proof assets for PDF display
     */
    public function filterAssetsForPDF(\App\Models\Proof $proof, int $maxAssets = 3): \Illuminate\Database\Eloquent\Collection
    {
        return $proof->assets()
            ->where('processing_status', 'completed')
            ->where('show_in_gallery', true)
            ->orderBy('is_primary', 'desc')
            ->orderBy('sort_order')
            ->limit($maxAssets)
            ->get();
    }

    /**
     * Generate asset URLs for PDF (absolute paths)
     */
    public function getAssetUrlsForPDF(\App\Models\ProofAsset $asset): array
    {
        $baseUrl = config('app.url');
        
        return [
            'original' => $baseUrl . '/storage/' . $asset->file_path,
            'thumbnail' => $asset->thumbnail_path ? 
                $baseUrl . '/storage/' . $asset->thumbnail_path : 
                $baseUrl . '/storage/' . $asset->file_path
        ];
    }

    /**
     * Check if proofs should be included in PDF based on model settings
     */
    public function shouldIncludeProofs($model): bool
    {
        // Check if model has proof-related settings or if company has proof integration enabled
        if (method_exists($model, 'includeProofsInPDF')) {
            return $model->includeProofsInPDF();
        }

        // Default to true if proofs exist for the company
        return Proof::where('company_id', $model->company_id)
            ->where('status', 'active')
            ->where('show_in_pdf', true)
            ->exists();
    }

    /**
     * Get proof section title based on document type
     */
    public function getProofSectionTitle(string $type = 'quotation'): string
    {
        return match ($type) {
            'quotation' => 'Why Choose Us',
            'invoice' => 'Our Credentials',
            'assessment' => 'Our Expertise & Credentials',
            'proof-pack' => 'Social Proof Portfolio',
            default => 'Our Proven Track Record'
        };
    }

    /**
     * Generate PDF for an assessment report
     */
    public function generateAssessmentPDF(Assessment $assessment): string
    {
        // Load assessment with all necessary relationships
        $assessment->load([
            'sections.items',
            'photos',
            'lead',
            'company',
            'assignedTo',
            'serviceTemplate',
            'proofs.assets'
        ]);

        // Render the HTML view for the PDF
        $html = view('pdf.assessment', compact('assessment'))->render();

        // Generate PDF using Browsershot with assessment-specific settings
        $browsershot = Browsershot::html($html)
            ->format('A4')
            ->margins(12, 12, 12, 12)  // Slightly smaller margins for more content
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->timeout(90); // Longer timeout for image-heavy assessments

        $pdf = $this->configureBrowsershot($browsershot)->pdf();

        // Store the PDF
        $filename = $this->generateAssessmentPDFFilename($assessment);
        $path = "pdfs/assessments/{$assessment->company_id}/{$filename}";

        Storage::put($path, $pdf);

        // Update assessment with PDF path
        $assessment->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * Generate a unique filename for the assessment PDF
     */
    protected function generateAssessmentPDFFilename(Assessment $assessment): string
    {
        $code = Str::slug($assessment->assessment_code);
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "assessment_{$code}_{$timestamp}.pdf";
    }

    /**
     * Check if PDF exists for an assessment
     */
    public function assessmentPdfExists(Assessment $assessment): bool
    {
        return $assessment->pdf_path && Storage::exists($assessment->pdf_path);
    }

    /**
     * Delete PDF file for an assessment
     */
    public function deleteAssessmentPDF(Assessment $assessment): bool
    {
        if ($assessment->pdf_path && Storage::exists($assessment->pdf_path)) {
            Storage::delete($assessment->pdf_path);
            $assessment->update(['pdf_path' => null]);
            return true;
        }

        return false;
    }

    /**
     * Generate assessment summary report (compact version)
     */
    public function generateAssessmentSummaryPDF(Assessment $assessment): string
    {
        // Load assessment with essential relationships only
        $assessment->load([
            'sections' => function ($query) {
                $query->where('status', 'completed')->orderBy('sort_order');
            },
            'company',
            'assignedTo',
            'serviceTemplate'
        ]);

        // Render the summary view
        $html = view('pdf.assessment-summary', compact('assessment'))->render();

        // Generate PDF with summary-specific settings
        $browsershot = Browsershot::html($html)
            ->format('A4')
            ->margins(15, 15, 15, 15)
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->timeout(60);

        $pdf = $this->configureBrowsershot($browsershot)->pdf();

        // Store the PDF with summary suffix
        $filename = $this->generateAssessmentSummaryFilename($assessment);
        $path = "pdfs/assessments/{$assessment->company_id}/summaries/{$filename}";

        Storage::put($path, $pdf);

        return $path;
    }

    /**
     * Generate filename for assessment summary PDF
     */
    protected function generateAssessmentSummaryFilename(Assessment $assessment): string
    {
        $code = Str::slug($assessment->assessment_code);
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "assessment_summary_{$code}_{$timestamp}.pdf";
    }

    /**
     * Generate service-specific assessment report
     */
    public function generateServiceSpecificAssessmentPDF(Assessment $assessment, array $options = []): string
    {
        // Default options
        $options = array_merge([
            'include_photos' => true,
            'include_recommendations' => true,
            'include_risk_analysis' => true,
            'include_compliance' => true,
            'watermark' => null,
            'format' => 'detailed'
        ], $options);

        // Load assessment with service-specific relationships
        $assessment->load([
            'sections.items',
            'photos' => function ($query) use ($options) {
                if ($options['include_photos']) {
                    $query->where('processing_status', 'completed')
                          ->orderBy('photo_type')
                          ->orderBy('sort_order');
                }
            },
            'lead',
            'company',
            'assignedTo',
            'serviceTemplate'
        ]);

        // Select template based on service type
        $template = $this->getServiceSpecificTemplate($assessment->service_type, $options['format']);

        // Render the HTML view
        $html = view($template, compact('assessment', 'options'))->render();

        // Generate PDF with service-specific settings
        $browsershot = Browsershot::html($html)
            ->format('A4')
            ->margins(10, 10, 10, 10) // Tighter margins for service reports
            ->showBackground()
            ->waitUntilNetworkIdle()
            ->timeout(120); // Extended timeout for complex reports

        $pdf = $this->configureBrowsershot($browsershot)->pdf();

        // Store the PDF with service-specific path
        $filename = $this->generateServiceSpecificFilename($assessment, $options['format']);
        $path = "pdfs/assessments/{$assessment->company_id}/{$assessment->service_type}/{$filename}";

        Storage::put($path, $pdf);

        return $path;
    }

    /**
     * Get service-specific PDF template
     */
    protected function getServiceSpecificTemplate(string $serviceType, string $format = 'detailed'): string
    {
        $templateMap = [
            'waterproofing' => [
                'detailed' => 'pdf.assessment.waterproofing-detailed',
                'summary' => 'pdf.assessment.waterproofing-summary',
                'compliance' => 'pdf.assessment.waterproofing-compliance'
            ],
            'painting' => [
                'detailed' => 'pdf.assessment.painting-detailed',
                'summary' => 'pdf.assessment.painting-summary',
                'color_consultation' => 'pdf.assessment.painting-color'
            ],
            'sports_court' => [
                'detailed' => 'pdf.assessment.sports-court-detailed',
                'summary' => 'pdf.assessment.sports-court-summary',
                'maintenance' => 'pdf.assessment.sports-court-maintenance'
            ],
            'industrial' => [
                'detailed' => 'pdf.assessment.industrial-detailed',
                'summary' => 'pdf.assessment.industrial-summary',
                'safety' => 'pdf.assessment.industrial-safety',
                'compliance' => 'pdf.assessment.industrial-compliance'
            ]
        ];

        // Return specific template or fall back to generic
        return $templateMap[$serviceType][$format] ?? 'pdf.assessment';
    }

    /**
     * Generate filename for service-specific assessment PDF
     */
    protected function generateServiceSpecificFilename(Assessment $assessment, string $format): string
    {
        $code = Str::slug($assessment->assessment_code);
        $service = Str::slug($assessment->service_type);
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "assessment_{$service}_{$format}_{$code}_{$timestamp}.pdf";
    }

    /**
     * Get assessment photos grouped by type for PDF display
     */
    public function getAssessmentPhotosForPDF(Assessment $assessment, int $maxPerType = 4): array
    {
        $photos = $assessment->photos()
            ->where('processing_status', 'completed')
            ->orderBy('photo_type')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('photo_type');

        // Limit photos per type to prevent PDF bloat
        return $photos->map(function ($typePhotos) use ($maxPerType) {
            return $typePhotos->take($maxPerType);
        })->toArray();
    }

    /**
     * Generate assessment analytics for PDF inclusion
     */
    public function getAssessmentAnalyticsForPDF(Assessment $assessment): array
    {
        $sections = $assessment->sections;
        $totalSections = $sections->count();
        $completedSections = $sections->where('status', 'completed')->count();
        $totalScore = $sections->sum('current_score');
        $maxPossibleScore = $sections->sum('max_score');

        return [
            'completion_rate' => $totalSections > 0 ? round(($completedSections / $totalSections) * 100, 1) : 0,
            'overall_score' => $maxPossibleScore > 0 ? round(($totalScore / $maxPossibleScore) * 100, 1) : 0,
            'total_sections' => $totalSections,
            'completed_sections' => $completedSections,
            'risk_level' => $this->calculateOverallRiskLevel($assessment),
            'quality_rating' => $this->calculateOverallQuality($assessment),
            'estimated_duration' => $assessment->estimated_duration,
            'actual_duration' => $sections->sum('actual_time_spent'),
            'recommendations_count' => $sections->whereNotNull('recommendations')->count(),
            'photos_count' => $assessment->photos()->where('processing_status', 'completed')->count(),
        ];
    }

    /**
     * Calculate overall risk level for assessment
     */
    protected function calculateOverallRiskLevel(Assessment $assessment): string
    {
        $riskScores = $assessment->sections()
            ->whereNotNull('risk_level')
            ->pluck('risk_level')
            ->map(function ($level) {
                return match ($level) {
                    'low' => 1,
                    'medium' => 2,
                    'high' => 3,
                    'critical' => 4,
                    default => 2
                };
            });

        if ($riskScores->isEmpty()) {
            return 'medium';
        }

        $averageRisk = $riskScores->avg();
        
        return match (true) {
            $averageRisk >= 3.5 => 'critical',
            $averageRisk >= 2.5 => 'high',
            $averageRisk >= 1.5 => 'medium',
            default => 'low'
        };
    }

    /**
     * Calculate overall quality rating for assessment
     */
    protected function calculateOverallQuality(Assessment $assessment): string
    {
        $qualityScores = $assessment->sections()
            ->whereNotNull('quality_rating')
            ->pluck('quality_rating')
            ->map(function ($rating) {
                return match ($rating) {
                    'excellent' => 5,
                    'good' => 4,
                    'fair' => 3,
                    'poor' => 2,
                    'critical' => 1,
                    default => 3
                };
            });

        if ($qualityScores->isEmpty()) {
            return 'fair';
        }

        $averageQuality = $qualityScores->avg();

        return match (true) {
            $averageQuality >= 4.5 => 'excellent',
            $averageQuality >= 3.5 => 'good',
            $averageQuality >= 2.5 => 'fair',
            $averageQuality >= 1.5 => 'poor',
            default => 'critical'
        };
    }

    /**
     * Generate PDF using DomPDF as fallback when Browsershot fails
     */
    protected function generatePDFWithDomPDF(string $html): string
    {
        // Import DomPDF
        $dompdf = new \Dompdf\Dompdf();

        // Set options for better rendering
        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultPaperSize', 'A4');
        $dompdf->setOptions($options);

        // Load HTML content
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Return the PDF content as string
        return $dompdf->output();
    }
}



