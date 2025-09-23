<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ServiceTemplate extends Model
{
    use HasFactory;
    // Note: SoftDeletes temporarily disabled until migration is run
    // use HasFactory, SoftDeletes;

    /**
     * Template categories
     */
    const CATEGORY_INSTALLATION = 'Installation';
    const CATEGORY_MAINTENANCE = 'Maintenance';
    const CATEGORY_CONSULTING = 'Consulting';
    const CATEGORY_TRAINING = 'Training';
    const CATEGORY_SUPPORT = 'Support';
    const CATEGORY_CUSTOM = 'Custom';

    const CATEGORIES = [
        self::CATEGORY_INSTALLATION => 'Installation Services',
        self::CATEGORY_MAINTENANCE => 'Maintenance Packages',
        self::CATEGORY_CONSULTING => 'Consulting Services',
        self::CATEGORY_TRAINING => 'Training Programs',
        self::CATEGORY_SUPPORT => 'Support Packages',
        self::CATEGORY_CUSTOM => 'Custom Solutions',
    ];

    protected $fillable = [
        'name',
        'description',
        'category',
        'company_id',
        'applicable_teams',
        'settings',
        'estimated_hours',
        'base_price',
        'is_active',
        'requires_approval',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'applicable_teams' => 'array',
        'settings' => 'array',
        'estimated_hours' => 'decimal:2',
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'requires_approval' => 'boolean',
        'last_used_at' => 'datetime',
        'usage_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically set company_id and created_by
        static::creating(function ($template) {
            if (!$template->company_id) {
                $template->company_id = auth()->user()->company_id;
            }
            if (!$template->created_by) {
                $template->created_by = auth()->id();
            }
        });

        // Update updated_by when template is modified
        static::updating(function ($template) {
            $template->updated_by = auth()->id();
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

    public function sections(): HasMany
    {
        return $this->hasMany(ServiceTemplateSection::class)->orderBy('sort_order');
    }

    public function activeSections(): HasMany
    {
        return $this->hasMany(ServiceTemplateSection::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Scopes for multi-tenancy and filtering
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?: (auth()->check() ? auth()->user()->company_id : null);
        return $query->where('company_id', $companyId);
    }

    public function scopeForUserTeams($query)
    {
        $user = auth()->user();
        
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'finance_manager'])) {
            return $query; // Can see all templates
        }
        
        if ($user->hasRole('sales_manager')) {
            // Can see templates for teams they manage + company-wide templates
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id')->toArray();
            return $query->where(function ($q) use ($managedTeamIds) {
                $q->whereJsonContains('applicable_teams', $managedTeamIds)
                  ->orWhereNull('applicable_teams'); // Company-wide templates
            });
        }
        
        // Sales coordinators and executives see templates for their teams
        $userTeamIds = $user->teams()->pluck('teams.id')->toArray();
        return $query->where(function ($q) use ($userTeamIds) {
            $q->whereJsonContains('applicable_teams', $userTeamIds)
              ->orWhereNull('applicable_teams'); // Company-wide templates
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get all available categories
     */
    public static function getCategories(): array
    {
        return self::CATEGORIES;
    }

    /**
     * Business logic methods
     */
    public function canBeUsedBy(User $user): bool
    {
        // Check if template is active
        if (!$this->is_active) {
            return false;
        }

        // Check company access
        if ($user->company_id !== $this->company_id) {
            return false;
        }

        // Superadmin and managers can use any template
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'sales_manager'])) {
            return true;
        }

        // Check team-specific access
        if (empty($this->applicable_teams)) {
            return true; // Company-wide template
        }

        $userTeamIds = $user->teams()->pluck('teams.id')->toArray();
        return !empty(array_intersect($this->applicable_teams, $userTeamIds));
    }

    public function canBeEditedBy(User $user): bool
    {
        // Check company access
        if ($user->company_id !== $this->company_id) {
            return false;
        }

        // Superadmin and company manager can edit any template
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Sales manager can edit templates they created or manage
        if ($user->hasRole('sales_manager')) {
            // Created by them or manages the applicable teams
            if ($this->created_by === $user->id) {
                return true;
            }

            if (!empty($this->applicable_teams)) {
                $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id')->toArray();
                return !empty(array_intersect($this->applicable_teams, $managedTeamIds));
            }
        }

        return false;
    }

    public function canBeDeletedBy(User $user): bool
    {
        // Only superadmin, company manager, and template creator can delete
        return $user->hasAnyRole(['superadmin', 'company_manager']) || 
               $this->created_by === $user->id;
    }

    /**
     * Template usage tracking
     */
    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function isPopular(): bool
    {
        return $this->usage_count > 10;
    }

    /**
     * Template duplication
     */
    public function duplicate(string $newName = null): self
    {
        $newName = $newName ?: $this->name . ' (Copy)';

        $template = $this->replicate();
        $template->name = $newName;
        $template->usage_count = 0;
        $template->last_used_at = null;
        $template->created_by = auth()->id();
        $template->updated_by = null;
        $template->save();

        // Deep copy sections and items
        foreach ($this->sections as $section) {
            $newSection = $section->replicate();
            $newSection->service_template_id = $template->id;
            $newSection->save();

            foreach ($section->items as $item) {
                $newItem = $item->replicate();
                $newItem->service_template_section_id = $newSection->id;
                $newItem->save();
            }
        }

        return $template;
    }

    /**
     * Convert template to quotation data
     */
    public function toQuotationData(array $customizations = []): array
    {
        $data = [
            'type' => 'service', // Service quotation type
            'title' => $customizations['title'] ?? $this->name,
            'description' => $customizations['description'] ?? $this->description,
            'estimated_hours' => $this->estimated_hours,
            'sections' => [],
            'items' => [],
        ];

        // Convert sections
        foreach ($this->activeSections as $section) {
            $sectionData = [
                'name' => $section->name,
                'description' => $section->description,
                'discount_percentage' => $section->default_discount_percentage,
                'sort_order' => $section->sort_order,
                'items' => [],
            ];

            // Convert items
            foreach ($section->activeItems as $item) {
                $itemData = [
                    'description' => $item->description,
                    'unit' => $item->unit,
                    'quantity' => $customizations['items'][$item->id]['quantity'] ?? $item->default_quantity,
                    'unit_price' => $customizations['items'][$item->id]['unit_price'] ?? $item->default_unit_price,
                    'item_code' => $item->item_code,
                    'specifications' => $item->specifications,
                    'notes' => $item->notes,
                    'sort_order' => $item->sort_order,
                ];

                $sectionData['items'][] = $itemData;
            }

            $data['sections'][] = $sectionData;
        }

        return $data;
    }

    /**
     * Calculate template totals (for estimates)
     */
    public function calculateEstimatedTotal(): float
    {
        $total = 0;

        foreach ($this->activeSections as $section) {
            $sectionTotal = 0;

            foreach ($section->activeItems as $item) {
                $sectionTotal += $item->default_quantity * $item->default_unit_price;
            }

            // Apply section discount
            $sectionTotal -= ($sectionTotal * $section->default_discount_percentage / 100);
            $total += $sectionTotal;
        }

        return $total;
    }

    /**
     * Get template complexity score (for analytics)
     */
    public function getComplexityScore(): int
    {
        $sectionCount = $this->sections()->count();
        $itemCount = ServiceTemplateItem::whereHas('section', function ($query) {
            $query->where('service_template_id', $this->id);
        })->count();

        return ($sectionCount * 2) + $itemCount;
    }

    /**
     * Check if template has required approvals
     */
    public function needsApproval(User $user): bool
    {
        return $this->requires_approval && 
               !$user->hasAnyRole(['superadmin', 'company_manager', 'sales_manager']);
    }
}
