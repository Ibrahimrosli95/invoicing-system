<?php

namespace App\Services;

use App\Models\WebhookEndpoint;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\PaymentRecord;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WebhookEventService
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Dispatch webhook event for all applicable endpoints
     */
    public function dispatch(string $eventType, array $payload, int $companyId): void
    {
        try {
            $this->webhookService->dispatchEvent($eventType, $payload, $companyId);
            
            Log::info('Webhook event dispatched', [
                'event_type' => $eventType,
                'company_id' => $companyId,
                'payload_keys' => array_keys($payload),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to dispatch webhook event', [
                'event_type' => $eventType,
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Lead Events
     */
    public function leadCreated(Lead $lead): void
    {
        $payload = [
            'id' => $lead->id,
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company_name' => $lead->company_name,
            'source' => $lead->source,
            'status' => $lead->status,
            'team_id' => $lead->team_id,
            'assigned_rep_id' => $lead->assigned_rep_id,
            'estimated_value' => $lead->estimated_value,
            'notes' => $lead->notes,
            'created_at' => $lead->created_at->toISOString(),
        ];

        $this->dispatch('lead.created', $payload, $lead->company_id);
    }

    public function leadUpdated(Lead $lead, array $changes): void
    {
        $payload = [
            'id' => $lead->id,
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company_name' => $lead->company_name,
            'source' => $lead->source,
            'status' => $lead->status,
            'team_id' => $lead->team_id,
            'assigned_rep_id' => $lead->assigned_rep_id,
            'estimated_value' => $lead->estimated_value,
            'changes' => $changes,
            'updated_at' => $lead->updated_at->toISOString(),
        ];

        $this->dispatch('lead.updated', $payload, $lead->company_id);
    }

    public function leadAssigned(Lead $lead, ?User $previousRep, User $newRep): void
    {
        $payload = [
            'id' => $lead->id,
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company_name' => $lead->company_name,
            'previous_rep' => $previousRep ? [
                'id' => $previousRep->id,
                'name' => $previousRep->name,
                'email' => $previousRep->email,
            ] : null,
            'new_rep' => [
                'id' => $newRep->id,
                'name' => $newRep->name,
                'email' => $newRep->email,
            ],
            'assigned_at' => now()->toISOString(),
        ];

        $this->dispatch('lead.assigned', $payload, $lead->company_id);
    }

    public function leadStatusChanged(Lead $lead, string $oldStatus, string $newStatus): void
    {
        $payload = [
            'id' => $lead->id,
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company_name' => $lead->company_name,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'status_changed_at' => now()->toISOString(),
        ];

        $this->dispatch('lead.status.changed', $payload, $lead->company_id);
    }

    /**
     * Quotation Events
     */
    public function quotationCreated(Quotation $quotation): void
    {
        $payload = [
            'id' => $quotation->id,
            'number' => $quotation->number,
            'customer_name' => $quotation->customer_name,
            'customer_email' => $quotation->customer_email,
            'customer_phone' => $quotation->customer_phone,
            'status' => $quotation->status,
            'type' => $quotation->type,
            'subtotal' => $quotation->subtotal,
            'discount_amount' => $quotation->discount_amount,
            'tax_amount' => $quotation->tax_amount,
            'total' => $quotation->total,
            'lead_id' => $quotation->lead_id,
            'customer_segment_id' => $quotation->customer_segment_id,
            'valid_until' => $quotation->valid_until?->toISOString(),
            'created_by' => $quotation->created_by,
            'created_at' => $quotation->created_at->toISOString(),
        ];

        $this->dispatch('quotation.created', $payload, $quotation->company_id);
    }

    public function quotationSent(Quotation $quotation): void
    {
        $payload = [
            'id' => $quotation->id,
            'number' => $quotation->number,
            'customer_name' => $quotation->customer_name,
            'customer_email' => $quotation->customer_email,
            'total' => $quotation->total,
            'status' => $quotation->status,
            'sent_at' => $quotation->sent_at?->toISOString(),
            'valid_until' => $quotation->valid_until?->toISOString(),
        ];

        $this->dispatch('quotation.sent', $payload, $quotation->company_id);
    }

    public function quotationViewed(Quotation $quotation): void
    {
        $payload = [
            'id' => $quotation->id,
            'number' => $quotation->number,
            'customer_name' => $quotation->customer_name,
            'customer_email' => $quotation->customer_email,
            'total' => $quotation->total,
            'status' => $quotation->status,
            'viewed_at' => now()->toISOString(),
        ];

        $this->dispatch('quotation.viewed', $payload, $quotation->company_id);
    }

    public function quotationAccepted(Quotation $quotation): void
    {
        $payload = [
            'id' => $quotation->id,
            'number' => $quotation->number,
            'customer_name' => $quotation->customer_name,
            'customer_email' => $quotation->customer_email,
            'total' => $quotation->total,
            'status' => $quotation->status,
            'accepted_at' => $quotation->accepted_at?->toISOString(),
        ];

        $this->dispatch('quotation.accepted', $payload, $quotation->company_id);
    }

    public function quotationRejected(Quotation $quotation): void
    {
        $payload = [
            'id' => $quotation->id,
            'number' => $quotation->number,
            'customer_name' => $quotation->customer_name,
            'customer_email' => $quotation->customer_email,
            'total' => $quotation->total,
            'status' => $quotation->status,
            'rejected_at' => now()->toISOString(),
        ];

        $this->dispatch('quotation.rejected', $payload, $quotation->company_id);
    }

    public function quotationExpired(Quotation $quotation): void
    {
        $payload = [
            'id' => $quotation->id,
            'number' => $quotation->number,
            'customer_name' => $quotation->customer_name,
            'customer_email' => $quotation->customer_email,
            'total' => $quotation->total,
            'status' => $quotation->status,
            'valid_until' => $quotation->valid_until?->toISOString(),
            'expired_at' => now()->toISOString(),
        ];

        $this->dispatch('quotation.expired', $payload, $quotation->company_id);
    }

    /**
     * Invoice Events
     */
    public function invoiceCreated(Invoice $invoice): void
    {
        $payload = [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'customer_name' => $invoice->customer_name,
            'customer_email' => $invoice->customer_email,
            'customer_phone' => $invoice->customer_phone,
            'status' => $invoice->status,
            'subtotal' => $invoice->subtotal,
            'discount_amount' => $invoice->discount_amount,
            'tax_amount' => $invoice->tax_amount,
            'total' => $invoice->total,
            'quotation_id' => $invoice->quotation_id,
            'due_date' => $invoice->due_date?->toISOString(),
            'created_by' => $invoice->created_by,
            'created_at' => $invoice->created_at->toISOString(),
        ];

        $this->dispatch('invoice.created', $payload, $invoice->company_id);
    }

    public function invoiceSent(Invoice $invoice): void
    {
        $payload = [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'customer_name' => $invoice->customer_name,
            'customer_email' => $invoice->customer_email,
            'total' => $invoice->total,
            'status' => $invoice->status,
            'due_date' => $invoice->due_date?->toISOString(),
            'sent_at' => $invoice->sent_at?->toISOString(),
        ];

        $this->dispatch('invoice.sent', $payload, $invoice->company_id);
    }

    public function invoicePaid(Invoice $invoice): void
    {
        $payload = [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'customer_name' => $invoice->customer_name,
            'customer_email' => $invoice->customer_email,
            'total' => $invoice->total,
            'status' => $invoice->status,
            'paid_amount' => $invoice->paid_amount,
            'outstanding_amount' => $invoice->outstanding_amount,
            'paid_at' => $invoice->paid_at?->toISOString(),
        ];

        $this->dispatch('invoice.paid', $payload, $invoice->company_id);
    }

    public function invoiceOverdue(Invoice $invoice): void
    {
        $payload = [
            'id' => $invoice->id,
            'number' => $invoice->number,
            'customer_name' => $invoice->customer_name,
            'customer_email' => $invoice->customer_email,
            'total' => $invoice->total,
            'outstanding_amount' => $invoice->outstanding_amount,
            'status' => $invoice->status,
            'due_date' => $invoice->due_date?->toISOString(),
            'days_overdue' => $invoice->getDaysOverdue(),
            'overdue_at' => now()->toISOString(),
        ];

        $this->dispatch('invoice.overdue', $payload, $invoice->company_id);
    }

    /**
     * Payment Events
     */
    public function paymentReceived(PaymentRecord $payment): void
    {
        $payload = [
            'id' => $payment->id,
            'receipt_number' => $payment->receipt_number,
            'invoice_id' => $payment->invoice_id,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'reference_number' => $payment->reference_number,
            'status' => $payment->status,
            'payment_date' => $payment->payment_date?->toISOString(),
            'created_at' => $payment->created_at->toISOString(),
            'invoice' => [
                'id' => $payment->invoice->id,
                'number' => $payment->invoice->number,
                'customer_name' => $payment->invoice->customer_name,
                'total' => $payment->invoice->total,
                'outstanding_amount' => $payment->invoice->outstanding_amount,
            ],
        ];

        $this->dispatch('payment.received', $payload, $payment->invoice->company_id);
    }

    public function paymentFailed(PaymentRecord $payment, string $reason): void
    {
        $payload = [
            'id' => $payment->id,
            'receipt_number' => $payment->receipt_number,
            'invoice_id' => $payment->invoice_id,
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method,
            'reference_number' => $payment->reference_number,
            'status' => $payment->status,
            'failure_reason' => $reason,
            'failed_at' => now()->toISOString(),
            'invoice' => [
                'id' => $payment->invoice->id,
                'number' => $payment->invoice->number,
                'customer_name' => $payment->invoice->customer_name,
                'total' => $payment->invoice->total,
            ],
        ];

        $this->dispatch('payment.failed', $payload, $payment->invoice->company_id);
    }

    /**
     * Proof Engine Events
     */
    public function proofCreated(\App\Models\Proof $proof): void
    {
        $payload = [
            'id' => $proof->id,
            'uuid' => $proof->uuid,
            'type' => $proof->type,
            'title' => $proof->title,
            'description' => $proof->description,
            'status' => $proof->status,
            'visibility' => $proof->visibility,
            'is_featured' => $proof->is_featured,
            'show_in_pdf' => $proof->show_in_pdf,
            'show_in_quotation' => $proof->show_in_quotation,
            'show_in_invoice' => $proof->show_in_invoice,
            'metadata' => $proof->metadata,
            'scope_type' => $proof->scope_type,
            'scope_id' => $proof->scope_id,
            'created_by' => $proof->created_by,
            'created_at' => $proof->created_at->toISOString(),
            'assets_count' => $proof->assets->count(),
            'company_context' => [
                'company_id' => $proof->company_id,
                'company_name' => $proof->company->name ?? null,
            ],
        ];

        $this->dispatch('proof.created', $payload, $proof->company_id);
    }

    public function proofPublished(\App\Models\Proof $proof): void
    {
        $payload = [
            'id' => $proof->id,
            'uuid' => $proof->uuid,
            'type' => $proof->type,
            'title' => $proof->title,
            'description' => $proof->description,
            'status' => $proof->status,
            'visibility' => $proof->visibility,
            'is_featured' => $proof->is_featured,
            'published_at' => $proof->published_at?->toISOString(),
            'expires_at' => $proof->expires_at?->toISOString(),
            'view_count' => $proof->view_count,
            'click_count' => $proof->click_count,
            'conversion_impact' => $proof->conversion_impact,
            'metadata' => $proof->metadata,
            'assets' => $proof->assets->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'filename' => $asset->filename,
                    'file_type' => $asset->file_type,
                    'file_size' => $asset->file_size,
                    'status' => $asset->status,
                ];
            })->toArray(),
            'publishing_context' => [
                'published_by' => $proof->updated_by,
                'auto_generated' => $proof->metadata['auto_generated'] ?? false,
                'source_event' => $proof->metadata['source_event'] ?? null,
            ],
        ];

        $this->dispatch('proof.published', $payload, $proof->company_id);
    }

    public function proofPackGenerated(int $companyId, array $proofPackData): void
    {
        $payload = [
            'company_id' => $companyId,
            'pack_type' => $proofPackData['pack_type'] ?? 'comprehensive',
            'trigger_event' => $proofPackData['trigger_event'] ?? null,
            'compilation_strategy' => $proofPackData['compilation_strategy'] ?? null,
            'proofs_included' => $proofPackData['proofs_included'] ?? [],
            'proof_counts' => $proofPackData['proof_counts'] ?? [],
            'generation_stats' => [
                'total_proofs' => $proofPackData['total_proofs'] ?? 0,
                'featured_proofs' => $proofPackData['featured_proofs'] ?? 0,
                'asset_count' => $proofPackData['asset_count'] ?? 0,
                'compilation_time' => $proofPackData['compilation_time'] ?? null,
            ],
            'pdf_info' => [
                'file_path' => $proofPackData['pdf_path'] ?? null,
                'file_size' => $proofPackData['pdf_size'] ?? null,
                'page_count' => $proofPackData['page_count'] ?? null,
            ],
            'context' => $proofPackData['context'] ?? [],
            'generated_at' => now()->toISOString(),
        ];

        $this->dispatch('proof.pack.generated', $payload, $companyId);
    }

    public function proofEffectivenessScored(\App\Models\Proof $proof, array $effectivenessData): void
    {
        $payload = [
            'proof' => [
                'id' => $proof->id,
                'uuid' => $proof->uuid,
                'type' => $proof->type,
                'title' => $proof->title,
                'status' => $proof->status,
            ],
            'effectiveness_scores' => $effectivenessData['component_scores'] ?? [],
            'overall_score' => $effectivenessData['overall_score'] ?? 0,
            'effectiveness_grade' => $effectivenessData['effectiveness_grade'] ?? 'Unknown',
            'performance_metrics' => $effectivenessData['performance_metrics'] ?? [],
            'analytics_data' => [
                'views' => $effectivenessData['views'] ?? 0,
                'engagement_rate' => $effectivenessData['engagement_rate'] ?? 0,
                'conversion_attribution' => $effectivenessData['conversion_attribution'] ?? 0,
                'business_impact' => $effectivenessData['business_impact'] ?? 0,
            ],
            'recommendations' => $effectivenessData['recommendations'] ?? [],
            'benchmark_comparison' => $effectivenessData['benchmark_comparison'] ?? [],
            'scoring_context' => [
                'analysis_period' => $effectivenessData['analysis_period'] ?? null,
                'sample_size' => $effectivenessData['sample_size'] ?? 0,
                'confidence_level' => $effectivenessData['confidence_level'] ?? null,
            ],
            'scored_at' => now()->toISOString(),
        ];

        $this->dispatch('proof.effectiveness.scored', $payload, $proof->company_id);
    }

    public function testimonialRequested($model, array $testimonialData): void
    {
        $payload = [
            'request_type' => 'testimonial',
            'source_model' => [
                'type' => get_class($model),
                'id' => $model->id,
                'identifier' => $model->number ?? $model->name ?? "#{$model->id}",
            ],
            'customer_data' => [
                'name' => $testimonialData['customer_name'] ?? null,
                'email' => $testimonialData['customer_email'] ?? null,
                'phone' => $testimonialData['customer_phone'] ?? null,
                'company' => $testimonialData['customer_company'] ?? null,
            ],
            'project_context' => [
                'title' => $testimonialData['project_title'] ?? null,
                'value' => $testimonialData['project_value'] ?? null,
                'completion_date' => $testimonialData['completion_date'] ?? null,
                'services_provided' => $testimonialData['services_provided'] ?? [],
                'project_duration' => $testimonialData['project_duration'] ?? null,
            ],
            'testimonial_details' => [
                'id' => $testimonialData['testimonial_id'] ?? null,
                'request_sent_at' => $testimonialData['request_sent_at'] ?? null,
                'qualification_criteria' => $testimonialData['qualification_criteria'] ?? [],
                'follow_up_scheduled' => $testimonialData['follow_up_scheduled'] ?? null,
            ],
            'automation_context' => [
                'auto_generated' => true,
                'trigger_event' => $testimonialData['trigger_event'] ?? 'automated',
                'qualification_score' => $testimonialData['qualification_score'] ?? null,
            ],
            'requested_at' => now()->toISOString(),
        ];

        $this->dispatch('testimonial.requested', $payload, $model->company_id);
    }

    public function caseStudyApprovalRequested($model, array $caseStudyData): void
    {
        $payload = [
            'request_type' => 'case_study_approval',
            'source_model' => [
                'type' => get_class($model),
                'id' => $model->id,
                'identifier' => $model->number ?? $model->name ?? "#{$model->id}",
            ],
            'customer_data' => [
                'name' => $caseStudyData['customer_name'] ?? null,
                'email' => $caseStudyData['customer_email'] ?? null,
                'phone' => $caseStudyData['customer_phone'] ?? null,
                'company' => $caseStudyData['customer_company'] ?? null,
                'industry' => $caseStudyData['industry'] ?? null,
            ],
            'case_study' => [
                'id' => $caseStudyData['case_study_id'] ?? null,
                'title' => $caseStudyData['case_study_title'] ?? null,
                'challenge' => $caseStudyData['challenge'] ?? null,
                'solution' => $caseStudyData['solution'] ?? null,
                'results' => $caseStudyData['results'] ?? null,
                'project_value' => $caseStudyData['project_value'] ?? null,
                'status' => $caseStudyData['status'] ?? 'approval_requested',
            ],
            'approval_context' => [
                'qualification_criteria' => $caseStudyData['qualification_criteria'] ?? [],
                'approval_requested_at' => $caseStudyData['approval_requested_at'] ?? null,
                'internal_team_notified' => $caseStudyData['internal_team_notified'] ?? false,
                'estimated_completion' => $caseStudyData['estimated_completion'] ?? null,
            ],
            'automation_context' => [
                'auto_generated' => true,
                'trigger_event' => $caseStudyData['trigger_event'] ?? 'automated',
                'qualification_score' => $caseStudyData['qualification_score'] ?? null,
            ],
            'requested_at' => now()->toISOString(),
        ];

        $this->dispatch('case_study.approval_requested', $payload, $model->company_id);
    }

    public function proofAssetOptimized(\App\Models\ProofAsset $asset, array $optimizationResults): void
    {
        $payload = [
            'asset' => [
                'id' => $asset->id,
                'proof_id' => $asset->proof_id,
                'filename' => $asset->filename,
                'file_type' => $asset->file_type,
                'file_size' => $asset->file_size,
                'original_filename' => $asset->original_filename,
                'status' => $asset->status,
            ],
            'optimization_results' => [
                'type' => $optimizationResults['type'] ?? 'unknown',
                'thumbnails_generated' => $optimizationResults['thumbnails_generated'] ?? [],
                'web_versions_created' => $optimizationResults['web_versions_created'] ?? [],
                'metadata_extracted' => $optimizationResults['metadata_extracted'] ?? [],
                'optimization_stats' => $optimizationResults['optimization_stats'] ?? [],
            ],
            'processing_info' => [
                'processing_time' => $optimizationResults['processing_time'] ?? null,
                'optimization_version' => $asset->metadata['optimization_version'] ?? null,
                'optimized_at' => $asset->metadata['optimized_at'] ?? null,
                'file_improvements' => $optimizationResults['file_improvements'] ?? [],
            ],
            'proof_context' => [
                'proof_type' => $asset->proof->type ?? null,
                'proof_title' => $asset->proof->title ?? null,
                'proof_status' => $asset->proof->status ?? null,
            ],
            'optimized_at' => now()->toISOString(),
        ];

        $this->dispatch('proof.asset.optimized', $payload, $asset->proof->company_id);
    }

    public function proofAnalyticsCompiled(int $companyId, array $analyticsData): void
    {
        $payload = [
            'company_id' => $companyId,
            'analytics_summary' => [
                'total_proofs' => $analyticsData['total_proofs'] ?? 0,
                'active_proofs' => $analyticsData['active_proofs'] ?? 0,
                'featured_proofs' => $analyticsData['featured_proofs'] ?? 0,
                'total_views' => $analyticsData['total_views'] ?? 0,
                'total_conversions' => $analyticsData['total_conversions'] ?? 0,
                'average_effectiveness' => $analyticsData['average_effectiveness'] ?? 0,
            ],
            'performance_insights' => [
                'top_performing_proofs' => $analyticsData['top_performing_proofs'] ?? [],
                'underperforming_proofs' => $analyticsData['underperforming_proofs'] ?? [],
                'trending_proof_types' => $analyticsData['trending_proof_types'] ?? [],
                'engagement_trends' => $analyticsData['engagement_trends'] ?? [],
            ],
            'kpi_updates' => $analyticsData['kpi_updates'] ?? [],
            'recommendations' => [
                'optimization_suggestions' => $analyticsData['optimization_suggestions'] ?? [],
                'content_gaps' => $analyticsData['content_gaps'] ?? [],
                'strategy_adjustments' => $analyticsData['strategy_adjustments'] ?? [],
            ],
            'compilation_context' => [
                'analysis_period' => $analyticsData['analysis_period'] ?? null,
                'data_points_analyzed' => $analyticsData['data_points_analyzed'] ?? 0,
                'compilation_trigger' => $analyticsData['compilation_trigger'] ?? null,
                'processing_time' => $analyticsData['processing_time'] ?? null,
            ],
            'compiled_at' => now()->toISOString(),
        ];

        $this->dispatch('proof.analytics.compiled', $payload, $companyId);
    }

    /**
     * User Events
     */
    public function userCreated(User $user): void
    {
        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'company_id' => $user->company_id,
            'roles' => $user->roles->pluck('name')->toArray(),
            'teams' => $user->teams->pluck('name')->toArray(),
            'created_at' => $user->created_at->toISOString(),
        ];

        $this->dispatch('user.created', $payload, $user->company_id);
    }

    public function userUpdated(User $user, array $changes): void
    {
        $payload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'company_id' => $user->company_id,
            'roles' => $user->roles->pluck('name')->toArray(),
            'teams' => $user->teams->pluck('name')->toArray(),
            'changes' => $changes,
            'updated_at' => $user->updated_at->toISOString(),
        ];

        $this->dispatch('user.updated', $payload, $user->company_id);
    }
}