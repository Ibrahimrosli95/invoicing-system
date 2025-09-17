<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Services\WebhookEventService;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'team_id',
        'assigned_to',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'postal_code',
        'source',
        'status',
        'requirements',
        'estimated_value',
        'urgency',
        'is_qualified',
        'lead_score',
        'tags',
        'last_contacted_at',
        'next_follow_up_at',
        'contact_attempts',
        'converted_at',
        'lost_reason',
        'lost_notes',
        'metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'is_qualified' => 'boolean',
            'lead_score' => 'integer',
            'contact_attempts' => 'integer',
            'tags' => 'array',
            'metadata' => 'array',
            'last_contacted_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    // Lead status constants
    const STATUS_NEW = 'NEW';
    const STATUS_CONTACTED = 'CONTACTED';
    const STATUS_QUOTED = 'QUOTED';
    const STATUS_WON = 'WON';
    const STATUS_LOST = 'LOST';

    // Urgency levels
    const URGENCY_LOW = 'low';
    const URGENCY_MEDIUM = 'medium';
    const URGENCY_HIGH = 'high';

    // Lead sources
    const SOURCE_WEBSITE = 'website';
    const SOURCE_REFERRAL = 'referral';
    const SOURCE_SOCIAL_MEDIA = 'social_media';
    const SOURCE_COLD_CALL = 'cold_call';
    const SOURCE_EMAIL_CAMPAIGN = 'email_campaign';
    const SOURCE_ADVERTISEMENT = 'advertisement';
    const SOURCE_WALK_IN = 'walk_in';
    const SOURCE_OTHER = 'other';

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_QUOTED => 'Quoted',
            self::STATUS_WON => 'Won',
            self::STATUS_LOST => 'Lost',
        ];
    }

    /**
     * Get all available urgency levels.
     */
    public static function getUrgencyLevels(): array
    {
        return [
            self::URGENCY_LOW => 'Low',
            self::URGENCY_MEDIUM => 'Medium',
            self::URGENCY_HIGH => 'High',
        ];
    }

    /**
     * Get all available sources.
     */
    public static function getSources(): array
    {
        return [
            self::SOURCE_WEBSITE => 'Website',
            self::SOURCE_REFERRAL => 'Referral',
            self::SOURCE_SOCIAL_MEDIA => 'Social Media',
            self::SOURCE_COLD_CALL => 'Cold Call',
            self::SOURCE_EMAIL_CAMPAIGN => 'Email Campaign',
            self::SOURCE_ADVERTISEMENT => 'Advertisement',
            self::SOURCE_WALK_IN => 'Walk-in',
            self::SOURCE_OTHER => 'Other',
        ];
    }

    /**
     * Get the company that owns the lead.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the team this lead is assigned to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user this lead is assigned to.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the lead activities.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    /**
     * Get quotations created from this lead.
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Get assessments created from this lead.
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function proofs()
    {
        return $this->morphMany(Proof::class, 'scope')->active()->published()->notExpired();
    }

    public function allProofs()
    {
        return $this->morphMany(Proof::class, 'scope');
    }

    /**
     * Scope to get leads for a specific company.
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get leads for specific teams (based on user access).
     */
    public function scopeForUserTeams($query, $user = null)
    {
        $user = $user ?? auth()->user();
        
        if ($user->hasRole('superadmin') || $user->hasRole('company_manager')) {
            return $query; // Can see all leads in company
        }

        if ($user->hasRole('sales_manager')) {
            // Can see leads from teams they manage
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $query->whereIn('team_id', $managedTeamIds);
        }

        if ($user->hasRole('sales_coordinator')) {
            // Can see leads from teams they coordinate
            $coordinatedTeamIds = Team::where('coordinator_id', $user->id)->pluck('id');
            return $query->whereIn('team_id', $coordinatedTeamIds);
        }

        if ($user->hasRole('sales_executive')) {
            // Can only see leads assigned to them
            return $query->where('assigned_to', $user->id);
        }

        return $query->where('id', null); // No access by default
    }

    /**
     * Scope to get leads by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get leads that need follow-up.
     */
    public function scopeNeedsFollowUp($query)
    {
        return $query->where('next_follow_up_at', '<=', now())
                    ->whereIn('status', [self::STATUS_NEW, self::STATUS_CONTACTED]);
    }

    /**
     * Scope to get overdue leads.
     */
    public function scopeOverdue($query)
    {
        return $query->where('next_follow_up_at', '<', now())
                    ->whereIn('status', [self::STATUS_NEW, self::STATUS_CONTACTED]);
    }

    /**
     * Check if lead is new.
     */
    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    /**
     * Check if lead is qualified.
     */
    public function isQualified(): bool
    {
        return $this->is_qualified;
    }

    /**
     * Check if lead is won.
     */
    public function isWon(): bool
    {
        return $this->status === self::STATUS_WON;
    }

    /**
     * Check if lead is lost.
     */
    public function isLost(): bool
    {
        return $this->status === self::STATUS_LOST;
    }

    /**
     * Check if lead needs follow-up.
     */
    public function needsFollowUp(): bool
    {
        return $this->next_follow_up_at && 
               $this->next_follow_up_at <= now() &&
               !$this->isWon() && 
               !$this->isLost();
    }

    /**
     * Check if lead is overdue for follow-up.
     */
    public function isOverdue(): bool
    {
        return $this->next_follow_up_at && 
               $this->next_follow_up_at < now() &&
               !$this->isWon() && 
               !$this->isLost();
    }

    /**
     * Get the status color for UI display.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_NEW => 'blue',
            self::STATUS_CONTACTED => 'yellow',
            self::STATUS_QUOTED => 'purple',
            self::STATUS_WON => 'green',
            self::STATUS_LOST => 'red',
            default => 'gray',
        };
    }

    /**
     * Get the urgency color for UI display.
     */
    public function getUrgencyColor(): string
    {
        return match($this->urgency) {
            self::URGENCY_HIGH => 'red',
            self::URGENCY_MEDIUM => 'yellow',
            self::URGENCY_LOW => 'green',
            default => 'gray',
        };
    }

    /**
     * Mark lead as contacted.
     */
    public function markAsContacted(Carbon $contactedAt = null): void
    {
        $this->update([
            'status' => self::STATUS_CONTACTED,
            'last_contacted_at' => $contactedAt ?? now(),
            'contact_attempts' => $this->contact_attempts + 1,
        ]);
    }

    /**
     * Mark lead as quoted.
     */
    public function markAsQuoted(): void
    {
        $this->update([
            'status' => self::STATUS_QUOTED,
        ]);
    }

    /**
     * Mark lead as won (converted).
     */
    public function markAsWon(Carbon $convertedAt = null): void
    {
        $this->update([
            'status' => self::STATUS_WON,
            'converted_at' => $convertedAt ?? now(),
        ]);
    }

    /**
     * Mark lead as lost.
     */
    public function markAsLost(string $reason, string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_LOST,
            'lost_reason' => $reason,
            'lost_notes' => $notes,
        ]);
    }

    /**
     * Schedule next follow-up.
     */
    public function scheduleFollowUp(Carbon $followUpAt): void
    {
        $this->update([
            'next_follow_up_at' => $followUpAt,
        ]);
    }

    /**
     * Update lead score.
     */
    public function updateScore(int $score): void
    {
        $score = max(0, min(100, $score)); // Ensure score is between 0-100
        $this->update(['lead_score' => $score]);
    }

    /**
     * Get formatted phone number for display.
     */
    public function getFormattedPhoneAttribute(): string
    {
        // Basic Malaysian phone formatting
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        
        if (strlen($phone) === 10 && substr($phone, 0, 1) === '0') {
            // Format: 012-345 6789
            return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . ' ' . substr($phone, 6);
        }
        
        if (strlen($phone) === 11 && substr($phone, 0, 2) === '60') {
            // Format: +60 12-345 6789  
            return '+60 ' . substr($phone, 2, 2) . '-' . substr($phone, 4, 3) . ' ' . substr($phone, 7);
        }
        
        return $this->phone; // Return original if no pattern matches
    }

    /**
     * Boot the model and set up event listeners.
     */
    protected static function booted()
    {
        // Webhook event for lead creation
        static::created(function ($lead) {
            $webhookService = app(WebhookEventService::class);
            $webhookService->leadCreated($lead);
        });

        // Listen for status changes and updates
        static::updated(function ($lead) {
            $webhookService = app(WebhookEventService::class);
            
            // Dispatch webhook event for any updates
            $changes = [];
            foreach ($lead->getDirty() as $key => $value) {
                $changes[$key] = [
                    'old' => $lead->getOriginal($key),
                    'new' => $value,
                ];
            }
            
            if (!empty($changes)) {
                $webhookService->leadUpdated($lead, $changes);
            }

            if ($lead->isDirty('status')) {
                $oldStatus = $lead->getOriginal('status');
                $newStatus = $lead->status;
                $lead->handleStatusChanged();
                
                // Dispatch webhook event for status change
                $webhookService->leadStatusChanged($lead, $oldStatus, $newStatus);
            }

            if ($lead->isDirty('assigned_to')) {
                $oldAssignedTo = $lead->getOriginal('assigned_to');
                $newAssignedTo = $lead->assigned_to;
                $lead->handleAssignmentChanged();
                
                // Dispatch webhook event for assignment change
                if ($newAssignedTo && $newAssignedTo !== $oldAssignedTo) {
                    $previousRep = $oldAssignedTo ? User::find($oldAssignedTo) : null;
                    $newRep = User::find($newAssignedTo);
                    if ($newRep) {
                        $webhookService->leadAssigned($lead, $previousRep, $newRep);
                    }
                }
            }
        });
    }

    /**
     * Handle lead status change notifications.
     */
    protected function handleStatusChanged()
    {
        $oldStatus = $this->getOriginal('status');
        $newStatus = $this->status;

        if ($oldStatus !== $newStatus) {
            // Notify team members based on their roles
            $this->notifyTeamOnStatusChange($oldStatus, $newStatus);
        }
    }

    /**
     * Handle lead assignment change notifications.
     */
    protected function handleAssignmentChanged()
    {
        $oldAssignedTo = $this->getOriginal('assigned_to');
        $newAssignedTo = $this->assigned_to;

        // Notify the newly assigned user
        if ($newAssignedTo && $newAssignedTo !== $oldAssignedTo) {
            $assignedUser = User::find($newAssignedTo);
            if ($assignedUser) {
                $assignedUser->notify(new \App\Notifications\LeadAssignedNotification($this));
            }
        }
    }

    /**
     * Notify team members about status changes.
     */
    protected function notifyTeamOnStatusChange($oldStatus, $newStatus)
    {
        // Get team members who should be notified
        $teamMembers = $this->team->users()->get();

        foreach ($teamMembers as $member) {
            // Skip if member doesn't want status change notifications
            if (!$member->wantsEmailNotification('lead_status_changed')) {
                continue;
            }

            // Only notify if they have permission to view this lead
            if ($member->can('view', $this)) {
                $member->notify(new \App\Notifications\LeadStatusChangedNotification($this, $oldStatus, $newStatus));
            }
        }
    }

    /**
     * Manually trigger assignment notification.
     */
    public function notifyAssignment($assignedBy = null)
    {
        if ($this->assigned_to) {
            $assignedUser = $this->assignedTo;
            if ($assignedUser) {
                $assignedUser->notify(new \App\Notifications\LeadAssignedNotification($this, $assignedBy));
            }
        }
    }

    /**
     * Get conversion metrics for this lead.
     */
    public function getConversionMetrics(): array
    {
        $quotations = $this->quotations()->get();
        $acceptedQuotations = $quotations->where('status', 'ACCEPTED');
        $convertedQuotations = $quotations->where('status', 'CONVERTED');

        return [
            'quotations_count' => $quotations->count(),
            'total_quoted_value' => $quotations->sum('total'),
            'accepted_quotations' => $acceptedQuotations->count(),
            'accepted_value' => $acceptedQuotations->sum('total'),
            'converted_quotations' => $convertedQuotations->count(),
            'converted_value' => $convertedQuotations->sum('total'),
            'conversion_rate' => $quotations->count() > 0 ? ($acceptedQuotations->count() / $quotations->count()) * 100 : 0,
        ];
    }

    /**
     * Check if lead has been converted to quotations.
     */
    public function hasQuotations(): bool
    {
        return $this->quotations()->exists();
    }

    /**
     * Get the latest quotation for this lead.
     */
    public function latestQuotation()
    {
        return $this->quotations()->latest()->first();
    }
}
