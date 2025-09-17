<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\ProofAsset;

class FileProcessingService
{
    // File type constants
    const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const ALLOWED_VIDEO_TYPES = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo'];
    const ALLOWED_DOCUMENT_TYPES = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    // File size limits (in bytes)
    const MAX_IMAGE_SIZE = 10 * 1024 * 1024; // 10MB
    const MAX_VIDEO_SIZE = 100 * 1024 * 1024; // 100MB
    const MAX_DOCUMENT_SIZE = 25 * 1024 * 1024; // 25MB
    
    // Thumbnail sizes
    const THUMBNAIL_SIZES = [
        'small' => ['width' => 150, 'height' => 150],
        'medium' => ['width' => 300, 'height' => 300],
        'large' => ['width' => 600, 'height' => 600],
    ];
    
    /**
     * Validate uploaded file
     */
    public function validateFile(UploadedFile $file): array
    {
        $errors = [];
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        
        // Check if file type is allowed
        $allowedTypes = array_merge(
            self::ALLOWED_IMAGE_TYPES,
            self::ALLOWED_VIDEO_TYPES,
            self::ALLOWED_DOCUMENT_TYPES
        );
        
        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = "File type '{$mimeType}' is not allowed.";
        }
        
        // Check file size limits
        if (in_array($mimeType, self::ALLOWED_IMAGE_TYPES) && $fileSize > self::MAX_IMAGE_SIZE) {
            $errors[] = "Image file size exceeds " . $this->formatFileSize(self::MAX_IMAGE_SIZE) . " limit.";
        } elseif (in_array($mimeType, self::ALLOWED_VIDEO_TYPES) && $fileSize > self::MAX_VIDEO_SIZE) {
            $errors[] = "Video file size exceeds " . $this->formatFileSize(self::MAX_VIDEO_SIZE) . " limit.";
        } elseif (in_array($mimeType, self::ALLOWED_DOCUMENT_TYPES) && $fileSize > self::MAX_DOCUMENT_SIZE) {
            $errors[] = "Document file size exceeds " . $this->formatFileSize(self::MAX_DOCUMENT_SIZE) . " limit.";
        }
        
