<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Insurance extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'policy_name',
        'description',
        'policy_type',
        'policy_number',
        'insurer_name',
        'insurer_contact',
        'effective_date',
        'expiry_date',
        'coverage_amount',
        'premium_amount',
        'payment_frequency',
        'currency',
        'coverage_details',
        'exclusions',
        'deductible_info',
        'broker_name',
        'broker_contact',
        'status',
        'beneficiaries',
        'claims_made',
        'claim_count',
        'last_claim_date',
        'renewal_notice_date',
        'auto_renewal',
        'renewal_notes',
        'policy_documents',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'coverage_amount' => 'decimal:2',
        'premium_amount' => 'decimal:2',
        'claims_made' => 'decimal:2',
        'last_claim_date' => 'datetime',
        'renewal_notice_date' => 'date',
        'auto_renewal' => 'boolean',
        'beneficiaries' => 'json',
        'policy_documents' => 'json',
        'metadata' => 'json',
        'is_active' => 'boolean',
    ];

    const TYPE_LIABILITY = 'liability';
    const TYPE_PROPERTY = 'property';
    const TYPE_PROFESSIONAL = 'professional';
    const TYPE_WORKERS_COMP = 'workers_comp';
    const TYPE_CYBER = 'cyber';

    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PENDING_RENEWAL = 'pending_renewal';

    const FREQUENCY_ANNUAL = 'annual';
    const FREQUENCY_SEMI_ANNUAL = 'semi_annual';
    const FREQUENCY_QUARTERLY = 'quarterly';
    const FREQUENCY_MONTHLY = 'monthly';

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
                    ->orWhere('expiry_date', '<', now());
    }

    public function scopeExpiringThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('expiry_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ])->where('status', self::STATUS_ACTIVE);
    }

    public function scopeExpiringWithinDays(Builder $query, int $days): Builder
    {
        return $query->whereBetween('expiry_date', [
            now(),
            now()->addDays($days)
        ])->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('policy_type', $type);
    }

    public function scopeWithClaims(Builder $query): Builder
    {
        return $query->where('claim_count', '>', 0);
    }

    public function scopeAutoRenewal(Builder $query): Builder
    {
        return $query->where('auto_renewal', true);
    }

    public function scopeDueForRenewal(Builder $query): Builder
    {
        return $query->where('renewal_notice_date', '<=', now())
                    ->where('status', self::STATUS_ACTIVE);
    }

    // Accessors & Mutators
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date < now() || $this->status === self::STATUS_EXPIRED;
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return $this->expiry_date ? now()->diffInDays($this->expiry_date, false) : 0;
    }

    public function getRemainingCoverageAttribute(): float
    {
        $totalAmount = (float) $this->coverage_amount;
        $claimsAmount = (float) $this->claims_made;
        return max(0, $totalAmount - $claimsAmount);
    }

    public function getClaimsUtilizationPercentageAttribute(): float
    {
        if (!$this->coverage_amount || $this->coverage_amount == 0) {
            return 0;
        }
        
        return min(100, ($this->claims_made / $this->coverage_amount) * 100);
    }

    public function getAnnualPremiumAttribute(): float
    {
        switch ($this->payment_frequency) {
            case self::FREQUENCY_MONTHLY:
                return $this->premium_amount * 12;
            case self::FREQUENCY_QUARTERLY:
                return $this->premium_amount * 4;
            case self::FREQUENCY_SEMI_ANNUAL:
                return $this->premium_amount * 2;
            case self::FREQUENCY_ANNUAL:
            default:
                return $this->premium_amount;
        }
    }

    public function getFormattedCoverageAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->coverage_amount, 2);
    }

    public function getFormattedPremiumAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->premium_amount, 2);
    }

    public function getFormattedClaimsMadeAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->claims_made, 2);
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

    public function cancel(string $reason = null): bool
    {
        $metadata = $this->metadata ?? [];
        $metadata['cancellation_history'][] = [
            'date' => now()->toIso8601String(),
            'reason' => $reason,
            'cancelled_by' => auth()->user()?->id
        ];

        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'metadata' => $metadata
        ]);
    }

    public function addClaim(float $amount, array $claimDetails = []): bool
    {
        if ($this->is_expired || !$this->is_active) {
            return false;
        }

        $newClaimsAmount = $this->claims_made + $amount;
        
        // Check if claim exceeds coverage
        if ($this->coverage_amount && $newClaimsAmount > $this->coverage_amount) {
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
            'claims_made' => $newClaimsAmount,
            'last_claim_date' => now(),
            'metadata' => $metadata
        ]);
    }

    public function renewPolicy(array $renewalData): bool
    {
        $defaultData = [
            'effective_date' => $this->expiry_date,
            'expiry_date' => Carbon::parse($this->expiry_date)->addYear(),
            'status' => self::STATUS_ACTIVE,
            'renewal_notice_date' => Carbon::parse($this->expiry_date)->addYear()->subDays(30),
        ];

        $updateData = array_merge($defaultData, $renewalData);

        $metadata = $this->metadata ?? [];
        $metadata['renewal_history'][] = [
            'date' => now()->toIso8601String(),
            'previous_expiry' => $this->expiry_date->toIso8601String(),
            'new_expiry' => $updateData['expiry_date']->toIso8601String(),
            'renewed_by' => auth()->user()?->id,
            'changes' => $renewalData
        ];
        $updateData['metadata'] = $metadata;

        return $this->update($updateData);
    }

    public function sendRenewalReminder(): bool
    {
        // This would integrate with the notification system
        $metadata = $this->metadata ?? [];
        $metadata['renewal_reminders'][] = [
            'sent_at' => now()->toIso8601String(),
            'days_until_expiry' => $this->days_until_expiry
        ];

        return $this->update([
            'metadata' => $metadata
        ]);
    }

    public function updateBeneficiaries(array $beneficiaries): bool
    {
        return $this->update([
            'beneficiaries' => $beneficiaries
        ]);
    }

    public function attachDocument(string $documentPath, string $documentType): bool
    {
        $documents = $this->policy_documents ?? [];
        $documents[] = [
            'path' => $documentPath,
            'type' => $documentType,
            'uploaded_at' => now()->toIso8601String(),
            'uploaded_by' => auth()->user()?->id
        ];

        return $this->update([
            'policy_documents' => $documents
        ]);
    }

    public function getPolicyStatus(): array
    {
        $status = [
            'is_active' => $this->is_active && $this->status === self::STATUS_ACTIVE,
            'is_expired' => $this->is_expired,
            'days_until_expiry' => $this->days_until_expiry,
            'claims_utilization' => $this->claims_utilization_percentage,
            'remaining_coverage' => $this->remaining_coverage,
            'claim_count' => $this->claim_count,
            'annual_premium' => $this->annual_premium,
            'needs_renewal' => $this->days_until_expiry <= 60 && $this->days_until_expiry > 0,
        ];

        if ($this->days_until_expiry <= 30 && $this->days_until_expiry > 0) {
            $status['warning'] = 'Policy expires within 30 days';
        }

        if ($this->claims_utilization_percentage > 80) {
            $status['warning'] = 'Claims utilization above 80%';
        }

        if ($this->renewal_notice_date && $this->renewal_notice_date <= now()) {
            $status['action_required'] = 'Renewal notice period has begun';
        }

        return $status;
    }

    public function calculateRisk(): string
    {
        $riskFactors = 0;

        // High claims utilization
        if ($this->claims_utilization_percentage > 70) {
            $riskFactors += 3;
        } elseif ($this->claims_utilization_percentage > 40) {
            $riskFactors += 2;
        } elseif ($this->claims_utilization_percentage > 20) {
            $riskFactors += 1;
        }

        // Multiple claims
        if ($this->claim_count > 5) {
            $riskFactors += 2;
        } elseif ($this->claim_count > 2) {
            $riskFactors += 1;
        }

        // Recent claims
        if ($this->last_claim_date && $this->last_claim_date->diffInMonths(now()) < 6) {
            $riskFactors += 1;
        }

        if ($riskFactors >= 5) {
            return 'high';
        } elseif ($riskFactors >= 3) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    // Static helper methods
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_LIABILITY => 'Liability Insurance',
            self::TYPE_PROPERTY => 'Property Insurance',
            self::TYPE_PROFESSIONAL => 'Professional Indemnity',
            self::TYPE_WORKERS_COMP => 'Workers Compensation',
            self::TYPE_CYBER => 'Cyber Security Insurance',
        ];
    }

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_PENDING_RENEWAL => 'Pending Renewal',
        ];
    }

    public static function getPaymentFrequencies(): array
    {
        return [
            self::FREQUENCY_ANNUAL => 'Annual',
            self::FREQUENCY_SEMI_ANNUAL => 'Semi-Annual',
            self::FREQUENCY_QUARTERLY => 'Quarterly',
            self::FREQUENCY_MONTHLY => 'Monthly',
        ];
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($insurance) {
            if (!$insurance->company_id && auth()->user()) {
                $insurance->company_id = auth()->user()->company_id;
            }

            // Set renewal notice date if not provided
            if (!$insurance->renewal_notice_date && $insurance->expiry_date) {
                $insurance->renewal_notice_date = Carbon::parse($insurance->expiry_date)->subDays(30);
            }
        });

        static::updating(function ($insurance) {
            // Auto-expire if expiry_date has passed
            if ($insurance->expiry_date < now() && $insurance->status === self::STATUS_ACTIVE) {
                $insurance->status = self::STATUS_EXPIRED;
            }
        });
    }
}
