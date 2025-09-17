<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\EmailDeliveryLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $notificationType;
    protected $relatedModel;
    protected $company;

    /**
     * Create a new notification instance.
     */
    public function __construct($relatedModel = null)
    {
        $this->relatedModel = $relatedModel;
        $this->company = auth()->user()?->company ?? Company::first();
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        // Check user preferences for email
        if ($notifiable->wantsEmailNotification($this->notificationType)) {
            $channels[] = 'mail';
        }

        // Add database channel for in-app notifications
        $channels[] = 'database';

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = new MailMessage();
        
        // Apply company branding
        $mailMessage->view('notifications.email.template', [
            'company' => $this->company,
            'notifiable' => $notifiable,
            'notification' => $this,
            'content' => $this->getEmailContent($notifiable),
        ]);

        // Set from address with company name
        $mailMessage->from(
            config('mail.from.address'),
            $this->company->name ?? config('mail.from.name')
        );

        // Set subject
        $mailMessage->subject($this->getEmailSubject($notifiable));

        // Log email delivery attempt
        $this->logEmailDelivery($notifiable, $mailMessage);

        return $mailMessage;
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->notificationType,
            'title' => $this->getDatabaseTitle($notifiable),
            'message' => $this->getDatabaseMessage($notifiable),
            'data' => $this->getDatabaseData($notifiable),
            'related_model_type' => $this->relatedModel ? get_class($this->relatedModel) : null,
            'related_model_id' => $this->relatedModel?->id,
            'company_id' => $this->company->id,
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    /**
     * Abstract methods to be implemented by child classes.
     */
    abstract protected function getEmailSubject(object $notifiable): string;
    abstract protected function getEmailContent(object $notifiable): array;
    abstract protected function getDatabaseTitle(object $notifiable): string;
    abstract protected function getDatabaseMessage(object $notifiable): string;
    
    protected function getDatabaseData(object $notifiable): array
    {
        return [];
    }

    /**
     * Log email delivery attempt.
     */
    protected function logEmailDelivery(object $notifiable, MailMessage $mailMessage): void
    {
        try {
            EmailDeliveryLog::create([
                'company_id' => $this->company->id,
                'notification_type' => $this->notificationType,
                'related_model_type' => $this->relatedModel ? get_class($this->relatedModel) : null,
                'related_model_id' => $this->relatedModel?->id,
                'recipient_email' => $notifiable->email,
                'recipient_name' => $notifiable->name,
                'subject' => $this->getEmailSubject($notifiable),
                'status' => 'pending',
                'metadata' => [
                    'notification_class' => static::class,
                    'channels' => $this->via($notifiable),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log email delivery', [
                'error' => $e->getMessage(),
                'notification_type' => $this->notificationType,
                'recipient' => $notifiable->email,
            ]);
        }
    }

    /**
     * Handle notification failure.
     */
    public function failed(\Exception $exception): void
    {
        Log::error('Notification failed', [
            'notification_type' => $this->notificationType,
            'error' => $exception->getMessage(),
            'related_model' => $this->relatedModel ? [
                'type' => get_class($this->relatedModel),
                'id' => $this->relatedModel->id,
            ] : null,
        ]);

        // Update email delivery log if exists
        if ($this->relatedModel) {
            EmailDeliveryLog::where('notification_type', $this->notificationType)
                ->where('related_model_type', get_class($this->relatedModel))
                ->where('related_model_id', $this->relatedModel->id)
                ->where('status', 'pending')
                ->latest()
                ->first()
                ?->markAsFailed($exception->getMessage());
        }
    }
}