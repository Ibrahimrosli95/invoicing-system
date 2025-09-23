<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;\r\nuse Illuminate\\Database\\Eloquent\\Relations\\MorphTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'quotation_item_id',
        'description',
        'specifications',
        'unit',
        'quantity',
        'unit_price',
        'total_price',
        'sort_order',
        'is_locked',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'sort_order' => 'integer',\r\n        'source_id' => 'integer',
        'is_locked' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            // Calculate total price
            $item->total_price = $item->quantity * $item->unit_price;
        });

        static::saved(function ($item) {
            // Update invoice totals when item is saved
            if ($item->invoice) {
                $item->invoice->calculateTotals();
            }
        });

        static::deleted(function ($item) {
            // Update invoice totals when item is deleted
            if ($item->invoice) {
                $item->invoice->calculateTotals();
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

    public function quotationItem(): BelongsTo
    {
        return $this->belongsTo(QuotationItem::class);
    }

    /**
     * Business logic
     */
    public function canBeEdited(): bool
    {
        return !$this->is_locked && $this->invoice && $this->invoice->canBeEdited();
    }

    public function lock(): void
    {
        $this->update(['is_locked' => true]);
    }

    public function unlock(): void
    {
        $this->update(['is_locked' => false]);
    }
}







