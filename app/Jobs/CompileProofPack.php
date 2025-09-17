<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Proof;
use App\Models\ProofAsset;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\Lead;
use App\Services\PDFService;
use App\Services\WebhookEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CompileProofPack implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $companyId;
    public array $triggerData;
    public int $timeout = 300; // 5 minutes
    public int $tries = 3;
    private $startTime;

    /**
     * Create a new job instance.
     */
    public function __construct(int $companyId, array $triggerData = [])
    {
        $this->companyId = $companyId;
        $this->triggerData = $triggerData;
        
        // Set queue priority based on trigger event
        $this->onQueue($this->determineQueue());
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->startTime = now();
        
        Log::info('Starting proof pack compilation', [
            'company_id' => $this->companyId,
            'trigger_event' => $this->triggerData['trigger_event'] ?? 'manual',
        ]);

        try {
            // Load company
            $company = Company::find($this->companyId);
            if (!$company) {
                throw new \Exception("Company not found: {$this->companyId}");
            }

            // Determine compilation strategy based on trigger event
            $strategy = $this->determineCompilationStrategy();
            
            // Compile proofs based on strategy
            $compiledProofs = $this->compileProofs($company, $strategy);
            
            // Generate proof pack assets if needed
            if ($this->shouldGenerateAssets($strategy)) {
                $this->generateProofPackAssets($company, $compiledProofs);
            }
            
            // Update proof analytics
            $this->updateProofAnalytics($compiledProofs);
            
            // Generate proof pack PDF if requested
            if ($this->shouldGeneratePDF($strategy)) {
                $this->generateProofPackPDF($company, $compiledProofs);
            }

            // Dispatch webhook for proof pack generation
            $this->dispatchProofPackWebhook($company, $compiledProofs, $strategy);

            Log::info('Proof pack compilation completed successfully', [
                'company_id' => $this->companyId,
                'proofs_compiled' => count($compiledProofs),
                'trigger_event' => $this->triggerData['trigger_event'] ?? 'manual',
            ]);

        } catch (\Exception $e) {
            Log::error('Proof pack compilation failed', [
                'company_id' => $this->companyId,
                'trigger_event' => $this->triggerData['trigger_event'] ?? 'manual',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Determine compilation strategy based on trigger event
     */
    private function determineCompilationStrategy(): array
    {
        $triggerEvent = $this->triggerData['trigger_event'] ?? 'comprehensive';
        
        return match($triggerEvent) {
            'quotation_accepted' => [
                'focus' => 'success_stories',
                'types' => ['professional_proof', 'performance_proof'],
                'scope' => 'recent_wins',
                'priority' => 'high',
                'generate_pdf' => false,
                'auto_publish' => true,
            ],
            'invoice_paid' => [
                'focus' => 'trust_building',
                'types' => ['trust_proof', 'performance_proof'],
                'scope' => 'payment_success',
                'priority' => 'medium',
                'generate_pdf' => false,
                'auto_publish' => true,
            ],
            'project_completed' => [
                'focus' => 'comprehensive',
                'types' => ['visual_proof', 'social_proof', 'professional_proof', 'performance_proof', 'trust_proof'],
                'scope' => 'full_project_cycle',
                'priority' => 'high',
                'generate_pdf' => true,
                'auto_publish' => false, // Requires review
            ],
            default => [
                'focus' => 'maintenance',
                'types' => ['professional_proof', 'trust_proof'],
                'scope' => 'active_proofs',
                'priority' => 'low',
                'generate_pdf' => false,
                'auto_publish' => false,
            ],
        };
    }

    /**
     * Compile proofs based on strategy
     */
    private function compileProofs(Company $company, array $strategy): array
    {
        $compiledProofs = [];
        
        // Get base proof query
        $baseQuery = Proof::where('company_id', $company->id)
                          ->where('status', 'active');

        // Apply type filters if specified
        if (!empty($strategy['types'])) {
            $baseQuery->whereIn('type', $strategy['types']);
        }

        // Apply scope-specific filters
        switch ($strategy['scope']) {
            case 'recent_wins':
                $proofs = $baseQuery->where('created_at', '>=', now()->subDays(30))
                                   ->orderBy('created_at', 'desc')
                                   ->limit(10)
                                   ->get();
                break;
                
            case 'payment_success':
                $proofs = $baseQuery->where('type', 'trust_proof')
                                   ->whereJsonContains('metadata->payment_completed', true)
                                   ->orderBy('created_at', 'desc')
                                   ->limit(5)
                                   ->get();
                break;
                
            case 'full_project_cycle':
                // Get comprehensive proof collection
                $proofs = $this->getComprehensiveProofs($company, $strategy);
                break;
                
            default:
                $proofs = $baseQuery->where('is_featured', true)
                                   ->orderBy('sort_order')
                                   ->limit(15)
                                   ->get();
        }

        // Process each proof
        foreach ($proofs as $proof) {
            $compiledProof = $this->processProofForCompilation($proof, $strategy);
            if ($compiledProof) {
                $compiledProofs[] = $compiledProof;
            }
        }

        return $compiledProofs;
    }

    /**
     * Get comprehensive proofs for full project cycle
     */
    private function getComprehensiveProofs(Company $company, array $strategy): \Illuminate\Database\Eloquent\Collection
    {
        $quotationId = $this->triggerData['quotation_id'] ?? null;
        $invoiceId = $this->triggerData['invoice_id'] ?? null;
        
        // Start with project-specific proofs
        $projectProofs = collect();
        
        if ($quotationId) {
            $projectProofs = $projectProofs->concat(
                Proof::where('company_id', $company->id)
                     ->where('scope_type', 'App\\Models\\Quotation')
                     ->where('scope_id', $quotationId)
                     ->get()
            );
        }
        
        if ($invoiceId) {
            $projectProofs = $projectProofs->concat(
                Proof::where('company_id', $company->id)
                     ->where('scope_type', 'App\\Models\\Invoice')
                     ->where('scope_id', $invoiceId)
                     ->get()
            );
        }
        
        // Add featured company proofs
        $companyProofs = Proof::where('company_id', $company->id)
                              ->where('is_featured', true)
                              ->where('status', 'active')
                              ->orderBy('sort_order')
                              ->limit(10)
                              ->get();
        
        return $projectProofs->concat($companyProofs)->unique('id');
    }

    /**
     * Process individual proof for compilation
     */
    private function processProofForCompilation(Proof $proof, array $strategy): ?array
    {
        try {
            // Load proof with relationships
            $proof->load(['assets', 'views', 'scope']);
            
            // Basic proof data
            $compiledProof = [
                'id' => $proof->id,
                'uuid' => $proof->uuid,
                'type' => $proof->type,
                'title' => $proof->title,
                'description' => $proof->description,
                'metadata' => $proof->metadata ?? [],
                'visibility' => $proof->visibility,
                'is_featured' => $proof->is_featured,
                'created_at' => $proof->created_at,
                'assets' => [],
                'analytics' => [],
            ];

            // Process assets
            foreach ($proof->assets as $asset) {
                if ($asset->status === 'processed') {
                    $compiledProof['assets'][] = [
                        'id' => $asset->id,
                        'type' => $asset->file_type,
                        'filename' => $asset->filename,
                        'file_path' => $asset->file_path,
                        'thumbnail_path' => $asset->thumbnail_path,
                        'is_primary' => $asset->is_primary,
                        'metadata' => $asset->metadata ?? [],
                    ];
                }
            }

            // Add analytics if requested
            if ($strategy['priority'] === 'high') {
                $compiledProof['analytics'] = [
                    'view_count' => $proof->view_count,
                    'click_count' => $proof->click_count,
                    'engagement_rate' => $proof->getEngagementRate(),
                    'conversion_impact' => $proof->conversion_impact,
                    'last_viewed' => $proof->views()->latest()->first()?->viewed_at,
                ];
            }

            // Add scope-specific data
            if ($proof->scope) {
                $compiledProof['scope_data'] = $this->extractScopeData($proof->scope);
            }

            return $compiledProof;

        } catch (\Exception $e) {
            Log::warning('Failed to process proof for compilation', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Extract relevant data from proof scope
     */
    private function extractScopeData($scope): array
    {
        if ($scope instanceof Quotation) {
            return [
                'type' => 'quotation',
                'number' => $scope->number,
                'customer_name' => $scope->customer_name,
                'total' => $scope->total,
                'status' => $scope->status,
            ];
        }
        
        if ($scope instanceof Invoice) {
            return [
                'type' => 'invoice',
                'number' => $scope->number,
                'total' => $scope->total,
                'status' => $scope->status,
                'customer_name' => $scope->quotation?->customer_name,
            ];
        }
        
        if ($scope instanceof Lead) {
            return [
                'type' => 'lead',
                'customer_name' => $scope->customer_name,
                'status' => $scope->status,
                'source' => $scope->source,
            ];
        }

        return [];
    }

    /**
     * Generate proof pack assets
     */
    private function generateProofPackAssets(Company $company, array $compiledProofs): void
    {
        // Create summary asset for the proof pack
        $summary = [
            'company_name' => $company->name,
            'compilation_date' => now(),
            'trigger_event' => $this->triggerData['trigger_event'] ?? 'manual',
            'total_proofs' => count($compiledProofs),
            'proof_types' => array_count_values(array_column($compiledProofs, 'type')),
            'total_assets' => array_sum(array_map(fn($p) => count($p['assets']), $compiledProofs)),
        ];

        // Store summary as JSON
        $summaryPath = "proof_packs/{$company->id}/compilations/" . now()->format('Y-m-d_H-i-s') . '_summary.json';
        Storage::put($summaryPath, json_encode($summary, JSON_PRETTY_PRINT));

        Log::info('Generated proof pack summary asset', [
            'company_id' => $company->id,
            'summary_path' => $summaryPath,
            'total_proofs' => $summary['total_proofs'],
        ]);
    }

    /**
     * Update proof analytics
     */
    private function updateProofAnalytics(array $compiledProofs): void
    {
        foreach ($compiledProofs as $compiledProof) {
            // Increment compilation count
            Proof::where('id', $compiledProof['id'])
                 ->increment('view_count'); // Using view_count as compilation count proxy

            // Update metadata with compilation info
            $proof = Proof::find($compiledProof['id']);
            if ($proof) {
                $metadata = $proof->metadata ?? [];
                $metadata['last_compilation'] = now();
                $metadata['compilation_count'] = ($metadata['compilation_count'] ?? 0) + 1;
                $metadata['last_trigger_event'] = $this->triggerData['trigger_event'] ?? 'manual';
                
                $proof->update(['metadata' => $metadata]);
            }
        }
    }

    /**
     * Generate proof pack PDF
     */
    private function generateProofPackPDF(Company $company, array $compiledProofs): void
    {
        try {
            $pdfService = app(PDFService::class);
            
            $options = [
                'title' => "Proof Pack - {$company->name}",
                'compilation_date' => now(),
                'trigger_event' => $this->triggerData['trigger_event'] ?? 'manual',
                'watermark' => 'AUTO-GENERATED',
                'include_analytics' => true,
            ];

            // Convert compiled proofs to the format expected by PDFService
            $proofs = collect($compiledProofs)->map(function ($compiledProof) {
                return Proof::find($compiledProof['id']);
            })->filter();

            $pdfPath = $pdfService->generateProofPackPDF($proofs, $options);

            Log::info('Generated automated proof pack PDF', [
                'company_id' => $company->id,
                'pdf_path' => $pdfPath,
                'proof_count' => $proofs->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate automated proof pack PDF', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the entire job for PDF generation issues
        }
    }

    /**
     * Determine appropriate queue
     */
    private function determineQueue(): string
    {
        $triggerEvent = $this->triggerData['trigger_event'] ?? 'default';
        
        return match($triggerEvent) {
            'quotation_accepted' => 'high',
            'project_completed' => 'high',
            'invoice_paid' => 'notifications',
            default => 'default',
        };
    }

    /**
     * Should generate assets for this compilation
     */
    private function shouldGenerateAssets(array $strategy): bool
    {
        return $strategy['priority'] === 'high' || $strategy['generate_pdf'];
    }

    /**
     * Should generate PDF for this compilation
     */
    private function shouldGeneratePDF(array $strategy): bool
    {
        return $strategy['generate_pdf'] ?? false;
    }

    /**
     * Dispatch webhook for proof pack generation
     */
    private function dispatchProofPackWebhook(Company $company, array $compiledProofs, array $strategy): void
    {
        try {
            $webhookService = app(WebhookEventService::class);
            
            // Calculate proof counts by type
            $proofCounts = [];
            $totalAssets = 0;
            $featuredCount = 0;
            
            foreach ($compiledProofs as $proof) {
                $proofType = $proof->type;
                $proofCounts[$proofType] = ($proofCounts[$proofType] ?? 0) + 1;
                
                if ($proof->is_featured) {
                    $featuredCount++;
                }
                
                $totalAssets += $proof->assets->count();
            }
            
            $proofPackData = [
                'pack_type' => $strategy['pack_type'] ?? 'comprehensive',
                'trigger_event' => $this->triggerData['trigger_event'] ?? 'manual',
                'compilation_strategy' => $strategy,
                'proofs_included' => collect($compiledProofs)->map(function ($proof) {
                    return [
                        'id' => $proof->id,
                        'uuid' => $proof->uuid,
                        'type' => $proof->type,
                        'title' => $proof->title,
                        'is_featured' => $proof->is_featured,
                        'asset_count' => $proof->assets->count(),
                    ];
                })->toArray(),
                'proof_counts' => $proofCounts,
                'total_proofs' => count($compiledProofs),
                'featured_proofs' => $featuredCount,
                'asset_count' => $totalAssets,
                'compilation_time' => $this->startTime ? now()->diffInSeconds($this->startTime) : null,
                'context' => $this->triggerData,
            ];
            
            $webhookService->proofPackGenerated($this->companyId, $proofPackData);
            
        } catch (\Exception $e) {
            Log::error('Failed to dispatch proof pack webhook', [
                'company_id' => $this->companyId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle failed job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('CompileProofPack job failed', [
            'company_id' => $this->companyId,
            'trigger_data' => $this->triggerData,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}