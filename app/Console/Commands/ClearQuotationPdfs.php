<?php

namespace App\Console\Commands;

use App\Models\Quotation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ClearQuotationPdfs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotations:clear-pdfs
                            {--id= : Clear PDF for specific quotation ID}
                            {--all : Clear all quotation PDFs}
                            {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear cached quotation PDF files and reset database references';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $quotationId = $this->option('id');
        $clearAll = $this->option('all');
        $force = $this->option('force');

        if (!$quotationId && !$clearAll) {
            $this->error('Please specify either --id=<ID> or --all');
            return 1;
        }

        if ($quotationId) {
            return $this->clearSingleQuotation($quotationId, $force);
        }

        if ($clearAll) {
            return $this->clearAllQuotations($force);
        }

        return 0;
    }

    /**
     * Clear PDF for a single quotation
     */
    private function clearSingleQuotation(int $id, bool $force): int
    {
        $quotation = Quotation::find($id);

        if (!$quotation) {
            $this->error("Quotation with ID {$id} not found");
            return 1;
        }

        $this->info("Found Quotation: {$quotation->number}");
        $this->info("Current PDF Path: " . ($quotation->pdf_path ?? 'null'));

        if (!$force && !$this->confirm('Clear this PDF cache?', true)) {
            $this->info('Operation cancelled');
            return 0;
        }

        return $this->clearPdf($quotation);
    }

    /**
     * Clear PDFs for all quotations
     */
    private function clearAllQuotations(bool $force): int
    {
        $count = Quotation::whereNotNull('pdf_path')->count();

        if ($count === 0) {
            $this->info('No quotations with cached PDFs found');
            return 0;
        }

        $this->info("Found {$count} quotations with cached PDFs");

        if (!$force && !$this->confirm("Clear all {$count} cached PDFs?", true)) {
            $this->info('Operation cancelled');
            return 0;
        }

        $cleared = 0;
        $failed = 0;

        $quotations = Quotation::whereNotNull('pdf_path')->get();

        $this->withProgressBar($quotations, function ($quotation) use (&$cleared, &$failed) {
            if ($this->clearPdf($quotation, false) === 0) {
                $cleared++;
            } else {
                $failed++;
            }
        });

        $this->newLine(2);
        $this->info("Successfully cleared: {$cleared}");

        if ($failed > 0) {
            $this->warn("Failed: {$failed}");
        }

        return 0;
    }

    /**
     * Clear PDF for a quotation
     */
    private function clearPdf(Quotation $quotation, bool $verbose = true): int
    {
        try {
            if ($quotation->pdf_path) {
                // Delete the physical file
                if (Storage::exists($quotation->pdf_path)) {
                    Storage::delete($quotation->pdf_path);
                    if ($verbose) {
                        $this->info('✓ Deleted cached PDF file');
                    }
                } else {
                    if ($verbose) {
                        $this->warn('! PDF file not found in storage (path may be stale)');
                    }
                }
            }

            // Reset database references
            $quotation->update([
                'pdf_path' => null,
                'pdf_generated_at' => null,
            ]);

            if ($verbose) {
                $this->info('✓ Reset pdf_path and pdf_generated_at to null');
                $this->info('✓ PDF will regenerate on next preview/download');
            }

            return 0;
        } catch (\Exception $e) {
            if ($verbose) {
                $this->error("Error: {$e->getMessage()}");
            }
            return 1;
        }
    }
}
