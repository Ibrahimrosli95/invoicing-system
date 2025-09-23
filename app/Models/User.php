<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Services\WebhookEventService;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'phone',
        'title',
        'department',
        'signature',
        'avatar_path',
        'timezone',
        'language',
        'preferences',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_enabled_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'json',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'two_factor_enabled_at' => 'datetime',
        ];
    }

    /**
     * Get the company that owns the user.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The teams that belong to the user.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }

    /**
     * Scope to get users for a specific company.
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?? auth()->user()?->company_id;
        return $query->where('company_id', $companyId);
    }

    /**
     * Get the leads assigned to this user.
     */
    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    /**
     * Get the quotations assigned to this user.
     */
    public function assignedQuotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'assigned_to');
    }

    /**
     * Get the quotations created by this user.
     */
    public function createdQuotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'created_by');
    }

    /**
     * Get the invoices assigned to this user.
     */
    public function assignedInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'assigned_to');
    }

    /**
     * Get the invoices created by this user.
     */
    public function createdInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    /**
     * Get the assessments assigned to this user.
     */
    public function assignedAssessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'assigned_to');
    }

    /**
     * Get the assessments created by this user.
     */
    public function createdAssessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'created_by');
    }

    /**
     * Get the payment records recorded by this user.
     */
    public function recordedPayments(): HasMany
    {
        return $this->hasMany(PaymentRecord::class, 'recorded_by');
    }

    /**
     * Get the lead activities created by this user.
     */
    public function leadActivities(): HasMany
    {
        return $this->hasMany(LeadActivity::class);
    }

    /**
     * Get the audit logs for this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the notification preferences for this user.
     */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get notification preference for a specific type.
     */
    public function getNotificationPreference(string $type): ?NotificationPreference
    {
        return $this->notificationPreferences()->where('notification_type', $type)->first();
    }

    /**
     * Check if user wants email notifications for a type.
     */
    public function wantsEmailNotification(string $type): bool
    {
        $preference = $this->getNotificationPreference($type);
        
        if (!$preference) {
            // Default to enabled if no preference set
            return true;
        }
        
        return $preference->email_enabled;
    }

    /**
     * Check if user wants push notifications for a type.
     */
    public function wantsPushNotification(string $type): bool
    {
        $preference = $this->getNotificationPreference($type);
        
        if (!$preference) {
            // Default to enabled if no preference set
            return true;
        }
        
        return $preference->push_enabled;
    }

    /**
     * Setup default notification preferences for new user.
     */
    public function setupDefaultNotificationPreferences(): void
    {
        $defaults = NotificationPreference::getDefaultPreferences();
        
        foreach ($defaults as $type => $settings) {
            $this->notificationPreferences()->create([
                'notification_type' => $type,
                'email_enabled' => $settings['email_enabled'],
                'push_enabled' => $settings['push_enabled'],
                'settings' => $settings['settings'],
            ]);
        }
    }

    /**
     * Boot the model and set up event listeners.
     */
    protected static function booted()
    {
        static::created(function ($user) {
            $webhookService = app(WebhookEventService::class);
            $webhookService->userCreated($user);
        });

        static::updated(function ($user) {
            $webhookService = app(WebhookEventService::class);
            
            // Get changes for webhook payload
            $changes = [];
            foreach ($user->getDirty() as $key => $value) {
                // Skip password field for security
                if ($key === 'password') {
                    continue;
                }
                
                $changes[$key] = [
                    'old' => $user->getOriginal($key),
                    'new' => $value,
                ];
            }
            
            if (!empty($changes)) {
                $webhookService->userUpdated($user, $changes);
            }
        });
    }
}
