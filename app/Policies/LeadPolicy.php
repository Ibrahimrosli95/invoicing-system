<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use App\Models\Team;

class LeadPolicy
{
    /**
     * Determine whether the user can view any leads.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('leads.view') || 
               $user->can('leads.manage');
    }

    /**
     * Determine whether the user can view the lead.
     */
    public function view(User $user, Lead $lead): bool
    {
        // Must be same company
        if ($user->company_id !== $lead->company_id) {
            return false;
        }

        // Superadmin and company manager can view all leads
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Sales managers can view leads from teams they manage
        if ($user->hasRole('sales_manager')) {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $managedTeamIds->contains($lead->team_id);
        }

        // Sales coordinators can view leads from teams they coordinate
        if ($user->hasRole('sales_coordinator')) {
            $coordinatedTeamIds = Team::where('coordinator_id', $user->id)->pluck('id');
            return $coordinatedTeamIds->contains($lead->team_id);
        }

        // Sales executives can only view leads assigned to them
        if ($user->hasRole('sales_executive')) {
            return $lead->assigned_to === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create leads.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'company_manager', 'sales_manager', 'sales_coordinator', 'sales_executive']) ||
               $user->can('leads.create');
    }

    /**
     * Determine whether the user can update the lead.
     */
    public function update(User $user, Lead $lead): bool
    {
        // Must be same company
        if ($user->company_id !== $lead->company_id) {
            return false;
        }

        // Superadmin and company manager can update all leads
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Sales managers can update leads from teams they manage
        if ($user->hasRole('sales_manager')) {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $managedTeamIds->contains($lead->team_id);
        }

        // Sales coordinators can update leads from teams they coordinate
        if ($user->hasRole('sales_coordinator')) {
            $coordinatedTeamIds = Team::where('coordinator_id', $user->id)->pluck('id');
            return $coordinatedTeamIds->contains($lead->team_id);
        }

        // Sales executives can only update leads assigned to them
        if ($user->hasRole('sales_executive')) {
            return $lead->assigned_to === $user->id;
        }

        return $user->can('leads.update');
    }

    /**
     * Determine whether the user can delete the lead.
     */
    public function delete(User $user, Lead $lead): bool
    {
        // Must be same company
        if ($user->company_id !== $lead->company_id) {
            return false;
        }

        // Only superadmin, company manager, and sales manager can delete leads
        if ($user->hasAnyRole(['superadmin', 'company_manager'])) {
            return true;
        }

        // Sales managers can only delete leads from teams they manage
        if ($user->hasRole('sales_manager')) {
            $managedTeamIds = Team::where('manager_id', $user->id)->pluck('id');
            return $managedTeamIds->contains($lead->team_id);
        }

        return $user->can('leads.delete');
    }

    /**
     * Determine whether the user can restore the lead.
     */
    public function restore(User $user, Lead $lead): bool
    {
        return $this->delete($user, $lead);
    }

    /**
     * Determine whether the user can permanently delete the lead.
     */
    public function forceDelete(User $user, Lead $lead): bool
    {
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can assign leads.
     */
    public function assign(User $user, Lead $lead): bool
    {
        // Must be same company
        if ($user->company_id !== $lead->company_id) {
            return false;
        }

        // Superadmin, company manager, and sales manager can assign leads
        if ($user->hasAnyRole(['superadmin', 'company_manager', 'sales_manager'])) {
            return true;
        }

        // Sales coordinators can assign leads from teams they coordinate
        if ($user->hasRole('sales_coordinator')) {
            $coordinatedTeamIds = Team::where('coordinator_id', $user->id)->pluck('id');
            return $coordinatedTeamIds->contains($lead->team_id);
        }

        return $user->can('leads.assign');
    }

    /**
     * Determine whether the user can add activities to the lead.
     */
    public function addActivity(User $user, Lead $lead): bool
    {
        return $this->view($user, $lead);
    }
}
