<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class PricingItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'pricing_category_id',
        'name',
        'description',
        'item_code',
        'unit',
        'unit_price',
        'cost_price',
        'minimum_price',
        'specifications',
        'tags',
        'image_path',
        'is_active',
        'is_featured',
        'sort_order',
        'settings',
        'stock_quantity',
        'track_stock',
        'markup_percentage',
        'last_price_update',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'minimum_price' => 'decimal:2',
        'tags' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
        'stock_quantity' => 'integer',
        'track_stock' => 'boolean',
        'markup_percentage' => 'decimal:2',
        'last_price_update' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically set company_id and created_by
        static::creating(function ($item) {
            if (!$item->company_id) {
                $item->company_id = auth()->user()->company_id;
            }
            if (!$item->created_by) {
                $item->created_by = auth()->id();
            }
            
            // Calculate markup percentage if cost price is provided
            if ($item->cost_price && $item->unit_price) {
                $item->markup_percentage = (($item->unit_price - $item->cost_price) / $item->cost_price) * 100;
            }
        });

        // Update updated_by and recalculate markup when item is modified
        static::updating(function ($item) {
            $item->updated_by = auth()->id();
            
            // Update last_price_update if unit_price changed
            if ($item->isDirty('unit_price')) {
                $item->last_price_update = now();
            }
            
            // Recalculate markup percentage if prices changed
            if ($item->isDirty(['unit_price', 'cost_price']) && $item->cost_price && $item->unit_price) {
                $item->markup_percentage = (($item->unit_price - $item->cost_price) / $item->cost_price) * 100;
            }
        });
    }

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PricingCategory::class, 'pricing_category_id');
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

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('pricing_category_id', $categoryId);
    }

    public function scopeByItemCode($query, $itemCode)
    {
        return $query->where('item_code', $itemCode);
    }

    public function scopeWithTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeByPriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where('unit_price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('unit_price', '<=', $maxPrice);
        }
        return $query;
    }

    /**
     * Business logic methods
     */
    public function hasImage(): bool
    {
        return !empty($this->image_path) && Storage::exists($this->image_path);
    }

    public function getImageUrl(): ?string
    {
        return $this->hasImage() ? Storage::url($this->image_path) : null;
    }

    public function hasCostPrice(): bool
    {
        return !is_null($this->cost_price) && $this->cost_price > 0;
    }

    public function hasMinimumPrice(): bool
    {
        return !is_null($this->minimum_price) && $this->minimum_price > 0;
    }

    public function isInStock(): bool
    {
        if (!$this->track_stock) {
            return true; // Items not tracking stock are always "in stock"
        }
        
        return $this->stock_quantity > 0;
    }

    public function isLowStock($threshold = 10): bool
    {
        if (!$this->track_stock) {
            return false;
        }
        
        return $this->stock_quantity <= $threshold;
    }

    /**
     * Calculate margin based on cost price
     */
    public function calculateMargin(float $sellingPrice = null, float $quantity = 1): array
    {
        $sellingPrice = $sellingPrice ?? $this->unit_price;
        
        if (!$this->hasCostPrice()) {
            return [
                'cost_total' => 0,
                'revenue_total' => $sellingPrice * $quantity,
                'margin_total' => $sellingPrice * $quantity,
                'margin_percentage' => 100,
                'has_cost_data' => false,
            ];
        }

        $costTotal = $this->cost_price * $quantity;
        $revenueTotal = $sellingPrice * $quantity;
        $marginTotal = $revenueTotal - $costTotal;
        $marginPercentage = $revenueTotal > 0 ? ($marginTotal / $revenueTotal) * 100 : 0;

        return [
            'cost_total' => $costTotal,
            'revenue_total' => $revenueTotal,
            'margin_total' => $marginTotal,
            'margin_percentage' => round($marginPercentage, 2),
            'has_cost_data' => true,
        ];
    }

    /**
     * Calculate suggested price based on cost and target margin
     */
    public function getSuggestedPrice(float $targetMarginPercentage = 30): float
    {
        if (!$this->hasCostPrice()) {
            return $this->unit_price;
        }

        $suggestedPrice = $this->cost_price / (1 - ($targetMarginPercentage / 100));

        // Ensure it meets minimum price requirements
        if ($this->hasMinimumPrice() && $suggestedPrice < $this->minimum_price) {
            return $this->minimum_price;
        }

        return round($suggestedPrice, 2);
    }

    /**
     * Validate selling price against business rules
     */
    public function validateSellingPrice(float $proposedPrice): array
    {
        $issues = [];
        
        if ($proposedPrice <= 0) {
            $issues[] = 'Selling price must be greater than zero';
        }

        if ($this->hasMinimumPrice() && $proposedPrice < $this->minimum_price) {
            $issues[] = "Selling price cannot be below minimum price of RM {$this->minimum_price}";
        }

        if ($this->hasCostPrice() && $proposedPrice <= $this->cost_price) {
            $issues[] = "Selling price should be above cost price of RM {$this->cost_price}";
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'proposed_price' => $proposedPrice,
            'minimum_price' => $this->minimum_price,
            'cost_price' => $this->cost_price,
        ];
    }

    /**
     * Update stock quantity (if tracking stock)
     */
    public function adjustStock(int $adjustment, string $reason = null): bool
    {
        if (!$this->track_stock) {
            return false;
        }

        $newQuantity = $this->stock_quantity + $adjustment;
        
        if ($newQuantity < 0) {
            return false; // Cannot have negative stock
        }

        $this->stock_quantity = $newQuantity;
        return $this->save();
    }

    /**
     * Get category breadcrumb path
     */
    public function getCategoryPath(): string
    {
        return $this->category ? $this->category->getFullPath() : '';
    }

    /**
     * Get pricing analytics
     */
    public function getPricingAnalytics(): array
    {
        $margin = $this->calculateMargin();
        
        return [
            'current_price' => $this->unit_price,
            'cost_price' => $this->cost_price,
            'minimum_price' => $this->minimum_price,
            'markup_percentage' => $this->markup_percentage,
            'margin_data' => $margin,
            'suggested_prices' => [
                '20_percent_margin' => $this->getSuggestedPrice(20),
                '30_percent_margin' => $this->getSuggestedPrice(30),
                '40_percent_margin' => $this->getSuggestedPrice(40),
            ],
            'last_price_update' => $this->last_price_update,
        ];
    }

    /**
     * Convert to quotation item format
     */
    public function toQuotationItemData(float $quantity = 1, array $customizations = []): array
    {
        return [
            'description' => $customizations['description'] ?? $this->name,
            'unit' => $customizations['unit'] ?? $this->unit,
            'quantity' => $customizations['quantity'] ?? $quantity,
            'unit_price' => $customizations['unit_price'] ?? $this->unit_price,
            'item_code' => $this->item_code,
            'specifications' => $customizations['specifications'] ?? $this->specifications,
            'notes' => $customizations['notes'] ?? '',
            'pricing_item_id' => $this->id,
            'is_from_pricing' => true,
            'category_name' => $this->category->name ?? '',
            'image_url' => $this->getImageUrl(),
        ];
    }

    /**
     * Search items by various criteria
     */
    public static function search($term, $companyId = null): \Illuminate\Support\Collection
    {
        $companyId = $companyId ?: auth()->user()->company_id;
        
        return static::forCompany($companyId)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                      ->orWhere('item_code', 'like', "%{$term}%")
                      ->orWhere('description', 'like', "%{$term}%")
                      ->orWhere('specifications', 'like', "%{$term}%");
            })
            ->active()
            ->with(['category'])
            ->ordered()
            ->get();
    }

    /**
     * Get items by tag
     */
    public static function getByTag($tag, $companyId = null): \Illuminate\Support\Collection
    {
        $companyId = $companyId ?: auth()->user()->company_id;
        
        return static::forCompany($companyId)
            ->withTag($tag)
            ->active()
            ->with(['category'])
            ->ordered()
            ->get();
    }

    /**
     * Get featured items
     */
    public static function getFeatured($companyId = null, $limit = 10): \Illuminate\Support\Collection
    {
        $companyId = $companyId ?: auth()->user()->company_id;
        
        return static::forCompany($companyId)
            ->featured()
            ->active()
            ->with(['category'])
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * Duplicate item to another category
     */
    public function duplicate($newCategoryId = null, $newName = null): self
    {
        $newItem = $this->replicate();
        $newItem->name = $newName ?: $this->name . ' (Copy)';
        $newItem->item_code = null; // Reset item code to avoid duplicates
        $newItem->created_by = auth()->id();
        $newItem->updated_by = null;
        
        if ($newCategoryId) {
            $newItem->pricing_category_id = $newCategoryId;
        }
        
        $newItem->save();
        
        return $newItem;
    }

    /**
     * Get items that need price review
     */
    public static function needsPriceReview($companyId = null): \Illuminate\Support\Collection
    {
        $companyId = $companyId ?: auth()->user()->company_id;
        $thirtyDaysAgo = now()->subDays(30);
        
        return static::forCompany($companyId)
            ->active()
            ->where(function ($query) use ($thirtyDaysAgo) {
                $query->whereNull('last_price_update')
                      ->orWhere('last_price_update', '<', $thirtyDaysAgo);
            })
            ->with(['category'])
            ->ordered()
            ->get();
    }

    /**
     * Get popular items (this would require usage tracking)
     */
    public static function getPopular($companyId = null, $limit = 10): \Illuminate\Support\Collection
    {
        $companyId = $companyId ?: auth()->user()->company_id;
        
        // For now, return featured items as popular
        // In a full implementation, this would track usage in quotations/invoices
        return static::getFeatured($companyId, $limit);
    }

    /**
     * Tier Pricing Methods
     */
    
    /**
     * Get price for a specific customer segment and quantity
     */
    public function getPriceForSegment($segmentId, int $quantity = 1): array
    {
        // Find the appropriate tier for this segment and quantity
        $tier = $this->pricingTiers()
            ->where('customer_segment_id', $segmentId)
            ->where('is_active', true)
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($query) use ($quantity) {
                $query->whereNull('max_quantity')
                      ->orWhere('max_quantity', '>=', $quantity);
            })
            ->orderBy('min_quantity', 'desc')
            ->first();

        if ($tier) {
            return [
                'unit_price' => $tier->unit_price,
                'total_price' => $tier->unit_price * $quantity,
                'discount_applied' => $tier->discount_percentage ?? 0,
                'pricing_method' => 'tier',
                'tier_id' => $tier->id,
                'tier_range' => $tier->getQuantityRange(),
                'savings' => max(0, ($this->unit_price - $tier->unit_price) * $quantity),
            ];
        }

        // Fallback to segment default discount if no tier found
        $segment = CustomerSegment::find($segmentId);
        if ($segment) {
            $unitPrice = $segment->calculateDiscountedPrice($this->unit_price);
            return [
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $quantity,
                'discount_applied' => $segment->default_discount_percentage,
                'pricing_method' => 'segment_discount',
                'tier_id' => null,
                'tier_range' => null,
                'savings' => ($this->unit_price - $unitPrice) * $quantity,
            ];
        }

        // Final fallback to base price
        return [
            'unit_price' => $this->unit_price,
            'total_price' => $this->unit_price * $quantity,
            'discount_applied' => 0,
            'pricing_method' => 'base_price',
            'tier_id' => null,
            'tier_range' => null,
            'savings' => 0,
        ];
    }

    /**
     * Get all available pricing tiers for this item
     */
    public function getTiersBySegment(): \Illuminate\Support\Collection
    {
        return $this->pricingTiers()
            ->with(['customerSegment'])
            ->where('is_active', true)
            ->orderBy('customer_segment_id')
            ->orderBy('min_quantity')
            ->get()
            ->groupBy('customer_segment_id');
    }

    /**
     * Check if item has tier pricing
     */
    public function hasTierPricing(): bool
    {
        return $this->pricingTiers()->where('is_active', true)->exists();
    }

    /**
     * Get all segments that have tiers for this item
     */
    public function getSegmentsWithTiers(): \Illuminate\Support\Collection
    {
        return CustomerSegment::whereIn('id', 
            $this->pricingTiers()
                ->select('customer_segment_id')
                ->distinct()
                ->where('is_active', true)
                ->pluck('customer_segment_id')
        )->get();
    }

    /**
     * Calculate tier discount for a specific segment and quantity
     */
    public function calculateTierDiscount($segmentId, int $quantity): array
    {
        $pricing = $this->getPriceForSegment($segmentId, $quantity);
        $baseTotalPrice = $this->unit_price * $quantity;
        
        return [
            'base_unit_price' => $this->unit_price,
            'base_total_price' => $baseTotalPrice,
            'tier_unit_price' => $pricing['unit_price'],
            'tier_total_price' => $pricing['total_price'],
            'discount_amount' => $baseTotalPrice - $pricing['total_price'],
            'discount_percentage' => $baseTotalPrice > 0 ? (($baseTotalPrice - $pricing['total_price']) / $baseTotalPrice) * 100 : 0,
            'pricing_method' => $pricing['pricing_method'],
            'tier_range' => $pricing['tier_range'],
        ];
    }

    /**
     * Get pricing analytics across all segments
     */
    public function getTierPricingAnalytics(): array
    {
        $segments = CustomerSegment::forCompany()->active()->get();
        $analytics = [];

        foreach ($segments as $segment) {
            $tiers = $this->pricingTiers()
                ->where('customer_segment_id', $segment->id)
                ->where('is_active', true)
                ->orderBy('min_quantity')
                ->get();

            $analytics[$segment->name] = [
                'segment_id' => $segment->id,
                'segment_color' => $segment->color,
                'default_discount' => $segment->default_discount_percentage,
                'tier_count' => $tiers->count(),
                'tiers' => $tiers->map(function ($tier) {
                    return [
                        'id' => $tier->id,
                        'range' => $tier->getQuantityRange(),
                        'price' => $tier->unit_price,
                        'discount' => $tier->discount_percentage ?? 0,
                        'savings_per_unit' => max(0, $this->unit_price - $tier->unit_price),
                    ];
                }),
                'price_for_qty_1' => $this->getPriceForSegment($segment->id, 1),
                'price_for_qty_10' => $this->getPriceForSegment($segment->id, 10),
                'price_for_qty_100' => $this->getPriceForSegment($segment->id, 100),
            ];
        }

        return $analytics;
    }

    /**
     * Validate tier pricing setup
     */
    public function validateTierPricing(): array
    {
        $issues = [];
        
        $tiersBySegment = $this->getTiersBySegment();
        
        foreach ($tiersBySegment as $segmentId => $tiers) {
            $segment = CustomerSegment::find($segmentId);
            $segmentName = $segment ? $segment->name : "Segment #{$segmentId}";
            
            // Check for gaps in quantity ranges
            $sortedTiers = $tiers->sortBy('min_quantity');
            $previousMax = 0;
            
            foreach ($sortedTiers as $tier) {
                if ($tier->min_quantity > $previousMax + 1) {
                    $issues[] = "{$segmentName}: Gap in quantity range between {$previousMax} and {$tier->min_quantity}";
                }
                $previousMax = $tier->max_quantity ?? PHP_INT_MAX;
            }
            
            // Check for price consistency (should generally decrease with higher quantities)
            $prevPrice = PHP_INT_MAX;
            foreach ($sortedTiers as $tier) {
                if ($tier->unit_price > $prevPrice) {
                    $issues[] = "{$segmentName}: Price increases at quantity {$tier->min_quantity} - this may confuse customers";
                }
                $prevPrice = $tier->unit_price;
            }
        }
        
        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'segments_count' => $tiersBySegment->count(),
            'total_tiers' => $this->pricingTiers()->where('is_active', true)->count(),
        ];
    }

    /**
     * Convert to quotation item with segment pricing applied
     */
    public function toQuotationItemWithSegmentPricing($segmentId, float $quantity = 1, array $customizations = []): array
    {
        $pricing = $this->getPriceForSegment($segmentId, $quantity);
        
        return [
            'description' => $customizations['description'] ?? $this->name,
            'unit' => $customizations['unit'] ?? $this->unit,
            'quantity' => $customizations['quantity'] ?? $quantity,
            'unit_price' => $customizations['unit_price'] ?? $pricing['unit_price'],
            'item_code' => $this->item_code,
            'specifications' => $customizations['specifications'] ?? $this->specifications,
            'notes' => $customizations['notes'] ?? '',
            'pricing_item_id' => $this->id,
            'customer_segment_id' => $segmentId,
            'tier_id' => $pricing['tier_id'],
            'is_from_pricing' => true,
            'pricing_method' => $pricing['pricing_method'],
            'tier_range' => $pricing['tier_range'],
            'discount_applied' => $pricing['discount_applied'],
            'base_unit_price' => $this->unit_price,
            'savings_amount' => $pricing['savings'],
            'category_name' => $this->category->name ?? '',
            'image_url' => $this->getImageUrl(),
        ];
    }

    /**
     * Export items to array format
     */
    public function toExportArray(): array
    {
        return [
            'item_code' => $this->item_code,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category->name ?? '',
            'unit' => $this->unit,
            'unit_price' => $this->unit_price,
            'cost_price' => $this->cost_price,
            'minimum_price' => $this->minimum_price,
            'markup_percentage' => $this->markup_percentage,
            'specifications' => $this->specifications,
            'is_active' => $this->is_active ? 'Yes' : 'No',
            'is_featured' => $this->is_featured ? 'Yes' : 'No',
            'stock_quantity' => $this->track_stock ? $this->stock_quantity : 'N/A',
            'tier_pricing' => $this->hasTierPricing() ? 'Yes' : 'No',
            'segments_with_tiers' => $this->getSegmentsWithTiers()->pluck('name')->join(', '),
            'created_at' => $this->created_at->format('Y-m-d'),
            'last_price_update' => $this->last_price_update?->format('Y-m-d'),
        ];
    }
}
