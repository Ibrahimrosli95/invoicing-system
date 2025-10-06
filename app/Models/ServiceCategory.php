<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ServiceCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'company_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name if not provided
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }

            // Auto-set company_id from authenticated user
            if (empty($category->company_id) && auth()->check()) {
                $category->company_id = auth()->user()->company_id;
            }
        });

        static::updating(function ($category) {
            // Update slug if name changed
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
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

    public function serviceTemplates(): HasMany
    {
        return $this->hasMany(ServiceTemplate::class, 'category_id');
    }

    /**
     * Scopes
     */
    public function scopeForCompany(Builder $query): Builder
    {
        if (auth()->check()) {
            return $query->where('company_id', auth()->user()->company_id);
        }
        return $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Business Logic Methods
     */
    public function getTemplateCountAttribute(): int
    {
        return $this->serviceTemplates()->count();
    }

    public function canBeDeleted(): bool
    {
        // Cannot delete if has templates
        return $this->serviceTemplates()->count() === 0;
    }

    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }
}
