<?php

namespace App\Notifications;

use App\Models\Invoice;
use Carbon\Carbon;

class InvoiceOverdueNotification extends BaseNotification
{
    protected $notificationType = 'invoice_overdue';
    protected Invoice $invoice;
    protected $isCustomerNotification;
    protected $daysOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice, bool $isCustomerNotification = false)
    {
        parent::__construct($invoice);
        $this->invoice = $invoice;
        $this->isCustomerNotification = $isCustomerNotification;
        $this->daysOverdue = Carbon::now()->diffInDays($this->invoice->due_date, false) * -1;
    }

    protected function getEmailSubject(object $notifiable): string
    {
        if ($this->isCustomerNotification) {
            return "URGENT: Overdue Payment - Invoice #{$this->invoice->number}";
        }

        return "Invoice Overdue Alert: #{$this->invoice->number}";
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
            'type' => 'error',
            'title' => 'Urgent: Payment Overdue',
            'message' => "Your invoice #{$this->invoice->number} is now {$this->daysOverdue} days overdue. Please arrange payment immediately to avoid any service disruption.",
            'details' => [
                'Invoice Number' => $this->invoice->number,
                'Original Due Date' => $this->invoice->due_date->format('M d, Y'),
                'Days Overdue' => $this->daysOverdue . ' days',
                'Outstanding Amount' => 'RM ' . number_format($this->invoice->balance, 2),
                'Late Fee' => $this->calculateLateFee(),
                'Total Due Now' => 'RM ' . number_format($this->invoice->balance + $this->calculateLateFeeAmount(), 2),
            ],
            'action_url' => route('invoices.payment-form', $this->invoice),
            'action_text' => 'Make Payment Now',
            'body' => $this->getPaymentInstructions(),
            'secondary_actions' => [
                [
                    'url' => route('invoices.preview', $this->invoice),
                    'text' => 'View Invoice',
                ],
            ],
        ];
    }

    /**
     * Get email content for internal team.
     */
    protected function getInternalEmailContent(object $notifiable): array
    {
        return [
            'type' => 'warning',
            'title' => 'Invoice Overdue Alert',
            'message' => "Invoice #{$this->invoice->number} is {$this->daysOverdue} days overdue. Follow-up action may be required.",
            'details' => [
                'Invoice Number' => $this->invoice->number,
                'Customer' => $this->invoice->customer_name,
                'Company' => $this->invoice->customer_company,
                'Original Due Date' => $this->invoice->due_date->format('M d, Y'),
                'Days Overdue' => $this->daysOverdue . ' days',
                'Outstanding Amount' => 'RM ' . number_format($this->invoice->balance, 2),
                'Last Payment' => $this->invoice->paymentRecords->last() ? 
                    $this->invoice->paymentRecords->last()->created_at->format('M d, Y') : 
                    'No payments received',
            ],
            'action_url' => route('invoices.show', $this->invoice),
            'action_text' => 'Review Invoice',
            'secondary_actions' => [
                [
                    'url' => route('invoices.index', ['overdue' => 1]),
                    'text' => 'View All Overdue',
                ],
            ],
        ];
    }

    /**
     * Calculate late fee display.
     */
    protected function calculateLateFee(): string
    {
        $lateFeeAmount = $this->calculateLateFeeAmount();
        
        if ($lateFeeAmount > 0) {
            return 'RM ' . number_format($lateFeeAmount, 2);
        }
        
        return 'None';
    }

    /**
     * Calculate actual late fee amount.
     */
    protected function calculateLateFeeAmount(): float
    {
        // Simple late fee: 1.5% per month or part thereof
        $monthsOverdue = ceil($this->daysOverdue / 30);
        return $this->invoice->balance * 0.015 * $monthsOverdue;
    }

    /**
     * Get payment instructions for customer.
     */
    protected function getPaymentInstructions(): string
    {
        return '<div style="margin-top: 20px; padding: 15px; background-color: #fef2f2; border: 2px solid #ef4444; border-radius: 5px;">
            <h4 style="color: #dc2626; margin-bottom: 10px;">Immediate Action Required</h4>
            <p style="color: #991b1b; margin-bottom: 15px;">
                To avoid further late fees and potential service interruption, please:
            </p>
            <ol style="color: #991b1b; margin-left: 20px;">
                <li>Make payment immediately using the details below</li>
                <li>Email payment confirmation to our accounts department</li>
                <li>Contact us if you need to discuss payment arrangements</li>
            </ol>
            <div style="margin-top: 15px; padding: 10px; background-color: #ffffff; border-radius: 3px;">
                <h5 style="color: #2563EB; margin-bottom: 5px;">Bank Details:</h5>
                <p><strong>Bank:</strong> ' . ($this->company->bank_name ?: 'CIMB Bank Berhad') . '</p>
                <p><strong>Account:</strong> ' . ($this->company->account_number ?: 'XXXX-XXXX-XXXX') . '</p>
                <p><strong>Reference:</strong> ' . $this->invoice->number . '</p>
            </div>
        </div>';
    }

    protected function getDatabaseTitle(object $notifiable): string
    {
        if ($this->isCustomerNotification) {
            return 'Payment Overdue';
        }

        return 'Invoice Overdue Alert';
    }

    protected function getDatabaseMessage(object $notifiable): string
    {
        if ($this->isCustomerNotification) {
            return "Invoice #{$this->invoice->number} is {$this->daysOverdue} days overdue - payment required urgently";
        }

        return "Invoice #{$this->invoice->number} from {$this->invoice->customer_name} is {$this->daysOverdue} days overdue";
    }

    protected function getDatabaseData(object $notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->number,
            'customer_name' => $this->invoice->customer_name,
            'days_overdue' => $this->daysOverdue,
            'outstanding_amount' => $this->invoice->balance,
            'late_fee' => $this->calculateLateFeeAmount(),
            'is_customer_notification' => $this->isCustomerNotification,
        ];
    }
}