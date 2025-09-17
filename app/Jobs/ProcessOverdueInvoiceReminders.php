<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InvoiceOverdueNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessOverdueInvoiceReminders implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Job configuration
     */
    public $timeout = 300; // 5 minutes
    public $tries = 3;
    public $backoff = [30, 60, 120]; // Exponential backoff

    /**
     * The number of days overdue to process
     */
    public array $reminderDays;

    /**
     * Whether to force send reminders even if already sent today
     */
    public bool $force;

    /**
     * Create a new job instance.
     */
    public function __construct(array $reminderDays = [1, 7, 14, 30, 60, 90], bool $force = false)
    {
        $this->reminderDays = $reminderDays;
        $this->force = $force;
        
        // Queue this job on the notifications queue for proper prioritization
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting overdue invoice reminder processing', [
            'reminder_days' => $this->reminderDays,
            'force' => $this->force,
        ]);

        $totalProcessed = 0;
        $totalSent = 0;

        foreach ($this->reminderDays as $days) {
            $results = $this->processRemindersForDays($days);
            $totalProcessed += $results['processed'];
            $totalSent += $results['sent'];
        }

        Log::info('Completed overdue invoice reminder processing', [
            'total_processed' => $totalProcessed,
            'total_sent' => $totalSent,
        ]);
    }

    /**
     * Process reminders for specific number of days overdue
     */
    private function processRemindersForDays(int $days): array
    {
        $processed = 0;
        $sent = 0;

        // Find invoices that are exactly X days overdue
        $overdueInvoices = Invoice::whereRaw('DATEDIFF(NOW(), due_date) = ?', [$days])
            ->whereNotIn('status', ['PAID', 'CANCELLED'])
            ->with(['assignedTo', 'createdBy', 'company'])
            ->get();

        Log::info("Processing {$days}-day overdue invoices", [
            'count' => $overdueInvoices->count(),
        ]);

        foreach ($overdueInvoices as $invoice) {
            $processed++;
            
            try {
                // Check if reminder already sent today (unless forced)
                if (!$this->force && $this->wasReminderSentToday($invoice, $days)) {
                    Log::debug("Skipping reminder - already sent today", [
                        'invoice_number' => $invoice->number,
                        'days_overdue' => $days,
                    ]);
                    continue;
                }

                if ($this->sendReminderNotification($invoice, $days)) {
                    $sent++;
                    Log::info("Sent overdue reminder", [
                        'invoice_number' => $invoice->number,
                        'customer' => $invoice->customer_name,
                        'days_overdue' => $days,
                        'amount_due' => $invoice->amount_due,
                    ]);
                } else {
                    Log::warning("Failed to send overdue reminder", [
                        'invoice_number' => $invoice->number,
                        'days_overdue' => $days,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Error processing overdue reminder", [
                    'invoice_number' => $invoice->number,
                    'days_overdue' => $days,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['processed' => $processed, 'sent' => $sent];
    }

    /**
     * Check if reminder was already sent today
     */
    private function wasReminderSentToday(Invoice $invoice, int $days): bool
    {
        // For now, use simple check based on updated_at
        // In production, you might want to implement a more sophisticated tracking system
        $lastUpdated = $invoice->updated_at;
        $today = Carbon::now()->startOfDay();
        
        return $lastUpdated->isAfter($today);
    }

    /**
     * Send reminder notification for the invoice
     */
    private function sendReminderNotification(Invoice $invoice, int $days): bool
    {
        try {
            $recipients = [];

            // Send to assigned user
            if ($invoice->assignedTo) {
                $recipients[] = $invoice->assignedTo;
            }

            // Send to creator if different
            if ($invoice->createdBy && $invoice->created_by !== $invoice->assigned_to) {
                $recipients[] = $invoice->createdBy;
            }

            // Send to finance managers
            $financeManagers = User::where('company_id', $invoice->company_id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'finance_manager');
                })
                ->get();

            $recipients = collect($recipients)->merge($financeManagers)->unique('id');

            // Send notifications
            foreach ($recipients as $recipient) {
                $recipient->notify(new InvoiceOverdueNotification($invoice, $days));
            }

            // Update invoice tracking
            $invoice->touch(); // Updates updated_at timestamp
            
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send overdue notification", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Overdue invoice reminder job failed', [
            'reminder_days' => $this->reminderDays,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
