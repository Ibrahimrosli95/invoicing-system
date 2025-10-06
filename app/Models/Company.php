<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'tagline',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'tax_number',
        'registration_number',
        'website',
        'logo',
        'logo_path',
        'primary_color',
        'secondary_color',
        'timezone',
        'currency',
        'date_format',
        'time_format',
        'number_format',
        'settings',
        'invoice_settings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'json',
            'invoice_settings' => 'json',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the users for the company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the teams for the company.
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Get the leads for the company.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Get the brands for the company.
     */
    public function brands(): HasMany
    {
        return $this->hasMany(CompanyBrand::class);
    }

    /**
     * Get the default brand for the company.
     */
    public function defaultBrand()
    {
        return $this->hasOne(CompanyBrand::class)->where('is_default', true);
    }

    /**
     * Get the active brands for the company.
     */
    public function activeBrands(): HasMany
    {
        return $this->hasMany(CompanyBrand::class)->where('is_active', true);
    }

    /**
     * Check if company is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get the company's primary color or default.
     */
    public function getPrimaryColor(): string
    {
        return $this->primary_color ?? '#2563EB';
    }

    /**
     * Get the company's secondary color or default.
     */
    public function getSecondaryColor(): string
    {
        return $this->secondary_color ?? '#10B981';
    }

    /**
     * Get all email delivery logs for this company.
     */
    public function emailDeliveryLogs(): HasMany
    {
        return $this->hasMany(EmailDeliveryLog::class);
    }

    /**
     * Get all customer segments for this company.
     */
    public function customerSegments(): HasMany
    {
        return $this->hasMany(\App\Models\CustomerSegment::class);
    }

    /**
     * Get all logos in the logo bank for this company.
     */
    public function logos(): HasMany
    {
        return $this->hasMany(\App\Models\CompanyLogo::class);
    }

    /**
     * Get the default logo for this company.
     */
    public function defaultLogo()
    {
        return $this->logos()->where('is_default', true)->first();
    }
}
