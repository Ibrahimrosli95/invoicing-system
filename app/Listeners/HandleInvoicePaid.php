<?php

namespace App\Listeners;

use App\Events\InvoicePaid;
use App\Jobs\RequestReviewJob;
use App\Jobs\CompileProofPack;
use App\Models\Proof;
use App\Models\KPI;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleInvoicePaid implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(InvoicePaid $event): void
    {
        try {
            Log::info('Processing InvoicePaid event', [
                'invoice_id' => $event->invoice->id,
                'invoice_number' => $event->invoice->number,
                'customer' => $event->invoice->quotation?->customer_name,
                'amount' => $event->payment->amount,
            ]);

            // Create payment success proof
            $this->createPaymentSuccessProof($event);

            // Update financial KPIs
            $this->updateFinancialKPIs($event);

            // Schedule testimonial collection if qualified
            if ($event->qualifiesForTestimonial()) {
                RequestReviewJob::dispatch($event->invoice, 'testimonial')
                    ->delay(now()->addDays(3)); // Quick follow-up for testimonials
                
                Log::info('Scheduled testimonial collection', [
                    'invoice_id' => $event->invoice->id,
                    'scheduled_for' => now()->addDays(3),
                ]);
            }

            // Update case study with completion data if exists
            $this->updateRelatedCaseStudy($event);

            // Queue proof pack compilation for payment success
            CompileProofPack::dispatch($event->invoice->company_id, [
                'trigger_event' => 'invoice_paid',
                'invoice_id' => $event->invoice->id,
                'proof_data' => [
                    'type' => 'trust_proof',
                    'payment_completed' => true,
                    'payment_method' => $event->payment->method,
                    'customer_info' => $event->getTestimonialData(),
                    'performance_metrics' => $event->getPerformanceMetrics(),
                ],
            ])->delay(now()->addMinutes(10));

            Log::info('InvoicePaid event processed successfully', [
                'invoice_id' => $event->invoice->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process InvoicePaid event', [
                'invoice_id' => $event->invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Create payment success proof entry
     */
    private function createPaymentSuccessProof(InvoicePaid $event): void
    {
        $testimonialData = $event->getTestimonialData();
        $performanceMetrics = $event->getPerformanceMetrics();

        $proof = Proof::create([
            'company_id' => $event->invoice->company_id,
            'scope_type' => 'App\\Models\\Invoice',
            'scope_id' => $event->invoice->id,
            'type' => 'trust_proof',
            'title' => "Payment Completed: {$testimonialData['project_title']}",
            'description' => "Successfully completed project for {$testimonialData['customer_name']} " .
                           "with payment of RM " . number_format($event->payment->amount, 2) . " " .
                           "received via {$event->payment->method}.",
            'metadata' => [
                'payment_date' => $event->payment->created_at,
                'payment_method' => $event->payment->method,
                'payment_amount' => $event->payment->amount,
                'project_value' => $testimonialData['project_value'],
                'payment_efficiency' => $performanceMetrics['payment_efficiency'],
                'customer_segment' => $performanceMetrics['customer_segment'],
                'services_completed' => $testimonialData['services_provided'],
                'auto_generated' => true,
                'source_event' => 'invoice_paid',
            ],
            'visibility' => 'public',
            'status' => 'active',
            'published_at' => now(),
            'created_by' => $event->processedBy?->id ?? $event->invoice->quotation?->created_by,
        ]);

        Log::info('Created payment success proof', [
            'proof_id' => $proof->id,
            'invoice_id' => $event->invoice->id,
        ]);
    }

    /**
     * Update financial performance KPIs
     */
    private function updateFinancialKPIs(InvoicePaid $event): void
    {
        $performanceMetrics = $event->getPerformanceMetrics();
        
        // Update company-wide collection efficiency
        $companyKpi = KPI::updateOrCreate([
            'company_id' => $event->invoice->company_id,
            'scope_type' => 'App\\Models\\Company',
            'scope_id' => $event->invoice->company_id,
            'metric_name' => 'payment_collection_efficiency',
            'period_type' => 'monthly',
            'period_start' => now()->startOfMonth(),
        ], [
            'current_value' => $performanceMetrics['payment_efficiency'],
            'target_value' => 30, // 30 days or less target
            'unit' => 'days',
            'metadata' => [
                'last_payment' => $event->payment->created_at,
                'last_payment_amount' => $event->payment->amount,
                'payment_method' => $event->payment->method,
            ],
        ]);

        $this->recalculateCollectionEfficiency($companyKpi);

        // Update team performance if available
        if ($event->invoice->quotation?->team_id) {
            $teamKpi = KPI::updateOrCreate([
                'company_id' => $event->invoice->company_id,
                'scope_type' => 'App\\Models\\Team',
                'scope_id' => $event->invoice->quotation->team_id,
                'metric_name' => 'project_completion_rate',
                'period_type' => 'monthly',
                'period_start' => now()->startOfMonth(),
            ], [
                'current_value' => 100, // Successful completion
                'target_value' => 95, // 95% completion rate target
                'unit' => 'percentage',
                'metadata' => [
                    'last_completion' => $event->payment->created_at,
                    'project_value' => $event->invoice->total,
                    'customer_segment' => $performanceMetrics['customer_segment'],
                ],
            ]);

            $this->recalculateCompletionRate($teamKpi);
        }

        // Update individual rep performance
        if ($event->invoice->quotation?->assigned_to) {
            $repKpi = KPI::updateOrCreate([
                'company_id' => $event->invoice->company_id,
                'scope_type' => 'App\\Models\\User',
                'scope_id' => $event->invoice->quotation->assigned_to,
                'metric_name' => 'revenue_generated',
                'period_type' => 'monthly',
                'period_start' => now()->startOfMonth(),
            ], [
                'current_value' => 0, // Will be calculated
                'target_value' => 100000, // RM 100k monthly target
                'unit' => 'currency',
                'metadata' => [
                    'last_sale' => $event->payment->created_at,
                    'last_sale_amount' => $event->invoice->total,
                ],
            ]);

            $this->recalculateRevenueGenerated($repKpi);
        }
    }

    /**
     * Update related case study with completion data
     */
    private function updateRelatedCaseStudy(InvoicePaid $event): void
    {
        if (!$event->invoice->quotation) return;

        $caseStudy = \App\Models\CaseStudy::where('company_id', $event->invoice->company_id)
            ->whereJsonContains('metadata->quotation_id', $event->invoice->quotation->id)
            ->first();

        if ($caseStudy) {
            $performanceMetrics = $event->getPerformanceMetrics();
            
            $caseStudy->update([
                'results' => $caseStudy->results . "\n\nProject completed successfully with payment received on " . 
                           $event->payment->created_at->format('M j, Y') . ". " .
                           "Payment collected within {$performanceMetrics['payment_efficiency']} days of invoice.",
                'metrics_after' => [
                    'payment_efficiency' => $performanceMetrics['payment_efficiency'],
                    'project_value_delivered' => $event->invoice->total,
                    'completion_date' => $event->payment->created_at,
                    'customer_satisfaction' => 'payment_completed_on_time',
                ],
                'status' => 'completed',
                'metadata' => array_merge($caseStudy->metadata ?? [], [
                    'invoice_id' => $event->invoice->id,
                    'completion_confirmed' => $event->payment->created_at,
                    'payment_method' => $event->payment->method,
                ]),
            ]);

            Log::info('Updated case study with completion data', [
                'case_study_id' => $caseStudy->id,
                'invoice_id' => $event->invoice->id,
            ]);
        }
    }

    /**
     * Recalculate collection efficiency
     */
    private function recalculateCollectionEfficiency(KPI $kpi): void
    {
        $payments = \App\Models\PaymentRecord::whereHas('invoice', function ($query) use ($kpi) {
                $query->where('company_id', $kpi->company_id);
            })
            ->where('created_at', '>=', $kpi->period_start)
            ->with('invoice')
            ->get();

        if ($payments->count() > 0) {
            $totalDays = $payments->sum(function ($payment) {
                return $payment->created_at->diffInDays($payment->invoice->created_at);
            });

            $averageEfficiency = $totalDays / $payments->count();
            $kpi->update(['current_value' => round($averageEfficiency, 1)]);
        }
    }

    /**
     * Recalculate project completion rate
     */
    private function recalculateCompletionRate(KPI $kpi): void
    {
        $totalProjects = \App\Models\Invoice::whereHas('quotation', function ($query) use ($kpi) {
                $query->where('team_id', $kpi->scope_id);
            })
            ->where('company_id', $kpi->company_id)
            ->where('created_at', '>=', $kpi->period_start)
            ->count();

        $completedProjects = \App\Models\Invoice::whereHas('quotation', function ($query) use ($kpi) {
                $query->where('team_id', $kpi->scope_id);
            })
            ->where('company_id', $kpi->company_id)
            ->where('status', 'PAID')
            ->where('created_at', '>=', $kpi->period_start)
            ->count();

        if ($totalProjects > 0) {
            $rate = ($completedProjects / $totalProjects) * 100;
            $kpi->update(['current_value' => round($rate, 2)]);
        }
    }

    /**
     * Recalculate revenue generated
     */
    private function recalculateRevenueGenerated(KPI $kpi): void
    {
        $totalRevenue = \App\Models\Invoice::whereHas('quotation', function ($query) use ($kpi) {
                $query->where('assigned_to', $kpi->scope_id);
            })
            ->where('company_id', $kpi->company_id)
            ->where('status', 'PAID')
            ->where('created_at', '>=', $kpi->period_start)
            ->sum('total');

        $kpi->update(['current_value' => $totalRevenue]);
    }

    /**
     * Handle failed jobs
     */
    public function failed(InvoicePaid $event, $exception): void
    {
        Log::error('InvoicePaid event listener failed', [
            'invoice_id' => $event->invoice->id,
            'error' => $exception->getMessage(),
        ]);
    }
}