<?php

namespace App\Services;

use App\Models\Proof;
use App\Models\ProofAsset;
use App\Models\ProofView;
use App\Models\Company;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProofRetentionService
{
    /**
     * Default retention periods in months
     */
    const DEFAULT_RETENTION_PERIODS = [
        'draft' => 6,           // 6 months for drafts
        'active' => 24,         // 2 years for active proofs
        'archived' => 12,       // 1 year for archived proofs
        'consent_revoked' => 3, // 3 months after consent revoked
        'view_logs' => 12,      // 1 year for view tracking logs
        'assets' => 24,         // 2 years for asset files
    ];

    /**
     * Get retention policy for a company
     */
    public function getRetentionPolicy(int $companyId): array
    {
        $company = Company::find($companyId);
        $settings = $company->settings ?? [];
        
        return array_merge(
            self::DEFAULT_RETENTION_PERIODS,
            $settings['proof_retention'] ?? []
        );
    }

    /**
     * Update retention policy for a company
     */
    public function updateRetentionPolicy(int $companyId, array $retentionPeriods): bool
    {
        try {
            $company = Company::find($companyId);
            $settings = $company->settings ?? [];
            
            $settings['proof_retention'] = array_merge(
                self::DEFAULT_RETENTION_PERIODS,
                $retentionPeriods
            );
            
            $company->update(['settings' => $settings]);
            
            Log::info('Retention policy updated', [
                'company_id' => $companyId,
                'new_policy' => $settings['proof_retention'],
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update retention policy', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Find proofs eligible for cleanup
     */
    public function findEligibleForCleanup(int $companyId = null): array
    {
        $query = Proof::query();
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        $proofs = $query->get();
        $eligible = [];
        
        foreach ($proofs as $proof) {
            $retentionPolicy = $this->getRetentionPolicy($proof->company_id);
            $eligibility = $this->checkProofEligibility($proof, $retentionPolicy);
            
            if ($eligibility['eligible']) {
                $eligible[] = [
                    'proof' => $proof,
                    'reason' => $eligibility['reason'],
                    'expires_at' => $eligibility['expires_at'],
                    'days_overdue' => $eligibility['days_overdue'],
                ];
            }
        }
        
        return $eligible;
    }

    /**
     * Check if a proof is eligible for cleanup
     */
    protected function checkProofEligibility(Proof $proof, array $retentionPolicy): array
    {
        $now = now();
        $createdAt = $proof->created_at;
        
        // Check based on proof status
        $retentionMonths = match ($proof->status) {
            'draft' => $retentionPolicy['draft'],
            'active' => $retentionPolicy['active'],
            'archived' => $retentionPolicy['archived'],
            default => $retentionPolicy['active'],
        };
        
        $expiresAt = $createdAt->addMonths($retentionMonths);
        $eligible = $now->isAfter($expiresAt);
        $reason = "Status: {$proof->status}, retention: {$retentionMonths} months";
        
        // Special check for consent revoked proofs
        $metadata = $proof->metadata ?? [];
        $consentData = $metadata['consent'] ?? [];
        
        if (($consentData['status'] ?? null) === 'revoked') {
            $revokedAt = Carbon::parse($consentData['revoked_at']);
            $consentExpiresAt = $revokedAt->addMonths($retentionPolicy['consent_revoked']);
            
            if ($now->isAfter($consentExpiresAt)) {
                $eligible = true;
                $reason = "Consent revoked, retention: {$retentionPolicy['consent_revoked']} months";
                $expiresAt = $consentExpiresAt;
            }
        }
        
        return [
            'eligible' => $eligible,
            'reason' => $reason,
            'expires_at' => $expiresAt,
            'days_overdue' => $eligible ? $now->diffInDays($expiresAt) : 0,
        ];
    }

    /**
     * Perform cleanup of eligible proofs
     */
    public function performCleanup(int $companyId = null, bool $dryRun = false): array
    {
        $eligible = $this->findEligibleForCleanup($companyId);
        $results = [
            'processed' => 0,
            'deleted_proofs' => 0,
            'deleted_assets' => 0,
            'deleted_views' => 0,
            'freed_storage' => 0,
            'errors' => [],
        ];
        
        foreach ($eligible as $item) {
            $proof = $item['proof'];
            $results['processed']++;
            
            try {
                if (!$dryRun) {
                    $cleanupResult = $this->cleanupProof($proof);
                    $results['deleted_proofs']++;
                    $results['deleted_assets'] += $cleanupResult['deleted_assets'];
                    $results['deleted_views'] += $cleanupResult['deleted_views'];
                    $results['freed_storage'] += $cleanupResult['freed_storage'];
                }
                
                Log::info('Proof cleanup performed', [
                    'proof_id' => $proof->id,
                    'company_id' => $proof->company_id,
                    'reason' => $item['reason'],
                    'dry_run' => $dryRun,
                ]);
                
            } catch (\Exception $e) {
                $error = "Failed to cleanup proof {$proof->id}: " . $e->getMessage();
                $results['errors'][] = $error;
                
                Log::error('Proof cleanup failed', [
                    'proof_id' => $proof->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return $results;
    }

    /**
     * Clean up a single proof and its related data
     */
    protected function cleanupProof(Proof $proof): array
    {
        $results = [
            'deleted_assets' => 0,
            'deleted_views' => 0,
            'freed_storage' => 0,
        ];
        
        // Clean up assets and files
        $assets = $proof->assets;
        foreach ($assets as $asset) {
            $results['freed_storage'] += $this->getAssetSize($asset);
            $this->deleteAssetFiles($asset);
            $asset->delete();
            $results['deleted_assets']++;
        }
        
        // Clean up view logs
        $viewCount = $proof->views()->count();
        $proof->views()->delete();
        $results['deleted_views'] = $viewCount;
        
        // Delete the proof itself
        $proof->delete();
        
        return $results;
    }

    /**
     * Clean up view logs based on retention policy
     */
    public function cleanupViewLogs(int $companyId = null): array
    {
        $retentionPolicy = $companyId 
            ? $this->getRetentionPolicy($companyId)
            : self::DEFAULT_RETENTION_PERIODS;
        
        $cutoffDate = now()->subMonths($retentionPolicy['view_logs']);
        
        $query = ProofView::where('created_at', '<', $cutoffDate);
        
        if ($companyId) {
            $query->whereHas('proof', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }
        
        $count = $query->count();
        $query->delete();
        
        Log::info('View logs cleaned up', [
            'company_id' => $companyId,
            'deleted_count' => $count,
            'cutoff_date' => $cutoffDate,
        ]);
        
        return [
            'deleted_views' => $count,
            'cutoff_date' => $cutoffDate,
        ];
    }

    /**
     * Archive old proofs instead of deleting
     */
    public function archiveOldProofs(int $companyId = null, int $archiveAfterMonths = 18): array
    {
        $cutoffDate = now()->subMonths($archiveAfterMonths);
        
        $query = Proof::where('status', 'active')
                     ->where('created_at', '<', $cutoffDate);
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        $proofs = $query->get();
        $archivedCount = 0;
        
        foreach ($proofs as $proof) {
            // Only archive if no recent activity
            $lastView = $proof->views()->latest()->first();
            $lastViewAt = $lastView ? $lastView->created_at : $proof->created_at;
            
            if ($lastViewAt->isBefore(now()->subMonths(6))) {
                $proof->update(['status' => 'archived']);
                $archivedCount++;
                
                Log::info('Proof archived due to inactivity', [
                    'proof_id' => $proof->id,
                    'last_activity' => $lastViewAt,
                ]);
            }
        }
        
        return [
            'archived_count' => $archivedCount,
            'cutoff_date' => $cutoffDate,
        ];
    }

    /**
     * Get storage usage statistics
     */
    public function getStorageUsage(int $companyId = null): array
    {
        $query = Proof::query();
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        $proofs = $query->with('assets')->get();
        
        $stats = [
            'total_proofs' => 0,
            'total_assets' => 0,
            'total_storage_bytes' => 0,
            'storage_by_type' => [],
            'storage_by_status' => [],
            'oldest_proof' => null,
            'newest_proof' => null,
        ];
        
        foreach ($proofs as $proof) {
            $stats['total_proofs']++;
            
            // Track by status
            if (!isset($stats['storage_by_status'][$proof->status])) {
                $stats['storage_by_status'][$proof->status] = [
                    'count' => 0,
                    'storage' => 0,
                ];
            }
            $stats['storage_by_status'][$proof->status]['count']++;
            
            foreach ($proof->assets as $asset) {
                $stats['total_assets']++;
                $assetSize = $this->getAssetSize($asset);
                $stats['total_storage_bytes'] += $assetSize;
                $stats['storage_by_status'][$proof->status]['storage'] += $assetSize;
                
                // Track by type
                $type = $asset->type ?? 'unknown';
                if (!isset($stats['storage_by_type'][$type])) {
                    $stats['storage_by_type'][$type] = [
                        'count' => 0,
                        'storage' => 0,
                    ];
                }
                $stats['storage_by_type'][$type]['count']++;
                $stats['storage_by_type'][$type]['storage'] += $assetSize;
            }
            
            // Track oldest and newest
            if (!$stats['oldest_proof'] || $proof->created_at < $stats['oldest_proof']) {
                $stats['oldest_proof'] = $proof->created_at;
            }
            if (!$stats['newest_proof'] || $proof->created_at > $stats['newest_proof']) {
                $stats['newest_proof'] = $proof->created_at;
            }
        }
        
        return $stats;
    }

    /**
     * Get asset file size
     */
    protected function getAssetSize(ProofAsset $asset): int
    {
        if (!$asset->file_path) {
            return 0;
        }
        
        try {
            return Storage::size($asset->file_path);
        } catch (\Exception $e) {
            Log::warning('Could not get asset size', [
                'asset_id' => $asset->id,
                'file_path' => $asset->file_path,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Delete asset files from storage
     */
    protected function deleteAssetFiles(ProofAsset $asset): void
    {
        $filesToDelete = array_filter([
            $asset->file_path,
            $asset->thumbnail_path,
        ]);
        
        foreach ($filesToDelete as $filePath) {
            try {
                if (Storage::exists($filePath)) {
                    Storage::delete($filePath);
                    Log::info('Asset file deleted', [
                        'asset_id' => $asset->id,
                        'file_path' => $filePath,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to delete asset file', [
                    'asset_id' => $asset->id,
                    'file_path' => $filePath,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Export proof data before deletion (GDPR compliance)
     */
    public function exportProofData(Proof $proof): array
    {
        return [
            'proof_data' => [
                'id' => $proof->id,
                'uuid' => $proof->uuid,
                'type' => $proof->type,
                'title' => $proof->title,
                'description' => $proof->description,
                'status' => $proof->status,
                'created_at' => $proof->created_at,
                'updated_at' => $proof->updated_at,
                'metadata' => $proof->metadata,
            ],
            'assets' => $proof->assets->map(function ($asset) {
                return [
                    'id' => $asset->id,
                    'type' => $asset->type,
                    'title' => $asset->title,
                    'file_name' => $asset->file_name,
                    'file_size' => $asset->file_size,
                    'created_at' => $asset->created_at,
                ];
            })->toArray(),
            'view_history' => $proof->views->map(function ($view) {
                return [
                    'viewed_at' => $view->created_at,
                    'viewer_type' => $view->viewer_type,
                    'source_type' => $view->source_type,
                ];
            })->toArray(),
        ];
    }

    /**
     * Schedule automatic cleanup
     */
    public function scheduleCleanup(int $companyId, array $schedule): bool
    {
        try {
            $company = Company::find($companyId);
            $settings = $company->settings ?? [];
            
            $settings['proof_cleanup_schedule'] = [
                'enabled' => $schedule['enabled'] ?? false,
                'frequency' => $schedule['frequency'] ?? 'monthly',
                'notify_before_days' => $schedule['notify_before_days'] ?? 7,
                'last_run' => null,
                'next_run' => $this->calculateNextRun($schedule['frequency'] ?? 'monthly'),
            ];
            
            $company->update(['settings' => $settings]);
            
            Log::info('Cleanup schedule configured', [
                'company_id' => $companyId,
                'schedule' => $settings['proof_cleanup_schedule'],
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to schedule cleanup', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Calculate next cleanup run date
     */
    protected function calculateNextRun(string $frequency): string
    {
        return match ($frequency) {
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'yearly' => now()->addYear(),
            default => now()->addMonth(),
        };
    }
}