        // Basic security check - scan for malicious patterns
        $filename = $file->getClientOriginalName();
        if ($this->containsMaliciousPatterns($filename)) {
            $errors[] = "Filename contains potentially dangerous characters.";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'type' => $this->determineAssetType($mimeType)
        ];
    }
    
    /**
     * Process uploaded file and create optimized versions
     */
    public function processFile(UploadedFile $file, ProofAsset $asset): array
    {
        $results = [
            'thumbnails_generated' => [],
            'dimensions_extracted' => false,
            'video_thumbnail' => false,
            'processing_errors' => []
        ];
        
        try {
            $mimeType = $file->getMimeType();
            
            if (in_array($mimeType, self::ALLOWED_IMAGE_TYPES)) {
                $results = array_merge($results, $this->processImage($asset));
            } elseif (in_array($mimeType, self::ALLOWED_VIDEO_TYPES)) {
                $results = array_merge($results, $this->processVideo($asset));
            } elseif (in_array($mimeType, self::ALLOWED_DOCUMENT_TYPES)) {
                $results = array_merge($results, $this->processDocument($asset));
            }
            
        } catch (\Exception $e) {
            Log::error("File processing failed for asset {$asset->id}: " . $e->getMessage());
            $results['processing_errors'][] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Process image file - extract dimensions and generate thumbnails
     */
    protected function processImage(ProofAsset $asset): array
    {
        $results = [
            'thumbnails_generated' => [],
            'dimensions_extracted' => false,
            'processing_errors' => []
        ];
        
        try {
            $fullPath = Storage::disk('public')->path($asset->file_path);
            
            if (!file_exists($fullPath)) {
                throw new \Exception("Original file not found: {$fullPath}");
            }
            
            // Extract dimensions using getimagesize (built-in PHP function)
            $imageInfo = getimagesize($fullPath);
            
            if (!$imageInfo) {
                throw new \Exception("Cannot extract image dimensions");
            }
            
            $width = $imageInfo[0];
            $height = $imageInfo[1];
            
            $asset->update([
                'width' => $width,
                'height' => $height,
                'processing_status' => 'processing'
            ]);
            
            $results['dimensions_extracted'] = true;
            
            // Generate thumbnails for each size using GD
            foreach (self::THUMBNAIL_SIZES as $sizeName => $dimensions) {
                try {
                    $thumbnailPath = $this->generateImageThumbnail($asset, $fullPath, $sizeName, $dimensions);
                    $results['thumbnails_generated'][$sizeName] = $thumbnailPath;
                } catch (\Exception $e) {
                    Log::warning("Failed to generate {$sizeName} thumbnail for asset {$asset->id}: " . $e->getMessage());
                    $results['processing_errors'][] = "Failed to generate {$sizeName} thumbnail";
                }
            }
            
            // Update processing status
            $asset->update([
                'thumbnail_path' => $results['thumbnails_generated']['medium'] ?? null,
                'processing_status' => 'completed',
                'metadata' => array_merge($asset->metadata ?? [], [
                    'thumbnails' => $results['thumbnails_generated'],
                    'original_dimensions' => ['width' => $width, 'height' => $height]
                ])
            ]);
            
        } catch (\Exception $e) {
            Log::error("Image processing failed for asset {$asset->id}: " . $e->getMessage());
            $results['processing_errors'][] = $e->getMessage();
            
            $asset->update(['processing_status' => 'failed']);
        }
        
        return $results;
    }
    
    /**
     * Generate image thumbnail using GD
     */
    protected function generateImageThumbnail(ProofAsset $asset, string $originalPath, string $sizeName, array $dimensions): string
    {
        // Create thumbnail path
        $pathInfo = pathinfo($asset->file_path);
        $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . "_{$sizeName}." . $pathInfo['extension'];
        $fullThumbnailPath = Storage::disk('public')->path($thumbnailPath);
        
        // Ensure directory exists
        $directory = dirname($fullThumbnailPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Get image info
        $imageInfo = getimagesize($originalPath);
        if (!$imageInfo) {
            throw new \Exception("Cannot get image info for thumbnail generation");
        }
        
        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        $imageType = $imageInfo[2];
        
        // Create image resource from original
        $originalImage = match($imageType) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($originalPath),
            IMAGETYPE_PNG => imagecreatefrompng($originalPath),
            IMAGETYPE_GIF => imagecreatefromgif($originalPath),
            IMAGETYPE_WEBP => imagecreatefromwebp($originalPath),
            default => throw new \Exception("Unsupported image type for thumbnail generation")
        };
        
        if (!$originalImage) {
            throw new \Exception("Cannot create image resource");
        }
        
        // Calculate thumbnail dimensions maintaining aspect ratio
        $thumbWidth = $dimensions['width'];
        $thumbHeight = $dimensions['height'];
        
        $originalAspect = $originalWidth / $originalHeight;
        $thumbAspect = $thumbWidth / $thumbHeight;
        
        if ($originalAspect > $thumbAspect) {
            // Original is wider, fit to height
            $newHeight = $thumbHeight;
            $newWidth = intval($thumbHeight * $originalAspect);
            $cropX = intval(($newWidth - $thumbWidth) / 2);
            $cropY = 0;
        } else {
            // Original is taller, fit to width
            $newWidth = $thumbWidth;
            $newHeight = intval($thumbWidth / $originalAspect);
            $cropX = 0;
            $cropY = intval(($newHeight - $thumbHeight) / 2);
        }
        
        // Create thumbnail canvas
        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // Preserve transparency for PNG/GIF
        if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefill($thumbnail, 0, 0, $transparent);
        }
        
        // Create intermediate resized image
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }
        
        // Resize original to intermediate size
        imagecopyresampled($resized, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Copy cropped portion to final thumbnail
        imagecopy($thumbnail, $resized, 0, 0, $cropX, $cropY, $thumbWidth, $thumbHeight);
        
        // Save thumbnail
        $success = match($imageType) {
            IMAGETYPE_JPEG => imagejpeg($thumbnail, $fullThumbnailPath, 85),
            IMAGETYPE_PNG => imagepng($thumbnail, $fullThumbnailPath, 8),
            IMAGETYPE_GIF => imagegif($thumbnail, $fullThumbnailPath),
            IMAGETYPE_WEBP => imagewebp($thumbnail, $fullThumbnailPath, 85),
            default => false
        };
        
        // Clean up memory
        imagedestroy($originalImage);
        imagedestroy($resized);
        imagedestroy($thumbnail);
        
        if (!$success) {
            throw new \Exception("Failed to save thumbnail");
        }
        
        return $thumbnailPath;
    }
    
    /**
     * Process video file - generate thumbnail from first frame
     */
    protected function processVideo(ProofAsset $asset): array
    {
        $results = [
            'video_thumbnail' => false,
            'dimensions_extracted' => false,
            'processing_errors' => []
        ];
        
        try {
            // For video processing, we would typically use FFmpeg
            // For now, we'll create a placeholder thumbnail and extract basic info
            
            $fullPath = Storage::disk('public')->path($asset->file_path);
            
            if (!file_exists($fullPath)) {
                throw new \Exception("Video file not found: {$fullPath}");
            }
            
            // Get video information (this would use FFprobe in a real implementation)
            $videoInfo = $this->getVideoInfo($fullPath);
            
            if ($videoInfo) {
                $asset->update([
                    'width' => $videoInfo['width'] ?? null,
                    'height' => $videoInfo['height'] ?? null,
                    'duration' => $videoInfo['duration'] ?? null,
                    'metadata' => array_merge($asset->metadata ?? [], [
                        'video_info' => $videoInfo
                    ])
                ]);
                
                $results['dimensions_extracted'] = true;
            }
            
            // Generate video thumbnail (placeholder for now)
            $thumbnailPath = $this->generateVideoThumbnail($asset);
            
            if ($thumbnailPath) {
                $asset->update([
                    'thumbnail_path' => $thumbnailPath,
                    'processing_status' => 'completed'
                ]);
                
                $results['video_thumbnail'] = true;
            }
            
        } catch (\Exception $e) {
            Log::error("Video processing failed for asset {$asset->id}: " . $e->getMessage());
            $results['processing_errors'][] = $e->getMessage();
            
            $asset->update(['processing_status' => 'failed']);
        }
        
        return $results;
    }
    
    /**
     * Generate video thumbnail (placeholder implementation)
     */
    protected function generateVideoThumbnail(ProofAsset $asset): ?string
    {
        try {
            // In a real implementation, this would use FFmpeg to extract a frame
            // For now, we'll create a placeholder image
            
            $pathInfo = pathinfo($asset->file_path);
            $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumbnail.jpg';
            $fullThumbnailPath = Storage::disk('public')->path($thumbnailPath);
            
            // Ensure directory exists
            $directory = dirname($fullThumbnailPath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Create a simple placeholder image using GD
            $image = imagecreatetruecolor(300, 200);
            
            // Fill with light gray background
            $bgColor = imagecolorallocate($image, 204, 204, 204);
            imagefill($image, 0, 0, $bgColor);
            
            // Add play icon (simple triangle)
            $iconColor = imagecolorallocate($image, 102, 102, 102);
            $playIcon = [
                120, 80,  // Point 1
                180, 100, // Point 2 (center right)
                120, 120  // Point 3
            ];
            imagefilledpolygon($image, $playIcon, 3, $iconColor);
            
            // Save the image
            imagejpeg($image, $fullThumbnailPath, 85);
            imagedestroy($image);
            
            return $thumbnailPath;
            
        } catch (\Exception $e) {
            Log::error("Video thumbnail generation failed for asset {$asset->id}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Process document file
     */
    protected function processDocument(ProofAsset $asset): array
    {
        $results = [
            'processing_errors' => []
        ];
        
        try {
            $fullPath = Storage::disk('public')->path($asset->file_path);
            
            if (!file_exists($fullPath)) {
                throw new \Exception("Document file not found: {$fullPath}");
            }
            
            // Extract document metadata
            $documentInfo = $this->getDocumentInfo($fullPath, $asset->mime_type);
            
            $asset->update([
                'metadata' => array_merge($asset->metadata ?? [], [
                    'document_info' => $documentInfo
                ]),
                'processing_status' => 'completed'
            ]);
            
        } catch (\Exception $e) {
            Log::error("Document processing failed for asset {$asset->id}: " . $e->getMessage());
            $results['processing_errors'][] = $e->getMessage();
            
            $asset->update(['processing_status' => 'failed']);
        }
        
        return $results;
    }
    
    /**
     * Get video information (placeholder - would use FFprobe)
     */
    protected function getVideoInfo(string $filePath): ?array
    {
        // Placeholder implementation
        // In a real application, this would use FFprobe to get video metadata
        
        return [
            'width' => 1920,
            'height' => 1080,
            'duration' => 30, // seconds
            'format' => 'mp4',
            'bitrate' => 5000000, // bits per second
        ];
    }
    
    /**
     * Get document information
     */
    protected function getDocumentInfo(string $filePath, string $mimeType): array
    {
        $info = [
            'mime_type' => $mimeType,
            'file_size' => filesize($filePath),
        ];
        
        // Extract additional info based on document type
        switch ($mimeType) {
            case 'application/pdf':
                $info['type'] = 'PDF Document';
                // Would use a PDF library to get page count, etc.
                break;
                
            case 'application/msword':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                $info['type'] = 'Word Document';
                break;
                
            default:
                $info['type'] = 'Document';
        }
        
        return $info;
    }
    
    /**
     * Determine asset type from MIME type
     */
    protected function determineAssetType(string $mimeType): string
    {
        if (in_array($mimeType, self::ALLOWED_IMAGE_TYPES)) {
            return 'image';
        } elseif (in_array($mimeType, self::ALLOWED_VIDEO_TYPES)) {
            return 'video';
        } elseif (in_array($mimeType, self::ALLOWED_DOCUMENT_TYPES)) {
            return 'document';
        }
        
        return 'other';
    }
    
    /**
     * Check for malicious patterns in filename
     */
    protected function containsMaliciousPatterns(string $filename): bool
    {
        $dangerousPatterns = [
            '/\.php$/i',
            '/\.exe$/i',
            '/\.bat$/i',
            '/\.sh$/i',
            '/\.com$/i',
            '/\.scr$/i',
            '/\.vbs$/i',
            '/\.js$/i',
            '/\.jar$/i',
            '/\.\./i', // Directory traversal
            '/[<>:"|\?*]/', // Invalid filename characters
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Format file size for human reading
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.1f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
    
    /**
     * Clean up temporary files and failed uploads
     */
    public function cleanup(ProofAsset $asset): bool
    {
        try {
            // Delete original file
            if ($asset->file_path && Storage::disk('public')->exists($asset->file_path)) {
                Storage::disk('public')->delete($asset->file_path);
            }
            
            // Delete thumbnail
            if ($asset->thumbnail_path && Storage::disk('public')->exists($asset->thumbnail_path)) {
                Storage::disk('public')->delete($asset->thumbnail_path);
            }
            
            // Delete additional thumbnails from metadata
            if ($asset->metadata && isset($asset->metadata['thumbnails'])) {
                foreach ($asset->metadata['thumbnails'] as $thumbnailPath) {
                    if (Storage::disk('public')->exists($thumbnailPath)) {
                        Storage::disk('public')->delete($thumbnailPath);
                    }
                }
            }
            
            return true;
            
        } catch (\Exception $e) {
            Log::error("Cleanup failed for asset {$asset->id}: " . $e->getMessage());
            return false;
        }
    }
}