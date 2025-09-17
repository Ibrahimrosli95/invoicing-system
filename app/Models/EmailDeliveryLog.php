<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailDeliveryLog extends Model
{
    protected $fillable = [
        'company_id',
        'notification_type',
        'related_model_type',
        'related_model_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'status',
        'error_message',
        'metadata',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function relatedModel(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function markAsFailed($errorMessage = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function markAsBounced($errorMessage = null)
    {
        $this->update([
            'status' => 'bounced',
            'error_message' => $errorMessage,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return in_array($this->status, ['sent', 'delivered']);
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'bounced']);
    }
}
