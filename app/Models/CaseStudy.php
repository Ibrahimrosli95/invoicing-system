<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CaseStudy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'title',
        'client_name',
        'client_company',
        'client_industry',
        'project_location',
        'project_type',
        'project_overview',
        'challenge_description',
        'solution_description',
        'results_achieved',
        'project_value',
        'project_start_date',
        'project_completion_date',
        'project_duration_days',
        'project_scope',
        'services_provided',
        'before_images',
        'after_images',
        'process_images',
        'hero_image',
        'client_contact_person',
        'client_position',
        'client_email',
        'client_phone',
        'client_consent_given',
        'consent_date',
        'consent_method',
        'technical_specifications',
        'materials_used',
        'equipment_used',
        'team_size',
        'team_roles',
        'key_metrics',
        'cost_savings_achieved',
        'completion_ahead_days',
        'client_satisfaction_score',
        'repeat_client',
        'status',
        'approval_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'is_featured',
        'show_client_name',
        'show_project_value',
        'allow_public_display',
        'display_order',
        'meta_title',
        'meta_description',
        'keywords',
        'case_study_slug',
        'awards_received',
        'media_coverage',
        'certifications_demonstrated',
        'view_count',
        'download_count',
        'share_count',
        'usage_stats',
        'related_quotation_id',
        'related_invoice_id',
        'related_lead_id',
        'pdf_version',
        'additional_documents',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'services_provided' => 'array',
        'before_images' => 'array',
        'after_images' => 'array',
        'process_images' => 'array',
        'technical_specifications' => 'array',
        'materials_used' => 'array',
        'equipment_used' => 'array',
        'team_roles' => 'array',
        'key_metrics' => 'array',
        'keywords' => 'array',
        'awards_received' => 'array',
        'certifications_demonstrated' => 'array',
        'usage_stats' => 'array',
        'additional_documents' => 'array',
        'project_value' => 'decimal:2',
        'cost_savings_achieved' => 'decimal:2',
        'client_satisfaction_score' => 'decimal:2',
        'project_start_date' => 'date',
        'project_completion_date' => 'date',
        'consent_date' => 'datetime',
        'approved_at' => 'datetime',
        'client_consent_given' => 'boolean',
        'repeat_client' => 'boolean',
        'is_featured' => 'boolean',
        'show_client_name' => 'boolean',
        'show_project_value' => 'boolean',
        'allow_public_display' => 'boolean',
        'project_duration_days' => 'integer',
        'team_size' => 'integer',
        'completion_ahead_days' => 'integer',
        'display_order' => 'integer',
        'view_count' => 'integer',
        'download_count' => 'integer',
        'share_count' => 'integer',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_REVIEW = 'review';
    const STATUS_APPROVED = 'approved';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    const APPROVAL_STATUS_PENDING = 'pending_approval';
    const APPROVAL_STATUS_APPROVED = 'approved';
    const APPROVAL_STATUS_REJECTED = 'rejected';

    protected static function booted()
    {
        static::creating(function ($caseStudy) {
            if (!$caseStudy->uuid) {
                $caseStudy->uuid = Str::uuid();
            }
            
            // Generate slug from title if not provided
            if (!$caseStudy->case_study_slug && $caseStudy->title) {
                $caseStudy->case_study_slug = Str::slug($caseStudy->title);
            }
            
            // Set company_id from authenticated user if not set
            if (!$caseStudy->company_id && auth()->check()) {
                $caseStudy->company_id = auth()->user()->company_id;
            }
            
            // Set created_by from authenticated user if not set
            if (!$caseStudy->created_by && auth()->check()) {
                $caseStudy->created_by = auth()->id();
            }

            // Calculate project duration if dates are provided
            if ($caseStudy->project_start_date && $caseStudy->project_completion_date) {
                $startDate = Carbon::parse($caseStudy->project_start_date);
                $endDate = Carbon::parse($caseStudy->project_completion_date);
                $caseStudy->project_duration_days = $startDate->diffInDays($endDate);
            }
        });

        static::updating(function ($caseStudy) {
            // Set updated_by from authenticated user
            if (auth()->check()) {
                $caseStudy->updated_by = auth()->id();
            }

            // Update slug if title changed
            if ($caseStudy->isDirty('title')) {
                $caseStudy->case_study_slug = Str::slug($caseStudy->title);
            }

            // Recalculate duration if dates changed
            if ($caseStudy->isDirty(['project_start_date', 'project_completion_date'])) {
                if ($caseStudy->project_start_date && $caseStudy->project_completion_date) {
                    $startDate = Carbon::parse($caseStudy->project_start_date);
                    $endDate = Carbon::parse($caseStudy->project_completion_date);
                    $caseStudy->project_duration_days = $startDate->diffInDays($endDate);
                }
            }
        });
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function relatedQuotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'related_quotation_id');
    }

    public function relatedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'related_invoice_id');
    }

    public function relatedLead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'related_lead_id');
    }

    public function proofViews(): MorphMany
    {
        return $this->morphMany(ProofView::class, 'proof');
    }

    // Scopes
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?: auth()->user()->company_id;
        return $query->where('company_id', $companyId);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                    ->where('allow_public_display', true);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::APPROVAL_STATUS_APPROVED);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeWithConsent($query)
    {
        return $query->where('client_consent_given', true);
    }

    public function scopeByIndustry($query, $industry)
    {
        return $query->where('client_industry', $industry);
    }

    public function scopeByProjectType($query, $projectType)
    {
        return $query->where('project_type', $projectType);
    }

    public function scopeCompletedAfter($query, Carbon $date)
    {
        return $query->where('project_completion_date', '>=', $date);
    }

    public function scopeWithImages($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('before_images')
              ->orWhereNotNull('after_images')
              ->orWhereNotNull('hero_image');
        });
    }

    // Business Logic Methods
    public function canBePublished(): bool
    {
        return $this->approval_status === self::APPROVAL_STATUS_APPROVED 
               && $this->client_consent_given 
               && $this->allow_public_display;
    }

    public function approve($userId = null): bool
    {
        $userId = $userId ?: auth()->id();
        
        $this->update([
            'approval_status' => self::APPROVAL_STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $userId,
            'rejection_reason' => null,
        ]);

        // Auto-publish if all conditions are met
        if ($this->canBePublished()) {
            $this->publish();
        }

        return true;
    }

    public function reject($reason, $userId = null): bool
    {
        $userId = $userId ?: auth()->id();
        
        return $this->update([
            'approval_status' => self::APPROVAL_STATUS_REJECTED,
            'rejection_reason' => $reason,
            'approved_at' => null,
            'approved_by' => null,
            'status' => self::STATUS_DRAFT,
        ]);
    }

    public function publish(): bool
    {
        if (!$this->canBePublished()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_PUBLISHED,
        ]);
    }

    public function archive(): bool
    {
        return $this->update([
            'status' => self::STATUS_ARCHIVED,
            'allow_public_display' => false,
        ]);
    }

    public function submitForReview(): bool
    {
        return $this->update([
            'status' => self::STATUS_REVIEW,
            'approval_status' => self::APPROVAL_STATUS_PENDING,
        ]);
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }

    public function incrementShares(): void
    {
        $this->increment('share_count');
    }

    // Utility Methods
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_REVIEW => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_PUBLISHED => 'blue',
            self::STATUS_ARCHIVED => 'gray',
            default => 'gray'
        };
    }

    public function getApprovalStatusBadgeColor(): string
    {
        return match($this->approval_status) {
            self::APPROVAL_STATUS_PENDING => 'yellow',
            self::APPROVAL_STATUS_APPROVED => 'green',
            self::APPROVAL_STATUS_REJECTED => 'red',
            default => 'gray'
        };
    }

    public function getClientDisplayName(): string
    {
        if (!$this->show_client_name) {
            return 'Confidential Client';
        }

        $name = $this->client_name;
        
        if ($this->client_company) {
            $name .= ', ' . $this->client_company;
        }

        if ($this->client_position) {
            $name .= ' (' . $this->client_position . ')';
        }

        return $name;
    }

    public function getProjectDuration(): string
    {
        if (!$this->project_duration_days) {
            return 'Duration not specified';
        }

        if ($this->project_duration_days == 1) {
            return '1 day';
        }

        if ($this->project_duration_days < 7) {
            return $this->project_duration_days . ' days';
        }

        if ($this->project_duration_days < 30) {
            $weeks = round($this->project_duration_days / 7, 1);
            return $weeks . ' weeks';
        }

        $months = round($this->project_duration_days / 30, 1);
        return $months . ' months';
    }

    public function getProjectValue(): string
    {
        if (!$this->project_value) {
            return 'Value not disclosed';
        }

        if (!$this->show_project_value) {
            return 'Value confidential';
        }

        return 'RM ' . number_format($this->project_value, 2);
    }

    public function getCostSavings(): string
    {
        if (!$this->cost_savings_achieved) {
            return 'No savings reported';
        }

        return 'RM ' . number_format($this->cost_savings_achieved, 2) . ' saved';
    }

    public function getClientSatisfactionRating(): string
    {
        if (!$this->client_satisfaction_score) {
            return 'Not rated';
        }

        $stars = str_repeat('★', (int)$this->client_satisfaction_score);
        $emptyStars = str_repeat('☆', 5 - (int)$this->client_satisfaction_score);
        
        return $stars . $emptyStars . ' (' . $this->client_satisfaction_score . '/5.0)';
    }

    public function getProjectTimeline(): string
    {
        if (!$this->project_start_date || !$this->project_completion_date) {
            return 'Timeline not specified';
        }

        $start = $this->project_start_date->format('M j, Y');
        $end = $this->project_completion_date->format('M j, Y');

        $timeline = "From {$start} to {$end}";

        if ($this->completion_ahead_days > 0) {
            $timeline .= " (completed {$this->completion_ahead_days} days ahead of schedule)";
        }

        return $timeline;
    }

    public function hasBeforeAfterImages(): bool
    {
        return !empty($this->before_images) && !empty($this->after_images);
    }

    public function hasProcessImages(): bool
    {
        return !empty($this->process_images);
    }

    public function getImageCount(): int
    {
        $count = 0;
        
        if (!empty($this->before_images)) {
            $count += count($this->before_images);
        }
        
        if (!empty($this->after_images)) {
            $count += count($this->after_images);
        }
        
        if (!empty($this->process_images)) {
            $count += count($this->process_images);
        }

        if ($this->hero_image) {
            $count += 1;
        }

        return $count;
    }

    public function trackUsage(string $context, array $metadata = []): void
    {
        $stats = $this->usage_stats ?: [];
        $stats[] = [
            'context' => $context,
            'metadata' => $metadata,
            'tracked_at' => now()->toISOString(),
        ];
        
        $this->update(['usage_stats' => $stats]);
    }

    public function getUsageCount(string $context = null): int
    {
        if (!$this->usage_stats) {
            return 0;
        }

        if ($context) {
            return count(array_filter($this->usage_stats, fn($stat) => $stat['context'] === $context));
        }

        return count($this->usage_stats);
    }

    public function isRecentProject(): bool
    {
        if (!$this->project_completion_date) {
            return false;
        }

        return $this->project_completion_date->isAfter(now()->subYears(2));
    }

    public function needsApproval(): bool
    {
        return $this->approval_status === self::APPROVAL_STATUS_PENDING;
    }

    public function getUrl(): string
    {
        return route('case-studies.show', $this->case_study_slug);
    }

    public function getKeywords(): string
    {
        if (!$this->keywords) {
            return '';
        }

        return implode(', ', $this->keywords);
    }

    public function hasAwards(): bool
    {
        return !empty($this->awards_received);
    }

    public function getAwardsCount(): int
    {
        return $this->awards_received ? count($this->awards_received) : 0;
    }

    public function demonstratesCertifications(): bool
    {
        return !empty($this->certifications_demonstrated);
    }

    public function getROI(): ?float
    {
        if (!$this->project_value || !$this->cost_savings_achieved) {
            return null;
        }

        return ($this->cost_savings_achieved / $this->project_value) * 100;
    }
}
