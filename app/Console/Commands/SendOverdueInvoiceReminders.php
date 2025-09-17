<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\User;
use App\Notifications\InvoiceOverdueNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendOverdueInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-overdue-reminders 
                            {--days=* : Specific overdue days to send reminders for (e.g. 1,7,14,30,60,90)}
                            {--force : Send reminders even if already sent today}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated reminders for overdue invoices based on aging buckets';

    /**
     * Default reminder intervals (days overdue)
     */
    protected $defaultReminderDays = [1, 7, 14, 30, 60, 90];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Processing overdue invoice reminders...');
        
        $reminderDays = $this->option('days') ?: $this->defaultReminderDays;
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        $totalProcessed = 0;
        $totalSent = 0;
        $results = [];

        foreach ($reminderDays as $days) {
            $this->info("\n📅 Processing {$days}-day overdue invoices...");
            
            // Find invoices that are exactly X days overdue
            $overdueInvoices = Invoice::whereRaw('DATEDIFF(NOW(), due_date) = ?', [$days])
                ->whereNotIn('status', ['PAID', 'CANCELLED'])
                ->with(['assignedTo', 'createdBy', 'company'])
                ->get();

            $this->info("   Found {$overdueInvoices->count()} invoices");

            foreach ($overdueInvoices as $invoice) {
                $totalProcessed++;
                
                // Check if reminder already sent today (unless forced)
                if (!$force && $this->wasReminderSentToday($invoice, $days)) {
                    $this->warn("   ⏭️  Skipping {$invoice->number} - reminder already sent today");
                    continue;
                }

                if ($isDryRun) {
                    $this->info("   📧 Would send reminder for {$invoice->number} to {$invoice->customer_name}");
                    $totalSent++;
                    continue;
                }

                // Send reminder
                if ($this->sendReminderNotification($invoice, $days)) {
                    $this->info("   ✅ Sent reminder for {$invoice->number} to {$invoice->customer_name}");
                    $totalSent++;
                    
                    // Track in results
                    if (!isset($results[$days])) {
                        $results[$days] = [];
                    }
                    $results[$days][] = $invoice->number;
                } else {
                    $this->error("   ❌ Failed to send reminder for {$invoice->number}");
                }
            }
        }

        // Summary
        $this->info("\n" . str_repeat('=', 50));
        $this->info('📊 SUMMARY');
        $this->info(str_repeat('=', 50));
        $this->info("Processed: {$totalProcessed} invoices");
        $this->info("Sent: {$totalSent} reminders");
        
        if (!empty($results)) {
            $this->info("\n📋 Reminders sent by aging bucket:");
            foreach ($results as $days => $invoiceNumbers) {
                $this->info("   {$days} days: " . implode(', ', $invoiceNumbers));
            }
        }

        if ($isDryRun) {
            $this->warn("\n⚠️  This was a dry run. Use --force to actually send reminders.");
        }

        return 0;
    }

    /**
     * Check if reminder was already sent today for this invoice and days overdue
     */
    protected function wasReminderSentToday(Invoice $invoice, int $days): bool
    {
        // This could be enhanced to track in database
        // For now, we'll use a simple check based on updated_at
        // In production, you might want to create a reminder_logs table
        
        return false; // For now, always allow sending
    }

    /**
     * Send reminder notification for the invoice
     */
    protected function sendReminderNotification(Invoice $invoice, int $days): bool
    {
        try {
            // Send to assigned user
            if ($invoice->assignedTo) {
                $invoice->assignedTo->notify(new InvoiceOverdueNotification($invoice, $days));
            }

            // Send to creator if different
            if ($invoice->createdBy && $invoice->created_by !== $invoice->assigned_to) {
                $invoice->createdBy->notify(new InvoiceOverdueNotification($invoice, $days));
            }

            // Send to finance managers
            $financeManagers = User::where('company_id', $invoice->company_id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'finance_manager');
                })
                ->get();

            foreach ($financeManagers as $manager) {
                $manager->notify(new InvoiceOverdueNotification($invoice, $days));
            }

            // Update invoice tracking (you might want to add last_reminder_sent_at field)
            $invoice->touch(); // Updates updated_at timestamp
            
            return true;
        } catch (\Exception $e) {
            $this->error("Failed to send notification: " . $e->getMessage());
            return false;
        }
    }
}
