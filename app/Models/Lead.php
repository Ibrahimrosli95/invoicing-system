<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Services\WebhookEventService;
use App\Models\LeadActivity;

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
        // Contact tracking fields
        'contacted_by',
        'quote_count',
        'last_quote_amount',
        'flagged_for_review',
        'review_flags',
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
            // Contact tracking casts
            'contacted_by' => 'array',
            'quote_count' => 'integer',
            'last_quote_amount' => 'decimal:2',
            'flagged_for_review' => 'boolean',
            'review_flags' => 'array',
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
    const SOURCE_QUOTATION_BUILDER = 'quotation_builder';
    const SOURCE_INVOICE_BUILDER = 'invoice_builder';
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
            self::SOURCE_QUOTATION_BUILDER => 'Quotation Builder',
            self::SOURCE_INVOICE_BUILDER => 'Invoice Builder',
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

    /**
     * Get the customer converted from this lead.
     */
    public function customer(): HasMany
    {
        return $this->hasMany(Customer::class);
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
        // Skip notification if lead has no team assigned
        if (!$this->team) {
            return;
        }

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

    /**
     * Find or create a lead from quotation/invoice data.
     * This ensures all customer interactions are tracked in the CRM.
     *
     * @param array $data Customer data from quotation/invoice
     * @param string $source Source type (quotation_builder, invoice_builder)
     * @return Lead
     */
    public static function findOrCreateFromCustomerData(array $data, string $source = self::SOURCE_QUOTATION_BUILDER): self
    {
        // First, try to find existing lead by phone number (most reliable identifier)
        $existingLead = static::forCompany()
            ->where('phone', $data['customer_phone'])
            ->first();

        if ($existingLead) {
            // Update lead with latest information if status allows
            if (in_array($existingLead->status, [self::STATUS_NEW, self::STATUS_CONTACTED])) {
                $existingLead->update([
                    'email' => $data['customer_email'] ?? $existingLead->email,
                    'address' => $data['customer_address'] ?? $existingLead->address,
                    'city' => $data['customer_city'] ?? $existingLead->city,
                    'state' => $data['customer_state'] ?? $existingLead->state,
                    'postal_code' => $data['customer_postal_code'] ?? $existingLead->postal_code,
                    'last_contacted_at' => now(),
                ]);
            }

            return $existingLead;
        }

        // Create new lead if not found
        $leadData = [
            'company_id' => auth()->user()->company_id,
            'team_id' => $data['team_id'] ?? auth()->user()->team_id,
            'assigned_to' => $data['assigned_to'] ?? auth()->id(),
            'name' => $data['customer_name'],
            'phone' => $data['customer_phone'],
            'email' => $data['customer_email'] ?? null,
            'address' => $data['customer_address'] ?? null,
            'city' => $data['customer_city'] ?? null,
            'state' => $data['customer_state'] ?? null,
            'postal_code' => $data['customer_postal_code'] ?? null,
            'source' => $source,
            'status' => self::STATUS_NEW,
            'requirements' => $data['description'] ?? $data['title'] ?? null,
            'estimated_value' => $data['estimated_value'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        $lead = static::create($leadData);

        // Log activity for auto-created lead
        LeadActivity::create([
            'company_id' => $lead->company_id,
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => 'lead_created',
            'title' => 'Lead Auto-Created',
            'description' => "Lead automatically created from " . ($source === self::SOURCE_QUOTATION_BUILDER ? 'quotation' : 'invoice') . " builder",
            'metadata' => [
                'source' => $source,
                'auto_created' => true,
            ],
        ]);

        return $lead;
    }

    /**
     * ========================================================================
     * CONTACT TRANSPARENCY TRACKING METHODS
     * ========================================================================
     */

    /**
     * Record a contact from a sales rep (with optional quote amount)
     */
    public function recordContact(User $user, ?float $quoteAmount = null): void
    {
        // Skip if tracking is disabled
        if (!config('lead_tracking.enabled')) {
            return;
        }

        if (!config('lead_tracking.contact_tracking.track_contacts')) {
            return;
        }

        $contacts = $this->contacted_by ?? [];

        // Add new contact record
        $contacts[] = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'contacted_at' => now()->toDateTimeString(),
            'quoted' => $quoteAmount,
        ];

        $this->update([
            'contacted_by' => $contacts,
            'quote_count' => count(array_filter($contacts, fn($c) => !empty($c['quoted']))),
            'last_quote_amount' => $quoteAmount ?? $this->last_quote_amount,
        ]);

        // Check for price war if quote amount provided
        if ($quoteAmount && config('lead_tracking.price_war_detection.enabled')) {
            $this->checkPriceWar($quoteAmount, $user);
        }

        // Alert if multiple reps are quoting
        if (config('lead_tracking.manager_alerts.multiple_quotes')) {
            $this->checkMultipleQuotes();
        }
    }

    /**
     * Check if there's a significant price drop (price war)
     */
    protected function checkPriceWar(float $newQuoteAmount, User $user): void
    {
        if (!$this->last_quote_amount) {
            return; // First quote, nothing to compare
        }

        $threshold = config('lead_tracking.price_war_detection.threshold_percentage', 15);
        $priceDrop = (($this->last_quote_amount - $newQuoteAmount) / $this->last_quote_amount) * 100;

        if ($priceDrop >= $threshold) {
            // Flag for review
            if (config('lead_tracking.price_war_detection.auto_flag_for_review')) {
                $this->flagForReview('price_war', [
                    'previous_quote' => $this->last_quote_amount,
                    'new_quote' => $newQuoteAmount,
                    'drop_percentage' => round($priceDrop, 2),
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'threshold' => $threshold,
                ]);
            }

            // TODO: Send notification to manager
            // if (config('lead_tracking.price_war_detection.notify_manager')) {
            //     // Send notification
            // }
        }
    }

    /**
     * Check if multiple reps are quoting same customer
     */
    protected function checkMultipleQuotes(): void
    {
        $threshold = config('lead_tracking.manager_alerts.multiple_quotes_threshold', 2);
        $uniqueReps = collect($this->contacted_by ?? [])
            ->pluck('user_id')
            ->unique()
            ->count();

        if ($uniqueReps >= $threshold) {
            $this->flagForReview('multiple_quotes', [
                'unique_reps' => $uniqueReps,
                'threshold' => $threshold,
                'reps' => $this->getActiveReps(),
            ]);
        }
    }

    /**
     * Flag this lead for manager review
     */
    public function flagForReview(string $type, array $details = []): void
    {
        $flags = $this->review_flags ?? [];

        $flags[] = [
            'type' => $type,
            'details' => $details,
            'flagged_at' => now()->toDateTimeString(),
        ];

        $this->update([
            'flagged_for_review' => true,
            'review_flags' => $flags,
        ]);
    }

    /**
     * Clear review flags for this lead
     */
    public function clearReviewFlags(): void
    {
        $this->update([
            'flagged_for_review' => false,
            'review_flags' => null,
        ]);
    }

    /**
     * Get all unique sales reps who contacted this customer
     */
    public function getActiveReps(): array
    {
        return collect($this->contacted_by ?? [])
            ->map(fn($contact) => [
                'id' => $contact['user_id'],
                'name' => $contact['user_name'],
                'contacted_at' => $contact['contacted_at'],
                'quoted' => $contact['quoted'] ?? null,
            ])
            ->unique('id')
            ->values()
            ->toArray();
    }

    /**
     * Get active reps as a simple name array
     */
    public function getActiveRepNames(): array
    {
        return collect($this->contacted_by ?? [])
            ->map(fn($contact) => $contact['user_name'])
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Check if multiple reps have contacted/quoted this lead
     */
    public function hasMultipleQuotes(): bool
    {
        return ($this->quote_count ?? 0) > 1;
    }

    /**
     * Check if multiple reps have contacted this lead
     */
    public function hasMultipleContacts(): bool
    {
        $uniqueReps = collect($this->contacted_by ?? [])
            ->pluck('user_id')
            ->unique()
            ->count();

        return $uniqueReps > 1;
    }

    /**
     * Check if current user has already contacted this lead
     */
    public function hasBeenContactedByCurrentUser(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        return collect($this->contacted_by ?? [])
            ->pluck('user_id')
            ->contains(auth()->id());
    }

    /**
     * Get price drop percentage from last quote
     */
    public function getPriceDropPercentage(): ?float
    {
        if (!$this->last_quote_amount || count($this->contacted_by ?? []) < 2) {
            return null;
        }

        $quotes = collect($this->contacted_by ?? [])
            ->filter(fn($c) => !empty($c['quoted']))
            ->pluck('quoted')
            ->sort()
            ->values();

        if ($quotes->count() < 2) {
            return null;
        }

        $highest = $quotes->last();
        $lowest = $quotes->first();

        return round((($highest - $lowest) / $highest) * 100, 2);
    }

    /**
     * Scope: Get leads flagged for review
     */
    public function scopeFlaggedForReview($query)
    {
        return $query->where('flagged_for_review', true);
    }

    /**
     * Scope: Get leads with price wars
     */
    public function scopeWithPriceWars($query)
    {
        return $query->where('flagged_for_review', true)
            ->whereRaw("JSON_EXTRACT(review_flags, '$[*].type') LIKE '%price_war%'");
    }

    /**
     * Scope: Get leads with multiple quotes
     */
    public function scopeWithMultipleQuotes($query)
    {
        return $query->where('quote_count', '>', 1);
    }
}
