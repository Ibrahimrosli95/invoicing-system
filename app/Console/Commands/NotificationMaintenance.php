<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class NotificationMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:maintenance 
                           {--cleanup-days=90 : Days of logs to keep}
                           {--retry-hours=24 : Hours back to retry failed notifications}
                           {--company-id= : Specific company ID to process}
                           {--dry-run : Show what would be done without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform notification system maintenance: cleanup old logs and retry failed notifications';

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
        $cleanupDays = (int) $this->option('cleanup-days');
        $retryHours = (int) $this->option('retry-hours');
        $companyId = $this->option('company-id');

        $this->info('Starting notification maintenance...');

        // Step 1: Cleanup old logs
        $this->info("\n1. Cleaning up old email logs (older than {$cleanupDays} days)...");
        
        if ($dryRun) {
            $this->line('DRY RUN: Would cleanup old logs');
        } else {
            $deleted = $this->notificationService->cleanupOldLogs($cleanupDays);
            $this->info("Deleted {$deleted} old email logs.");
        }

        // Step 2: Retry failed notifications
        $this->info("\n2. Retrying failed notifications (from last {$retryHours} hours)...");
        
        if ($companyId) {
            $this->retryFailedForCompany($companyId, $retryHours, $dryRun);
        } else {
            $this->retryFailedForAllCompanies($retryHours, $dryRun);
        }

        // Step 3: Show statistics
        $this->info("\n3. Delivery Statistics:");
        if ($companyId) {
            $this->showStatsForCompany($companyId);
        } else {
            $this->showOverallStats();
        }

        $this->info("\nNotification maintenance completed.");
        return Command::SUCCESS;
    }

    /**
     * Retry failed notifications for a specific company.
     */
    protected function retryFailedForCompany(int $companyId, int $retryHours, bool $dryRun): void
    {
        if ($dryRun) {
            $this->line("DRY RUN: Would retry failed notifications for company {$companyId}");
            return;
        }

        $retried = $this->notificationService->retryFailedNotifications($companyId, $retryHours);
        $this->info("Retried {$retried} failed notifications for company {$companyId}.");
    }

    /**
     * Retry failed notifications for all companies.
     */
    protected function retryFailedForAllCompanies(int $retryHours, bool $dryRun): void
    {
        // Get all companies with failed notifications
        $companies = \App\Models\Company::whereHas('emailDeliveryLogs', function ($query) use ($retryHours) {
            $query->whereIn('status', ['failed', 'bounced'])
                  ->where('created_at', '>=', now()->subHours($retryHours));
        })->get();

        if ($companies->isEmpty()) {
            $this->info('No companies with failed notifications found.');
            return;
        }

        $totalRetried = 0;

        foreach ($companies as $company) {
            if ($dryRun) {
                $this->line("DRY RUN: Would retry failed notifications for company {$company->id} ({$company->name})");
            } else {
                $retried = $this->notificationService->retryFailedNotifications($company->id, $retryHours);
                $totalRetried += $retried;
                
                if ($retried > 0) {
                    $this->line("Retried {$retried} notifications for {$company->name}");
                }
            }
        }

        if (!$dryRun) {
            $this->info("Total retried: {$totalRetried} notifications across {$companies->count()} companies.");
        }
    }

    /**
     * Show delivery statistics for a specific company.
     */
    protected function showStatsForCompany(int $companyId): void
    {
        $stats = $this->notificationService->getDeliveryStats($companyId, 7);
        
        $this->line("Company {$companyId} - Last 7 days:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Emails', $stats['total']],
                ['Successfully Sent', $stats['sent']],
                ['Failed', $stats['failed']],
                ['Pending', $stats['pending']],
                ['Success Rate', $stats['success_rate'] . '%'],
            ]
        );

        if (!empty($stats['by_type'])) {
            $this->line("\nBy Notification Type:");
            $typeRows = [];
            foreach ($stats['by_type'] as $type => $typeStats) {
                $typeRows[] = [
                    str_replace('_', ' ', ucwords($type, '_')),
                    $typeStats['total'],
                    $typeStats['sent'],
                    $typeStats['failed'],
                ];
            }
            
            $this->table(['Type', 'Total', 'Sent', 'Failed'], $typeRows);
        }
    }

    /**
     * Show overall delivery statistics.
     */
    protected function showOverallStats(): void
    {
        // Get stats for all companies
        $companies = \App\Models\Company::has('emailDeliveryLogs')->get();
        
        if ($companies->isEmpty()) {
            $this->line('No email delivery data found.');
            return;
        }

        $overallStats = [
            'total' => 0,
            'sent' => 0,
            'failed' => 0,
            'pending' => 0,
        ];

        $companyRows = [];

        foreach ($companies as $company) {
            $stats = $this->notificationService->getDeliveryStats($company->id, 7);
            
            $overallStats['total'] += $stats['total'];
            $overallStats['sent'] += $stats['sent'];
            $overallStats['failed'] += $stats['failed'];
            $overallStats['pending'] += $stats['pending'];
            
            $companyRows[] = [
                $company->name,
                $stats['total'],
                $stats['sent'],
                $stats['failed'],
                $stats['success_rate'] . '%',
            ];
        }

        // Calculate overall success rate
        $overallSuccessRate = $overallStats['total'] > 0 
            ? round(($overallStats['sent'] / $overallStats['total']) * 100, 2)
            : 0;

        $this->line("Overall Statistics (Last 7 days):");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Emails', $overallStats['total']],
                ['Successfully Sent', $overallStats['sent']],
                ['Failed', $overallStats['failed']],
                ['Pending', $overallStats['pending']],
                ['Success Rate', $overallSuccessRate . '%'],
            ]
        );

        $this->line("\nBy Company:");
        $this->table(['Company', 'Total', 'Sent', 'Failed', 'Success Rate'], $companyRows);
    }
}