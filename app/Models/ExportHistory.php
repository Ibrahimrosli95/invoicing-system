<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ExportHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'export_type',
        'data_type',
        'format',
        'configuration',
        'filename',
        'file_path',
        'file_size',
        'download_url',
        'status',
        'total_records',
        'processed_records',
        'progress_percentage',
        'started_at',
        'completed_at',
        'processing_time',
        'error_message',
        'error_details',
        'retry_count',
        'expires_at',
        'is_downloaded',
        'downloaded_at',
    ];

    protected $casts = [
        'configuration' => 'array',
        'error_details' => 'array',
        'is_downloaded' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'downloaded_at' => 'datetime',
        'progress_percentage' => 'decimal:2',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopeForUser($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeReadyForCleanup($query)
    {
        return $query->where(function($q) {
            $q->expired()
              ->orWhere(function($q) {
                  $q->where('is_downloaded', true)
                    ->where('downloaded_at', '<', now()->subDays(7));
              });
        });
    }

    // Business Logic Methods
    public function markAsStarted()
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'started_at' => now(),
        ]);
    }

    public function updateProgress($processedRecords, $totalRecords = null)
    {
        $total = $totalRecords ?? $this->total_records;
        $percentage = $total > 0 ? ($processedRecords / $total) * 100 : 0;

        $this->update([
            'processed_records' => $processedRecords,
            'total_records' => $total,
            'progress_percentage' => min(100, $percentage),
        ]);
    }

    public function markAsCompleted($filePath, $fileSize = null)
    {
        $processingTime = $this->started_at ? now()->diffInSeconds($this->started_at) : null;
        
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'file_path' => $filePath,
            'file_size' => $fileSize ?? ($filePath ? Storage::size($filePath) : null),
            'completed_at' => now(),
            'processing_time' => $processingTime,
            'progress_percentage' => 100,
            'expires_at' => now()->addDays(7), // Files expire after 7 days
        ]);
    }

    public function markAsFailed($errorMessage, $errorDetails = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
            'error_details' => $errorDetails,
            'completed_at' => now(),
        ]);
    }

    public function markAsDownloaded()
    {
        $this->update([
            'is_downloaded' => true,
            'downloaded_at' => now(),
        ]);
    }

    public function retry()
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'retry_count' => $this->retry_count + 1,
            'error_message' => null,
            'error_details' => null,
            'started_at' => null,
            'completed_at' => null,
            'processing_time' => null,
        ]);
    }

    // Accessors & Mutators
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_PROCESSING => 'blue',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_FAILED => 'red',
            default => 'gray',
        };
    }

    public function getStatusIconAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'clock',
            self::STATUS_PROCESSING => 'cog',
            self::STATUS_COMPLETED => 'check-circle',
            self::STATUS_FAILED => 'x-circle',
            default => 'question-mark-circle',
        };
    }

    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    public function getFormattedProcessingTimeAttribute()
    {
        if (!$this->processing_time) {
            return 'N/A';
        }

        if ($this->processing_time < 60) {
            return $this->processing_time . ' seconds';
        }

        $minutes = floor($this->processing_time / 60);
        $seconds = $this->processing_time % 60;

        return $minutes . 'm ' . $seconds . 's';
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getCanDownloadAttribute()
    {
        return $this->status === self::STATUS_COMPLETED && 
               !$this->is_expired && 
               $this->file_path && 
               Storage::exists($this->file_path);
    }

    public function getCanRetryAttribute()
    {
        return $this->status === self::STATUS_FAILED && $this->retry_count < 3;
    }

    public function getExportTypeDisplayAttribute()
    {
        return match($this->export_type) {
            'report' => 'Report Export',
            'bulk_data' => 'Bulk Data Export',
            'scheduled' => 'Scheduled Report',
            default => ucwords(str_replace('_', ' ', $this->export_type)),
        };
    }

    public function getDataTypeDisplayAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->data_type));
    }

    public function getFormatDisplayAttribute()
    {
        return strtoupper($this->format);
    }

    // File management
    public function generateDownloadUrl()
    {
        if (!$this->can_download) {
            return null;
        }

        return route('exports.download', $this->id);
    }

    public function deleteFile()
    {
        if ($this->file_path && Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
    }

    // Boot method for model events
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($exportHistory) {
            $exportHistory->deleteFile();
        });
    }
}