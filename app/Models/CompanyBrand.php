<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CompanyBrand extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'legal_name',
        'registration_number',
        'logo_path',
        'address',
        'city',
        'state',
        'postal_code',
        'phone',
        'email',
        'website',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'is_default',
        'is_active',
        'tagline',
        'color_primary',
        'color_secondary',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // When creating a new brand, if set as default, unset others
        static::creating(function ($brand) {
            if ($brand->is_default) {
                static::where('company_id', $brand->company_id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });

        // When updating, if setting as default, unset others
        static::updating(function ($brand) {
            if ($brand->is_default && $brand->isDirty('is_default')) {
                static::where('company_id', $brand->company_id)
                    ->where('id', '!=', $brand->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });

        // Delete logo file when brand is deleted
        static::deleting(function ($brand) {
            if ($brand->logo_path) {
                Storage::disk('public')->delete($brand->logo_path);
            }
        });
    }

    // ========================================
    // Relationships
    // ========================================

    /**
     * Brand belongs to a company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Brand has many quotations.
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Brand has many invoices.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // ========================================
    // Scopes
    // ========================================

    /**
     * Scope to get only active brands.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get brands for a specific company.
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?: (auth()->check() ? auth()->user()->company_id : null);
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get default brand.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // ========================================
    // Methods
    // ========================================

    /**
     * Set this brand as the default for the company.
     */
    public function setAsDefault(): void
    {
        // Unset other defaults
        static::where('company_id', $this->company_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }

    /**
     * Get the full URL for the logo.
     */
    public function getLogoUrl(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

    /**
     * Check if brand has its own bank details.
     */
    public function hasOwnBankDetails(): bool
    {
        return !empty($this->bank_name)
            && !empty($this->bank_account_name)
            && !empty($this->bank_account_number);
    }

    /**
     * Get bank details (own or from company).
     */
    public function getBankDetails(): array
    {
        if ($this->hasOwnBankDetails()) {
            return [
                'bank_name' => $this->bank_name,
                'account_name' => $this->bank_account_name,
                'account_number' => $this->bank_account_number,
            ];
        }

        // Fallback to company bank details if available
        if ($this->company) {
            return [
                'bank_name' => $this->company->bank_name ?? null,
                'account_name' => $this->company->bank_account_name ?? null,
                'account_number' => $this->company->bank_account_number ?? null,
            ];
        }

        return [];
    }

    /**
     * Get brand colors or default.
     */
    public function getBrandColors(): array
    {
        return [
            'primary' => $this->color_primary ?? '#2563EB',
            'secondary' => $this->color_secondary ?? '#1E40AF',
        ];
    }

    /**
     * Check if brand is being used in any documents.
     */
    public function isUsedInDocuments(): bool
    {
        return $this->quotations()->exists() || $this->invoices()->exists();
    }

    /**
     * Get count of documents using this brand.
     */
    public function getDocumentCount(): int
    {
        return $this->quotations()->count() + $this->invoices()->count();
    }
}
