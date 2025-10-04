<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Services\WebhookEventService;

class Quotation extends Model
{

    protected $fillable = [
        'number',
        'type',
        'status',
        'company_id',
        'company_logo_id',
        'lead_id',
        'customer_segment_id',
        'team_id',
        'assigned_to',
        'created_by',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'customer_city',
        'customer_state',
        'customer_postal_code',
        'customer_company',
        'title',
        'description',
        'terms_conditions',
        'notes',
        'payment_instructions',
        'optional_sections',
        'shipping_info',
        'quotation_date',
        'valid_until',
        'reference_number',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total',
        'sent_at',
        'viewed_at',
        'accepted_at',
        'rejected_at',
        'pdf_path',
        'view_count',
        'view_history',
        'is_active',
        'rejection_reason',
        'internal_notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'view_history' => 'array',
        'optional_sections' => 'array',
        'shipping_info' => 'array',
        'is_active' => 'boolean',
        'view_count' => 'integer',
    ];

    // Status constants
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SENT = 'SENT';
    const STATUS_VIEWED = 'VIEWED';
    const STATUS_ACCEPTED = 'ACCEPTED';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_EXPIRED = 'EXPIRED';
    const STATUS_CONVERTED = 'CONVERTED';

    // Type constants
    const TYPE_PRODUCT = 'product';
    const TYPE_SERVICE = 'service';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Sent',
            self::STATUS_VIEWED => 'Viewed',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_EXPIRED => 'Expired',
            self::STATUS_CONVERTED => 'Converted',
        ];
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_PRODUCT => 'Product Quotation',
            self::TYPE_SERVICE => 'Service Quotation',
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

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function customerSegment(): BelongsTo
    {
        return $this->belongsTo(CustomerSegment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(QuotationSection::class)->orderBy('sort_order');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function proofs()
    {
        return $this->morphMany(Proof::class, 'scope')->active()->published()->notExpired();
    }

    public function allProofs()
    {
        return $this->morphMany(Proof::class, 'scope');
    }

    // Scopes for multi-tenancy and role-based access
    public function scopeForCompany(Builder $query, ?int $companyId = null): Builder
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        return $query->where('company_id', $companyId);
    }

    public function scopeForUserTeams(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0'); // No results if no user
        }

        if ($user->hasRole('superadmin') || $user->hasRole('company_manager')) {
            return $query; // Can see all quotations in company
        }

        if ($user->hasRole('finance_manager')) {
            return $query; // Finance can see all quotations
        }

        if ($user->hasRole('sales_manager')) {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $query->whereIn('team_id', $managedTeamIds);
        }

        if ($user->hasRole('sales_coordinator')) {
            $coordinatedTeamIds = Team::where('coordinator_id', $user->id)->pluck('id');
            return $query->whereIn('team_id', $coordinatedTeamIds);
        }

        if ($user->hasRole('sales_executive')) {
            return $query->where('assigned_to', $user->id);
        }

        return $query->whereRaw('1 = 0'); // No results for unknown roles
    }

    // Business logic methods
    public function generateNumber(): string
    {
        if ($this->number) {
            return $this->number;
        }

        $year = now()->year;
        $companyId = $this->company_id;
        
        // Get the next sequence number for this company and year
        $lastQuotation = static::forCompany($companyId)
            ->where('number', 'like', "QTN-{$year}-%")
            ->orderByDesc('number')
            ->first();

        if ($lastQuotation) {
            $lastNumber = intval(substr($lastQuotation->number, -6));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('QTN-%s-%06d', $year, $nextNumber);
    }

    public function calculateTotals(): void
    {
        if ($this->type === self::TYPE_SERVICE && $this->sections()->exists()) {
            // Service quotation with sections
            $this->subtotal = $this->sections->sum('total');
        } else {
            // Product quotation or service without sections
            $this->subtotal = $this->items->sum('total_price');
        }

        // Apply discount
        if ($this->discount_percentage > 0) {
            $this->discount_amount = ($this->subtotal * $this->discount_percentage) / 100;
        }

        $afterDiscount = $this->subtotal - $this->discount_amount;

        // Apply tax
        if ($this->tax_percentage > 0) {
            $this->tax_amount = ($afterDiscount * $this->tax_percentage) / 100;
        }

        $this->total = $afterDiscount + $this->tax_amount;
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SENT, self::STATUS_VIEWED]);
    }

    public function canBeSent(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->items()->exists();
    }

    public function canBeAccepted(): bool
    {
        return in_array($this->status, [self::STATUS_SENT, self::STATUS_VIEWED]) && !$this->isExpired();
    }

    public function canBeConverted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    public function markAsViewed(): void
    {
        if ($this->status === self::STATUS_SENT) {
            $this->update([
                'status' => self::STATUS_VIEWED,
                'viewed_at' => now(),
            ]);
        }

        // Increment view count
        $this->increment('view_count');

        // Add to view history
        $history = $this->view_history ?? [];
        $history[] = [
            'viewed_at' => now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
        
        $this->update(['view_history' => $history]);
    }

    public function markAsAccepted(): void
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        // Update related lead status if exists
        if ($this->lead) {
            $this->lead->update(['status' => Lead::STATUS_QUOTED]);
        }

        // Fire event for proof compilation
        \App\Events\QuotationAccepted::dispatch($this, auth()->user());
    }

    public function markAsRejected(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function markAsConverted(): void
    {
        $this->update([
            'status' => self::STATUS_CONVERTED,
        ]);

        // Update related lead status if exists
        if ($this->lead) {
            $this->lead->update(['status' => Lead::STATUS_WON]);
        }

        // Fire project completion event
        \App\Events\ProjectCompleted::dispatch($this, null, $this->lead, auth()->user());
    }

    // Helper methods
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_SENT => 'bg-blue-100 text-blue-800',
            self::STATUS_VIEWED => 'bg-yellow-100 text-yellow-800',
            self::STATUS_ACCEPTED => 'bg-green-100 text-green-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            self::STATUS_EXPIRED => 'bg-red-100 text-red-800',
            self::STATUS_CONVERTED => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function getFormattedCustomerAddress(): string
    {
        $parts = array_filter([
            $this->customer_address,
            implode(' ', array_filter([
                $this->customer_postal_code,
                $this->customer_city,
                $this->customer_state,
            ])),
        ]);

        return implode("\n", $parts);
    }

    // Boot method for auto-generating number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quotation) {
            if (!$quotation->number) {
                $quotation->number = $quotation->generateNumber();
            }
            
            if (!$quotation->created_by) {
                $quotation->created_by = auth()->id();
            }

            // Set default validity (30 days from creation)
            if (!$quotation->valid_until) {
                $quotation->valid_until = now()->addDays(30);
            }
        });

        static::created(function ($quotation) {
            $webhookService = app(WebhookEventService::class);
            $webhookService->quotationCreated($quotation);
        });

        static::updated(function ($quotation) {
            $webhookService = app(WebhookEventService::class);
            
            // Check for status changes that trigger specific webhook events
            if ($quotation->isDirty('status')) {
                $newStatus = $quotation->status;
                
                switch ($newStatus) {
                    case 'SENT':
                        $webhookService->quotationSent($quotation);
                        break;
                    case 'ACCEPTED':
                        $webhookService->quotationAccepted($quotation);
                        break;
                    case 'REJECTED':
                        $webhookService->quotationRejected($quotation);
                        break;
                    case 'EXPIRED':
                        $webhookService->quotationExpired($quotation);
                        break;
                }
            }
            
            // Check for viewed_at changes
            if ($quotation->isDirty('viewed_at') && $quotation->viewed_at) {
                $webhookService->quotationViewed($quotation);
            }
        });

        static::saved(function ($quotation) {
            // Recalculate totals when quotation is saved
            if ($quotation->wasChanged(['discount_percentage', 'discount_amount', 'tax_percentage'])) {
                $quotation->calculateTotals();
                $quotation->saveQuietly(); // Prevent infinite loop
            }
        });
    }
}
