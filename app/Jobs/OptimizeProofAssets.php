<?php

namespace App\Jobs;

use App\Models\ProofAsset;
use App\Models\Proof;
use App\Services\FileProcessingService;
use App\Services\WebhookEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OptimizeProofAssets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ProofAsset $asset;
    public array $optimizationOptions;
    public int $timeout = 600; // 10 minutes for heavy processing
    public int $tries = 2;
    private $startTime;

    /**
     * Create a new job instance.
     */
    public function __construct(ProofAsset $asset, array $optimizationOptions = [])
    {
        $this->asset = $asset;
        $this->optimizationOptions = array_merge([
            'generate_thumbnails' => true,
            'optimize_original' => false,
            'create_web_versions' => true,
            'extract_metadata' => true,
            'quality_analysis' => false,
        ], $optimizationOptions);
        
        // Use dedicated processing queue
        $this->onQueue('proof-processing');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->startTime = now();
        
        Log::info('Starting proof asset optimization', [
            'asset_id' => $this->asset->id,
            'filename' => $this->asset->filename,
            'file_type' => $this->asset->file_type,
            'options' => $this->optimizationOptions,
        ]);

        try {
            // Verify asset exists and is accessible
            if (!$this->verifyAssetAccess()) {
                throw new \Exception("Asset file not accessible: {$this->asset->file_path}");
            }

            // Update status to processing
            $this->asset->update(['status' => 'processing']);

            $fileProcessingService = app(FileProcessingService::class);
            $optimizationResults = [];

            // Process based on file type
            switch ($this->asset->file_type) {
                case 'image':
                    $optimizationResults = $this->optimizeImage($fileProcessingService);
                    break;
                    
                case 'video':
                    $optimizationResults = $this->optimizeVideo($fileProcessingService);
                    break;
                    
                case 'document':
                    $optimizationResults = $this->optimizeDocument($fileProcessingService);
                    break;
                    
                default:
                    $optimizationResults = $this->optimizeGenericAsset($fileProcessingService);
            }

            // Update asset with optimization results
            $this->updateAssetWithResults($optimizationResults);

            // Update related proof analytics
            $this->updateProofAnalytics();

            // Dispatch webhook for asset optimization completion
            $this->dispatchAssetOptimizationWebhook($optimizationResults);

            Log::info('Proof asset optimization completed', [
                'asset_id' => $this->asset->id,
                'optimization_results' => $optimizationResults,
            ]);

        } catch (\Exception $e) {
            Log::error('Proof asset optimization failed', [
                'asset_id' => $this->asset->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update status to failed
            $this->asset->update([
                'status' => 'failed',
                'metadata' => array_merge($this->asset->metadata ?? [], [
                    'optimization_error' => $e->getMessage(),
                    'optimization_failed_at' => now(),
                ])
            ]);

            throw $e;
        }
    }

    /**
     * Verify asset file access
     */
    private function verifyAssetAccess(): bool
    {
        return Storage::exists($this->asset->file_path) && 
               filesize(Storage::path($this->asset->file_path)) > 0;
    }

    /**
     * Optimize image assets
     */
    private function optimizeImage(FileProcessingService $fileProcessingService): array
    {
        $results = [
            'type' => 'image',
            'thumbnails_generated' => [],
            'web_versions_created' => [],
            'metadata_extracted' => [],
            'optimization_stats' => [],
        ];

        $filePath = Storage::path($this->asset->file_path);
        $fileInfo = pathinfo($filePath);

        // Generate thumbnails if requested
        if ($this->optimizationOptions['generate_thumbnails']) {
            $thumbnails = $this->generateImageThumbnails($fileProcessingService, $filePath, $fileInfo);
            $results['thumbnails_generated'] = $thumbnails;
        }

        // Create web-optimized versions
        if ($this->optimizationOptions['create_web_versions']) {
            $webVersions = $this->createWebOptimizedImages($fileProcessingService, $filePath, $fileInfo);
            $results['web_versions_created'] = $webVersions;
        }

        // Extract image metadata
        if ($this->optimizationOptions['extract_metadata']) {
            $metadata = $this->extractImageMetadata($filePath);
            $results['metadata_extracted'] = $metadata;
        }

        // Optimize original if requested
        if ($this->optimizationOptions['optimize_original']) {
            $optimization = $this->optimizeOriginalImage($fileProcessingService, $filePath);
            $results['optimization_stats'] = $optimization;
        }

        return $results;
    }

    /**
     * Optimize video assets
     */
    private function optimizeVideo(FileProcessingService $fileProcessingService): array
    {
        $results = [
            'type' => 'video',
            'thumbnails_generated' => [],
            'preview_created' => false,
            'metadata_extracted' => [],
            'compression_stats' => [],
        ];

        $filePath = Storage::path($this->asset->file_path);

        // Generate video thumbnails/preview frames
        if ($this->optimizationOptions['generate_thumbnails']) {
            $thumbnails = $this->generateVideoThumbnails($filePath);
            $results['thumbnails_generated'] = $thumbnails;
        }

        // Extract video metadata
        if ($this->optimizationOptions['extract_metadata']) {
            $metadata = $this->extractVideoMetadata($filePath);
            $results['metadata_extracted'] = $metadata;
        }

        // Create web preview (placeholder functionality)
        if ($this->optimizationOptions['create_web_versions']) {
            $preview = $this->createVideoPreview($filePath);
            $results['preview_created'] = $preview;
        }

        return $results;
    }

    /**
     * Optimize document assets
     */
    private function optimizeDocument(FileProcessingService $fileProcessingService): array
    {
        $results = [
            'type' => 'document',
            'preview_generated' => false,
            'text_extracted' => false,
            'metadata_extracted' => [],
        ];

        $filePath = Storage::path($this->asset->file_path);

        // Generate document preview
        if ($this->optimizationOptions['generate_thumbnails']) {
            $preview = $this->generateDocumentPreview($filePath);
            $results['preview_generated'] = $preview;
        }

        // Extract document metadata
        if ($this->optimizationOptions['extract_metadata']) {
            $metadata = $this->extractDocumentMetadata($filePath);
            $results['metadata_extracted'] = $metadata;
        }

        // Extract searchable text (for future search functionality)
        $textContent = $this->extractDocumentText($filePath);
        $results['text_extracted'] = !empty($textContent);

        return $results;
    }

    /**
     * Optimize generic assets
     */
    private function optimizeGenericAsset(FileProcessingService $fileProcessingService): array
    {
        return [
            'type' => 'generic',
            'file_analyzed' => true,
            'metadata_extracted' => $this->extractBasicMetadata(),
        ];
    }

    /**
     * Generate image thumbnails
     */
    private function generateImageThumbnails(FileProcessingService $fileProcessingService, string $filePath, array $fileInfo): array
    {
        $thumbnails = [];
        $sizes = [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
        ];

        foreach ($sizes as $sizeName => $dimensions) {
            try {
                $thumbnailPath = $this->generateThumbnailPath($fileInfo, $sizeName);
                $success = $fileProcessingService->generateThumbnail($filePath, $thumbnailPath, $dimensions[0], $dimensions[1]);
                
                if ($success) {
                    $thumbnails[$sizeName] = [
                        'path' => $thumbnailPath,
                        'size' => $dimensions,
                        'file_size' => filesize($thumbnailPath),
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Failed to generate thumbnail', [
                    'asset_id' => $this->asset->id,
                    'size' => $sizeName,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $thumbnails;
    }

    /**
     * Create web-optimized image versions
     */
    private function createWebOptimizedImages(FileProcessingService $fileProcessingService, string $filePath, array $fileInfo): array
    {
        $webVersions = [];
        
        // Create compressed web version
        try {
            $webPath = $this->generateWebVersionPath($fileInfo);
            $success = $fileProcessingService->createWebOptimizedVersion($filePath, $webPath, 85); // 85% quality
            
            if ($success) {
                $webVersions['compressed'] = [
                    'path' => $webPath,
                    'quality' => 85,
                    'file_size' => filesize($webPath),
                    'size_reduction' => (1 - filesize($webPath) / filesize($filePath)) * 100,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to create web-optimized version', [
                'asset_id' => $this->asset->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $webVersions;
    }

    /**
     * Extract image metadata
     */
    private function extractImageMetadata(string $filePath): array
    {
        $metadata = [];
        
        try {
            $imageInfo = getimagesize($filePath);
            if ($imageInfo) {
                $metadata = [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'type' => image_type_to_mime_type($imageInfo[2]),
                    'bits' => $imageInfo['bits'] ?? null,
                    'channels' => $imageInfo['channels'] ?? null,
                ];
            }

            // Extract EXIF data if available
            if (function_exists('exif_read_data') && in_array(strtolower(pathinfo($filePath, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'tiff'])) {
                $exif = @exif_read_data($filePath);
                if ($exif) {
                    $metadata['exif'] = [
                        'make' => $exif['Make'] ?? null,
                        'model' => $exif['Model'] ?? null,
                        'datetime' => $exif['DateTime'] ?? null,
                        'orientation' => $exif['Orientation'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to extract image metadata', [
                'asset_id' => $this->asset->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }

    /**
     * Optimize original image
     */
    private function optimizeOriginalImage(FileProcessingService $fileProcessingService, string $filePath): array
    {
        $originalSize = filesize($filePath);
        $backupPath = $filePath . '.backup';
        
        try {
            // Create backup
            copy($filePath, $backupPath);
            
            // Optimize image
            $success = $fileProcessingService->optimizeImage($filePath, 90); // 90% quality
            
            if ($success) {
                $newSize = filesize($filePath);
                $reduction = (($originalSize - $newSize) / $originalSize) * 100;
                
                // Remove backup if optimization was successful and saved space
                if ($reduction > 5) { // Only keep optimization if we saved >5%
                    unlink($backupPath);
                    return [
                        'optimized' => true,
                        'original_size' => $originalSize,
                        'new_size' => $newSize,
                        'reduction_percent' => round($reduction, 2),
                    ];
                } else {
                    // Restore original if optimization wasn't worthwhile
                    copy($backupPath, $filePath);
                    unlink($backupPath);
                    return ['optimized' => false, 'reason' => 'insufficient_savings'];
                }
            }
        } catch (\Exception $e) {
            // Restore original on error
            if (file_exists($backupPath)) {
                copy($backupPath, $filePath);
                unlink($backupPath);
            }
            throw $e;
        }

        return ['optimized' => false, 'reason' => 'optimization_failed'];
    }

    /**
     * Generate video thumbnails (placeholder functionality)
     */
    private function generateVideoThumbnails(string $filePath): array
    {
        // This would use FFmpeg in a production environment
        // For now, return placeholder functionality
        return [
            'placeholder_created' => true,
            'frames_extracted' => 0,
            'duration_analyzed' => false,
        ];
    }

    /**
     * Extract video metadata (placeholder functionality)
     */
    private function extractVideoMetadata(string $filePath): array
    {
        // This would use FFmpeg or similar in production
        return [
            'duration' => null,
            'dimensions' => null,
            'codec' => null,
            'bitrate' => null,
        ];
    }

    /**
     * Create video preview (placeholder functionality)
     */
    private function createVideoPreview(string $filePath): bool
    {
        // Placeholder for video preview creation
        return false;
    }

    /**
     * Generate document preview
     */
    private function generateDocumentPreview(string $filePath): bool
    {
        // This would convert first page of document to image
        // Placeholder for now
        return false;
    }

    /**
     * Extract document metadata
     */
    private function extractDocumentMetadata(string $filePath): array
    {
        $metadata = ['pages' => null, 'title' => null, 'author' => null];
        
        try {
            // Basic file info
            $metadata['file_size'] = filesize($filePath);
            $metadata['modified_time'] = filemtime($filePath);
            
            // For PDF files, could extract more metadata
            if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'pdf') {
                // Placeholder for PDF metadata extraction
                $metadata['type'] = 'pdf';
            }
        } catch (\Exception $e) {
            Log::warning('Failed to extract document metadata', [
                'asset_id' => $this->asset->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $metadata;
    }

    /**
     * Extract document text
     */
    private function extractDocumentText(string $filePath): string
    {
        // This would extract searchable text from documents
        // Placeholder for now
        return '';
    }

    /**
     * Extract basic metadata
     */
    private function extractBasicMetadata(): array
    {
        $filePath = Storage::path($this->asset->file_path);
        
        return [
            'file_size' => filesize($filePath),
            'modified_time' => filemtime($filePath),
            'mime_type' => mime_content_type($filePath),
        ];
    }

    /**
     * Generate thumbnail path
     */
    private function generateThumbnailPath(array $fileInfo, string $size): string
    {
        $directory = dirname(Storage::path($this->asset->file_path));
        return $directory . '/thumbnails/' . $fileInfo['filename'] . '_' . $size . '.jpg';
    }

    /**
     * Generate web version path
     */
    private function generateWebVersionPath(array $fileInfo): string
    {
        $directory = dirname(Storage::path($this->asset->file_path));
        return $directory . '/web/' . $fileInfo['filename'] . '_web.' . $fileInfo['extension'];
    }

    /**
     * Update asset with optimization results
     */
    private function updateAssetWithResults(array $results): void
    {
        $metadata = array_merge($this->asset->metadata ?? [], [
            'optimization_results' => $results,
            'optimized_at' => now(),
            'optimization_version' => '1.0',
        ]);

        // Update thumbnail path if thumbnails were generated
        $thumbnailPath = null;
        if (!empty($results['thumbnails_generated']['medium'])) {
            $thumbnailPath = str_replace(Storage::path(''), '', $results['thumbnails_generated']['medium']['path']);
        }

        $this->asset->update([
            'status' => 'processed',
            'thumbnail_path' => $thumbnailPath,
            'metadata' => $metadata,
            'processed_at' => now(),
        ]);
    }

    /**
     * Update proof analytics
     */
    private function updateProofAnalytics(): void
    {
        $proof = $this->asset->proof;
        if (!$proof) return;

        // Update proof metadata with asset optimization info
        $proofMetadata = $proof->metadata ?? [];
        $proofMetadata['assets_optimized'] = ($proofMetadata['assets_optimized'] ?? 0) + 1;
        $proofMetadata['last_asset_optimization'] = now();

        $proof->update(['metadata' => $proofMetadata]);
    }

    /**
     * Dispatch webhook for asset optimization completion
     */
    private function dispatchAssetOptimizationWebhook(array $optimizationResults): void
    {
        try {
            $webhookService = app(WebhookEventService::class);
            
            // Add processing time to optimization results
            $optimizationResults['processing_time'] = $this->startTime ? now()->diffInSeconds($this->startTime) : null;
            $optimizationResults['file_improvements'] = $this->calculateFileImprovements($optimizationResults);
            
            $webhookService->proofAssetOptimized($this->asset, $optimizationResults);
            
        } catch (\Exception $e) {
            Log::error('Failed to dispatch proof asset optimization webhook', [
                'asset_id' => $this->asset->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate file improvements from optimization results
     */
    private function calculateFileImprovements(array $optimizationResults): array
    {
        $improvements = [];
        
        // Calculate thumbnail benefits
        if (!empty($optimizationResults['thumbnails_generated'])) {
            $improvements['thumbnails_count'] = count($optimizationResults['thumbnails_generated']);
            $improvements['thumbnail_sizes_available'] = array_keys($optimizationResults['thumbnails_generated']);
        }
        
        // Calculate optimization benefits
        if (!empty($optimizationResults['optimization_stats'])) {
            $stats = $optimizationResults['optimization_stats'];
            if (isset($stats['reduction_percent'])) {
                $improvements['size_reduction_percent'] = $stats['reduction_percent'];
                $improvements['optimized'] = true;
            }
        }
        
        // Calculate web version benefits
        if (!empty($optimizationResults['web_versions_created'])) {
            $webVersion = $optimizationResults['web_versions_created']['compressed'] ?? null;
            if ($webVersion) {
                $improvements['web_version_created'] = true;
                $improvements['web_version_reduction'] = $webVersion['size_reduction'] ?? 0;
            }
        }
        
        return $improvements;
    }

    /**
     * Handle failed job
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('OptimizeProofAssets job failed', [
            'asset_id' => $this->asset->id,
            'filename' => $this->asset->filename,
            'error' => $exception->getMessage(),
        ]);

        // Update asset status to failed
        $this->asset->update([
            'status' => 'failed',
            'metadata' => array_merge($this->asset->metadata ?? [], [
                'optimization_error' => $exception->getMessage(),
                'failed_at' => now(),
            ])
        ]);
    }
}