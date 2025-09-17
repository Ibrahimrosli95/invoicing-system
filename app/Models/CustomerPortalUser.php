<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class CustomerPortalUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'company_name',
        'password',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'is_active',
        'notification_preferences',
        'preferred_language',
        'timezone',
        'accessible_quotations',
        'accessible_invoices',
        'can_download_pdfs',
        'can_view_payment_history',
        'last_login_at',
        'last_login_ip',
        'login_count',
        'password_reset_token',
        'password_reset_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'password_reset_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'notification_preferences' => 'array',
        'accessible_quotations' => 'array',
        'accessible_invoices' => 'array',
        'can_download_pdfs' => 'boolean',
        'can_view_payment_history' => 'boolean',
        'last_login_at' => 'datetime',
        'login_count' => 'integer',
        'password_reset_expires_at' => 'datetime',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'customer_email', 'email')
            ->where('company_id', $this->company_id);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_email', 'email')
            ->where('company_id', $this->company_id);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?? auth('customer-portal')->user()?->company_id;
        return $query->where('company_id', $companyId);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    // Business Logic Methods
    public function getAccessibleQuotations()
    {
        $query = Quotation::where('company_id', $this->company_id);

        // If specific quotations are defined, filter by those
        if (!empty($this->accessible_quotations)) {
            $query->whereIn('id', $this->accessible_quotations);
        } else {
            // Otherwise, show quotations where customer email matches
            $query->where('customer_email', $this->email);
        }

        return $query->with(['createdBy', 'items'])
            ->orderBy('created_at', 'desc');
    }

    public function getAccessibleInvoices()
    {
        $query = Invoice::where('company_id', $this->company_id);

        // If specific invoices are defined, filter by those
        if (!empty($this->accessible_invoices)) {
            $query->whereIn('id', $this->accessible_invoices);
        } else {
            // Otherwise, show invoices where customer email matches
            $query->where('customer_email', $this->email);
        }

        return $query->with(['quotation', 'createdBy', 'paymentRecords'])
            ->orderBy('created_at', 'desc');
    }

    public function canAccessQuotation($quotationId)
    {
        // Check if user has specific quotation access
        if (!empty($this->accessible_quotations)) {
            return in_array($quotationId, $this->accessible_quotations);
        }

        // Check if quotation belongs to this customer
        return Quotation::where('id', $quotationId)
            ->where('company_id', $this->company_id)
            ->where('customer_email', $this->email)
            ->exists();
    }

    public function canAccessInvoice($invoiceId)
    {
        // Check if user has specific invoice access
        if (!empty($this->accessible_invoices)) {
            return in_array($invoiceId, $this->accessible_invoices);
        }

        // Check if invoice belongs to this customer
        return Invoice::where('id', $invoiceId)
            ->where('company_id', $this->company_id)
            ->where('customer_email', $this->email)
            ->exists();
    }

    public function updateLoginTracking($ipAddress = null)
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress ?? request()->ip(),
            'login_count' => $this->login_count + 1,
        ]);
    }

    public function generatePasswordResetToken()
    {
        $token = bin2hex(random_bytes(32));
        
        $this->update([
            'password_reset_token' => $token,
            'password_reset_expires_at' => now()->addHours(24),
        ]);

        return $token;
    }

    public function clearPasswordResetToken()
    {
        $this->update([
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);
    }

    public function isPasswordResetTokenValid($token)
    {
        return $this->password_reset_token === $token && 
               $this->password_reset_expires_at && 
               $this->password_reset_expires_at->isFuture();
    }

    // Accessors & Mutators
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function getOutstandingBalanceAttribute()
    {
        return $this->getAccessibleInvoices()
            ->where('status', '!=', 'PAID')
            ->sum('outstanding_amount');
    }

    public function getTotalPaidAttribute()
    {
        return $this->getAccessibleInvoices()
            ->sum('paid_amount');
    }

    public function getRecentQuotationsAttribute()
    {
        return $this->getAccessibleQuotations()
            ->limit(5)
            ->get();
    }

    public function getRecentInvoicesAttribute()
    {
        return $this->getAccessibleInvoices()
            ->limit(5)
            ->get();
    }

    public function getOverdueInvoicesCountAttribute()
    {
        return $this->getAccessibleInvoices()
            ->where('status', 'OVERDUE')
            ->count();
    }

    public function getQuotationsCountAttribute()
    {
        return $this->getAccessibleQuotations()->count();
    }

    public function getInvoicesCountAttribute()
    {
        return $this->getAccessibleInvoices()->count();
    }

    public function getIsVerifiedAttribute()
    {
        return !is_null($this->email_verified_at);
    }

    public function getStatusDisplayAttribute()
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if (!$this->is_verified) {
            return 'Pending Verification';
        }

        return 'Active';
    }

    public function getStatusColorAttribute()
    {
        if (!$this->is_active) {
            return 'red';
        }

        if (!$this->is_verified) {
            return 'yellow';
        }

        return 'green';
    }

    public function getLastLoginDisplayAttribute()
    {
        if (!$this->last_login_at) {
            return 'Never';
        }

        return $this->last_login_at->diffForHumans();
    }

    // Notification preferences
    public function wantsNotification($type)
    {
        $preferences = $this->notification_preferences ?? [];
        return $preferences[$type] ?? true; // Default to true if not set
    }

    public function updateNotificationPreference($type, $enabled)
    {
        $preferences = $this->notification_preferences ?? [];
        $preferences[$type] = $enabled;
        
        $this->update(['notification_preferences' => $preferences]);
    }

    // Security methods
    public function markEmailAsVerified()
    {
        $this->update(['email_verified_at' => now()]);
    }

    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function grantQuotationAccess($quotationId)
    {
        $accessible = $this->accessible_quotations ?? [];
        if (!in_array($quotationId, $accessible)) {
            $accessible[] = $quotationId;
            $this->update(['accessible_quotations' => $accessible]);
        }
    }

    public function revokeQuotationAccess($quotationId)
    {
        $accessible = $this->accessible_quotations ?? [];
        $accessible = array_filter($accessible, fn($id) => $id != $quotationId);
        $this->update(['accessible_quotations' => array_values($accessible)]);
    }

    public function grantInvoiceAccess($invoiceId)
    {
        $accessible = $this->accessible_invoices ?? [];
        if (!in_array($invoiceId, $accessible)) {
            $accessible[] = $invoiceId;
            $this->update(['accessible_invoices' => $accessible]);
        }
    }

    public function revokeInvoiceAccess($invoiceId)
    {
        $accessible = $this->accessible_invoices ?? [];
        $accessible = array_filter($accessible, fn($id) => $id != $invoiceId);
        $this->update(['accessible_invoices' => array_values($accessible)]);
    }
}