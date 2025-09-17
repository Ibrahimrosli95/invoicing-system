<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'quotation_section_id',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'total_price',
        'item_code',
        'specifications',
        'notes',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(QuotationSection::class, 'quotation_section_id');
    }

    // Business logic
    public function calculateTotal(): void
    {
        $this->total_price = $this->quantity * $this->unit_price;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            // Always calculate total before saving
            $item->calculateTotal();
        });

        static::saved(function ($item) {
            // Recalculate quotation totals when item changes
            if ($item->quotation) {
                $item->quotation->calculateTotals();
                $item->quotation->saveQuietly();
            }

            // Recalculate section totals if item belongs to a section
            if ($item->section) {
                $item->section->calculateTotal();
                $item->section->saveQuietly();
            }
        });

        static::deleted(function ($item) {
            // Recalculate quotation totals when item is deleted
            if ($item->quotation) {
                $item->quotation->calculateTotals();
                $item->quotation->saveQuietly();
            }

            // Recalculate section totals if item belonged to a section
            if ($item->section) {
                $item->section->calculateTotal();
                $item->section->saveQuietly();
            }
        });
    }
}
