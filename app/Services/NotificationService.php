<?php

namespace App\Services;

use App\Models\EmailDeliveryLog;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send notification with delivery tracking.
     */
    public function sendNotification($notifiable, Notification $notification): bool
    {
        try {
            // Send the notification
            $notifiable->notify($notification);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'notification_class' => get_class($notification),
                'notifiable_id' => $notifiable->id,
                'notifiable_type' => get_class($notifiable),
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Send notification to multiple users with delivery tracking.
     */
    public function sendToMultiple(array $notifiables, Notification $notification): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($notifiables as $notifiable) {
            if ($this->sendNotification($notifiable, $notification)) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = "Failed to send to {$notifiable->email}";
            }
        }

        return $results;
    }

    /**
     * Send notification to team members with role filtering.
     */
    public function sendToTeam($team, Notification $notification, array $roles = null): array
    {
        $query = $team->users();

        if ($roles) {
            $query->whereHas('roles', function ($q) use ($roles) {
                $q->whereIn('name', $roles);
            });
        }

        $users = $query->get();
        
        return $this->sendToMultiple($users->toArray(), $notification);
    }

    /**
     * Send notification to company users with role filtering.
     */
    public function sendToCompany($company, Notification $notification, array $roles = null): array
    {
        $query = $company->users();

        if ($roles) {
            $query->whereHas('roles', function ($q) use ($roles) {
                $q->whereIn('name', $roles);
            });
        }

        $users = $query->get();
        
        return $this->sendToMultiple($users->toArray(), $notification);
    }

    /**
     * Queue notification for bulk sending.
     */
    public function queueBulkNotification(array $userIds, string $notificationClass, array $data = []): bool
    {
        try {
            Queue::later(now()->addSeconds(10), new \App\Jobs\SendBulkNotificationJob([
                'user_ids' => $userIds,
                'notification_class' => $notificationClass,
                'notification_data' => $data,
            ]));

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to queue bulk notification', [
                'notification_class' => $notificationClass,
                'user_count' => count($userIds),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get delivery statistics for a company.
     */
    public function getDeliveryStats($companyId, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $logs = EmailDeliveryLog::where('company_id', $companyId)
            ->where('created_at', '>=', $startDate)
            ->get();

        $stats = [
            'total' => $logs->count(),
            'sent' => $logs->whereIn('status', ['sent', 'delivered'])->count(),
            'failed' => $logs->whereIn('status', ['failed', 'bounced'])->count(),
            'pending' => $logs->where('status', 'pending')->count(),
            'by_type' => [],
            'by_status' => [],
            'success_rate' => 0,
        ];

        // Group by notification type
        $stats['by_type'] = $logs->groupBy('notification_type')
            ->map(function ($group) {
                return [
                    'total' => $group->count(),
                    'sent' => $group->whereIn('status', ['sent', 'delivered'])->count(),
                    'failed' => $group->whereIn('status', ['failed', 'bounced'])->count(),
                ];
            })->toArray();

        // Group by status
        $stats['by_status'] = $logs->groupBy('status')
            ->map(function ($group) {
                return $group->count();
            })->toArray();

        // Calculate success rate
        if ($stats['total'] > 0) {
            $stats['success_rate'] = round(($stats['sent'] / $stats['total']) * 100, 2);
        }

        return $stats;
    }

    /**
     * Retry failed notifications.
     */
    public function retryFailedNotifications($companyId, int $hours = 24): int
    {
        $cutoffTime = now()->subHours($hours);
        
        $failedLogs = EmailDeliveryLog::where('company_id', $companyId)
            ->whereIn('status', ['failed', 'bounced'])
            ->where('created_at', '>=', $cutoffTime)
            ->limit(100) // Limit batch size
            ->get();

        $retried = 0;

        foreach ($failedLogs as $log) {
            try {
                // Find the user and model to retry
                if ($log->related_model_type && $log->related_model_id) {
                    $relatedModel = $log->related_model_type::find($log->related_model_id);
                    
                    if ($relatedModel && $log->notification_type) {
                        // Determine notification class from type
                        $notificationClass = $this->getNotificationClass($log->notification_type);
                        
                        if ($notificationClass) {
                            $user = User::where('email', $log->recipient_email)->first();
                            
                            if ($user && $user->wantsEmailNotification($log->notification_type)) {
                                $notification = new $notificationClass($relatedModel);
                                
                                if ($this->sendNotification($user, $notification)) {
                                    // Mark old log as retried
                                    $log->update([
                                        'status' => 'retried',
                                        'metadata' => array_merge($log->metadata ?: [], [
                                            'retried_at' => now()->toISOString(),
                                        ]),
                                    ]);
                                    
                                    $retried++;
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to retry notification', [
                    'log_id' => $log->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $retried;
    }

    /**
     * Get notification class from type string.
     */
    protected function getNotificationClass(string $type): ?string
    {
        $classMap = [
            'lead_assigned' => \App\Notifications\LeadAssignedNotification::class,
            'lead_status_changed' => \App\Notifications\LeadStatusChangedNotification::class,
            'quotation_sent' => \App\Notifications\QuotationSentNotification::class,
            'quotation_accepted' => \App\Notifications\QuotationAcceptedNotification::class,
            'invoice_sent' => \App\Notifications\InvoiceSentNotification::class,
            'invoice_overdue' => \App\Notifications\InvoiceOverdueNotification::class,
        ];

        return $classMap[$type] ?? null;
    }

    /**
     * Clean up old email logs.
     */
    public function cleanupOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return EmailDeliveryLog::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Get recent delivery logs for debugging.
     */
    public function getRecentLogs($companyId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return EmailDeliveryLog::where('company_id', $companyId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Mark email as delivered (webhook handler).
     */
    public function markAsDelivered(string $messageId, array $metadata = []): bool
    {
        $log = EmailDeliveryLog::where('metadata->message_id', $messageId)->first();
        
        if ($log) {
            $log->markAsDelivered();
            $log->update([
                'metadata' => array_merge($log->metadata ?: [], $metadata),
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Mark email as bounced (webhook handler).
     */
    public function markAsBounced(string $messageId, string $reason = null): bool
    {
        $log = EmailDeliveryLog::where('metadata->message_id', $messageId)->first();
        
        if ($log) {
            $log->markAsBounced($reason);
            
            return true;
        }
        
        return false;
    }
}