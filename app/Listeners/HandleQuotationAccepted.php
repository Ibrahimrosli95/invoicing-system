<?php

namespace App\Listeners;

use App\Events\QuotationAccepted;
use App\Jobs\CompileProofPack;
use App\Jobs\RequestReviewJob;
use App\Models\Proof;
use App\Models\CaseStudy;
use App\Models\KPI;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleQuotationAccepted implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(QuotationAccepted $event): void
    {
        try {
            Log::info('Processing QuotationAccepted event', [
                'quotation_id' => $event->quotation->id,
                'quotation_number' => $event->quotation->number,
                'customer' => $event->quotation->customer_name,
                'value' => $event->quotation->total,
            ]);

            // Create success story proof entry
            $this->createSuccessStoryProof($event);

            // Update performance KPIs
            $this->updatePerformanceKPIs($event);

            // If qualifies, create case study entry
            if ($event->qualifiesForCaseStudy()) {
                $this->createCaseStudyEntry($event);
            }

            // Queue proof pack compilation
            CompileProofPack::dispatch($event->quotation->company_id, [
                'trigger_event' => 'quotation_accepted',
                'quotation_id' => $event->quotation->id,
                'proof_data' => $event->getProofData(),
            ])->delay(now()->addMinutes(5));

            // Schedule testimonial collection for later
            RequestReviewJob::dispatch($event->quotation, 'testimonial')
                ->delay(now()->addDays(7)); // Ask for testimonial in 1 week

            Log::info('QuotationAccepted event processed successfully', [
                'quotation_id' => $event->quotation->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process QuotationAccepted event', [
                'quotation_id' => $event->quotation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Re-throw to trigger retry mechanism
            throw $e;
        }
    }

    /**
     * Create a success story proof entry
     */
    private function createSuccessStoryProof(QuotationAccepted $event): void
    {
        $proofData = $event->getProofData();

        $proof = Proof::create([
            'company_id' => $event->quotation->company_id,
            'scope_type' => 'App\\Models\\Quotation',
            'scope_id' => $event->quotation->id,
            'type' => 'professional_proof',
            'title' => $proofData['title'],
            'description' => "Successfully completed project for {$event->quotation->customer_name}. " .
                           "Project value: RM " . number_format($event->quotation->total, 2) . ". " .
                           "Team: " . implode(', ', $proofData['team_members'] ?: ['Sales Team']),
            'metadata' => [
                'customer_name' => $proofData['customer_name'],
                'project_value' => $proofData['project_value'],
                'services_provided' => $proofData['services_provided'],
                'completion_date' => $proofData['completion_date'],
                'metrics' => $proofData['metrics'],
                'auto_generated' => true,
                'source_event' => 'quotation_accepted',
            ],
            'visibility' => 'public',
            'status' => 'active',
            'published_at' => now(),
            'created_by' => $event->acceptedBy?->id ?? $event->quotation->created_by,
        ]);

        Log::info('Created success story proof', [
            'proof_id' => $proof->id,
            'quotation_id' => $event->quotation->id,
        ]);
    }

    /**
     * Update performance KPIs
     */
    private function updatePerformanceKPIs(QuotationAccepted $event): void
    {
        // Create or update team performance KPI
        if ($event->quotation->team_id) {
            $kpi = KPI::updateOrCreate([
                'company_id' => $event->quotation->company_id,
                'scope_type' => 'App\\Models\\Team',
                'scope_id' => $event->quotation->team_id,
                'metric_name' => 'quotation_acceptance_rate',
                'period_type' => 'monthly',
                'period_start' => now()->startOfMonth(),
            ], [
                'current_value' => 0, // Will be calculated properly
                'target_value' => 80, // 80% target acceptance rate
                'unit' => 'percentage',
                'metadata' => [
                    'last_success' => now(),
                    'last_quotation_id' => $event->quotation->id,
                    'total_value_won' => $event->quotation->total,
                ],
            ]);

            // Recalculate acceptance rate
            $this->recalculateAcceptanceRate($kpi);
        }

        // Create individual performance KPI
        if ($event->quotation->assigned_to) {
            $userKpi = KPI::updateOrCreate([
                'company_id' => $event->quotation->company_id,
                'scope_type' => 'App\\Models\\User',
                'scope_id' => $event->quotation->assigned_to,
                'metric_name' => 'quotation_success_rate',
                'period_type' => 'monthly',
                'period_start' => now()->startOfMonth(),
            ], [
                'current_value' => 0, // Will be calculated
                'target_value' => 75, // 75% target for individual
                'unit' => 'percentage',
                'metadata' => [
                    'last_win' => now(),
                    'last_quotation_value' => $event->quotation->total,
                ],
            ]);

            $this->recalculateUserSuccessRate($userKpi);
        }
    }

    /**
     * Create case study entry if qualified
     */
    private function createCaseStudyEntry(QuotationAccepted $event): void
    {
        $proofData = $event->getProofData();
        
        $caseStudy = CaseStudy::create([
            'company_id' => $event->quotation->company_id,
            'title' => "Case Study: {$proofData['title']}",
            'client_name' => $event->quotation->customer_name,
            'industry' => $event->quotation->customerSegment?->name ?? 'General',
            'challenge' => 'To be documented during project execution',
            'solution' => implode(', ', $proofData['services_provided']),
            'results' => 'Project successfully completed and delivered on time',
            'project_value' => $event->quotation->total,
            'completion_date' => now(),
            'team_members' => implode(', ', $proofData['team_members'] ?: ['Sales Team']),
            'technologies_used' => [], // To be populated
            'metrics_before' => [], // To be populated
            'metrics_after' => [], // To be populated
            'client_testimonial' => null, // To be collected
            'status' => 'draft', // Will be completed after project delivery
            'featured' => $event->quotation->total >= 100000, // Feature high-value projects
            'metadata' => [
                'quotation_id' => $event->quotation->id,
                'auto_generated' => true,
                'source_event' => 'quotation_accepted',
                'expected_completion' => now()->addDays(30), // Estimated project duration
            ],
            'created_by' => $event->acceptedBy?->id ?? $event->quotation->created_by,
        ]);

        Log::info('Created case study entry', [
            'case_study_id' => $caseStudy->id,
            'quotation_id' => $event->quotation->id,
            'client' => $event->quotation->customer_name,
        ]);
    }

    /**
     * Recalculate team acceptance rate
     */
    private function recalculateAcceptanceRate(KPI $kpi): void
    {
        $totalQuotes = \App\Models\Quotation::where('company_id', $kpi->company_id)
            ->where('team_id', $kpi->scope_id)
            ->where('created_at', '>=', $kpi->period_start)
            ->count();

        $acceptedQuotes = \App\Models\Quotation::where('company_id', $kpi->company_id)
            ->where('team_id', $kpi->scope_id)
            ->where('status', 'ACCEPTED')
            ->where('created_at', '>=', $kpi->period_start)
            ->count();

        if ($totalQuotes > 0) {
            $rate = ($acceptedQuotes / $totalQuotes) * 100;
            $kpi->update(['current_value' => round($rate, 2)]);
        }
    }

    /**
     * Recalculate user success rate
     */
    private function recalculateUserSuccessRate(KPI $kpi): void
    {
        $totalQuotes = \App\Models\Quotation::where('company_id', $kpi->company_id)
            ->where('assigned_to', $kpi->scope_id)
            ->where('created_at', '>=', $kpi->period_start)
            ->count();

        $successfulQuotes = \App\Models\Quotation::where('company_id', $kpi->company_id)
            ->where('assigned_to', $kpi->scope_id)
            ->where('status', 'ACCEPTED')
            ->where('created_at', '>=', $kpi->period_start)
            ->count();

        if ($totalQuotes > 0) {
            $rate = ($successfulQuotes / $totalQuotes) * 100;
            $kpi->update(['current_value' => round($rate, 2)]);
        }
    }

    /**
     * Handle failed jobs
     */
    public function failed(QuotationAccepted $event, $exception): void
    {
        Log::error('QuotationAccepted event listener failed', [
            'quotation_id' => $event->quotation->id,
            'error' => $exception->getMessage(),
        ]);
    }
}