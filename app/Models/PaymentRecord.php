<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\WebhookEventService;

class PaymentRecord extends Model
{
    use HasFactory;

    /**
     * Payment method constants
     */
    const METHOD_CASH = 'CASH';
    const METHOD_CHEQUE = 'CHEQUE';
    const METHOD_BANK_TRANSFER = 'BANK_TRANSFER';
    const METHOD_CREDIT_CARD = 'CREDIT_CARD';
    const METHOD_ONLINE_BANKING = 'ONLINE_BANKING';
    const METHOD_OTHER = 'OTHER';

    const METHODS = [
        self::METHOD_CASH => 'Cash',
        self::METHOD_CHEQUE => 'Cheque',
        self::METHOD_BANK_TRANSFER => 'Bank Transfer',
        self::METHOD_CREDIT_CARD => 'Credit Card',
        self::METHOD_ONLINE_BANKING => 'Online Banking',
        self::METHOD_OTHER => 'Other',
    ];

    /**
     * Payment status constants
     */
    const STATUS_PENDING = 'PENDING';
    const STATUS_CLEARED = 'CLEARED';
    const STATUS_BOUNCED = 'BOUNCED';
    const STATUS_CANCELLED = 'CANCELLED';

    const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_CLEARED => 'Cleared',
        self::STATUS_BOUNCED => 'Bounced',
        self::STATUS_CANCELLED => 'Cancelled',
    ];

    protected $fillable = [
        'invoice_id',
        'company_id',
        'recorded_by',
        'amount',
        'payment_date',
        'recorded_date',
        'payment_method',
        'reference_number',
        'notes',
        'bank_name',
        'account_number',
        'cheque_number',
        'cheque_date',
        'cheque_bank',
        'receipt_number',
        'receipt_issued',
        'status',
        'clearance_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'recorded_date' => 'date',
        'cheque_date' => 'date',
        'clearance_date' => 'date',
        'receipt_issued' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            // Set recorded_date to today if not set
            if (!$payment->recorded_date) {
                $payment->recorded_date = now()->toDateString();
            }

            // Set company_id from auth user if not set
            if (!$payment->company_id && auth()->check()) {
                $payment->company_id = auth()->user()->company_id;
            }

            // Set recorded_by from auth user if not set
            if (!$payment->recorded_by && auth()->check()) {
                $payment->recorded_by = auth()->id();
            }

            // Auto-generate receipt number if not set
            if (!$payment->receipt_number) {
                $payment->receipt_number = $payment->generateReceiptNumber();
            }

            // Set clearance date for immediate payment methods
            if (in_array($payment->payment_method, [self::METHOD_CASH, self::METHOD_CREDIT_CARD, self::METHOD_ONLINE_BANKING]) && !$payment->clearance_date) {
                $payment->clearance_date = $payment->payment_date;
            }
        });

        static::created(function ($payment) {
            $webhookService = app(WebhookEventService::class);
            $webhookService->paymentReceived($payment);
        });

        static::updated(function ($payment) {
            $webhookService = app(WebhookEventService::class);
            
            // Check for status changes that indicate payment failure
            if ($payment->isDirty('status') && $payment->status === 'BOUNCED') {
                $webhookService->paymentFailed($payment, 'Payment bounced or was cancelled');
            }
        });

        static::saved(function ($payment) {
            // Update invoice payment status when payment is saved
            if ($payment->invoice) {
                $payment->invoice->updatePaymentStatus();

                // If invoice is now fully paid, fire InvoicePaid event and create customer
                if ($payment->invoice->status === 'PAID') {
                    \App\Events\InvoicePaid::dispatch($payment->invoice, $payment, auth()->user());

                    // Automatically create customer record when invoice is paid
                    if (!$payment->invoice->customer_id) {
                        $payment->invoice->createOrLinkCustomer();
                    }
                }
                // Also create customer on first partial payment
                elseif ($payment->invoice->status === 'PARTIAL' && !$payment->invoice->customer_id) {
                    $payment->invoice->createOrLinkCustomer();
                }
            }
        });

        static::deleted(function ($payment) {
            // Update invoice payment status when payment is deleted
            if ($payment->invoice) {
                $payment->invoice->updatePaymentStatus();
            }
        });
    }

    /**
     * Relationships
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Scopes
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?: (auth()->check() ? auth()->user()->company_id : null);
        return $query->where('company_id', $companyId);
    }

    public function scopeCleared($query)
    {
        return $query->where('status', self::STATUS_CLEARED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Business logic methods
     */
    public function isCleared(): bool
    {
        return $this->status === self::STATUS_CLEARED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isBounced(): bool
    {
        return $this->status === self::STATUS_BOUNCED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function markAsCleared(): void
    {
        $this->update([
            'status' => self::STATUS_CLEARED,
            'clearance_date' => $this->clearance_date ?: now()->toDateString(),
        ]);
    }

    public function markAsBounced(): void
    {
        $this->update([
            'status' => self::STATUS_BOUNCED,
            'clearance_date' => null,
        ]);
    }

    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }

    public function issueReceipt(): void
    {
        $this->update([
            'receipt_issued' => true,
        ]);
    }

    /**
     * Generate receipt number
     */
    protected function generateReceiptNumber(): string
    {
        $companyId = $this->company_id ?: (auth()->check() ? auth()->user()->company_id : 1);
        $year = now()->year;
        
        // Get the last receipt number for this company and year
        $lastPayment = static::forCompany($companyId)
            ->where('receipt_number', 'like', "RCP-{$year}-%")
            ->orderByDesc('receipt_number')
            ->first();
        
        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->receipt_number, -6);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return sprintf('RCP-%s-%06d', $year, $nextNumber);
    }

    /**
     * Get method badge CSS classes
     */
    public function getMethodBadgeColor(): string
    {
        return match($this->payment_method) {
            self::METHOD_CASH => 'bg-green-100 text-green-800',
            self::METHOD_CHEQUE => 'bg-yellow-100 text-yellow-800',
            self::METHOD_BANK_TRANSFER => 'bg-blue-100 text-blue-800',
            self::METHOD_CREDIT_CARD => 'bg-purple-100 text-purple-800',
            self::METHOD_ONLINE_BANKING => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get status badge CSS classes
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-yellow-100 text-yellow-800',
            self::STATUS_CLEARED => 'bg-green-100 text-green-800',
            self::STATUS_BOUNCED => 'bg-red-100 text-red-800',
            self::STATUS_CANCELLED => 'bg-gray-100 text-gray-500',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
