<?php

namespace App\Policies;

use App\Models\Proof;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProofPolicy
{
    /**
     * Determine whether the user can view any proofs
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view proofs from their company
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can view the proof
     */
    public function view(User $user, Proof $proof): bool
    {
        // Users can view proofs from their company
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        // Check if proof is published and not expired for non-management users
        if (!$this->isManagement($user)) {
            return $proof->canBeViewed() || $proof->created_by === $user->id;
        }

        return true;
    }

    /**
     * Determine whether the user can create proofs
     */
    public function create(User $user): bool
    {
        // Sales coordinators and above can create proofs
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager',
            'sales_manager',
            'sales_coordinator'
        ]);
    }

    /**
     * Determine whether the user can update the proof
     */
    public function update(User $user, Proof $proof): bool
    {
        // Must be from same company
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        // Superadmin and company managers can update any proof
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Sales managers can update proofs they created or from their teams
        if ($user->hasRole('sales_manager')) {
            return $proof->created_by === $user->id || $this->isFromUserTeams($user, $proof);
        }

        // Sales coordinators and creators can update their own proofs
        if ($user->hasAnyRole(['sales_coordinator', 'finance_manager'])) {
            return $proof->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the proof
     */
    public function delete(User $user, Proof $proof): bool
    {
        // Must be from same company
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        // Superadmin and company managers can delete any proof
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Sales managers can delete proofs they created
        if ($user->hasRole('sales_manager')) {
            return $proof->created_by === $user->id;
        }

        // Only creators can delete their own draft proofs
        return $proof->created_by === $user->id && $proof->status === 'draft';
    }

    /**
     * Determine whether the user can publish/unpublish proofs
     */
    public function publish(User $user, Proof $proof): bool
    {
        // Must be from same company
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        // Managers can publish any proof
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can manage featured proofs
     */
    public function manageFeatured(User $user, Proof $proof): bool
    {
        // Must be from same company
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        // Only company managers and above can manage featured proofs
        return $user->hasAnyRole([
            'superadmin',
            'company_manager'
        ]);
    }

    /**
     * Determine whether the user can duplicate the proof
     */
    public function duplicate(User $user, Proof $proof): bool
    {
        // Can duplicate if they can view and create
        return $this->view($user, $proof) && $this->create($user);
    }

    /**
     * Determine whether the user can upload assets to the proof
     */
    public function uploadAssets(User $user, Proof $proof): bool
    {
        // Same as update permission
        return $this->update($user, $proof);
    }

    /**
     * Determine whether the user can view proof analytics
     */
    public function viewAnalytics(User $user): bool
    {
        // Sales coordinators and above can view analytics
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager',
            'sales_manager',
            'sales_coordinator'
        ]);
    }

    /**
     * Determine whether the user can attach proofs to documents
     */
    public function attachToDocument(User $user): bool
    {
        // All users who can create proofs can also attach them
        return $this->create($user);
    }

    /**
     * Determine whether the user can view sensitive proof data
     */
    public function viewSensitiveData(User $user, Proof $proof): bool
    {
        // Must be from same company
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        // Management can view all sensitive data
        if ($this->isManagement($user)) {
            return true;
        }

        // Users can view sensitive data for proofs they created
        return $proof->created_by === $user->id;
    }

    /**
     * Check if user is in management roles
     */
    protected function isManagement(User $user): bool
    {
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager',
            'sales_manager'
        ]);
    }

    /**
     * Check if proof creator is from user's teams
     */
    protected function isFromUserTeams(User $user, Proof $proof): bool
    {
        if (!$user->hasRole('sales_manager')) {
            return false;
        }

        // Get teams where user is a sales manager
        $managedTeamIds = $user->teams()->pluck('teams.id');
        
        // Check if proof creator is in any of these teams
        return User::find($proof->created_by)
                   ->teams()
                   ->whereIn('teams.id', $managedTeamIds)
                   ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Proof $proof): bool
    {
        // Only superadmin can restore
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Proof $proof): bool
    {
        // Only superadmin can force delete
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can manage customer consent for testimonials
     */
    public function manageConsent(User $user, Proof $proof): bool
    {
        // Must be from same company
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        // Only testimonial/case study proofs require consent management
        if (!in_array($proof->type, ['testimonial', 'case_study', 'client_review'])) {
            return false;
        }

        // Management and creators can manage consent
        return $this->isManagement($user) || $proof->created_by === $user->id;
    }

    /**
     * Determine whether the user can access archived proofs
     */
    public function viewArchived(User $user): bool
    {
        // Only management can view archived proofs
        return $this->isManagement($user);
    }

    /**
     * Determine whether the user can approve/reject proof content
     */
    public function approveContent(User $user, Proof $proof): bool
    {
        // Must be from same company
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        // Sales managers and above can approve content
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can manage proof retention policies
     */
    public function manageRetention(User $user): bool
    {
        // Only company managers and superadmin can manage retention
        return $user->hasAnyRole([
            'superadmin',
            'company_manager'
        ]);
    }

    /**
     * Determine whether the user can export proof data (GDPR)
     */
    public function exportData(User $user, Proof $proof): bool
    {
        // Must be from same company
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        // Management and creators can export their proof data
        return $this->isManagement($user) || $proof->created_by === $user->id;
    }

    /**
     * Determine whether the user can anonymize proof data
     */
    public function anonymizeData(User $user, Proof $proof): bool
    {
        // Must be from same company
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        // Only management can anonymize data
        return $this->isManagement($user);
    }

    /**
     * Determine whether the user can share proofs externally
     */
    public function shareExternally(User $user, Proof $proof): bool
    {
        // Must be from same company and proof must be published
        if ($user->company_id !== $proof->company_id || $proof->status !== 'active') {
            return false;
        }

        // Management and creators can share externally
        return $this->isManagement($user) || $proof->created_by === $user->id;
    }

    /**
     * Determine whether the user can manage proof templates
     */
    public function manageTemplates(User $user): bool
    {
        // Sales managers and above can manage templates
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can access proof audit logs
     */
    public function viewAuditLogs(User $user): bool
    {
        // Only management can view audit logs
        return $this->isManagement($user);
    }

    /**
     * Determine whether the user can perform bulk operations
     */
    public function bulkOperations(User $user): bool
    {
        // Sales coordinators and above can perform bulk operations
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager',
            'sales_manager',
            'sales_coordinator'
        ]);
    }

    /**
     * Determine whether the user can manage proof workflows
     */
    public function manageWorkflows(User $user): bool
    {
        // Only company managers and above can manage workflows
        return $user->hasAnyRole([
            'superadmin',
            'company_manager'
        ]);
    }

    /**
     * Determine whether the user can access financial data in proofs
     */
    public function viewFinancialData(User $user, Proof $proof): bool
    {
        // Must be from same company
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        // Finance managers and above can view financial data
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'finance_manager'
        ]) || $proof->created_by === $user->id;
    }

    /**
     * Determine whether the user can manage customer data in proofs
     */
    public function manageCustomerData(User $user, Proof $proof): bool
    {
        // Must be from same company
        if ($user->company_id !== $proof->company_id) {
            return false;
        }

        // Only management and creators can manage customer data
        return $this->isManagement($user) || $proof->created_by === $user->id;
    }

    /**
     * Determine whether the user can schedule proof operations
     */
    public function scheduleOperations(User $user): bool
    {
        // Sales managers and above can schedule operations
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager'
        ]);
    }

    /**
     * Check if proof contains sensitive customer information
     */
    protected function containsSensitiveData(Proof $proof): bool
    {
        // Check metadata for sensitive data flags
        $metadata = $proof->metadata ?? [];
        
        return isset($metadata['contains_pii']) && $metadata['contains_pii'] === true ||
               isset($metadata['customer_consent_required']) && $metadata['customer_consent_required'] === true ||
               in_array($proof->type, ['testimonial', 'case_study', 'client_review']) ||
               isset($metadata['financial_data']) && !empty($metadata['financial_data']);
    }

    /**
     * Check if user has specific proof permission
     */
    protected function hasProofPermission(User $user, string $permission): bool
    {
        return $user->can($permission);
    }
}
