<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProofRetentionService;
use App\Services\ProofSecurityService;
use App\Services\ProofAuditService;
use App\Services\ProofConsentService;
use App\Models\Company;
use Illuminate\Support\Facades\Log;

class ProofSecurityMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'proof:security-maintenance 
                          {--company= : Specific company ID to process}
                          {--dry-run : Run without making changes}
                          {--task=all : Specific task to run (cleanup|audit|consent|security)}
                          {--retention-months=24 : Retention period in months}
                          {--verbose : Detailed output}';

    /**
     * The console command description.
     */
    protected $description = 'Run automated proof security and maintenance tasks';

    protected ProofRetentionService $retentionService;
    protected ProofSecurityService $securityService;
    protected ProofAuditService $auditService;
    protected ProofConsentService $consentService;

    public function __construct(
        ProofRetentionService $retentionService,
        ProofSecurityService $securityService,
        ProofAuditService $auditService,
        ProofConsentService $consentService
    ) {
        parent::__construct();
        
        $this->retentionService = $retentionService;
        $this->securityService = $securityService;
        $this->auditService = $auditService;
        $this->consentService = $consentService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting Proof Security Maintenance...');
        
        $companyId = $this->option('company');
        $dryRun = $this->option('dry-run');
        $task = $this->option('task');
        $retentionMonths = (int) $this->option('retention-months');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $companies = $companyId 
            ? [Company::find($companyId)] 
            : Company::all();

        if (empty($companies) || !$companies[0]) {
            $this->error('No companies found to process');
            return 1;
        }

        $overallResults = [
            'companies_processed' => 0,
            'total_cleanup' => 0,
            'total_audit_entries_cleaned' => 0,
            'consent_violations' => 0,
            'security_issues' => 0,
        ];

        foreach ($companies as $company) {
            if (!$company) continue;
            
            $this->info("Processing company: {$company->name} (ID: {$company->id})");
            $overallResults['companies_processed']++;

            try {
                if ($task === 'all' || $task === 'cleanup') {
                    $cleanupResults = $this->runCleanupTasks($company->id, $dryRun, $retentionMonths);
                    $overallResults['total_cleanup'] += $cleanupResults['total_cleaned'];
                }

                if ($task === 'all' || $task === 'audit') {
                    $auditResults = $this->runAuditMaintenance($company->id, $dryRun, $retentionMonths);
                    $overallResults['total_audit_entries_cleaned'] += $auditResults['cleaned_entries'];
                }

                if ($task === 'all' || $task === 'consent') {
                    $consentResults = $this->runConsentMaintenance($company->id, $dryRun);
                    $overallResults['consent_violations'] += $consentResults['violations_found'];
                }

                if ($task === 'all' || $task === 'security') {
                    $securityResults = $this->runSecurityChecks($company->id, $dryRun);
                    $overallResults['security_issues'] += $securityResults['issues_found'];
                }

            } catch (\Exception $e) {
                $this->error("Error processing company {$company->id}: " . $e->getMessage());
                Log::error('Proof maintenance error', [
                    'company_id' => $company->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->displayResults($overallResults);
        
        Log::info('Proof security maintenance completed', $overallResults);
        
        return 0;
    }

    /**
     * Run cleanup tasks
     */
    protected function runCleanupTasks(int $companyId, bool $dryRun, int $retentionMonths): array
    {
        $this->line('  Running cleanup tasks...');
        
        $results = [
            'proofs_cleaned' => 0,
            'views_cleaned' => 0,
            'storage_freed' => 0,
            'total_cleaned' => 0,
        ];

        // Clean up eligible proofs
        $cleanupResults = $this->retentionService->performCleanup($companyId, $dryRun);
        $results['proofs_cleaned'] = $cleanupResults['deleted_proofs'];
        $results['storage_freed'] = $cleanupResults['freed_storage'];
        $results['total_cleaned'] += $cleanupResults['deleted_proofs'];

        if ($this->option('verbose')) {
            $this->info("    Proofs cleaned: {$cleanupResults['deleted_proofs']}");
            $this->info("    Assets deleted: {$cleanupResults['deleted_assets']}");
            $this->info("    Storage freed: " . $this->formatBytes($cleanupResults['freed_storage']));
        }

        // Clean up old view logs
        $viewCleanupResults = $this->retentionService->cleanupViewLogs($companyId);
        $results['views_cleaned'] = $viewCleanupResults['deleted_views'];
        $results['total_cleaned'] += $viewCleanupResults['deleted_views'];

        if ($this->option('verbose')) {
            $this->info("    View logs cleaned: {$viewCleanupResults['deleted_views']}");
        }

        // Archive old proofs
        $archiveResults = $this->retentionService->archiveOldProofs($companyId);
        $results['archived'] = $archiveResults['archived_count'];
        $results['total_cleaned'] += $archiveResults['archived_count'];

        if ($this->option('verbose')) {
            $this->info("    Proofs archived: {$archiveResults['archived_count']}");
        }

        return $results;
    }

    /**
     * Run audit maintenance
     */
    protected function runAuditMaintenance(int $companyId, bool $dryRun, int $retentionMonths): array
    {
        $this->line('  Running audit maintenance...');
        
        $results = [
            'cleaned_entries' => 0,
        ];

        if (!$dryRun) {
            $cleanupResults = $this->auditService->cleanupOldAuditLogs($companyId, $retentionMonths);
            $results['cleaned_entries'] = $cleanupResults['cleaned_entries'];
        } else {
            $this->info('    [DRY RUN] Would clean up old audit logs');
        }

        if ($this->option('verbose')) {
            $this->info("    Audit entries cleaned: {$results['cleaned_entries']}");
        }

        return $results;
    }

    /**
     * Run consent maintenance
     */
    protected function runConsentMaintenance(int $companyId, bool $dryRun): array
    {
        $this->line('  Running consent maintenance...');
        
        $results = [
            'violations_found' => 0,
            'expiring_consents' => 0,
        ];

        // Check for expiring consents
        $expiringConsents = $this->consentService->getExpiringConsents(30);
        $results['expiring_consents'] = count($expiringConsents);

        if (!empty($expiringConsents)) {
            $this->warn("    Found {$results['expiring_consents']} consents expiring within 30 days");
            
            if ($this->option('verbose')) {
                foreach ($expiringConsents as $expiring) {
                    $this->line("      Proof ID {$expiring['proof']->id}: expires in {$expiring['expires_in_days']} days");
                }
            }
        }

        return $results;
    }

    /**
     * Run security checks
     */
    protected function runSecurityChecks(int $companyId, bool $dryRun): array
    {
        $this->line('  Running security checks...');
        
        $results = [
            'issues_found' => 0,
            'high_security_proofs' => 0,
            'access_violations' => 0,
        ];

        // Get company audit stats
        $auditStats = $this->auditService->getCompanyAuditStats($companyId, 30);
        $results['access_violations'] = $auditStats['security_events'];

        if ($results['access_violations'] > 0) {
            $this->warn("    Found {$results['access_violations']} security events in last 30 days");
        }

        $results['issues_found'] = $results['access_violations'];

        if ($this->option('verbose')) {
            $this->info("    Security events: {$auditStats['security_events']}");
            $this->info("    Data protection events: {$auditStats['data_protection_events']}");
        }

        return $results;
    }

    /**
     * Display final results
     */
    protected function displayResults(array $results): void
    {
        $this->info('');
        $this->info('Maintenance Results:');
        $this->info('==================');
        $this->info("Companies processed: {$results['companies_processed']}");
        $this->info("Total items cleaned: {$results['total_cleanup']}");
        $this->info("Audit entries cleaned: {$results['total_audit_entries_cleaned']}");
        
        if ($results['consent_violations'] > 0) {
            $this->warn("Consent issues found: {$results['consent_violations']}");
        }
        
        if ($results['security_issues'] > 0) {
            $this->warn("Security issues found: {$results['security_issues']}");
        }
        
        if ($results['consent_violations'] === 0 && $results['security_issues'] === 0) {
            $this->info('No critical issues found.');
        }
    }

    /**
     * Format bytes for display
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}