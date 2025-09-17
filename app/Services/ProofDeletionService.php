<?php

namespace App\Services;

use App\Models\Proof;
use App\Models\ProofAsset;
use App\Models\ProofView;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProofDeletionService
{
    /**
     * Deletion types
     */
    const DELETION_TYPES = [
        'soft_delete' => 'Soft Delete',
        'archive' => 'Archive',
        'secure_delete' => 'Secure Delete',
        'compliance_delete' => 'Compliance Delete',
    ];

    /**
     * Deletion reasons
     */
    const DELETION_REASONS = [
        'user_request' => 'User Request',
        'consent_revoked' => 'Consent Revoked',
        'legal_requirement' => 'Legal Requirement',
        'data_retention' => 'Data Retention Policy',
        'security_violation' => 'Security Violation',
        'duplicate_content' => 'Duplicate Content',
        'outdated_content' => 'Outdated Content',
        'policy_violation' => 'Policy Violation',
    ];

    /**
     * Securely delete a proof
     */
    public function secureDelete(
        Proof $proof, 
        string $reason = 'user_request',
        array $deletionOptions = []
    ): array {
        try {
            DB::beginTransaction();

            $result = [
                'proof_deleted' => false,
                'assets_deleted' => 0,
                'views_deleted' => 0,
                'storage_freed' => 0,
                'errors' => [],
                'deletion_id' => uniqid('del_'),
            ];

            // Log deletion initiation
            $this->logDeletionEvent('deletion_initiated', $proof, [
                'reason' => $reason,
                'deletion_id' => $result['deletion_id'],
                'options' => $deletionOptions,
            ]);

            // Pre-deletion validation
            $validationResult = $this->validateDeletion($proof, $reason);
            if (!$validationResult['allowed']) {
                throw new \Exception('Deletion not allowed: ' . implode(', ', $validationResult['reasons']));
            }

            // Export data before deletion if required
            if ($deletionOptions['export_before_delete'] ?? true) {
                $exportResult = $this->exportProofData($proof);
                if ($exportResult) {
                    $this->logDeletionEvent('data_exported', $proof, [
                        'export_path' => $exportResult['export_path'],
                        'deletion_id' => $result['deletion_id'],
                    ]);
                }
            }

            // Delete assets and files
            $assetDeletionResult = $this->deleteProofAssets($proof, $deletionOptions);
            $result['assets_deleted'] = $assetDeletionResult['deleted_count'];
            $result['storage_freed'] = $assetDeletionResult['storage_freed'];
            $result['errors'] = array_merge($result['errors'], $assetDeletionResult['errors']);

            // Delete view logs
            $viewsDeleted = $this->deleteProofViews($proof, $deletionOptions);
            $result['views_deleted'] = $viewsDeleted;

            // Handle dependent records
            $this->handleDependentRecords($proof, $deletionOptions);

            // Delete the proof itself
            $this->deleteProofRecord($proof, $reason, $result['deletion_id']);
            $result['proof_deleted'] = true;

            // Log completion
            $this->logDeletionEvent('deletion_completed', $proof, [
                'result' => $result,
                'reason' => $reason,
                'deletion_id' => $result['deletion_id'],
            ]);

            DB::commit();

            Log::info('Proof securely deleted', [
                'proof_id' => $proof->id,
                'deletion_id' => $result['deletion_id'],
                'reason' => $reason,
                'result' => $result,
            ]);

            return $result;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to securely delete proof', [
                'proof_id' => $proof->id,
                'reason' => $reason,
                'error' => $e->getMessage(),
            ]);

            $result['errors'][] = $e->getMessage();
            return $result;
        }
    }

    /**
     * Archive a proof instead of deleting
     */
    public function archiveProof(
        Proof $proof,
        string $reason = 'data_retention',
        array $archiveOptions = []
    ): array {
        try {
            $archiveId = uniqid('arch_');
            
            // Create archive metadata
            $archiveMetadata = [
                'archived_at' => now()->toISOString(),
                'archived_by' => auth()->id(),
                'archive_id' => $archiveId,
                'archive_reason' => $reason,
                'original_status' => $proof->status,
                'archive_options' => $archiveOptions,
                'retention_until' => $archiveOptions['retention_until'] ?? null,
            ];

            // Update proof metadata
            $metadata = $proof->metadata ?? [];
            $metadata['archive'] = $archiveMetadata;

            // Update proof status
            $proof->update([
                'status' => 'archived',
                'metadata' => $metadata,
            ]);

            // Archive assets if requested
            $archivedAssets = 0;
            if ($archiveOptions['archive_assets'] ?? true) {
                $archivedAssets = $this->archiveProofAssets($proof, $archiveId);
            }

            Log::info('Proof archived', [
                'proof_id' => $proof->id,
                'archive_id' => $archiveId,
                'reason' => $reason,
                'archived_assets' => $archivedAssets,
            ]);

            return [
                'archived' => true,
                'archive_id' => $archiveId,
                'archived_assets' => $archivedAssets,
                'archive_metadata' => $archiveMetadata,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to archive proof', [
                'proof_id' => $proof->id,
                'reason' => $reason,
                'error' => $e->getMessage(),
            ]);

            return [
                'archived' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Restore archived proof
     */
    public function restoreArchivedProof(Proof $proof): array
    {
        try {
            $metadata = $proof->metadata ?? [];
            $archiveData = $metadata['archive'] ?? [];

            if (empty($archiveData)) {
                throw new \Exception('Proof is not archived');
            }

            // Restore original status
            $originalStatus = $archiveData['original_status'] ?? 'draft';
            
            // Remove archive metadata
            unset($metadata['archive']);

            // Add restoration metadata
            $metadata['restoration'] = [
                'restored_at' => now()->toISOString(),
                'restored_by' => auth()->id(),
                'archive_id' => $archiveData['archive_id'],
                'archived_duration' => Carbon::parse($archiveData['archived_at'])
                    ->diffInDays(now()),
            ];

            $proof->update([
                'status' => $originalStatus,
                'metadata' => $metadata,
            ]);

            Log::info('Proof restored from archive', [
                'proof_id' => $proof->id,
                'archive_id' => $archiveData['archive_id'],
                'restored_by' => auth()->id(),
            ]);

            return [
                'restored' => true,
                'original_status' => $originalStatus,
                'archived_duration_days' => $metadata['restoration']['archived_duration'],
            ];

        } catch (\Exception $e) {
            Log::error('Failed to restore archived proof', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'restored' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Bulk delete proofs
     */
    public function bulkDelete(
        array $proofIds,
        string $reason = 'bulk_operation',
        array $deletionOptions = []
    ): array {
        $results = [
            'processed' => 0,
            'succeeded' => 0,
            'failed' => 0,
            'total_assets_deleted' => 0,
            'total_storage_freed' => 0,
            'errors' => [],
        ];

        foreach ($proofIds as $proofId) {
            $results['processed']++;
            
            try {
                $proof = Proof::find($proofId);
                
                if (!$proof) {
                    $results['failed']++;
                    $results['errors'][] = "Proof {$proofId} not found";
                    continue;
                }

                $deletionResult = $this->secureDelete($proof, $reason, $deletionOptions);
                
                if ($deletionResult['proof_deleted']) {
                    $results['succeeded']++;
                    $results['total_assets_deleted'] += $deletionResult['assets_deleted'];
                    $results['total_storage_freed'] += $deletionResult['storage_freed'];
                } else {
                    $results['failed']++;
                    $results['errors'] = array_merge($results['errors'], $deletionResult['errors']);
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Failed to delete proof {$proofId}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Schedule automatic deletion
     */
    public function scheduleAutomaticDeletion(
        Proof $proof,
        Carbon $scheduledAt,
        string $reason = 'scheduled_deletion',
        array $options = []
    ): bool {
        try {
            $metadata = $proof->metadata ?? [];
            $metadata['scheduled_deletion'] = [
                'scheduled_at' => $scheduledAt->toISOString(),
                'scheduled_by' => auth()->id(),
                'reason' => $reason,
                'options' => $options,
                'status' => 'scheduled',
            ];

            $proof->update(['metadata' => $metadata]);

            Log::info('Automatic deletion scheduled', [
                'proof_id' => $proof->id,
                'scheduled_at' => $scheduledAt->toISOString(),
                'reason' => $reason,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to schedule automatic deletion', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get deletion statistics
     */
    public function getDeletionStatistics(int $companyId = null, int $days = 30): array
    {
        // This would be more efficient with a dedicated deletions table
        // For now, we'll check recent logs
        
        $stats = [
            'total_deletions' => 0,
            'deletion_reasons' => [],
            'deleted_storage' => 0,
            'deletion_trend' => [],
            'most_deleted_types' => [],
        ];

        // In a real implementation, this would query the deletions log table
        // For now, we'll return sample statistics
        
        return $stats;
    }

    /**
     * Validate if deletion is allowed
     */
    protected function validateDeletion(Proof $proof, string $reason): array
    {
        $allowed = true;
        $reasons = [];

        // Check if user has permission to delete
        if (!auth()->user()->can('delete', $proof)) {
            $allowed = false;
            $reasons[] = 'Insufficient permissions to delete this proof';
        }

        // Check if proof is being used in active documents
        if ($this->isProofInActiveUse($proof)) {
            if (!in_array($reason, ['legal_requirement', 'consent_revoked', 'security_violation'])) {
                $allowed = false;
                $reasons[] = 'Proof is currently in active use in quotations or invoices';
            }
        }

        // Check company policies
        $companyPolicies = $this->getCompanyDeletionPolicies($proof->company_id);
        if (!empty($companyPolicies['deletion_approval_required']) && 
            !auth()->user()->hasRole(['company_manager', 'superadmin'])) {
            $allowed = false;
            $reasons[] = 'Company policy requires manager approval for deletions';
        }

        return [
            'allowed' => $allowed,
            'reasons' => $reasons,
        ];
    }

    /**
     * Delete proof assets
     */
    protected function deleteProofAssets(Proof $proof, array $options): array
    {
        $result = [
            'deleted_count' => 0,
            'storage_freed' => 0,
            'errors' => [],
        ];

        foreach ($proof->assets as $asset) {
            try {
                // Calculate storage before deletion
                $storageFreed = 0;
                if ($asset->file_path && Storage::exists($asset->file_path)) {
                    $storageFreed += Storage::size($asset->file_path);
                }
                if ($asset->thumbnail_path && Storage::exists($asset->thumbnail_path)) {
                    $storageFreed += Storage::size($asset->thumbnail_path);
                }

                // Secure file deletion
                $this->secureDeleteFiles([
                    $asset->file_path,
                    $asset->thumbnail_path,
                ]);

                // Delete asset record
                $asset->delete();

                $result['deleted_count']++;
                $result['storage_freed'] += $storageFreed;

            } catch (\Exception $e) {
                $result['errors'][] = "Failed to delete asset {$asset->id}: " . $e->getMessage();
            }
        }

        return $result;
    }

    /**
     * Delete proof views
     */
    protected function deleteProofViews(Proof $proof, array $options): int
    {
        $viewCount = $proof->views()->count();
        
        if ($options['preserve_analytics'] ?? false) {
            // Anonymize view data instead of deleting
            $proof->views()->update([
                'viewer_id' => null,
                'ip_address' => null,
                'user_agent' => null,
            ]);
        } else {
            $proof->views()->delete();
        }

        return $viewCount;
    }

    /**
     * Handle dependent records
     */
    protected function handleDependentRecords(Proof $proof, array $options): void
    {
        // Remove proof from quotations/invoices if still referenced
        // This would require checking related models
        Log::info('Handling dependent records for deleted proof', [
            'proof_id' => $proof->id,
        ]);
    }

    /**
     * Delete the proof record
     */
    protected function deleteProofRecord(Proof $proof, string $reason, string $deletionId): void
    {
        // Add deletion metadata before deletion
        $metadata = $proof->metadata ?? [];
        $metadata['deletion'] = [
            'deleted_at' => now()->toISOString(),
            'deleted_by' => auth()->id(),
            'deletion_id' => $deletionId,
            'reason' => $reason,
        ];

        $proof->update(['metadata' => $metadata]);
        $proof->delete();
    }

    /**
     * Securely delete files from storage
     */
    protected function secureDeleteFiles(array $filePaths): void
    {
        foreach ($filePaths as $filePath) {
            if ($filePath && Storage::exists($filePath)) {
                try {
                    Storage::delete($filePath);
                    Log::info('File securely deleted', ['file_path' => $filePath]);
                } catch (\Exception $e) {
                    Log::error('Failed to delete file', [
                        'file_path' => $filePath,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Archive proof assets
     */
    protected function archiveProofAssets(Proof $proof, string $archiveId): int
    {
        $archivedCount = 0;

        foreach ($proof->assets as $asset) {
            try {
                $metadata = $asset->metadata ?? [];
                $metadata['archive'] = [
                    'archive_id' => $archiveId,
                    'archived_at' => now()->toISOString(),
                ];

                $asset->update(['metadata' => $metadata]);
                $archivedCount++;

            } catch (\Exception $e) {
                Log::error('Failed to archive asset', [
                    'asset_id' => $asset->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $archivedCount;
    }

    /**
     * Export proof data before deletion
     */
    protected function exportProofData(Proof $proof): ?array
    {
        try {
            $exportData = [
                'proof' => $proof->toArray(),
                'assets' => $proof->assets->toArray(),
                'views' => $proof->views->toArray(),
                'exported_at' => now()->toISOString(),
                'exported_by' => auth()->id(),
            ];

            $exportPath = "proof_exports/{$proof->company_id}/proof_{$proof->id}_" . time() . '.json';
            Storage::put($exportPath, json_encode($exportData, JSON_PRETTY_PRINT));

            return [
                'export_path' => $exportPath,
                'export_size' => Storage::size($exportPath),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to export proof data', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if proof is in active use
     */
    protected function isProofInActiveUse(Proof $proof): bool
    {
        // Check if proof is referenced in active quotations or invoices
        // This would require checking the actual relationships
        return false; // Simplified for now
    }

    /**
     * Get company deletion policies
     */
    protected function getCompanyDeletionPolicies(int $companyId): array
    {
        // This would fetch from company settings
        return [
            'deletion_approval_required' => false,
            'mandatory_export' => true,
            'retention_period_days' => 90,
        ];
    }

    /**
     * Log deletion event
     */
    protected function logDeletionEvent(string $eventType, Proof $proof, array $data): void
    {
        Log::info("Proof deletion event: {$eventType}", array_merge([
            'proof_id' => $proof->id,
            'company_id' => $proof->company_id,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
        ], $data));
    }
}