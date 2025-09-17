<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class AssessmentPhoto extends Model
{
    protected $fillable = [
        'assessment_id',
        'item_id',
        'section_id',
        'file_path',
        'file_name',
        'original_name',
        'file_size',
        'mime_type',
        'title',
        'description',
        'photo_type',
        'width',
        'height',
        'camera_make',
        'camera_model',
        'taken_at',
        'latitude',
        'longitude',
        'location_address',
        'is_processed',
        'thumbnail_path',
        'processing_metadata',
        'display_order',
        'is_featured',
        'include_in_report',
        'annotations',
        'technical_notes',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'taken_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_processed' => 'boolean',
        'processing_metadata' => 'array',
        'display_order' => 'integer',
        'is_featured' => 'boolean',
        'include_in_report' => 'boolean',
        'annotations' => 'array',
    ];

    // Photo Type Constants
    const TYPE_BEFORE = 'before';
    const TYPE_DURING = 'during';
    const TYPE_AFTER = 'after';
    const TYPE_ISSUE = 'issue';
    const TYPE_GENERAL = 'general';
    const TYPE_REFERENCE = 'reference';

    /**
     * Get all photo types.
     */
    public static function getPhotoTypes(): array
    {
        return [
            self::TYPE_BEFORE => 'Before',
            self::TYPE_DURING => 'During Assessment',
            self::TYPE_AFTER => 'After',
            self::TYPE_ISSUE => 'Issue/Problem',
            self::TYPE_GENERAL => 'General',
            self::TYPE_REFERENCE => 'Reference',
        ];
    }

    // Relationships
    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(AssessmentItem::class, 'item_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(AssessmentSection::class, 'section_id');
    }

    // Scopes
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeForReport(Builder $query): Builder
    {
        return $query->where('include_in_report', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('photo_type', $type);
    }

    public function scopeProcessed(Builder $query): Builder
    {
        return $query->where('is_processed', true);
    }

    public function scopeWithLocation(Builder $query): Builder
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    // Business Logic Methods

    /**
     * Get the full file path.
     */
    public function getFullPath(): string
    {
        return Storage::path($this->file_path);
    }

    /**
     * Get the file URL.
     */
    public function getFileUrl(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the thumbnail URL.
     */
    public function getThumbnailUrl(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }
        
        return Storage::url($this->thumbnail_path);
    }

    /**
     * Check if file exists.
     */
    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    /**
     * Get file size in human readable format.
     */
    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the photo is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the photo is a video.
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Get photo dimensions as string.
     */
    public function getDimensionsString(): ?string
    {
        if ($this->width && $this->height) {
            return "{$this->width} × {$this->height}";
        }
        
        return null;
    }

    /**
     * Get photo type color for UI.
     */
    public function getTypeColor(): string
    {
        return match($this->photo_type) {
            self::TYPE_BEFORE => 'blue',
            self::TYPE_DURING => 'yellow',
            self::TYPE_AFTER => 'green',
            self::TYPE_ISSUE => 'red',
            self::TYPE_GENERAL => 'gray',
            self::TYPE_REFERENCE => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get location coordinates.
     */
    public function getCoordinates(): ?array
    {
        if ($this->latitude && $this->longitude) {
            return [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ];
        }
        
        return null;
    }

    /**
     * Get Google Maps URL.
     */
    public function getGoogleMapsUrl(): ?string
    {
        $coordinates = $this->getCoordinates();
        if (!$coordinates) {
            return null;
        }
        
        return "https://www.google.com/maps?q={$coordinates['lat']},{$coordinates['lng']}";
    }

    /**
     * Check if photo has annotations.
     */
    public function hasAnnotations(): bool
    {
        return !empty($this->annotations);
    }

    /**
     * Get annotation count.
     */
    public function getAnnotationCount(): int
    {
        return count($this->annotations ?? []);
    }

    /**
     * Add annotation.
     */
    public function addAnnotation(array $annotation): bool
    {
        $annotations = $this->annotations ?? [];
        $annotation['id'] = uniqid();
        $annotation['created_at'] = now()->toISOString();
        $annotations[] = $annotation;
        
        $this->annotations = $annotations;
        return $this->save();
    }

    /**
     * Remove annotation.
     */
    public function removeAnnotation(string $annotationId): bool
    {
        $annotations = $this->annotations ?? [];
        $annotations = array_filter($annotations, fn($a) => $a['id'] !== $annotationId);
        
        $this->annotations = array_values($annotations);
        return $this->save();
    }

    /**
     * Mark as featured.
     */
    public function markAsFeatured(): bool
    {
        // Unmark other featured photos in the same context
        if ($this->item_id) {
            static::where('item_id', $this->item_id)
                  ->where('id', '!=', $this->id)
                  ->update(['is_featured' => false]);
        } elseif ($this->section_id) {
            static::where('section_id', $this->section_id)
                  ->where('id', '!=', $this->id)
                  ->update(['is_featured' => false]);
        }
        
        $this->is_featured = true;
        return $this->save();
    }

    /**
     * Process photo (extract metadata, create thumbnails).
     */
    public function processPhoto(): bool
    {
        if ($this->is_processed) {
            return true;
        }

        try {
            $metadata = [];
            
            // Extract EXIF data if image
            if ($this->isImage() && function_exists('exif_read_data')) {
                $exif = @exif_read_data($this->getFullPath());
                if ($exif) {
                    $metadata['exif'] = $exif;
                    
                    // Update camera info
                    if (isset($exif['Make'])) {
                        $this->camera_make = $exif['Make'];
                    }
                    if (isset($exif['Model'])) {
                        $this->camera_model = $exif['Model'];
                    }
                    
                    // Update taken date
                    if (isset($exif['DateTime'])) {
                        $this->taken_at = \Carbon\Carbon::createFromFormat('Y:m:d H:i:s', $exif['DateTime']);
                    }
                }
            }
            
            // Get image dimensions
            if ($this->isImage()) {
                $imageInfo = getimagesize($this->getFullPath());
                if ($imageInfo) {
                    $this->width = $imageInfo[0];
                    $this->height = $imageInfo[1];
                    $metadata['dimensions'] = ['width' => $this->width, 'height' => $this->height];
                }
            }
            
            $this->processing_metadata = $metadata;
            $this->is_processed = true;
            
            return $this->save();
        } catch (\Exception $e) {
            \Log::error('Photo processing failed', [
                'photo_id' => $this->id,
                'file_path' => $this->file_path,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Generate thumbnail.
     */
    public function generateThumbnail(int $width = 300, int $height = 300): bool
    {
        if (!$this->isImage() || $this->thumbnail_path) {
            return true;
        }

        try {
            // Use GD to create thumbnail
            $sourceImage = null;
            
            switch ($this->mime_type) {
                case 'image/jpeg':
                    $sourceImage = imagecreatefromjpeg($this->getFullPath());
                    break;
                case 'image/png':
                    $sourceImage = imagecreatefrompng($this->getFullPath());
                    break;
                case 'image/gif':
                    $sourceImage = imagecreatefromgif($this->getFullPath());
                    break;
                default:
                    return false;
            }
            
            if (!$sourceImage) {
                return false;
            }
            
            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);
            
            // Calculate thumbnail dimensions maintaining aspect ratio
            $ratio = min($width / $sourceWidth, $height / $sourceHeight);
            $thumbWidth = round($sourceWidth * $ratio);
            $thumbHeight = round($sourceHeight * $ratio);
            
            // Create thumbnail
            $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
            
            // Preserve transparency
            if ($this->mime_type === 'image/png' || $this->mime_type === 'image/gif') {
                imagealphablending($thumbnail, false);
                imagesavealpha($thumbnail, true);
                $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
                imagefill($thumbnail, 0, 0, $transparent);
            }
            
            imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);
            
            // Save thumbnail
            $pathInfo = pathinfo($this->file_path);
            $thumbnailPath = $pathInfo['dirname'] . '/thumbs/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
            $fullThumbnailPath = Storage::path($thumbnailPath);
            
            // Create directory if needed
            Storage::makeDirectory(dirname($thumbnailPath));
            
            $success = false;
            switch ($this->mime_type) {
                case 'image/jpeg':
                    $success = imagejpeg($thumbnail, $fullThumbnailPath, 85);
                    break;
                case 'image/png':
                    $success = imagepng($thumbnail, $fullThumbnailPath, 8);
                    break;
                case 'image/gif':
                    $success = imagegif($thumbnail, $fullThumbnailPath);
                    break;
            }
            
            // Cleanup
            imagedestroy($sourceImage);
            imagedestroy($thumbnail);
            
            if ($success) {
                $this->thumbnail_path = $thumbnailPath;
                return $this->save();
            }
            
            return false;
        } catch (\Exception $e) {
            \Log::error('Thumbnail generation failed', [
                'photo_id' => $this->id,
                'file_path' => $this->file_path,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Delete photo and associated files.
     */
    public function deleteFiles(): bool
    {
        $success = true;
        
        // Delete main file
        if ($this->file_path && Storage::exists($this->file_path)) {
            $success = Storage::delete($this->file_path) && $success;
        }
        
        // Delete thumbnail
        if ($this->thumbnail_path && Storage::exists($this->thumbnail_path)) {
            $success = Storage::delete($this->thumbnail_path) && $success;
        }
        
        return $success;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($photo) {
            $photo->deleteFiles();
        });
    }
}
