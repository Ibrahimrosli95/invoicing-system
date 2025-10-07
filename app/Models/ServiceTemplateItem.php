<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceTemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_template_section_id',
        'description',
        'unit',
        'default_quantity',
        'default_unit_price',
        'item_code',
        'specifications',
        'notes',
        'sort_order',
        'is_required',
        'quantity_editable',
        'price_editable',
        'is_active',
        'settings',
        'cost_price',
        'minimum_price',
        'amount_override',
        'amount_manually_edited',
    ];

    protected $casts = [
        'default_quantity' => 'decimal:2',
        'default_unit_price' => 'decimal:2',
        'sort_order' => 'integer',
        'is_required' => 'boolean',
        'quantity_editable' => 'boolean',
        'price_editable' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
        'cost_price' => 'decimal:2',
        'minimum_price' => 'decimal:2',
        'amount_override' => 'decimal:2',
        'amount_manually_edited' => 'boolean',
    ];

    /**
     * Computed attributes
     */
    protected $appends = ['calculated_amount', 'final_amount'];

    /**
     * Relationships
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(ServiceTemplateSection::class, 'service_template_section_id');
    }

    public function template(): BelongsTo
    {
        return $this->section->template();
    }

    /**
     * Scopes for filtering
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeForSection($query, $sectionId)
    {
        return $query->where('service_template_section_id', $sectionId);
    }

    public function scopeByItemCode($query, $itemCode)
    {
        return $query->where('item_code', $itemCode);
    }

    /**
     * Computed Attributes - Amount Calculation
     */

    /**
     * Get calculated amount (quantity × unit_price)
     *
     * @return float
     */
    public function getCalculatedAmountAttribute(): float
    {
        return $this->default_quantity * $this->default_unit_price;
    }

    /**
     * Get final amount - uses override if set, otherwise calculated
     *
     * @return float
     */
    public function getFinalAmountAttribute(): float
    {
        // If amount is manually overridden, use override
        if ($this->amount_override !== null) {
            return $this->amount_override;
        }

        // Otherwise use calculated amount
        return $this->calculated_amount;
    }

    /**
     * Business logic methods
     */
    public function canBeRemovedFromQuotation(): bool
    {
        return !$this->is_required;
    }

    public function isQuantityEditable(): bool
    {
        return $this->quantity_editable;
    }

    public function isPriceEditable(): bool
    {
        return $this->price_editable;
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
     * Amount Override Management
     */

    /**
     * Set amount override manually
     *
     * @param float $amount
     * @return void
     */
    public function setAmountOverride(float $amount): void
    {
        $this->amount_override = $amount;
        $this->amount_manually_edited = true;
        $this->save();
    }

    /**
     * Clear amount override and return to calculated amount
     *
     * @return void
     */
    public function clearAmountOverride(): void
    {
        $this->amount_override = null;
        $this->amount_manually_edited = false;
        $this->save();
    }

    /**
     * Check if amount is manually overridden
     *
     * @return bool
     */
    public function hasAmountOverride(): bool
    {
        return $this->amount_manually_edited && $this->amount_override !== null;
    }

    /**
     * Get the difference between override and calculated amount
     *
     * @return float
     */
    public function getAmountDifference(): float
    {
        if (!$this->hasAmountOverride()) {
            return 0;
        }

        return $this->amount_override - $this->calculated_amount;
    }

    /**
     * Get percentage difference from calculated amount
     *
     * @return float
     */
    public function getAmountDifferencePercentage(): float
    {
        if (!$this->hasAmountOverride() || $this->calculated_amount == 0) {
            return 0;
        }

        return (($this->amount_override - $this->calculated_amount) / $this->calculated_amount) * 100;
    }

    /**
     * Calculate default total price (quantity × unit price)
     */
    public function calculateDefaultTotal(): float
    {
        return $this->default_quantity * $this->default_unit_price;
    }

    /**
     * Calculate margin based on cost price
     */
    public function calculateMargin(float $sellingPrice = null, float $quantity = null): array
    {
        $sellingPrice = $sellingPrice ?? $this->default_unit_price;
        $quantity = $quantity ?? $this->default_quantity;
        
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
     * Check if selling price meets minimum requirements
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
     * Get suggested selling price based on cost and margin targets
     */
    public function getSuggestedPrice(float $targetMarginPercentage = 30): float
    {
        if (!$this->hasCostPrice()) {
            return $this->default_unit_price;
        }

        $suggestedPrice = $this->cost_price / (1 - ($targetMarginPercentage / 100));

        // Ensure it meets minimum price requirements
        if ($this->hasMinimumPrice() && $suggestedPrice < $this->minimum_price) {
            return $this->minimum_price;
        }

        return round($suggestedPrice, 2);
    }

    /**
     * Convert item to quotation item format
     */
    public function toQuotationItemData(array $customizations = []): array
    {
        return [
            'description' => $customizations['description'] ?? $this->description,
            'unit' => $customizations['unit'] ?? $this->unit,
            'quantity' => $customizations['quantity'] ?? $this->default_quantity,
            'unit_price' => $customizations['unit_price'] ?? $this->default_unit_price,
            'item_code' => $this->item_code,
            'specifications' => $customizations['specifications'] ?? $this->specifications,
            'notes' => $customizations['notes'] ?? $this->notes,
            'sort_order' => $customizations['sort_order'] ?? $this->sort_order,
            'template_item_id' => $this->id,
            'is_from_template' => true,
            'quantity_editable' => $this->quantity_editable,
            'price_editable' => $this->price_editable,
            'minimum_price' => $this->minimum_price,
            'cost_price' => $this->cost_price,
        ];
    }

    /**
     * Duplicate item for template/section duplication
     */
    public function duplicate($newSectionId): self
    {
        $item = $this->replicate();
        $item->service_template_section_id = $newSectionId;
        $item->save();

        return $item;
    }

    /**
     * Get item complexity score
     */
    public function getComplexityScore(): int
    {
        $score = 1; // Base score for any item

        // Add complexity for specifications
        if (!empty($this->specifications)) {
            $score += 1;
        }

        // Add complexity for notes
        if (!empty($this->notes)) {
            $score += 1;
        }

        // Add complexity for item code
        if (!empty($this->item_code)) {
            $score += 1;
        }

        // Add complexity for pricing constraints
        if ($this->hasMinimumPrice()) {
            $score += 1;
        }

        // Add complexity for settings
        if (!empty($this->settings)) {
            $score += count($this->settings);
        }

        return $score;
    }

    /**
     * Check if item configuration needs review
     */
    public function needsReview(): bool
    {
        // Item needs review if:
        // - No cost price but has minimum price
        // - Unit price is below cost price
        // - Large quantity (> 100)
        // - Complex specifications

        if ($this->hasMinimumPrice() && !$this->hasCostPrice()) {
            return true;
        }

        if ($this->hasCostPrice() && $this->default_unit_price <= $this->cost_price) {
            return true;
        }

        if ($this->default_quantity > 100) {
            return true;
        }

        if (!empty($this->specifications) && strlen($this->specifications) > 500) {
            return true;
        }

        return false;
    }

    /**
     * Validate item configuration
     */
    public function validateConfiguration(): array
    {
        $issues = [];

        // Basic validation
        if (empty($this->description)) {
            $issues[] = 'Item description is required';
        }

        if ($this->default_quantity <= 0) {
            $issues[] = 'Default quantity must be greater than zero';
        }

        if ($this->default_unit_price < 0) {
            $issues[] = 'Default unit price cannot be negative';
        }

        // Pricing logic validation
        if ($this->hasCostPrice() && $this->default_unit_price <= $this->cost_price) {
            $issues[] = 'Default unit price should be above cost price';
        }

        if ($this->hasMinimumPrice() && $this->default_unit_price < $this->minimum_price) {
            $issues[] = 'Default unit price is below minimum selling price';
        }

        // Business logic validation
        if (!$this->quantity_editable && !$this->price_editable && !$this->is_required) {
            $issues[] = 'Optional items should allow quantity or price editing';
        }

        return $issues;
    }

    /**
     * Get item performance analytics
     */
    public function getAnalytics(): array
    {
        $margin = $this->calculateMargin();
        
        return [
            'usage_frequency' => 0, // To be implemented with quotation tracking
            'default_total' => $this->calculateDefaultTotal(),
            'margin_data' => $margin,
            'complexity_score' => $this->getComplexityScore(),
            'needs_review' => $this->needsReview(),
            'configuration_issues' => $this->validateConfiguration(),
            'pricing_flexibility' => [
                'quantity_editable' => $this->quantity_editable,
                'price_editable' => $this->price_editable,
                'has_minimum_price' => $this->hasMinimumPrice(),
                'has_cost_price' => $this->hasCostPrice(),
            ],
        ];
    }

    /**
     * Get pricing recommendations
     */
    public function getPricingRecommendations(): array
    {
        $recommendations = [];

        if ($this->hasCostPrice()) {
            $margin20 = $this->getSuggestedPrice(20);
            $margin30 = $this->getSuggestedPrice(30);
            $margin40 = $this->getSuggestedPrice(40);

            $recommendations['margin_based'] = [
                '20_percent' => $margin20,
                '30_percent' => $margin30,
                '40_percent' => $margin40,
            ];
        }

        if ($this->hasMinimumPrice()) {
            $recommendations['constraints'] = [
                'minimum_price' => $this->minimum_price,
                'current_price' => $this->default_unit_price,
                'meets_minimum' => $this->default_unit_price >= $this->minimum_price,
            ];
        }

        $currentMargin = $this->calculateMargin();
        $recommendations['current_performance'] = $currentMargin;

        return $recommendations;
    }

    /**
     * Generate item summary for reporting
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'item_code' => $this->item_code,
            'unit' => $this->unit,
            'default_quantity' => $this->default_quantity,
            'default_unit_price' => $this->default_unit_price,
            'default_total' => $this->calculateDefaultTotal(),
            'cost_price' => $this->cost_price,
            'minimum_price' => $this->minimum_price,
            'margin_percentage' => $this->calculateMargin()['margin_percentage'],
            'is_active' => $this->is_active,
            'is_required' => $this->is_required,
            'sort_order' => $this->sort_order,
        ];
    }
}
