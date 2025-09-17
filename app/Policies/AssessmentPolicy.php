<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssessmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any assessments.
     */
    public function viewAny(User $user): bool
    {
        // Check if user has permission to view assessments
        if (!$user->can('view assessments')) {
            return false;
        }

        // All users with view permission can see the assessment listing
        // (actual data filtering happens in controller based on role)
        return true;
    }

    /**
     * Determine whether the user can view the assessment.
     */
    public function view(User $user, Assessment $assessment): bool
    {
        // Check basic permission
        if (!$user->can('view assessments')) {
            return false;
        }

        // Multi-tenant check - must be same company
        if ($assessment->company_id !== $user->company_id) {
            return false;
        }

        // Role-based access control
        if ($user->hasRole(['superadmin', 'company_manager'])) {
            return true; // Can view all assessments in company
        }

        if ($user->hasRole('sales_manager')) {
            // Can view assessments from teams they manage
            $managedTeamIds = $user->managedTeams()->pluck('teams.id');
            
            // Check if assessment's assigned user belongs to managed teams
            if ($assessment->assigned_to) {
                $assignedUser = $assessment->assignedTo;
                $userTeamIds = $assignedUser->teams()->pluck('teams.id');
                return $managedTeamIds->intersect($userTeamIds)->isNotEmpty();
            }
            
            // If no specific assignment, check if assessment's lead belongs to managed teams
            if ($assessment->lead_id) {
                return $managedTeamIds->contains($assessment->lead->team_id);
            }
            
            return false;
        }

        if ($user->hasRole('sales_coordinator')) {
            // Can view assessments from their company (broader access than sales exec)
            return true;
        }

        if ($user->hasRole('sales_executive')) {
            // Can only view assessments assigned to them or from their leads
            if ($assessment->assigned_to === $user->id) {
                return true;
            }
            
            // Can view if it's from their lead
            if ($assessment->lead_id) {
                return $assessment->lead->assigned_to === $user->id;
            }
            
            return false;
        }

        // Finance managers can view assessments for billing purposes
        if ($user->hasRole('finance_manager')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create assessments.
     */
    public function create(User $user): bool
    {
        // Check basic permission
        if (!$user->can('create assessments')) {
            return false;
        }

        // Only sales roles can create assessments
        return $user->hasAnyRole([
            'superadmin',
            'company_manager', 
            'sales_manager',
            'sales_coordinator',
            'sales_executive'
        ]);
    }

    /**
     * Determine whether the user can update the assessment.
     */
    public function update(User $user, Assessment $assessment): bool
    {
        // Check basic permission
        if (!$user->can('update assessments')) {
            return false;
        }

        // Multi-tenant check
        if ($assessment->company_id !== $user->company_id) {
            return false;
        }

        // Cannot edit completed or cancelled assessments (except notes/recommendations)
        if (in_array($assessment->status, [Assessment::STATUS_COMPLETED, Assessment::STATUS_CANCELLED])) {
            // Only allow limited updates for these statuses
            return $user->hasAnyRole(['superadmin', 'company_manager', 'sales_manager']);
        }

        // Role-based update permissions
        if ($user->hasRole(['superadmin', 'company_manager'])) {
            return true;
        }

        if ($user->hasRole('sales_manager')) {
            // Can update assessments from teams they manage
            $managedTeamIds = $user->managedTeams()->pluck('teams.id');
            
            if ($assessment->assigned_to) {
                $assignedUser = $assessment->assignedTo;
                $userTeamIds = $assignedUser->teams()->pluck('teams.id');
                return $managedTeamIds->intersect($userTeamIds)->isNotEmpty();
            }
            
            if ($assessment->lead_id) {
                return $managedTeamIds->contains($assessment->lead->team_id);
            }
            
            return false;
        }

        if ($user->hasRole('sales_coordinator')) {
            // Can update assessments but with some restrictions
            return !in_array($assessment->status, [Assessment::STATUS_IN_PROGRESS, Assessment::STATUS_COMPLETED]);
        }

        if ($user->hasRole('sales_executive')) {
            // Can only update their own assessments
            if ($assessment->assigned_to === $user->id) {
                return true;
            }
            
            // Can update if it's from their lead
            if ($assessment->lead_id) {
                return $assessment->lead->assigned_to === $user->id;
            }
            
            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the assessment.
     */
    public function delete(User $user, Assessment $assessment): bool
    {
        // Check basic permission
        if (!$user->can('delete assessments')) {
            return false;
        }

        // Multi-tenant check
        if ($assessment->company_id !== $user->company_id) {
            return false;
        }

        // Cannot delete completed assessments (data integrity)
        if ($assessment->status === Assessment::STATUS_COMPLETED) {
            return false;
        }

        // Cannot delete if assessment has photos (must remove photos first)
        if ($assessment->photos()->count() > 0) {
            return $user->hasRole(['superadmin', 'company_manager']);
        }

        // Role-based delete permissions
        if ($user->hasRole(['superadmin', 'company_manager'])) {
            return true;
        }

        if ($user->hasRole('sales_manager')) {
            // Can delete draft assessments from their teams
            if ($assessment->status === Assessment::STATUS_DRAFT) {
                $managedTeamIds = $user->managedTeams()->pluck('teams.id');
                
                if ($assessment->assigned_to) {
                    $assignedUser = $assessment->assignedTo;
                    $userTeamIds = $assignedUser->teams()->pluck('teams.id');
                    return $managedTeamIds->intersect($userTeamIds)->isNotEmpty();
                }
                
                if ($assessment->lead_id) {
                    return $managedTeamIds->contains($assessment->lead->team_id);
                }
            }
            
            return false;
        }

        if ($user->hasRole('sales_executive')) {
            // Can only delete their own draft assessments
            return $assessment->status === Assessment::STATUS_DRAFT && 
                   ($assessment->assigned_to === $user->id || 
                    ($assessment->lead_id && $assessment->lead->assigned_to === $user->id));
        }

        return false;
    }

    /**
     * Determine whether the user can upload photos to the assessment.
     */
    public function uploadPhotos(User $user, Assessment $assessment): bool
    {
        // Must be able to update the assessment
        if (!$this->update($user, $assessment)) {
            return false;
        }

        // Cannot upload photos to cancelled assessments
        if ($assessment->status === Assessment::STATUS_CANCELLED) {
            return false;
        }

        // Additional photo upload restrictions
        if ($assessment->status === Assessment::STATUS_COMPLETED) {
            // Only managers can add photos to completed assessments
            return $user->hasAnyRole(['superadmin', 'company_manager', 'sales_manager']);
        }

        return true;
    }

    /**
     * Determine whether the user can download photos from the assessment.
     */
    public function downloadPhotos(User $user, Assessment $assessment): bool
    {
        // Must be able to view the assessment
        return $this->view($user, $assessment);
    }

    /**
     * Determine whether the user can generate PDF reports for the assessment.
     */
    public function generatePdf(User $user, Assessment $assessment): bool
    {
        // Must be able to view the assessment
        if (!$this->view($user, $assessment)) {
            return false;
        }

        // Additional PDF generation permissions
        if ($user->hasRole(['superadmin', 'company_manager', 'sales_manager', 'finance_manager'])) {
            return true;
        }

        if ($user->hasRole(['sales_coordinator', 'sales_executive'])) {
            // Can generate PDF for completed assessments only
            return $assessment->status === Assessment::STATUS_COMPLETED;
        }

        return false;
    }

    /**
     * Determine whether the user can assign the assessment to other users.
     */
    public function assign(User $user, Assessment $assessment): bool
    {
        // Check basic permission
        if (!$user->can('assign assessments')) {
            return false;
        }

        // Multi-tenant check
        if ($assessment->company_id !== $user->company_id) {
            return false;
        }

        // Cannot reassign completed or cancelled assessments
        if (in_array($assessment->status, [Assessment::STATUS_COMPLETED, Assessment::STATUS_CANCELLED])) {
            return false;
        }

        // Role-based assignment permissions
        if ($user->hasRole(['superadmin', 'company_manager'])) {
            return true;
        }

        if ($user->hasRole('sales_manager')) {
            // Can assign assessments within their managed teams
            return true; // Controller will handle team-specific logic
        }

        if ($user->hasRole('sales_coordinator')) {
            // Can assign assessments but with restrictions
            return $assessment->status === Assessment::STATUS_DRAFT;
        }

        return false;
    }

    /**
     * Determine whether the user can change the assessment status.
     */
    public function changeStatus(User $user, Assessment $assessment): bool
    {
        // Must be able to update the assessment
        if (!$this->update($user, $assessment)) {
            return false;
        }

        // Role-based status change permissions
        if ($user->hasRole(['superadmin', 'company_manager'])) {
            return true; // Can change any status
        }

        if ($user->hasRole('sales_manager')) {
            return true; // Can change status for managed assessments
        }

        if ($user->hasRole('sales_coordinator')) {
            // Limited status changes
            $allowedTransitions = [
                Assessment::STATUS_DRAFT => [Assessment::STATUS_SCHEDULED],
                Assessment::STATUS_SCHEDULED => [Assessment::STATUS_CANCELLED, Assessment::STATUS_RESCHEDULED],
                Assessment::STATUS_RESCHEDULED => [Assessment::STATUS_SCHEDULED],
            ];
            
            return isset($allowedTransitions[$assessment->status]);
        }

        if ($user->hasRole('sales_executive')) {
            // Can change status for their own assessments
            $isOwner = $assessment->assigned_to === $user->id || 
                      ($assessment->lead_id && $assessment->lead->assigned_to === $user->id);
            
            return $isOwner;
        }

        return false;
    }

    /**
     * Determine whether the user can view assessment analytics and reports.
     */
    public function viewAnalytics(User $user): bool
    {
        // Analytics viewing permissions
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager',
            'finance_manager'
        ]);
    }

    /**
     * Determine whether the user can export assessment data.
     */
    public function export(User $user): bool
    {
        // Export permissions
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager',
            'finance_manager'
        ]);
    }

    /**
     * Determine whether the user can manage service assessment templates.
     */
    public function manageTemplates(User $user): bool
    {
        // Template management permissions
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can view sensitive assessment data.
     */
    public function viewSensitiveData(User $user, Assessment $assessment): bool
    {
        // Must be able to view the assessment first
        if (!$this->view($user, $assessment)) {
            return false;
        }

        // Sensitive data includes client contact info, pricing, etc.
        if ($user->hasRole(['superadmin', 'company_manager', 'finance_manager'])) {
            return true;
        }

        if ($user->hasRole('sales_manager')) {
            // Can view sensitive data for their managed teams
            $managedTeamIds = $user->managedTeams()->pluck('teams.id');
            
            if ($assessment->assigned_to) {
                $assignedUser = $assessment->assignedTo;
                $userTeamIds = $assignedUser->teams()->pluck('teams.id');
                return $managedTeamIds->intersect($userTeamIds)->isNotEmpty();
            }
            
            if ($assessment->lead_id) {
                return $managedTeamIds->contains($assessment->lead->team_id);
            }
            
            return false;
        }

        // Sales coordinators and executives have limited access to sensitive data
        return false;
    }

    /**
     * Determine whether the user can bulk update assessments.
     */
    public function bulkUpdate(User $user): bool
    {
        // Bulk update permissions
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can access assessment history and audit logs.
     */
    public function viewHistory(User $user, Assessment $assessment): bool
    {
        // Must be able to view the assessment
        if (!$this->view($user, $assessment)) {
            return false;
        }

        // History and audit log permissions
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager',
            'finance_manager'
        ]);
    }

    /**
     * Determine whether the user can schedule assessment reminders.
     */
    public function manageReminders(User $user, Assessment $assessment): bool
    {
        // Must be able to update the assessment
        if (!$this->update($user, $assessment)) {
            return false;
        }

        // Reminder management permissions
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager',
            'sales_coordinator',
            'sales_executive'
        ]);
    }

    /**
     * Determine whether the user can approve assessment results.
     */
    public function approve(User $user, Assessment $assessment): bool
    {
        // Multi-tenant check
        if ($assessment->company_id !== $user->company_id) {
            return false;
        }

        // Cannot approve incomplete assessments
        if ($assessment->status !== Assessment::STATUS_COMPLETED) {
            return false;
        }

        // Approval permissions - higher roles only
        return $user->hasAnyRole([
            'superadmin',
            'company_manager',
            'sales_manager'
        ]);
    }

    /**
     * Determine whether the user can access assessment settings.
     */
    public function manageSettings(User $user): bool
    {
        // Settings management permissions
        return $user->hasAnyRole([
            'superadmin',
            'company_manager'
        ]);
    }
}