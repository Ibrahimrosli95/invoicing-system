<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceTemplateSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_template_id',
        'name',
        'description',
        'default_discount_percentage',
        'sort_order',
        'is_required',
        'is_active',
        'settings',
        'estimated_hours',
        'instructions',
    ];

    protected $casts = [
        'default_discount_percentage' => 'decimal:2',
        'sort_order' => 'integer',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
        'estimated_hours' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(ServiceTemplate::class, 'service_template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceTemplateItem::class)->orderBy('sort_order');
    }

    public function activeItems(): HasMany
    {
        return $this->hasMany(ServiceTemplateItem::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function requiredItems(): HasMany
    {
        return $this->hasMany(ServiceTemplateItem::class)
            ->where('is_required', true)
            ->where('is_active', true)
            ->orderBy('sort_order');
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

    public function scopeForTemplate($query, $templateId)
    {
        return $query->where('service_template_id', $templateId);
    }

    /**
     * Business logic methods
     */
    public function canBeRemovedFromQuotation(): bool
    {
        return !$this->is_required;
    }

    public function hasCustomizableDiscount(): bool
    {
        return $this->default_discount_percentage > 0;
    }

    public function getItemCount(): int
    {
        return $this->items()->count();
    }

    public function getActiveItemCount(): int
    {
        return $this->activeItems()->count();
    }

    public function getRequiredItemCount(): int
    {
        return $this->requiredItems()->count();
    }

    /**
     * Calculate section totals (before discount)
     */
    public function calculateSubtotal(): float
    {
        return $this->activeItems->sum(function ($item) {
            return $item->default_quantity * $item->default_unit_price;
        });
    }

    /**
     * Calculate section discount amount
     */
    public function calculateDiscountAmount(float $subtotal = null): float
    {
        $subtotal = $subtotal ?? $this->calculateSubtotal();
        return $subtotal * ($this->default_discount_percentage / 100);
    }

    /**
     * Calculate section total (after discount)
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->calculateSubtotal();
        return $subtotal - $this->calculateDiscountAmount($subtotal);
    }

    /**
     * Get estimated margin for this section
     */
    public function calculateEstimatedMargin(): array
    {
        $revenue = $this->calculateTotal();
        $cost = $this->activeItems->sum(function ($item) {
            return ($item->cost_price ?? 0) * $item->default_quantity;
        });

        $margin = $revenue - $cost;
        $marginPercentage = $revenue > 0 ? ($margin / $revenue) * 100 : 0;

        return [
            'revenue' => $revenue,
            'cost' => $cost,
            'margin' => $margin,
            'margin_percentage' => round($marginPercentage, 2),
        ];
    }

    /**
     * Check if section has items with cost prices defined
     */
    public function hasCostData(): bool
    {
        return $this->activeItems()
            ->whereNotNull('cost_price')
            ->exists();
    }

    /**
     * Convert section to quotation section format
     */
    public function toQuotationSectionData(array $customizations = []): array
    {
        return [
            'name' => $customizations['name'] ?? $this->name,
            'description' => $customizations['description'] ?? $this->description,
            'discount_percentage' => $customizations['discount_percentage'] ?? $this->default_discount_percentage,
            'sort_order' => $customizations['sort_order'] ?? $this->sort_order,
            'is_required' => $this->is_required,
            'estimated_hours' => $this->estimated_hours,
            'items' => $this->activeItems->map(function ($item) use ($customizations) {
                $itemCustomizations = $customizations['items'][$item->id] ?? [];
                return $item->toQuotationItemData($itemCustomizations);
            })->toArray(),
        ];
    }

    /**
     * Duplicate section for template duplication
     */
    public function duplicate($newTemplateId): self
    {
        $section = $this->replicate();
        $section->service_template_id = $newTemplateId;
        $section->save();

        // Duplicate all items
        foreach ($this->items as $item) {
            $item->duplicate($section->id);
        }

        return $section;
    }

    /**
     * Get complexity score for this section
     */
    public function getComplexityScore(): int
    {
        $itemCount = $this->items()->count();
        $hasDiscount = $this->default_discount_percentage > 0 ? 1 : 0;
        $hasInstructions = !empty($this->instructions) ? 1 : 0;
        $hasSettings = !empty($this->settings) ? 1 : 0;

        return $itemCount + $hasDiscount + $hasInstructions + $hasSettings;
    }

    /**
     * Check if section needs manual review
     */
    public function needsReview(): bool
    {
        if ($this->default_discount_percentage > 10) {
            return true;
        }

        if (!$this->hasCostData()) {
            return true;
        }

        if ($this->getItemCount() > 10) {
            return true;
        }

        return false;
    }

    /**
     * Validate section configuration
     */
    public function validateConfiguration(): array
    {
        $issues = [];

        if ($this->getActiveItemCount() === 0) {
            $issues[] = 'Section has no active items';
        }

        if ($this->is_required && $this->getRequiredItemCount() === 0) {
            $issues[] = 'Required section has no required items';
        }

        if ($this->default_discount_percentage < 0 || $this->default_discount_percentage > 100) {
            $issues[] = 'Invalid discount percentage';
        }

        $invalidPriceItems = $this->activeItems()
            ->where('default_unit_price', '<', 0)
            ->count();

        if ($invalidPriceItems > 0) {
            $issues[] = "{$invalidPriceItems} items have invalid unit prices";
        }

        return $issues;
    }

    /**
     * Get section performance analytics
     */
    public function getAnalytics(): array
    {
        return [
            'usage_frequency' => 0,
            'average_discount_applied' => $this->default_discount_percentage,
            'total_items' => $this->getItemCount(),
            'active_items' => $this->getActiveItemCount(),
            'required_items' => $this->getRequiredItemCount(),
            'estimated_margin' => $this->calculateEstimatedMargin(),
            'complexity_score' => $this->getComplexityScore(),
            'needs_review' => $this->needsReview(),
            'configuration_issues' => $this->validateConfiguration(),
        ];
    }
}
