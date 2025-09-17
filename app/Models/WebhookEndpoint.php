<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WebhookEndpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'url',
        'secret_key',
        'events',
        'is_active',
        'description',
        'timeout',
        'max_retries',
        'headers',
        'last_ping_at',
        'last_ping_status',
        'success_count',
        'failure_count',
    ];

    protected $casts = [
        'events' => 'array',
        'headers' => 'array',
        'is_active' => 'boolean',
        'last_ping_at' => 'datetime',
        'timeout' => 'integer',
        'max_retries' => 'integer',
        'success_count' => 'integer',
        'failure_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($endpoint) {
            if (empty($endpoint->secret_key)) {
                $endpoint->secret_key = 'wh_' . Str::random(32);
            }
        });
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?? auth()->user()->company_id;
        return $query->where('company_id', $companyId);
    }

    public function scopeSubscribedTo($query, $eventType)
    {
        return $query->whereJsonContains('events', $eventType);
    }

    // Methods
    public function isSubscribedTo(string $eventType): bool
    {
        return in_array($eventType, $this->events ?? []);
    }

    public function generateNewSecretKey(): string
    {
        $this->secret_key = 'wh_' . Str::random(32);
        $this->save();
        
        return $this->secret_key;
    }

    public function recordSuccess(): void
    {
        $this->increment('success_count');
        $this->update([
            'last_ping_at' => now(),
            'last_ping_status' => 'success',
        ]);
    }

    public function recordFailure(): void
    {
        $this->increment('failure_count');
        $this->update([
            'last_ping_at' => now(),
            'last_ping_status' => 'failed',
        ]);
    }

    public function getSuccessRateAttribute(): float
    {
        $total = $this->success_count + $this->failure_count;
        return $total > 0 ? ($this->success_count / $total) * 100 : 0;
    }

    public function getHealthStatusAttribute(): string
    {
        $rate = $this->success_rate;
        
        if ($rate >= 95) return 'excellent';
        if ($rate >= 80) return 'good';
        if ($rate >= 60) return 'warning';
        return 'critical';
    }

    public function getHealthColorAttribute(): string
    {
        return match($this->health_status) {
            'excellent' => 'green',
            'good' => 'blue',
            'warning' => 'yellow',
            'critical' => 'red',
            default => 'gray'
        };
    }

    public static function getAvailableEvents(): array
    {
        return [
            'lead.created' => 'Lead Created',
            'lead.updated' => 'Lead Updated',
            'lead.assigned' => 'Lead Assigned',
            'lead.status.changed' => 'Lead Status Changed',
            'quotation.created' => 'Quotation Created',
            'quotation.sent' => 'Quotation Sent',
            'quotation.viewed' => 'Quotation Viewed',
            'quotation.accepted' => 'Quotation Accepted',
            'quotation.rejected' => 'Quotation Rejected',
            'quotation.expired' => 'Quotation Expired',
            'invoice.created' => 'Invoice Created',
            'invoice.sent' => 'Invoice Sent',
            'invoice.paid' => 'Invoice Paid',
            'invoice.overdue' => 'Invoice Overdue',
            'payment.received' => 'Payment Received',
            'payment.failed' => 'Payment Failed',
            'user.created' => 'User Created',
            'user.updated' => 'User Updated',
        ];
    }

    public function getSubscribedEventsLabelsAttribute(): array
    {
        $availableEvents = self::getAvailableEvents();
        return collect($this->events ?? [])
            ->map(fn($event) => $availableEvents[$event] ?? $event)
            ->toArray();
    }
}
