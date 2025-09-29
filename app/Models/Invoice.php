<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\WebhookEventService;

class Invoice extends Model
{
    use HasFactory;

    /**
     * Invoice status constants
     */
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_SENT = 'SENT';
    const STATUS_PARTIAL = 'PARTIAL';
    const STATUS_PAID = 'PAID';
    const STATUS_OVERDUE = 'OVERDUE';
    const STATUS_CANCELLED = 'CANCELLED';

    const TYPE_PRODUCT = 'product';
    const TYPE_SERVICE = 'service';

    /**
     * All available statuses
     */
    const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_SENT => 'Sent',
        self::STATUS_PARTIAL => 'Partially Paid',
        self::STATUS_PAID => 'Paid',
        self::STATUS_OVERDUE => 'Overdue',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    protected $fillable = [
        'company_id',
        'team_id',
        'assigned_to',
        'created_by',
        'quotation_id',
        'lead_id',
        'customer_id',
        'customer_segment_id',
        'number',
        'status',
        'issued_date',
        'due_date',
        'payment_terms',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_address',
        'customer_city',
        'customer_state',
        'customer_postal_code',
        'customer_company',
        'shipping_info',
        'title',
        'description',
        'terms_conditions',
        'notes',
        'payment_instructions',
        'optional_sections',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total',
        'amount_paid',
        'amount_due',
        'last_payment_date',
        'overdue_days',
        'sent_at',
        'paid_at',
        'cancelled_at',
        'cancellation_reason',
        'pdf_path',
        'pdf_generated_at',
        'bank_details',
        'reference_number',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_payment_date' => 'datetime',
        'pdf_generated_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'payment_terms' => 'integer',
        'type' => 'string',
        'overdue_days' => 'integer',
        'optional_sections' => 'array',
        'shipping_info' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            // Auto-generate invoice number if not set
            if (!$invoice->number) {
                $invoice->number = static::generateNumber($invoice->company_id);
            }

            // Set issued_date to today if not set
            if (!$invoice->issued_date) {
                $invoice->issued_date = now()->toDateString();
            }

            // Calculate due_date based on payment_terms
            if (!$invoice->due_date && $invoice->payment_terms) {
                $invoice->due_date = now()->addDays($invoice->payment_terms)->toDateString();
            }

            // Set amount_due equal to total initially
            $invoice->amount_due = $invoice->total;

            // Set company_id from auth user if not set
            if (!$invoice->company_id && auth()->check()) {
                $invoice->company_id = auth()->user()->company_id;
            }

            // Set created_by from auth user if not set
            if (!$invoice->created_by && auth()->check()) {
                $invoice->created_by = auth()->id();
            }
        });

        static::created(function ($invoice) {
            $webhookService = app(WebhookEventService::class);
            $webhookService->invoiceCreated($invoice);
        });

        static::updated(function ($invoice) {
            $webhookService = app(WebhookEventService::class);
            
            // Check for status changes that trigger specific webhook events
            if ($invoice->isDirty('status')) {
                $newStatus = $invoice->status;
                
                switch ($newStatus) {
                    case 'SENT':
                        $webhookService->invoiceSent($invoice);
                        break;
                    case 'PAID':
                        $webhookService->invoicePaid($invoice);
                        break;
                    case 'OVERDUE':
                        $webhookService->invoiceOverdue($invoice);
                        break;
                }
            }
            
            // Update overdue days when invoice is updated
            $invoice->updateOverdueDays();
        });

        static::retrieved(function ($invoice) {
            // Auto-update overdue status when retrieved
            $invoice->checkOverdueStatus();
        });
    }

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerSegment(): BelongsTo
    {
        return $this->belongsTo(CustomerSegment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function paymentRecords(): HasMany
    {
        return $this->hasMany(PaymentRecord::class)->orderBy('payment_date', 'desc');
    }

    public function proofs()
    {
        return $this->morphMany(Proof::class, 'scope')->active()->published()->notExpired();
    }

    public function allProofs()
    {
        return $this->morphMany(Proof::class, 'scope');
    }

    /**
     * Scopes for multi-tenancy and filtering
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?: (auth()->check() ? auth()->user()->company_id : null);
        return $query->where('company_id', $companyId);
    }

    public function scopeForUserTeams($query)
    {
        if (!auth()->check()) {
            return $query->whereNull('id'); // Return empty if not authenticated
        }

        $user = auth()->user();
        
        // Superadmin and company managers see all company invoices
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager'])) {
            return $query->forCompany();
        }
        
        // Sales managers see their team invoices
        if ($user->hasRole('sales_manager')) {
            $teamIds = $user->managedTeams()->pluck('teams.id');
            return $query->forCompany()->whereIn('team_id', $teamIds);
        }
        
        // Sales coordinators see all company invoices
        if ($user->hasRole('sales_coordinator')) {
            return $query->forCompany();
        }
        
        // Sales executives see only their own invoices
        return $query->forCompany()->where('assigned_to', $user->id);
    }

    /**
     * Aging-related scopes
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_PAID, self::STATUS_CANCELLED]);
    }

    public function scopeCurrent($query)
    {
        return $query->where(function($q) {
            $q->where('due_date', '>=', now())
              ->orWhereNull('due_date');
        })->whereNotIn('status', [self::STATUS_PAID, self::STATUS_CANCELLED]);
    }

    public function scopeAging0To30($query)
    {
        return $query->whereBetween('due_date', [now()->subDays(30), now()->subDay()])
                    ->whereNotIn('status', [self::STATUS_PAID, self::STATUS_CANCELLED]);
    }

    public function scopeAging31To60($query)
    {
        return $query->whereBetween('due_date', [now()->subDays(60), now()->subDays(31)])
                    ->whereNotIn('status', [self::STATUS_PAID, self::STATUS_CANCELLED]);
    }

    public function scopeAging61To90($query)
    {
        return $query->whereBetween('due_date', [now()->subDays(90), now()->subDays(61)])
                    ->whereNotIn('status', [self::STATUS_PAID, self::STATUS_CANCELLED]);
    }

    public function scopeAging90Plus($query)
    {
        return $query->where('due_date', '<', now()->subDays(90))
                    ->whereNotIn('status', [self::STATUS_PAID, self::STATUS_CANCELLED]);
    }

    /**
     * Late fee calculations
     */
    public function calculateLateFee(float $lateFeePercentage = 1.5, int $gracePeriodDays = 7): float
    {
        if (!$this->isOverdue() || $this->isPaid()) {
            return 0;
        }

        $daysOverdue = $this->getDaysOverdue();
        
        if ($daysOverdue <= $gracePeriodDays) {
            return 0; // Grace period
        }

        // Calculate late fee as percentage of outstanding amount
        return ($this->amount_due * $lateFeePercentage) / 100;
    }

    /**
     * Get recommended action based on aging bucket
     */
    public function getRecommendedAction(): string
    {
        return match($this->getAgingBucket()) {
            'current' => 'Monitor - not due yet',
            '0-30' => 'Send friendly reminder',
            '31-60' => 'Follow up with phone call',
            '61-90' => 'Urgent collection required',
            '90+' => 'Consider collections agency or legal action',
            'paid' => 'No action needed',
            default => 'Review required',
        };
    }

    /**
     * Get risk level based on aging bucket
     */
    public function getRiskLevel(): string
    {
        return match($this->getAgingBucket()) {
            'current' => 'low',
            '0-30' => 'low',
            '31-60' => 'medium',
            '61-90' => 'high',
            '90+' => 'critical',
            'paid' => 'none',
            default => 'unknown',
        };
    }

    /**
     * Status checking methods
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE || 
               ($this->due_date && $this->due_date->isPast() && !$this->isPaid());
    }

    /**
     * Get days overdue (negative if not yet due)
     */
    public function getDaysOverdue(): int
    {
        if (!$this->due_date || $this->isPaid()) {
            return 0;
        }
        
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Get aging bucket for this invoice
     */
    public function getAgingBucket(): string
    {
        if ($this->isPaid()) {
            return 'paid';
        }

        $daysOverdue = $this->getDaysOverdue();
        
        if ($daysOverdue <= 0) {
            return 'current'; // Not due yet
        } elseif ($daysOverdue <= 30) {
            return '0-30';
        } elseif ($daysOverdue <= 60) {
            return '31-60';
        } elseif ($daysOverdue <= 90) {
            return '61-90';
        } else {
            return '90+';
        }
    }

    /**
     * Get aging bucket display name
     */
    public function getAgingBucketName(): string
    {
        return match($this->getAgingBucket()) {
            'current' => 'Current (Not Due)',
            '0-30' => '1-30 Days',
            '31-60' => '31-60 Days', 
            '61-90' => '61-90 Days',
            '90+' => '90+ Days',
            'paid' => 'Paid',
            default => 'Unknown',
        };
    }

    /**
     * Get aging bucket color class
     */
    public function getAgingBucketColor(): string
    {
        return match($this->getAgingBucket()) {
            'current' => 'text-green-600 bg-green-100',
            '0-30' => 'text-yellow-600 bg-yellow-100',
            '31-60' => 'text-orange-600 bg-orange-100',
            '61-90' => 'text-red-600 bg-red-100',
            '90+' => 'text-red-800 bg-red-200',
            'paid' => 'text-blue-600 bg-blue-100',
            default => 'text-gray-600 bg-gray-100',
        };
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Status management methods
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'amount_paid' => $this->total,
            'amount_due' => 0,
        ]);

        // Lock all invoice items
        $this->items()->update(['is_locked' => true]);

        // Update customer status - mark as returning customer if this is their first payment
        if ($this->customer_phone) {
            $customer = Customer::forCompany()->where('phone', $this->customer_phone)->first();
            if ($customer) {
                $customer->updateCustomerStatus();
            }
        }
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Payment methods
     */
    public function recordPayment(float $amount, array $paymentDetails): PaymentRecord
    {
        $payment = $this->paymentRecords()->create(array_merge($paymentDetails, [
            'amount' => $amount,
            'company_id' => $this->company_id,
            'recorded_by' => auth()->id(),
        ]));

        $this->updatePaymentStatus();

        return $payment;
    }

    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->paymentRecords()->where('status', 'CLEARED')->sum('amount');
        $amountDue = $this->total_amount - $totalPaid;

        $status = $this->status;
        if ($totalPaid >= $this->total_amount) {
            $status = self::STATUS_PAID;
            $this->paid_at = now();
        } elseif ($totalPaid > 0) {
            $status = self::STATUS_PARTIAL;
        } elseif ($this->isOverdue()) {
            $status = self::STATUS_OVERDUE;
        }

        $this->update([
            'amount_paid' => $totalPaid,
            'amount_due' => max(0, $amountDue),
            'status' => $status,
            'last_payment_date' => $this->paymentRecords()->where('status', 'CLEARED')->max('payment_date'),
        ]);

        // Lock items if fully paid
        if ($status === self::STATUS_PAID) {
            $this->items()->update(['is_locked' => true]);
        }
    }

    /**
     * Financial calculations
     */
    public function calculateTotals(): void
    {
        $itemsTotal = $this->items->sum('total_price');
        
        $this->subtotal_amount = $itemsTotal;
        $this->discount_amount = ($this->subtotal_amount * $this->discount_percentage) / 100;
        $afterDiscount = $this->subtotal_amount - $this->discount_amount;
        $this->tax_amount = ($afterDiscount * $this->tax_percentage) / 100;
        $this->total_amount = $afterDiscount + $this->tax_amount;
        $this->amount_due = $this->total_amount - $this->amount_paid;

        $this->saveQuietly(); // Save without firing events
    }

    /**
     * Business logic methods
     */
    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT]);
    }

    public function canBeSent(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->items->isNotEmpty();
    }

    public function canBeCancelled(): bool
    {
        return !in_array($this->status, [self::STATUS_PAID, self::STATUS_CANCELLED]);
    }

    public function canAcceptPayment(): bool
    {
        return in_array($this->status, [self::STATUS_SENT, self::STATUS_PARTIAL, self::STATUS_OVERDUE]) 
               && $this->amount_due > 0;
    }

    /**
     * Generate invoice number
     */
    public static function generateNumber(?int $companyId = null): string
    {
        $companyId = $companyId ?: (auth()->check() ? auth()->user()->company_id : 1);
        $year = now()->year;

        // Get the last invoice number for this company and year
        $lastInvoice = static::forCompany($companyId)
            ->where('number', 'like', "INV-{$year}-%")
            ->orderByDesc('number')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->number, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('INV-%s-%06d', $year, $nextNumber);
    }

    /**
     * Update overdue days
     */
    protected function updateOverdueDays(): void
    {
        if ($this->due_date && $this->due_date->isPast() && !$this->isPaid()) {
            $this->overdue_days = $this->due_date->diffInDays(now());
        } else {
            $this->overdue_days = 0;
        }
    }

    /**
     * Check and update overdue status
     */
    protected function checkOverdueStatus(): void
    {
        if ($this->due_date && $this->due_date->isPast() && 
            !$this->isPaid() && !$this->isCancelled() && 
            $this->status !== self::STATUS_OVERDUE) {
            
            $this->updateOverdueDays();
            $this->update(['status' => self::STATUS_OVERDUE]);
        }
    }

    /**
     * Create invoice from quotation
     */
    public static function createFromQuotation(Quotation $quotation): self
    {
        $invoice = static::create([
            'company_id' => $quotation->company_id,
            'team_id' => $quotation->team_id,
            'assigned_to' => $quotation->assigned_to,
            'created_by' => auth()->id(),
            'quotation_id' => $quotation->id,
            'customer_segment_id' => $quotation->customer_segment_id,
            'title' => $quotation->title,
            'customer_name' => $quotation->customer_name,
            'customer_phone' => $quotation->customer_phone,
            'customer_email' => $quotation->customer_email,
            'customer_address' => $quotation->customer_address,
            'customer_city' => $quotation->customer_city,
            'customer_state' => $quotation->customer_state,
            'customer_postal_code' => $quotation->customer_postal_code,
            'description' => $quotation->description,
            'terms_conditions' => $quotation->terms_conditions,
            'notes' => $quotation->notes,
            'subtotal' => $quotation->subtotal_amount,
            'discount_percentage' => $quotation->discount_percentage,
            'discount_amount' => $quotation->discount_amount,
            'tax_percentage' => $quotation->tax_percentage,
            'tax_amount' => $quotation->tax_amount,
            'total' => $quotation->total_amount,
            'due_date' => now()->addDays(30),
            'payment_terms' => 30,
        ]);

        // Copy quotation items
        foreach ($quotation->items as $quotationItem) {
            $invoice->items()->create([
                'quotation_item_id' => $quotationItem->id,
                'description' => $quotationItem->description,
                'specifications' => $quotationItem->specifications,
                'unit' => $quotationItem->unit,
                'quantity' => $quotationItem->quantity,
                'unit_price' => $quotationItem->unit_price,
                'total_price' => $quotationItem->total_price,
                'sort_order' => $quotationItem->sort_order,
            ]);
        }

        // Mark quotation as converted
        $quotation->update(['status' => Quotation::STATUS_CONVERTED]);

        return $invoice;
    }

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return self::STATUSES;
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_PRODUCT => 'Product Invoice',
            self::TYPE_SERVICE => 'Service Invoice',
        ];
    }

    /**
     * Status badge CSS classes
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_SENT => 'bg-blue-100 text-blue-800',
            self::STATUS_PARTIAL => 'bg-yellow-100 text-yellow-800',
            self::STATUS_PAID => 'bg-green-100 text-green-800',
            self::STATUS_OVERDUE => 'bg-red-100 text-red-800',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-500',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Optional sections functionality
     */
    public function getOptionalSectionsAttribute($value): array
    {
        return $value ? json_decode($value, true) : $this->getDefaultOptionalSections();
    }

    public function setOptionalSectionsAttribute($value): void
    {
        $this->attributes['optional_sections'] = json_encode($value);
    }

    public function getDefaultOptionalSections(): array
    {
        return [
            'show_shipping' => true,
            'show_payment_instructions' => true,
            'show_signatures' => true,
            'show_company_logo' => true,
            'show_additional_notes' => false,
        ];
    }

    /**
     * Create invoice from customer
     */
    public static function createFromCustomer(Customer $customer, array $data = []): self
    {
        $invoice = new static($data);
        $invoice->customer_id = $customer->id;
        $invoice->populateCustomerData($customer);
        $invoice->save();

        return $invoice;
    }

    /**
     * Check if invoice has customer relationship
     */
    public function hasCustomer(): bool
    {
        return !is_null($this->customer_id);
    }

    /**
     * Get customer display name (fallback to customer_name field)
     */
    public function getCustomerDisplayNameAttribute(): string
    {
        return $this->customer?->name ?: $this->customer_name;
    }

    /**
     * Get customer full address (fallback to individual fields)
     */
    public function getCustomerFullAddressAttribute(): string
    {
        if ($this->customer) {
            return $this->customer->full_address;
        }

        $parts = array_filter([
            $this->customer_address,
            $this->customer_city,
            $this->customer_state,
            $this->customer_postal_code
        ]);

        return implode(', ', $parts);
    }

    /**
     * Check if a section should be shown
     */
    public function shouldShowSection(string $section): bool
    {
        $sections = $this->optional_sections ?? [];

        if (is_string($sections)) {
            $sections = json_decode($sections, true) ?? [];
        }

        return $sections["show_{$section}"] ?? false;
    }

    /**
     * Get logo position setting
     */
    public function getLogoPosition(): string
    {
        $settings = $this->company->invoice_settings ?? [];
        return $settings['logo']['logo_position'] ?? 'left';
    }

    /**
     * Get logo size setting
     */
    public function getLogoSize(): string
    {
        $settings = $this->company->invoice_settings ?? [];
        return $settings['logo']['logo_size'] ?? 'medium';
    }

    /**
     * Get payment instructions from company settings
     */
    public function getPaymentInstructions(): array
    {
        $settings = $this->company->invoice_settings ?? [];
        return $settings['content']['payment_instructions'] ?? [
            'bank_name' => '',
            'account_number' => '',
            'account_holder' => '',
            'swift_code' => '',
            'additional_info' => 'Please include invoice number in payment reference.',
        ];
    }

    /**
     * Get customer display name
     */
    public function getCustomerDisplayName(): string
    {
        if ($this->customer) {
            return $this->customer->name;
        }

        return $this->customer_name ?? 'N/A';
    }

    /**
     * Apply invoice settings to this invoice
     */
    public function applyInvoiceSettings(): void
    {
        $service = app(\App\Services\InvoiceSettingsService::class);
        $service->applySettingsToInvoice($this, $this->company_id);
    }

    /**
     * Toggle a section's visibility
     */
    public function toggleSection(string $section, bool $visible = null): void
    {
        $sections = $this->optional_sections ?? [];

        if (is_string($sections)) {
            $sections = json_decode($sections, true) ?? [];
        }

        $visible = $visible ?? !($sections["show_{$section}"] ?? false);
        $sections["show_{$section}"] = $visible;

        $this->optional_sections = $sections;
        $this->save();
    }

    /**
     * Populate customer data from a customer or lead
     */
    public function populateCustomerData($source): void
    {
        if ($source instanceof \App\Models\Customer) {
            $this->customer_id = $source->id;
            $this->customer_name = $source->name;
            $this->customer_phone = $source->phone;
            $this->customer_email = $source->email;
            $this->customer_company = $source->company;
            $this->customer_address = $source->address;
            $this->customer_city = $source->city;
            $this->customer_state = $source->state;
            $this->customer_postal_code = $source->postal_code;
            $this->customer_segment_id = $source->customer_segment_id;
        } elseif ($source instanceof \App\Models\Lead) {
            $this->customer_name = $source->name;
            $this->customer_phone = $source->phone;
            $this->customer_email = $source->email;
            $this->customer_company = $source->company;
            $this->customer_address = $source->address;
            $this->customer_city = $source->city;
            $this->customer_state = $source->state;
            $this->customer_postal_code = $source->postal_code;
        }
    }
}



