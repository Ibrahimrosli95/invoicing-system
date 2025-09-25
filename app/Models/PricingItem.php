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
        'unit_price',
        'cost_price',
        'minimum_price',
        'segment_selling_prices',
        'segment_target_margins',
        'segment_prices_updated_at',
        'use_segment_pricing',
        'image_path',
        'is_active',
        'sort_order',
        'settings',
        'markup_percentage',
        'last_price_update',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'minimum_price' => 'decimal:2',
        'segment_selling_prices' => 'array',
        'segment_target_margins' => 'array',
        'segment_prices_updated_at' => 'datetime',
        'use_segment_pricing' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
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


    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('pricing_category_id', $categoryId);
    }

    public function scopeByItemCode($query, $itemCode)
    {
        return $query->where('item_code', $itemCode);
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
            'unit' => $customizations['unit'] ?? 'pcs', // Default unit since field removed
            'quantity' => $customizations['quantity'] ?? $quantity,
            'unit_price' => $customizations['unit_price'] ?? $this->unit_price,
            'item_code' => $this->item_code,
            'specifications' => $customizations['specifications'] ?? '', // Empty since field removed
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
                      ->orWhere('description', 'like', "%{$term}%");
            })
            ->active()
            ->with(['category'])
            ->ordered()
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

        // Return most recently updated items as popular since featured functionality removed
        return static::forCompany($companyId)
            ->active()
            ->with(['category'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
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
     * New Segment Pricing Methods (Simplified System)
     */

    /**
     * Check if this item uses segment-specific pricing
     */
    public function hasSegmentPricing(): bool
    {
        return $this->use_segment_pricing && !empty($this->segment_selling_prices);
    }

    /**
     * Get selling price for a specific customer segment
     */
    public function getSellingPriceForSegment($segmentId): float
    {
        if (!$this->hasSegmentPricing()) {
            // Fallback to segment discount on base price
            $segment = CustomerSegment::find($segmentId);
            if ($segment) {
                return $segment->calculateDiscountedPrice($this->unit_price);
            }
            return $this->unit_price;
        }

        // Get segment-specific price from JSON
        $segmentPrices = $this->segment_selling_prices ?? [];
        return isset($segmentPrices[$segmentId])
            ? (float) $segmentPrices[$segmentId]
            : $this->unit_price;
    }

    /**
     * Set selling price for a specific customer segment
     */
    public function setSellingPriceForSegment($segmentId, float $price): self
    {
        $segmentPrices = $this->segment_selling_prices ?? [];
        $segmentPrices[$segmentId] = number_format($price, 2, '.', '');

        $this->segment_selling_prices = $segmentPrices;
        $this->use_segment_pricing = true;
        $this->segment_prices_updated_at = now();

        return $this;
    }

    /**
     * Get margin percentage for a specific customer segment
     */
    public function getMarginForSegment($segmentId): array
    {
        $sellingPrice = $this->getSellingPriceForSegment($segmentId);
        $costPrice = $this->cost_price ?? 0;

        if ($sellingPrice <= 0) {
            return [
                'margin_percentage' => 0,
                'margin_amount' => 0,
                'status' => 'invalid',
                'color' => 'text-red-600',
                'bg_color' => 'bg-red-50',
            ];
        }

        if ($costPrice <= 0) {
            return [
                'margin_percentage' => null,
                'margin_amount' => null,
                'status' => 'no_cost',
                'color' => 'text-gray-600',
                'bg_color' => 'bg-gray-50',
            ];
        }

        $marginAmount = $sellingPrice - $costPrice;
        $marginPercentage = ($marginAmount / $sellingPrice) * 100;

        // Determine status and colors based on margin
        if ($marginPercentage < 0) {
            $status = 'loss';
            $color = 'text-red-600';
            $bgColor = 'bg-red-50';
        } elseif ($marginPercentage < 10) {
            $status = 'low';
            $color = 'text-orange-600';
            $bgColor = 'bg-orange-50';
        } elseif ($marginPercentage < 20) {
            $status = 'medium';
            $color = 'text-yellow-600';
            $bgColor = 'bg-yellow-50';
        } else {
            $status = 'good';
            $color = 'text-green-600';
            $bgColor = 'bg-green-50';
        }

        return [
            'margin_percentage' => round($marginPercentage, 2),
            'margin_amount' => round($marginAmount, 2),
            'status' => $status,
            'color' => $color,
            'bg_color' => $bgColor,
        ];
    }

    /**
     * Get all segment prices for this item
     */
    public function getAllSegmentPrices(): array
    {
        $segments = CustomerSegment::forCompany()->active()->ordered()->get();
        $prices = [];

        foreach ($segments as $segment) {
            $sellingPrice = $this->getSellingPriceForSegment($segment->id);
            $margin = $this->getMarginForSegment($segment->id);

            $prices[] = [
                'segment_id' => $segment->id,
                'segment_name' => $segment->name,
                'segment_color' => $segment->color,
                'selling_price' => $sellingPrice,
                'margin' => $margin,
                'is_custom_price' => isset($this->segment_selling_prices[$segment->id]),
            ];
        }

        return $prices;
    }

    /**
     * Set multiple segment prices at once
     */
    public function setSegmentPrices(array $prices): self
    {
        $segmentPrices = [];
        foreach ($prices as $segmentId => $price) {
            if ($price !== null && $price > 0) {
                $segmentPrices[$segmentId] = number_format($price, 2, '.', '');
            }
        }

        $this->segment_selling_prices = $segmentPrices;
        $this->use_segment_pricing = !empty($segmentPrices);
        $this->segment_prices_updated_at = now();

        return $this;
    }

    /**
     * Copy segment prices from another item
     */
    public function copySegmentPricesFrom(PricingItem $sourceItem): self
    {
        if ($sourceItem->hasSegmentPricing()) {
            $this->segment_selling_prices = $sourceItem->segment_selling_prices;
            $this->segment_target_margins = $sourceItem->segment_target_margins;
            $this->use_segment_pricing = true;
            $this->segment_prices_updated_at = now();
        }

        return $this;
    }

    /**
     * Generate suggested segment prices based on cost and target margins
     */
    public function generateSuggestedSegmentPrices(array $targetMargins = null): array
    {
        $segments = CustomerSegment::forCompany()->active()->ordered()->get();
        $suggestions = [];

        // Default target margins per segment if not provided
        $defaultMargins = [
            'End User' => 25,
            'Contractor' => 20,
            'Dealer' => 15,
        ];

        foreach ($segments as $segment) {
            $targetMargin = $targetMargins[$segment->id]
                ?? $this->segment_target_margins[$segment->id]
                ?? $defaultMargins[$segment->name]
                ?? 20;

            $costPrice = $this->cost_price ?? 0;

            if ($costPrice > 0 && $targetMargin > 0) {
                // Calculate price for target margin: selling_price = cost_price / (1 - margin/100)
                $suggestedPrice = $costPrice / (1 - $targetMargin / 100);

                $suggestions[] = [
                    'segment_id' => $segment->id,
                    'segment_name' => $segment->name,
                    'current_price' => $this->getSellingPriceForSegment($segment->id),
                    'suggested_price' => round($suggestedPrice, 2),
                    'target_margin' => $targetMargin,
                    'cost_price' => $costPrice,
                ];
            }
        }

        return $suggestions;
    }

    /**
     * Validate segment pricing setup
     */
    public function validateSegmentPricing(): array
    {
        $issues = [];
        $warnings = [];

        if ($this->hasSegmentPricing()) {
            $costPrice = $this->cost_price ?? 0;

            foreach ($this->segment_selling_prices as $segmentId => $price) {
                $segment = CustomerSegment::find($segmentId);
                $segmentName = $segment ? $segment->name : "Segment #{$segmentId}";

                // Check if price is below cost
                if ($costPrice > 0 && $price < $costPrice) {
                    $issues[] = "{$segmentName}: Selling price (RM {$price}) is below cost price (RM {$costPrice})";
                }

                // Check if price is below minimum
                if ($this->minimum_price > 0 && $price < $this->minimum_price) {
                    $issues[] = "{$segmentName}: Selling price (RM {$price}) is below minimum price (RM {$this->minimum_price})";
                }

                // Check margin warnings
                $margin = $this->getMarginForSegment($segmentId);
                if ($margin['status'] === 'low') {
                    $warnings[] = "{$segmentName}: Low margin ({$margin['margin_percentage']}%)";
                }
            }
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'warnings' => $warnings,
            'has_segment_pricing' => $this->hasSegmentPricing(),
        ];
    }

    /**
     * Export items to array format with segment pricing
     */
    public function toExportArray(): array
    {
        $baseData = [
            'item_code' => $this->item_code,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category->name ?? '',
            'unit' => $this->unit,
            'cost_price' => $this->cost_price,
            'unit_price' => $this->unit_price,
            'minimum_price' => $this->minimum_price,
            'specifications' => $this->specifications,
            'is_active' => $this->is_active ? 'Yes' : 'No',
            'is_featured' => $this->is_featured ? 'Yes' : 'No',
            'use_segment_pricing' => $this->use_segment_pricing ? 'Yes' : 'No',
            'created_at' => $this->created_at->format('Y-m-d'),
        ];

        // Add segment prices if enabled
        if ($this->hasSegmentPricing()) {
            $segments = CustomerSegment::forCompany()->active()->ordered()->get();
            foreach ($segments as $segment) {
                $price = $this->getSellingPriceForSegment($segment->id);
                $margin = $this->getMarginForSegment($segment->id);

                $baseData[$segment->name . '_Price'] = $price;
                $baseData[$segment->name . '_Margin'] = $margin['margin_percentage']
                    ? $margin['margin_percentage'] . '%'
                    : 'N/A';
            }
        }

        return $baseData;
    }

    /**
     * Convert to import array format for CSV templates
     */
    public function toImportArray(): array
    {
        $segments = CustomerSegment::forCompany()->active()->ordered()->get();
        $data = [
            'item_code' => $this->item_code,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category->name ?? '',
            'unit' => $this->unit,
            'cost_price' => $this->cost_price,
        ];

        // Add segment price columns
        foreach ($segments as $segment) {
            $data[strtolower(str_replace(' ', '_', $segment->name)) . '_price'] =
                $this->getSellingPriceForSegment($segment->id);
        }

        return $data;
    }
}
