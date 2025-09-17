<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Assessment extends Model
{
    protected $fillable = [
        'uuid',
        'assessment_code',
        'company_id',
        'lead_id',
        'team_id',
        'assigned_to',
        'created_by',
        'client_name',
        'company',
        'contact_email',
        'contact_phone',
        'property_address',
        'property_type',
        'property_size',
        'property_age',
        'service_type',
        'assessment_type',
        'priority',
        'requested_date',
        'scheduled_date',
        'completed_date',
        'status',
        'risk_level',
        'overall_score',
        'summary',
        'recommendations',
        'internal_notes',
        'quotation_id',
        'service_template_id',
        'report_generated',
        'report_path',
        'weather_conditions',
        'temperature',
        'humidity_percentage',
        'estimated_cost',
        'budget_range',
        'timeline_urgency',
    ];

    protected $casts = [
        'requested_date' => 'date',
        'scheduled_date' => 'datetime',
        'completed_date' => 'datetime',
        'overall_score' => 'decimal:2',
        'temperature' => 'decimal:1',
        'humidity_percentage' => 'integer',
        'estimated_cost' => 'decimal:2',
        'report_generated' => 'boolean',
        'property_age' => 'integer',
    ];

    // Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REPORTED = 'reported';
    const STATUS_QUOTED = 'quoted';

    // Risk Level Constants
    const RISK_LOW = 'low';
    const RISK_MEDIUM = 'medium';
    const RISK_HIGH = 'high';
    const RISK_CRITICAL = 'critical';

    // Service Type Constants
    const SERVICE_WATERPROOFING = 'waterproofing';
    const SERVICE_PAINTING = 'painting';
    const SERVICE_SPORTS_COURT = 'sports_court';
    const SERVICE_INDUSTRIAL = 'industrial';

    // Assessment Type Constants
    const TYPE_INITIAL = 'initial';
    const TYPE_DETAILED = 'detailed';
    const TYPE_MAINTENANCE = 'maintenance';
    const TYPE_WARRANTY = 'warranty';
    const TYPE_COMPLIANCE = 'compliance';

    // Priority Constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($assessment) {
            if (empty($assessment->uuid)) {
                $assessment->uuid = (string) Str::uuid();
            }
            if (empty($assessment->assessment_code)) {
                $assessment->assessment_code = $assessment->generateAssessmentCode();
            }
        });
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REPORTED => 'Reported',
            self::STATUS_QUOTED => 'Quoted',
        ];
    }

    /**
     * Get all service types.
     */
    public static function getServiceTypes(): array
    {
        return [
            self::SERVICE_WATERPROOFING => 'Waterproofing Assessment',
            self::SERVICE_PAINTING => 'Painting Works Assessment',
            self::SERVICE_SPORTS_COURT => 'Sports Court Flooring Assessment',
            self::SERVICE_INDUSTRIAL => 'Industrial Flooring Assessment',
        ];
    }

    /**
     * Get all assessment types.
     */
    public static function getAssessmentTypes(): array
    {
        return [
            self::TYPE_INITIAL => 'Initial Assessment',
            self::TYPE_DETAILED => 'Detailed Assessment',
            self::TYPE_MAINTENANCE => 'Maintenance Assessment',
            self::TYPE_WARRANTY => 'Warranty Assessment',
            self::TYPE_COMPLIANCE => 'Compliance Assessment',
        ];
    }

    /**
     * Get all priority levels.
     */
    public static function getPriorityLevels(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    /**
     * Get all risk levels.
     */
    public static function getRiskLevels(): array
    {
        return [
            self::RISK_LOW => 'Low Risk',
            self::RISK_MEDIUM => 'Medium Risk',
            self::RISK_HIGH => 'High Risk',
            self::RISK_CRITICAL => 'Critical Risk',
        ];
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function serviceTemplate(): BelongsTo
    {
        return $this->belongsTo(ServiceAssessmentTemplate::class, 'service_template_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(AssessmentSection::class)->orderBy('section_order');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AssessmentPhoto::class)->orderBy('display_order');
    }

    public function proofs(): MorphMany
    {
        return $this->morphMany(Proof::class, 'scope')->active()->published()->notExpired();
    }

    public function allProofs(): MorphMany
    {
        return $this->morphMany(Proof::class, 'scope');
    }

    // Scopes
    public function scopeForCompany(Builder $query, $companyId = null): Builder
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        return $query->where('company_id', $companyId);
    }

    public function scopeForUserTeams(Builder $query, $user = null): Builder
    {
        $user = $user ?? auth()->user();
        
        if ($user->hasRole('superadmin') || $user->hasRole('company_manager')) {
            return $query; // Can see all assessments in company
        }

        if ($user->hasRole('sales_manager')) {
            // Can see assessments from teams they manage
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $query->whereIn('team_id', $managedTeamIds);
        }

        if ($user->hasRole('sales_coordinator')) {
            // Can see assessments from teams they coordinate
            $coordinatedTeamIds = Team::where('coordinator_id', $user->id)->pluck('id');
            return $query->whereIn('team_id', $coordinatedTeamIds);
        }

        // Sales executives can only see assessments assigned to them
        return $query->where('assigned_to', $user->id);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByServiceType(Builder $query, string $serviceType): Builder
    {
        return $query->where('service_type', $serviceType);
    }

    public function scopeByRiskLevel(Builder $query, string $riskLevel): Builder
    {
        return $query->where('risk_level', $riskLevel);
    }

    public function scopeScheduledBetween(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('scheduled_date', [$start, $end]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('scheduled_date', '<', now())
                    ->whereIn('status', [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS]);
    }

    public function scopeCompletedThisWeek(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED)
                    ->whereBetween('completed_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    // Business Logic Methods
    
    /**
     * Generate assessment code.
     */
    public function generateAssessmentCode(): string
    {
        $year = now()->year;
        $companyId = $this->company_id ?? auth()->user()->company_id;
        
        // Get next sequence number for this company
        $lastAssessment = static::where('company_id', $companyId)
            ->where('assessment_code', 'like', "ASS-{$year}-%")
            ->orderBy('assessment_code', 'desc')
            ->first();

        if ($lastAssessment && preg_match('/ASS-\d{4}-(\d{6})/', $lastAssessment->assessment_code, $matches)) {
            $nextNumber = intval($matches[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('ASS-%s-%06d', $year, $nextNumber);
    }

    /**
     * Calculate overall score based on sections.
     */
    public function calculateOverallScore(): float
    {
        $totalScore = 0;
        $totalWeight = 0;

        foreach ($this->sections as $section) {
            if ($section->actual_score !== null) {
                $weightedScore = $section->actual_score * $section->weight;
                $totalScore += $weightedScore;
                $totalWeight += $section->weight;
            }
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0;
    }

    /**
     * Determine risk level based on score and service type.
     */
    public function determineRiskLevel(float $score = null): string
    {
        $score = $score ?? $this->overall_score ?? $this->calculateOverallScore();
        
        $thresholds = $this->getRiskThresholds();
        
        if ($score >= $thresholds['low_max']) return self::RISK_LOW;
        if ($score >= $thresholds['medium_max']) return self::RISK_MEDIUM;
        if ($score >= $thresholds['high_max']) return self::RISK_HIGH;
        
        return self::RISK_CRITICAL;
    }

    /**
     * Get risk thresholds for the service type.
     */
    protected function getRiskThresholds(): array
    {
        $thresholds = [
            self::SERVICE_WATERPROOFING => [
                'low_max' => 66, 'medium_max' => 41, 'high_max' => 21, 'critical_min' => 0
            ],
            self::SERVICE_PAINTING => [
                'low_max' => 51, 'medium_max' => 31, 'high_max' => 16, 'critical_min' => 0
            ],
            self::SERVICE_SPORTS_COURT => [
                'low_max' => 56, 'medium_max' => 36, 'high_max' => 19, 'critical_min' => 0
            ],
            self::SERVICE_INDUSTRIAL => [
                'low_max' => 71, 'medium_max' => 46, 'high_max' => 26, 'critical_min' => 0
            ],
        ];

        return $thresholds[$this->service_type] ?? $thresholds[self::SERVICE_WATERPROOFING];
    }

    /**
     * Check if assessment can transition to a new status.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $validTransitions = [
            self::STATUS_DRAFT => [self::STATUS_SCHEDULED],
            self::STATUS_SCHEDULED => [self::STATUS_IN_PROGRESS, self::STATUS_DRAFT],
            self::STATUS_IN_PROGRESS => [self::STATUS_COMPLETED, self::STATUS_SCHEDULED],
            self::STATUS_COMPLETED => [self::STATUS_REPORTED],
            self::STATUS_REPORTED => [self::STATUS_QUOTED],
            self::STATUS_QUOTED => [], // Final status
        ];

        return in_array($newStatus, $validTransitions[$this->status] ?? []);
    }

    /**
     * Mark assessment as completed.
     */
    public function markAsCompleted(): bool
    {
        if (!$this->canTransitionTo(self::STATUS_COMPLETED)) {
            return false;
        }

        $this->status = self::STATUS_COMPLETED;
        $this->completed_date = now();
        $this->overall_score = $this->calculateOverallScore();
        $this->risk_level = $this->determineRiskLevel();

        return $this->save();
    }

    /**
     * Schedule the assessment.
     */
    public function schedule(Carbon $scheduledDate, int $assignedTo): bool
    {
        if (!$this->canTransitionTo(self::STATUS_SCHEDULED)) {
            return false;
        }

        $this->status = self::STATUS_SCHEDULED;
        $this->scheduled_date = $scheduledDate;
        $this->assigned_to = $assignedTo;

        return $this->save();
    }

    /**
     * Start the assessment.
     */
    public function start(): bool
    {
        if (!$this->canTransitionTo(self::STATUS_IN_PROGRESS)) {
            return false;
        }

        $this->status = self::STATUS_IN_PROGRESS;
        return $this->save();
    }

    /**
     * Generate quotation from assessment.
     */
    public function generateQuotation(): ?Quotation
    {
        if ($this->status !== self::STATUS_COMPLETED || $this->quotation_id) {
            return null;
        }

        $quotation = new Quotation([
            'company_id' => $this->company_id,
            'lead_id' => $this->lead_id,
            'team_id' => $this->team_id,
            'assigned_to' => $this->assigned_to,
            'created_by' => auth()->id(),
            'customer_name' => $this->client_name,
            'customer_phone' => $this->contact_phone,
            'customer_email' => $this->contact_email,
            'customer_address' => $this->property_address,
            'title' => "Assessment-Based Quotation for {$this->client_name}",
            'description' => "Generated from Assessment: {$this->assessment_code}",
            'notes' => $this->recommendations,
            'type' => Quotation::TYPE_SERVICE,
        ]);

        if ($quotation->save()) {
            $this->quotation_id = $quotation->id;
            $this->status = self::STATUS_QUOTED;
            $this->save();

            return $quotation;
        }

        return null;
    }

    /**
     * Check if assessment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->scheduled_date && 
               $this->scheduled_date < now() && 
               in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_SCHEDULED => 'blue',
            self::STATUS_IN_PROGRESS => 'yellow',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_REPORTED => 'purple',
            self::STATUS_QUOTED => 'indigo',
            default => 'gray',
        };
    }

    /**
     * Get risk level color for UI.
     */
    public function getRiskColor(): string
    {
        return match($this->risk_level) {
            self::RISK_LOW => 'green',
            self::RISK_MEDIUM => 'yellow',
            self::RISK_HIGH => 'orange',
            self::RISK_CRITICAL => 'red',
            default => 'gray',
        };
    }

    /**
     * Get priority color for UI.
     */
    public function getPriorityColor(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'green',
            self::PRIORITY_MEDIUM => 'yellow',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red',
            default => 'gray',
        };
    }

    /**
     * Get route key name for URL generation.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get assessment progress percentage.
     */
    public function getProgressPercentage(): int
    {
        $totalSections = $this->sections()->count();
        if ($totalSections === 0) return 0;

        $completedSections = $this->sections()->where('status', 'completed')->count();
        return round(($completedSections / $totalSections) * 100);
    }

    /**
     * Check if all critical items are addressed.
     */
    public function hasCriticalIssues(): bool
    {
        return $this->sections()
            ->whereHas('items', function ($query) {
                $query->where('is_critical', true)
                      ->where('risk_factor', 'critical');
            })
            ->exists();
    }

    /**
     * Get assessment duration in minutes.
     */
    public function getDurationInMinutes(): ?int
    {
        if (!$this->scheduled_date || !$this->completed_date) {
            return null;
        }

        return $this->scheduled_date->diffInMinutes($this->completed_date);
    }
}
