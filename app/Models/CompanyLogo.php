<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CompanyLogo extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'file_path',
        'is_default',
        'notes',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the company that owns this logo.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the full URL for this logo.
     */
    public function getUrlAttribute(): string
    {
        return route('logo-bank.serve', $this->id);
    }

    /**
     * Get the full file path.
     */
    public function getFullPathAttribute(): string
    {
        return Storage::disk('public')->path($this->file_path);
    }

    /**
     * Set this logo as default and unset others.
     */
    public function setAsDefault(): void
    {
        $this->company->logos()->where('id', '!=', $this->id)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }

    /**
     * Delete logo file from storage.
     */
    public function deleteFile(): void
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }
    }

    /**
     * Boot method to handle model events.
     */
    protected static function booted(): void
    {
        // Delete file when logo is deleted
        static::deleting(function (CompanyLogo $logo) {
            $logo->deleteFile();
        });
    }
}
