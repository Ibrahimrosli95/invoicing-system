<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'user_name',
        'auditable_type',
        'auditable_id',
        'event',
        'action',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'session_id',
        'batch_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Event constants
    const EVENT_CREATED = 'created';
    const EVENT_UPDATED = 'updated';
    const EVENT_DELETED = 'deleted';
    const EVENT_RESTORED = 'restored';
    const EVENT_LOGIN = 'login';
    const EVENT_LOGOUT = 'logout';
    const EVENT_FAILED_LOGIN = 'failed_login';
    const EVENT_PASSWORD_RESET = 'password_reset';
    const EVENT_PERMISSION_DENIED = 'permission_denied';

    // Action constants for business events
    const ACTION_QUOTATION_SENT = 'sent_quotation';
    const ACTION_QUOTATION_ACCEPTED = 'accepted_quotation';
    const ACTION_QUOTATION_REJECTED = 'rejected_quotation';
    const ACTION_INVOICE_SENT = 'sent_invoice';
    const ACTION_INVOICE_PAID = 'paid_invoice';
    const ACTION_PAYMENT_RECORDED = 'recorded_payment';
    const ACTION_LEAD_ASSIGNED = 'assigned_lead';
    const ACTION_LEAD_STATUS_CHANGED = 'changed_lead_status';
    const ACTION_ASSESSMENT_COMPLETED = 'completed_assessment';
    const ACTION_ASSESSMENT_REPORTED = 'reported_assessment';

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeForCompany(Builder $query, ?int $companyId = null): Builder
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        return $query->where('company_id', $companyId);
    }

    public function scopeForUser(Builder $query, ?int $userId = null): Builder
    {
        $userId = $userId ?? auth()->id();
        return $query->where('user_id', $userId);
    }

    public function scopeForModel(Builder $query, string $modelType, int $modelId): Builder
    {
        return $query->where('auditable_type', $modelType)
                    ->where('auditable_id', $modelId);
    }

    public function scopeForEvent(Builder $query, string $event): Builder
    {
        return $query->where('event', $event);
    }

    public function scopeForAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeInDateRange(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function scopeWithBatch(Builder $query, string $batchId): Builder
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Business Logic Methods
     */
    public static function record(
        string $event,
        Model $model,
        ?string $action = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $metadata = null,
        ?string $batchId = null
    ): self {
        $user = auth()->user();
        $request = request();

        return static::create([
            'company_id' => $user?->company_id ?? $model->company_id ?? null,
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'event' => $event,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->header('User-Agent'),
            'url' => $request?->fullUrl(),
            'method' => $request?->method(),
            'session_id' => session()->getId(),
            'batch_id' => $batchId,
        ]);
    }

    public static function recordLogin(User $user, bool $successful = true): self
    {
        return static::record(
            $successful ? self::EVENT_LOGIN : self::EVENT_FAILED_LOGIN,
            $user,
            null,
            null,
            null,
            [
                'successful' => $successful,
                'login_time' => now()->toIso8601String(),
            ]
        );
    }

    public static function recordLogout(User $user): self
    {
        return static::record(
            self::EVENT_LOGOUT,
            $user,
            null,
            null,
            null,
            [
                'logout_time' => now()->toIso8601String(),
            ]
        );
    }

    public static function recordPermissionDenied(string $action, ?Model $model = null): self
    {
        $dummyModel = $model ?? new User(); // Fallback model for permission logging

        return static::record(
            self::EVENT_PERMISSION_DENIED,
            $dummyModel,
            null,
            null,
            null,
            [
                'attempted_action' => $action,
                'denied_time' => now()->toIso8601String(),
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model?->getKey(),
            ]
        );
    }

    /**
     * Business event recording methods
     */
    public static function recordQuotationSent(Model $quotation): self
    {
        return static::record(
            self::EVENT_UPDATED,
            $quotation,
            self::ACTION_QUOTATION_SENT,
            null,
            ['status' => 'SENT'],
            [
                'quotation_number' => $quotation->number ?? null,
                'customer_name' => $quotation->customer_name ?? null,
                'total_amount' => $quotation->total ?? null,
                'sent_time' => now()->toIso8601String(),
            ]
        );
    }

    public static function recordQuotationAccepted(Model $quotation): self
    {
        return static::record(
            self::EVENT_UPDATED,
            $quotation,
            self::ACTION_QUOTATION_ACCEPTED,
            ['status' => 'SENT'],
            ['status' => 'ACCEPTED'],
            [
                'quotation_number' => $quotation->number ?? null,
                'customer_name' => $quotation->customer_name ?? null,
                'total_amount' => $quotation->total ?? null,
                'accepted_time' => now()->toIso8601String(),
            ]
        );
    }

    public static function recordInvoicePaid(Model $invoice, Model $payment): self
    {
        return static::record(
            self::EVENT_UPDATED,
            $invoice,
            self::ACTION_INVOICE_PAID,
            ['status' => 'SENT'],
            ['status' => 'PAID'],
            [
                'invoice_number' => $invoice->number ?? null,
                'payment_amount' => $payment->amount ?? null,
                'payment_method' => $payment->method ?? null,
                'payment_reference' => $payment->reference ?? null,
                'paid_time' => now()->toIso8601String(),
            ]
        );
    }

    public static function recordLeadAssigned(Model $lead, Model $assignedTo): self
    {
        return static::record(
            self::EVENT_UPDATED,
            $lead,
            self::ACTION_LEAD_ASSIGNED,
            ['assigned_to_id' => $lead->getOriginal('assigned_to_id')],
            ['assigned_to_id' => $assignedTo->id],
            [
                'lead_name' => $lead->customer_name ?? null,
                'assigned_to_name' => $assignedTo->name ?? null,
                'assigned_time' => now()->toIso8601String(),
            ]
        );
    }

    /**
     * Analysis and reporting methods
     */
    public function getChangedFields(): array
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $field => $newValue) {
            $oldValue = $this->old_values[$field] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    public function getChangesSummary(): string
    {
        $changes = $this->getChangedFields();
        if (empty($changes)) {
            return 'No field changes recorded';
        }

        $summaries = [];
        foreach ($changes as $field => $change) {
            $summaries[] = sprintf(
                '%s: %s → %s',
                ucfirst(str_replace('_', ' ', $field)),
                $change['old'] ?? 'null',
                $change['new'] ?? 'null'
            );
        }

        return implode(', ', $summaries);
    }

    public function getUserDisplayName(): string
    {
        return $this->user?->name ?? $this->user_name ?? 'System';
    }

    public function getModelDisplayName(): string
    {
        $className = class_basename($this->auditable_type);
        return $className . ' #' . $this->auditable_id;
    }

    public function getEventDisplayName(): string
    {
        return match($this->event) {
            self::EVENT_CREATED => 'Created',
            self::EVENT_UPDATED => 'Updated',
            self::EVENT_DELETED => 'Deleted',
            self::EVENT_RESTORED => 'Restored',
            self::EVENT_LOGIN => 'Logged In',
            self::EVENT_LOGOUT => 'Logged Out',
            self::EVENT_FAILED_LOGIN => 'Failed Login',
            self::EVENT_PASSWORD_RESET => 'Password Reset',
            self::EVENT_PERMISSION_DENIED => 'Permission Denied',
            default => ucfirst($this->event),
        };
    }

    public function getActionDisplayName(): string
    {
        if (!$this->action) {
            return '';
        }

        return match($this->action) {
            self::ACTION_QUOTATION_SENT => 'Quotation Sent',
            self::ACTION_QUOTATION_ACCEPTED => 'Quotation Accepted',
            self::ACTION_QUOTATION_REJECTED => 'Quotation Rejected',
            self::ACTION_INVOICE_SENT => 'Invoice Sent',
            self::ACTION_INVOICE_PAID => 'Invoice Paid',
            self::ACTION_PAYMENT_RECORDED => 'Payment Recorded',
            self::ACTION_LEAD_ASSIGNED => 'Lead Assigned',
            self::ACTION_LEAD_STATUS_CHANGED => 'Lead Status Changed',
            self::ACTION_ASSESSMENT_COMPLETED => 'Assessment Completed',
            self::ACTION_ASSESSMENT_REPORTED => 'Assessment Reported',
            default => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    /**
     * Statistics and analytics
     */
    public static function getActivityStats(?int $companyId = null, int $days = 30): array
    {
        $query = static::query()
            ->forCompany($companyId)
            ->where('created_at', '>=', now()->subDays($days));

        return [
            'total_activities' => $query->count(),
            'users_active' => $query->distinct('user_id')->count('user_id'),
            'models_affected' => $query->distinct('auditable_type')->count('auditable_type'),
            'events_breakdown' => $query->groupBy('event')
                ->selectRaw('event, count(*) as count')
                ->pluck('count', 'event')
                ->toArray(),
            'actions_breakdown' => $query->whereNotNull('action')
                ->groupBy('action')
                ->selectRaw('action, count(*) as count')
                ->pluck('count', 'action')
                ->toArray(),
            'daily_activity' => $query->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray(),
        ];
    }

    public static function getUserActivitySummary(int $userId, int $days = 30): array
    {
        $query = static::query()
            ->forUser($userId)
            ->where('created_at', '>=', now()->subDays($days));

        return [
            'total_activities' => $query->count(),
            'models_modified' => $query->distinct('auditable_type')->count('auditable_type'),
            'most_active_day' => $query->selectRaw('DATE(created_at) as date, count(*) as count')
                ->groupBy('date')
                ->orderByDesc('count')
                ->first()?->date,
            'recent_activities' => $query->with(['auditable'])
                ->latest()
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Cleanup and maintenance
     */
    public static function cleanupOldLogs(int $retentionDays = 365): int
    {
        $cutoffDate = now()->subDays($retentionDays);

        return static::where('created_at', '<', $cutoffDate)->delete();
    }

    public static function archiveOldLogs(int $archiveDays = 90): int
    {
        // This would typically move records to an archive table
        // For now, we'll just return the count that would be archived
        $cutoffDate = now()->subDays($archiveDays);

        return static::where('created_at', '<', $cutoffDate)->count();
    }
}