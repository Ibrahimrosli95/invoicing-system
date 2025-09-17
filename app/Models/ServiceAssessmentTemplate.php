<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServiceAssessmentTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'service_type',
        'template_name',
        'template_description',
        'template_version',
        'is_active',
        'is_default',
        'requires_approval',
        'sections_config',
        'scoring_method',
        'passing_score',
        'risk_thresholds',
        'critical_items',
        'usage_count',
        'last_used_at',
        'created_by',
        'approved_by',
        'approved_at',
        'parent_template_id',
        'change_notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'requires_approval' => 'boolean',
        'sections_config' => 'array',
        'risk_thresholds' => 'array',
        'critical_items' => 'array',
        'passing_score' => 'decimal:2',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Service Type Constants
    const SERVICE_WATERPROOFING = 'waterproofing';
    const SERVICE_PAINTING = 'painting';
    const SERVICE_SPORTS_COURT = 'sports_court';
    const SERVICE_INDUSTRIAL = 'industrial';

    // Scoring Method Constants
    const SCORING_WEIGHTED = 'weighted';
    const SCORING_SIMPLE = 'simple';
    const SCORING_CRITICAL_POINTS = 'critical_points';

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
     * Get all scoring methods.
     */
    public static function getScoringMethods(): array
    {
        return [
            self::SCORING_WEIGHTED => 'Weighted Scoring',
            self::SCORING_SIMPLE => 'Simple Additive',
            self::SCORING_CRITICAL_POINTS => 'Critical Points Only',
        ];
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parentTemplate(): BelongsTo
    {
        return $this->belongsTo(ServiceAssessmentTemplate::class, 'parent_template_id');
    }

    public function childTemplates(): HasMany
    {
        return $this->hasMany(ServiceAssessmentTemplate::class, 'parent_template_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'service_template_id');
    }

    // Scopes
    public function scopeForCompany(Builder $query, $companyId = null): Builder
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        return $query->where('company_id', $companyId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForServiceType(Builder $query, string $serviceType): Builder
    {
        return $query->where('service_type', $serviceType);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereNotNull('approved_at');
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('requires_approval', true)
                    ->whereNull('approved_at');
    }

    public function scopeRecentlyUsed(Builder $query): Builder
    {
        return $query->whereNotNull('last_used_at')
                    ->orderBy('last_used_at', 'desc');
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->where('usage_count', '>', 0)
                    ->orderBy('usage_count', 'desc');
    }

    // Business Logic Methods

    /**
     * Get default risk thresholds for service type.
     */
    public function getDefaultRiskThresholds(): array
    {
        $defaultThresholds = [
            self::SERVICE_WATERPROOFING => [
                'low_max' => 66,
                'medium_max' => 41,
                'high_max' => 21,
                'critical_min' => 0
            ],
            self::SERVICE_PAINTING => [
                'low_max' => 51,
                'medium_max' => 31,
                'high_max' => 16,
                'critical_min' => 0
            ],
            self::SERVICE_SPORTS_COURT => [
                'low_max' => 56,
                'medium_max' => 36,
                'high_max' => 19,
                'critical_min' => 0
            ],
            self::SERVICE_INDUSTRIAL => [
                'low_max' => 71,
                'medium_max' => 46,
                'high_max' => 26,
                'critical_min' => 0
            ],
        ];

        return $defaultThresholds[$this->service_type] ?? $defaultThresholds[self::SERVICE_WATERPROOFING];
    }

    /**
     * Get effective risk thresholds (custom or default).
     */
    public function getEffectiveRiskThresholds(): array
    {
        return $this->risk_thresholds ?? $this->getDefaultRiskThresholds();
    }

    /**
     * Check if template is approved.
     */
    public function isApproved(): bool
    {
        if (!$this->requires_approval) {
            return true;
        }

        return $this->approved_at !== null;
    }

    /**
     * Check if template can be used.
     */
    public function canBeUsed(): bool
    {
        return $this->is_active && $this->isApproved();
    }

    /**
     * Mark template as default for service type.
     */
    public function markAsDefault(): bool
    {
        return DB::transaction(function () {
            // Unmark other default templates for same service type and company
            static::forCompany($this->company_id)
                  ->forServiceType($this->service_type)
                  ->where('id', '!=', $this->id)
                  ->update(['is_default' => false]);

            $this->is_default = true;
            return $this->save();
        });
    }

    /**
     * Approve template.
     */
    public function approve(User $approver): bool
    {
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        return $this->save();
    }

    /**
     * Revoke approval.
     */
    public function revokeApproval(): bool
    {
        $this->approved_by = null;
        $this->approved_at = null;
        return $this->save();
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): bool
    {
        $this->increment('usage_count');
        $this->last_used_at = now();
        return $this->save();
    }

    /**
     * Create new version of template.
     */
    public function createNewVersion(array $changes, string $changeNotes = null): self
    {
        $newVersion = $this->replicate();
        $newVersion->parent_template_id = $this->id;
        $newVersion->template_version = $this->incrementVersion();
        $newVersion->change_notes = $changeNotes;
        $newVersion->is_default = false;
        $newVersion->approved_at = null;
        $newVersion->approved_by = null;
        $newVersion->usage_count = 0;
        $newVersion->last_used_at = null;

        // Apply changes
        foreach ($changes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $newVersion->$key = $value;
            }
        }

        $newVersion->save();
        return $newVersion;
    }

    /**
     * Increment version number.
     */
    protected function incrementVersion(): string
    {
        $parts = explode('.', $this->template_version);
        $major = (int) ($parts[0] ?? 1);
        $minor = (int) ($parts[1] ?? 0);

        return $major . '.' . ($minor + 1);
    }

    /**
     * Generate sections from template configuration.
     */
    public function generateAssessmentSections(Assessment $assessment): array
    {
        $sectionsConfig = $this->sections_config ?? [];
        $createdSections = [];

        foreach ($sectionsConfig as $index => $sectionConfig) {
            $section = AssessmentSection::create([
                'assessment_id' => $assessment->id,
                'section_name' => $sectionConfig['name'] ?? "Section " . ($index + 1),
                'section_description' => $sectionConfig['description'] ?? null,
                'section_order' => $sectionConfig['order'] ?? ($index + 1),
                'max_score' => $sectionConfig['max_score'] ?? 100,
                'weight' => $sectionConfig['weight'] ?? 1.0,
                'is_critical' => $sectionConfig['is_critical'] ?? false,
                'requires_photo' => $sectionConfig['requires_photo'] ?? false,
            ]);

            // Generate items for this section
            if (isset($sectionConfig['items']) && is_array($sectionConfig['items'])) {
                foreach ($sectionConfig['items'] as $itemIndex => $itemConfig) {
                    AssessmentItem::create([
                        'section_id' => $section->id,
                        'item_name' => $itemConfig['name'] ?? "Item " . ($itemIndex + 1),
                        'item_description' => $itemConfig['description'] ?? null,
                        'item_order' => $itemConfig['order'] ?? ($itemIndex + 1),
                        'item_type' => $itemConfig['type'] ?? AssessmentItem::TYPE_RATING,
                        'item_options' => $itemConfig['options'] ?? null,
                        'max_points' => $itemConfig['max_points'] ?? 10,
                        'is_critical' => $itemConfig['is_critical'] ?? false,
                        'photo_required' => $itemConfig['photo_required'] ?? false,
                        'min_photos' => $itemConfig['min_photos'] ?? 0,
                        'max_photos' => $itemConfig['max_photos'] ?? 5,
                    ]);
                }
            }

            $createdSections[] = $section;
        }

        return $createdSections;
    }

    /**
     * Get template complexity score.
     */
    public function getComplexityScore(): int
    {
        $sectionsConfig = $this->sections_config ?? [];
        $score = 0;

        $score += count($sectionsConfig) * 2; // Base score for sections

        foreach ($sectionsConfig as $section) {
            $items = $section['items'] ?? [];
            $score += count($items); // Score for items

            foreach ($items as $item) {
                if (($item['is_critical'] ?? false)) {
                    $score += 2; // Extra score for critical items
                }
                if (($item['photo_required'] ?? false)) {
                    $score += 1; // Extra score for photo requirements
                }
            }
        }

        return $score;
    }

    /**
     * Get template usage statistics.
     */
    public function getUsageStatistics(): array
    {
        $assessments = $this->assessments();

        return [
            'total_assessments' => $assessments->count(),
            'completed_assessments' => $assessments->where('status', Assessment::STATUS_COMPLETED)->count(),
            'average_score' => $assessments->whereNotNull('overall_score')->avg('overall_score'),
            'last_used' => $this->last_used_at?->format('Y-m-d H:i:s'),
            'usage_count' => $this->usage_count,
            'success_rate' => $this->calculateSuccessRate(),
        ];
    }

    /**
     * Calculate success rate based on passing score.
     */
    protected function calculateSuccessRate(): ?float
    {
        $completedAssessments = $this->assessments()
            ->where('status', Assessment::STATUS_COMPLETED)
            ->whereNotNull('overall_score')
            ->get();

        if ($completedAssessments->isEmpty()) {
            return null;
        }

        $passedCount = $completedAssessments->where('overall_score', '>=', $this->passing_score)->count();
        return round(($passedCount / $completedAssessments->count()) * 100, 2);
    }

    /**
     * Validate sections configuration.
     */
    public function validateSectionsConfig(): array
    {
        $errors = [];
        $sectionsConfig = $this->sections_config ?? [];

        if (empty($sectionsConfig)) {
            $errors[] = 'Template must have at least one section';
            return $errors;
        }

        foreach ($sectionsConfig as $index => $section) {
            $sectionNum = $index + 1;

            if (empty($section['name'])) {
                $errors[] = "Section {$sectionNum}: Name is required";
            }

            if (!isset($section['items']) || !is_array($section['items']) || empty($section['items'])) {
                $errors[] = "Section {$sectionNum}: Must have at least one item";
                continue;
            }

            foreach ($section['items'] as $itemIndex => $item) {
                $itemNum = $itemIndex + 1;

                if (empty($item['name'])) {
                    $errors[] = "Section {$sectionNum}, Item {$itemNum}: Name is required";
                }

                if (empty($item['type'])) {
                    $errors[] = "Section {$sectionNum}, Item {$itemNum}: Type is required";
                }

                if (($item['max_points'] ?? 0) <= 0) {
                    $errors[] = "Section {$sectionNum}, Item {$itemNum}: Max points must be greater than 0";
                }
            }
        }

        return $errors;
    }

    /**
     * Get template service type label.
     */
    public function getServiceTypeLabel(): string
    {
        return static::getServiceTypes()[$this->service_type] ?? $this->service_type;
    }

    /**
     * Get template scoring method label.
     */
    public function getScoringMethodLabel(): string
    {
        return static::getScoringMethods()[$this->scoring_method] ?? $this->scoring_method;
    }
}