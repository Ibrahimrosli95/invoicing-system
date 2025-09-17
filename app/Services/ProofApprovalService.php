<?php

namespace App\Services;

use App\Models\Proof;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ProofApprovalService
{
    /**
     * Approval workflow states
     */
    const WORKFLOW_STATES = [
        'pending_review' => 'Pending Review',
        'under_review' => 'Under Review',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'revision_requested' => 'Revision Requested',
        'withdrawn' => 'Withdrawn',
    ];

    /**
     * Submit proof for approval
     */
    public function submitForApproval(Proof $proof, array $approvers = null): bool
    {
        try {
            // Determine approvers if not specified
            if (!$approvers) {
                $approvers = $this->getDefaultApprovers($proof);
            }

            if (empty($approvers)) {
                Log::warning('No approvers found for proof submission', [
                    'proof_id' => $proof->id,
                    'company_id' => $proof->company_id,
                ]);
                return false;
            }

            $metadata = $proof->metadata ?? [];
            $metadata['approval'] = [
                'workflow_state' => 'pending_review',
                'submitted_at' => now()->toISOString(),
                'submitted_by' => auth()->id(),
                'approvers' => $approvers,
                'current_approver' => $approvers[0], // First approver
                'approval_history' => [],
                'requires_all_approvers' => $this->requiresAllApprovers($proof),
                'deadline' => now()->addDays(5)->toISOString(), // 5 day deadline
            ];

            $proof->update([
                'metadata' => $metadata,
                'status' => 'draft', // Keep as draft until approved
            ]);

            // Notify first approver
            $this->notifyApprover($proof, $approvers[0]);

            Log::info('Proof submitted for approval', [
                'proof_id' => $proof->id,
                'approvers' => $approvers,
                'submitted_by' => auth()->id(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to submit proof for approval', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Approve a proof
     */
    public function approveProof(Proof $proof, array $approvalData = []): bool
    {
        try {
            $metadata = $proof->metadata ?? [];
            $approvalInfo = $metadata['approval'] ?? [];
            
            if (!$this->canApprove(auth()->id(), $approvalInfo)) {
                Log::warning('User cannot approve this proof', [
                    'proof_id' => $proof->id,
                    'user_id' => auth()->id(),
                ]);
                return false;
            }

            // Record approval
            $approval = [
                'approver_id' => auth()->id(),
                'decision' => 'approved',
                'approved_at' => now()->toISOString(),
                'comments' => $approvalData['comments'] ?? null,
                'conditions' => $approvalData['conditions'] ?? null,
            ];

            $approvalInfo['approval_history'][] = $approval;
            
            // Check if we need more approvers
            $nextApprover = $this->getNextApprover($approvalInfo, auth()->id());
            
            if ($nextApprover && $approvalInfo['requires_all_approvers']) {
                // Move to next approver
                $approvalInfo['current_approver'] = $nextApprover;
                $approvalInfo['workflow_state'] = 'under_review';
                
                $this->notifyApprover($proof, $nextApprover);
                
                Log::info('Proof approved, forwarded to next approver', [
                    'proof_id' => $proof->id,
                    'approved_by' => auth()->id(),
                    'next_approver' => $nextApprover,
                ]);
            } else {
                // Final approval - publish the proof
                $approvalInfo['workflow_state'] = 'approved';
                $approvalInfo['final_approval_at'] = now()->toISOString();
                
                $proof->update([
                    'status' => 'active',
                    'published_at' => now(),
                ]);
                
                $this->notifySubmitter($proof, 'approved');
                
                Log::info('Proof fully approved and published', [
                    'proof_id' => $proof->id,
                    'final_approver' => auth()->id(),
                ]);
            }

            $metadata['approval'] = $approvalInfo;
            $proof->update(['metadata' => $metadata]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to approve proof', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Reject a proof
     */
    public function rejectProof(Proof $proof, array $rejectionData): bool
    {
        try {
            $metadata = $proof->metadata ?? [];
            $approvalInfo = $metadata['approval'] ?? [];
            
            if (!$this->canApprove(auth()->id(), $approvalInfo)) {
                return false;
            }

            // Record rejection
            $rejection = [
                'approver_id' => auth()->id(),
                'decision' => 'rejected',
                'rejected_at' => now()->toISOString(),
                'reason' => $rejectionData['reason'] ?? 'No reason provided',
                'comments' => $rejectionData['comments'] ?? null,
                'suggested_changes' => $rejectionData['suggested_changes'] ?? null,
            ];

            $approvalInfo['approval_history'][] = $rejection;
            $approvalInfo['workflow_state'] = 'rejected';
            $approvalInfo['rejected_at'] = now()->toISOString();

            $metadata['approval'] = $approvalInfo;
            $proof->update(['metadata' => $metadata]);

            $this->notifySubmitter($proof, 'rejected', $rejectionData);

            Log::info('Proof rejected', [
                'proof_id' => $proof->id,
                'rejected_by' => auth()->id(),
                'reason' => $rejectionData['reason'] ?? 'No reason provided',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to reject proof', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Request revisions for a proof
     */
    public function requestRevisions(Proof $proof, array $revisionData): bool
    {
        try {
            $metadata = $proof->metadata ?? [];
            $approvalInfo = $metadata['approval'] ?? [];
            
            if (!$this->canApprove(auth()->id(), $approvalInfo)) {
                return false;
            }

            // Record revision request
            $revision = [
                'approver_id' => auth()->id(),
                'decision' => 'revision_requested',
                'requested_at' => now()->toISOString(),
                'requested_changes' => $revisionData['requested_changes'] ?? [],
                'comments' => $revisionData['comments'] ?? null,
                'deadline' => isset($revisionData['deadline']) 
                    ? Carbon::parse($revisionData['deadline'])->toISOString()
                    : now()->addDays(3)->toISOString(),
            ];

            $approvalInfo['approval_history'][] = $revision;
            $approvalInfo['workflow_state'] = 'revision_requested';
            $approvalInfo['revision_requested_at'] = now()->toISOString();

            $metadata['approval'] = $approvalInfo;
            $proof->update(['metadata' => $metadata]);

            $this->notifySubmitter($proof, 'revision_requested', $revisionData);

            Log::info('Proof revision requested', [
                'proof_id' => $proof->id,
                'requested_by' => auth()->id(),
                'changes_requested' => count($revisionData['requested_changes'] ?? []),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to request revision', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Resubmit proof after revisions
     */
    public function resubmitAfterRevisions(Proof $proof, string $revisionNotes = null): bool
    {
        try {
            $metadata = $proof->metadata ?? [];
            $approvalInfo = $metadata['approval'] ?? [];
            
            if ($approvalInfo['workflow_state'] !== 'revision_requested') {
                return false;
            }

            // Record resubmission
            $resubmission = [
                'resubmitted_by' => auth()->id(),
                'resubmitted_at' => now()->toISOString(),
                'revision_notes' => $revisionNotes,
            ];

            $approvalInfo['approval_history'][] = $resubmission;
            $approvalInfo['workflow_state'] = 'pending_review';
            $approvalInfo['resubmitted_at'] = now()->toISOString();

            // Reset to first approver
            $approvalInfo['current_approver'] = $approvalInfo['approvers'][0];

            $metadata['approval'] = $approvalInfo;
            $proof->update(['metadata' => $metadata]);

            // Notify approver about resubmission
            $this->notifyApprover($proof, $approvalInfo['current_approver'], 'resubmitted');

            Log::info('Proof resubmitted after revisions', [
                'proof_id' => $proof->id,
                'resubmitted_by' => auth()->id(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to resubmit proof', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Withdraw proof from approval
     */
    public function withdrawFromApproval(Proof $proof, string $reason = null): bool
    {
        try {
            $metadata = $proof->metadata ?? [];
            $approvalInfo = $metadata['approval'] ?? [];
            
            // Only submitter can withdraw
            if (($approvalInfo['submitted_by'] ?? null) !== auth()->id()) {
                return false;
            }

            $withdrawal = [
                'withdrawn_by' => auth()->id(),
                'withdrawn_at' => now()->toISOString(),
                'reason' => $reason,
            ];

            $approvalInfo['approval_history'][] = $withdrawal;
            $approvalInfo['workflow_state'] = 'withdrawn';
            $approvalInfo['withdrawn_at'] = now()->toISOString();

            $metadata['approval'] = $approvalInfo;
            $proof->update(['metadata' => $metadata]);

            Log::info('Proof withdrawn from approval', [
                'proof_id' => $proof->id,
                'withdrawn_by' => auth()->id(),
                'reason' => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to withdraw proof', [
                'proof_id' => $proof->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get approval status for a proof
     */
    public function getApprovalStatus(Proof $proof): array
    {
        $metadata = $proof->metadata ?? [];
        $approvalInfo = $metadata['approval'] ?? [];

        if (empty($approvalInfo)) {
            return ['status' => 'not_submitted'];
        }

        return [
            'status' => $approvalInfo['workflow_state'],
            'submitted_at' => $approvalInfo['submitted_at'] ?? null,
            'submitted_by' => $approvalInfo['submitted_by'] ?? null,
            'current_approver' => $approvalInfo['current_approver'] ?? null,
            'approvers' => $approvalInfo['approvers'] ?? [],
            'approval_history' => $approvalInfo['approval_history'] ?? [],
            'deadline' => $approvalInfo['deadline'] ?? null,
            'is_overdue' => $this->isApprovalOverdue($approvalInfo),
            'days_pending' => $this->getDaysPending($approvalInfo),
            'requires_all_approvers' => $approvalInfo['requires_all_approvers'] ?? false,
        ];
    }

    /**
     * Get proofs pending approval for a user
     */
    public function getPendingApprovals(int $userId): array
    {
        $proofs = Proof::whereNotNull('metadata')
            ->where('status', 'draft')
            ->get()
            ->filter(function ($proof) use ($userId) {
                $approvalInfo = $proof->metadata['approval'] ?? [];
                return ($approvalInfo['current_approver'] ?? null) === $userId &&
                       in_array($approvalInfo['workflow_state'] ?? null, ['pending_review', 'under_review']);
            });

        return $proofs->map(function ($proof) {
            return [
                'proof' => $proof,
                'approval_status' => $this->getApprovalStatus($proof),
                'urgency' => $this->getApprovalUrgency($proof),
            ];
        })->sortBy('urgency')->values()->toArray();
    }

    /**
     * Get overdue approvals
     */
    public function getOverdueApprovals(int $companyId = null): array
    {
        $query = Proof::whereNotNull('metadata')->where('status', 'draft');
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        
        $proofs = $query->get()->filter(function ($proof) {
            $approvalInfo = $proof->metadata['approval'] ?? [];
            return $this->isApprovalOverdue($approvalInfo);
        });

        return $proofs->map(function ($proof) {
            return [
                'proof' => $proof,
                'approval_status' => $this->getApprovalStatus($proof),
                'days_overdue' => $this->getDaysOverdue($proof->metadata['approval'] ?? []),
            ];
        })->toArray();
    }

    /**
     * Bulk approve multiple proofs
     */
    public function bulkApprove(array $proofIds, array $approvalData = []): array
    {
        $results = [
            'approved' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($proofIds as $proofId) {
            try {
                $proof = Proof::find($proofId);
                if ($proof && $this->approveProof($proof, $approvalData)) {
                    $results['approved']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to approve proof {$proofId}";
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error approving proof {$proofId}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get default approvers for a proof
     */
    protected function getDefaultApprovers(Proof $proof): array
    {
        // Find users with approval permissions for this company
        $approvers = User::where('company_id', $proof->company_id)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', [
                    'company_manager',
                    'sales_manager'
                ]);
            })
            ->pluck('id')
            ->toArray();

        // If no specific approvers, use company manager
        if (empty($approvers)) {
            $companyManager = User::where('company_id', $proof->company_id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'company_manager');
                })
                ->first();
            
            if ($companyManager) {
                $approvers = [$companyManager->id];
            }
        }

        return $approvers;
    }

    /**
     * Check if user can approve this proof
     */
    protected function canApprove(int $userId, array $approvalInfo): bool
    {
        return ($approvalInfo['current_approver'] ?? null) === $userId &&
               in_array($approvalInfo['workflow_state'] ?? null, ['pending_review', 'under_review']);
    }

    /**
     * Get next approver in workflow
     */
    protected function getNextApprover(array $approvalInfo, int $currentApprover): ?int
    {
        $approvers = $approvalInfo['approvers'] ?? [];
        $currentIndex = array_search($currentApprover, $approvers);
        
        if ($currentIndex !== false && $currentIndex + 1 < count($approvers)) {
            return $approvers[$currentIndex + 1];
        }
        
        return null;
    }

    /**
     * Check if proof requires all approvers
     */
    protected function requiresAllApprovers(Proof $proof): bool
    {
        // High-value proofs or sensitive content require all approvers
        return in_array($proof->type, ['case_study', 'testimonial']) ||
               ($proof->metadata['contains_pii'] ?? false) ||
               ($proof->metadata['high_value'] ?? false);
    }

    /**
     * Check if approval is overdue
     */
    protected function isApprovalOverdue(array $approvalInfo): bool
    {
        $deadline = $approvalInfo['deadline'] ?? null;
        return $deadline && Carbon::parse($deadline)->isPast();
    }

    /**
     * Get days pending approval
     */
    protected function getDaysPending(array $approvalInfo): int
    {
        $submittedAt = $approvalInfo['submitted_at'] ?? null;
        return $submittedAt ? now()->diffInDays(Carbon::parse($submittedAt)) : 0;
    }

    /**
     * Get days overdue
     */
    protected function getDaysOverdue(array $approvalInfo): int
    {
        $deadline = $approvalInfo['deadline'] ?? null;
        return $deadline && Carbon::parse($deadline)->isPast() 
            ? now()->diffInDays(Carbon::parse($deadline)) 
            : 0;
    }

    /**
     * Get approval urgency level
     */
    protected function getApprovalUrgency(Proof $proof): int
    {
        $approvalInfo = $proof->metadata['approval'] ?? [];
        $daysOverdue = $this->getDaysOverdue($approvalInfo);
        
        if ($daysOverdue > 5) return 5; // Critical
        if ($daysOverdue > 2) return 4; // High
        if ($daysOverdue > 0) return 3; // Medium
        
        $daysPending = $this->getDaysPending($approvalInfo);
        if ($daysPending > 3) return 2; // Low urgency
        
        return 1; // Normal
    }

    /**
     * Notify approver about pending approval
     */
    protected function notifyApprover(Proof $proof, int $approverId, string $type = 'new'): void
    {
        // In a real implementation, you would send an email notification
        Log::info('Approver notification sent', [
            'proof_id' => $proof->id,
            'approver_id' => $approverId,
            'type' => $type,
            'proof_title' => $proof->title,
        ]);
    }

    /**
     * Notify submitter about approval decision
     */
    protected function notifySubmitter(Proof $proof, string $decision, array $data = []): void
    {
        $submitterId = $proof->metadata['approval']['submitted_by'] ?? null;
        
        if ($submitterId) {
            Log::info('Submitter notification sent', [
                'proof_id' => $proof->id,
                'submitter_id' => $submitterId,
                'decision' => $decision,
                'proof_title' => $proof->title,
            ]);
        }
    }
}