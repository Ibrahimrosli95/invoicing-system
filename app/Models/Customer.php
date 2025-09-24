<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use App\Services\WebhookEventService;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'lead_id',
        'name',
        'company_name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'postal_code',
        'customer_segment_id',
        'is_new_customer',
        'is_active',
        'notes',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_new_customer' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            // Set company_id from auth user if not set
            if (!$customer->company_id && auth()->check()) {
                $customer->company_id = auth()->user()->company_id;
            }

            // Set created_by from auth user
            if (!$customer->created_by && auth()->check()) {
                $customer->created_by = auth()->id();
            }
        });

        static::updating(function ($customer) {
            // Set updated_by from auth user
            if (auth()->check()) {
                $customer->updated_by = auth()->id();
            }
        });
    }

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customerSegment(): BelongsTo
    {
        return $this->belongsTo(CustomerSegment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes for multi-tenancy and filtering
     */
    public function scopeForCompany(Builder $query, $companyId = null): Builder
    {
        $companyId = $companyId ?: (auth()->check() ? auth()->user()->company_id : null);
        return $query->where('company_id', $companyId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeNewCustomers(Builder $query): Builder
    {
        return $query->where('is_new_customer', true);
    }

    public function scopeReturningCustomers(Builder $query): Builder
    {
        return $query->where('is_new_customer', false);
    }

    public function scopeWithSegment(Builder $query): Builder
    {
        return $query->whereNotNull('customer_segment_id');
    }

    /**
     * Search scope for AJAX endpoints
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('company_name', 'LIKE', "%{$term}%")
              ->orWhere('phone', 'LIKE', "%{$term}%")
              ->orWhere('email', 'LIKE', "%{$term}%")
              ->orWhere('city', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Business logic methods
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code
        ]);

        return implode(', ', $parts);
    }

    public function getTotalInvoiceAmountAttribute(): float
    {
        return $this->invoices()->sum('total');
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return $this->invoices()
            ->whereNotIn('status', ['PAID', 'CANCELLED'])
            ->sum('amount_due');
    }

    public function getInvoiceCountAttribute(): int
    {
        return $this->invoices()->count();
    }

    public function getPaidInvoiceCountAttribute(): int
    {
        return $this->invoices()->where('status', 'PAID')->count();
    }

    public function getOverdueInvoiceCountAttribute(): int
    {
        return $this->invoices()->where('status', 'OVERDUE')->count();
    }

    /**
     * Create customer from lead
     */
    public static function createFromLead(Lead $lead, array $additionalData = []): self
    {
        $customerData = array_merge([
            'company_id' => $lead->company_id,
            'lead_id' => $lead->id,
            'name' => $lead->name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'postal_code' => $lead->postal_code,
            'is_new_customer' => true,
            'notes' => $lead->notes,
            'created_by' => auth()->id(),
        ], $additionalData);

        $customer = static::create($customerData);

        // Mark lead as converted
        $lead->update([
            'status' => 'CONVERTED',
            'converted_at' => now(),
        ]);

        // Create lead activity
        $lead->activities()->create([
            'type' => 'converted_to_customer',
            'description' => "Lead converted to customer: {$customer->name}",
            'user_id' => auth()->id(),
            'metadata' => ['customer_id' => $customer->id],
        ]);

        return $customer;
    }

    /**
     * Mark customer as returning (not new)
     */
    public function markAsReturning(): void
    {
        $this->update(['is_new_customer' => false]);
    }

    /**
     * Check if customer has any transactions
     */
    public function hasTransactions(): bool
    {
        return $this->invoices()->exists() || $this->quotations()->exists();
    }

    /**
     * Get customer type badge
     */
    public function getTypeBadgeAttribute(): array
    {
        if ($this->is_new_customer) {
            return [
                'text' => 'New Customer',
                'class' => 'bg-green-100 text-green-800',
                'icon' => 'plus-circle'
            ];
        }

        return [
            'text' => 'Returning Customer',
            'class' => 'bg-blue-100 text-blue-800',
            'icon' => 'refresh'
        ];
    }

    /**
     * Get customer status badge
     */
    public function getStatusBadgeAttribute(): array
    {
        if (!$this->is_active) {
            return [
                'text' => 'Inactive',
                'class' => 'bg-gray-100 text-gray-800'
            ];
        }

        $overdueCount = $this->getOverdueInvoiceCountAttribute();
        if ($overdueCount > 0) {
            return [
                'text' => 'Has Overdue',
                'class' => 'bg-red-100 text-red-800'
            ];
        }

        $outstandingBalance = $this->getOutstandingBalanceAttribute();
        if ($outstandingBalance > 0) {
            return [
                'text' => 'Outstanding Balance',
                'class' => 'bg-yellow-100 text-yellow-800'
            ];
        }

        return [
            'text' => 'Good Standing',
            'class' => 'bg-green-100 text-green-800'
        ];
    }

    /**
     * Format customer for API response
     */
    public function toSearchResult(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company_name' => $this->company_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'city' => $this->city,
            'full_address' => $this->full_address,
            'customer_segment' => $this->customerSegment?->name,
            'is_new_customer' => $this->is_new_customer,
            'type_badge' => $this->type_badge,
            'status_badge' => $this->status_badge,
            'invoice_count' => $this->invoice_count,
            'outstanding_balance' => $this->outstanding_balance,
        ];
    }
}
