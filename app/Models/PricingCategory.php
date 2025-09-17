<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PricingCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'code',
        'parent_id',
        'sort_order',
        'is_active',
        'settings',
        'icon',
        'color',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically set company_id and created_by
        static::creating(function ($category) {
            if (!$category->company_id) {
                $category->company_id = auth()->user()->company_id;
            }
            if (!$category->created_by) {
                $category->created_by = auth()->id();
            }
        });

        // Update updated_by when category is modified
        static::updating(function ($category) {
            $category->updated_by = auth()->id();
        });
    }

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PricingCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PricingCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function activeChildren(): HasMany
    {
        return $this->hasMany(PricingCategory::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PricingItem::class)->orderBy('sort_order');
    }

    public function activeItems(): HasMany
    {
        return $this->hasMany(PricingItem::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
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

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeWithDepth($query, $maxDepth = 3)
    {
        return $query->with(['children' => function ($query) use ($maxDepth) {
            if ($maxDepth > 1) {
                $query->withDepth($maxDepth - 1);
            }
        }]);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Business logic methods
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    public function hasItems(): bool
    {
        return $this->items()->count() > 0;
    }

    public function canBeDeleted(): bool
    {
        return !$this->hasChildren() && !$this->hasItems();
    }

    public function getDepth(): int
    {
        $depth = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        
        return $depth;
    }

    public function getBreadcrumb(): array
    {
        $breadcrumb = [];
        $current = $this;
        
        while ($current) {
            array_unshift($breadcrumb, [
                'id' => $current->id,
                'name' => $current->name,
                'code' => $current->code,
            ]);
            $current = $current->parent;
        }
        
        return $breadcrumb;
    }

    public function getAllDescendants(): \Illuminate\Support\Collection
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }
        
        return $descendants;
    }

    public function getTotalItemCount(): int
    {
        $count = $this->items()->count();
        
        foreach ($this->children as $child) {
            $count += $child->getTotalItemCount();
        }
        
        return $count;
    }

    public function getActiveItemCount(): int
    {
        $count = $this->activeItems()->count();
        
        foreach ($this->activeChildren as $child) {
            $count += $child->getActiveItemCount();
        }
        
        return $count;
    }

    /**
     * Get the full category path for display
     */
    public function getFullPath(): string
    {
        $breadcrumb = $this->getBreadcrumb();
        return collect($breadcrumb)->pluck('name')->implode(' > ');
    }

    /**
     * Get category statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_items' => $this->getTotalItemCount(),
            'active_items' => $this->getActiveItemCount(),
            'direct_items' => $this->items()->count(),
            'subcategories' => $this->children()->count(),
            'active_subcategories' => $this->activeChildren()->count(),
            'depth' => $this->getDepth(),
        ];
    }

    /**
     * Move category to new parent
     */
    public function moveTo($newParentId): bool
    {
        // Prevent circular references
        if ($newParentId && $this->isAncestorOf($newParentId)) {
            return false;
        }

        $this->parent_id = $newParentId;
        return $this->save();
    }

    /**
     * Check if this category is ancestor of given category ID
     */
    protected function isAncestorOf($categoryId): bool
    {
        $descendants = $this->getAllDescendants();
        return $descendants->contains('id', $categoryId);
    }

    /**
     * Get available parent categories (excluding self and descendants)
     */
    public function getAvailableParents()
    {
        $excludeIds = [$this->id];
        $excludeIds = array_merge($excludeIds, $this->getAllDescendants()->pluck('id')->toArray());
        
        return static::forCompany()
            ->whereNotIn('id', $excludeIds)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Update sort orders for categories in same parent
     */
    public function updateSortOrder(int $newOrder): void
    {
        $siblings = static::where('parent_id', $this->parent_id)
            ->where('id', '!=', $this->id)
            ->orderBy('sort_order')
            ->get();

        $currentOrder = 0;
        foreach ($siblings as $sibling) {
            if ($currentOrder == $newOrder) {
                $currentOrder++;
            }
            $sibling->update(['sort_order' => $currentOrder]);
            $currentOrder++;
        }

        $this->update(['sort_order' => $newOrder]);
    }

    /**
     * Create hierarchical tree structure
     */
    public static function getTree($companyId = null): \Illuminate\Support\Collection
    {
        $companyId = $companyId ?: auth()->user()->company_id;
        
        return static::forCompany($companyId)
            ->active()
            ->rootCategories()
            ->withDepth(3)
            ->ordered()
            ->get();
    }

    /**
     * Get flat list with indentation
     */
    public static function getFlatList($companyId = null): \Illuminate\Support\Collection
    {
        $tree = static::getTree($companyId);
        $flatList = collect();
        
        foreach ($tree as $category) {
            static::addToFlatList($flatList, $category, 0);
        }
        
        return $flatList;
    }

    protected static function addToFlatList($list, $category, $depth)
    {
        $category->depth = $depth;
        $category->indented_name = str_repeat('— ', $depth) . $category->name;
        $list->push($category);
        
        foreach ($category->children as $child) {
            static::addToFlatList($list, $child, $depth + 1);
        }
    }

    /**
     * Search categories by name or code
     */
    public static function search($term, $companyId = null): \Illuminate\Support\Collection
    {
        $companyId = $companyId ?: auth()->user()->company_id;
        
        return static::forCompany($companyId)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                      ->orWhere('code', 'like', "%{$term}%")
                      ->orWhere('description', 'like', "%{$term}%");
            })
            ->active()
            ->ordered()
            ->get();
    }
}
