<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProofView extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'proof_id',
        'company_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'source',
        'document_type',
        'document_id',
        'duration_seconds',
        'clicked_asset',
        'downloaded_asset',
        'shared',
        'country',
        'city',
        'region',
        'device_type',
        'browser',
        'platform',
        'referrer_url',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'viewed_at',
    ];

    protected $casts = [
        'clicked_asset' => 'boolean',
        'downloaded_asset' => 'boolean',
        'shared' => 'boolean',
        'duration_seconds' => 'integer',
        'viewed_at' => 'datetime',
    ];

    protected $attributes = [
        'duration_seconds' => 0,
        'clicked_asset' => false,
        'downloaded_asset' => false,
        'shared' => false,
        'device_type' => 'unknown',
    ];

    // Constants for source tracking
    const SOURCES = [
        'quotation_pdf' => 'Quotation PDF',
        'invoice_pdf' => 'Invoice PDF',
        'web_interface' => 'Web Interface',
        'mobile_app' => 'Mobile App',
        'email_link' => 'Email Link',
        'direct_link' => 'Direct Link',
        'customer_portal' => 'Customer Portal',
    ];

    // Constants for device types
    const DEVICE_TYPES = [
        'desktop' => 'Desktop',
        'tablet' => 'Tablet',
        'mobile' => 'Mobile',
        'unknown' => 'Unknown',
    ];

    // Boot method to handle UUID generation
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
            
            if (empty($model->viewed_at)) {
                $model->viewed_at = now();
            }

            // Auto-detect device type from user agent
            if (empty($model->device_type) && !empty($model->user_agent)) {
                $model->device_type = self::detectDeviceType($model->user_agent);
            }

            // Auto-detect browser and platform
            if (!empty($model->user_agent)) {
                $browserInfo = self::parseBrowserInfo($model->user_agent);
                $model->browser = $browserInfo['browser'];
                $model->platform = $browserInfo['platform'];
            }
        });
    }

    // Relationships
    public function proof(): BelongsTo
    {
        return $this->belongsTo(Proof::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeForCompany(Builder $query): Builder
    {
        if (auth()->check()) {
            return $query->where('company_id', auth()->user()->company_id);
        }
        return $query;
    }

    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    public function scopeByDeviceType(Builder $query, string $deviceType): Builder
    {
        return $query->where('device_type', $deviceType);
    }

    public function scopeByUser(Builder $query, ?int $userId): Builder
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        } else {
            return $query->whereNull('user_id');
        }
    }

    public function scopeAnonymous(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    public function scopeRegistered(Builder $query): Builder
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeWithEngagement(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('clicked_asset', true)
              ->orWhere('downloaded_asset', true)
              ->orWhere('shared', true)
              ->orWhere('duration_seconds', '>', 10);
        });
    }

    public function scopeInPeriod(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('viewed_at', [$start, $end]);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('viewed_at', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('viewed_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('viewed_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    // Business Logic Methods
    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? 'Unknown';
    }

    public function getDeviceTypeLabelAttribute(): string
    {
        return self::DEVICE_TYPES[$this->device_type] ?? 'Unknown';
    }

    public function isAnonymous(): bool
    {
        return $this->user_id === null;
    }

    public function isRegisteredUser(): bool
    {
        return $this->user_id !== null;
    }

    public function hasEngagement(): bool
    {
        return $this->clicked_asset || 
               $this->downloaded_asset || 
               $this->shared || 
               $this->duration_seconds > 10;
    }

    public function getDurationFormatted(): string
    {
        if ($this->duration_seconds < 60) {
            return $this->duration_seconds . ' seconds';
        }
        
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        
        return $minutes . 'm ' . $seconds . 's';
    }

    public function getLocationString(): ?string
    {
        $parts = array_filter([$this->city, $this->region, $this->country]);
        
        return !empty($parts) ? implode(', ', $parts) : null;
    }

    public function recordClick(): bool
    {
        return $this->update(['clicked_asset' => true]);
    }

    public function recordDownload(): bool
    {
        return $this->update(['downloaded_asset' => true]);
    }

    public function recordShare(): bool
    {
        return $this->update(['shared' => true]);
    }

    public function updateDuration(int $seconds): bool
    {
        return $this->update(['duration_seconds' => max($this->duration_seconds, $seconds)]);
    }

    // Static helper methods
    public static function detectDeviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);
        
        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android')) {
            return 'mobile';
        } elseif (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        } elseif (str_contains($userAgent, 'windows') || str_contains($userAgent, 'macintosh') || str_contains($userAgent, 'linux')) {
            return 'desktop';
        }
        
        return 'unknown';
    }

    public static function parseBrowserInfo(string $userAgent): array
    {
        $userAgent = strtolower($userAgent);
        
        // Detect browser
        if (str_contains($userAgent, 'chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'safari') && !str_contains($userAgent, 'chrome')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'edge')) {
            $browser = 'Edge';
        } elseif (str_contains($userAgent, 'opera')) {
            $browser = 'Opera';
        } else {
            $browser = 'Unknown';
        }
        
        // Detect platform
        if (str_contains($userAgent, 'windows')) {
            $platform = 'Windows';
        } elseif (str_contains($userAgent, 'macintosh') || str_contains($userAgent, 'mac os')) {
            $platform = 'macOS';
        } elseif (str_contains($userAgent, 'linux')) {
            $platform = 'Linux';
        } elseif (str_contains($userAgent, 'android')) {
            $platform = 'Android';
        } elseif (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            $platform = 'iOS';
        } else {
            $platform = 'Unknown';
        }
        
        return [
            'browser' => $browser,
            'platform' => $platform,
        ];
    }

    // Analytics helper methods
    public static function getTopSources(int $limit = 5): array
    {
        return static::selectRaw('source, COUNT(*) as count')
                    ->groupBy('source')
                    ->orderBy('count', 'desc')
                    ->limit($limit)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [self::SOURCES[$item->source] ?? $item->source => $item->count];
                    })
                    ->toArray();
    }

    public static function getTopDeviceTypes(int $limit = 5): array
    {
        return static::selectRaw('device_type, COUNT(*) as count')
                    ->groupBy('device_type')
                    ->orderBy('count', 'desc')
                    ->limit($limit)
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [self::DEVICE_TYPES[$item->device_type] ?? $item->device_type => $item->count];
                    })
                    ->toArray();
    }

    public static function getEngagementStats(): array
    {
        $total = static::count();
        $engaged = static::withEngagement()->count();
        
        return [
            'total_views' => $total,
            'engaged_views' => $engaged,
            'engagement_rate' => $total > 0 ? round(($engaged / $total) * 100, 2) : 0,
        ];
    }

    // JSON representation for APIs
    public function toApiArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'proof_id' => $this->proof_id,
            'user_id' => $this->user_id,
            'is_anonymous' => $this->isAnonymous(),
            'source' => $this->source,
            'source_label' => $this->source_label,
            'device_type' => $this->device_type,
            'device_type_label' => $this->device_type_label,
            'browser' => $this->browser,
            'platform' => $this->platform,
            'location' => $this->getLocationString(),
            'duration_seconds' => $this->duration_seconds,
            'duration_formatted' => $this->getDurationFormatted(),
            'clicked_asset' => $this->clicked_asset,
            'downloaded_asset' => $this->downloaded_asset,
            'shared' => $this->shared,
            'has_engagement' => $this->hasEngagement(),
            'utm_source' => $this->utm_source,
            'utm_medium' => $this->utm_medium,
            'utm_campaign' => $this->utm_campaign,
            'viewed_at' => $this->viewed_at,
        ];
    }
}
