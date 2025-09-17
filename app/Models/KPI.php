<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class KPI extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kpis';

    protected $fillable = [
        'company_id',
        'owner_id',
        'name',
        'description',
        'category',
        'metric_type',
        'current_value',
        'target_value',
        'baseline_value',
        'unit',
        'measurement_frequency',
        'calculation_method',
        'data_sources',
        'status',
        'trend',
        'previous_value',
        'last_measured_at',
        'next_measurement_at',
        'alert_threshold_type',
        'alert_threshold_value',
        'alerts_enabled',
        'alert_recipients',
        'notes',
        'historical_data',
        'visualization_type',
        'chart_config',
        'display_order',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'current_value' => 'decimal:4',
        'target_value' => 'decimal:4',
        'baseline_value' => 'decimal:4',
        'previous_value' => 'decimal:4',
        'alert_threshold_value' => 'decimal:4',
        'data_sources' => 'json',
        'alert_recipients' => 'json',
        'historical_data' => 'json',
        'chart_config' => 'json',
        'metadata' => 'json',
        'last_measured_at' => 'datetime',
        'next_measurement_at' => 'datetime',
        'alerts_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    const CATEGORY_PERFORMANCE = 'performance';
    const CATEGORY_FINANCIAL = 'financial';
    const CATEGORY_OPERATIONAL = 'operational';
    const CATEGORY_CUSTOMER = 'customer';
    const CATEGORY_QUALITY = 'quality';

    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_NUMBER = 'number';
    const TYPE_CURRENCY = 'currency';
    const TYPE_RATIO = 'ratio';
    const TYPE_TIME = 'time';

    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_QUARTERLY = 'quarterly';
    const FREQUENCY_YEARLY = 'yearly';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ARCHIVED = 'archived';

    const TREND_IMPROVING = 'improving';
    const TREND_DECLINING = 'declining';
    const TREND_STABLE = 'stable';

    const THRESHOLD_ABOVE = 'above';
    const THRESHOLD_BELOW = 'below';
    const THRESHOLD_EQUAL = 'equal';

    const VIZ_LINE = 'line';
    const VIZ_BAR = 'bar';
    const VIZ_GAUGE = 'gauge';
    const VIZ_PIE = 'pie';

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Scopes
    public function scopeForCompany(Builder $query, $companyId = null): Builder
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        return $query->where('company_id', $companyId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeByOwner(Builder $query, $userId): Builder
    {
        return $query->where('owner_id', $userId);
    }

    public function scopeDueForMeasurement(Builder $query): Builder
    {
        return $query->where('next_measurement_at', '<=', now())
                    ->where('status', self::STATUS_ACTIVE);
    }

    public function scopeWithAlerts(Builder $query): Builder
    {
        return $query->where('alerts_enabled', true);
    }

    public function scopeOrderedByDisplayOrder(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    // Accessors & Mutators
    public function getFormattedCurrentValueAttribute(): string
    {
        return $this->formatValue($this->current_value);
    }

    public function getFormattedTargetValueAttribute(): string
    {
        return $this->target_value ? $this->formatValue($this->target_value) : 'N/A';
    }

    public function getFormattedPreviousValueAttribute(): string
    {
        return $this->previous_value ? $this->formatValue($this->previous_value) : 'N/A';
    }

    public function getPerformancePercentageAttribute(): float
    {
        if (!$this->target_value || $this->target_value == 0) {
            return 0;
        }

        return min(100, max(0, ($this->current_value / $this->target_value) * 100));
    }

    public function getTrendPercentageAttribute(): float
    {
        if (!$this->previous_value || $this->previous_value == 0) {
            return 0;
        }

        return (($this->current_value - $this->previous_value) / $this->previous_value) * 100;
    }

    public function getIsTargetMetAttribute(): bool
    {
        if (!$this->target_value) {
            return false;
        }

        // For percentage KPIs, consider target met if within 5%
        if ($this->metric_type === self::TYPE_PERCENTAGE) {
            return abs($this->current_value - $this->target_value) <= 5;
        }

        return $this->current_value >= $this->target_value;
    }

    public function getAlertStatusAttribute(): ?string
    {
        if (!$this->alerts_enabled || !$this->alert_threshold_value) {
            return null;
        }

        switch ($this->alert_threshold_type) {
            case self::THRESHOLD_ABOVE:
                return $this->current_value > $this->alert_threshold_value ? 'triggered' : 'ok';
            case self::THRESHOLD_BELOW:
                return $this->current_value < $this->alert_threshold_value ? 'triggered' : 'ok';
            case self::THRESHOLD_EQUAL:
                return $this->current_value == $this->alert_threshold_value ? 'triggered' : 'ok';
            default:
                return 'ok';
        }
    }

    // Business Logic Methods
    public function updateValue(float $newValue, array $context = []): bool
    {
        $oldValue = $this->current_value;
        
        // Update historical data
        $historicalData = $this->historical_data ?? [];
        $historicalData[] = [
            'value' => $oldValue,
            'date' => $this->last_measured_at?->toIso8601String() ?? now()->toIso8601String(),
            'context' => $context
        ];

        // Calculate trend
        $trend = $this->calculateTrend($oldValue, $newValue);

        // Set next measurement date
        $nextMeasurement = $this->calculateNextMeasurementDate();

        $updated = $this->update([
            'previous_value' => $oldValue,
            'current_value' => $newValue,
            'trend' => $trend,
            'last_measured_at' => now(),
            'next_measurement_at' => $nextMeasurement,
            'historical_data' => $historicalData,
        ]);

        // Check for alerts
        if ($updated && $this->alert_status === 'triggered') {
            $this->sendAlert();
        }

        return $updated;
    }

    public function calculateTrend(float $oldValue, float $newValue): string
    {
        if ($oldValue == 0 || $oldValue == $newValue) {
            return self::TREND_STABLE;
        }

        $changePercentage = (($newValue - $oldValue) / $oldValue) * 100;

        if (abs($changePercentage) < 5) {
            return self::TREND_STABLE;
        }

        return $changePercentage > 0 ? self::TREND_IMPROVING : self::TREND_DECLINING;
    }

    public function calculateNextMeasurementDate(): Carbon
    {
        $baseDate = $this->last_measured_at ?? now();

        switch ($this->measurement_frequency) {
            case self::FREQUENCY_DAILY:
                return $baseDate->copy()->addDay();
            case self::FREQUENCY_WEEKLY:
                return $baseDate->copy()->addWeek();
            case self::FREQUENCY_MONTHLY:
                return $baseDate->copy()->addMonth();
            case self::FREQUENCY_QUARTERLY:
                return $baseDate->copy()->addQuarter();
            case self::FREQUENCY_YEARLY:
                return $baseDate->copy()->addYear();
            default:
                return $baseDate->copy()->addMonth();
        }
    }

    public function sendAlert(): bool
    {
        if (!$this->alerts_enabled || !$this->alert_recipients) {
            return false;
        }

        // This would integrate with the notification system
        $metadata = $this->metadata ?? [];
        $metadata['alert_history'][] = [
            'triggered_at' => now()->toIso8601String(),
            'value' => $this->current_value,
            'threshold' => $this->alert_threshold_value,
            'type' => $this->alert_threshold_type,
            'recipients' => $this->alert_recipients
        ];

        return $this->update([
            'metadata' => $metadata
        ]);
    }

    public function formatValue(float $value): string
    {
        switch ($this->metric_type) {
            case self::TYPE_PERCENTAGE:
                return number_format($value, 2) . '%';
            case self::TYPE_CURRENCY:
                return ($this->unit ?? 'MYR') . ' ' . number_format($value, 2);
            case self::TYPE_TIME:
                return $this->formatTimeValue($value);
            case self::TYPE_RATIO:
                return number_format($value, 3) . ':1';
            case self::TYPE_NUMBER:
            default:
                return number_format($value, 2) . ($this->unit ? ' ' . $this->unit : '');
        }
    }

    private function formatTimeValue(float $hours): string
    {
        if ($hours < 1) {
            return round($hours * 60) . ' minutes';
        } elseif ($hours < 24) {
            return number_format($hours, 1) . ' hours';
        } else {
            return number_format($hours / 24, 1) . ' days';
        }
    }

    public function getPerformanceStatus(): array
    {
        return [
            'current_value' => $this->formatted_current_value,
            'target_value' => $this->formatted_target_value,
            'performance_percentage' => $this->performance_percentage,
            'trend' => $this->trend,
            'trend_percentage' => $this->trend_percentage,
            'is_target_met' => $this->is_target_met,
            'alert_status' => $this->alert_status,
            'last_measured' => $this->last_measured_at?->diffForHumans(),
            'next_measurement' => $this->next_measurement_at?->diffForHumans(),
        ];
    }

    public function getChartData(int $dataPoints = 12): array
    {
        $historicalData = $this->historical_data ?? [];
        
        // Get the most recent data points
        $recentData = array_slice($historicalData, -$dataPoints);
        
        // Add current value
        $recentData[] = [
            'value' => $this->current_value,
            'date' => $this->last_measured_at?->toIso8601String() ?? now()->toIso8601String(),
        ];

        $labels = [];
        $values = [];

        foreach ($recentData as $dataPoint) {
            $labels[] = Carbon::parse($dataPoint['date'])->format('M Y');
            $values[] = $dataPoint['value'];
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'target_line' => array_fill(0, count($values), $this->target_value),
        ];
    }

    public function duplicate(string $newName = null): self
    {
        $newKpi = $this->replicate();
        $newKpi->name = $newName ?? $this->name . ' (Copy)';
        $newKpi->current_value = 0;
        $newKpi->previous_value = null;
        $newKpi->historical_data = null;
        $newKpi->last_measured_at = null;
        $newKpi->next_measurement_at = $this->calculateNextMeasurementDate();
        $newKpi->save();

        return $newKpi;
    }

    // Static helper methods
    public static function getAvailableCategories(): array
    {
        return [
            self::CATEGORY_PERFORMANCE => 'Performance',
            self::CATEGORY_FINANCIAL => 'Financial',
            self::CATEGORY_OPERATIONAL => 'Operational',
            self::CATEGORY_CUSTOMER => 'Customer',
            self::CATEGORY_QUALITY => 'Quality',
        ];
    }

    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_PERCENTAGE => 'Percentage',
            self::TYPE_NUMBER => 'Number',
            self::TYPE_CURRENCY => 'Currency',
            self::TYPE_RATIO => 'Ratio',
            self::TYPE_TIME => 'Time',
        ];
    }

    public static function getAvailableFrequencies(): array
    {
        return [
            self::FREQUENCY_DAILY => 'Daily',
            self::FREQUENCY_WEEKLY => 'Weekly',
            self::FREQUENCY_MONTHLY => 'Monthly',
            self::FREQUENCY_QUARTERLY => 'Quarterly',
            self::FREQUENCY_YEARLY => 'Yearly',
        ];
    }

    public static function getVisualizationTypes(): array
    {
        return [
            self::VIZ_LINE => 'Line Chart',
            self::VIZ_BAR => 'Bar Chart',
            self::VIZ_GAUGE => 'Gauge Chart',
            self::VIZ_PIE => 'Pie Chart',
        ];
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kpi) {
            if (!$kpi->company_id && auth()->user()) {
                $kpi->company_id = auth()->user()->company_id;
            }

            if (!$kpi->next_measurement_at) {
                $kpi->next_measurement_at = $kpi->calculateNextMeasurementDate();
            }

            if ($kpi->display_order === null) {
                $maxOrder = static::forCompany($kpi->company_id)->max('display_order') ?? 0;
                $kpi->display_order = $maxOrder + 1;
            }
        });
    }
}
