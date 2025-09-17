<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingTier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pricing_item_id',
        'customer_segment_id',
        'min_quantity',
        'max_quantity',
        'unit_price',
        'discount_percentage',
        'is_active',
        'sort_order',
        'settings',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically set created_by
        static::creating(function ($tier) {
            if (!$tier->created_by) {
                $tier->created_by = auth()->id();
            }
        });

        // Update updated_by when tier is modified
        static::updating(function ($tier) {
            $tier->updated_by = auth()->id();
        });
    }

    /**
     * Relationships
     */
    public function pricingItem(): BelongsTo
    {
        return $this->belongsTo(PricingItem::class);
    }

    public function customerSegment(): BelongsTo
    {
        return $this->belongsTo(CustomerSegment::class);
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
     * Scopes for filtering
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?: (auth()->check() ? auth()->user()->company_id : null);
        return $query->whereHas('pricingItem', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForItem($query, $pricingItemId)
    {
        return $query->where('pricing_item_id', $pricingItemId);
    }

    public function scopeForSegment($query, $customerSegmentId)
    {
        return $query->where('customer_segment_id', $customerSegmentId);
    }

    public function scopeForQuantity($query, int $quantity)
    {
        return $query->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')
                  ->orWhere('max_quantity', '>=', $quantity);
            });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('min_quantity')->orderBy('sort_order');
    }

    /**
     * Business logic methods
     */
    public function isUnlimitedQuantity(): bool
    {
        return is_null($this->max_quantity);
    }

    public function getQuantityRange(): string
    {
        if ($this->isUnlimitedQuantity()) {
            return $this->min_quantity . '+';
        }

        if ($this->min_quantity == $this->max_quantity) {
            return (string) $this->min_quantity;
        }

        return $this->min_quantity . '-' . $this->max_quantity;
    }

    public function appliesToQuantity(int $quantity): bool
    {
        if ($quantity < $this->min_quantity) {
            return false;
        }

        if (!$this->isUnlimitedQuantity() && $quantity > $this->max_quantity) {
            return false;
        }

        return true;
    }

    public function calculateTotalPrice(int $quantity): array
    {
        if (!$this->appliesToQuantity($quantity)) {
            return [
                'error' => 'Quantity does not fall within this tier range',
                'tier_range' => $this->getQuantityRange(),
                'quantity' => $quantity,
            ];
        }

        $totalPrice = $this->unit_price * $quantity;
        $basePrice = $this->pricingItem->unit_price * $quantity;
        $savings = max(0, $basePrice - $totalPrice);

        return [
            'unit_price' => $this->unit_price,
            'total_price' => $totalPrice,
            'base_total' => $basePrice,
            'savings' => $savings,
            'discount_applied' => $this->discount_percentage ?? 0,
            'tier_range' => $this->getQuantityRange(),
            'quantity' => $quantity,
        ];
    }

    /**
     * Validate tier against business rules
     */
    public function validateTier(): array
    {
        $issues = [];

        // Check quantity range validity
        if ($this->min_quantity < 1) {
            $issues[] = 'Minimum quantity must be at least 1';
        }

        if (!$this->isUnlimitedQuantity() && $this->max_quantity < $this->min_quantity) {
            $issues[] = 'Maximum quantity cannot be less than minimum quantity';
        }

        // Check price validity
        if ($this->unit_price <= 0) {
            $issues[] = 'Unit price must be greater than zero';
        }

        // Check for overlapping tiers within the same item/segment
        $overlapping = static::where('pricing_item_id', $this->pricing_item_id)
            ->where('customer_segment_id', $this->customer_segment_id)
            ->where('id', '!=', $this->id)
            ->where('is_active', true)
            ->where(function ($query) {
                // Check for overlaps
                $query->where(function ($q) {
                    // New tier min falls within existing range
                    $q->where('min_quantity', '<=', $this->min_quantity)
                      ->where(function ($subQ) {
                          $subQ->whereNull('max_quantity')
                               ->orWhere('max_quantity', '>=', $this->min_quantity);
                      });
                })->orWhere(function ($q) {
                    // New tier max falls within existing range (if not unlimited)
                    if (!$this->isUnlimitedQuantity()) {
                        $q->where('min_quantity', '<=', $this->max_quantity)
                          ->where(function ($subQ) {
                              $subQ->whereNull('max_quantity')
                                   ->orWhere('max_quantity', '>=', $this->max_quantity);
                          });
                    }
                });
            })
            ->exists();

        if ($overlapping) {
            $issues[] = 'This tier overlaps with an existing tier for the same item and segment';
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Check if tier needs price adjustment based on cost
     */
    public function needsPriceAdjustment(): array
    {
        $item = $this->pricingItem;
        $suggestions = [];

        if ($item->hasCostPrice()) {
            $margin = (($this->unit_price - $item->cost_price) / $this->unit_price) * 100;
            
            if ($margin < 10) {
                $suggestions[] = [
                    'type' => 'warning',
                    'message' => 'Margin is very low (' . round($margin, 1) . '%)',
                    'suggested_price' => $item->getSuggestedPrice(20),
                ];
            }

            if ($this->unit_price <= $item->cost_price) {
                $suggestions[] = [
                    'type' => 'error',
                    'message' => 'Selling price is below cost price',
                    'suggested_price' => $item->getSuggestedPrice(15),
                ];
            }
        }

        if ($item->hasMinimumPrice() && $this->unit_price < $item->minimum_price) {
            $suggestions[] = [
                'type' => 'error',
                'message' => 'Price is below minimum allowed price',
                'suggested_price' => $item->minimum_price,
            ];
        }

        return [
            'needs_adjustment' => !empty($suggestions),
            'suggestions' => $suggestions,
        ];
    }

    /**
     * Generate suggested tiers for an item/segment combination
     */
    public static function generateSuggestedTiers($pricingItem, $customerSegment): array
    {
        $basePrice = $pricingItem->unit_price;
        $segmentDiscount = $customerSegment->default_discount_percentage;
        
        // Calculate base discounted price
        $discountedPrice = $basePrice * (1 - $segmentDiscount / 100);

        // Generate suggested tier structure
        return [
            [
                'min_quantity' => 1,
                'max_quantity' => 9,
                'unit_price' => $discountedPrice,
                'discount_percentage' => $segmentDiscount,
                'description' => 'Small quantity - base segment price',
            ],
            [
                'min_quantity' => 10,
                'max_quantity' => 49,
                'unit_price' => $discountedPrice * 0.95, // Additional 5% discount
                'discount_percentage' => $segmentDiscount + 5,
                'description' => 'Medium quantity - 5% additional discount',
            ],
            [
                'min_quantity' => 50,
                'max_quantity' => 99,
                'unit_price' => $discountedPrice * 0.90, // Additional 10% discount
                'discount_percentage' => $segmentDiscount + 10,
                'description' => 'Large quantity - 10% additional discount',
            ],
            [
                'min_quantity' => 100,
                'max_quantity' => null, // Unlimited
                'unit_price' => $discountedPrice * 0.85, // Additional 15% discount
                'discount_percentage' => $segmentDiscount + 15,
                'description' => 'Bulk quantity - 15% additional discount',
            ],
        ];
    }

    /**
     * Get tier performance analytics
     */
    public function getPerformanceAnalytics(): array
    {
        // This would require usage tracking in quotations/invoices
        // For now, return basic structure
        return [
            'usage_count' => 0, // Would be calculated from quotation_items
            'total_revenue' => 0, // Would be calculated from invoices
            'average_quantity' => 0,
            'conversion_rate' => 0,
            'last_used_at' => null,
        ];
    }

    /**
     * Export tier to array format
     */
    public function toExportArray(): array
    {
        return [
            'item_name' => $this->pricingItem->name,
            'item_code' => $this->pricingItem->item_code,
            'segment' => $this->customerSegment->name,
            'quantity_range' => $this->getQuantityRange(),
            'min_quantity' => $this->min_quantity,
            'max_quantity' => $this->max_quantity,
            'unit_price' => $this->unit_price,
            'discount_percentage' => $this->discount_percentage,
            'is_active' => $this->is_active ? 'Yes' : 'No',
            'notes' => $this->notes,
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }

    /**
     * Clone tier to another item
     */
    public function cloneToItem($targetItemId): self
    {
        $newTier = $this->replicate();
        $newTier->pricing_item_id = $targetItemId;
        $newTier->created_by = auth()->id();
        $newTier->updated_by = null;
        $newTier->save();

        return $newTier;
    }

    /**
     * Get the next logical tier for this item/segment
     */
    public function getNextTierSuggestion(): ?array
    {
        $nextMinQuantity = $this->isUnlimitedQuantity() ? null : $this->max_quantity + 1;
        
        if (!$nextMinQuantity) {
            return null; // Already at unlimited tier
        }

        return [
            'min_quantity' => $nextMinQuantity,
            'max_quantity' => $nextMinQuantity * 5, // Suggest 5x range
            'unit_price' => $this->unit_price * 0.95, // 5% additional discount
            'discount_percentage' => ($this->discount_percentage ?? 0) + 5,
        ];
    }
}
