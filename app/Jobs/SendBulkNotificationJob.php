<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkNotificationJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;
    public $backoff = [10, 30, 60]; // Exponential backoff in seconds

    protected array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $userIds = $this->data['user_ids'] ?? [];
        $notificationClass = $this->data['notification_class'] ?? null;
        $notificationData = $this->data['notification_data'] ?? [];

        if (empty($userIds) || !$notificationClass) {
            Log::warning('Bulk notification job missing required data', [
                'user_ids_count' => count($userIds),
                'notification_class' => $notificationClass,
            ]);
            return;
        }

        if (!class_exists($notificationClass)) {
            Log::error('Bulk notification job - notification class not found', [
                'notification_class' => $notificationClass,
            ]);
            return;
        }

        $users = User::whereIn('id', $userIds)->get();
        $processed = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                // Create notification instance with data
                $notification = new $notificationClass(...$notificationData);
                
                // Send notification
                if ($notificationService->sendNotification($user, $notification)) {
                    $processed++;
                } else {
                    $failed++;
                }
                
                // Prevent overwhelming the email system
                if ($processed % 10 === 0) {
                    sleep(1);
                }
                
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to send bulk notification to user', [
                    'user_id' => $user->id,
                    'notification_class' => $notificationClass,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Bulk notification job completed', [
            'notification_class' => $notificationClass,
            'total_users' => count($userIds),
            'processed' => $processed,
            'failed' => $failed,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk notification job failed', [
            'notification_class' => $this->data['notification_class'] ?? 'unknown',
            'user_count' => count($this->data['user_ids'] ?? []),
            'error' => $exception->getMessage(),
            'attempt' => $this->attempts(),
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return $this->backoff;
    }
}
