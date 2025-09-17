<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Testimonial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'customer_name',
        'customer_email',
        'customer_company',
        'customer_position',
        'customer_phone',
        'title',
        'content',
        'summary',
        'rating',
        'project_type',
        'project_value',
        'project_completion_date',
        'customer_photo',
        'customer_signature',
        'project_images',
        'status',
        'approval_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'allow_public_display',
        'show_customer_name',
        'show_customer_company',
        'is_featured',
        'display_order',
        'consent_given',
        'consent_date',
        'consent_method',
        'marketing_consent',
        'view_count',
        'click_count',
        'usage_stats',
        'collection_method',
        'source_url',
        'form_data',
        'related_quotation_id',
        'related_invoice_id',
        'related_lead_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'project_images' => 'array',
        'usage_stats' => 'array',
        'form_data' => 'array',
        'project_value' => 'decimal:2',
        'project_completion_date' => 'date',
        'approved_at' => 'datetime',
        'consent_date' => 'datetime',
        'consent_given' => 'boolean',
        'marketing_consent' => 'boolean',
        'allow_public_display' => 'boolean',
        'show_customer_name' => 'boolean',
        'show_customer_company' => 'boolean',
        'is_featured' => 'boolean',
        'view_count' => 'integer',
        'click_count' => 'integer',
        'display_order' => 'integer',
        'rating' => 'integer',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    const APPROVAL_STATUS_PENDING = 'pending_approval';
    const APPROVAL_STATUS_APPROVED = 'approved';
    const APPROVAL_STATUS_REJECTED = 'rejected';

    const COLLECTION_METHOD_MANUAL = 'manual';
    const COLLECTION_METHOD_EMAIL_REQUEST = 'email_request';
    const COLLECTION_METHOD_FORM_SUBMISSION = 'form_submission';
    const COLLECTION_METHOD_IMPORTED = 'imported';

    protected static function booted()
    {
        static::creating(function ($testimonial) {
            if (!$testimonial->uuid) {
                $testimonial->uuid = Str::uuid();
            }
            
            // Set company_id from authenticated user if not set
            if (!$testimonial->company_id && auth()->check()) {
                $testimonial->company_id = auth()->user()->company_id;
            }
            
            // Set created_by from authenticated user if not set
            if (!$testimonial->created_by && auth()->check()) {
                $testimonial->created_by = auth()->id();
            }
        });

        static::updating(function ($testimonial) {
            // Set updated_by from authenticated user
            if (auth()->check()) {
                $testimonial->updated_by = auth()->id();
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
        return $query->where('consent_given', true);
    }

    public function scopeByRating($query, $minRating = null)
    {
        if ($minRating) {
            return $query->where('rating', '>=', $minRating);
        }
        return $query->whereNotNull('rating');
    }

    public function scopeForProjectType($query, $projectType)
    {
        return $query->where('project_type', $projectType);
    }

    // Business Logic Methods
    public function canBePublished(): bool
    {
        return $this->approval_status === self::APPROVAL_STATUS_APPROVED 
               && $this->consent_given 
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
            'status' => self::STATUS_REJECTED,
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

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function incrementClicks(): void
    {
        $this->increment('click_count');
    }

    // Utility Methods
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
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

    public function getCustomerDisplayName(): string
    {
        if (!$this->show_customer_name) {
            return 'Anonymous Customer';
        }

        $name = $this->customer_name;
        
        if ($this->show_customer_company && $this->customer_company) {
            $name .= ', ' . $this->customer_company;
        }

        if ($this->customer_position) {
            $name .= ' (' . $this->customer_position . ')';
        }

        return $name;
    }

    public function getRatingStars(): string
    {
        if (!$this->rating) {
            return '';
        }

        $stars = str_repeat('★', $this->rating);
        $emptyStars = str_repeat('☆', 5 - $this->rating);
        
        return $stars . $emptyStars;
    }

    public function getSummaryOrTruncated(int $length = 150): string
    {
        if ($this->summary) {
            return $this->summary;
        }

        return Str::limit($this->content, $length);
    }

    public function hasProjectImages(): bool
    {
        return !empty($this->project_images);
    }

    public function getProjectCompletedAgo(): string
    {
        if (!$this->project_completion_date) {
            return 'Date not specified';
        }

        return $this->project_completion_date->diffForHumans();
    }

    public function getProjectValue(): string
    {
        if (!$this->project_value) {
            return 'Not specified';
        }

        return 'RM ' . number_format($this->project_value, 2);
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

    public function isRecentlyCreated(): bool
    {
        return $this->created_at->isAfter(now()->subDays(30));
    }

    public function needsApproval(): bool
    {
        return $this->approval_status === self::APPROVAL_STATUS_PENDING;
    }
}
