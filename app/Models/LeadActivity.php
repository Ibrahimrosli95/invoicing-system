<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'lead_id',
        'user_id',
        'type',
        'title',
        'description',
        'outcome',
        'contact_method',
        'duration',
        'follow_up_at',
        'follow_up_notes',
        'attachments',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'metadata' => 'array',
            'follow_up_at' => 'datetime',
        ];
    }

    // Activity type constants
    const TYPE_CALL = 'call';
    const TYPE_EMAIL = 'email';
    const TYPE_MEETING = 'meeting';
    const TYPE_NOTE = 'note';
    const TYPE_STATUS_CHANGE = 'status_change';
    const TYPE_ASSIGNMENT = 'assignment';
    const TYPE_DOCUMENT = 'document';
    const TYPE_FOLLOW_UP = 'follow_up';
    const TYPE_QUOTATION = 'quotation';

    // Outcome constants
    const OUTCOME_SUCCESSFUL = 'successful';
    const OUTCOME_NO_ANSWER = 'no_answer';
    const OUTCOME_CALLBACK_REQUESTED = 'callback_requested';
    const OUTCOME_NOT_INTERESTED = 'not_interested';
    const OUTCOME_INTERESTED = 'interested';
    const OUTCOME_NEED_INFO = 'need_info';
    const OUTCOME_DECISION_PENDING = 'decision_pending';

    // Contact method constants
    const CONTACT_PHONE = 'phone';
    const CONTACT_EMAIL = 'email';
    const CONTACT_IN_PERSON = 'in_person';
    const CONTACT_VIDEO_CALL = 'video_call';
    const CONTACT_WHATSAPP = 'whatsapp';
    const CONTACT_SMS = 'sms';

    /**
     * Get all available activity types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_CALL => 'Call',
            self::TYPE_EMAIL => 'Email',
            self::TYPE_MEETING => 'Meeting',
            self::TYPE_NOTE => 'Note',
            self::TYPE_STATUS_CHANGE => 'Status Change',
            self::TYPE_ASSIGNMENT => 'Assignment',
            self::TYPE_DOCUMENT => 'Document',
            self::TYPE_FOLLOW_UP => 'Follow-up',
            self::TYPE_QUOTATION => 'Quotation',
        ];
    }

    /**
     * Get all available outcomes.
     */
    public static function getOutcomes(): array
    {
        return [
            self::OUTCOME_SUCCESSFUL => 'Successful',
            self::OUTCOME_NO_ANSWER => 'No Answer',
            self::OUTCOME_CALLBACK_REQUESTED => 'Callback Requested',
            self::OUTCOME_NOT_INTERESTED => 'Not Interested',
            self::OUTCOME_INTERESTED => 'Interested',
            self::OUTCOME_NEED_INFO => 'Need More Info',
            self::OUTCOME_DECISION_PENDING => 'Decision Pending',
        ];
    }

    /**
     * Get all available contact methods.
     */
    public static function getContactMethods(): array
    {
        return [
            self::CONTACT_PHONE => 'Phone',
            self::CONTACT_EMAIL => 'Email',
            self::CONTACT_IN_PERSON => 'In Person',
            self::CONTACT_VIDEO_CALL => 'Video Call',
            self::CONTACT_WHATSAPP => 'WhatsApp',
            self::CONTACT_SMS => 'SMS',
        ];
    }

    /**
     * Get the company that owns the activity.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the lead this activity belongs to.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user who performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get activities for a specific company.
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get activities by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get activities that need follow-up.
     */
    public function scopeNeedsFollowUp($query)
    {
        return $query->whereNotNull('follow_up_at')
                    ->where('follow_up_at', '<=', now());
    }

    /**
     * Get the activity type icon for UI display.
     */
    public function getTypeIcon(): string
    {
        return match($this->type) {
            self::TYPE_CALL => 'phone',
            self::TYPE_EMAIL => 'mail',
            self::TYPE_MEETING => 'users',
            self::TYPE_NOTE => 'file-text',
            self::TYPE_STATUS_CHANGE => 'refresh-cw',
            self::TYPE_ASSIGNMENT => 'user-plus',
            self::TYPE_DOCUMENT => 'file',
            self::TYPE_FOLLOW_UP => 'clock',
            self::TYPE_QUOTATION => 'file-text',
            default => 'circle',
        };
    }

    /**
     * Get the activity type color for UI display.
     */
    public function getTypeColor(): string
    {
        return match($this->type) {
            self::TYPE_CALL => 'blue',
            self::TYPE_EMAIL => 'green',
            self::TYPE_MEETING => 'purple',
            self::TYPE_NOTE => 'gray',
            self::TYPE_STATUS_CHANGE => 'yellow',
            self::TYPE_ASSIGNMENT => 'orange',
            self::TYPE_DOCUMENT => 'pink',
            self::TYPE_FOLLOW_UP => 'indigo',
            self::TYPE_QUOTATION => 'emerald',
            default => 'gray',
        };
    }

    /**
     * Get the outcome color for UI display.
     */
    public function getOutcomeColor(): string
    {
        return match($this->outcome) {
            self::OUTCOME_SUCCESSFUL, self::OUTCOME_INTERESTED => 'green',
            self::OUTCOME_NOT_INTERESTED => 'red',
            self::OUTCOME_NO_ANSWER, self::OUTCOME_CALLBACK_REQUESTED => 'yellow',
            self::OUTCOME_NEED_INFO, self::OUTCOME_DECISION_PENDING => 'blue',
            default => 'gray',
        };
    }

    /**
     * Check if this activity has a follow-up scheduled.
     */
    public function hasFollowUp(): bool
    {
        return !is_null($this->follow_up_at);
    }

    /**
     * Check if the follow-up is due.
     */
    public function isFollowUpDue(): bool
    {
        return $this->hasFollowUp() && $this->follow_up_at <= now();
    }

    /**
     * Get formatted duration for display.
     */
    public function getFormattedDuration(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        // If duration is in minutes (numeric)
        if (is_numeric($this->duration)) {
            $minutes = (int) $this->duration;
            if ($minutes < 60) {
                return $minutes . ' min';
            } else {
                $hours = floor($minutes / 60);
                $remainingMinutes = $minutes % 60;
                return $remainingMinutes > 0 
                    ? "{$hours}h {$remainingMinutes}m" 
                    : "{$hours}h";
            }
        }

        // Return as-is if it's already formatted
        return $this->duration;
    }

    /**
     * Create a new activity record.
     */
    public static function createActivity(
        Lead $lead,
        User $user,
        string $type,
        string $title,
        ?string $description = null,
        ?string $outcome = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'company_id' => $lead->company_id,
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'outcome' => $outcome,
            'metadata' => $metadata,
        ]);
    }
}
