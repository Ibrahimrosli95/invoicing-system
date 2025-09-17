<?php

namespace App\Jobs;

use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Testimonial;
use App\Models\CaseStudy;
use App\Notifications\TestimonialRequestNotification;
use App\Notifications\CaseStudyApprovalNotification;
use App\Notifications\AssetCollectionRequestNotification;
use App\Services\WebhookEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class RequestReviewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $model;
    public string $requestType;
    public array $options;
    public int $timeout = 120;
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct($model, string $requestType, array $options = [])
    {
        $this->model = $model;
        $this->requestType = $requestType;
        $this->options = $options;
        
        // Set appropriate queue
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing review request', [
            'model_type' => get_class($this->model),
            'model_id' => $this->model->id,
            'request_type' => $this->requestType,
        ]);

        try {
            switch ($this->requestType) {
                case 'testimonial':
                    $this->handleTestimonialRequest();
                    break;
                    
                case 'case_study_approval':
                    $this->handleCaseStudyApprovalRequest();
                    break;
                    
                case 'asset_collection':
                    $this->handleAssetCollectionRequest();
                    break;
                    
                case 'follow_up_review':
                    $this->handleFollowUpReview();
                    break;
                    
                default:
                    throw new \InvalidArgumentException("Unknown request type: {$this->requestType}");
            }

            Log::info('Review request processed successfully', [
                'model_type' => get_class($this->model),
                'model_id' => $this->model->id,
                'request_type' => $this->requestType,
            ]);

        } catch (\Exception $e) {
            Log::error('Review request processing failed', [
                'model_type' => get_class($this->model),
                'model_id' => $this->model->id,
                'request_type' => $this->requestType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle testimonial request
     */
    private function handleTestimonialRequest(): void
    {
        // Check if testimonial request is appropriate
        if (!$this->shouldRequestTestimonial()) {
            Log::info('Testimonial request skipped - not appropriate', [
                'model_type' => get_class($this->model),
                'model_id' => $this->model->id,
            ]);
            return;
        }

        // Create testimonial record in draft state
        $testimonial = $this->createTestimonialRecord();
        
        // Prepare customer contact data
        $customerData = $this->extractCustomerData();
        
        if (empty($customerData['email']) && empty($customerData['phone'])) {
            Log::warning('No contact information available for testimonial request', [
                'model_type' => get_class($this->model),
                'model_id' => $this->model->id,
            ]);
            return;
        }

        // Send testimonial request
        $this->sendTestimonialRequest($testimonial, $customerData);
        
        // Dispatch webhook for testimonial request
        $this->dispatchTestimonialWebhook($testimonial, $customerData);
        
        // Schedule follow-up if no response
        $this->scheduleTestimonialFollowUp($testimonial);

        Log::info('Testimonial request sent', [
            'testimonial_id' => $testimonial->id,
            'customer_email' => $customerData['email'],
        ]);
    }

    /**
     * Handle case study approval request
     */
    private function handleCaseStudyApprovalRequest(): void
    {
        // Find or create case study
        $caseStudy = $this->findOrCreateCaseStudy();
        
        if (!$caseStudy) {
            Log::info('Case study approval request skipped - not qualified', [
                'model_type' => get_class($this->model),
                'model_id' => $this->model->id,
            ]);
            return;
        }

        // Get customer data for approval request
        $customerData = $this->extractCustomerData();
        
        // Send approval request to customer
        if (!empty($customerData['email'])) {
            Notification::route('mail', $customerData['email'])
                       ->notify(new CaseStudyApprovalNotification($caseStudy, $customerData));
        }

        // Notify internal team about case study opportunity
        $this->notifyInternalTeamAboutCaseStudy($caseStudy, $customerData);

        // Dispatch webhook for case study approval request
        $this->dispatchCaseStudyWebhook($caseStudy, $customerData);

        Log::info('Case study approval request sent', [
            'case_study_id' => $caseStudy->id,
            'customer_email' => $customerData['email'],
        ]);
    }

    /**
     * Handle asset collection request
     */
    private function handleAssetCollectionRequest(): void
    {
        // Get project details for asset collection
        $projectData = $this->extractProjectData();
        
        // Send asset collection request to team
        $teamMembers = $this->getTeamMembers();
        
        foreach ($teamMembers as $member) {
            $member->notify(new AssetCollectionRequestNotification($this->model, $projectData));
        }

        // Create reminder for asset collection
        $this->scheduleAssetCollectionReminder($projectData);

        Log::info('Asset collection request sent', [
            'model_type' => get_class($this->model),
            'model_id' => $this->model->id,
            'team_members' => $teamMembers->count(),
        ]);
    }

    /**
     * Handle follow-up review
     */
    private function handleFollowUpReview(): void
    {
        // Check if follow-up is needed based on previous requests
        $previousRequests = $this->getPreviousRequests();
        
        if ($previousRequests->isEmpty()) {
            Log::info('No previous requests found for follow-up');
            return;
        }

        // Process each type of follow-up needed
        foreach ($previousRequests as $request) {
            $this->processFollowUpRequest($request);
        }

        Log::info('Follow-up reviews processed', [
            'follow_ups' => $previousRequests->count(),
        ]);
    }

    /**
     * Check if testimonial request is appropriate
     */
    private function shouldRequestTestimonial(): bool
    {
        // Check if we already have a recent testimonial from this customer
        $customerData = $this->extractCustomerData();
        if (empty($customerData['phone'])) return false;

        $recentTestimonial = Testimonial::where('company_id', $this->model->company_id)
            ->where('customer_phone', $customerData['phone'])
            ->where('created_at', '>=', now()->subMonths(6))
            ->exists();

        if ($recentTestimonial) return false;

        // Check project value and success criteria
        if ($this->model instanceof Quotation) {
            return $this->model->status === 'ACCEPTED' && $this->model->total >= 5000;
        }
        
        if ($this->model instanceof Invoice) {
            return $this->model->status === 'PAID' && $this->model->total >= 5000;
        }

        return false;
    }

    /**
     * Create testimonial record
     */
    private function createTestimonialRecord(): Testimonial
    {
        $customerData = $this->extractCustomerData();
        $projectData = $this->extractProjectData();

        return Testimonial::create([
            'company_id' => $this->model->company_id,
            'customer_name' => $customerData['name'],
            'customer_email' => $customerData['email'],
            'customer_phone' => $customerData['phone'],
            'project_title' => $projectData['title'],
            'project_value' => $projectData['value'],
            'completion_date' => $projectData['completion_date'],
            'services_provided' => $projectData['services'],
            'status' => 'requested',
            'request_sent_at' => now(),
            'source_type' => get_class($this->model),
            'source_id' => $this->model->id,
            'metadata' => [
                'auto_generated' => true,
                'request_trigger' => $this->options['trigger'] ?? 'automated',
                'project_data' => $projectData,
            ],
        ]);
    }

    /**
     * Send testimonial request
     */
    private function sendTestimonialRequest(Testimonial $testimonial, array $customerData): void
    {
        if (!empty($customerData['email'])) {
            Notification::route('mail', $customerData['email'])
                       ->notify(new TestimonialRequestNotification($testimonial, $customerData));
        }

        // Update testimonial record
        $testimonial->update([
            'request_sent_at' => now(),
            'status' => 'sent',
        ]);
    }

    /**
     * Schedule testimonial follow-up
     */
    private function scheduleTestimonialFollowUp(Testimonial $testimonial): void
    {
        // Schedule follow-up in 1 week if no response
        static::dispatch($this->model, 'follow_up_review', [
            'testimonial_id' => $testimonial->id,
            'follow_up_type' => 'testimonial',
        ])->delay(now()->addWeek());
    }

    /**
     * Find or create case study
     */
    private function findOrCreateCaseStudy(): ?CaseStudy
    {
        // Check if project qualifies for case study
        if (!$this->qualifiesForCaseStudy()) {
            return null;
        }

        // Look for existing case study
        $caseStudy = null;
        if ($this->model instanceof Quotation) {
            $caseStudy = CaseStudy::where('company_id', $this->model->company_id)
                                  ->whereJsonContains('metadata->quotation_id', $this->model->id)
                                  ->first();
        }

        if (!$caseStudy) {
            $customerData = $this->extractCustomerData();
            $projectData = $this->extractProjectData();
            
            $caseStudy = CaseStudy::create([
                'company_id' => $this->model->company_id,
                'title' => "Case Study: {$projectData['title']}",
                'client_name' => $customerData['name'],
                'industry' => $this->determineIndustry(),
                'challenge' => 'Customer challenge to be documented',
                'solution' => implode(', ', $projectData['services']),
                'results' => 'Project results to be documented',
                'project_value' => $projectData['value'],
                'status' => 'approval_requested',
                'metadata' => [
                    'quotation_id' => $this->model instanceof Quotation ? $this->model->id : null,
                    'invoice_id' => $this->model instanceof Invoice ? $this->model->id : null,
                    'auto_generated' => true,
                    'approval_requested_at' => now(),
                ],
            ]);
        }

        return $caseStudy;
    }

    /**
     * Notify internal team about case study
     */
    private function notifyInternalTeamAboutCaseStudy(CaseStudy $caseStudy, array $customerData): void
    {
        $teamMembers = $this->getTeamMembers();
        $managers = $this->getManagers();

        $recipients = $teamMembers->concat($managers)->unique('id');

        foreach ($recipients as $member) {
            $member->notify(new \App\Notifications\CaseStudyOpportunityNotification($caseStudy, $customerData));
        }
    }

    /**
     * Schedule asset collection reminder
     */
    private function scheduleAssetCollectionReminder(array $projectData): void
    {
        static::dispatch($this->model, 'follow_up_review', [
            'follow_up_type' => 'asset_collection',
            'project_data' => $projectData,
        ])->delay(now()->addDays(3));
    }

    /**
     * Extract customer data
     */
    private function extractCustomerData(): array
    {
        if ($this->model instanceof Quotation) {
            return [
                'name' => $this->model->customer_name,
                'email' => $this->model->customer_email,
                'phone' => $this->model->customer_phone,
                'address' => $this->model->customer_address,
            ];
        }

        if ($this->model instanceof Invoice) {
            $quotation = $this->model->quotation;
            return [
                'name' => $quotation?->customer_name ?? 'Valued Customer',
                'email' => $quotation?->customer_email,
                'phone' => $quotation?->customer_phone,
                'address' => $quotation?->customer_address,
            ];
        }

        if ($this->model instanceof Lead) {
            return [
                'name' => $this->model->customer_name,
                'email' => $this->model->customer_email,
                'phone' => $this->model->phone,
                'address' => null,
            ];
        }

        return [];
    }

    /**
     * Extract project data
     */
    private function extractProjectData(): array
    {
        if ($this->model instanceof Quotation) {
            return [
                'title' => $this->model->title ?? 'Project',
                'value' => $this->model->total,
                'completion_date' => $this->model->accepted_at ?? now(),
                'services' => $this->model->items->pluck('description')->toArray(),
                'duration' => $this->model->created_at->diffInDays(now()),
            ];
        }

        if ($this->model instanceof Invoice) {
            return [
                'title' => $this->model->quotation?->title ?? 'Project',
                'value' => $this->model->total,
                'completion_date' => now(),
                'services' => $this->model->items->pluck('description')->toArray(),
                'duration' => $this->model->created_at->diffInDays(now()),
            ];
        }

        return [
            'title' => 'Project',
            'value' => 0,
            'completion_date' => now(),
            'services' => [],
            'duration' => 0,
        ];
    }

    /**
     * Get team members
     */
    private function getTeamMembers(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->model instanceof Quotation && $this->model->team) {
            return $this->model->team->members;
        }

        if ($this->model instanceof Invoice && $this->model->quotation?->team) {
            return $this->model->quotation->team->members;
        }

        return collect();
    }

    /**
     * Get managers
     */
    private function getManagers(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\User::where('company_id', $this->model->company_id)
            ->role(['sales_manager', 'company_manager'])
            ->get();
    }

    /**
     * Check if qualifies for case study
     */
    private function qualifiesForCaseStudy(): bool
    {
        if ($this->model instanceof Quotation) {
            return $this->model->total >= 50000 || 
                   $this->model->items->count() >= 10 ||
                   in_array($this->model->customerSegment?->name, ['Dealer', 'Contractor']);
        }

        if ($this->model instanceof Invoice) {
            return $this->model->total >= 50000;
        }

        return false;
    }

    /**
     * Determine industry from model data
     */
    private function determineIndustry(): string
    {
        if ($this->model instanceof Quotation) {
            return $this->model->customerSegment?->name ?? 'Construction';
        }

        return 'General';
    }

    /**
     * Get previous requests for follow-up
     */
    private function getPreviousRequests(): \Illuminate\Support\Collection
    {
        // This would query for previous testimonial/case study requests
        // For now, return empty collection
        return collect();
    }

    /**
     * Process follow-up request
     */
    private function processFollowUpRequest($request): void
    {
        // Process individual follow-up requests
        Log::info('Processing follow-up request', ['request' => $request]);
    }

    /**
     * Dispatch webhook for testimonial request
     */
    private function dispatchTestimonialWebhook(Testimonial $testimonial, array $customerData): void
    {
        try {
            $webhookService = app(WebhookEventService::class);
            
            $testimonialData = [
                'testimonial_id' => $testimonial->id,
                'customer_name' => $customerData['name'],
                'customer_email' => $customerData['email'],
                'customer_phone' => $customerData['phone'],
                'customer_company' => $customerData['address'] ?? null,
                'project_title' => $testimonial->project_title,
                'project_value' => $testimonial->project_value,
                'completion_date' => $testimonial->completion_date?->toISOString(),
                'services_provided' => $testimonial->services_provided ?? [],
                'project_duration' => $testimonial->metadata['project_data']['duration'] ?? null,
                'request_sent_at' => $testimonial->request_sent_at?->toISOString(),
                'qualification_criteria' => $this->getTestimonialQualificationCriteria(),
                'follow_up_scheduled' => now()->addWeek()->toISOString(),
                'trigger_event' => $this->options['trigger'] ?? 'automated',
                'qualification_score' => $this->calculateTestimonialScore(),
            ];
            
            $webhookService->testimonialRequested($this->model, $testimonialData);
            
        } catch (\Exception $e) {
            Log::error('Failed to dispatch testimonial request webhook', [
                'testimonial_id' => $testimonial->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Dispatch webhook for case study approval request
     */
    private function dispatchCaseStudyWebhook(CaseStudy $caseStudy, array $customerData): void
    {
        try {
            $webhookService = app(WebhookEventService::class);
            
            $caseStudyData = [
                'case_study_id' => $caseStudy->id,
                'case_study_title' => $caseStudy->title,
                'customer_name' => $customerData['name'],
                'customer_email' => $customerData['email'],
                'customer_phone' => $customerData['phone'],
                'customer_company' => $customerData['address'] ?? null,
                'industry' => $caseStudy->industry,
                'challenge' => $caseStudy->challenge,
                'solution' => $caseStudy->solution,
                'results' => $caseStudy->results,
                'project_value' => $caseStudy->project_value,
                'status' => $caseStudy->status,
                'qualification_criteria' => $this->getCaseStudyQualificationCriteria(),
                'approval_requested_at' => $caseStudy->metadata['approval_requested_at'] ?? now()->toISOString(),
                'internal_team_notified' => true,
                'estimated_completion' => now()->addWeeks(2)->toISOString(),
                'trigger_event' => $this->options['trigger'] ?? 'automated',
                'qualification_score' => $this->calculateCaseStudyScore(),
            ];
            
            $webhookService->caseStudyApprovalRequested($this->model, $caseStudyData);
            
        } catch (\Exception $e) {
            Log::error('Failed to dispatch case study approval webhook', [
                'case_study_id' => $caseStudy->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get testimonial qualification criteria
     */
    private function getTestimonialQualificationCriteria(): array
    {
        $criteria = ['customer_has_contact_info'];
        
        if ($this->model instanceof Quotation) {
            if ($this->model->total >= 5000) $criteria[] = 'high_value_project';
            if ($this->model->status === 'ACCEPTED') $criteria[] = 'project_accepted';
        }
        
        if ($this->model instanceof Invoice) {
            if ($this->model->status === 'PAID') $criteria[] = 'payment_completed';
            if ($this->model->total >= 5000) $criteria[] = 'high_value_invoice';
        }
        
        return $criteria;
    }

    /**
     * Get case study qualification criteria
     */
    private function getCaseStudyQualificationCriteria(): array
    {
        $criteria = [];
        
        if ($this->model instanceof Quotation) {
            if ($this->model->total >= 50000) $criteria[] = 'high_value_project';
            if ($this->model->items->count() >= 10) $criteria[] = 'complex_project';
            if (in_array($this->model->customerSegment?->name, ['Dealer', 'Contractor'])) {
                $criteria[] = 'strategic_customer_segment';
            }
        }
        
        return $criteria;
    }

    /**
     * Calculate testimonial qualification score
     */
    private function calculateTestimonialScore(): ?float
    {
        $score = 0;
        $maxScore = 100;
        
        // Project value score (30% weight)
        if ($this->model instanceof Quotation || $this->model instanceof Invoice) {
            $value = $this->model->total;
            if ($value >= 10000) $score += 30;
            elseif ($value >= 5000) $score += 20;
            elseif ($value >= 1000) $score += 10;
        }
        
        // Customer data completeness (40% weight)
        $customerData = $this->extractCustomerData();
        if (!empty($customerData['email'])) $score += 20;
        if (!empty($customerData['phone'])) $score += 20;
        
        // Project completion status (30% weight)
        if ($this->model instanceof Invoice && $this->model->status === 'PAID') {
            $score += 30;
        } elseif ($this->model instanceof Quotation && $this->model->status === 'ACCEPTED') {
            $score += 20;
        }
        
        return round(($score / $maxScore) * 100, 2);
    }

    /**
     * Calculate case study qualification score
     */
    private function calculateCaseStudyScore(): ?float
    {
        $score = 0;
        $maxScore = 100;
        
        if ($this->model instanceof Quotation) {
            // Project value (50% weight)
            if ($this->model->total >= 100000) $score += 50;
            elseif ($this->model->total >= 50000) $score += 35;
            elseif ($this->model->total >= 25000) $score += 20;
            
            // Project complexity (30% weight)
            if ($this->model->items->count() >= 20) $score += 30;
            elseif ($this->model->items->count() >= 10) $score += 20;
            elseif ($this->model->items->count() >= 5) $score += 10;
            
            // Customer segment (20% weight)
            if (in_array($this->model->customerSegment?->name, ['Dealer', 'Contractor'])) {
                $score += 20;
            }
        }
        
        return round(($score / $maxScore) * 100, 2);
    }

    /**
     * Handle failed job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('RequestReviewJob failed', [
            'model_type' => get_class($this->model),
            'model_id' => $this->model->id,
            'request_type' => $this->requestType,
            'error' => $exception->getMessage(),
        ]);
    }
}