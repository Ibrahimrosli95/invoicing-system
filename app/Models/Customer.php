<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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

            // Automatically mark all new customers as "new" until they have payment history
            $customer->is_new_customer = true;
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

    /**
     * Get invoices for this customer.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get quotations for this customer.
     * Since quotations don't have customer_id, we get quotations through the lead relationship.
     */
    public function quotations(): HasMany
    {
        // Create a custom relationship query that gets quotations through the lead
        return $this->hasMany(Quotation::class, 'lead_id', 'lead_id');
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

    // Note: Invoice relationship methods removed due to missing customer_id foreign key
    // These will be restored once the database schema is updated to support customer relationships

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
     * Check and automatically update customer status based on business logic
     */
    public function updateCustomerStatus(): void
    {
        // If customer is currently marked as new, check if they should be returning
        if ($this->is_new_customer) {
            // Mark as returning if they have any paid invoices
            $hasPaidInvoices = \App\Models\Invoice::forCompany()
                ->where('customer_phone', $this->phone)
                ->where('status', 'PAID')
                ->exists();

            if ($hasPaidInvoices) {
                $this->markAsReturning();
            }
        }
    }

    /**
     * Check if customer has any transactions
     * Note: Since invoices and quotations don't have customer_id foreign keys,
     * we check by matching customer phone number which is the current identifier used
     */
    public function hasTransactions(): bool
    {
        // Check if any invoices exist with this customer's phone
        $hasInvoices = \App\Models\Invoice::forCompany()
            ->where('customer_phone', $this->phone)
            ->exists();

        // Check if any quotations exist with this customer's phone
        $hasQuotations = \App\Models\Quotation::forCompany()
            ->where('customer_phone', $this->phone)
            ->exists();

        return $hasInvoices || $hasQuotations;
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

        // Check for overdue invoices by phone number
        $overdueCount = \App\Models\Invoice::forCompany()
            ->where('customer_phone', $this->phone)
            ->where('status', 'OVERDUE')
            ->count();

        if ($overdueCount > 0) {
            return [
                'text' => 'Has Overdue',
                'class' => 'bg-red-100 text-red-800'
            ];
        }

        // Note: Outstanding balance check removed due to missing customer_id foreign key

        return [
            'text' => 'Good Standing',
            'class' => 'bg-green-100 text-green-800'
        ];
    }

    /**
     * Find or create a customer from invoice data.
     * Creates customer record when invoice is paid to track paying customers.
     *
     * @param \App\Models\Invoice $invoice Invoice to create customer from
     * @return Customer
     */
    public static function findOrCreateFromInvoice(\App\Models\Invoice $invoice): self
    {
        // First, try to find existing customer by phone number
        $existingCustomer = static::forCompany($invoice->company_id)
            ->where('phone', $invoice->customer_phone)
            ->first();

        if ($existingCustomer) {
            // Update customer with latest information
            $existingCustomer->update([
                'email' => $invoice->customer_email ?? $existingCustomer->email,
                'company_name' => $invoice->customer_company ?? $existingCustomer->company_name,
                'address' => $invoice->customer_address ?? $existingCustomer->address,
                'city' => $invoice->customer_city ?? $existingCustomer->city,
                'state' => $invoice->customer_state ?? $existingCustomer->state,
                'postal_code' => $invoice->customer_postal_code ?? $existingCustomer->postal_code,
                'customer_segment_id' => $invoice->customer_segment_id ?? $existingCustomer->customer_segment_id,
            ]);

            // Check if customer should be marked as returning
            $existingCustomer->updateCustomerStatus();

            return $existingCustomer;
        }

        // Create new customer if not found
        // If invoice has a lead, create from lead (includes lead status update)
        if ($invoice->lead_id && $invoice->lead) {
            $customer = static::createFromLead($invoice->lead, [
                'company_name' => $invoice->customer_company,
                'customer_segment_id' => $invoice->customer_segment_id,
            ]);
        } else {
            // Create standalone customer without lead
            $customerData = [
                'company_id' => $invoice->company_id,
                'name' => $invoice->customer_name,
                'company_name' => $invoice->customer_company,
                'phone' => $invoice->customer_phone,
                'email' => $invoice->customer_email,
                'address' => $invoice->customer_address,
                'city' => $invoice->customer_city,
                'state' => $invoice->customer_state,
                'postal_code' => $invoice->customer_postal_code,
                'customer_segment_id' => $invoice->customer_segment_id,
                'is_new_customer' => true,
                'notes' => "Customer created from invoice #{$invoice->number}",
                'created_by' => auth()->id() ?? $invoice->created_by,
            ];

            $customer = static::create($customerData);
        }

        return $customer;
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
            // Note: invoice_count and outstanding_balance removed due to missing customer_id foreign key
        ];
    }
}
