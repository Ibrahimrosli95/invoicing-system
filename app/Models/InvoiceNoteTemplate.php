<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class InvoiceNoteTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'type',
        'content',
        'is_default',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    const TYPE_NOTES = 'notes';
    const TYPE_TERMS = 'terms';
    const TYPE_PAYMENT_INSTRUCTIONS = 'payment_instructions';

    public static function getTypes(): array
    {
        return [
            self::TYPE_NOTES => 'Notes',
            self::TYPE_TERMS => 'Terms & Conditions',
            self::TYPE_PAYMENT_INSTRUCTIONS => 'Payment Instructions',
        ];
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Scopes
    public function scopeForCompany(Builder $query): Builder
    {
        return $query->where('company_id', auth()->user()->company_id);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    // Methods
    public function setAsDefault(): void
    {
        // Remove default status from other templates of the same type
        static::where('company_id', $this->company_id)
            ->where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this template as default
        $this->update(['is_default' => true]);
    }

    public function getTypeDisplayName(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    public static function getDefaultForType(string $type, ?int $companyId = null): ?self
    {
        $companyId = $companyId ?? auth()->user()->company_id;

        return static::where('company_id', $companyId)
            ->where('type', $type)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    public static function getTemplatesForType(string $type, ?int $companyId = null): \Illuminate\Database\Eloquent\Collection
    {
        $companyId = $companyId ?? auth()->user()->company_id;

        return static::where('company_id', $companyId)
            ->where('type', $type)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();
    }
}
