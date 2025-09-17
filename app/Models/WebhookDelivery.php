<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class WebhookDelivery extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';
    const STATUS_RETRYING = 'retrying';

    protected $fillable = [
        'webhook_endpoint_id',
        'event_type',
        'payload',
        'status',
        'http_status_code',
        'response_body',
        'error_message',
        'attempts',
        'sent_at',
        'next_retry_at',
        'signature',
        'response_time_ms',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'attempts' => 'integer',
        'response_time_ms' => 'integer',
    ];

    // Relationships
    public function webhookEndpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeRetryable($query)
    {
        return $query->where('status', self::STATUS_RETRYING)
                    ->where('next_retry_at', '<=', now());
    }

    public function scopeRecentDeliveries($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Methods
    public function markAsSent(int $httpStatusCode, string $responseBody = null, int $responseTime = null): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'http_status_code' => $httpStatusCode,
            'response_body' => $responseBody,
            'sent_at' => now(),
            'response_time_ms' => $responseTime,
            'error_message' => null,
        ]);

        $this->webhookEndpoint->recordSuccess();
    }

    public function markAsFailed(string $errorMessage, int $httpStatusCode = null, string $responseBody = null): void
    {
        $this->increment('attempts');

        $canRetry = $this->attempts < $this->webhookEndpoint->max_retries;
        
        $this->update([
            'status' => $canRetry ? self::STATUS_RETRYING : self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'http_status_code' => $httpStatusCode,
            'response_body' => $responseBody,
            'next_retry_at' => $canRetry ? $this->calculateNextRetryTime() : null,
        ]);

        if (!$canRetry) {
            $this->webhookEndpoint->recordFailure();
        }
    }

    public function calculateNextRetryTime(): Carbon
    {
        // Exponential backoff: 1min, 5min, 15min, 1hr, 4hr
        $delays = [60, 300, 900, 3600, 14400]; // seconds
        $delayIndex = min($this->attempts - 1, count($delays) - 1);
        
        return now()->addSeconds($delays[$delayIndex]);
    }

    public function isRetryable(): bool
    {
        return $this->status === self::STATUS_RETRYING 
               && $this->attempts < $this->webhookEndpoint->max_retries
               && $this->next_retry_at <= now();
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SENT => 'green',
            self::STATUS_PENDING => 'blue',
            self::STATUS_RETRYING => 'yellow',
            self::STATUS_FAILED => 'red',
            default => 'gray'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_SENT => 'Delivered',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_RETRYING => 'Retrying',
            self::STATUS_FAILED => 'Failed',
            default => 'Unknown'
        };
    }

    public function getIsSuccessfulAttribute(): bool
    {
        return $this->status === self::STATUS_SENT && 
               $this->http_status_code >= 200 && 
               $this->http_status_code < 300;
    }

    public function getFormattedResponseTimeAttribute(): string
    {
        if (!$this->response_time_ms) {
            return 'N/A';
        }

        if ($this->response_time_ms < 1000) {
            return $this->response_time_ms . 'ms';
        }

        return round($this->response_time_ms / 1000, 2) . 's';
    }
}
