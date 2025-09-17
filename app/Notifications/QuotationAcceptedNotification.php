<?php

namespace App\Notifications;

use App\Models\Quotation;

class QuotationAcceptedNotification extends BaseNotification
{
    protected $notificationType = 'quotation_accepted';
    protected Quotation $quotation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Quotation $quotation)
    {
        parent::__construct($quotation);
        $this->quotation = $quotation;
    }

    protected function getEmailSubject(object $notifiable): string
    {
        return "🎉 Quotation Accepted: #{$this->quotation->number}";
    }

    protected function getEmailContent(object $notifiable): array
    {
        return [
            'type' => 'success',
            'title' => 'Quotation Accepted! 🎉',
            'message' => "Great news! Customer has accepted quotation #{$this->quotation->number}. Time to convert to invoice and begin project delivery.",
            'details' => [
                'Quotation Number' => $this->quotation->number,
                'Customer' => $this->quotation->customer_name,
                'Company' => $this->quotation->customer_company,
                'Project Value' => 'RM ' . number_format($this->quotation->total, 2),
                'Accepted At' => now()->format('M d, Y \a\t h:i A'),
                'Sales Rep' => $this->quotation->createdBy->name,
            ],
            'action_url' => route('quotations.convert', $this->quotation),
            'action_text' => 'Convert to Invoice',
            'secondary_actions' => [
                [
                    'url' => route('quotations.show', $this->quotation),
                    'text' => 'View Quotation',
                ],
            ],
        ];
    }

    protected function getDatabaseTitle(object $notifiable): string
    {
        return 'Quotation Accepted';
    }

    protected function getDatabaseMessage(object $notifiable): string
    {
        return "Quotation #{$this->quotation->number} accepted by {$this->quotation->customer_name} - RM " . number_format($this->quotation->total, 2);
    }

    protected function getDatabaseData(object $notifiable): array
    {
        return [
            'quotation_id' => $this->quotation->id,
            'quotation_number' => $this->quotation->number,
            'customer_name' => $this->quotation->customer_name,
            'total_amount' => $this->quotation->total,
            'accepted_at' => now(),
        ];
    }
}