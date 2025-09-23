<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\WebhookEventService;

class Proof extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'company_id',
        'scope_type',
        'scope_id',
        'type',
        'title',
        'description',
        'metadata',
        'visibility',
        'sort_order',
        'is_featured',
        'show_in_pdf',
        'show_in_quotation',
        'show_in_invoice',
        'status',
        'published_at',
        'expires_at',
        'view_count',
        'click_count',
        'conversion_impact',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_featured' => 'boolean',
        'show_in_pdf' => 'boolean',
        'show_in_quotation' => 'boolean',
        'show_in_invoice' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'conversion_impact' => 'decimal:2',
    ];

    protected $attributes = [
        'sort_order' => 0,
        'is_featured' => false,
        'show_in_pdf' => true,
        'show_in_quotation' => true,
        'show_in_invoice' => false,
        'status' => 'draft',
        'view_count' => 0,
        'click_count' => 0,
        'conversion_impact' => 0,
    ];

    // Constants for proof types
    const TYPES = [
        'visual_proof' => 'Visual Proof',
        'social_proof' => 'Social Proof',
        'professional_proof' => 'Professional Proof',
        'performance_proof' => 'Performance Proof',
        'trust_proof' => 'Trust Proof',
    ];

    // Constants for status
    const STATUSES = [
        'draft' => 'Draft',
        'active' => 'Active',
        'archived' => 'Archived',
    ];

    // Alias for backward compatibility
    const STATUS_OPTIONS = self::STATUSES;

    // Constants for visibility
    const VISIBILITY_LEVELS = [
        'public' => 'Public',
        'private' => 'Private', 
        'restricted' => 'Restricted',
    ];

    // Boot method to handle UUID generation
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
            
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->company_id = auth()->user()->company_id;
            }
        });
        
        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        // Webhook event integration
        static::created(function ($proof) {
            try {
                $webhookService = app(WebhookEventService::class);
                $webhookService->proofCreated($proof);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to dispatch proof.created webhook', [
                    'proof_id' => $proof->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        static::updated(function ($proof) {
            try {
                // Check if proof was published (status changed to 'active')
                if ($proof->isDirty('status') && $proof->status === self::STATUS_ACTIVE) {
                    $webhookService = app(WebhookEventService::class);
                    $webhookService->proofPublished($proof);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to dispatch proof.published webhook', [
                    'proof_id' => $proof->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    // Relationships
    public function scope(): MorphTo
    {
        return $this->morphTo();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ProofAsset::class)->orderBy('sort_order');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ProofView::class);
    }

    // Scopes
    public function scopeForCompany(Builder $query): Builder
    {
        if (auth()->check()) {
            return $query->where('company_id', auth()->user()->company_id);
        }
        return $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('published_at')
                          ->orWhere('published_at', '<=', now());
                    });
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByVisibility(Builder $query, string $visibility): Builder
    {
        return $query->where('visibility', $visibility);
    }

    public function scopeForPDF(Builder $query): Builder
    {
        return $query->where('show_in_pdf', true);
    }

    public function scopeForQuotation(Builder $query): Builder
    {
        return $query->where('show_in_quotation', true);
    }

    public function scopeForInvoice(Builder $query): Builder
    {
        return $query->where('show_in_invoice', true);
    }

    // Business Logic Methods
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? 'Unknown';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Unknown';
    }

    public function getVisibilityLabelAttribute(): string
    {
        return self::VISIBILITY_LEVELS[$this->visibility] ?? 'Unknown';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPublished(): bool
    {
        return $this->isActive() && 
               ($this->published_at === null || $this->published_at <= now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at < now();
    }

    public function canBeViewed(): bool
    {
        return $this->isPublished() && !$this->isExpired();
    }

    public function hasAssets(): bool
    {
        return $this->assets()->exists();
    }

    public function getPrimaryAsset(): ?ProofAsset
    {
        return $this->assets()->where('is_primary', true)->first() ?: 
               $this->assets()->first();
    }

    public function publish(Carbon $publishAt = null): bool
    {
        $this->update([
            'status' => 'active',
            'published_at' => $publishAt ?: now(),
        ]);
        
        return true;
    }

    public function archive(): bool
    {
        return $this->update(['status' => 'archived']);
    }

    public function markAsFeatured(): bool
    {
        return $this->update(['is_featured' => true]);
    }

    public function unmarkAsFeatured(): bool
    {
        return $this->update(['is_featured' => false]);
    }

    public function recordView(array $data = []): ProofView
    {
        $this->increment('view_count');
        
        return $this->views()->create(array_merge([
            'company_id' => $this->company_id,
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'viewed_at' => now(),
        ], $data));
    }

    public function recordClick(): void
    {
        $this->increment('click_count');
    }

    public function updateConversionImpact(float $impact): bool
    {
        return $this->update(['conversion_impact' => $impact]);
    }

    public function getViewsInPeriod(int $days = 30): int
    {
        return $this->views()
                   ->where('viewed_at', '>=', now()->subDays($days))
                   ->count();
    }

    public function getUniqueViewsInPeriod(int $days = 30): int
    {
        return $this->views()
                   ->where('viewed_at', '>=', now()->subDays($days))
                   ->distinct('ip_address')
                   ->count();
    }

    public function getEngagementRate(): float
    {
        if ($this->view_count === 0) {
            return 0;
        }
        
        return ($this->click_count / $this->view_count) * 100;
    }

    public function duplicate(): self
    {
        $duplicate = $this->replicate();
        $duplicate->title = $this->title . ' (Copy)';
        $duplicate->status = 'draft';
        $duplicate->is_featured = false;
        $duplicate->published_at = null;
        $duplicate->uuid = Str::uuid();
        $duplicate->save();
        
        // Duplicate assets
        foreach ($this->assets as $asset) {
            $newAsset = $asset->replicate();
            $newAsset->proof_id = $duplicate->id;
            $newAsset->uuid = Str::uuid();
            $newAsset->save();
        }
        
        return $duplicate;
    }

    // URL Generation
    public function getUrl(): string
    {
        return route('proofs.show', $this->uuid);
    }

    public function getEditUrl(): string
    {
        return route('proofs.edit', $this->uuid);
    }

    // JSON representation for APIs
    public function toApiArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'visibility' => $this->visibility,
            'is_featured' => $this->is_featured,
            'view_count' => $this->view_count,
            'click_count' => $this->click_count,
            'conversion_impact' => $this->conversion_impact,
            'engagement_rate' => $this->getEngagementRate(),
            'is_active' => $this->isActive(),
            'is_published' => $this->isPublished(),
            'is_expired' => $this->isExpired(),
            'has_assets' => $this->hasAssets(),
            'assets_count' => $this->assets->count(),
            'created_at' => $this->created_at,
            'published_at' => $this->published_at,
            'expires_at' => $this->expires_at,
        ];
    }
}
