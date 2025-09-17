<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_type',
        'email_enabled',
        'push_enabled',
        'settings',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'push_enabled' => 'boolean',
        'settings' => 'array',
    ];

    // Available notification types
    const TYPES = [
        // Lead notifications
        'lead_assigned' => 'Lead Assigned to Me',
        'lead_status_changed' => 'Lead Status Changes',
        'lead_new_activity' => 'New Lead Activity',
        
        // Quotation notifications
        'quotation_created' => 'New Quotation Created',
        'quotation_sent' => 'Quotation Sent to Customer',
        'quotation_accepted' => 'Quotation Accepted',
        'quotation_rejected' => 'Quotation Rejected',
        'quotation_expires_soon' => 'Quotation Expires Soon',
        
        // Invoice notifications
        'invoice_created' => 'New Invoice Created',
        'invoice_sent' => 'Invoice Sent to Customer',
        'invoice_payment_received' => 'Payment Received',
        'invoice_overdue' => 'Invoice Overdue',
        'invoice_reminder' => 'Payment Reminders',
        
        // Team notifications
        'team_assignment' => 'Team Assignment Changes',
        'team_performance' => 'Team Performance Updates',
        
        // System notifications
        'system_maintenance' => 'System Maintenance',
        'system_updates' => 'System Updates',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeEnabled($query)
    {
        return $query->where('email_enabled', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    public static function getDefaultPreferences(): array
    {
        $defaults = [];
        foreach (self::TYPES as $type => $label) {
            $defaults[$type] = [
                'email_enabled' => true,
                'push_enabled' => true,
                'settings' => self::getDefaultSettings($type),
            ];
        }
        return $defaults;
    }

    public static function getDefaultSettings(string $type): array
    {
        return match ($type) {
            'invoice_reminder' => [
                'frequency' => 'weekly',
                'days_before' => [7, 3, 1],
            ],
            'quotation_expires_soon' => [
                'days_before' => 3,
            ],
            'team_performance' => [
                'frequency' => 'weekly',
            ],
            default => [],
        };
    }

    public function isEmailEnabled(): bool
    {
        return $this->email_enabled;
    }

    public function isPushEnabled(): bool
    {
        return $this->push_enabled;
    }

    public function toggleEmail(): void
    {
        $this->update(['email_enabled' => !$this->email_enabled]);
    }

    public function togglePush(): void
    {
        $this->update(['push_enabled' => !$this->push_enabled]);
    }
}
