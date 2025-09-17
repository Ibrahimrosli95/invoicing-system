<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Certification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'title',
        'certification_body',
        'certificate_number',
        'description',
        'certification_type',
        'scope',
        'issued_date',
        'expiry_date',
        'does_expire',
        'validity_years',
        'auto_renewal',
        'status',
        'verification_status',
        'verification_notes',
        'last_verified_at',
        'verified_by',
        'certificate_file',
        'accreditation_logo',
        'supporting_documents',
        'show_on_documents',
        'show_on_website',
        'show_expiry_date',
        'is_featured',
        'display_order',
        'issuing_authority',
        'assessor_name',
        'assessor_number',
        'certificate_details',
        'next_assessment_date',
        'next_surveillance_date',
        'renewal_reminder_days',
        'reminder_sent',
        'reminder_sent_at',
        'certification_cost',
        'business_benefits',
        'compliance_requirements',
        'view_count',
        'document_usage_count',
        'usage_stats',
        'applicable_services',
        'applicable_projects',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'supporting_documents' => 'array',
        'certificate_details' => 'array',
        'compliance_requirements' => 'array',
        'usage_stats' => 'array',
        'applicable_services' => 'array',
        'applicable_projects' => 'array',
        'issued_date' => 'date',
        'expiry_date' => 'date',
        'next_assessment_date' => 'date',
        'next_surveillance_date' => 'date',
        'last_verified_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'does_expire' => 'boolean',
        'auto_renewal' => 'boolean',
        'show_on_documents' => 'boolean',
        'show_on_website' => 'boolean',
        'show_expiry_date' => 'boolean',
        'is_featured' => 'boolean',
        'reminder_sent' => 'boolean',
        'certification_cost' => 'decimal:2',
        'validity_years' => 'integer',
        'renewal_reminder_days' => 'integer',
        'view_count' => 'integer',
        'document_usage_count' => 'integer',
        'display_order' => 'integer',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_REVOKED = 'revoked';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_PENDING_RENEWAL = 'pending_renewal';

    const VERIFICATION_STATUS_VERIFIED = 'verified';
    const VERIFICATION_STATUS_PENDING = 'pending_verification';
    const VERIFICATION_STATUS_UNVERIFIED = 'unverified';

    protected static function booted()
    {
        static::creating(function ($certification) {
            if (!$certification->uuid) {
                $certification->uuid = Str::uuid();
            }
            
            // Set company_id from authenticated user if not set
            if (!$certification->company_id && auth()->check()) {
                $certification->company_id = auth()->user()->company_id;
            }
            
            // Set created_by from authenticated user if not set
            if (!$certification->created_by && auth()->check()) {
                $certification->created_by = auth()->id();
            }
        });

        static::updating(function ($certification) {
            // Set updated_by from authenticated user
            if (auth()->check()) {
                $certification->updated_by = auth()->id();
            }

            // Auto-update status based on expiry date
            if ($certification->does_expire && $certification->expiry_date && $certification->expiry_date->isPast()) {
                if ($certification->status === self::STATUS_ACTIVE) {
                    $certification->status = self::STATUS_EXPIRED;
                }
            }
        });

        static::retrieved(function ($certification) {
            // Auto-update status when retrieved if expired
            if ($certification->doesExpire() && $certification->isExpired() && $certification->status === self::STATUS_ACTIVE) {
                $certification->update(['status' => self::STATUS_EXPIRED]);
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

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
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

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function scopeExpiringWithin($query, $days = 90)
    {
        return $query->where('does_expire', true)
                    ->whereNotNull('expiry_date')
                    ->whereBetween('expiry_date', [now(), now()->addDays($days)])
                    ->where('status', self::STATUS_ACTIVE);
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', self::VERIFICATION_STATUS_VERIFIED);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForDocuments($query)
    {
        return $query->where('show_on_documents', true);
    }

    public function scopeForWebsite($query)
    {
        return $query->where('show_on_website', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('certification_type', $type);
    }

    // Expiry and Status Methods
    public function doesExpire(): bool
    {
        return $this->does_expire;
    }

    public function isExpired(): bool
    {
        if (!$this->doesExpire() || !$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    public function isExpiringSoon($days = 90): bool
    {
        if (!$this->doesExpire() || !$this->expiry_date || $this->isExpired()) {
            return false;
        }

        return $this->expiry_date->isBefore(now()->addDays($days));
    }

    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->doesExpire() || !$this->expiry_date) {
            return null;
        }

        if ($this->isExpired()) {
            return 0;
        }

        return now()->diffInDays($this->expiry_date);
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isExpired()) {
            return 0;
        }

        return $this->expiry_date->diffInDays(now());
    }

    public function getExpiryStatus(): string
    {
        if (!$this->doesExpire()) {
            return 'No expiry';
        }

        if ($this->isExpired()) {
            return 'Expired ' . $this->getDaysOverdue() . ' days ago';
        }

        if ($this->isExpiringSoon(30)) {
            return 'Expires in ' . $this->getDaysUntilExpiry() . ' days';
        }

        return 'Valid until ' . $this->expiry_date->format('M j, Y');
    }

    // Business Logic Methods
    public function markAsExpired(): bool
    {
        return $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);
    }

    public function renew(Carbon $newExpiryDate, $userId = null): bool
    {
        $userId = $userId ?: auth()->id();
        
        return $this->update([
            'expiry_date' => $newExpiryDate,
            'status' => self::STATUS_ACTIVE,
            'reminder_sent' => false,
            'reminder_sent_at' => null,
            'updated_by' => $userId,
        ]);
    }

    public function verify($notes = null, $userId = null): bool
    {
        $userId = $userId ?: auth()->id();
        
        return $this->update([
            'verification_status' => self::VERIFICATION_STATUS_VERIFIED,
            'verification_notes' => $notes,
            'last_verified_at' => now(),
            'verified_by' => $userId,
        ]);
    }

    public function revoke($reason = null): bool
    {
        return $this->update([
            'status' => self::STATUS_REVOKED,
            'verification_notes' => $reason,
        ]);
    }

    public function suspend($reason = null): bool
    {
        return $this->update([
            'status' => self::STATUS_SUSPENDED,
            'verification_notes' => $reason,
        ]);
    }

    public function reactivate(): bool
    {
        if ($this->isExpired()) {
            return false; // Cannot reactivate expired certificates
        }

        return $this->update([
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    public function sendRenewalReminder(): bool
    {
        if ($this->reminder_sent) {
            return false;
        }

        // Here you would trigger a notification/email
        // For now, just mark as sent
        return $this->update([
            'reminder_sent' => true,
            'reminder_sent_at' => now(),
        ]);
    }

    // Utility Methods
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'green',
            self::STATUS_EXPIRED => 'red',
            self::STATUS_REVOKED => 'red',
            self::STATUS_SUSPENDED => 'yellow',
            self::STATUS_PENDING_RENEWAL => 'blue',
            default => 'gray'
        };
    }

    public function getVerificationStatusBadgeColor(): string
    {
        return match($this->verification_status) {
            self::VERIFICATION_STATUS_VERIFIED => 'green',
            self::VERIFICATION_STATUS_PENDING => 'yellow',
            self::VERIFICATION_STATUS_UNVERIFIED => 'red',
            default => 'gray'
        };
    }

    public function getDisplayTitle(): string
    {
        if ($this->certificate_number) {
            return $this->title . ' (' . $this->certificate_number . ')';
        }

        return $this->title;
    }

    public function getValidityPeriod(): string
    {
        if (!$this->doesExpire()) {
            return 'Permanent certification';
        }

        $issued = $this->issued_date->format('M j, Y');
        $expires = $this->expiry_date ? $this->expiry_date->format('M j, Y') : 'No expiry';

        return "Valid from {$issued} to {$expires}";
    }

    public function getCertificationCost(): string
    {
        if (!$this->certification_cost) {
            return 'Not specified';
        }

        return 'RM ' . number_format($this->certification_cost, 2);
    }

    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    public function incrementUsage(): void
    {
        $this->increment('document_usage_count');
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

    public function isApplicableToService(string $service): bool
    {
        if (!$this->applicable_services) {
            return false;
        }

        return in_array($service, $this->applicable_services);
    }

    public function isApplicableToProject(string $projectType): bool
    {
        if (!$this->applicable_projects) {
            return false;
        }

        return in_array($projectType, $this->applicable_projects);
    }

    public function needsRenewal(): bool
    {
        return $this->isExpiringSoon($this->renewal_reminder_days);
    }

    public function canBeDisplayed(): bool
    {
        return $this->status === self::STATUS_ACTIVE 
               && $this->verification_status === self::VERIFICATION_STATUS_VERIFIED;
    }
}
