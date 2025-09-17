<?php

namespace App\Listeners;

use App\Events\ProjectCompleted;
use App\Jobs\CompileProofPack;
use App\Jobs\RequestReviewJob;
use App\Models\Proof;
use App\Models\CaseStudy;
use App\Models\KPI;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleProjectCompleted implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ProjectCompleted $event): void
    {
        try {
            Log::info('Processing ProjectCompleted event', [
                'quotation_id' => $event->quotation?->id,
                'invoice_id' => $event->invoice?->id,
                'lead_id' => $event->lead?->id,
                'completion_date' => $event->metadata['completion_date'],
            ]);

            // Create comprehensive project completion proof
            $this->createProjectCompletionProof($event);

            // Update all related KPIs
            $this->updateProjectKPIs($event);

            // Update or create case study
            $this->updateCaseStudy($event);

            // Queue comprehensive proof pack compilation
            CompileProofPack::dispatch($event->quotation?->company_id ?? $event->lead?->company_id, [
                'trigger_event' => 'project_completed',
                'quotation_id' => $event->quotation?->id,
                'invoice_id' => $event->invoice?->id,
                'lead_id' => $event->lead?->id,
                'proof_pack_data' => $event->getProofPackData(),
                'comprehensive' => true,
            ])->delay(now()->addMinutes(15));

            // Schedule multiple review requests
            $this->scheduleReviewRequests($event);

            Log::info('ProjectCompleted event processed successfully', [
                'quotation_id' => $event->quotation?->id,
                'proofs_generated' => true,
                'kpis_updated' => true,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process ProjectCompleted event', [
                'quotation_id' => $event->quotation?->id,
                'invoice_id' => $event->invoice?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Create comprehensive project completion proof
     */
    private function createProjectCompletionProof(ProjectCompleted $event): void
    {
        $proofPackData = $event->getProofPackData();
        $companyId = $event->quotation?->company_id ?? $event->lead?->company_id;

        // Main project completion proof
        $mainProof = Proof::create([
            'company_id' => $companyId,
            'scope_type' => $event->quotation ? 'App\\Models\\Quotation' : 'App\\Models\\Lead',
            'scope_id' => $event->quotation?->id ?? $event->lead?->id,
            'type' => 'performance_proof',
            'title' => "Project Completion: {$proofPackData['project_overview']['title']}",
            'description' => $this->buildCompletionDescription($proofPackData),
            'metadata' => array_merge($proofPackData, [
                'auto_generated' => true,
                'source_event' => 'project_completed',
                'completion_verification' => [
                    'timeline_met' => $proofPackData['customer_satisfaction']['on_time_completion'],
                    'budget_met' => $proofPackData['customer_satisfaction']['budget_adherence'],
                    'quality_delivered' => $proofPackData['customer_satisfaction']['quality_delivered'],
                ],
            ]),
            'visibility' => 'public',
            'status' => 'active',
            'is_featured' => $this->shouldFeatureProject($proofPackData),
            'published_at' => now(),
            'created_by' => $event->completedBy?->id ?? $event->quotation?->created_by ?? $event->lead?->assigned_to,
        ]);

        // Create category-specific proofs
        $this->createCategoryProofs($event, $proofPackData, $companyId);

        Log::info('Created project completion proofs', [
            'main_proof_id' => $mainProof->id,
            'project_title' => $proofPackData['project_overview']['title'],
        ]);
    }

    /**
     * Create category-specific proofs
     */
    private function createCategoryProofs(ProjectCompleted $event, array $proofPackData, int $companyId): void
    {
        $proofCategories = $proofPackData['proof_categories'];

        // Performance proof for metrics
        if (!empty($proofCategories['performance_proof'])) {
            Proof::create([
                'company_id' => $companyId,
                'scope_type' => $event->quotation ? 'App\\Models\\Quotation' : 'App\\Models\\Lead',
                'scope_id' => $event->quotation?->id ?? $event->lead?->id,
                'type' => 'performance_proof',
                'title' => "Performance Metrics: {$proofPackData['project_overview']['title']}",
                'description' => $this->buildPerformanceDescription($proofCategories['performance_proof']),
                'metadata' => [
                    'performance_data' => $proofCategories['performance_proof'],
                    'financial_performance' => $proofPackData['financial_success'],
                    'auto_generated' => true,
                ],
                'visibility' => 'public',
                'status' => 'active',
                'published_at' => now(),
                'created_by' => $event->completedBy?->id ?? $event->quotation?->created_by,
            ]);
        }

        // Trust proof for payment/reliability
        if (!empty($proofCategories['trust_proof'])) {
            Proof::create([
                'company_id' => $companyId,
                'scope_type' => $event->invoice ? 'App\\Models\\Invoice' : ($event->quotation ? 'App\\Models\\Quotation' : 'App\\Models\\Lead'),
                'scope_id' => $event->invoice?->id ?? $event->quotation?->id ?? $event->lead?->id,
                'type' => 'trust_proof',
                'title' => "Reliable Delivery: {$proofPackData['project_overview']['customer']['name']}",
                'description' => $this->buildTrustDescription($proofCategories['trust_proof']),
                'metadata' => [
                    'trust_indicators' => $proofCategories['trust_proof'],
                    'customer_info' => $proofPackData['project_overview']['customer'],
                    'auto_generated' => true,
                ],
                'visibility' => 'public',
                'status' => 'active',
                'published_at' => now(),
                'created_by' => $event->completedBy?->id ?? $event->quotation?->created_by,
            ]);
        }
    }

    /**
     * Update comprehensive project KPIs
     */
    private function updateProjectKPIs(ProjectCompleted $event): void
    {
        $proofPackData = $event->getProofPackData();
        $companyId = $event->quotation?->company_id ?? $event->lead?->company_id;

        // Project completion rate KPI
        $this->updateProjectCompletionKPI($companyId, $proofPackData);

        // Team efficiency KPI
        if ($teamInfo = $proofPackData['team_performance']) {
            $this->updateTeamEfficiencyKPI($companyId, $teamInfo, $proofPackData);
        }

        // Customer satisfaction indicators
        $this->updateCustomerSatisfactionKPI($companyId, $proofPackData);

        // Financial performance KPI
        if ($event->quotation && $event->invoice) {
            $this->updateFinancialPerformanceKPI($companyId, $proofPackData);
        }
    }

    /**
     * Update or create comprehensive case study
     */
    private function updateCaseStudy(ProjectCompleted $event): void
    {
        $proofPackData = $event->getProofPackData();
        $companyId = $event->quotation?->company_id ?? $event->lead?->company_id;

        // Find existing case study or create new one
        $caseStudy = null;
        if ($event->quotation) {
            $caseStudy = \App\Models\CaseStudy::where('company_id', $companyId)
                ->whereJsonContains('metadata->quotation_id', $event->quotation->id)
                ->first();
        }

        if (!$caseStudy && $this->qualifiesForCaseStudy($proofPackData)) {
            // Create comprehensive case study
            $caseStudy = CaseStudy::create([
                'company_id' => $companyId,
                'title' => "Complete Case Study: {$proofPackData['project_overview']['title']}",
                'client_name' => $proofPackData['project_overview']['customer']['name'],
                'industry' => $proofPackData['project_overview']['customer']['segment'],
                'challenge' => $this->buildChallengeDescription($proofPackData),
                'solution' => $this->buildSolutionDescription($proofPackData),
                'results' => $this->buildResultsDescription($proofPackData),
                'project_value' => $proofPackData['financial_success']['quoted_value'],
                'completion_date' => $proofPackData['project_overview']['completion_date'],
                'team_members' => implode(', ', [
                    $proofPackData['team_performance']['lead_rep'] ?? 'Sales Team',
                    $proofPackData['team_performance']['team_name'] ?? 'Project Team'
                ]),
                'technologies_used' => $this->extractTechnologies($proofPackData),
                'metrics_before' => $this->buildBeforeMetrics($proofPackData),
                'metrics_after' => $this->buildAfterMetrics($proofPackData),
                'status' => 'completed',
                'featured' => $this->shouldFeatureProject($proofPackData),
                'metadata' => [
                    'quotation_id' => $event->quotation?->id,
                    'invoice_id' => $event->invoice?->id,
                    'lead_id' => $event->lead?->id,
                    'auto_generated' => true,
                    'comprehensive_data' => $proofPackData,
                    'completion_verified' => now(),
                ],
                'created_by' => $event->completedBy?->id ?? $event->quotation?->created_by,
            ]);
        } elseif ($caseStudy) {
            // Update existing case study with completion data
            $caseStudy->update([
                'results' => $this->buildResultsDescription($proofPackData),
                'metrics_after' => $this->buildAfterMetrics($proofPackData),
                'status' => 'completed',
                'metadata' => array_merge($caseStudy->metadata ?? [], [
                    'completion_data' => $proofPackData,
                    'completion_verified' => now(),
                ]),
            ]);
        }

        if ($caseStudy) {
            Log::info('Case study updated with project completion data', [
                'case_study_id' => $caseStudy->id,
                'project_title' => $proofPackData['project_overview']['title'],
            ]);
        }
    }

    /**
     * Schedule various review requests
     */
    private function scheduleReviewRequests(ProjectCompleted $event): void
    {
        $proofPackData = $event->getProofPackData();
        
        // Schedule testimonial request
        if ($event->quotation && !empty($event->quotation->customer_email)) {
            RequestReviewJob::dispatch($event->quotation, 'testimonial')
                ->delay(now()->addDays(2));
        }

        // Schedule case study approval request for high-value projects
        if ($this->qualifiesForCaseStudy($proofPackData)) {
            RequestReviewJob::dispatch($event->quotation ?? $event->lead, 'case_study_approval')
                ->delay(now()->addDays(5));
        }

        // Schedule photo/asset collection request
        RequestReviewJob::dispatch($event->quotation ?? $event->lead, 'asset_collection')
            ->delay(now()->addDays(1));
    }

    // Helper methods for building descriptions and data

    private function buildCompletionDescription(array $proofPackData): string
    {
        $overview = $proofPackData['project_overview'];
        $financial = $proofPackData['financial_success'];
        
        return "Successfully completed project '{$overview['title']}' for {$overview['customer']['name']}. " .
               "Project duration: {$overview['duration']} days. " .
               "Value delivered: RM " . number_format($financial['quoted_value'] ?? 0, 2) . ". " .
               "Customer segment: {$overview['customer']['segment']}. " .
               "Team performance: On-time delivery with quality standards met.";
    }

    private function buildPerformanceDescription(array $performanceData): string
    {
        return "Project performance metrics: " .
               "Timeline adherence: " . ($performanceData['timeline_met'] ? 'Yes' : 'No') . ", " .
               "Budget performance: {$performanceData['budget_adherence']}, " .
               "Efficiency metrics tracked and documented.";
    }

    private function buildTrustDescription(array $trustData): string
    {
        return "Trust indicators: " .
               "Payment completed: " . ($trustData['payment_completed'] ? 'Yes' : 'Pending') . ", " .
               "Warranty provided: " . ($trustData['warranty_provided'] ? 'Yes' : 'No') . ", " .
               "Insurance coverage: " . ($trustData['insurance_coverage'] ? 'Yes' : 'No') . ".";
    }

    private function buildChallengeDescription(array $proofPackData): string
    {
        $deliverables = $proofPackData['deliverables'] ?? [];
        if (empty($deliverables)) {
            return "Customer required comprehensive solution within specified timeline and budget.";
        }

        return "Customer needed: " . implode(', ', array_slice(array_column($deliverables, 'description'), 0, 3)) . 
               (count($deliverables) > 3 ? ' and ' . (count($deliverables) - 3) . ' additional services.' : '.');
    }

    private function buildSolutionDescription(array $proofPackData): string
    {
        $deliverables = $proofPackData['deliverables'] ?? [];
        $team = $proofPackData['team_performance'];
        
        return "Deployed {$team['team_name']} with {$team['team_size']} specialists to deliver: " .
               implode(', ', array_column($deliverables, 'description')) . 
               ". Lead by {$team['lead_rep']} with oversight from {$team['manager']}.";
    }

    private function buildResultsDescription(array $proofPackData): string
    {
        $financial = $proofPackData['financial_success'];
        $satisfaction = $proofPackData['customer_satisfaction'];
        
        return "Project completed successfully with {$satisfaction['budget_adherence']} budget performance. " .
               "Customer satisfaction: High. Payment terms: " . 
               ($satisfaction['payment_terms_met'] ? 'Met on schedule' : 'Completed') . ". " .
               "Total value delivered: RM " . number_format($financial['invoiced_value'] ?? $financial['quoted_value'] ?? 0, 2) . ".";
    }

    private function extractTechnologies(array $proofPackData): array
    {
        // Extract technology/service types from deliverables
        $deliverables = $proofPackData['deliverables'] ?? [];
        return array_unique(array_column($deliverables, 'category'));
    }

    private function buildBeforeMetrics(array $proofPackData): array
    {
        return [
            'project_status' => 'planning_phase',
            'customer_need' => 'identified',
            'solution_status' => 'proposed',
        ];
    }

    private function buildAfterMetrics(array $proofPackData): array
    {
        $satisfaction = $proofPackData['customer_satisfaction'];
        $financial = $proofPackData['financial_success'];
        
        return [
            'project_status' => 'completed',
            'customer_satisfaction' => 'high',
            'budget_performance' => $satisfaction['budget_adherence'],
            'timeline_performance' => $satisfaction['on_time_completion'] ? 'on_time' : 'completed',
            'payment_status' => $satisfaction['payment_terms_met'] ? 'received_on_time' : 'received',
            'value_delivered' => $financial['invoiced_value'] ?? $financial['quoted_value'],
        ];
    }

    private function shouldFeatureProject(array $proofPackData): bool
    {
        $financialValue = $proofPackData['financial_success']['quoted_value'] ?? 0;
        $customerSegment = $proofPackData['project_overview']['customer']['segment'] ?? '';
        
        return $financialValue >= 75000 || 
               in_array($customerSegment, ['Dealer', 'Contractor']) ||
               count($proofPackData['deliverables'] ?? []) >= 15;
    }

    private function qualifiesForCaseStudy(array $proofPackData): bool
    {
        return $this->shouldFeatureProject($proofPackData);
    }

    // KPI Update Methods
    private function updateProjectCompletionKPI(int $companyId, array $proofPackData): void
    {
        $kpi = KPI::updateOrCreate([
            'company_id' => $companyId,
            'scope_type' => 'App\\Models\\Company',
            'scope_id' => $companyId,
            'metric_name' => 'project_completion_rate',
            'period_type' => 'monthly',
            'period_start' => now()->startOfMonth(),
        ], [
            'current_value' => 100, // This is a successful completion
            'target_value' => 95,
            'unit' => 'percentage',
            'metadata' => [
                'last_completion' => now(),
                'completion_quality' => 'high',
            ],
        ]);

        $this->recalculateCompanyCompletionRate($kpi);
    }

    private function updateTeamEfficiencyKPI(int $companyId, array $teamInfo, array $proofPackData): void
    {
        $teamId = $this->extractTeamId($proofPackData);
        if (!$teamId) return;

        KPI::updateOrCreate([
            'company_id' => $companyId,
            'scope_type' => 'App\\Models\\Team',
            'scope_id' => $teamId,
            'metric_name' => 'team_efficiency_score',
            'period_type' => 'monthly',
            'period_start' => now()->startOfMonth(),
        ], [
            'current_value' => $this->calculateEfficiencyScore($proofPackData),
            'target_value' => 85,
            'unit' => 'score',
            'metadata' => [
                'project_duration' => $proofPackData['project_overview']['duration'],
                'budget_performance' => $proofPackData['customer_satisfaction']['budget_adherence'],
                'team_size' => $teamInfo['team_size'],
            ],
        ]);
    }

    private function updateCustomerSatisfactionKPI(int $companyId, array $proofPackData): void
    {
        $satisfactionScore = $this->calculateSatisfactionScore($proofPackData);
        
        KPI::updateOrCreate([
            'company_id' => $companyId,
            'scope_type' => 'App\\Models\\Company',
            'scope_id' => $companyId,
            'metric_name' => 'customer_satisfaction_score',
            'period_type' => 'monthly',
            'period_start' => now()->startOfMonth(),
        ], [
            'current_value' => $satisfactionScore,
            'target_value' => 90,
            'unit' => 'score',
            'metadata' => [
                'last_project_score' => $satisfactionScore,
                'completion_quality' => 'high',
            ],
        ]);
    }

    private function updateFinancialPerformanceKPI(int $companyId, array $proofPackData): void
    {
        $financial = $proofPackData['financial_success'];
        $profitMargin = $financial['profit_margin'] ?? 20; // Default assumption
        
        KPI::updateOrCreate([
            'company_id' => $companyId,
            'scope_type' => 'App\\Models\\Company',
            'scope_id' => $companyId,
            'metric_name' => 'project_profit_margin',
            'period_type' => 'monthly',
            'period_start' => now()->startOfMonth(),
        ], [
            'current_value' => $profitMargin,
            'target_value' => 25,
            'unit' => 'percentage',
            'metadata' => [
                'project_value' => $financial['invoiced_value'],
                'budget_adherence' => $proofPackData['customer_satisfaction']['budget_adherence'],
            ],
        ]);
    }

    private function extractTeamId(array $proofPackData): ?int
    {
        // This would need to be extracted from the quotation or lead data
        return null; // Implementation depends on data structure
    }

    private function calculateEfficiencyScore(array $proofPackData): int
    {
        $score = 70; // Base score

        // Bonus for on-time delivery
        if ($proofPackData['customer_satisfaction']['on_time_completion']) {
            $score += 15;
        }

        // Bonus for budget adherence
        $budgetPerformance = $proofPackData['customer_satisfaction']['budget_adherence'];
        if ($budgetPerformance === 'excellent') $score += 15;
        elseif ($budgetPerformance === 'good') $score += 10;
        elseif ($budgetPerformance === 'fair') $score += 5;

        return min($score, 100);
    }

    private function calculateSatisfactionScore(array $proofPackData): int
    {
        $score = 75; // Base satisfaction

        if ($proofPackData['customer_satisfaction']['on_time_completion']) $score += 10;
        if ($proofPackData['customer_satisfaction']['budget_adherence'] === 'excellent') $score += 15;
        if ($proofPackData['customer_satisfaction']['quality_delivered']) $score += 10;

        return min($score, 100);
    }

    private function recalculateCompanyCompletionRate(KPI $kpi): void
    {
        // Recalculate based on all completed projects this period
        $totalProjects = \App\Models\Invoice::where('company_id', $kpi->company_id)
            ->where('created_at', '>=', $kpi->period_start)
            ->count();

        $completedProjects = \App\Models\Invoice::where('company_id', $kpi->company_id)
            ->where('status', 'PAID')
            ->where('created_at', '>=', $kpi->period_start)
            ->count();

        if ($totalProjects > 0) {
            $rate = ($completedProjects / $totalProjects) * 100;
            $kpi->update(['current_value' => round($rate, 2)]);
        }
    }

    /**
     * Handle failed jobs
     */
    public function failed(ProjectCompleted $event, $exception): void
    {
        Log::error('ProjectCompleted event listener failed', [
            'quotation_id' => $event->quotation?->id,
            'invoice_id' => $event->invoice?->id,
            'error' => $exception->getMessage(),
        ]);
    }
}