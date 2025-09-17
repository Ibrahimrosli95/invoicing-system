<?php

namespace App\Notifications;

use App\Models\User;
use App\Models\Proof;
use App\Services\PDFService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ProofPackSharedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Collection $proofs;
    public array $shareData;
    public User $sender;
    public ?string $pdfPath = null;

    /**
     * Create a new notification instance.
     */
    public function __construct(Collection $proofs, array $shareData, User $sender)
    {
        $this->proofs = $proofs;
        $this->shareData = $shareData;
        $this->sender = $sender;
        
        // Generate PDF for email attachment if requested
        if ($shareData['attach_pdf'] ?? false) {
            $this->generatePdfAttachment();
        }
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $shareUrl = $this->shareData['share_url'] ?? '#';
        $expiresAt = \Carbon\Carbon::parse($this->shareData['expires_at']);
        
        $mailMessage = (new MailMessage)
            ->subject($this->shareData['email_subject'] ?? 'Proof Pack Shared: ' . $this->shareData['title'])
            ->greeting('Hello' . ($this->shareData['recipient_name'] ? ' ' . $this->shareData['recipient_name'] : '') . ',')
            ->line($this->sender->name . ' has shared a proof pack with you: "' . $this->shareData['title'] . '"')
            ->when($this->shareData['description'], function ($message) {
                return $message->line('Description: ' . $this->shareData['description']);
            })
            ->line('This proof pack contains ' . $this->proofs->count() . ' proof' . ($this->proofs->count() !== 1 ? 's' : '') . ' showcasing our work and credentials.')
            ->action('View Proof Pack', $shareUrl)
            ->line('You can view the proof pack online or download it as a PDF.')
            ->line('**Important:** This link will expire on ' . $expiresAt->format('M j, Y \a\t g:i A T') . ' (' . $expiresAt->diffForHumans() . ')')
            ->when($this->shareData['message'], function ($message) {
                return $message->line('Personal Message:')
                    ->line('"' . $this->shareData['message'] . '"');
            })
            ->line('If you have any questions, please don\'t hesitate to contact us.')
            ->salutation('Best regards,' . PHP_EOL . $this->sender->name . PHP_EOL . ($this->sender->company->name ?? 'Sales Team'));

        // Attach PDF if generated
        if ($this->pdfPath && Storage::exists($this->pdfPath)) {
            $filename = \Illuminate\Support\Str::slug($this->shareData['title']) . '_proof_pack.pdf';
            $mailMessage->attach(Storage::path($this->pdfPath), [
                'as' => $filename,
                'mime' => 'application/pdf',
            ]);
        }

        return $mailMessage;
    }

    /**
     * Generate PDF attachment for email
     */
    private function generatePdfAttachment(): void
    {
        try {
            $pdfService = app(PDFService::class);
            $options = [
                'title' => $this->shareData['title'],
                'description' => $this->shareData['description'] ?? null,
                'show_company_info' => true,
                'include_analytics' => false,
                'watermark' => 'EMAIL COPY',
            ];

            $this->pdfPath = $pdfService->generateProofPackPDF($this->proofs, $options);
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate PDF attachment for proof pack email: ' . $e->getMessage());
            // Continue without attachment if PDF generation fails
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'proof_pack_shared',
            'title' => $this->shareData['title'],
            'proof_count' => $this->proofs->count(),
            'sender_name' => $this->sender->name,
            'recipient_email' => $this->shareData['recipient_email'],
            'expires_at' => $this->shareData['expires_at'],
            'share_url' => $this->shareData['share_url'] ?? null,
        ];
    }

    /**
     * Clean up PDF file after notification is processed
     */
    public function __destruct()
    {
        if ($this->pdfPath && Storage::exists($this->pdfPath)) {
            Storage::delete($this->pdfPath);
        }
    }
}