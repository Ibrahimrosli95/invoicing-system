<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Warranty extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'type',
        'coverage_period_months',
        'start_date',
        'end_date',
        'coverage_details',
        'exclusions',
        'claim_process',
        'contact_info',
        'status',
        'coverage_amount',
        'currency',
        'terms_conditions',
        'certificate_number',
        'provider_name',
        'provider_contact',
        'claim_count',
        'claimed_amount',
        'last_claimed_at',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'coverage_amount' => 'decimal:2',
        'claimed_amount' => 'decimal:2',
        'terms_conditions' => 'json',
        'metadata' => 'json',
        'last_claimed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    const TYPE_PRODUCT = 'product';
    const TYPE_SERVICE = 'service';
    const TYPE_EXTENDED = 'extended';
    const TYPE_MANUFACTURER = 'manufacturer';

    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CLAIMED = 'claimed';
    const STATUS_SUSPENDED = 'suspended';

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_EXPIRED)
                    ->orWhere('end_date', '<', now());
    }

    public function scopeExpiringThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('end_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ])->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeWithClaims(Builder $query): Builder
    {
        return $query->where('claim_count', '>', 0);
    }

    // Accessors & Mutators
    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date < now() || $this->status === self::STATUS_EXPIRED;
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return $this->end_date ? now()->diffInDays($this->end_date, false) : 0;
    }

    public function getRemainingCoverageAttribute(): float
    {
        $totalAmount = (float) $this->coverage_amount;
        $claimedAmount = (float) $this->claimed_amount;
        return max(0, $totalAmount - $claimedAmount);
    }

    public function getCoverageUtilizationPercentageAttribute(): float
    {
        if (!$this->coverage_amount || $this->coverage_amount == 0) {
            return 0;
        }
        
        return min(100, ($this->claimed_amount / $this->coverage_amount) * 100);
    }

    public function getFormattedCoverageAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->coverage_amount, 2);
    }

    public function getFormattedClaimedAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->claimed_amount, 2);
    }

    public function getFormattedRemainingCoverageAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->remaining_coverage, 2);
    }

    // Business Logic Methods
    public function markAsExpired(): bool
    {
        if ($this->status === self::STATUS_EXPIRED) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_EXPIRED
        ]);
    }

    public function addClaim(float $amount, array $claimDetails = []): bool
    {
        if ($this->is_expired || !$this->is_active) {
            return false;
        }

        $newClaimedAmount = $this->claimed_amount + $amount;
        
        // Check if claim exceeds coverage
        if ($this->coverage_amount && $newClaimedAmount > $this->coverage_amount) {
            return false;
        }

        $metadata = $this->metadata ?? [];
        $metadata['claims'][] = [
            'amount' => $amount,
            'date' => now()->toIso8601String(),
            'details' => $claimDetails
        ];

        return $this->update([
            'claim_count' => $this->claim_count + 1,
            'claimed_amount' => $newClaimedAmount,
            'last_claimed_at' => now(),
            'metadata' => $metadata,
            'status' => $newClaimedAmount >= $this->coverage_amount ? self::STATUS_CLAIMED : $this->status
        ]);
    }

    public function suspend(string $reason = null): bool
    {
        $metadata = $this->metadata ?? [];
        $metadata['suspension_history'][] = [
            'date' => now()->toIso8601String(),
            'reason' => $reason,
            'suspended_by' => auth()->user()?->id
        ];

        return $this->update([
            'status' => self::STATUS_SUSPENDED,
            'metadata' => $metadata
        ]);
    }

    public function reactivate(): bool
    {
        if ($this->is_expired) {
            return false;
        }

        $metadata = $this->metadata ?? [];
        $metadata['reactivation_history'][] = [
            'date' => now()->toIso8601String(),
            'reactivated_by' => auth()->user()?->id
        ];

        return $this->update([
            'status' => self::STATUS_ACTIVE,
            'metadata' => $metadata
        ]);
    }

    public function extendWarranty(int $additionalMonths, float $additionalCoverage = 0): bool
    {
        $newEndDate = Carbon::parse($this->end_date)->addMonths($additionalMonths);
        
        $updateData = [
            'end_date' => $newEndDate,
            'coverage_period_months' => $this->coverage_period_months + $additionalMonths,
        ];

        if ($additionalCoverage > 0) {
            $updateData['coverage_amount'] = $this->coverage_amount + $additionalCoverage;
        }

        $metadata = $this->metadata ?? [];
        $metadata['extensions'][] = [
            'date' => now()->toIso8601String(),
            'additional_months' => $additionalMonths,
            'additional_coverage' => $additionalCoverage,
            'extended_by' => auth()->user()?->id
        ];
        $updateData['metadata'] = $metadata;

        return $this->update($updateData);
    }

    public function getWarrantyStatus(): array
    {
        $status = [
            'is_active' => $this->is_active && $this->status === self::STATUS_ACTIVE,
            'is_expired' => $this->is_expired,
            'days_until_expiry' => $this->days_until_expiry,
            'coverage_utilization' => $this->coverage_utilization_percentage,
            'remaining_coverage' => $this->remaining_coverage,
            'claim_count' => $this->claim_count,
        ];

        if ($this->days_until_expiry <= 30 && $this->days_until_expiry > 0) {
            $status['warning'] = 'Warranty expires within 30 days';
        }

        if ($this->coverage_utilization_percentage > 80) {
            $status['warning'] = 'Coverage utilization above 80%';
        }

        return $status;
    }

    public function generateCertificate(): string
    {
        if ($this->certificate_number) {
            return $this->certificate_number;
        }

        $number = 'WTY-' . $this->company_id . '-' . now()->year . '-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
        
        $this->update(['certificate_number' => $number]);
        
        return $number;
    }

    // Static helper methods
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_PRODUCT => 'Product Warranty',
            self::TYPE_SERVICE => 'Service Warranty',
            self::TYPE_EXTENDED => 'Extended Warranty',
            self::TYPE_MANUFACTURER => 'Manufacturer Warranty',
        ];
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CLAIMED => 'Claimed',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($warranty) {
            if (!$warranty->company_id && auth()->user()) {
                $warranty->company_id = auth()->user()->company_id;
            }
        });

        static::updating(function ($warranty) {
            // Auto-expire if end_date has passed
            if ($warranty->end_date < now() && $warranty->status === self::STATUS_ACTIVE) {
                $warranty->status = self::STATUS_EXPIRED;
            }
        });
    }
}
