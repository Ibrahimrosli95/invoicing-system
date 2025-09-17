<?php

namespace App\Events;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuotationAccepted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Quotation $quotation;
    public ?User $acceptedBy;
    public array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(Quotation $quotation, ?User $acceptedBy = null, array $metadata = [])
    {
        $this->quotation = $quotation;
        $this->acceptedBy = $acceptedBy;
        $this->metadata = array_merge([
            'acceptance_date' => now(),
            'conversion_source' => 'quotation',
            'total_value' => $quotation->total,
            'customer_segment' => $quotation->customerSegment?->name,
            'team_id' => $quotation->team_id,
            'assigned_to' => $quotation->assigned_to,
        ], $metadata);
    }

    /**
     * Get data for proof compilation
     */
    public function getProofData(): array
    {
        return [
            'type' => 'success_story',
            'title' => "Project Success: {$this->quotation->title}",
            'customer_name' => $this->quotation->customer_name,
            'project_value' => $this->quotation->total,
            'completion_date' => now(),
            'team_members' => $this->quotation->team?->members->pluck('name')->toArray() ?? [],
            'services_provided' => $this->quotation->items->pluck('description')->toArray(),
            'customer_feedback' => null, // To be collected later
            'before_after_photos' => [], // To be added later
            'metrics' => [
                'project_duration' => $this->quotation->created_at->diffInDays(now()),
                'team_size' => $this->quotation->team?->members->count() ?? 1,
                'success_rate' => 100, // Successful completion
            ],
        ];
    }

    /**
     * Check if this quotation qualifies for case study
     */
    public function qualifiesForCaseStudy(): bool
    {
        // High-value projects or strategic customer segments
        return $this->quotation->total >= 50000 || 
               in_array($this->quotation->customerSegment?->name, ['Dealer', 'Contractor']) ||
               $this->quotation->items->count() >= 10; // Complex projects
    }
}