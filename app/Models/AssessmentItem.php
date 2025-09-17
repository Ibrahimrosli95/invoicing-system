<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class AssessmentItem extends Model
{
    protected $fillable = [
        'section_id',
        'item_name',
        'item_description',
        'item_order',
        'item_type',
        'item_options',
        'max_points',
        'actual_points',
        'response_value',
        'response_notes',
        'recommendations',
        'risk_factor',
        'is_critical',
        'requires_immediate_attention',
        'photo_required',
        'photos_count',
        'min_photos',
        'max_photos',
        'measurement_unit',
        'measurement_value',
        'acceptable_min',
        'acceptable_max',
    ];

    protected $casts = [
        'max_points' => 'decimal:2',
        'actual_points' => 'decimal:2',
        'item_options' => 'array',
        'is_critical' => 'boolean',
        'requires_immediate_attention' => 'boolean',
        'photo_required' => 'boolean',
        'photos_count' => 'integer',
        'min_photos' => 'integer',
        'max_photos' => 'integer',
        'item_order' => 'integer',
        'measurement_value' => 'decimal:3',
        'acceptable_min' => 'decimal:3',
        'acceptable_max' => 'decimal:3',
    ];

    // Item Type Constants
    const TYPE_RATING = 'rating';
    const TYPE_YES_NO = 'yes_no';
    const TYPE_TEXT = 'text';
    const TYPE_MEASUREMENT = 'measurement';
    const TYPE_PHOTO = 'photo';
    const TYPE_MULTIPLE_CHOICE = 'multiple_choice';

    // Risk Factor Constants
    const RISK_NONE = 'none';
    const RISK_LOW = 'low';
    const RISK_MEDIUM = 'medium';
    const RISK_HIGH = 'high';
    const RISK_CRITICAL = 'critical';

    /**
     * Get all item types.
     */
    public static function getItemTypes(): array
    {
        return [
            self::TYPE_RATING => 'Rating Scale',
            self::TYPE_YES_NO => 'Yes/No Question',
            self::TYPE_TEXT => 'Text Response',
            self::TYPE_MEASUREMENT => 'Measurement',
            self::TYPE_PHOTO => 'Photo Documentation',
            self::TYPE_MULTIPLE_CHOICE => 'Multiple Choice',
        ];
    }

    /**
     * Get all risk factors.
     */
    public static function getRiskFactors(): array
    {
        return [
            self::RISK_NONE => 'No Risk',
            self::RISK_LOW => 'Low Risk',
            self::RISK_MEDIUM => 'Medium Risk',
            self::RISK_HIGH => 'High Risk',
            self::RISK_CRITICAL => 'Critical Risk',
        ];
    }

    // Relationships
    public function section(): BelongsTo
    {
        return $this->belongsTo(AssessmentSection::class, 'section_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(AssessmentPhoto::class, 'item_id')->orderBy('display_order');
    }

    // Scopes
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('item_order');
    }

    public function scopeCritical(Builder $query): Builder
    {
        return $query->where('is_critical', true);
    }

    public function scopeRequiringPhotos(Builder $query): Builder
    {
        return $query->where('photo_required', true);
    }

    public function scopeByRiskFactor(Builder $query, string $riskFactor): Builder
    {
        return $query->where('risk_factor', $riskFactor);
    }

    public function scopeRequiringAttention(Builder $query): Builder
    {
        return $query->where('requires_immediate_attention', true);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('actual_points');
    }

    // Business Logic Methods

    /**
     * Calculate points based on response and item type.
     */
    public function calculatePoints(): float
    {
        if ($this->response_value === null) {
            return 0;
        }

        switch ($this->item_type) {
            case self::TYPE_RATING:
                return $this->calculateRatingPoints();
            
            case self::TYPE_YES_NO:
                return $this->calculateYesNoPoints();
            
            case self::TYPE_MEASUREMENT:
                return $this->calculateMeasurementPoints();
            
            case self::TYPE_MULTIPLE_CHOICE:
                return $this->calculateMultipleChoicePoints();
            
            case self::TYPE_TEXT:
            case self::TYPE_PHOTO:
                // For text and photo types, points need to be manually assigned
                return $this->actual_points ?? 0;
            
            default:
                return 0;
        }
    }

    /**
     * Calculate points for rating type items.
     */
    protected function calculateRatingPoints(): float
    {
        $options = $this->item_options ?? [];
        $scale = $options['scale'] ?? 10;
        $reverse = $options['reverse_scoring'] ?? false;
        
        $rating = (float) $this->response_value;
        
        if ($reverse) {
            $rating = $scale - $rating + 1;
        }
        
        return round(($rating / $scale) * $this->max_points, 2);
    }

    /**
     * Calculate points for yes/no type items.
     */
    protected function calculateYesNoPoints(): float
    {
        $options = $this->item_options ?? [];
        $positiveResponse = $options['positive_response'] ?? 'yes';
        
        $response = strtolower($this->response_value);
        $isPositive = ($response === strtolower($positiveResponse));
        
        return $isPositive ? $this->max_points : 0;
    }

    /**
     * Calculate points for measurement type items.
     */
    protected function calculateMeasurementPoints(): float
    {
        $value = (float) $this->measurement_value;
        $min = $this->acceptable_min;
        $max = $this->acceptable_max;
        
        if ($min !== null && $max !== null) {
            // Value should be within range
            if ($value >= $min && $value <= $max) {
                return $this->max_points;
            } else {
                // Calculate partial points based on how far outside the range
                $range = $max - $min;
                $deviation = min(abs($value - $min), abs($value - $max));
                $deductionPercentage = min($deviation / $range, 1);
                
                return round($this->max_points * (1 - $deductionPercentage), 2);
            }
        } elseif ($min !== null) {
            // Value should be at least min
            return $value >= $min ? $this->max_points : 0;
        } elseif ($max !== null) {
            // Value should be at most max
            return $value <= $max ? $this->max_points : 0;
        }
        
        return $this->max_points; // No constraints
    }

    /**
     * Calculate points for multiple choice items.
     */
    protected function calculateMultipleChoicePoints(): float
    {
        $options = $this->item_options ?? [];
        $choices = $options['choices'] ?? [];
        
        foreach ($choices as $choice) {
            if ($choice['value'] === $this->response_value) {
                return $choice['points'] ?? 0;
            }
        }
        
        return 0;
    }

    /**
     * Update actual points based on current response.
     */
    public function updatePoints(): bool
    {
        $this->actual_points = $this->calculatePoints();
        return $this->save();
    }

    /**
     * Check if item is completed.
     */
    public function isCompleted(): bool
    {
        return $this->actual_points !== null || 
               ($this->item_type === self::TYPE_TEXT && !empty($this->response_value)) ||
               ($this->item_type === self::TYPE_PHOTO && $this->photos_count > 0);
    }

    /**
     * Get risk color for UI.
     */
    public function getRiskColor(): string
    {
        return match($this->risk_factor) {
            self::RISK_NONE => 'gray',
            self::RISK_LOW => 'green',
            self::RISK_MEDIUM => 'yellow',
            self::RISK_HIGH => 'orange',
            self::RISK_CRITICAL => 'red',
            default => 'gray',
        };
    }

    /**
     * Check if measurement is within acceptable range.
     */
    public function isMeasurementAcceptable(): ?bool
    {
        if ($this->item_type !== self::TYPE_MEASUREMENT || $this->measurement_value === null) {
            return null;
        }

        $value = $this->measurement_value;
        $min = $this->acceptable_min;
        $max = $this->acceptable_max;

        if ($min !== null && $value < $min) return false;
        if ($max !== null && $value > $max) return false;
        
        return true;
    }

    /**
     * Get formatted measurement with unit.
     */
    public function getFormattedMeasurement(): ?string
    {
        if ($this->measurement_value === null) return null;
        
        $unit = $this->measurement_unit ?? '';
        return $this->measurement_value . ($unit ? ' ' . $unit : '');
    }

    /**
     * Get acceptable range display.
     */
    public function getAcceptableRangeDisplay(): ?string
    {
        $min = $this->acceptable_min;
        $max = $this->acceptable_max;
        $unit = $this->measurement_unit ?? '';
        
        if ($min !== null && $max !== null) {
            return "{$min} - {$max}" . ($unit ? " {$unit}" : '');
        } elseif ($min !== null) {
            return "≥ {$min}" . ($unit ? " {$unit}" : '');
        } elseif ($max !== null) {
            return "≤ {$max}" . ($unit ? " {$unit}" : '');
        }
        
        return null;
    }

    /**
     * Check if item needs immediate attention.
     */
    public function needsImmediateAttention(): bool
    {
        return $this->requires_immediate_attention || 
               $this->risk_factor === self::RISK_CRITICAL ||
               ($this->item_type === self::TYPE_MEASUREMENT && $this->isMeasurementAcceptable() === false);
    }

    /**
     * Get photo requirements status.
     */
    public function getPhotoRequirementsStatus(): array
    {
        if (!$this->photo_required) {
            return ['required' => false, 'met' => true, 'current' => 0, 'needed' => 0];
        }

        $current = $this->photos_count;
        $needed = max(0, $this->min_photos - $current);
        
        return [
            'required' => true,
            'met' => $current >= $this->min_photos,
            'current' => $current,
            'needed' => $needed,
            'min' => $this->min_photos,
            'max' => $this->max_photos,
        ];
    }

    /**
     * Get completion status.
     */
    public function getCompletionStatus(): array
    {
        $photoStatus = $this->getPhotoRequirementsStatus();
        $hasResponse = !empty($this->response_value) || $this->actual_points !== null;
        
        return [
            'has_response' => $hasResponse,
            'photos_met' => $photoStatus['met'],
            'is_complete' => $hasResponse && $photoStatus['met'],
            'needs_attention' => $this->needsImmediateAttention(),
        ];
    }

    /**
     * Set response and calculate points.
     */
    public function setResponse($value, $notes = null): bool
    {
        $this->response_value = $value;
        $this->response_notes = $notes;
        
        // Auto-calculate points for supported types
        if (in_array($this->item_type, [self::TYPE_RATING, self::TYPE_YES_NO, self::TYPE_MEASUREMENT, self::TYPE_MULTIPLE_CHOICE])) {
            $this->actual_points = $this->calculatePoints();
        }
        
        return $this->save();
    }
}
