<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class AssessmentSection extends Model
{
    protected $fillable = [
        'assessment_id',
        'section_name',
        'section_description',
        'section_order',
        'max_score',
        'actual_score',
        'weight',
        'status',
        'notes',
        'recommendations',
        'is_critical',
        'requires_photo',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'actual_score' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_critical' => 'boolean',
        'requires_photo' => 'boolean',
        'section_order' => 'integer',
    ];

    // Status Constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    // Relationships
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AssessmentItem::class, 'section_id')->orderBy('item_order');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AssessmentPhoto::class, 'section_id')->orderBy('display_order');
    }

    // Scopes
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('section_order');
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('is_critical', true);
    }

    public function scopeRequiringPhotos(Builder $query): Builder
    {
        return $query->where('requires_photo', true);
    }

    // Business Logic Methods

    /**
     * Calculate section score based on items.
     */
    public function calculateSectionScore(): float
    {
        $totalPossible = 0;
        $totalActual = 0;

        foreach ($this->items as $item) {
            if ($item->actual_points !== null) {
                $totalPossible += $item->max_points;
                $totalActual += $item->actual_points;
            }
        }

        if ($totalPossible == 0) {
            return 0;
        }

        // Convert to percentage based on max_score
        $percentage = ($totalActual / $totalPossible) * 100;
        return round(min($percentage, $this->max_score), 2);
    }

    /**
     * Update section score and status.
     */
    public function updateScore(): bool
    {
        $this->actual_score = $this->calculateSectionScore();
        
        // Update status based on completion
        $totalItems = $this->items()->count();
        $completedItems = $this->items()->whereNotNull('actual_points')->count();
        
        if ($totalItems > 0) {
            if ($completedItems === 0) {
                $this->status = self::STATUS_PENDING;
            } elseif ($completedItems < $totalItems) {
                $this->status = self::STATUS_IN_PROGRESS;
            } else {
                $this->status = self::STATUS_COMPLETED;
            }
        }

        return $this->save();
    }

    /**
     * Check if section is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if section has critical issues.
     */
    public function hasCriticalIssues(): bool
    {
        return $this->items()
            ->where('is_critical', true)
            ->where('risk_factor', 'critical')
            ->exists();
    }

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentage(): int
    {
        $totalItems = $this->items()->count();
        if ($totalItems === 0) return 0;

        $completedItems = $this->items()->whereNotNull('actual_points')->count();
        return round(($completedItems / $totalItems) * 100);
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'gray',
            self::STATUS_IN_PROGRESS => 'yellow',
            self::STATUS_COMPLETED => 'green',
            default => 'gray',
        };
    }

    /**
     * Get risk level based on critical items.
     */
    public function getRiskLevel(): string
    {
        $criticalItems = $this->items()->where('is_critical', true)->count();
        $highRiskItems = $this->items()->where('risk_factor', 'high')->count();
        $criticalRiskItems = $this->items()->where('risk_factor', 'critical')->count();

        if ($criticalRiskItems > 0) return 'critical';
        if ($criticalItems > 0 || $highRiskItems > 0) return 'high';
        if ($this->actual_score && $this->actual_score < 50) return 'medium';
        
        return 'low';
    }

    /**
     * Get items requiring immediate attention.
     */
    public function getImmediateAttentionItems()
    {
        return $this->items()
            ->where('requires_immediate_attention', true)
            ->orWhere('risk_factor', 'critical')
            ->get();
    }

    /**
     * Get photo count for section.
     */
    public function getPhotoCount(): int
    {
        return $this->photos()->count() + 
               $this->items()->sum('photos_count');
    }

    /**
     * Check if section requires more photos.
     */
    public function needsMorePhotos(): bool
    {
        if (!$this->requires_photo) return false;

        $currentPhotos = $this->getPhotoCount();
        $requiredPhotos = $this->items()
            ->where('photo_required', true)
            ->sum('min_photos');

        return $currentPhotos < $requiredPhotos;
    }

    /**
     * Mark section as completed.
     */
    public function markAsCompleted(): bool
    {
        // Check if all required items are completed
        $incompleteItems = $this->items()
            ->whereNull('actual_points')
            ->count();

        if ($incompleteItems > 0) {
            return false;
        }

        // Check if photo requirements are met
        if ($this->needsMorePhotos()) {
            return false;
        }

        $this->status = self::STATUS_COMPLETED;
        $this->updateScore();

        return $this->save();
    }

    /**
     * Get weighted score contribution to assessment.
     */
    public function getWeightedScore(): float
    {
        if ($this->actual_score === null) return 0;
        
        return $this->actual_score * $this->weight;
    }

    /**
     * Get recommendations summary.
     */
    public function getRecommendationsSummary(): array
    {
        $recommendations = [];
        
        // Section-level recommendations
        if ($this->recommendations) {
            $recommendations[] = $this->recommendations;
        }

        // Item-level recommendations
        foreach ($this->items as $item) {
            if ($item->recommendations) {
                $recommendations[] = $item->recommendations;
            }
        }

        return $recommendations;
    }
}
