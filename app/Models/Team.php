<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'manager_id',
        'coordinator_id',
        'territory',
        'target_revenue',
        'settings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'json',
            'target_revenue' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the company that owns the team.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the team manager.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the team coordinator.
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /**
     * The users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the leads for the team.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Scope to get teams for a specific company.
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        return $query->where('company_id', $companyId);
    }

    /**
     * Check if team is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
