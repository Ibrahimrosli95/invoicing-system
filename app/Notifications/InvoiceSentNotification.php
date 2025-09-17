<?php

namespace App\Notifications;

use App\Models\Invoice;

class InvoiceSentNotification extends BaseNotification
{
    protected $notificationType = 'invoice_sent';
    protected Invoice $invoice;
    protected $isCustomerNotification;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice, bool $isCustomerNotification = false)
    {
        parent::__construct($invoice);
        $this->invoice = $invoice;
        $this->isCustomerNotification = $isCustomerNotification;
    }

    protected function getEmailSubject(object $notifiable): string
    {
        if ($this->isCustomerNotification) {
            return "Invoice #{$this->invoice->number} from {$this->company->name}";
        }

        return "Invoice Sent: #{$this->invoice->number}";
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
            'title' => 'New Invoice Available',
            'message' => "Thank you for your business. Please find attached your invoice #{$this->invoice->number} for immediate payment.",
            'details' => [
                'Invoice Number' => $this->invoice->number,
                'Invoice Date' => $this->invoice->invoice_date->format('M d, Y'),
                'Due Date' => $this->invoice->due_date->format('M d, Y'),
                'Total Amount' => 'RM ' . number_format($this->invoice->total, 2),
                'Payment Terms' => $this->invoice->payment_terms ?: 'Net 30 days',
            ],
            'action_url' => route('invoices.preview', $this->invoice),
            'action_text' => 'View Invoice',
            'body' => $this->getBankDetails(),
        ];
    }

    /**
     * Get email content for internal team.
     */
    protected function getInternalEmailContent(object $notifiable): array
    {
        return [
            'type' => 'success',
            'title' => 'Invoice Successfully Sent',
            'message' => "Invoice #{$this->invoice->number} has been sent to the customer.",
            'details' => [
                'Invoice Number' => $this->invoice->number,
                'Customer' => $this->invoice->customer_name,
                'Company' => $this->invoice->customer_company,
                'Email' => $this->invoice->customer_email,
                'Total Amount' => 'RM ' . number_format($this->invoice->total, 2),
                'Due Date' => $this->invoice->due_date->format('M d, Y'),
                'Sent At' => now()->format('M d, Y \a\t h:i A'),
            ],
            'action_url' => route('invoices.show', $this->invoice),
            'action_text' => 'View Invoice',
            'secondary_actions' => [
                [
                    'url' => route('invoices.index'),
                    'text' => 'View All Invoices',
                ],
            ],
        ];
    }

    /**
     * Get bank details for customer payments.
     */
    protected function getBankDetails(): string
    {
        return '<div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
            <h4 style="color: #2563EB; margin-bottom: 10px;">Payment Details</h4>
            <p><strong>Bank Name:</strong> ' . ($this->company->bank_name ?: 'CIMB Bank Berhad') . '</p>
            <p><strong>Account Name:</strong> ' . ($this->company->account_name ?: $this->company->name) . '</p>
            <p><strong>Account Number:</strong> ' . ($this->company->account_number ?: 'XXXX-XXXX-XXXX') . '</p>
            <p style="margin-top: 15px; font-size: 14px; color: #6b7280;">
                Please use invoice number <strong>' . $this->invoice->number . '</strong> as payment reference.
            </p>
        </div>';
    }

    protected function getDatabaseTitle(object $notifiable): string
    {
        if ($this->isCustomerNotification) {
            return 'Invoice Received';
        }

        return 'Invoice Sent';
    }

    protected function getDatabaseMessage(object $notifiable): string
    {
        if ($this->isCustomerNotification) {
            return "Invoice #{$this->invoice->number} is ready for payment";
        }

        return "Invoice #{$this->invoice->number} sent to {$this->invoice->customer_name}";
    }

    protected function getDatabaseData(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->number,
            'customer_name' => $this->invoice->customer_name,
            'total_amount' => $this->invoice->total,
            'due_date' => $this->invoice->due_date,
            'is_customer_notification' => $this->isCustomerNotification,
        ];
    }
}