<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->auditCreated();
        });

        static::updated(function ($model) {
            $model->auditUpdated();
        });

        static::deleted(function ($model) {
            $model->auditDeleted();
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->auditRestored();
            });
        }
    }

    /**
     * Get all audit logs for this model.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest();
    }

    /**
     * Get recent audit logs (last 10 by default).
     */
    public function recentAuditLogs(int $limit = 10): MorphMany
    {
        return $this->auditLogs()->limit($limit);
    }

    /**
     * Handle the created event.
     */
    protected function auditCreated(): void
    {
        if ($this->shouldAudit('created')) {
            AuditLog::record(
                AuditLog::EVENT_CREATED,
                $this,
                null,
                null,
                $this->getAuditableAttributes(),
                $this->getAuditMetadata('created')
            );
        }
    }

    /**
     * Handle the updated event.
     */
    protected function auditUpdated(): void
    {
        if ($this->shouldAudit('updated') && $this->wasChanged()) {
            $oldValues = [];
            $newValues = [];

            foreach ($this->getDirty() as $key => $newValue) {
                if ($this->shouldAuditAttribute($key)) {
                    $oldValues[$key] = $this->getOriginal($key);
                    $newValues[$key] = $newValue;
                }
            }

            if (!empty($oldValues) || !empty($newValues)) {
                AuditLog::record(
                    AuditLog::EVENT_UPDATED,
                    $this,
                    $this->getAuditAction('updated'),
                    $oldValues,
                    $newValues,
                    $this->getAuditMetadata('updated')
                );
            }
        }
    }

    /**
     * Handle the deleted event.
     */
    protected function auditDeleted(): void
    {
        if ($this->shouldAudit('deleted')) {
            AuditLog::record(
                AuditLog::EVENT_DELETED,
                $this,
                null,
                $this->getAuditableAttributes(),
                null,
                $this->getAuditMetadata('deleted')
            );
        }
    }

    /**
     * Handle the restored event.
     */
    protected function auditRestored(): void
    {
        if ($this->shouldAudit('restored')) {
            AuditLog::record(
                AuditLog::EVENT_RESTORED,
                $this,
                null,
                null,
                $this->getAuditableAttributes(),
                $this->getAuditMetadata('restored')
            );
        }
    }

    /**
     * Determine if the model should be audited for the given event.
     */
    protected function shouldAudit(string $event): bool
    {
        // Check if auditing is globally disabled
        if (property_exists($this, 'auditEnabled') && !$this->auditEnabled) {
            return false;
        }

        // Check if this specific event should be audited
        if (property_exists($this, 'auditEvents')) {
            return in_array($event, $this->auditEvents);
        }

        // Default: audit all events
        return true;
    }

    /**
     * Determine if a specific attribute should be audited.
     */
    protected function shouldAuditAttribute(string $attribute): bool
    {
        // Skip certain attributes that shouldn't be audited
        $skipAttributes = [
            'updated_at',
            'created_at',
            'deleted_at',
            'password',
            'remember_token',
            'email_verified_at',
        ];

        if (in_array($attribute, $skipAttributes)) {
            return false;
        }

        // Check if there are specific attributes to exclude
        if (property_exists($this, 'auditExclude')) {
            return !in_array($attribute, $this->auditExclude);
        }

        // Check if there are specific attributes to include
        if (property_exists($this, 'auditInclude')) {
            return in_array($attribute, $this->auditInclude);
        }

        // Default: audit all attributes
        return true;
    }

    /**
     * Get auditable attributes for the model.
     */
    protected function getAuditableAttributes(): array
    {
        $attributes = [];

        foreach ($this->getAttributes() as $key => $value) {
            if ($this->shouldAuditAttribute($key)) {
                $attributes[$key] = $value;
            }
        }

        return $attributes;
    }

    /**
     * Get audit action for the given event.
     */
    protected function getAuditAction(string $event): ?string
    {
        // Check for status changes that should have specific actions
        if ($event === 'updated' && $this->isDirty('status')) {
            $newStatus = $this->getAttribute('status');
            $modelClass = class_basename($this);

            // Map status changes to audit actions
            if ($modelClass === 'Quotation') {
                return match($newStatus) {
                    'SENT' => AuditLog::ACTION_QUOTATION_SENT,
                    'ACCEPTED' => AuditLog::ACTION_QUOTATION_ACCEPTED,
                    'REJECTED' => AuditLog::ACTION_QUOTATION_REJECTED,
                    default => null,
                };
            }

            if ($modelClass === 'Invoice') {
                return match($newStatus) {
                    'SENT' => AuditLog::ACTION_INVOICE_SENT,
                    'PAID' => AuditLog::ACTION_INVOICE_PAID,
                    default => null,
                };
            }

            if ($modelClass === 'Assessment') {
                return match($newStatus) {
                    'completed' => AuditLog::ACTION_ASSESSMENT_COMPLETED,
                    'reported' => AuditLog::ACTION_ASSESSMENT_REPORTED,
                    default => null,
                };
            }
        }

        // Check for assignment changes
        if ($event === 'updated' && $this->isDirty('assigned_to_id')) {
            $modelClass = class_basename($this);
            if ($modelClass === 'Lead') {
                return AuditLog::ACTION_LEAD_ASSIGNED;
            }
        }

        return null;
    }

    /**
     * Get audit metadata for the given event.
     */
    protected function getAuditMetadata(string $event): array
    {
        $metadata = [
            'model_class' => get_class($this),
            'model_table' => $this->getTable(),
            'event_time' => now()->toIso8601String(),
        ];

        // Add model-specific metadata
        if (method_exists($this, 'getAuditMetadataCustom')) {
            $metadata = array_merge($metadata, $this->getAuditMetadataCustom($event));
        }

        // Add common business metadata
        if (isset($this->attributes['number'])) {
            $metadata['record_number'] = $this->attributes['number'];
        }

        if (isset($this->attributes['customer_name'])) {
            $metadata['customer_name'] = $this->attributes['customer_name'];
        }

        if (isset($this->attributes['total'])) {
            $metadata['total_amount'] = $this->attributes['total'];
        }

        if (isset($this->attributes['status'])) {
            $metadata['status'] = $this->attributes['status'];
        }

        return $metadata;
    }

    /**
     * Record a custom audit event.
     */
    public function recordAuditEvent(
        string $event,
        ?string $action = null,
        ?array $metadata = null
    ): AuditLog {
        return AuditLog::record(
            $event,
            $this,
            $action,
            null,
            null,
            array_merge($this->getAuditMetadata($event), $metadata ?? [])
        );
    }

    /**
     * Record a business action audit event.
     */
    public function recordBusinessAction(string $action, ?array $metadata = null): AuditLog
    {
        return AuditLog::record(
            AuditLog::EVENT_UPDATED,
            $this,
            $action,
            null,
            null,
            array_merge($this->getAuditMetadata('business_action'), $metadata ?? [])
        );
    }

    /**
     * Get audit summary for this model.
     */
    public function getAuditSummary(int $limit = 10): array
    {
        $logs = $this->auditLogs()->limit($limit)->get();

        return [
            'total_changes' => $this->auditLogs()->count(),
            'last_updated' => $logs->first()?->created_at,
            'last_updated_by' => $logs->first()?->getUserDisplayName(),
            'recent_activities' => $logs->map(function ($log) {
                return [
                    'event' => $log->getEventDisplayName(),
                    'action' => $log->getActionDisplayName(),
                    'user' => $log->getUserDisplayName(),
                    'time' => $log->created_at,
                    'changes' => $log->getChangesSummary(),
                ];
            }),
        ];
    }

    /**
     * Temporarily disable auditing for this model instance.
     */
    public function withoutAuditing(callable $callback)
    {
        $originalValue = $this->auditEnabled ?? true;
        $this->auditEnabled = false;

        try {
            return $callback();
        } finally {
            $this->auditEnabled = $originalValue;
        }
    }

    /**
     * Enable auditing for this model instance.
     */
    public function enableAuditing(): void
    {
        $this->auditEnabled = true;
    }

    /**
     * Disable auditing for this model instance.
     */
    public function disableAuditing(): void
    {
        $this->auditEnabled = false;
    }
}