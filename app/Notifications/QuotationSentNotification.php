<?php

namespace App\Notifications;

use App\Models\Quotation;

class QuotationSentNotification extends BaseNotification
{
    protected $notificationType = 'quotation_sent';
    protected Quotation $quotation;
    protected $isCustomerNotification;

    /**
     * Create a new notification instance.
     */
    public function __construct(Quotation $quotation, bool $isCustomerNotification = false)
    {
        parent::__construct($quotation);
        $this->quotation = $quotation;
        $this->isCustomerNotification = $isCustomerNotification;
    }

    protected function getEmailSubject(object $notifiable): string
    {
        if ($this->isCustomerNotification) {
            return "Quotation #{$this->quotation->number} from {$this->company->name}";
        }

        return "Quotation Sent: #{$this->quotation->number}";
    }

    protected function getEmailContent(object $notifiable): array
    {
        if ($this->isCustomerNotification) {
            return $this->getCustomerEmailContent($notifiable);
        }

        return $this->getInternalEmailContent($notifiable);
    }

    /**
     * Get email content for customer.
     */
    protected function getCustomerEmailContent(object $notifiable): array
    {
        return [
            'type' => 'info',
            'title' => 'New Quotation Available',
            'message' => "Thank you for your interest in our services. Please find attached your quotation #{$this->quotation->number}.",
            'details' => [
                'Quotation Number' => $this->quotation->number,
                'Project' => $this->quotation->project_name ?: 'Service Quotation',
                'Total Amount' => 'RM ' . number_format($this->quotation->total, 2),
                'Valid Until' => $this->quotation->valid_until ? $this->quotation->valid_until->format('M d, Y') : 'Not specified',
                'Prepared By' => $this->quotation->createdBy->name,
            ],
            'action_url' => route('quotations.preview', $this->quotation),
            'action_text' => 'View Quotation',
            'body' => $this->quotation->notes ? '<p><strong>Additional Notes:</strong></p><p>' . nl2br(e($this->quotation->notes)) . '</p>' : null,
        ];
    }

    /**
     * Get email content for internal team.
     */
    protected function getInternalEmailContent(object $notifiable): array
    {
        return [
            'type' => 'success',
            'title' => 'Quotation Successfully Sent',
            'message' => "Quotation #{$this->quotation->number} has been sent to the customer.",
            'details' => [
                'Quotation Number' => $this->quotation->number,
                'Customer' => $this->quotation->customer_name,
                'Company' => $this->quotation->customer_company,
                'Email' => $this->quotation->customer_email,
                'Total Amount' => 'RM ' . number_format($this->quotation->total, 2),
                'Sent At' => now()->format('M d, Y \a\t h:i A'),
            ],
            'action_url' => route('quotations.show', $this->quotation),
            'action_text' => 'View Quotation',
            'secondary_actions' => [
                [
                    'url' => route('quotations.index'),
                    'text' => 'View All Quotations',
                ],
            ],
        ];
    }

    protected function getDatabaseTitle(object $notifiable): string
    {
        if ($this->isCustomerNotification) {
            return 'Quotation Received';
        }

        return 'Quotation Sent';
    }

    protected function getDatabaseMessage(object $notifiable): string
    {
        if ($this->isCustomerNotification) {
            return "Quotation #{$this->quotation->number} has been prepared for you";
        }

        return "Quotation #{$this->quotation->number} sent to {$this->quotation->customer_name}";
    }

    protected function getDatabaseData(object $notifiable): array
    {
        return [
            'quotation_id' => $this->quotation->id,
            'quotation_number' => $this->quotation->number,
            'customer_name' => $this->quotation->customer_name,
            'total_amount' => $this->quotation->total,
            'is_customer_notification' => $this->isCustomerNotification,
        ];
    }
}