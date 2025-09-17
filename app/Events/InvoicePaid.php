<?php

namespace App\Events;

use App\Models\Invoice;
use App\Models\PaymentRecord;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoicePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Invoice $invoice;
    public PaymentRecord $payment;
    public ?User $processedBy;
    public array $metadata;

    /**
     * Create a new event instance.
     */
    public function __construct(Invoice $invoice, PaymentRecord $payment, ?User $processedBy = null, array $metadata = [])
    {
        $this->invoice = $invoice;
        $this->payment = $payment;
        $this->processedBy = $processedBy;
        $this->metadata = array_merge([
            'payment_date' => $payment->created_at,
            'payment_method' => $payment->method,
            'project_duration' => $invoice->quotation ? 
                $invoice->quotation->created_at->diffInDays($payment->created_at) : null,
            'customer_segment' => $invoice->quotation?->customerSegment?->name,
            'team_id' => $invoice->quotation?->team_id,
            'assigned_to' => $invoice->quotation?->assigned_to,
            'total_value' => $invoice->total,
        ], $metadata);
    }

    /**
     * Get data for testimonial collection
     */
    public function getTestimonialData(): array
    {
        return [
            'customer_name' => $this->invoice->quotation?->customer_name ?? 'Valued Customer',
            'customer_email' => $this->invoice->quotation?->customer_email,
            'customer_phone' => $this->invoice->quotation?->customer_phone,
            'project_title' => $this->invoice->quotation?->title ?? 'Project',
            'completion_date' => $this->payment->created_at,
            'services_provided' => $this->invoice->quotation?->items->pluck('description')->toArray() ?? [],
            'project_value' => $this->invoice->total,
            'team_members' => $this->invoice->quotation?->team?->members->pluck('name')->toArray() ?? [],
            'follow_up_date' => now()->addDays(7), // Schedule follow-up in 1 week
        ];
    }

    /**
     * Check if this project qualifies for testimonial collection
     */
    public function qualifiesForTestimonial(): bool
    {
        return $this->invoice->total >= 10000 || // High-value projects
               $this->payment->created_at->diffInDays($this->invoice->created_at) <= 30 || // Quick completion
               !empty($this->invoice->quotation?->customer_email); // Has contact info
    }

    /**
     * Get performance metrics for KPI tracking
     */
    public function getPerformanceMetrics(): array
    {
        $quotation = $this->invoice->quotation;
        
        return [
            'conversion_time' => $quotation ? $quotation->created_at->diffInDays($this->payment->created_at) : null,
            'payment_efficiency' => $this->invoice->created_at->diffInDays($this->payment->created_at),
            'project_value' => $this->invoice->total,
            'customer_segment' => $quotation?->customerSegment?->name,
            'team_performance' => [
                'team_id' => $quotation?->team_id,
                'assigned_to' => $quotation?->assigned_to,
                'success_indicator' => 'on_time_payment',
            ],
        ];
    }
}