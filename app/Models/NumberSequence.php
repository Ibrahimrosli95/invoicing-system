<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumberSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',
        'prefix',
        'current_number',
        'year',
        'padding',
        'format',
        'yearly_reset',
        'last_generated_at',
        'last_generated_number',
    ];

    protected $casts = [
        'yearly_reset' => 'boolean',
        'last_generated_at' => 'datetime',
        'current_number' => 'integer',
        'year' => 'integer',
        'padding' => 'integer',
    ];

    // Sequence types
    const TYPE_QUOTATION = 'quotation';
    const TYPE_INVOICE = 'invoice';
    const TYPE_PAYMENT = 'payment';
    const TYPE_LEAD = 'lead';
    const TYPE_RECEIPT = 'receipt';
    const TYPE_PURCHASE_ORDER = 'purchase_order';
    const TYPE_DELIVERY_NOTE = 'delivery_note';
    const TYPE_CREDIT_NOTE = 'credit_note';
    const TYPE_ASSESSMENT = 'assessment';

    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_QUOTATION => 'Quotations',
            self::TYPE_INVOICE => 'Invoices',
            self::TYPE_PAYMENT => 'Payments',
            self::TYPE_LEAD => 'Leads',
            self::TYPE_RECEIPT => 'Receipts',
            self::TYPE_PURCHASE_ORDER => 'Purchase Orders',
            self::TYPE_DELIVERY_NOTE => 'Delivery Notes',
            self::TYPE_CREDIT_NOTE => 'Credit Notes',
            self::TYPE_ASSESSMENT => 'Assessments',
        ];
    }

    // Default prefixes for each type
    public static function getDefaultPrefixes(): array
    {
        return [
            self::TYPE_QUOTATION => 'QTN',
            self::TYPE_INVOICE => 'INV',
            self::TYPE_PAYMENT => 'PAY',
            self::TYPE_LEAD => 'LEAD',
            self::TYPE_RECEIPT => 'RCP',
            self::TYPE_PURCHASE_ORDER => 'PO',
            self::TYPE_DELIVERY_NOTE => 'DN',
            self::TYPE_CREDIT_NOTE => 'CN',
        ];
    }

    // Default formats for each type
    public static function getDefaultFormats(): array
    {
        return [
            self::TYPE_QUOTATION => '{prefix}-{year}-{number}',
            self::TYPE_INVOICE => '{prefix}-{year}-{number}',
            self::TYPE_PAYMENT => '{prefix}-{year}-{number}',
            self::TYPE_LEAD => '{prefix}-{year}-{number}',
            self::TYPE_RECEIPT => '{prefix}-{year}-{number}',
            self::TYPE_PURCHASE_ORDER => '{prefix}-{year}-{number}',
            self::TYPE_DELIVERY_NOTE => '{prefix}-{year}-{number}',
            self::TYPE_CREDIT_NOTE => '{prefix}-{year}-{number}',
        ];
    }

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scopes
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        return $query->where('company_id', $companyId);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCurrentYear($query)
    {
        return $query->where('year', now()->year);
    }

    /**
     * Generate the next number for this sequence
     */
    public function generateNext(): string
    {
        $currentYear = now()->year;
        
        // Check if we need to reset for new year
        if ($this->yearly_reset && $this->year !== $currentYear) {
            $this->update([
                'current_number' => 1,
                'year' => $currentYear,
            ]);
        } else {
            $this->increment('current_number');
        }

        $this->update([
            'last_generated_at' => now(),
        ]);

        $number = $this->formatNumber();
        
        $this->update([
            'last_generated_number' => $number,
        ]);

        return $number;
    }

    /**
     * Format the number according to the format template
     */
    public function formatNumber(int $customNumber = null): string
    {
        $number = $customNumber ?? $this->current_number;
        $paddedNumber = str_pad($number, $this->padding, '0', STR_PAD_LEFT);
        
        return str_replace(
            ['{prefix}', '{year}', '{number}'],
            [$this->prefix, $this->year ?: now()->year, $paddedNumber],
            $this->format
        );
    }

    /**
     * Preview what the next number would look like
     */
    public function previewNext(): string
    {
        $nextNumber = $this->current_number + 1;
        $currentYear = now()->year;
        
        // If yearly reset and new year, start from 1
        if ($this->yearly_reset && $this->year !== $currentYear) {
            $nextNumber = 1;
        }

        $paddedNumber = str_pad($nextNumber, $this->padding, '0', STR_PAD_LEFT);
        
        return str_replace(
            ['{prefix}', '{year}', '{number}'],
            [$this->prefix, $currentYear, $paddedNumber],
            $this->format
        );
    }

    /**
     * Reset sequence to a specific number
     */
    public function resetTo(int $number = 0): void
    {
        $this->update([
            'current_number' => $number,
            'year' => now()->year,
            'last_generated_at' => $number > 0 ? now() : null,
            'last_generated_number' => $number > 0 ? $this->formatNumber($number) : null,
        ]);
    }

    /**
     * Get sequence by type for current company
     */
    public static function getForType(string $type, int $companyId = null): self
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        
        return static::firstOrCreate(
            [
                'company_id' => $companyId,
                'type' => $type,
                'year' => now()->year,
            ],
            [
                'prefix' => static::getDefaultPrefixes()[$type] ?? strtoupper($type),
                'current_number' => 0,
                'padding' => 6,
                'format' => static::getDefaultFormats()[$type] ?? '{prefix}-{year}-{number}',
                'yearly_reset' => true,
            ]
        );
    }

    /**
     * Generate next number for a specific type
     */
    public static function nextFor(string $type, int $companyId = null): string
    {
        $sequence = static::getForType($type, $companyId);
        return $sequence->generateNext();
    }

    /**
     * Preview next number for a specific type
     */
    public static function previewFor(string $type, int $companyId = null): string
    {
        $sequence = static::getForType($type, $companyId);
        return $sequence->previewNext();
    }

    /**
     * Get statistics for this sequence
     */
    public function getStatistics(): array
    {
        return [
            'total_generated' => $this->current_number,
            'last_generated' => $this->last_generated_number,
            'last_generated_at' => $this->last_generated_at,
            'next_preview' => $this->previewNext(),
            'current_year' => $this->year,
            'yearly_reset' => $this->yearly_reset,
        ];
    }

    /**
     * Validate format template
     */
    public static function validateFormat(string $format): bool
    {
        // Must contain {number}
        if (!str_contains($format, '{number}')) {
            return false;
        }

        // Valid placeholders
        $validPlaceholders = ['{prefix}', '{year}', '{number}'];
        
        // Extract all placeholders from format
        preg_match_all('/\{[^}]+\}/', $format, $matches);
        
        foreach ($matches[0] as $placeholder) {
            if (!in_array($placeholder, $validPlaceholders)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get available format placeholders
     */
    public static function getFormatPlaceholders(): array
    {
        return [
            '{prefix}' => 'Document prefix (e.g., QTN, INV)',
            '{year}' => 'Current year (e.g., 2025)',
            '{number}' => 'Sequential number (e.g., 000001)',
        ];
    }
}