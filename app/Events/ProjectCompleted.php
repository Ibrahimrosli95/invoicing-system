<?php

namespace App\Events;

use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Lead;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public ?Quotation $quotation;
    public ?Invoice $invoice;
    public ?Lead $lead;
    public ?User $completedBy;
    public array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(?Quotation $quotation = null, ?Invoice $invoice = null, ?Lead $lead = null, ?User $completedBy = null, array $metadata = [])
    {
        $this->quotation = $quotation;
        $this->invoice = $invoice;
        $this->lead = $lead;
        $this->completedBy = $completedBy;
        
        // Build comprehensive project metadata
        $this->metadata = array_merge([
            'completion_date' => now(),
            'project_type' => $this->determineProjectType(),
            'customer_info' => $this->getCustomerInfo(),
            'financial_summary' => $this->getFinancialSummary(),
            'team_info' => $this->getTeamInfo(),
            'timeline' => $this->getProjectTimeline(),
            'success_indicators' => $this->getSuccessIndicators(),
        ], $metadata);
    }

    /**
     * Get comprehensive proof pack data
     */
    public function getProofPackData(): array
    {
        return [
            'project_overview' => [
                'title' => $this->quotation?->title ?? 'Completed Project',
                'description' => $this->quotation?->description,
                'customer' => $this->getCustomerInfo(),
                'completion_date' => now(),
                'duration' => $this->getProjectDuration(),
            ],
            'financial_success' => $this->getFinancialSummary(),
            'team_performance' => $this->getTeamInfo(),
            'deliverables' => $this->getDeliverables(),
            'customer_satisfaction' => [
                'on_time_completion' => true,
                'budget_adherence' => $this->getBudgetAdherence(),
                'quality_delivered' => true, // To be validated
            ],
            'proof_categories' => $this->categorizeProofElements(),
        ];
    }

    /**
     * Determine what type of project this was
     */
    private function determineProjectType(): string
    {
        if ($this->quotation) {
            $itemCount = $this->quotation->items->count();
            $hasServices = $this->quotation->sections->count() > 0;
            
            if ($itemCount > 20) return 'large_installation';
            if ($hasServices) return 'service_project';
            if ($this->quotation->total > 100000) return 'enterprise_project';
            return 'standard_project';
        }
        
        return 'unknown';
    }

    /**
     * Get customer information
     */
    private function getCustomerInfo(): array
    {
        $source = $this->quotation ?? $this->lead;
        
        return [
            'name' => $source?->customer_name ?? 'Valued Customer',
            'segment' => $this->quotation?->customerSegment?->name ?? 'End User',
            'location' => $source?->customer_city,
            'contact_method' => $source?->customer_phone ? 'phone' : 'email',
        ];
    }

    /**
     * Get financial summary
     */
    private function getFinancialSummary(): array
    {
        return [
            'quoted_value' => $this->quotation?->total,
            'invoiced_value' => $this->invoice?->total,
            'paid_amount' => $this->invoice?->amount_paid,
            'profit_margin' => $this->calculateProfitMargin(),
            'payment_terms_met' => $this->invoice ? !$this->invoice->isOverdue() : null,
        ];
    }

    /**
     * Get team information
     */
    private function getTeamInfo(): array
    {
        $team = $this->quotation?->team ?? $this->lead?->team;
        
        return [
            'team_name' => $team?->name,
            'team_size' => $team?->members->count() ?? 1,
            'lead_rep' => $this->quotation?->assignedRep?->name ?? $this->lead?->assignedRep?->name,
            'manager' => $team?->manager?->name,
            'specialization' => $team?->specialization ?? 'General',
        ];
    }

    /**
     * Get project timeline
     */
    private function getProjectTimeline(): array
    {
        $start = $this->lead?->created_at ?? $this->quotation?->created_at;
        $quote_sent = $this->quotation?->sent_at;
        $quote_accepted = $this->quotation?->accepted_at;
        $invoice_issued = $this->invoice?->created_at;
        $completion = now();

        return [
            'lead_to_quote' => $start && $this->quotation ? $start->diffInDays($this->quotation->created_at) : null,
            'quote_to_acceptance' => $quote_sent && $quote_accepted ? $quote_sent->diffInDays($quote_accepted) : null,
            'acceptance_to_completion' => $quote_accepted ? $quote_accepted->diffInDays($completion) : null,
            'total_duration' => $start ? $start->diffInDays($completion) : null,
        ];
    }

    /**
     * Get success indicators
     */
    private function getSuccessIndicators(): array
    {
        return [
            'on_time_delivery' => true, // Assume true unless specified
            'customer_satisfaction' => 'high', // To be confirmed via testimonial
            'budget_performance' => $this->getBudgetAdherence(),
            'team_efficiency' => $this->calculateTeamEfficiency(),
            'repeat_customer' => $this->checkRepeatCustomer(),
        ];
    }

    /**
     * Calculate profit margin if cost data available
     */
    private function calculateProfitMargin(): ?float
    {
        // This would require cost data which isn't always available
        // Return null for now, can be enhanced with cost tracking
        return null;
    }

    /**
     * Check budget adherence
     */
    private function getBudgetAdherence(): string
    {
        if (!$this->quotation || !$this->invoice) return 'unknown';
        
        $variance = abs($this->quotation->total - $this->invoice->total) / $this->quotation->total;
        
        if ($variance <= 0.05) return 'excellent'; // Within 5%
        if ($variance <= 0.10) return 'good';      // Within 10%
        if ($variance <= 0.20) return 'fair';     // Within 20%
        return 'poor';
    }

    /**
     * Calculate team efficiency metrics
     */
    private function calculateTeamEfficiency(): array
    {
        $timeline = $this->getProjectTimeline();
        
        return [
            'response_time' => $timeline['lead_to_quote'] ?? null,
            'conversion_rate' => $this->quotation?->status === 'ACCEPTED' ? 100 : 0,
            'delivery_speed' => $timeline['total_duration'] ?? null,
        ];
    }

    /**
     * Check if this is a repeat customer
     */
    private function checkRepeatCustomer(): bool
    {
        if (!$this->quotation) return false;
        
        $previousQuotes = \App\Models\Quotation::where('company_id', $this->quotation->company_id)
            ->where('customer_phone', $this->quotation->customer_phone)
            ->where('id', '!=', $this->quotation->id)
            ->exists();
            
        return $previousQuotes;
    }

    /**
     * Get project deliverables
     */
    private function getDeliverables(): array
    {
        if (!$this->quotation) return [];
        
        return $this->quotation->items->map(function ($item) {
            return [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'value' => $item->total,
                'category' => $item->category ?? 'Service',
            ];
        })->toArray();
    }

    /**
     * Categorize proof elements for different proof types
     */
    private function categorizeProofElements(): array
    {
        return [
            'visual_proof' => [
                'before_photos' => [], // To be added
                'after_photos' => [], // To be added
                'process_photos' => [], // To be added
            ],
            'social_proof' => [
                'testimonial_ready' => true,
                'case_study_potential' => $this->qualifiesForCaseStudy(),
                'reference_permission' => false, // To be requested
            ],
            'professional_proof' => [
                'certifications_used' => [], // To be populated
                'team_credentials' => [], // To be populated
                'quality_standards' => [], // To be populated
            ],
            'performance_proof' => [
                'timeline_met' => true,
                'budget_adherence' => $this->getBudgetAdherence(),
                'efficiency_metrics' => $this->calculateTeamEfficiency(),
            ],
            'trust_proof' => [
                'payment_completed' => $this->invoice?->status === 'PAID',
                'warranty_provided' => false, // To be confirmed
                'insurance_coverage' => false, // To be confirmed
            ],
        ];
    }

    /**
     * Check if project qualifies for case study
     */
    private function qualifiesForCaseStudy(): bool
    {
        return ($this->quotation?->total ?? 0) >= 50000 || // High value
               ($this->quotation?->items->count() ?? 0) >= 15 || // Complex project
               in_array($this->quotation?->customerSegment?->name, ['Dealer', 'Contractor']); // Strategic segments
    }

    /**
     * Get project duration in days
     */
    private function getProjectDuration(): ?int
    {
        $start = $this->lead?->created_at ?? $this->quotation?->created_at;
        return $start ? $start->diffInDays(now()) : null;
    }
}