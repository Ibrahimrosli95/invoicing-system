<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use App\Services\FileProcessingService;

class ProofAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'proof_id',
        'company_id',
        'filename',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'type',
        'width',
        'height',
        'alt_text',
        'duration',
        'thumbnail_path',
        'title',
        'description',
        'sort_order',
        'is_primary',
        'is_public',
        'processing_status',
        'view_count',
        'download_count',
        'uploaded_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_public' => 'boolean',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
        'view_count' => 'integer',
        'download_count' => 'integer',
    ];

    protected $attributes = [
        'sort_order' => 0,
        'is_primary' => false,
        'is_public' => true,
        'processing_status' => 'completed',
        'view_count' => 0,
        'download_count' => 0,
    ];

    // Constants for asset types
    const TYPES = [
        'image' => 'Image',
        'video' => 'Video',
        'document' => 'Document',
        'audio' => 'Audio',
        'other' => 'Other',
    ];

    // Constants for processing status
    const PROCESSING_STATUSES = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'optimizing' => 'Optimizing',
    ];

    // Boot method to handle UUID generation
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid();
            }
            
            if (auth()->check()) {
                $model->uploaded_by = auth()->id();
                $model->company_id = auth()->user()->company_id;
            }
        });
        
        static::deleting(function ($model) {
            // Delete the actual file when model is deleted
            $model->deleteFile();
        });
    }

    // Relationships
    public function proof(): BelongsTo
    {
        return $this->belongsTo(Proof::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes
    public function scopeForCompany(Builder $query): Builder
    {
        if (auth()->check()) {
            return $query->where('company_id', auth()->user()->company_id);
        }
        return $query;
    }

    public function scopeImages(Builder $query): Builder
    {
        return $query->where('type', 'image');
    }

    public function scopeVideos(Builder $query): Builder
    {
        return $query->where('type', 'video');
    }

    public function scopeDocuments(Builder $query): Builder
    {
        return $query->where('type', 'document');
    }

    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('processing_status', 'completed');
    }

    // Business Logic Methods
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? 'Unknown';
    }

    public function getProcessingStatusLabelAttribute(): string
    {
        return self::PROCESSING_STATUSES[$this->processing_status] ?? 'Unknown';
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isDocument(): bool
    {
        return $this->type === 'document';
    }

    public function isAudio(): bool
    {
        return $this->type === 'audio';
    }

    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    public function isProcessingComplete(): bool
    {
        return $this->processing_status === 'completed';
    }

    public function hasProcessingFailed(): bool
    {
        return $this->processing_status === 'failed';
    }

    public function getHumanReadableSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDimensionsString(): ?string
    {
        if ($this->isImage() && $this->width && $this->height) {
            return $this->width . ' × ' . $this->height;
        }
        
        return null;
    }

    public function getDurationString(): ?string
    {
        if (!$this->duration) {
            return null;
        }
        
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function getThumbnailUrl(): ?string
    {
        if ($this->thumbnail_path) {
            return Storage::url($this->thumbnail_path);
        }
        
        return null;
    }

    public function getDownloadUrl(): string
    {
        return route('proof-assets.download', $this->uuid);
    }

    public function recordView(): void
    {
        $this->increment('view_count');
    }

    public function recordDownload(): void
    {
        $this->increment('download_count');
    }

    public function makePrimary(): bool
    {
        // Unset other primary assets for this proof
        $this->proof->assets()->where('id', '!=', $this->id)->update(['is_primary' => false]);
        
        return $this->update(['is_primary' => true]);
    }

    public function unmakePrimary(): bool
    {
        return $this->update(['is_primary' => false]);
    }

    public function markAsPublic(): bool
    {
        return $this->update(['is_public' => true]);
    }

    public function markAsPrivate(): bool
    {
        return $this->update(['is_public' => false]);
    }

    public function updateProcessingStatus(string $status): bool
    {
        return $this->update(['processing_status' => $status]);
    }

    public function deleteFile(): bool
    {
        // Use FileProcessingService for comprehensive cleanup
        $fileProcessingService = app(FileProcessingService::class);
        return $fileProcessingService->cleanup($this);
    }

    // Static methods for file handling
    public static function createFromUpload(UploadedFile $file, Proof $proof, array $data = []): self
    {
        // Use FileProcessingService for validation
        $fileProcessingService = app(\App\Services\FileProcessingService::class);
        $validation = $fileProcessingService->validateFile($file);
        
        if (!$validation['valid']) {
            throw new \InvalidArgumentException('File validation failed: ' . implode(', ', $validation['errors']));
        }
        
        // Generate filename and path
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = "proofs/{$proof->company_id}/{$proof->id}/" . $filename;
        
        // Store file
        $file->storeAs(dirname($path), $filename, 'public');
        
        // Create asset record
        $asset = self::create(array_merge([
            'proof_id' => $proof->id,
            'company_id' => $proof->company_id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $validation['mime_type'],
            'file_size' => $validation['file_size'],
            'type' => $validation['type'],
            'processing_status' => 'pending',
        ], $data));
        
        // Queue file processing for background execution to improve response time
        \App\Jobs\ProcessProofAsset::dispatch($asset);
        
        return $asset;
    }

    public static function determineTypeFromMime(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        } elseif (in_array($mimeType, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            return 'document';
        }
        
        return 'other';
    }

    // File processing methods using FileProcessingService
    public function processFile(?\Illuminate\Http\UploadedFile $uploadedFile = null): array
    {
        $fileProcessingService = app(\App\Services\FileProcessingService::class);
        return $fileProcessingService->processFile($uploadedFile ?? $this->getUploadedFileInstance(), $this);
    }
    
    /**
     * Get a mock UploadedFile instance for reprocessing
     */
    protected function getUploadedFileInstance(): \Illuminate\Http\UploadedFile
    {
        $fullPath = Storage::disk('public')->path($this->file_path);
        
        return new \Illuminate\Http\UploadedFile(
            $fullPath,
            $this->original_filename,
            $this->mime_type,
            null,
            true // test mode
        );
    }

    // Image processing methods (legacy compatibility)
    public function extractImageDimensions(): bool
    {
        if (!$this->isImage()) {
            return false;
        }
        
        $results = $this->processFile();
        return $results['dimensions_extracted'] ?? false;
    }

    // Generate thumbnail for images (legacy compatibility)
    public function generateThumbnail(int $width = 300, int $height = 300): bool
    {
        if (!$this->isImage()) {
            return false;
        }
        
        $results = $this->processFile();
        return !empty($results['thumbnails_generated']);
    }

    // Asset search and categorization methods
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
    
    public function scopeByProcessingStatus(Builder $query, string $status): Builder
    {
        return $query->where('processing_status', $status);
    }
    
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('original_filename', 'like', "%{$search}%")
              ->orWhere('alt_text', 'like', "%{$search}%");
        });
    }
    
    public function scopeByDimensionsRange(Builder $query, ?int $minWidth = null, ?int $maxWidth = null, ?int $minHeight = null, ?int $maxHeight = null): Builder
    {
        if ($minWidth) {
            $query->where('width', '>=', $minWidth);
        }
        if ($maxWidth) {
            $query->where('width', '<=', $maxWidth);
        }
        if ($minHeight) {
            $query->where('height', '>=', $minHeight);
        }
        if ($maxHeight) {
            $query->where('height', '<=', $maxHeight);
        }
        
        return $query;
    }
    
    public function scopeByFileSize(Builder $query, ?int $minSize = null, ?int $maxSize = null): Builder
    {
        if ($minSize) {
            $query->where('file_size', '>=', $minSize);
        }
        if ($maxSize) {
            $query->where('file_size', '<=', $maxSize);
        }
        
        return $query;
    }
    
    public function scopeWithProof(Builder $query): Builder
    {
        return $query->with('proof');
    }
    
    public function scopeOrdered(Builder $query, string $column = 'sort_order', string $direction = 'asc'): Builder
    {
        return $query->orderBy($column, $direction);
    }
    
    // Asset categorization helpers
    public function getAssetCategory(): string
    {
        if ($this->isImage()) {
            if ($this->isPrimary()) {
                return 'primary_image';
            }
            return 'gallery_image';
        } elseif ($this->isVideo()) {
            return 'video_content';
        } elseif ($this->type === 'document') {
            return 'document_attachment';
        }
        
        return 'other_asset';
    }
    
    public function getAssetTags(): array
    {
        $tags = [];
        
        // Type-based tags
        $tags[] = $this->type;
        
        // Category tags
        $tags[] = $this->getAssetCategory();
        
        // Processing status tags
        $tags[] = "status_{$this->processing_status}";
        
        // Size-based tags
        if ($this->isImage() && $this->width && $this->height) {
            $aspectRatio = $this->width / $this->height;
            if ($aspectRatio > 1.5) {
                $tags[] = 'landscape';
            } elseif ($aspectRatio < 0.75) {
                $tags[] = 'portrait';
            } else {
                $tags[] = 'square';
            }
            
            // Resolution tags
            $totalPixels = $this->width * $this->height;
            if ($totalPixels > 8000000) { // 8MP+
                $tags[] = 'high_resolution';
            } elseif ($totalPixels > 2000000) { // 2MP+
                $tags[] = 'medium_resolution';
            } else {
                $tags[] = 'low_resolution';
            }
        }
        
        // File size tags
        $sizeInMB = $this->file_size / (1024 * 1024);
        if ($sizeInMB > 10) {
            $tags[] = 'large_file';
        } elseif ($sizeInMB > 1) {
            $tags[] = 'medium_file';
        } else {
            $tags[] = 'small_file';
        }
        
        return array_unique($tags);
    }

    // JSON representation for APIs
    public function toApiArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'filename' => $this->filename,
            'original_filename' => $this->original_filename,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'human_readable_size' => $this->getHumanReadableSize(),
            'dimensions' => $this->getDimensionsString(),
            'duration' => $this->getDurationString(),
            'is_primary' => $this->is_primary,
            'is_public' => $this->is_public,
            'processing_status' => $this->processing_status,
            'processing_status_label' => $this->processing_status_label,
            'view_count' => $this->view_count,
            'download_count' => $this->download_count,
            'url' => $this->getUrl(),
            'thumbnail_url' => $this->getThumbnailUrl(),
            'download_url' => $this->getDownloadUrl(),
            'created_at' => $this->created_at,
        ];
    }
}
