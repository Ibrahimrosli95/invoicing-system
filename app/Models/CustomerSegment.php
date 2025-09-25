<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerSegment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'default_discount_percentage',
        'is_active',
        'sort_order',
        'settings',
        'color',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'default_discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically set company_id and created_by
        static::creating(function ($segment) {
            if (!$segment->company_id) {
                $segment->company_id = auth()->user()->company_id;
            }
            if (!$segment->created_by) {
                $segment->created_by = auth()->id();
            }
        });

        // Update updated_by when segment is modified
        static::updating(function ($segment) {
            $segment->updated_by = auth()->id();
        });
    }

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function pricingTiers(): HasMany
    {
        return $this->hasMany(PricingTier::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Get the customers assigned to this segment.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Scopes for multi-tenancy and filtering
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?: (auth()->check() ? auth()->user()->company_id : null);
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Business logic methods
     */
    public function hasDefaultDiscount(): bool
    {
        return $this->default_discount_percentage > 0;
    }

    public function calculateDiscountedPrice(float $basePrice): float
    {
        if (!$this->hasDefaultDiscount()) {
            return $basePrice;
        }

        $discount = ($this->default_discount_percentage / 100) * $basePrice;
        return max(0, $basePrice - $discount);
    }

    public function getDiscountAmount(float $basePrice): float
    {
        if (!$this->hasDefaultDiscount()) {
            return 0;
        }

        return ($this->default_discount_percentage / 100) * $basePrice;
    }

    /**
     * Get all pricing tiers for items in this segment
     */
    public function getPricingTiersForItem($pricingItemId): \Illuminate\Support\Collection
    {
        return $this->pricingTiers()
            ->where('pricing_item_id', $pricingItemId)
            ->where('is_active', true)
            ->orderBy('min_quantity')
            ->get();
    }

    /**
     * Get the appropriate pricing tier for a specific quantity
     */
    public function getTierForQuantity($pricingItemId, int $quantity): ?PricingTier
    {
        return $this->pricingTiers()
            ->where('pricing_item_id', $pricingItemId)
            ->where('is_active', true)
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($query) use ($quantity) {
                $query->whereNull('max_quantity')
                      ->orWhere('max_quantity', '>=', $quantity);
            })
            ->orderBy('min_quantity', 'desc')
            ->first();
    }

    /**
     * Calculate price for a specific item and quantity
     */
    public function calculatePriceForItem($pricingItem, int $quantity): array
    {
        // First check if there's a specific tier for this segment and quantity
        $tier = $this->getTierForQuantity($pricingItem->id, $quantity);
        
        if ($tier) {
            return [
                'unit_price' => $tier->unit_price,
                'total_price' => $tier->unit_price * $quantity,
                'discount_applied' => $tier->discount_percentage ?? 0,
                'pricing_method' => 'tier',
                'tier_id' => $tier->id,
                'tier_range' => $tier->min_quantity . '-' . ($tier->max_quantity ?? 'âˆž'),
            ];
        }

        // Fallback to segment default discount on base price
        $basePrice = is_object($pricingItem) ? $pricingItem->unit_price : $pricingItem['unit_price'];
        $unitPrice = $this->calculateDiscountedPrice($basePrice);
        
        return [
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
            'discount_applied' => $this->default_discount_percentage,
            'pricing_method' => 'segment_discount',
            'tier_id' => null,
            'tier_range' => null,
        ];
    }

    /**
     * Get segment statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_tiers' => $this->pricingTiers()->count(),
            'active_tiers' => $this->pricingTiers()->where('is_active', true)->count(),
            'items_with_tiers' => $this->pricingTiers()
                ->distinct('pricing_item_id')
                ->count('pricing_item_id'),
            'default_discount' => $this->default_discount_percentage,
            'color' => $this->color,
        ];
    }

    /**
     * Duplicate segment to another company (for superadmin use)
     */
    public function duplicateToCompany($targetCompanyId): self
    {
        $newSegment = $this->replicate();
        $newSegment->company_id = $targetCompanyId;
        $newSegment->created_by = auth()->id();
        $newSegment->updated_by = null;
        $newSegment->save();

        // Also duplicate all pricing tiers if needed
        $this->pricingTiers->each(function ($tier) use ($newSegment) {
            // This would require mapping pricing items between companies
            // Implementation depends on business requirements
        });

        return $newSegment;
    }

    /**
     * Validate segment settings
     */
    public function validateSettings(): array
    {
        $issues = [];

        if (empty($this->name)) {
            $issues[] = 'Segment name is required';
        }

        if ($this->default_discount_percentage < 0 || $this->default_discount_percentage > 100) {
            $issues[] = 'Default discount percentage must be between 0 and 100';
        }

        // Check for duplicate names within company
        $duplicate = static::forCompany($this->company_id)
            ->where('name', $this->name)
            ->where('id', '!=', $this->id)
            ->exists();

        if ($duplicate) {
            $issues[] = 'A segment with this name already exists';
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Get default segments for seeding
     */
    public static function getDefaultSegments(): array
    {
        return [
            [
                'name' => 'End User',
                'description' => 'Retail customers and individual buyers',
                'default_discount_percentage' => 0,
                'sort_order' => 1,
                'color' => '#10B981', // Green
                'settings' => [
                    'require_payment_terms' => false,
                    'default_payment_terms' => 'immediate',
                    'credit_limit' => null,
                ],
            ],
            [
                'name' => 'Contractor',
                'description' => 'Construction contractors and project-based buyers',
                'default_discount_percentage' => 5,
                'sort_order' => 2,
                'color' => '#3B82F6', // Blue
                'settings' => [
                    'require_payment_terms' => true,
                    'default_payment_terms' => '30_days',
                    'credit_limit' => 50000,
                ],
            ],
            [
                'name' => 'Dealer',
                'description' => 'Authorized dealers and resellers',
                'default_discount_percentage' => 15,
                'sort_order' => 3,
                'color' => '#8B5CF6', // Purple
                'settings' => [
                    'require_payment_terms' => true,
                    'default_payment_terms' => '30_days',
                    'credit_limit' => 100000,
                ],
            ],
        ];
    }

    /**
     * Export segment to array format
     */
    public function toExportArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'default_discount_percentage' => $this->default_discount_percentage,
            'is_active' => $this->is_active ? 'Yes' : 'No',
            'sort_order' => $this->sort_order,
            'total_tiers' => $this->pricingTiers()->count(),
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}



