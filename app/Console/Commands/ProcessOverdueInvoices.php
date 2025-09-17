<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Notifications\InvoiceOverdueNotification;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ProcessOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:process-overdue {--dry-run : Show what would be processed without sending notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process overdue invoices and send notifications to customers and internal team';

    protected NotificationService $notificationService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Processing overdue invoices...');
        
        // Find overdue invoices
        $overdueInvoices = Invoice::where('status', '!=', 'PAID')
            ->where('status', '!=', 'CANCELLED')
            ->where('due_date', '<', Carbon::now())
            ->with(['company', 'paymentRecords'])
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('No overdue invoices found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$overdueInvoices->count()} overdue invoices.");

        $processed = 0;
        $errors = 0;

        foreach ($overdueInvoices as $invoice) {
            try {
                $daysOverdue = Carbon::now()->diffInDays($invoice->due_date, false) * -1;
                
                if ($dryRun) {
                    $this->line("Would process: Invoice #{$invoice->number} - {$invoice->customer_name} ({$daysOverdue} days overdue)");
                } else {
                    // Update invoice status if not already overdue
                    if ($invoice->status !== 'OVERDUE') {
                        $invoice->update(['status' => 'OVERDUE']);
                    }

                    // Send customer notification
                    $this->sendCustomerNotification($invoice);
                    
                    // Send internal notification for severely overdue invoices (7+ days)
                    if ($daysOverdue >= 7) {
                        $this->sendInternalNotification($invoice);
                    }
                    
                    $this->line("Processed: Invoice #{$invoice->number} - {$invoice->customer_name} ({$daysOverdue} days overdue)");
                }
                
                $processed++;
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("Error processing invoice #{$invoice->number}: {$e->getMessage()}");
            }
        }

        if ($dryRun) {
            $this->info("Dry run completed. Would have processed {$processed} invoices.");
        } else {
            $this->info("Processing completed. Processed: {$processed}, Errors: {$errors}");
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Send overdue notification to customer.
     */
    protected function sendCustomerNotification(Invoice $invoice): void
    {
        try {
            // Create a mock user object for customer email
            $customer = (object) [
                'email' => $invoice->customer_email,
                'name' => $invoice->customer_name,
            ];

            // We need to extend the notification to handle non-User notifiables
            // For now, log that we would send customer notification
            $this->line("  → Would send customer notification to {$invoice->customer_email}");
            
        } catch (\Exception $e) {
            $this->error("  → Failed to send customer notification: {$e->getMessage()}");
        }
    }

    /**
     * Send overdue notification to internal team.
     */
    protected function sendInternalNotification(Invoice $invoice): void
    {
        try {
            // Get finance managers and sales team
            $company = $invoice->company;
            
            $recipients = $company->users()
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['finance_manager', 'company_manager']);
                })
                ->get();

            foreach ($recipients as $user) {
                if ($user->wantsEmailNotification('invoice_overdue')) {
                    $notification = new InvoiceOverdueNotification($invoice, false);
                    $this->notificationService->sendNotification($user, $notification);
                    $this->line("  → Sent internal notification to {$user->email}");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("  → Failed to send internal notifications: {$e->getMessage()}");
        }
    }
}
