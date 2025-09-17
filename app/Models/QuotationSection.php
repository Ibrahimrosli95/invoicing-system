<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationSection extends Model
{
    protected $fillable = [
        'quotation_id',
        'name',
        'description',
        'notes',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'total',
        'sort_order',
        'is_active',
        'show_in_pdf',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'show_in_pdf' => 'boolean',
    ];

    // Relationships
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    // Business logic
    public function calculateTotal(): void
    {
        // Calculate subtotal from items
        $this->subtotal = $this->items->sum('total_price');

        // Apply discount
        if ($this->discount_percentage > 0) {
            $this->discount_amount = ($this->subtotal * $this->discount_percentage) / 100;
        }

        // Calculate final total
        $this->total = $this->subtotal - $this->discount_amount;
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($section) {
            // Recalculate quotation totals when section changes
            if ($section->quotation) {
                $section->quotation->calculateTotals();
                $section->quotation->saveQuietly();
            }
        });

        static::deleted(function ($section) {
            // Recalculate quotation totals when section is deleted
            if ($section->quotation) {
                $section->quotation->calculateTotals();
                $section->quotation->saveQuietly();
            }
        });
    }
}
