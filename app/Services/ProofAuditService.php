<?php

namespace App\Services;

use App\Models\Proof;
use App\Models\ProofAsset;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProofAuditService
{
    /**
     * Audit event types
     */
    const EVENT_TYPES = [
        'created' => 'Created',
        'updated' => 'Updated',
        'deleted' => 'Deleted',
        'published' => 'Published',
        'archived' => 'Archived',
        'viewed' => 'Viewed',
        'downloaded' => 'Downloaded',
        'shared' => 'Shared',
        'consent_granted' => 'Consent Granted',
        'consent_revoked' => 'Consent Revoked',
        'approval_submitted' => 'Submitted for Approval',
        'approval_granted' => 'Approved',
        'approval_rejected' => 'Rejected',
        'asset_uploaded' => 'Asset Uploaded',
        'asset_deleted' => 'Asset Deleted',
        'data_exported' => 'Data Exported',
        'data_anonymized' => 'Data Anonymized',
    ];

    /**
     * Log proof audit event
     */
    public function logEvent(
        string $eventType,
        Model $auditable,
        array $data = [],
        int $userId = null
    ): bool {
        try {
            $auditData = [
                'event_type' => $eventType,
                'auditable_type' => get_class($auditable),
                'auditable_id' => $auditable->id,
                'user_id' => $userId ?? auth()->id(),
                'company_id' => $this->getCompanyId($auditable),
                'data' => $data,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toISOString(),
            ];

            // Store in proof metadata for now (in production, use dedicated audit_logs table)
            $this->storeAuditLog($auditable, $auditData);

            Log::info('Proof audit event logged', [
                'event_type' => $eventType,
                'auditable_type' => get_class($auditable),
                'auditable_id' => $auditable->id,
                'user_id' => $auditData['user_id'],
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to log audit event', [
                'event_type' => $eventType,
                'auditable_id' => $auditable->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Log proof creation
     */
    public function logProofCreated(Proof $proof): void
    {
        $this->logEvent('created', $proof, [
            'title' => $proof->title,
            'type' => $proof->type,
            'status' => $proof->status,
            'scope_type' => $proof->scope_type,
            'scope_id' => $proof->scope_id,
        ]);
    }

    /**
     * Log proof update
     */
    public function logProofUpdated(Proof $proof, array $changes): void
    {
        $this->logEvent('updated', $proof, [
            'changes' => $changes,
            'updated_fields' => array_keys($changes),
        ]);
    }

    /**
     * Log proof publication
     */
    public function logProofPublished(Proof $proof): void
    {
        $this->logEvent('published', $proof, [
            'title' => $proof->title,
            'published_at' => $proof->published_at?->toISOString(),
        ]);
    }

    /**
     * Log proof archival
     */
    public function logProofArchived(Proof $proof): void
    {
        $this->logEvent('archived', $proof, [
            'title' => $proof->title,
            'previous_status' => $proof->getOriginal('status'),
        ]);
    }

    /**
     * Log proof view
     */
    public function logProofViewed(Proof $proof, string $context = null): void
    {
        $this->logEvent('viewed', $proof, [
            'title' => $proof->title,
            'context' => $context,
            'viewer_ip' => request()->ip(),
        ]);
    }

    /**
     * Log proof download
     */
    public function logProofDownloaded(Proof $proof, string $format = 'pdf'): void
    {
        $this->logEvent('downloaded', $proof, [
            'title' => $proof->title,
            'format' => $format,
            'download_ip' => request()->ip(),
        ]);
    }

    /**
     * Log proof sharing
     */
    public function logProofShared(Proof $proof, array $shareData): void
    {
        $this->logEvent('shared', $proof, [
            'title' => $proof->title,
            'share_type' => $shareData['type'] ?? 'unknown',
            'recipients' => $shareData['recipients'] ?? [],
            'expiration' => $shareData['expiration'] ?? null,
        ]);
    }

    /**
     * Log consent events
     */
    public function logConsentGranted(Proof $proof, array $consentData): void
    {
        $this->logEvent('consent_granted', $proof, [
            'title' => $proof->title,
            'customer_email' => $consentData['customer_email'] ?? null,
            'consent_type' => $consentData['consent_type'] ?? null,
            'ip_address' => $consentData['ip_address'] ?? null,
        ]);
    }

    public function logConsentRevoked(Proof $proof, string $reason = null): void
    {
        $this->logEvent('consent_revoked', $proof, [
            'title' => $proof->title,
            'reason' => $reason,
            'revoked_by_ip' => request()->ip(),
        ]);
    }

    /**
     * Log approval events
     */
    public function logApprovalSubmitted(Proof $proof, array $approvers): void
    {
        $this->logEvent('approval_submitted', $proof, [
            'title' => $proof->title,
            'approvers' => $approvers,
            'submitted_by' => auth()->id(),
        ]);
    }

    public function logApprovalGranted(Proof $proof, int $approverId): void
    {
        $this->logEvent('approval_granted', $proof, [
            'title' => $proof->title,
            'approved_by' => $approverId,
        ]);
    }

    public function logApprovalRejected(Proof $proof, int $approverId, string $reason): void
    {
        $this->logEvent('approval_rejected', $proof, [
            'title' => $proof->title,
            'rejected_by' => $approverId,
            'reason' => $reason,
        ]);
    }

    /**
     * Log asset events
     */
    public function logAssetUploaded(ProofAsset $asset): void
    {
        $this->logEvent('asset_uploaded', $asset, [
            'file_name' => $asset->file_name,
            'file_type' => $asset->type,
            'file_size' => $asset->file_size,
            'proof_id' => $asset->proof_id,
        ]);
    }

    public function logAssetDeleted(ProofAsset $asset): void
    {
        $this->logEvent('asset_deleted', $asset, [
            'file_name' => $asset->file_name,
            'file_type' => $asset->type,
            'proof_id' => $asset->proof_id,
        ]);
    }

    /**
     * Log data protection events
     */
    public function logDataExported(Proof $proof, string $format): void
    {
        $this->logEvent('data_exported', $proof, [
            'title' => $proof->title,
            'export_format' => $format,
            'exported_by' => auth()->id(),
        ]);
    }

    public function logDataAnonymized(Proof $proof): void
    {
        $this->logEvent('data_anonymized', $proof, [
            'title' => $proof->title,
            'anonymized_by' => auth()->id(),
        ]);
    }

    /**
     * Get audit log for a proof
     */
    public function getAuditLog(Proof $proof): array
    {
        $metadata = $proof->metadata ?? [];
        $auditLog = $metadata['audit_log'] ?? [];

        // Sort by timestamp descending (newest first)
        usort($auditLog, function ($a, $b) {
            return strcmp($b['timestamp'] ?? '', $a['timestamp'] ?? '');
        });

        return array_map(function ($entry) {
            return [
                'event_type' => $entry['event_type'],
                'event_name' => self::EVENT_TYPES[$entry['event_type']] ?? $entry['event_type'],
                'user_id' => $entry['user_id'],
                'user_name' => $this->getUserName($entry['user_id']),
                'timestamp' => $entry['timestamp'],
                'ip_address' => $entry['ip_address'] ?? null,
                'data' => $entry['data'] ?? [],
            ];
        }, $auditLog);
    }

    /**
     * Get audit summary for a proof
     */
    public function getAuditSummary(Proof $proof): array
    {
        $auditLog = $this->getAuditLog($proof);
        
        $summary = [
            'total_events' => count($auditLog),
            'event_counts' => [],
            'unique_users' => [],
            'first_event' => null,
            'last_event' => null,
            'most_active_user' => null,
        ];

        $userCounts = [];

        foreach ($auditLog as $entry) {
            // Count event types
            $eventType = $entry['event_type'];
            $summary['event_counts'][$eventType] = ($summary['event_counts'][$eventType] ?? 0) + 1;
            
            // Track unique users
            if ($entry['user_id']) {
                $summary['unique_users'][$entry['user_id']] = $entry['user_name'];
                $userCounts[$entry['user_id']] = ($userCounts[$entry['user_id']] ?? 0) + 1;
            }
        }

        // Get first and last events
        if (!empty($auditLog)) {
            $summary['last_event'] = $auditLog[0]; // First in sorted array (newest)
            $summary['first_event'] = end($auditLog); // Last in sorted array (oldest)
        }

        // Find most active user
        if (!empty($userCounts)) {
            $mostActiveUserId = array_key_first(array_slice(arsort($userCounts) ? $userCounts : [], 0, 1, true));
            $summary['most_active_user'] = [
                'user_id' => $mostActiveUserId,
                'user_name' => $summary['unique_users'][$mostActiveUserId] ?? 'Unknown',
                'event_count' => $userCounts[$mostActiveUserId],
            ];
        }

        return $summary;
    }

    /**
     * Get company-wide audit statistics
     */
    public function getCompanyAuditStats(int $companyId, int $days = 30): array
    {
        // This would be more efficient with a dedicated audit_logs table
        // For now, we'll aggregate from proof metadata
        $cutoffDate = now()->subDays($days);
        
        $proofs = Proof::where('company_id', $companyId)->get();
        $stats = [
            'total_events' => 0,
            'event_counts' => [],
            'user_activity' => [],
            'most_audited_proofs' => [],
            'security_events' => 0,
            'data_protection_events' => 0,
        ];

        foreach ($proofs as $proof) {
            $auditLog = $this->getAuditLog($proof);
            
            foreach ($auditLog as $entry) {
                $entryDate = Carbon::parse($entry['timestamp']);
                
                if ($entryDate->isAfter($cutoffDate)) {
                    $stats['total_events']++;
                    
                    // Count by event type
                    $eventType = $entry['event_type'];
                    $stats['event_counts'][$eventType] = ($stats['event_counts'][$eventType] ?? 0) + 1;
                    
                    // Count by user
                    if ($entry['user_id']) {
                        $userId = $entry['user_id'];
                        if (!isset($stats['user_activity'][$userId])) {
                            $stats['user_activity'][$userId] = [
                                'user_name' => $entry['user_name'],
                                'event_count' => 0,
                            ];
                        }
                        $stats['user_activity'][$userId]['event_count']++;
                    }
                    
                    // Count security-related events
                    if (in_array($eventType, ['consent_revoked', 'data_exported', 'data_anonymized', 'approval_rejected'])) {
                        $stats['security_events']++;
                    }
                    
                    // Count data protection events
                    if (in_array($eventType, ['consent_granted', 'consent_revoked', 'data_exported', 'data_anonymized'])) {
                        $stats['data_protection_events']++;
                    }
                }
            }
            
            // Track most audited proofs
            $eventCount = count(array_filter($auditLog, function ($entry) use ($cutoffDate) {
                return Carbon::parse($entry['timestamp'])->isAfter($cutoffDate);
            }));
            
            if ($eventCount > 0) {
                $stats['most_audited_proofs'][] = [
                    'proof_id' => $proof->id,
                    'proof_title' => $proof->title,
                    'event_count' => $eventCount,
                ];
            }
        }

        // Sort most audited proofs
        usort($stats['most_audited_proofs'], function ($a, $b) {
            return $b['event_count'] - $a['event_count'];
        });
        $stats['most_audited_proofs'] = array_slice($stats['most_audited_proofs'], 0, 10);

        return $stats;
    }

    /**
     * Export audit log
     */
    public function exportAuditLog(int $companyId, array $filters = []): array
    {
        $proofs = Proof::where('company_id', $companyId)->get();
        $exportData = [];

        foreach ($proofs as $proof) {
            $auditLog = $this->getAuditLog($proof);
            
            foreach ($auditLog as $entry) {
                if ($this->matchesFilters($entry, $filters)) {
                    $exportData[] = [
                        'proof_id' => $proof->id,
                        'proof_title' => $proof->title,
                        'proof_type' => $proof->type,
                        'event_type' => $entry['event_type'],
                        'event_name' => $entry['event_name'],
                        'user_id' => $entry['user_id'],
                        'user_name' => $entry['user_name'],
                        'timestamp' => $entry['timestamp'],
                        'ip_address' => $entry['ip_address'],
                        'event_data' => json_encode($entry['data']),
                    ];
                }
            }
        }

        return $exportData;
    }

    /**
     * Clean up old audit logs
     */
    public function cleanupOldAuditLogs(int $companyId, int $retentionMonths = 24): array
    {
        $cutoffDate = now()->subMonths($retentionMonths);
        $cleanedCount = 0;
        
        $proofs = Proof::where('company_id', $companyId)->get();
        
        foreach ($proofs as $proof) {
            $metadata = $proof->metadata ?? [];
            $auditLog = $metadata['audit_log'] ?? [];
            
            $originalCount = count($auditLog);
            
            // Keep only logs after cutoff date
            $auditLog = array_filter($auditLog, function ($entry) use ($cutoffDate) {
                return Carbon::parse($entry['timestamp'])->isAfter($cutoffDate);
            });
            
            $cleanedCount += $originalCount - count($auditLog);
            
            if ($originalCount !== count($auditLog)) {
                $metadata['audit_log'] = array_values($auditLog);
                $proof->update(['metadata' => $metadata]);
            }
        }

        Log::info('Audit log cleanup completed', [
            'company_id' => $companyId,
            'retention_months' => $retentionMonths,
            'cleaned_entries' => $cleanedCount,
        ]);

        return [
            'cleaned_entries' => $cleanedCount,
            'retention_months' => $retentionMonths,
            'cutoff_date' => $cutoffDate,
        ];
    }

    /**
     * Store audit log entry in model metadata
     */
    protected function storeAuditLog(Model $model, array $auditData): void
    {
        if ($model instanceof Proof) {
            $metadata = $model->metadata ?? [];
            $metadata['audit_log'] = $metadata['audit_log'] ?? [];
            $metadata['audit_log'][] = $auditData;
            $model->update(['metadata' => $metadata]);
        } elseif ($model instanceof ProofAsset) {
            // For proof assets, store in the parent proof
            $proof = $model->proof;
            if ($proof) {
                $this->storeAuditLog($proof, $auditData);
            }
        }
    }

    /**
     * Get company ID from auditable model
     */
    protected function getCompanyId(Model $model): ?int
    {
        if (isset($model->company_id)) {
            return $model->company_id;
        }
        
        if ($model instanceof ProofAsset && $model->proof) {
            return $model->proof->company_id;
        }
        
        return auth()->user()->company_id ?? null;
    }

    /**
     * Get user name for audit display
     */
    protected function getUserName(?int $userId): ?string
    {
        if (!$userId) {
            return 'System';
        }
        
        $user = User::find($userId);
        return $user ? $user->name : 'Unknown User';
    }

    /**
     * Check if audit entry matches filters
     */
    protected function matchesFilters(array $entry, array $filters): bool
    {
        if (!empty($filters['event_types']) && !in_array($entry['event_type'], $filters['event_types'])) {
            return false;
        }
        
        if (!empty($filters['user_ids']) && !in_array($entry['user_id'], $filters['user_ids'])) {
            return false;
        }
        
        if (!empty($filters['start_date'])) {
            $entryDate = Carbon::parse($entry['timestamp']);
            $startDate = Carbon::parse($filters['start_date']);
            if ($entryDate->isBefore($startDate)) {
                return false;
            }
        }
        
        if (!empty($filters['end_date'])) {
            $entryDate = Carbon::parse($entry['timestamp']);
            $endDate = Carbon::parse($filters['end_date']);
            if ($entryDate->isAfter($endDate)) {
                return false;
            }
        }
        
        return true;
    }
}