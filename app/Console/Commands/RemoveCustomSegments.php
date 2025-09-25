<?php

namespace App\Console\Commands;

use App\Models\CustomerSegment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveCustomSegments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'segments:remove-custom {--dry-run : Show what would be removed without actually removing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove any customer segments named "Custom" and associated data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('Searching for "Custom" customer segments...');

        // Find all segments named "Custom" (case insensitive)
        $customSegments = CustomerSegment::whereRaw('LOWER(name) = ?', ['custom'])->get();

        if ($customSegments->isEmpty()) {
            $this->info('✅ No "Custom" segments found. System is clean.');
            return 0;
        }

        $this->warn("Found {$customSegments->count()} 'Custom' segment(s):");

        foreach ($customSegments as $segment) {
            $companyName = $segment->company ? $segment->company->name : 'Unknown';
            $this->line("  - ID: {$segment->id}, Name: '{$segment->name}', Company: {$companyName}");
        }

        if ($dryRun) {
            $this->info("\n🔍 DRY RUN MODE - No changes will be made");
            $this->info("To actually remove these segments, run without --dry-run flag");
            return 0;
        }

        // Ask for confirmation
        if (!$this->confirm('Do you want to remove these "Custom" segments? This action cannot be undone.')) {
            $this->info('Operation cancelled.');
            return 1;
        }

        $this->info('Removing "Custom" segments...');

        DB::beginTransaction();

        try {
            $removedCount = 0;

            foreach ($customSegments as $segment) {
                // Check if segment has associated pricing tiers
                $tierCount = $segment->pricingTiers()->count();

                if ($tierCount > 0) {
                    $this->warn("  Removing {$tierCount} pricing tiers for segment: {$segment->name}");
                    $segment->pricingTiers()->delete();
                }

                $companyName = $segment->company ? $segment->company->name : 'Unknown';
                $this->info("  Removing segment: {$segment->name} (Company: {$companyName})");
                $segment->delete();
                $removedCount++;
            }

            DB::commit();

            $this->info("\n✅ Successfully removed {$removedCount} 'Custom' segments.");
            $this->info("The system now contains only the standard segments: End User, Contractor, Dealer");

        } catch (\Exception $e) {
            DB::rollback();
            $this->error("❌ Error removing segments: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
