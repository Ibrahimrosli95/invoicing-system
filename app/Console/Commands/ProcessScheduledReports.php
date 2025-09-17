<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledReport;
use App\Jobs\ExecuteScheduledReport;
use Carbon\Carbon;

class ProcessScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:process-scheduled 
                            {--dry-run : Show what would be processed without executing}
                            {--force : Force execution even if not due}
                            {--limit=50 : Maximum number of reports to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled reports that are due for execution';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $limit = (int) $this->option('limit');

        $this->info('Processing scheduled reports...');

        // Get reports due for execution
        $query = ScheduledReport::query()
            ->active()
            ->with(['company', 'user']);

        if ($force) {
            $this->warn('Force mode enabled - processing all active reports regardless of schedule');
        } else {
            $query->dueForExecution();
        }

        $scheduledReports = $query->limit($limit)->get();

        if ($scheduledReports->isEmpty()) {
            $this->info('No scheduled reports are due for execution.');
            return 0;
        }

        $this->info("Found {$scheduledReports->count()} scheduled reports to process.");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No reports will be executed');
            $this->table(
                ['ID', 'Name', 'Type', 'Company', 'Next Run', 'Recipients'],
                $scheduledReports->map(function ($report) {
                    return [
                        $report->id,
                        $report->name,
                        $report->report_type,
                        $report->company->name,
                        $report->next_run_at?->format('Y-m-d H:i:s') ?? 'Not set',
                        $report->recipients_count,
                    ];
                })
            );
            return 0;
        }

        $processed = 0;
        $failed = 0;

        foreach ($scheduledReports as $report) {
            try {
                $this->info("Processing: {$report->name} (ID: {$report->id})");

                // Dispatch the job
                ExecuteScheduledReport::dispatch($report);

                $processed++;
                $this->line("  ✓ Queued for execution");

            } catch (\Exception $e) {
                $failed++;
                $this->error("  ✗ Failed to queue: {$e->getMessage()}");
            }
        }

        // Summary
        $this->newLine();
        $this->info("Summary:");
        $this->line("  • Reports processed: {$processed}");
        if ($failed > 0) {
            $this->error("  • Reports failed: {$failed}");
        }

        // Show next scheduled reports
        $this->showUpcomingReports();

        return $failed > 0 ? 1 : 0;
    }

    /**
     * Show upcoming scheduled reports
     */
    private function showUpcomingReports()
    {
        $this->newLine();
        $this->info('Next 5 scheduled reports:');

        $upcoming = ScheduledReport::active()
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '>', now())
            ->orderBy('next_run_at')
            ->limit(5)
            ->with(['company'])
            ->get();

        if ($upcoming->isEmpty()) {
            $this->line('No upcoming scheduled reports.');
            return;
        }

        $this->table(
            ['Name', 'Type', 'Company', 'Next Run', 'In'],
            $upcoming->map(function ($report) {
                return [
                    $report->name,
                    $report->report_type,
                    $report->company->name,
                    $report->next_run_at->format('Y-m-d H:i:s'),
                    $report->next_run_at->diffForHumans(),
                ];
            })
        );
    }
}
