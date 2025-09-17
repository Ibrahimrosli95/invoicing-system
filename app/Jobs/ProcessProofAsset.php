<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\ProofAsset;
use App\Services\FileProcessingService;

class ProcessProofAsset implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;
    
    protected ProofAsset $asset;

    /**
     * Create a new job instance.
     */
    public function __construct(ProofAsset $asset)
    {
        $this->asset = $asset;
        $this->onQueue('proof-processing');
    }

    /**
     * Execute the job.
     */
    public function handle(FileProcessingService $fileProcessingService): void
    {
        try {
            Log::info("Starting background processing for proof asset: {$this->asset->id}");
            
            // Update status to processing
            $this->asset->update(['processing_status' => 'processing']);
            
            // Process the file
            $results = $this->asset->processFile();
            
            // Log results
            Log::info("Proof asset {$this->asset->id} processed successfully", [
                'thumbnails_generated' => count($results['thumbnails_generated'] ?? []),
                'dimensions_extracted' => $results['dimensions_extracted'] ?? false,
                'video_thumbnail' => $results['video_thumbnail'] ?? false,
                'processing_errors' => $results['processing_errors'] ?? []
            ]);
            
            // Update final status
            if (!empty($results['processing_errors'])) {
                $this->asset->update([
                    'processing_status' => 'completed_with_errors',
                    'metadata' => array_merge($this->asset->metadata ?? [], [
                        'processing_errors' => $results['processing_errors']
                    ])
                ]);
            } else {
                $this->asset->update(['processing_status' => 'completed']);
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to process proof asset {$this->asset->id}: " . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            
            $this->asset->update([
                'processing_status' => 'failed',
                'metadata' => array_merge($this->asset->metadata ?? [], [
                    'processing_error' => $e->getMessage(),
                    'failed_at' => now()->toISOString()
                ])
            ]);
            
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Proof asset processing job failed permanently: {$this->asset->id}", [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        $this->asset->update([
            'processing_status' => 'failed',
            'metadata' => array_merge($this->asset->metadata ?? [], [
                'processing_error' => $exception->getMessage(),
                'failed_permanently_at' => now()->toISOString(),
                'attempts' => $this->attempts()
            ])
        ]);
    }
}
